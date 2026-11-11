<?php

namespace App\MessageHandler;

use App\Messages\SendNotificationMessage;
use App\Services\NotificationService;
use App\Core\Database\Database;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'notifications')]
class SendNotificationHandler
{
    private NotificationService $notificationService;
    private LoggerInterface $logger;

    public function __construct(
        NotificationService $notificationService,
        LoggerInterface $logger
    ) {
        $this->notificationService = $notificationService;
        $this->logger = $logger;
    }

    public function __invoke(SendNotificationMessage $message): void
    {
        $this->logger->info('Processing notification job', [
            'user_id' => $message->userId,
            'channels' => $message->channels,
            'template' => $message->templateKey,
        ]);

        try {
            $type = $message->templateKey ?? 'notification';
            $result = $this->notificationService->notify(
                $type,
                $message->message,
                $message->userId,
                $message->actionUrl,
                $message->title
            );

            $this->logger->info('Notification sent successfully', [
                'user_id' => $message->userId,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send notification', [
                'user_id' => $message->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}