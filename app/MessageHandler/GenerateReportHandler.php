<?php

namespace App\MessageHandler;

use App\Messages\GenerateReportMessage;
use App\Services\Report\ReportGeneratorService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'reports')]
class GenerateReportHandler
{
    private ReportGeneratorService $reportService;
    private LoggerInterface $logger;

    public function __construct(
        ReportGeneratorService $reportService,
        LoggerInterface $logger
    ) {
        $this->reportService = $reportService;
        $this->logger = $logger;
    }

    public function __invoke(GenerateReportMessage $message): void
    {
        $this->logger->info('Processing report generation job', [
            'type' => $message->type,
            'format' => $message->format,
        ]);

        try {
            $result = $this->reportService->generate(
                $message->type,
                $message->filters,
                $message->format,
                $message->outputPath
            );

            $this->logger->info('Report generated successfully', [
                'type' => $message->type,
                'output' => $result,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to generate report', [
                'type' => $message->type,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}