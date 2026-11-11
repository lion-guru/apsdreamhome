<?php

namespace App\Message;

class SendNotificationMessage
{
    public function __construct(
        public readonly int $userId,
        public readonly string $title,
        public readonly string $message,
        public readonly string $type = 'info',
        public readonly array $data = [],
        public readonly ?string $actionUrl = null,
        public readonly int $priority = 0
    ) {}
}