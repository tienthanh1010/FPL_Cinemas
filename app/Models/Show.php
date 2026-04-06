<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
<<<<<<< HEAD
use Illuminate\Support\Carbon;
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

class Show extends Model
{
    use HasFactory;

    protected $table = 'shows';

    protected $fillable = [
        'public_id',
        'auditorium_id',
        'movie_version_id',
        'pricing_profile_id',
        'start_time',
        'end_time',
        'on_sale_from',
        'on_sale_until',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'on_sale_from' => 'datetime',
        'on_sale_until' => 'datetime',
    ];

    public function auditorium(): BelongsTo
    {
        return $this->belongsTo(Auditorium::class, 'auditorium_id');
    }

    public function movieVersion(): BelongsTo
    {
        return $this->belongsTo(MovieVersion::class, 'movie_version_id');
    }

    public function pricingProfile(): BelongsTo
    {
        return $this->belongsTo(PricingProfile::class, 'pricing_profile_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(BookingTicket::class, 'show_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ShowPrice::class, 'show_id');
    }

    public function getMovieAttribute(): ?Movie
    {
        return $this->movieVersion?->movie;
    }
<<<<<<< HEAD

    public function cinemaTimezone(): string
    {
        $timezone = $this->auditorium?->cinema?->timezone ?: config('app.timezone', 'Asia/Ho_Chi_Minh');

        return match ($timezone) {
            'Asia/Ha_Noi', 'Asia/Saigon', 'Vietnam' => 'Asia/Ho_Chi_Minh',
            default => $timezone,
        };
    }

    public function currentCinemaNow(): Carbon
    {
        return now($this->cinemaTimezone());
    }

    public function hasStarted(?Carbon $now = null): bool
    {
        $now ??= $this->currentCinemaNow();

        return $this->start_time ? $now->gte($this->start_time->copy()->setTimezone($now->getTimezone())) : false;
    }

    public function isOnSaleNow(?Carbon $now = null): bool
    {
        if ($this->status !== 'ON_SALE') {
            return false;
        }

        $now ??= $this->currentCinemaNow();

        if ($this->on_sale_from && $now->lt($this->on_sale_from->copy()->setTimezone($now->getTimezone()))) {
            return false;
        }

        if ($this->on_sale_until && $now->gte($this->on_sale_until->copy()->setTimezone($now->getTimezone()))) {
            return false;
        }

        return ! $this->hasStarted($now);
    }

    public function scopeFrontendVisible(Builder $query): Builder
    {
        return $query
            ->whereIn('status', ['SCHEDULED', 'ON_SALE', 'SOLD_OUT'])
            ->where('end_time', '>', now()->subMinutes(5));
    }

    public function frontendStatusLabel(): string
    {
        return match ($this->status) {
            'CANCELLED' => 'Đã huỷ',
            'SOLD_OUT' => 'Hết vé',
            'ENDED' => 'Đã chiếu',
            'SCHEDULED' => 'Sắp mở bán',
            'ON_SALE' => $this->isOnSaleNow() ? 'Mở bán' : 'Sắp mở bán',
            default => 'Chưa mở bán',
        };
    }

=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
}
