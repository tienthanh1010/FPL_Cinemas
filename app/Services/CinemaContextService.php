<?php

namespace App\Services;

use App\Models\Cinema;

class CinemaContextService
{
    private ?Cinema $resolvedCinema = null;
    private bool $hasResolved = false;

    public function singleMode(): bool
    {
        return (bool) config('cinema.single_mode', true);
    }

    public function currentCinemaId(): ?int
    {
        return $this->currentCinema()?->getKey();
    }

    public function currentCinema(): ?Cinema
    {
        if ($this->hasResolved) {
            return $this->resolvedCinema;
        }

        $this->hasResolved = true;

        if (! class_exists(Cinema::class)) {
            return null;
        }

        $configuredId = config('cinema.default_cinema_id');
        if ($configuredId) {
            $cinema = Cinema::query()
                ->whereKey((int) $configuredId)
                ->where('status', 'ACTIVE')
                ->first();

            if ($cinema) {
                return $this->resolvedCinema = $cinema;
            }
        }

        return $this->resolvedCinema = Cinema::query()
            ->where('status', 'ACTIVE')
            ->orderBy('id')
            ->first();
    }

    public function applyToCinemaQuery($query)
    {
        if (! $this->singleMode()) {
            return $query;
        }

        $cinemaId = $this->currentCinemaId();
        if ($cinemaId) {
            $query->where('id', $cinemaId);
        }

        return $query;
    }

    public function applyToAuditoriumQuery($query)
    {
        if (! $this->singleMode()) {
            return $query;
        }

        $cinemaId = $this->currentCinemaId();
        if ($cinemaId) {
            $query->where('cinema_id', $cinemaId);
        }

        return $query;
    }

    public function applyToShowQuery($query)
    {
        if (! $this->singleMode()) {
            return $query;
        }

        $cinemaId = $this->currentCinemaId();
        if ($cinemaId) {
            $query->whereHas('auditorium', fn ($auditoriumQuery) => $auditoriumQuery->where('cinema_id', $cinemaId));
        }

        return $query;
    }
}
