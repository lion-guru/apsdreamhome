<?php

namespace App\Services\Document;

use App\Services\Pdf\PdfService;
use Psr\Log\LoggerInterface;

/**
 * PdfGeneratorService - Wrapper around PdfService to provide a simplified interface
 * for the GeneratePdfHandler and QueueDispatcher.
 */
class PdfGeneratorService
{
    private PdfService $pdfService;
    private LoggerInterface $logger;

    public function __construct(PdfService $pdfService, LoggerInterface $logger)
    {
        $this->pdfService = $pdfService;
        $this->logger = $logger;
    }

    /**
     * Generate a PDF based on the message parameters.
     * 
     * @param string $type The type of PDF (receipt, invoice, agreement, report, brochure, payslip, possession, form16)
     * @param int $referenceId The reference ID (booking ID, report ID, etc.)
     * @param array $data Additional data for the PDF
     * @param string|null $template The template name (maps to PDF type)
     * @param string|null $outputPath Optional output path
     * @return array Result with success, data, or error
     */
    public function generate(string $type, int $referenceId, array $data = [], ?string $template = null, ?string $outputPath = null): array
    {
        // Map template to PDF type if provided
        $type = $template ?: $type;
        
        $this->logger->info('Generating PDF', [
            'type' => $type,
            'reference_id' => $referenceId,
            'template' => $template,
        ]);

        try {
            // Use the PdfService's generate method
            $result = $this->pdfService->generate($type, $referenceId, $data ?: []);
            
            if (($result['success'] ?? false) && $outputPath) {
                // If output path specified, copy/move the generated PDF
                $generatedPath = $result['data']['path'] ?? null;
                if ($generatedPath && is_file($generatedPath)) {
                    $dir = dirname($outputPath);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0775, true);
                    }
                    copy($generatedPath, $outputPath);
                    $result['data']['path'] = $outputPath;
                    $result['data']['url'] = $this->getUrlForOutput($outputPath);
                }
            }
            
            return $result;
            
        } catch (\Throwable $e) {
            $this->logger->error('PDF generation failed', [
                'type' => $type,
                'reference_id' => $referenceId,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Generate a receipt PDF for a booking
     */
    public function generateReceipt(int $bookingId, array $data = []): array
    {
        return $this->generate('receipt', $bookingId, $data);
    }

    /**
     * Generate an invoice PDF for a booking
     */
    public function generateInvoice(int $bookingId, array $data = []): array
    {
        return $this->generate('invoice', $bookingId, $data);
    }

    /**
     * Generate an agreement PDF for a booking
     */
    public function generateAgreement(int $bookingId, array $data = []): array
    {
        return $this->generate('agreement', $bookingId, $data);
    }

    /**
     * Generate a report PDF
     */
    public function generateReport(int $reportId, array $data = []): array
    {
        return $this->generate('report', $reportId, $data);
    }

    /**
     * Generate a brochure PDF
     */
    public function generateBrochure(int $propertyId, array $data = []): array
    {
        return $this->generate('brochure', $data);
    }

    /**
     * Generate a payslip PDF
     */
    public function generatePayslip(int $payslipId, array $data = []): array
    {
        return $this->generate('payslip', $payslipId, $data);
    }

    /**
     * Generate a possession certificate PDF
     */
    public function generatePossession(int $bookingId, array $data = []): array
    {
        return $this->generate('possession', $bookingId, $data);
    }

    /**
     * Generate a Form 16 PDF
     */
    public function generateForm16(int $employeeId, int $financialYear, array $data = []): array
    {
        return $this->generate('form16', $employeeId, array_merge($data, ['financial_year' => $financialYear]));
    }

    /**
     * Get the URL for a generated PDF output path
     */
    private function getUrlForOutput(string $outputPath): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        $relativePath = str_replace(dirname(__DIR__, 3) . '/storage/', '', $outputPath);
        return rtrim(BASE_URL, '/') . '/storage/' . ltrim($relativePath, '/');
    }
}