<?php

namespace App\Services\Communication;

use App\Services\Communication\SmsService;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use \App\Traits\ServiceTenantTrait;

/**
 * SmsSenderService - Legacy wrapper for backward compatibility
 * 
 * @deprecated Use App\Services\Communication\SmsService instead
 * This class delegates to the unified SmsService
 */
class SmsSenderService
{
    use \App\Traits\ServiceTenantTrait;

    private $unifiedService;

    public function __construct()
    {
        $this->unifiedService = new SmsService();
    }

    /**
     * Send SMS - delegates to unified service
     */
    public function send($phone, $message)
    {
        try {
            return $this->unifiedService->send($phone, $message);
        } catch (\Throwable $e) {
            error_log("SmsSenderService: Send failed (legacy): " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send from queue - delegates to unified service
     */
    public function sendFromQueue($queueId)
    {
        try {
            return $this->unifiedService->sendFromQueue($queueId);
        } catch (\Throwable $e) {
            error_log("SmsSenderService: sendFromQueue failed (legacy): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process queue - delegates to unified service
     */
    public function processQueue($limit = 10)
    {
        try {
            return $this->unifiedService->processQueue($limit);
        } catch (\Throwable $e) {
            error_log("SmsSenderService: processQueue failed (legacy): " . $e->getMessage());
            return ['processed' => 0, 'sent' => 0, 'failed' => 0];
        }
    }

    /**
     * Queue SMS for async processing
     */
    public function queue($phone, $message, $priority = 'normal')
    {
        try {
            return $this->unifiedService->queue($phone, $message, $priority);
        } catch (\Throwable $e) {
            error_log("SmsSenderService: Queue failed (legacy): " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get queue stats
     */
    public function getQueueStats()
    {
        try {
            return $this->unifiedService->getQueueStats();
        } catch (\Throwable $e) {
            error_log("SmsSenderService: getQueueStats failed (legacy): " . $e->getMessage());
            return ['pending' => 0, 'sent' => 0, 'failed' => 0];
        }
    }

    /**
     * Get queue items
     */
    public function getQueueItems($status = null, $limit = 50, $offset = 0)
    {
        try {
            return $this->unifiedService->getQueueItems($status, $limit, $offset);
        } catch (\Throwable $e) {
            error_log("SmsSenderService: getQueueItems failed (legacy): " . $e->getMessage());
            return [];
        }
    }

    /**
     * @deprecated Use SmsService directly
     */
    public function getUnifiedService(): \App\Services\Communication\SmsService
    {
        return $this->unifiedService;
    }
}