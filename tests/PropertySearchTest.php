<?php
namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class PropertySearchTest extends TestCase
{
    public function testPropertySearchEndpointRespondsWith200()
    {
        $url = 'http://localhost:8000/properties/search?location=Delhi&min_price=1000000&max_price=5000000';
        $response = $this->httpGet($url);
        $this->assertStringContains('200', $response['status']);
    }

    private function httpGet(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Accept: application/json'
            ]
        ]);
        $content = file_get_contents($url, false, $context);
        return [
            'status' => $http_response_header[0] ?? '',
            'body'   => $content
        ];
    }
}