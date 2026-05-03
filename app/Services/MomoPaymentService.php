<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MomoPaymentService
{
    public function createPayment(Payment $payment, Booking $booking): array
    {
        $this->ensureConfigured();

        $amount = (int) $payment->amount;
        $orderId = $this->newOrderId($payment);
        $requestId = $orderId;
        $orderInfo = 'Thanh toán qua ATM MoMo';
        $extraData = '';

        // MoMo ATM sandbox is sensitive to the shape of the create-payment request.
        // Keep the payload close to MoMo's official ATM sample: short unique orderId,
        // empty extraData, no autoCapture, partnerName=Test and storeId=MomoTestStore.

        $redirectUrl = $this->redirectUrl();
        $ipnUrl = $this->ipnUrl();
        $requestType = $this->requestType();

        $rawSignature = $this->buildCreateRawSignature([
            'amount' => $amount,
            'extraData' => $extraData,
            'ipnUrl' => $ipnUrl,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'requestId' => $requestId,
            'requestType' => $requestType,
        ]);

        $payload = [
            'partnerCode' => $this->partnerCode(),
            'partnerName' => 'Test',
            'storeId' => 'MomoTestStore',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'requestType' => $requestType,
            'extraData' => $extraData,
            'signature' => $this->hash($rawSignature),
        ];

        $response = Http::asJson()
            ->acceptJson()
            ->timeout(20)
            ->post($this->endpoint(), $payload);

        $body = $response->json() ?: [];
        if (! $response->successful()) {
            Log::warning('MoMo create payment HTTP error', [
                'status' => $response->status(),
                'request' => Arr::except($payload, ['signature']),
                'response' => $response->body(),
            ]);

            throw new \RuntimeException('Không kết nối được MoMo. HTTP ' . $response->status() . '. Xem storage/logs/laravel.log để biết phản hồi chi tiết từ MoMo.');
        }

        if ((int) Arr::get($body, 'resultCode', -1) !== 0) {
            Log::warning('MoMo create payment rejected', [
                'request' => Arr::except($payload, ['signature']),
                'response' => $body,
            ]);

            throw new \RuntimeException((string) (Arr::get($body, 'message') ?: 'MoMo không tạo được giao dịch.'));
        }

        return [
            'request' => $payload,
            'response' => $body,
            'order_id' => $orderId,
            'request_id' => $requestId,
            'order_info' => $orderInfo,
            'pay_url' => Arr::get($body, 'payUrl'),
            'deeplink' => Arr::get($body, 'deeplink'),
            'qr_code_url' => Arr::get($body, 'qrCodeUrl') ?: Arr::get($body, 'qrCode'),
        ];
    }


    private function newOrderId(Payment $payment): string
    {
        // Official MoMo ATM examples use a short, unique orderId. Using a long value
        // such as MOMO-2026... can still open the payment page, but some sandbox
        // bank/issuer test flows reject the payment after entering card details.
        return now()->format('YmdHis') . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT) . random_int(10, 99);
    }

    public function verifyResultSignature(array $data): bool
    {
        $signature = (string) Arr::get($data, 'signature', '');
        if ($signature === '') {
            return false;
        }

        return hash_equals($signature, $this->hash($this->buildResultRawSignature($data)));
    }

    public function resultOrderId(array $data): ?string
    {
        $orderId = trim((string) Arr::get($data, 'orderId', ''));

        return $orderId !== '' ? $orderId : null;
    }

    public function isSuccessfulResult(array $data): bool
    {
        return (int) Arr::get($data, 'resultCode', -1) === 0;
    }

    public function isCancelledResult(array $data): bool
    {
        return in_array((int) Arr::get($data, 'resultCode', -1), [1006, 1027], true);
    }

    private function buildCreateRawSignature(array $data): string
    {
        return 'accessKey=' . $this->accessKey()
            . '&amount=' . $data['amount']
            . '&extraData=' . $data['extraData']
            . '&ipnUrl=' . $data['ipnUrl']
            . '&orderId=' . $data['orderId']
            . '&orderInfo=' . $data['orderInfo']
            . '&partnerCode=' . $this->partnerCode()
            . '&redirectUrl=' . $data['redirectUrl']
            . '&requestId=' . $data['requestId']
            . '&requestType=' . $data['requestType'];
    }

    private function buildResultRawSignature(array $data): string
    {
        return 'accessKey=' . $this->accessKey()
            . '&amount=' . (string) Arr::get($data, 'amount', '')
            . '&extraData=' . (string) Arr::get($data, 'extraData', '')
            . '&message=' . (string) Arr::get($data, 'message', '')
            . '&orderId=' . (string) Arr::get($data, 'orderId', '')
            . '&orderInfo=' . (string) Arr::get($data, 'orderInfo', '')
            . '&orderType=' . (string) Arr::get($data, 'orderType', '')
            . '&partnerCode=' . (string) Arr::get($data, 'partnerCode', '')
            . '&payType=' . (string) Arr::get($data, 'payType', '')
            . '&requestId=' . (string) Arr::get($data, 'requestId', '')
            . '&responseTime=' . (string) Arr::get($data, 'responseTime', '')
            . '&resultCode=' . (string) Arr::get($data, 'resultCode', '')
            . '&transId=' . (string) Arr::get($data, 'transId', '');
    }

    private function hash(string $raw): string
    {
        return hash_hmac('sha256', $raw, $this->secretKey());
    }

    private function ensureConfigured(): void
    {
        foreach (['partner_code', 'access_key', 'secret_key', 'endpoint'] as $key) {
            if (blank(config('services.momo.' . $key))) {
                throw new \RuntimeException('Thiếu cấu hình MoMo: services.momo.' . $key . '. Vui lòng kiểm tra file .env.');
            }
        }
    }

    private function partnerCode(): string
    {
        return (string) config('services.momo.partner_code');
    }

    private function accessKey(): string
    {
        return (string) config('services.momo.access_key');
    }

    private function secretKey(): string
    {
        return (string) config('services.momo.secret_key');
    }

    private function endpoint(): string
    {
        return rtrim((string) config('services.momo.endpoint'), '/');
    }

    private function requestType(): string
    {
        return (string) config('services.momo.request_type', 'captureWallet');
    }

    private function redirectUrl(): string
    {
        return (string) (config('services.momo.redirect_url') ?: route('payment.momo.return'));
    }

    private function ipnUrl(): string
    {
        return (string) (config('services.momo.ipn_url') ?: route('payment.momo.ipn'));
    }
}
