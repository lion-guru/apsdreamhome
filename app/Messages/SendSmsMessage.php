<?php

namespace App\Messages;

use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * SendSmsMessage - Queue job for sending SMS
 */
#[AsMessage('sms')]
class SendSmsMessage
{
    public function __construct(
        public readonly string $to,
        public readonly string $body,
        public readonly ?string $from = null,
        public readonly ?string $templateKey = null,
        public readonly array $templateData = [],
        public readonly ?int $tenantId = 1
    ) {}

    public function toArray(): array
    {
        return [
            'to' => $this->to,
            'body' => $this->body,
            'from' => $this->from,
            'template_key' => $this->templateKey,
            'template_data' => $this->templateData,
            'tenant_id' => $this->tenantId,
        ];
    }
}