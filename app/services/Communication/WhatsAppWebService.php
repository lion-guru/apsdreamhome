<?php
namespace App\Services\Communication;

use Exception;

class WhatsAppWebService
{
    private string $baseUrl;
    private int $timeout = 10;

    public function __construct()
    {
        $host = getenv('WA_HOST') ?: '127.0.0.1';
        $port = getenv('WA_PORT') ?: '3001';
        $this->baseUrl = "http://{$host}:{$port}";
    }

    public function isConnected(): array
    {
        return $this->get('/api/status');
    }

    public function sendMessage(string $to, string $message, ?array $media = null): array
    {
        return $this->post('/api/send', [
            'to' => $to,
            'message' => $message,
            'media' => $media
        ]);
    }

    public function sendTemplate(string $to, string $message, ?string $title = null, ?string $footer = null): array
    {
        return $this->post('/api/send-template', [
            'to' => $to,
            'message' => $message,
            'title' => $title,
            'footer' => $footer
        ]);
    }

    public function getQR(): array
    {
        return $this->get('/api/qr');
    }

    public function reconnect(): array
    {
        return $this->post('/api/reconnect');
    }

    public function logout(): array
    {
        return $this->post('/api/logout');
    }

    private function get(string $path): array
    {
        $ctx = stream_context_create([
            'http' => ['timeout' => $this->timeout, 'method' => 'GET']
        ]);
        $result = @file_get_contents($this->baseUrl . $path, false, $ctx);
        return $result ? json_decode($result, true) : ['error' => 'Connection refused'];
    }

    private function post(string $path, array $data): array
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data)
            ]
        ]);
        $result = @file_get_contents($this->baseUrl . $path, false, $ctx);
        return $result ? json_decode($result, true) : ['error' => 'Connection refused'];
    }
}
