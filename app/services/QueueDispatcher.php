<?php

namespace App\Services;

use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Bridge\Redis\Transport\Connection;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use App\Messages\SendNotificationMessage;
use App\Messages\SendEmailMessage;
use App\Messages\SendSmsMessage;
use App\Messages\GeneratePdfMessage;
use App\MessageHandler\SendNotificationHandler;
use App\MessageHandler\SendEmailHandler;
use App\MessageHandler\SendSmsHandler;
use App\MessageHandler\GeneratePdfHandler;

class QueueDispatcher
{
    private static ?MessageBus $bus = null;
    private static ?\Symfony\Component\Messenger\Bridge\Redis\Transport\Connection $transport = null;
    
    private static function getBus(): MessageBus
    {
        if (self::$bus !== null) {
            return self::$bus;
        }
        
        $config = require __DIR__ . '/../../config/messenger/messenger.php';
        $messengerConfig = $config['messenger'];
        
        $transportConfig = $messengerConfig['transports']['async'];
        $dsn = $transportConfig['dsn'];
        
        self::$transport = Connection::fromDsn($dsn, [
            'stream' => $transportConfig['options']['stream'] ?? [],
        ]);
        self::$transport->setup();
        
        // Get handlers from container
        $container = \App\Services\DependencyContainer::getInstance();
        
        $handlers = [
            SendNotificationMessage::class => [$container->get(SendNotificationHandler::class)],
            SendEmailMessage::class => [$container->get(SendEmailHandler::class)],
            SendSmsMessage::class => [$container->get(SendSmsHandler::class)],
            GeneratePdfMessage::class => [$container->get(GeneratePdfHandler::class)],
        ];
        
        $handlerLocator = new HandlersLocator($handlers);
        
        self::$bus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator($handlers)),
        ]);
        
        return self::$bus;
    }
    
    public static function dispatchNotification(
        int $userId,
        string $title,
        string $message,
        string $templateKey = 'info',
        array $data = [],
        ?string $actionUrl = null,
        int $priority = 0
    ): void {
        self::getBus()->dispatch(new SendNotificationMessage(
            userId: $userId,
            title: $title,
            message: $message,
            templateKey: $templateKey,
            data: $data,
            actionUrl: $actionUrl,
            priority: $priority
        ));
    }
    
    public static function dispatchEmail(
        string $to,
        string $subject,
        string $body,
        ?string $from = null,
        ?string $fromName = null,
        array $attachments = [],
        int $priority = 0
    ): void {
        self::getBus()->dispatch(new SendEmailMessage(
            to: $to,
            subject: $subject,
            body: $body,
            from: $from,
            fromName: $fromName,
            attachments: $attachments,
        ));
    }
    
    public static function dispatchSms(
        string $to,
        string $message,
        ?string $templateKey = null,
        array $templateData = []
    ): void {
        self::getBus()->dispatch(new SendSmsMessage(
            to: $to,
            body: $message,
            templateKey: $templateKey,
            templateData: $templateData
        ));
    }
    
    public static function dispatchPdf(
        string $type,
        int $referenceId,
        array $data = [],
        ?string $template = null,
        ?string $outputPath = null
    ): void {
        self::getBus()->dispatch(new GeneratePdfMessage(
            type: $type,
            referenceId: $referenceId,
            data: $data,
            template: $template,
            outputPath: $outputPath,
        ));
    }
    
    public static function getTransport(): \Symfony\Component\Messenger\Bridge\Redis\Transport\Connection
    {
        if (self::$transport === null) {
            self::getBus(); // Initialize transport
        }
        return self::$transport;
    }
}