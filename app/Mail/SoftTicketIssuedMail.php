<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SoftTicketIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        $this->booking->loadMissing([
            'tickets.ticket',
            'tickets.seat',
            'tickets.ticketType',
            'tickets.seatType',
            'show.movieVersion.movie',
            'show.auditorium.cinema',
            'customer',
        ]);
    }

    public function build(): self
    {
        return $this->subject('Vé điện tử ' . $this->booking->booking_code)
            ->view('emails.soft_ticket_issued', [
                'booking' => $this->booking,
            ]);
    }
}
