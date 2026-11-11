<?php
namespace App\Services\Communication;

use \App\Traits\ServiceTenantTrait;

class QueueProcessorService
{
    use \App\Traits\ServiceTenantTrait;

    private $emailSender;
    private $smsService;

    public function __construct()
    {
        $this->emailSender = new EmailService();
        $this->smsService = new SmsService();
    }

    public function processEmailQueue($limit = 10)
    {
        return $this->emailSender->processQueue($limit);
    }

    public function processSmsQueue($limit = 10)
    {
        return $this->smsService->processQueue($limit);
    }

    public function processAll($emailLimit = 10, $smsLimit = 10)
    {
        return [
            'email' => $this->processEmailQueue($emailLimit),
            'sms' => $this->processSmsQueue($smsLimit),
        ];
    }

    public function getCombinedStats()
    {
        return [
            'email' => $this->emailSender->getQueueStats(),
            'sms' => $this->smsService->getQueueStats(),
        ];
    }
}
