<?php

namespace App\Core\AI;

class OpenRouterClient {
    private string $apiKey;
    private string $model;
    public function __construct(?string $apiKey = null, ?string $model = null) {
        $this->apiKey = $apiKey ?? (getenv('OPENROUTER_API_KEY') ?: '');
        $this->model = $model ?? (getenv('OPENROUTER_MODEL') ?: 'qwen/qwen3-coder:free');
    }
    public function chat(string $system, string $user): array {
        if ($this->apiKey === '') {
            return ['ok' => false, 'error' => 'missing_api_key'];
        }
        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ];
        $ch = curl_init('https://api.openrouter.ai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($err) {
            return ['ok' => false, 'error' => $err];
        }
        $data = json_decode($resp, true);
        if ($status >= 200 && $status < 300 && isset($data['choices'][0]['message']['content'])) {
            return ['ok' => true, 'content' => $data['choices'][0]['message']['content']];
        }
        return ['ok' => false, 'error' => is_string($resp) ? $resp : 'request_failed'];
    }
}

