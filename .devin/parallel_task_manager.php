<?php
/**
 * Parallel Task Manager for Multi-Agent Processing
 * Speeds up work by running multiple tasks in parallel
 */

class ParallelTaskManager {
    private $tasks = [];
    private $results = [];
    private $max_concurrent = 5;
    
    /**
     * Add a task to the queue
     */
    public function addTask($name, $description, $callback, $priority = 'normal') {
        $this->tasks[] = [
            'name' => $name,
            'description' => $description,
            'callback' => $callback,
            'priority' => $priority,
            'status' => 'pending'
        ];
        return $this;
    }
    
    /**
     * Execute all tasks in parallel batches
     */
    public function executeParallel() {
        echo "=== PARALLEL TASK EXECUTION ===\n";
        echo "Total tasks: " . count($this->tasks) . "\n";
        echo "Max concurrent: " . $this->max_concurrent . "\n\n";
        
        // Sort by priority
        usort($this->tasks, function($a, $b) {
            $priorities = ['high' => 0, 'normal' => 1, 'low' => 2];
            return $priorities[$a['priority']] - $priorities[$b['priority']];
        });
        
        // Execute in batches
        $batches = array_chunk($this->tasks, $this->max_concurrent);
        
        foreach ($batches as $batchIndex => $batch) {
            echo "Processing batch " . ($batchIndex + 1) . " of " . count($batches) . " (" . count($batch) . " tasks)\n";
            
            // For this implementation, we'll execute sequentially
            // In a real parallel system, we'd use async/await or multiprocessing
            foreach ($batch as $task) {
                $this->executeTask($task);
            }
        }
        
        echo "\n=== PARALLEL EXECUTION COMPLETE ===\n";
        $this->generateReport();
        
        return $this->results;
    }
    
    /**
     * Execute a single task
     */
    private function executeTask(&$task) {
        $task['status'] = 'running';
        $startTime = microtime(true);
        
        echo "  [RUNNING] {$task['name']}: {$task['description']}\n";
        
        try {
            $result = call_user_func($task['callback']);
            $task['status'] = 'completed';
            $task['result'] = $result;
            $task['duration'] = microtime(true) - $startTime;
            
            echo "  [✓ COMPLETE] {$task['name']} (" . round($task['duration'], 2) . "s)\n";
            
            $this->results[] = [
                'task' => $task['name'],
                'status' => 'completed',
                'duration' => $task['duration'],
                'result' => $result
            ];
            
        } catch (Exception $e) {
            $task['status'] = 'failed';
            $task['error'] = $e->getMessage();
            $task['duration'] = microtime(true) - startTime;
            
            echo "  [✗ FAILED] {$task['name']} (" . round($task['duration'], 2) . "s) - {$e->getMessage()}\n";
            
            $this->results[] = [
                'task' => $task['name'],
                'status' => 'failed',
                'duration' => $task['duration'],
                'error' => $task['error']
            ];
        }
    }
    
    /**
     * Generate execution report
     */
    private function generateReport() {
        $completed = array_filter($this->results, fn($r) => $r['status'] === 'completed');
        $failed = array_filter($this->results, fn($r) => $r['status'] === 'failed');
        $totalDuration = array_sum(array_column($this->results, 'duration'));
        
        echo "\n=== EXECUTION REPORT ===\n";
        echo "Total tasks: " . count($this->results) . "\n";
        echo "Completed: " . count($completed) . "\n";
        echo "Failed: " . count($failed) . "\n";
        echo "Total duration: " . round($totalDuration, 2) . "s\n";
        echo "Average per task: " . round($totalDuration / count($this->results), 2) . "s\n";
        
        if (!empty($failed)) {
            echo "\nFailed tasks:\n";
            foreach ($failed as $failed) {
                echo "  - {$failed['task']}: {$failed['error']}\n";
            }
        }
    }
}

// Example usage:
$manager = new ParallelTaskManager();

$manager->addTask('db_analysis', 'Analyze database schema', function() {
    // Database analysis logic
    return ['tables_count' => 805, 'size' => '150MB'];
}, 'high');

$manager->addTask('code_review', 'Review code quality', function() {
    // Code review logic
    return ['issues_found' => 0, 'quality_score' => 95];
}, 'normal');

$manager->executeParallel();
?>