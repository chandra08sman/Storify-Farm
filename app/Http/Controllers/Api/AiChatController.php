<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    public function chat(Request $request)
    {
        $data = $request->validate([
            'system' => ['required', 'string'],
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['nullable', 'array'],
            'history.*.role' => ['required_with:history', 'in:user,model'],
            'history.*.text' => ['required_with:history', 'string'],
        ]);

        $history = collect($data['history'] ?? [])
            ->map(fn ($h) => [
                'role' => $h['role'],
                'parts' => [['text' => $h['text']]],
            ])
            ->push(['role' => 'user', 'parts' => [['text' => $data['message']]]])
            ->values()
            ->all();
        $errors = [];
        $requiresCurrentWebData = (bool) preg_match(
            '/harga\s+pasar|harga\s+(beras|komoditas)|harga\s+sekarang|harga\s+terkini|harga\s+hari\s+ini|harga\s+terbaru/i',
            $data['message']
        );

        if ($requiresCurrentWebData) {
            $data['system'] .= "\n\nATURAN WAJIB UNTUK PERTANYAAN HARGA: gunakan pencarian web dan data terbaru. Jangan mengarang angka. Setiap angka harga harus menyebutkan tanggal/periode dan sumber yang ditemukan. Jika sumber terbaru atau lokasi pasar tidak jelas, katakan data harga tidak dapat dipastikan dan jangan memberikan angka perkiraan.";
        }

        if (config('services.ollama.enabled') && ! $requiresCurrentWebData) {
            try {
                $messages = collect($data['history'] ?? [])
                    ->map(fn ($h) => [
                        'role' => $h['role'] === 'model' ? 'assistant' : 'user',
                        'content' => $h['text'],
                    ])
                    ->prepend(['role' => 'system', 'content' => $data['system']])
                    ->push(['role' => 'user', 'content' => $data['message']])
                    ->values()
                    ->all();

                // Ollama is optional: fail quickly, then continue to Gemini.
                $ollamaRequest = Http::timeout(config('services.ollama.timeout', 8));
                if (config('services.ollama.api_key')) {
                    $ollamaRequest = $ollamaRequest->withToken(config('services.ollama.api_key'));
                }
                $response = $ollamaRequest
                    ->post(config('services.ollama.url').'/api/chat', [
                        'model' => config('services.ollama.model', 'qwen2.5:0.5b'),
                        'messages' => $messages,
                        'stream' => false,
                        'options' => [
                            'temperature' => 0.2,
                            'num_predict' => config('services.ollama.max_output_tokens', 384),
                        ],
                    ]);
                $text = trim((string) $response->json('message.content', ''));

                if ($response->successful() && $text !== '') {
                    return response()->json(['ok' => true, 'reply' => $text, 'provider' => 'ollama']);
                }

                $errors[] = 'Ollama '.$response->status();
                Log::warning('Storify AI: Ollama gagal, mencoba provider berikutnya', ['status' => $response->status()]);
            } catch (\Throwable $e) {
                $errors[] = 'Ollama network error';
                Log::warning('Storify AI: Ollama tidak tersedia, mencoba provider berikutnya', ['error' => $e->getMessage()]);
            }
        }

        // Ollama failure must never stop the primary cloud fallback.
        $allowWebSearch = true;

        foreach (config('services.gemini.keys', []) as $apiKey) {
            try {
                $model = config('services.gemini.model', 'gemini-3.6-flash');
                $geminiPayload = [
                    'systemInstruction' => ['parts' => [['text' => $data['system']]]],
                    'contents' => $history,
                    'generationConfig' => ['maxOutputTokens' => 1024],
                ];
                if ($allowWebSearch) {
                    $geminiPayload['tools'] = [['google_search' => (object) []]];
                }
                $response = Http::timeout(60)->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.urlencode($apiKey),
                    $geminiPayload
                );
                $text = collect($response->json('candidates.0.content.parts', []))->pluck('text')->filter()->implode("\n");
                if ($response->successful() && $text !== '') {
                    if ($requiresCurrentWebData && ! $this->isUsableCurrentWebAnswer($text)) {
                        $errors[] = 'Gemini returned an unsourced price answer';
                        continue;
                    }
                    return response()->json(['ok' => true, 'reply' => $text]);
                }
                $errors[] = 'Gemini '.$response->status();
                Log::warning('Storify AI: Gemini key gagal, mencoba key berikutnya', ['status' => $response->status()]);
            } catch (\Throwable $e) {
                $errors[] = 'Gemini network error';
                Log::warning('Storify AI: Gemini key gagal', ['error' => $e->getMessage()]);
            }
        }

        foreach (config('services.openai.keys', []) as $apiKey) {
            try {
                $messages = collect($data['history'] ?? [])
                    ->map(fn ($h) => ['role' => $h['role'] === 'model' ? 'assistant' : 'user', 'content' => $h['text']])
                    ->prepend(['role' => 'system', 'content' => $data['system']])
                    ->push(['role' => 'user', 'content' => $data['message']])
                    ->values()
                    ->all();
                $response = Http::withToken($apiKey)->timeout(60)->post('https://api.openai.com/v1/responses', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'input' => $messages,
                    'tools' => [['type' => 'web_search_preview']],
                    'max_output_tokens' => 1024,
                ]);
                $text = collect($response->json('output', []))
                    ->flatMap(fn ($item) => $item['content'] ?? [])
                    ->pluck('text')
                    ->filter()
                    ->implode("\n");
                if ($response->successful() && $text !== '') {
                    if ($requiresCurrentWebData && ! $this->isUsableCurrentWebAnswer($text)) {
                        $errors[] = 'OpenAI returned an unsourced price answer';
                        continue;
                    }
                    return response()->json(['ok' => true, 'reply' => $text]);
                }
                $errors[] = 'OpenAI '.$response->status();
                Log::warning('Storify AI: OpenAI key gagal, mencoba key berikutnya', ['status' => $response->status()]);
            } catch (\Throwable $e) {
                $errors[] = 'OpenAI network error';
                Log::warning('Storify AI: OpenAI key gagal', ['error' => $e->getMessage()]);
            }
        }

        if (! config('services.gemini.keys', []) && ! config('services.openai.keys', [])) {
            return response()->json(['ok' => false, 'message' => 'API key AI belum diatur di file .env.'], 503);
        }

        if (collect($errors)->contains(fn ($error) => str_contains($error, '429'))) {
            $provider = collect($errors)->contains(fn ($error) => str_starts_with($error, 'Ollama'))
                ? 'Ollama Cloud'
                : 'Gemini/OpenAI';
            return response()->json(['ok' => false, 'message' => 'Kuota '.$provider.' sedang habis atau request terlalu banyak. Periksa usage/billing atau gunakan API key lain.'], 429);
        }

        return response()->json(['ok' => false, 'message' => 'Semua API AI sedang tidak tersedia. Periksa API key dan koneksi server.'], 503);
    }

    private function isUsableCurrentWebAnswer(string $text): bool
    {
        $normalized = mb_strtolower($text);
        $isExplicitlyUnavailable = str_contains($normalized, 'tidak dapat dipastikan')
            || str_contains($normalized, 'data tidak tersedia')
            || str_contains($normalized, 'tidak menemukan data');

        if ($isExplicitlyUnavailable) {
            return true;
        }

        $hasDateOrSource = preg_match('/20\d{2}|hari ini|tanggal|per tanggal|sumber|source|https?:\/\//i', $text);
        $isGreeting = str_contains($normalized, 'halo')
            || str_contains($normalized, 'ada yang mau ditanyakan')
            || str_contains($normalized, 'storify assistant');

        return (bool) $hasDateOrSource && ! $isGreeting;
    }
}
