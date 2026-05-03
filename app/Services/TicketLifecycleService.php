<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingTicket;
use App\Models\Ticket;
use Illuminate\Support\Str;

class TicketLifecycleService
{
    public function syncForBooking(Booking $booking): array
    {
        $summary = [
            'booking_id' => $booking->id,
            'created' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'skipped' => 0,
        ];

        $booking->loadMissing(['tickets.ticket', 'payments.refunds']);

        $fullyRefunded = $this->isFullyRefunded($booking);
        $bookingStatus = (string) $booking->status;

        foreach ($booking->tickets as $bookingTicket) {
            $bookingTicket->loadMissing('booking');
            $targetStatus = $this->resolveTicketStatus($bookingTicket, $bookingStatus, $fullyRefunded);

            if ($targetStatus === null) {
                $summary['skipped']++;
                continue;
            }

            $ticket = $bookingTicket->ticket;
            if (! $ticket && in_array($targetStatus, ['ISSUED', 'USED'], true)) {
                $ticketCode = $this->generateTicketCode();
                $ticket = Ticket::create([
                    'booking_ticket_id' => $bookingTicket->id,
                    'ticket_code' => $ticketCode,
                    'qr_payload' => $this->buildQrPayload($bookingTicket, $ticketCode),
                    'status' => $targetStatus,
                    'issued_at' => $targetStatus === 'ISSUED' ? now() : null,
                    'used_at' => $targetStatus === 'USED' ? now() : null,
                ]);
                $bookingTicket->setRelation('ticket', $ticket);
                $summary['created']++;
                continue;
            }

            if (! $ticket) {
                $summary['skipped']++;
                continue;
            }

            $payload = ['status' => $targetStatus];

            if ($targetStatus === 'ISSUED') {
                $payload['issued_at'] = $ticket->issued_at ?: now();
                $payload['used_at'] = null;
            } elseif ($targetStatus === 'USED') {
                $payload['issued_at'] = $ticket->issued_at ?: now();
                $payload['used_at'] = $ticket->used_at ?: now();
            } elseif (in_array($targetStatus, ['VOID', 'REFUNDED'], true)) {
                if ($ticket->status !== 'USED') {
                    $payload['used_at'] = null;
                }
                $payload['issued_at'] = $ticket->issued_at;
            }

            $dirty = false;
            foreach ($payload as $key => $value) {
                if ($ticket->{$key} != $value) {
                    $dirty = true;
                    break;
                }
            }

            if ($dirty) {
                $ticket->update($payload);
                $summary['updated']++;
            } else {
                $summary['unchanged']++;
            }
        }

        return $summary;
    }

    private function resolveTicketStatus(BookingTicket $bookingTicket, string $bookingStatus, bool $fullyRefunded): ?string
    {
        if ($fullyRefunded) {
            return 'REFUNDED';
        }

        return match ((string) $bookingTicket->status) {
            'ISSUED' => $bookingTicket->ticket?->status === 'USED' ? 'USED' : 'ISSUED',
            'CANCELLED', 'EXPIRED' => 'VOID',
            'RESERVED' => $bookingTicket->ticket ? 'VOID' : null,
            default => $bookingTicket->ticket?->status,
        };
    }

    private function isFullyRefunded(Booking $booking): bool
    {
        $paidAmount = (int) $booking->paid_amount;
        $successfulRefundAmount = (int) $booking->payments
            ->flatMap(fn ($payment) => $payment->refunds)
            ->where('status', 'SUCCESS')
            ->sum('amount');

        return $successfulRefundAmount > 0 && $paidAmount <= 0 && $booking->total_amount > 0;
    }

    private function generateTicketCode(): string
    {
        do {
            $code = 'TK' . strtoupper(Str::random(10));
        } while (Ticket::query()->where('ticket_code', $code)->exists());

        return $code;
    }

    private function buildQrPayload(BookingTicket $bookingTicket, string $ticketCode): string
    {
        $booking = $bookingTicket->booking;

        return (string) json_encode([
            'ticket_code' => $ticketCode,
            'booking_code' => $booking?->booking_code,
            'booking_ticket_id' => $bookingTicket->id,
            'seat_id' => $bookingTicket->seat_id,
            'show_id' => $bookingTicket->show_id,
        ], JSON_UNESCAPED_UNICODE);
    }
}
