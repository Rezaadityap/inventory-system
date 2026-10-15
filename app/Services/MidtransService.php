<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MidtransService
{
    /** `transaction_status` Midtrans yang berarti dananya sudah masuk. */
    private const PAID_STATUSES = ['settlement', 'capture'];

    /**
     * Buka sesi pembayaran Snap dan kembalikan tokennya.
     *
     * Pelanggan memilih sendiri channel pembayarannya di dalam Snap, jadi di sini
     * tidak ada bank yang perlu ditentukan.
     *
     * @return array{order_id: string, token: string, redirect_url: ?string, expires_at: Carbon}
     */
    public function createPaymentSession(
        string $orderId,
        int $amount,
        string $customerName,
        ?\DateTimeInterface $expiresAt = null,
    ): array {
        // Kalau tidak diisi, sesi Snap default kedaluwarsa 24 jam.
        $expiresAt ??= now()->addDay();

        $response = $this->snapRequest()->post('/snap/v1/transactions', [
            'transaction_details' => [
                'order_id' => $orderId,
                // Midtrans hanya menerima IDR bulat -- pecahan ditolak.
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $customerName,
            ],
            'expiry' => $this->snapExpiry($expiresAt),
        ]);

        $token = $response->json('token');

        if ($response->failed() || ! $token) {
            Log::error('Midtrans snap session failed', [
                'order_id' => $orderId,
                'http_status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException(
                'Midtrans gagal membuka sesi pembayaran: '
                . ($this->snapErrorMessage($response->json()) ?: $response->body())
            );
        }

        return [
            'order_id' => $orderId,
            'token' => $token,
            'redirect_url' => $response->json('redirect_url'),
            'expires_at' => Carbon::parse($expiresAt),
        ];
    }

    /**
     * Snap melaporkan kegagalan lewat `error_messages`, bukan `status_message`.
     */
    private function snapErrorMessage(mixed $body): ?string
    {
        $messages = data_get($body, 'error_messages');

        return $messages ? implode(', ', (array) $messages) : null;
    }

    /**
     * Ambil status transaksi berdasarkan order_id.
     */
    public function findTransaction(string $orderId): ?array
    {
        $response = $this->statusRequest()->get("/v2/{$orderId}/status");

        // 404 berarti transaksinya memang belum pernah dibuat -- bukan kondisi error.
        if ($response->failed() && $response->status() !== 404) {
            Log::error('Midtrans lookup transaction failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->successful() ? ($response->json() ?: []) : null;
    }

    /**
     * Tanya langsung ke Midtrans: apakah tagihan ini sudah dibayar?
     *
     * Dipakai sebagai jaring pengaman kalau notifikasi HTTP tidak sampai
     * (URL notifikasi belum terdaftar, tunnel mati, dsb).
     */
    public function isTransactionPaid(string $orderId): bool
    {
        return $this->isPaid($this->findTransaction($orderId) ?? []);
    }

    /**
     * Apakah payload (notifikasi webhook maupun hasil cek status) menyatakan lunas?
     */
    public function isPaid(array $payload): bool
    {
        $status = strtolower((string) ($payload['transaction_status'] ?? ''));

        if (! in_array($status, self::PAID_STATUSES, true)) {
            return false;
        }

        // `capture` baru sah kalau lolos pemeriksaan fraud Midtrans.
        return $status !== 'capture'
            || strtolower((string) ($payload['fraud_status'] ?? 'accept')) === 'accept';
    }

    /**
     * Verifikasi keaslian notifikasi Midtrans.
     *
     * Midtrans tidak memakai header token; keasliannya dibuktikan lewat
     * sha512(order_id + status_code + gross_amount + server_key).
     */
    public function isValidSignature(array $payload): bool
    {
        $expected = hash('sha512',
            ($payload['order_id'] ?? '')
            . ($payload['status_code'] ?? '')
            . ($payload['gross_amount'] ?? '')
            . (string) config('services.midtrans.server_key')
        );

        return hash_equals($expected, (string) ($payload['signature_key'] ?? ''));
    }

    /**
     * Klien untuk Status API (`api.midtrans.com`). Host-nya berbeda dari Snap, dan
     * ini satu-satunya cara menanyakan status transaksi -- Snap tidak punya
     * padanannya, jadi endpoint ini tetap dipakai meski penerbitan VA lewat Snap.
     */
    private function statusRequest(): PendingRequest
    {
        return Http::withBasicAuth((string) config('services.midtrans.server_key'), '')
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->baseUrl(config('services.midtrans.is_production')
                ? 'https://api.midtrans.com'
                : 'https://api.sandbox.midtrans.com');
    }

    /** Klien untuk Snap (`app.midtrans.com`) -- host-nya berbeda dari API transaksi. */
    private function snapRequest(): PendingRequest
    {
        return Http::withBasicAuth((string) config('services.midtrans.server_key'), '')
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->baseUrl($this->snapBaseUrl());
    }

    private function snapBaseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    /**
     * Midtrans tidak menerima tanggal kedaluwarsa absolut, hanya durasi dari
     * `start_time`. Offset zona waktu wajib ikut, mis. "2026-09-05 14:30:00 +0700".
     */
    private function snapExpiry(\DateTimeInterface $expiresAt): array
    {
        $now = now();

        return [
            'start_time' => $now->format('Y-m-d H:i:s O'),
            'duration' => max(1, (int) $now->diffInMinutes($expiresAt, false)),
            'unit' => 'minute',
        ];
    }
}
