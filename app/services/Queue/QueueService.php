<?php

namespace App\Services\Queue;

use App\Core\Database\Database;

/**
 * Queue Service
 * Background job processing with database queue
 */
class QueueService
{
    private $database;
    private $defaultQueue;
    private $tenantId;
    
    public function __construct(string $defaultQueue = 'default')
    {
        $this->database = Database::getInstance();
        $this->defaultQueue = $defaultQueue;
        $this->tenantId = (int)(\App\Core\Middleware\TenantContext::getId() ?? 0);
        $this->ensureTablesExist();
    }
    
    private function getTenantId(): int
    {
        return $this->tenantId;
    }
    
    private function tenantFilter(): string
    {
        return $this->tenantId > 1 ? " AND tenant_id = " . $this->tenantId : "";
    }
    
    private function tenantFilterParams(): array
    {
        return $this->tenantId > 1 ? [$this->tenantId] : [];
    }
    
    /**
     * Ensure queue tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Jobs table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Failed jobs table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Job batches (for batch processing)
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Batch jobs
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Queue workers table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Push job to queue
     */
    public function push(string $jobClass, array $data = [], ?string $queue = null, int $delay = 0): int
    {
        $queue = $queue ?? $this->defaultQueue;
        $availableAt = date('Y-m-d H:i:s', time() + $delay);
        $tid = $this->getTenantId();
        
        $sql = "INSERT INTO queue_jobs 
            (queue, job_class, job_data, available_at, tenant_id)
            VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            $queue,
            $jobClass,
            json_encode($data),
            $availableAt,
            $tid
        ]);
        
        return $this->database->lastInsertId();
    }
    
    /**
     * Push multiple jobs
     */
    public function pushBulk(array $jobs, ?string $queue = null): int
    {
        $queue = $queue ?? $this->defaultQueue;
        $count = 0;
        
        foreach ($jobs as $job) {
            $this->push($job['class'], $job['data'] ?? [], $queue, $job['delay'] ?? 0);
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Pop next available job
     */
    public function pop(?string $queue = null): ?array
    {
        $queue = $queue ?? $this->defaultQueue;
        $tid = $this->getTenantId();
        
        // Find and reserve next available job
        $sql = "SELECT * FROM queue_jobs 
            WHERE queue = ? 
            AND reserved_at IS NULL 
            AND available_at <= NOW()
            AND attempts < max_attempts";
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
        }
        $sql .= " ORDER BY id ASC LIMIT 1";
        
        $params = [$queue];
        if ($tid > 1) $params[] = $tid;
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $job = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$job) {
            return null;
        }
        
        // Reserve the job
        $reserveSql = "UPDATE queue_jobs SET 
            reserved_at = NOW(),
            attempts = attempts + 1
            WHERE id = ? AND reserved_at IS NULL";
        if ($tid > 1) {
            $reserveSql .= " AND tenant_id = ?";
        }
        
        $reserveStmt = $this->database->prepare($reserveSql);
        $reserveParams = [$job['id']];
        if ($tid > 1) $reserveParams[] = $tid;
        $reserveStmt->execute($reserveParams);
        
        if ($reserveStmt->rowCount() === 0) {
            // Job was taken by another worker
            return null;
        }
        
        return $job;
    }
    
    /**
     * Mark job as completed
     */
    public function complete(int $jobId): bool
    {
        $tid = $this->getTenantId();
        $sql = "DELETE FROM queue_jobs WHERE id = ?";
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
        }
        $stmt = $this->database->prepare($sql);
        $params = [$jobId];
        if ($tid > 1) $params[] = $tid;
        return $stmt->execute($params);
    }
    
    /**
     * Mark job as failed
     */
    public function fail(int $jobId, string $exception, ?string $queue = null): bool
    {
        $tid = $this->getTenantId();
        
        // Get job data
        $sql = "SELECT * FROM queue_jobs WHERE id = ?";
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
        }
        $stmt = $this->database->prepare($sql);
        $params = [$jobId];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        $job = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$job) {
            return false;
        }
        
        // Add to failed jobs
        $failSql = "INSERT INTO failed_jobs 
            (queue, job_class, job_data, exception, tenant_id)
            VALUES (?, ?, ?, ?, ?)";
        
        $failStmt = $this->database->prepare($failSql);
        $failStmt->execute([
            $job['queue'],
            $job['job_class'],
            $job['job_data'],
            $exception,
            $tid
        ]);
        
        // Remove from queue
        $deleteSql = "DELETE FROM queue_jobs WHERE id = ?";
        if ($tid > 1) {
            $deleteSql .= " AND tenant_id = ?";
        }
        $deleteStmt = $this->database->prepare($deleteSql);
        $deleteParams = [$jobId];
        if ($tid > 1) $deleteParams[] = $tid;
        return $deleteStmt->execute($deleteParams);
    }
    
    /**
     * Release job back to queue (for retry)
     */
    public function release(int $jobId, int $delay = 0): bool
    {
        $availableAt = date('Y-m-d H:i:s', time() + $delay);
        $tid = $this->getTenantId();
        
        $sql = "UPDATE queue_jobs SET 
            reserved_at = NULL,
            available_at = ?
            WHERE id = ?";
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
        }
        
        $stmt = $this->database->prepare($sql);
        $params = [$availableAt, $jobId];
        if ($tid > 1) $params[] = $tid;
        return $stmt->execute($params);
    }
    
    /**
     * Retry failed job
     */
    public function retryFailed(int $failedJobId): bool
    {
        $tid = $this->getTenantId();
        
        // Get failed job
        $sql = "SELECT * FROM failed_jobs WHERE id = ?";
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
        }
        $stmt = $this->database->prepare($sql);
        $params = [$failedJobId];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        $job = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$job) {
            return false;
        }
        
        // Push back to queue
        $this->push($job['job_class'], json_decode($job['job_data'], true), $job['queue']);
        
        // Remove from failed
        $deleteSql = "DELETE FROM failed_jobs WHERE id = ?";
        if ($tid > 1) {
            $deleteSql .= " AND tenant_id = ?";
        }
        $deleteStmt = $this->database->prepare($deleteSql);
        $deleteParams = [$failedJobId];
        if ($tid > 1) $deleteParams[] = $tid;
        return $deleteStmt->execute($deleteParams);
    }
    
    /**
     * Get queue size
     */
    public function size(?string $queue = null): int
    {
        $queue = $queue ?? $this->defaultQueue;
        $tid = $this->getTenantId();
        
        $sql = "SELECT COUNT(*) FROM queue_jobs 
            WHERE queue = ? 
            AND reserved_at IS NULL 
            AND available_at <= NOW()";
        $params = [$queue];
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tid;
        }
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Get queue statistics
     */
    public function getStats(): array
    {
        $tid = $this->getTenantId();
        
        $sql = "SELECT 
            queue,
            COUNT(*) as total,
            SUM(CASE WHEN reserved_at IS NULL AND available_at <= NOW() THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN reserved_at IS NOT NULL THEN 1 ELSE 0 END) as reserved,
            SUM(CASE WHEN available_at > NOW() THEN 1 ELSE 0 END) as delayed
            FROM queue_jobs";
        $params = [];
        if ($tid > 1) {
            $sql .= " WHERE tenant_id = ?";
            $params[] = $tid;
        }
        $sql .= " GROUP BY queue";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        return [
            'queues' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'failed' => $this->getFailedCount()
        ];
    }
    
    /**
     * Get failed jobs count
     */
    private function getFailedCount(): int
    {
        $tid = $this->getTenantId();
        $sql = "SELECT COUNT(*) FROM failed_jobs";
        $params = [];
        if ($tid > 1) {
            $sql .= " WHERE tenant_id = ?";
            $params[] = $tid;
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Clear queue
     */
    public function clear(?string $queue = null): int
    {
        $queue = $queue ?? $this->defaultQueue;
        $tid = $this->getTenantId();
        
        $sql = "DELETE FROM queue_jobs WHERE queue = ?";
        $params = [$queue];
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tid;
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Flush all failed jobs
     */
    public function flushFailed(): int
    {
        $tid = $this->getTenantId();
        
        $sql = "DELETE FROM failed_jobs";
        $params = [];
        if ($tid > 1) {
            $sql .= " WHERE tenant_id = ?";
            $params[] = $tid;
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    /**
     * Create job batch
     */
    public function createBatch(array $jobs, array $options = []): string
    {
        $batchId = uniqid('batch_');
        
        $sql = "INSERT INTO job_batches 
            (batch_id, total_jobs, pending_jobs, options)
            VALUES (?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            $batchId,
            count($jobs),
            count($jobs),
            json_encode($options)
        ]);
        
        // Add jobs to batch
        foreach ($jobs as $job) {
            $jobId = $this->push($job['class'], $job['data'] ?? [], $options['queue'] ?? null);
            
            $batchJobSql = "INSERT INTO batch_jobs (batch_id, job_id) VALUES (?, ?)";
            $batchJobStmt = $this->database->prepare($batchJobSql);
            $batchJobStmt->execute([$batchId, $jobId]);
        }
        
        return $batchId;
    }
    
    /**
     * Get batch status
     */
    public function getBatchStatus(string $batchId): ?array
    {
        $sql = "SELECT * FROM job_batches WHERE batch_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$batchId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Process job
     */
    public function processJob(array $job): bool
    {
        $class = $job['job_class'];
        $data = json_decode($job['job_data'], true);
        
        try {
            if (class_exists($class)) {
                $instance = new $class();
                if (method_exists($instance, 'handle')) {
                    $instance->handle($data);
                }
            }
            
            $this->complete($job['id']);
            return true;
            
        } catch (\Exception $e) {
            // Check if should retry
            if ($job['attempts'] < $job['max_attempts']) {
                $this->release($job['id'], 60); // Retry after 60 seconds
            } else {
                $this->fail($job['id'], $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Run worker (to be called by cron or supervisor)
     */
    public function work(?string $queue = null, int $sleep = 3, int $tries = 0): void
    {
        $queue = $queue ?? $this->defaultQueue;
        $workerId = getmypid() . '_' . uniqid();
        
        // Register worker
        $this->registerWorker($workerId, $queue);
        
        $processed = 0;
        
        while (true) {
            $job = $this->pop($queue);
            
            if ($job) {
                $this->processJob($job);
                $processed++;
                
                // Update worker stats
                $this->updateWorker($workerId, $processed);
                
                // Check if should exit
                if ($tries > 0 && $processed >= $tries) {
                    break;
                }
            } else {
                // No jobs, sleep
                sleep($sleep);
            }
        }
        
        // Unregister worker
        $this->unregisterWorker($workerId);
    }
    
    /**
     * Register worker
     */
    private function registerWorker(string $workerId, string $queue): void
    {
        $sql = "INSERT INTO queue_workers 
            (worker_id, queue, process_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
            started_at = NOW(),
            is_active = 1";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$workerId, $queue, getmypid()]);
    }
    
    /**
     * Update worker
     */
    private function updateWorker(string $workerId, int $jobsProcessed): void
    {
        $sql = "UPDATE queue_workers SET 
            jobs_processed = ?,
            last_seen_at = NOW()
            WHERE worker_id = ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$jobsProcessed, $workerId]);
    }
    
    /**
     * Unregister worker
     */
    private function unregisterWorker(string $workerId): void
    {
        $sql = "UPDATE queue_workers SET is_active = 0 WHERE worker_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$workerId]);
    }
    
    /**
     * Get active workers
     */
    public function getWorkers(): array
    {
        $sql = "SELECT * FROM queue_workers WHERE is_active = 1 
            AND last_seen_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ORDER BY started_at DESC";
        
        $stmt = $this->database->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
