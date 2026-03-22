<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function enabled(): bool
    {
        return (bool) config('whatsapp.enabled')
            && !empty(config('whatsapp.phone_number_id'))
            && !empty(config('whatsapp.token'));
    }

    public function sendText(string $to, string $message): bool
    {
        if (!$this->enabled()) {
            return false;
        }

        $toNormalized = $this->normalizePhone($to);
        if (!$toNormalized) {
            return false;
        }

        $url = rtrim(config('whatsapp.api_url'), '/') . '/' . config('whatsapp.phone_number_id') . '/messages';

        $response = Http::withToken(config('whatsapp.token'))
            ->acceptJson()
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $toNormalized,
                'type' => 'text',
                'text' => [
                    'body' => $message,
                ],
            ]);

        if ($response->successful()) {
            return true;
        }

        Log::warning('WhatsApp send failed', [
            'status' => $response->status(),
            'body' => $response->body(),
            'to' => $toNormalized,
        ]);

        return false;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        // Si viene local (ej: 3001234567), agrega codigo de pais.
        if (strlen($digits) === 10) {
            $digits = config('whatsapp.default_country_code', '57') . $digits;
        }

        return $digits;
    }
}

