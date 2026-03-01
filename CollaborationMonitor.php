<?php
/**
 * APS Dream Home - Real-Time Collaboration Monitor
 * Monitors Git changes and sends instant notifications
 */

class CollaborationMonitor {
    private $projectRoot;
    private $lastCommitHash;
    private $notificationWebhook;
    private $userName;
    private $taskFile;

    public function __construct($webhookUrl = null, $userName = 'User1') {
        $this->projectRoot = dirname(__DIR__, 2);
        $this->userName = $userName;
        $this->notificationWebhook = $webhookUrl ?: 'http://localhost:3000/notify';
        $this->taskFile = $this->projectRoot . '/collaboration_tasks.json';

        $this->initializeMonitoring();
    }

    private function initializeMonitoring() {
        // Get current commit hash
        $this->lastCommitHash = $this->getCurrentCommitHash();

        // Create task file if not exists
        if (!file_exists($this->taskFile)) {
            $this->createInitialTaskFile();
        }
    }

    private function getCurrentCommitHash() {
        $output = shell_exec("cd \"{$this->projectRoot}\" && git rev-parse HEAD 2>/dev/null");
        return trim($output);
    }

    private function createInitialTaskFile() {
        $initialTasks = [
            'timestamp' => date('Y-m-d H:i:s'),
            'users' => [
                'user1' => [
                    'name' => 'Local User',
                    'current_task' => 'Monitoring setup',
                    'status' => 'active',
                    'last_update' => date('Y-m-d H:i:s')
                ],
                'user2' => [
                    'name' => 'Remote User',
                    'current_task' => 'Setting up collaboration',
                    'status' => 'active',
                    'last_update' => date('Y-m-d H:i:s')
                ]
            ],
            'work_division' => [
                'frontend' => 'user1',
                'backend' => 'user2',
                'database' => 'user2',
                'testing' => 'user1',
                'deployment' => 'shared'
            ],
            'notifications' => []
        ];

        file_put_contents($this->taskFile, json_encode($initialTasks, JSON_PRETTY_PRINT));
    }

    public function checkForChanges() {
        $currentHash = $this->getCurrentCommitHash();

        if ($currentHash !== $this->lastCommitHash) {
            $changes = $this->getRecentChanges($this->lastCommitHash, $currentHash);
            $this->sendNotification('git_change', [
                'user' => $this->userName,
                'changes' => $changes,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            $this->lastCommitHash = $currentHash;
            return $changes;
        }

        return null;
    }

    private function getRecentChanges($oldHash, $newHash) {
        $cmd = "cd \"{$this->projectRoot}\" && git log --oneline --name-status {$oldHash}..{$newHash}";
        $output = shell_exec($cmd);

        $changes = [];
        $lines = explode("\n", trim($output));

        foreach ($lines as $line) {
            if (empty($line)) continue;

            if (preg_match('/^[A-Z]\s+(.+)$/', $line, $matches)) {
                $changes[] = [
                    'type' => substr($line, 0, 1),
                    'file' => $matches[1]
                ];
            }
        }

        return $changes;
    }

    public function updateCurrentTask($task, $status = 'active') {
        $tasks = json_decode(file_get_contents($this->taskFile), true);

        $tasks['users'][$this->userName]['current_task'] = $task;
        $tasks['users'][$this->userName]['status'] = $status;
        $tasks['users'][$this->userName]['last_update'] = date('Y-m-d H:i:s');

        file_put_contents($this->taskFile, json_encode($tasks, JSON_PRETTY_PRINT));

        $this->sendNotification('task_update', [
            'user' => $this->userName,
            'task' => $task,
            'status' => $status,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function getWorkDivision() {
        $tasks = json_decode(file_get_contents($this->taskFile), true);
        return $tasks['work_division'] ?? [];
    }

    public function assignTask($taskType, $user) {
        $tasks = json_decode(file_get_contents($this->taskFile), true);
        $tasks['work_division'][$taskType] = $user;

        file_put_contents($this->taskFile, json_encode($tasks, JSON_PRETTY_PRINT));

        $this->sendNotification('task_assignment', [
            'task_type' => $taskType,
            'assigned_to' => $user,
            'assigned_by' => $this->userName,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    private function sendNotification($type, $data) {
        $payload = [
            'type' => $type,
            'data' => $data,
            'source' => $this->userName
        ];

        // Log notification locally
        $this->logNotification($payload);

        // Send to webhook if configured
        if ($this->notificationWebhook) {
            $this->sendWebhookNotification($payload);
        }

        // Show desktop notification
        $this->showDesktopNotification($type, $data);
    }

    private function logNotification($payload) {
        $logFile = $this->projectRoot . '/collaboration_notifications.log';
        $logEntry = '[' . date('Y-m-d H:i:s') . '] ' . json_encode($payload) . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    private function sendWebhookNotification($payload) {
        $ch = curl_init($this->notificationWebhook);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private function showDesktopNotification($type, $data) {
        $title = "APS Dream Home - " . ucfirst(str_replace('_', ' ', $type));
        $message = $this->formatNotificationMessage($type, $data);

        // Windows notification (requires powershell)
        $cmd = "powershell -Command \"[Windows.UI.Notifications.ToastNotificationManager, Windows.UI.Notifications, ContentType = WindowsRuntime] | Out-Null; [Windows.UI.Notifications.ToastNotification, Windows.UI.Notifications, ContentType = WindowsRuntime] | Out-Null; [Windows.Data.Xml.Dom.XmlDocument, Windows.Data.Xml.Dom.XmlDocument, ContentType = WindowsRuntime] | Out-Null; \$template = @\"<toast><visual><binding template=\\\"ToastGeneric\\\"><text>$title</text><text>$message</text></binding></visual></toast>\"@; \$xml = New-Object Windows.Data.Xml.Dom.XmlDocument; \$xml.LoadXml(\$template); \$toast = New-Object Windows.UI.Notifications.ToastNotification \$xml; [Windows.UI.Notifications.ToastNotificationManager]::CreateToastNotifier('APS Dream Home').Show(\$toast);\"";

        shell_exec($cmd);
    }

    private function formatNotificationMessage($type, $data) {
        switch ($type) {
            case 'git_change':
                return "{$data['user']} made {$data['count']} changes";
            case 'task_update':
                return "{$data['user']} is now working on: {$data['task']}";
            case 'task_assignment':
                return "{$data['task_type']} assigned to {$data['assigned_to']}";
            default:
                return "Collaboration update from {$data['user']}";
        }
    }

    public function getCollaborationStatus() {
        $tasks = json_decode(file_get_contents($this->taskFile), true);

        return [
            'current_tasks' => $tasks['users'],
            'work_division' => $tasks['work_division'],
            'last_sync' => $this->getCurrentCommitHash(),
            'notifications_count' => count(file($this->projectRoot . '/collaboration_notifications.log') ?: [])
        ];
    }

    public function startRealTimeMonitoring() {
        echo "🔄 Starting Real-Time Collaboration Monitor...\n";
        echo "👤 User: {$this->userName}\n";
        echo "📁 Project: {$this->projectRoot}\n";
        echo "🔗 Webhook: {$this->notificationWebhook}\n\n";

        $this->updateCurrentTask('Real-time monitoring active');

        while (true) {
            $changes = $this->checkForChanges();

            if ($changes) {
                echo "📝 Changes detected: " . count($changes) . " files modified\n";
            }

            // Check for task file updates from other users
            $this->checkTaskFileUpdates();

            sleep(5); // Check every 5 seconds
        }
    }

    private function checkTaskFileUpdates() {
        // This would be enhanced with file watching
        // For now, just periodic checks
        static $lastTaskCheck = 0;

        if (time() - $lastTaskCheck > 10) { // Check every 10 seconds
            $tasks = json_decode(file_get_contents($this->taskFile), true);

            foreach ($tasks['users'] as $userId => $userData) {
                if ($userId !== $this->userName && strtotime($userData['last_update']) > $lastTaskCheck) {
                    echo "👥 {$userData['name']} updated: {$userData['current_task']}\n";
                }
            }

            $lastTaskCheck = time();
        }
    }
}

// Usage examples:
/*
// Initialize monitor
$monitor = new CollaborationMonitor('http://remote-system:3000/notify', 'user1');

// Update current task
$monitor->updateCurrentTask('Working on user authentication');

// Assign tasks
$monitor->assignTask('frontend', 'user1');
$monitor->assignTask('backend', 'user2');

// Start real-time monitoring
$monitor->startRealTimeMonitoring();

// Get collaboration status
$status = $monitor->getCollaborationStatus();
*/
