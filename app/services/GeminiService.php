<?php

namespace App\Services;

class GeminiService {
    private $config;
    private $apiKey;
    private $apiUrl;

    public function __construct() {
        require_once __DIR__ . '/../config/env.php';
        $this->config = require_once __DIR__ . '/../config/gemini_config.php';
        $this->apiKey = $this->config['api_key'];
        $this->apiUrl = $this->config['api_url'];
    }

    public function generateContent($prompt) {
        if (empty($this->apiKey)) {
            return [
                'error' => true,
                'message' => 'Gemini API key is not configured. Please check your environment settings.'
            ];
        }

        try {
            $ch = curl_init();
            $url = $this->apiUrl . '?key=' . $this->apiKey;

            $data = [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ];

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new \Exception('cURL Error: ' . $error);
            }

            if ($httpCode === 403) {
                return [
                    'error' => true,
                    'message' => 'Authentication failed. Please verify your Gemini API key.'
                ];
            }

            if ($httpCode !== 200) {
                throw new \Exception('API request failed with status code: ' . $httpCode);
            }

            $decodedResponse = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Failed to decode API response: ' . json_last_error_msg());
            }

            return $decodedResponse;
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}