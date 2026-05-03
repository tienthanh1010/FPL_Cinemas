<?php

namespace App\Services;

use App\Models\Booking;

class BankTransferQrService
{
    public function providerLabel(): string
    {
        return (string) config('cinema_booking.bank_transfer.provider_label', 'MB Bank');
    }

    public function bankId(): string
    {
        return (string) config('cinema_booking.bank_transfer.bank_id', 'MBBank');
    }

    public function accountNumber(): string
    {
        return (string) config('cinema_booking.bank_transfer.account_no', '000230705');
    }

    public function accountName(): string
    {
        return (string) config('cinema_booking.bank_transfer.account_name', 'FPL CINEMA');
    }

    public function qrTemplate(): string
    {
        return (string) config('cinema_booking.bank_transfer.qr_template', 'compact2');
    }

    public function transferContent(Booking $booking): string
    {
        $prefix = (string) config('cinema_booking.bank_transfer.description_prefix', 'FPL');

        return booking_transfer_reference($prefix . ' ' . $booking->booking_code);
    }

    public function qrImageUrl(Booking $booking, int $amount): string
    {
        return vietqr_url(
            bankId: $this->bankId(),
            accountNo: $this->accountNumber(),
            amount: $amount,
            addInfo: $this->transferContent($booking),
            accountName: $this->accountName(),
            template: $this->qrTemplate(),
        );
    }

    public function payloadForBooking(Booking $booking, int $amount): array
    {
        return [
            'provider_label' => $this->providerLabel(),
            'bank_id' => $this->bankId(),
            'account_no' => $this->accountNumber(),
            'account_name' => $this->accountName(),
            'transfer_content' => $this->transferContent($booking),
            'amount' => $amount,
            'qr_template' => $this->qrTemplate(),
            'qr_image_url' => $this->qrImageUrl($booking, $amount),
        ];
    }
}
