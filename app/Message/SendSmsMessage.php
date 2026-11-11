<?php

namespace App\Message;

class SendSmsMessage
{
    public function __construct(
        public readonly string $to,
        public readonly string $message,
        public readonly ?string $templateId = null,
        public readonly array $templateParams = [],
        public readonly int $priority = 0
    ) {}
}