<?php

namespace App\MessageHandler;

use App\Messages\GeneratePdfMessage;
use App\Services\Document\PdfGeneratorService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'pdf')]
class GeneratePdfHandler
{
    private \App\Services\Document\PdfGeneratorService $pdfService;
    private LoggerInterface $logger;

    public function __construct(
        \App\Services\Document\PdfGeneratorService $pdfService,
        LoggerInterface $logger
    ) {
        $this->pdfService = $pdfService;
        $this->logger = $logger;
    }

    public function __invoke(GeneratePdfMessage $message): void
    {
        $this->logger->info('Processing PDF generation job', [
            'type' => $message->type,
            'reference_id' => $message->referenceId,
            'template' => $message->template,
        ]);

        try {
            $result = $this->pdfService->generate(
                $message->type,
                $message->referenceId,
                $message->data,
                $message->template,
                $message->outputPath
            );

            $this->logger->info('PDF generated successfully', [
                'type' => $message->type,
                'reference_id' => $message->referenceId,
                'output' => $result,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to generate PDF', [
                'type' => $message->type,
                'reference_id' => $message->referenceId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}