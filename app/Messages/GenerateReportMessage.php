<?php

namespace App\Messages;

use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * GenerateReportMessage - Queue job for generating reports
 * Supports financial reports, sales reports, commission reports, etc.
 */
#[AsMessage('reports')]
class GenerateReportMessage
{
    public function __construct(
        public readonly string $type,
        public readonly array $filters = [],
        public readonly ?string $format = 'pdf',
        public readonly ?string $outputPath = null,
        public readonly ?int $tenantId = 1,
        public readonly ?int $userId = null
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'filters' => $this->filters,
            'format' => $this->format,
            'output_path' => $this->outputPath,
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
        ];
    }
}