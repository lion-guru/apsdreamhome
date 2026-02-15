<?php

namespace App\Services\Legacy;
/**
 * Ollama Client for Local AI Integration
 * Provides a simple interface to interact with Ollama's local API
 */

class OllamaClient {
    private $baseUrl;
    private $model;

    public function __construct($model = 'llama3', $baseUrl = 'http://localhost:11434') {
        $this->baseUrl = $baseUrl;
        $this->model = $model;
    }

    /**
     * Generate text using Ollama
     * @param string $prompt The input prompt
     * @param array $options Additional options (temperature, etc.)
     * @return string|null Generated text or null on error
     */
    public function generate($prompt, $options = []) {
        $data = [
            'model' => $this->model,
            'prompt' => $prompt,
            'stream' => false
        ];

        if (!empty($options)) {
            $data = array_merge($data, $options);
        }

        $response = $this->makeRequest('/api/generate', $data);

        if ($response && isset($response['response'])) {
            return $response['response'];
        }

        return null;
    }

    /**
     * Chat with the model
     * @param array $messages Array of message objects
     * @param array $options Additional options
     * @return string|null Response or null on error
     */
    public function chat($messages, $options = []) {
        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false
        ];

        if (!empty($options)) {
            $data = array_merge($data, $options);
        }

        $response = $this->makeRequest('/api/chat', $data);

        if ($response && isset($response['message']['content'])) {
            return $response['message']['content'];
        }

        return null;
    }

    /**
     * Check if Ollama is running
     * @return bool
     */
    public function isAvailable() {
        $response = $this->makeRequest('/api/tags', []);
        return $response !== null;
    }

    /**
     * List available models
     * @return array|null
     */
    public function listModels() {
        $response = $this->makeRequest('/api/tags', []);
        return $response ? $response['models'] : null;
    }

    /**
     * Make HTTP request to Ollama API
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array|null Decoded JSON response or null on error
     */
    private function makeRequest($endpoint, $data) {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout for local requests

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }

        return null;
    }
}

/**
 * Helper function to get Ollama client instance
 * @param string $model Model name (default: llama3)
 * @return OllamaClient
 */
function getOllamaClient($model = 'llama3') {
    return new OllamaClient($model);
}
?>
