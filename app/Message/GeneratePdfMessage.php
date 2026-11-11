<?php

namespace App\Message;

class GeneratePdfMessage
{
    public function __construct(
        public readonly string $template,
        public readonly array $data,
        public readonly string $outputPath,
        public readonly ?string $filename = null,
        public readonly int $priority = 0
    ) {}
}