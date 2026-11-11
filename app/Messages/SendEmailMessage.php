<?php

namespace App\Messages;

use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * SendEmailMessage - Queue job for sending emails
 */
#[AsMessage('emails')]
class SendEmailMessage
{
    public function __construct(
        public readonly string $to,
        public readonly string $subject,
        public readonly string $body,
        public readonly ?string $from = null,
        public readonly ?string $fromName = null,
        public readonly array $attachments = [],
        public readonly ?string $templateKey = null,
        public readonly array $templateData = [],
        public readonly ?int $tenantId = 1
    ) {}

    public function toArray(): array
    {
        return [
            'to' => $this->to,
            'subject' => $this->subject,
            'body' => $this->body,
            'from' => $this->from,
            'from_name' => $this->fromName,
            'attachments' => $this->attachments,
            'template_key' => $this->templateKey,
            'template_data' => $this->templateData,
            'tenant_id' => $this->tenantId,
        ];
    }
}