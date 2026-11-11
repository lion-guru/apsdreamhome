<?php

namespace App\Messages;

use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * GeneratePdfMessage - Queue job for generating PDF documents
 * Supports receipts, contracts, certificates, reports, etc.
 */
#[AsMessage('pdf')]
class GeneratePdfMessage
{
    public function __construct(
        public readonly string $type,
        public readonly int $referenceId,
        public readonly array $data = [],
        public readonly ?string $template = null,
        public readonly ?string $outputPath = null,
        public readonly ?int $tenantId = 1,
        public readonly ?int $userId = null
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'reference_id' => $this->referenceId,
            'data' => $this->data,
            'template' => $this->template,
            'output_path' => $this->outputPath,
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
        ];
    }
}