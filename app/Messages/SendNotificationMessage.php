<?php

namespace App\Messages;

use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * SendNotificationMessage - Queue job for sending notifications
 * Supports multiple channels: in_app, push, email, whatsapp, sms
 */
#[AsMessage('notifications')]
class SendNotificationMessage
{
    public function __construct(
        public readonly int $userId,
        public readonly string $title,
        public readonly string $message,
        public readonly array $data = [],
        public readonly array $channels = ['in_app', 'push'],
        public readonly ?int $priority = null,
        public readonly ?string $actionUrl = null,
        public readonly ?string $templateKey = null,
        public readonly ?int $tenantId = 1
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'channels' => $this->channels,
            'priority' => $this->priority,
            'action_url' => $this->actionUrl,
            'template_key' => $this->templateKey,
            'tenant_id' => $this->tenantId,
        ];
    }
}