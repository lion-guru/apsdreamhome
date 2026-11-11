<?php
/**
 * VoiceProviderInterface — Interface for voice providers
 */

namespace App\Services\Providers;

interface VoiceProviderInterface
{
    public function makeCall(string $to, string $from, array $options): array;
    public function generateWebhookResponse(string $event, array $params): string;
    public function handleIncomingCall(array $params): array;
    public function handleCallStatus(array $params): array;
    public function handleRecording(array $params): array;
    public function handleTranscription(array $params): array;
    public function getCallDetails(string $callSid): array;
    public function getRecordingUrl(string $recordingSid): ?string;
    public function downloadRecording(string $recordingSid, string $savePath): bool;
    public function validateSignature(array $params, string $signature): bool;
    public function getCapabilities(): array;
    public function testConnection(): array;
}