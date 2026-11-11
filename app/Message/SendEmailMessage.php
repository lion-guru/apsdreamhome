<?php

namespace App\Message;

class SendEmailMessage
{
    public function __construct(
        public readonly string $to,
        public readonly string $subject,
        public readonly string $body,
        public readonly ?string $from = null,
        public readonly ?string $fromName = null,
        public readonly array $attachments = [],
        public readonly int $priority = 0
    ) {}
}