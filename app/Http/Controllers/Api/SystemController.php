<?php
namespace App\Http\Controllers\Api;

class SystemController extends BaseApiController
{
    public function health()
    {
        header("Content-Type: application/json");

        $status = [
            "status" => "healthy",
            "timestamp" => date("Y-m-d H:i:s"),
            "version" => "1.0.0",
            "database" => "connected",
            "server" => "running"
        ];

        echo json_encode($status);
    }

    /**
     * Public read-only AI degraded flag.
     * Returns {available: bool, degraded: bool, checked_at: iso-string}
     * Safe for guest widgets to call on every page load.
     * No auth, no secrets, no internal hostnames.
     */
    public function aiHealth()
    {
        header("Content-Type: application/json");
        $degraded = false;
        $checkedAt = null;
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT config_value, updated_at FROM service_configs WHERE service_name = 'ai_health' AND config_key = 'degraded' LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $degraded = ((string)$row['config_value'] === '1');
                $checkedAt = (string)($row['updated_at'] ?? '');
            }
        } catch (\Throwable $e) {
            $degraded = true; // fail closed: mark degraded if any doubt
        }

        echo json_encode([
            'available'  => !$degraded,
            'degraded'   => (bool)$degraded,
            'checked_at' => $checkedAt,
        ]);
    }
}
