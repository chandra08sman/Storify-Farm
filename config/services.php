<?php

return [
    'ollama' => [
        'enabled' => (bool) env('OLLAMA_ENABLED', true),
        'url' => rtrim(env('OLLAMA_URL', 'http://127.0.0.1:11434'), '/'),
        'api_key' => env('OLLAMA_API_KEY'),
        'model' => env('OLLAMA_MODEL', 'qwen2.5:0.5b'),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 8),
        'max_output_tokens' => (int) env('OLLAMA_MAX_OUTPUT_TOKENS', 384),
    ],
    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
    ],
    'gemini' => [
        'keys' => array_values(array_filter(array_map('trim', explode(',', env('GEMINI_API_KEYS', env('GEMINI_API_KEY', '')))))),
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
    ],
    'openai' => [
        'keys' => array_values(array_filter(array_map('trim', explode(',', env('OPENAI_API_KEYS', env('OPENAI_API_KEY', '')))))),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'search_model' => env('OPENAI_SEARCH_MODEL', 'gpt-4o-search-preview'),
    ],
];
