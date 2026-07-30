<?php
/**
 * Ollama Client - Local AI Integration
 * Uses Qwen 2.5 7B (or configured model) for agentic reasoning
 */

namespace App\Core\Agentic;

class OllamaClient
{
    private string $baseUrl;
    private string $model;
    private string $fallbackModel;
    private int $timeoutMs;
    private bool $enabled;

    public function __construct(array $config)
    {
        $this->enabled = $config['enabled'] ?? false;
        $this->baseUrl = $config['base_url'] ?? 'http://localhost:11434';
        $this->model = $config['model'] ?? 'qwen2.5:7b';
        $this->fallbackModel = $config['fallback_model'] ?? 'qwen2.5:3b';
        $this->timeoutMs = $config['timeout_ms'] ?? 120000;
    }

    public function isAvailable(): bool
    {
        if (!$this->enabled) return false;

        try {
            $response = $this->request('/api/tags', [], 'GET');
            return isset($response['models']) && is_array($response['models']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getModel(): string
    {
        if ($this->isModelAvailable($this->model)) return $this->model;
        if ($this->isModelAvailable($this->fallbackModel)) return $this->fallbackModel;
        return '';
    }

    public function isModelAvailable(string $model): bool
    {
        try {
            $response = $this->request('/api/tags', [], 'GET');
            if (($response['models'] ?? []) === null) return false;
            foreach ($response['models'] as $m) {
                if (strpos($m['name'], $model) === 0) return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function generate(string $prompt, string $system = '', int $maxTokens = 2048): string
    {
        $model = $this->getModel();
        if ($model === '') {
            throw new \RuntimeException('No Ollama model available');
        }

        $body = [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0.3,
                'num_predict' => $maxTokens,
            ]
        ];

        if (!empty($system)) {
            $body['system'] = $system;
        }

        $response = $this->request('/api/generate', $body, 'POST');
        return $response['response'] ?? '';
    }

    public function chat(array $messages, int $maxTokens = 2048): string
    {
        $model = $this->getModel();
        if ($model === '') {
            throw new \RuntimeException('No Ollama model available');
        }

        $body = [
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => 0.3,
                'num_predict' => $maxTokens,
            ]
        ];

        $response = $this->request('/api/chat', $body, 'POST');
        return $response['message']['content'] ?? '';
    }

    private function request(string $endpoint, array $body, string $method): array
    {
        $url = $this->baseUrl . $endpoint;

        $opts = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => $method,
                'timeout' => $this->timeoutMs / 1000,
            ]
        ];

        if ($method === 'POST') {
            $opts['http']['content'] = json_encode($body);
        }

        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            throw new \RuntimeException("Ollama request failed: {$url}");
        }

        $decoded = json_decode($result, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Ollama response parse error: " . json_last_error_msg());
        }

        return $decoded ?? [];
    }
}