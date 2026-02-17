<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/includes/superadmin_helpers.php';

if (!isSuperAdmin()) {
    header('Location: login.php');
    exit();
}

/**
 * Log Viewer
 * Allows administrators to view and analyze log files
 */

require_once(__DIR__ . '/../includes/security_logger.php');
require_once(__DIR__ . '/../includes/middleware/rate_limit_middleware.php');

// Apply rate limiting
$rateLimitMiddleware->handle('admin');

class LogViewer {
    private $logPath;
    private $allowedTypes = ['security', 'error', 'access', 'debug'];
    private $pageSize = 50;

    public function __construct() {
        $this->logPath = __DIR__ . '/../logs';
    }

    /**
     * Get available log files
     */
    public function getLogFiles($type = null) {
        $files = [];

        if ($type) {
            if (!in_array($type, $this->allowedTypes)) {
                return [];
            }
            $typeDir = $this->logPath . '/' . $type;
            if (is_dir($typeDir)) {
                $files = glob($typeDir . '/*.log*');
            }
        } else {
            foreach ($this->allowedTypes as $logType) {
                $typeDir = $this->logPath . '/' . $logType;
                if (is_dir($typeDir)) {
                    $files = array_merge($files, glob($typeDir . '/*.log*'));
                }
            }
        }

        // Sort files by modification time
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return $files;
    }

    /**
     * Read log file contents with pagination
     */
    public function readLogFile($file, $page = 1) {
        if (!file_exists($file)) {
            return [
                'error' => 'File not found',
                'entries' => [],
                'total_pages' => 0,
                'current_page' => 1
            ];
        }

        $entries = [];
        $lines = [];

        // Handle compressed files
        if (strpos($file, '.gz') !== false) {
            $handle = gzopen($file, 'r');
            while (!gzeof($handle)) {
                $lines[] = gzgets($handle);
            }
            gzclose($handle);
        } else {
            $lines = file($file);
        }

        // Reverse array to show newest entries first
        $lines = array_reverse($lines);

        // Calculate pagination
        $totalEntries = count($lines);
        $totalPages = ceil($totalEntries / $this->pageSize);
        $page = max(1, min($page, $totalPages));
        $start = ($page - 1) * $this->pageSize;

        // Get entries for current page
        $pageLines = array_slice($lines, $start, $this->pageSize);

        foreach ($pageLines as $line) {
            if (trim($line) === '') continue;

            // Parse log entry
            $entry = $this->parseLogEntry($line);
            if ($entry) {
                $entries[] = $entry;
            }
        }

        return [
            'entries' => $entries,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'total_entries' => $totalEntries
        ];
    }

    /**
     * Parse a log entry line
     */
    private function parseLogEntry($line) {
        // Expected format: [timestamp] [level] [sapi] [IP: ip] [User: id] [ReqID: id] message context
        $pattern = '/// SECURITY: Removed potentially dangerous code(.*?)\] // SECURITY: Removed potentially dangerous code(.*?)\] // SECURITY: Removed potentially dangerous code(.*?)\] // SECURITY: Removed potentially dangerous codeIP: (.*?)\] // SECURITY: Removed potentially dangerous codeUser: (.*?)\] // SECURITY: Removed potentially dangerous codeReqID: (.*?)\] (.*?) ({.*})?/';

        if (preg_match($pattern, $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'level' => $matches[2],
                'sapi' => $matches[3],
                'ip' => $matches[4],
                'user' => $matches[5],
                'request_id' => $matches[6],
                'message' => $matches[7],
                'context' => isset($matches[8]) ? json_decode($matches[8], true) : null
            ];
        }

        return null;
    }

    /**
     * Search logs
     */
    public function searchLogs($query, $type = null, $startDate = null, $endDate = null) {
        $results = [];
        $files = $this->getLogFiles($type);

        foreach ($files as $file) {
            // Skip files outside date range
            $fileDate = $this->getDateFromFilename($file);
            if ($startDate && $fileDate < $startDate) continue;
            if ($endDate && $fileDate > $endDate) continue;

            $fileResults = $this->searchFile($file, $query);
            $results = array_merge($results, $fileResults);
        }

        return $results;
    }

    /**
     * Search within a single file
     */
    private function searchFile($file, $query) {
        $results = [];

        // Handle compressed files
        if (strpos($file, '.gz') !== false) {
            $handle = gzopen($file, 'r');
            while (!gzeof($handle)) {
                $line = gzgets($handle);
                if (stripos($line, $query) !== false) {
                    $entry = $this->parseLogEntry($line);
                    if ($entry) {
                        $results[] = $entry;
                    }
                }
            }
            gzclose($handle);
        } else {
            $handle = fopen($file, 'r');
            while (($line = fgets($handle)) !== false) {
                if (stripos($line, $query) !== false) {
                    $entry = $this->parseLogEntry($line);
                    if ($entry) {
                        $results[] = $entry;
                    }
                }
            }
            fclose($handle);
        }

        return $results;
    }

    /**
     * Get date from filename
     */
    private function getDateFromFilename($file) {
        $pattern = '/(\d{4}-\d{2}-\d{2})/';
        if (preg_match($pattern, basename($file), $matches)) {
            return $matches[1];
        }
        return null;
    }
}

// Initialize LogViewer
$logViewer = new LogViewer();

$log_file = __DIR__ . '/../includes/logs/admin_activity.log';
$log_entries = [];
if (file_exists($log_file)) {
    $log_entries = array_reverse(file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
}
// Filtering logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date = isset($_GET['date']) ? trim($_GET['date']) : '';
$action_filter = isset($_GET['action_type']) ? trim($_GET['action_type']) : '';
$user_filter = isset($_GET['user']) ? trim($_GET['user']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 30;
$action_types = [];
$usernames = [];
foreach ($log_entries as $entry) {
    $parts = explode("|", $entry, 3);
    $action = $parts[1] ?? '';
    $details = $parts[2] ?? '';
    if ($action !== '' && !in_array($action, $action_types)) {
        $action_types[] = $action;
    }
    // Try to extract a username/email from details (simple heuristic)
    if (preg_match('/(admin|user|email|by):?\s*([\w@.\-]+)/i', $details, $m)) {
        $uname = $m[2];
        if ($uname !== '' && !in_array($uname, $usernames)) {
            $usernames[] = $uname;
        }
    }
}
$filtered_entries = [];
foreach ($log_entries as $entry) {
    $parts = explode("|", $entry, 3);
    $timestamp = $parts[0] ?? '';
    $action = $parts[1] ?? '';
    $details = $parts[2] ?? '';
    $match = true;
    if ($search !== '' && stripos($entry, $search) === false) $match = false;
    if ($date !== '' && strpos($timestamp, $date) !== 0) $match = false;
    if ($action_filter !== '' && $action !== $action_filter) $match = false;
    if ($user_filter !== '' && stripos($details, $user_filter) === false) $match = false;
    if ($match) $filtered_entries[] = [$timestamp, $action, $details];
}
$total = count($filtered_entries);
$total_pages = ($total > 0) ? ceil($total / $per_page) : 1;
$start = ($page - 1) * $per_page;
$entries_to_show = array_slice($filtered_entries, $start, $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Activity Log Viewer</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; }
        .container { margin-top: 40px; }
        .log-table { font-size: 0.95rem; }
        .log-entry { font-family: monospace; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4">Admin Activity Log</h2>
    <form class="row g-3 mb-3" method="get">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Keyword (action, user, details)" value="<?= h($search) ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="date" class="form-control" value="<?= h($date) ?>">
        </div>
        <div class="col-md-3">
            <select name="action_type" class="form-select">
                <option value="">All Actions</option>
                <?php foreach ($action_types as $atype): ?>
                    <option value="<?= h($atype) ?>"<?= ($action_filter === $atype) ? ' selected' : '' ?>><?= h($atype) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="user" class="form-select">
                <option value="">All Users</option>
                <?php foreach ($usernames as $uname): ?>
                    <option value="<?= h($uname) ?>"<?= ($user_filter === $uname) ? ' selected' : '' ?>><?= h($uname) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-1">
            <a href="log_viewer.php" class="btn btn-secondary w-100">Reset</a>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-striped log-table">
            <thead class="table-dark">
                <tr><th>#</th><th>Timestamp</th><th>Action</th><th>Details</th></tr>
            </thead>
            <tbody>
            <?php foreach ($entries_to_show as $i => [$timestamp, $action, $details]): ?>
                <tr>
                    <td><?= (int)($start + $i + 1) ?></td>
                    <td class="log-entry"><?= h($timestamp) ?></td>
                    <td class="log-entry"><?= h($action) ?></td>
                    <td class="log-entry"><?= h($details) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($entries_to_show)): ?>
            <div class="alert alert-info">No matching log entries found.</div>
        <?php endif; ?>
    </div>
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Log pagination">
      <ul class="pagination justify-content-center mt-3">
        <li class="page-item<?= ($page <= 1) ? ' disabled' : '' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">Previous</a>
        </li>
        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
          <li class="page-item<?= ($p == $page) ? ' active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item<?= ($page >= $total_pages) ? ' disabled' : '' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>">Next</a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
