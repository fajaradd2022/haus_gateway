<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaService
{
    private string $baseUrl;
    private string $apiKey;
    private string $session;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.waha.url'), '/');
        $this->apiKey  = (string) config('services.waha.api_key');
        $this->session = (string) config('services.waha.session', 'default');
    }

    public function sendText(string $phone, string $text, ?string $session = null): ?string
    {
        $chatId  = $this->toChatId($phone);
        $session = $session ?? $this->session;

        try {
            $response = Http::withHeader('X-Api-Key', $this->apiKey)
                ->timeout(10)
                ->post("{$this->baseUrl}/api/sendText", [
                    'session' => $session,
                    'chatId'  => $chatId,
                    'text'    => $text,
                ]);

            if ($response->failed()) {
                Log::error('WAHA sendText failed', [
                    'phone'  => $phone,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->json('id');
        } catch (\Throwable $e) {
            Log::error('WAHA sendText exception', [
                'phone'   => $phone,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function sendMedia(string $phone, string $mediaUrl, string $caption = '', ?string $session = null): ?string
    {
        $chatId  = $this->toChatId($phone);
        $session = $session ?? $this->session;

        try {
            $response = Http::withHeader('X-Api-Key', $this->apiKey)
                ->timeout(10)
                ->post("{$this->baseUrl}/api/sendImage", [
                    'session' => $session,
                    'chatId'  => $chatId,
                    'caption' => $caption,
                    'file'    => ['url' => $mediaUrl],
                ]);

            if ($response->failed()) {
                Log::error('WAHA sendMedia failed', [
                    'phone'  => $phone,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->json('id');
        } catch (\Throwable $e) {
            Log::error('WAHA sendMedia exception', [
                'phone'   => $phone,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function toChatId(string $phone): string
    {
        $phone = preg_replace('/@c\.us$/', '', $phone);
        return "{$phone}@c.us";
    }
}
