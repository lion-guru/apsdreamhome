<?php

namespace App\MessageHandler;

use App\Messages\SendSmsMessage;
use App\Services\Communication\SmsService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'sms')]
class SendSmsHandler
{
    private SmsService $smsService;
    private LoggerInterface $logger;

    public function __construct(
        SmsService $smsService,
        LoggerInterface $logger
    ) {
        $this->smsService = $smsService;
        $this->logger = $logger;
    }

    public function __invoke(SendSmsMessage $message): void
    {
        $this->logger->info('Processing SMS job', [
            'to' => $message->to,
            'template' => $message->templateKey,
        ]);

        try {
            $result = $this->smsService->send(
                $message->to,
                $message->body
            );

            $this->logger->info('SMS sent successfully', [
                'to' => $message->to,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send SMS', [
                'to' => $message->to,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}