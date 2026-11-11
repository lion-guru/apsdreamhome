<?php

namespace App\MessageHandler;

use App\Messages\SendEmailMessage;
use App\Services\Communication\EmailService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'emails')]
class SendEmailHandler
{
    private EmailService $emailService;
    private LoggerInterface $logger;

    public function __construct(
        EmailService $emailService,
        LoggerInterface $logger
    ) {
        $this->emailService = $emailService;
        $this->logger = $logger;
    }

    public function __invoke(SendEmailMessage $message): void
    {
        $this->logger->info('Processing email job', [
            'to' => $message->to,
            'subject' => $message->subject,
            'template' => $message->templateKey,
        ]);

        try {
            $result = $this->emailService->send(
                $message->to,
                $message->subject,
                $message->body,
                $message->from,
                $message->fromName,
                $message->attachments
            );

            $this->logger->info('Email sent successfully', [
                'to' => $message->to,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send email', [
                'to' => $message->to,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}