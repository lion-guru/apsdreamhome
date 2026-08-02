<?php
namespace App\Services\Communication;

use \App\Traits\ServiceTenantTrait;

class QueueProcessorService
{
    use \App\Traits\ServiceTenantTrait;

    private $emailSender;
    private $smsSender;

    public function __construct()
    {
        $this->emailSender = new EmailSenderService();
        $this->smsSender = new SmsSenderService();
    }

    public function processEmailQueue($limit = 10)
    {
        return $this->emailSender->processQueue($limit);
    }

    public function processSmsQueue($limit = 10)
    {
        return $this->smsSender->processQueue($limit);
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
            'sms' => $this->smsSender->getQueueStats(),
        ];
    }
}
