<?php

namespace App\Handler;

use App\Message\GeneratePdfMessage;
use App\Core\Database\Database;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GeneratePdfHandler
{
    public function __invoke(GeneratePdfMessage $message): void
    {
        try {
            // Use existing PDF service
            $pdfService = new \App\Services\PDFService();
            
            $html = $this->renderTemplate($message->template, $message->data);
            
            $pdfService->generateFromHtml($html, $message->outputPath);
            
        } catch (\Throwable $e) {
            error_log("GeneratePdfHandler failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function renderTemplate(string $template, array $data): string
    {
        $templatePath = __DIR__ . "/../../app/views/pdf_templates/{$template}.php";
        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("PDF template not found: {$template}");
        }
        
        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}