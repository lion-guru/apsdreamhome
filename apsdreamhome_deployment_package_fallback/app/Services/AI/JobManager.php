<?php

namespace App\Services\AI;

class JobManager {
    private $db;
    private $aiManager;

    public function __construct($db = null, $aiManager = null) {
        $this->db = $db ?: \App\Core\App::database();
        $this->aiManager = $aiManager;
    }

    private function getAiManager() {
        if (!$this->aiManager) {
            require_once __DIR__ . '/AIManager.php';
            $this->aiManager = new AIManager($this->db);
        }
        return $this->aiManager;
    }

    /**
     * Add a task to the job queue
     */
    public function enqueue($agent_id, $task_type, $input_data, $workflow_id = null, $priority = 0, $scheduled_at = null) {
        // If no agent_id provided, find the best one automatically
        if ($agent_id === null || $agent_id === 0) {
            $agent_id = $this->getAiManager()->findBestAgentForTask($task_type);
        }

        $sql = "INSERT INTO ai_jobs (agent_id, workflow_id, task_type, input_data, priority, scheduled_at) VALUES (?, ?, ?, ?, ?, ?)";
        $input_json = json_encode($input_data);

        if ($this->db->execute($sql, [$agent_id, $workflow_id, $task_type, $input_json, $priority, $scheduled_at])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Get the next pending job from the queue
     */
    public function getNextJob() {
        $sql = "SELECT * FROM ai_jobs
                WHERE status = 'pending'
                AND (scheduled_at IS NULL OR scheduled_at <= CURRENT_TIMESTAMP)
                ORDER BY priority DESC, created_at ASC
                LIMIT 1";

        $job = $this->db->fetch($sql);

        if ($job) {
            // Mark as processing immediately to avoid double execution
            $this->updateJobStatus($job['id'], 'processing');
            return $job;
        }
        return null;
    }

    /**
     * Process a specific job
     */
    public function processJob($jobId) {
        $sql = "SELECT * FROM ai_jobs WHERE id = ?";
        $job = $this->db->fetch($sql, [$jobId]);

        if (!$job) return false;

        $this->getAiManager()->auditLog('job_processing', ['job_id' => $jobId, 'task_type' => $job['task_type']], 'info');

        try {
            $inputData = json_decode($job['input_data'], true);
            $result = $this->getAiManager()->executeTask(
                $job['agent_id'],
                $job['task_type'],
                $inputData,
                $job['workflow_id']
            );

            if ($result['status'] === 'success') {
                $this->updateJobStatus($jobId, 'completed');
                return true;
            } else {
                $this->handleJobFailure($jobId, $job['attempts'], $result['error'] ?? 'Unknown AI error');
                return false;
            }
        } catch (Exception $e) {
            $this->handleJobFailure($jobId, $job['attempts'], $e->getMessage());
            return false;
        }
    }

    private function updateJobStatus($id, $status) {
        $sql = "UPDATE ai_jobs SET status = ? WHERE id = ?";
        return $this->db->execute($sql, [$status, $id]);
    }

    public function handleJobFailure($id, $currentAttempts, $errorMessage) {
        $newAttempts = $currentAttempts + 1;
        $maxAttempts = 3;

        $status = ($newAttempts < $maxAttempts) ? 'pending' : 'failed';
        $sql = "UPDATE ai_jobs SET status = ?, attempts = ?, error_message = ? WHERE id = ?";
        return $this->db->execute($sql, [$status, $newAttempts, $errorMessage, $id]);
    }
}
?>
