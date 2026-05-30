<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.sumopod.base_url'), '/');
        $this->apiKey  = (string) config('services.sumopod.api_key');
    }

    /**
     * Returns up to 3 RAG-grounded suggestions: [{label, text, source}]
     * Returns [] on any failure — callers must not throw.
     */
    public function suggestReplies(string $ticketContext, string $sopContext): array
    {
        $systemPrompt = <<<'PROMPT'
You are an IT helpdesk agent assistant. You MUST only suggest replies grounded in the provided SOP/Knowledge Base below.
Do NOT invent information not found in the SOP. If the SOP does not cover the topic, suggest asking the customer for more details.

Respond ONLY with a valid JSON array of exactly 3 objects. Each object must have:
- "label": a short action label in Indonesian (max 4 words)
- "text": the full reply text in Indonesian (1-3 sentences, professional and friendly tone)
- "source": the exact SOP title this reply is based on (from the provided SOP list)

No markdown, no explanation, no text outside the JSON array.
PROMPT;

        $userMessage = $ticketContext . "\n\n--- SOP / Knowledge Base ---\n" . $sopContext;

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'       => 'gpt-4o-mini',
                    'temperature' => 0.4,
                    'max_tokens'  => 500,
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $userMessage],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('AiService: API request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            $raw     = $response->json('choices.0.message.content', '');
            $decoded = json_decode(trim($raw), true);

            if (! is_array($decoded)) {
                Log::warning('AiService: response is not a valid JSON array', ['raw' => $raw]);
                return [];
            }

            return collect($decoded)
                ->filter(fn ($item) =>
                    is_array($item) &&
                    isset($item['label'], $item['text'], $item['source']) &&
                    is_string($item['label']) &&
                    is_string($item['text']) &&
                    is_string($item['source'])
                )
                ->map(fn ($item) => [
                    'label'  => mb_substr(trim($item['label']), 0, 60),
                    'text'   => mb_substr(trim($item['text']), 0, 800),
                    'source' => mb_substr(trim($item['source']), 0, 120),
                ])
                ->values()
                ->take(3)
                ->all();

        } catch (\Throwable $e) {
            Log::error('AiService: exception', ['message' => $e->getMessage()]);
            return [];
        }
    }
}
