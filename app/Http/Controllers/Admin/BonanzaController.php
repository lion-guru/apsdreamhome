<?php

namespace App\Http\Controllers\Admin;

/**
 * Festive bonanza / reward races: bookings-per-associate leaderboards.
 */
class BonanzaController extends AdminController
{
    /**
     * Bookings per associate inside a race window (shared by admin + portal).
     */
    public static function standings($db, string $start, string $end, int $limit = 10): array
    {
        try {
            return $db->fetchAll(
                "SELECT b.associate_id, COUNT(*) AS booked,
                        COALESCE(a.name, u.name, CONCAT('Associate #', b.associate_id)) AS name
                 FROM plot_bookings b
                 LEFT JOIN associates a ON a.id = b.associate_id
                 LEFT JOIN users u ON u.id = a.user_id
                 WHERE b.associate_id IS NOT NULL AND b.associate_id > 0
                   AND b.status != 'cancelled'
                   AND DATE(COALESCE(b.booking_date, b.created_at)) BETWEEN ? AND ?
                 GROUP BY b.associate_id ORDER BY booked DESC LIMIT " . max(1, $limit),
                [$start, $end]
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[BonanzaController::standings] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * GET /admin/sales/bonanza-races
     */
    public function index()
    {
        $this->requireAdmin();
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $rows = $this->db->fetchAll(
                "SELECT * FROM sales_races WHERE 1=1" . $tSql . " ORDER BY id DESC LIMIT 50",
                $tParams
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[BonanzaController::index] ' . $e->getMessage());
            $rows = [];
        }
        $this->render('admin/sales/bonanza_races', [
            'page_title' => 'Bonanza Races',
            'rows' => $rows,
        ]);
    }

    /**
     * POST /admin/sales/bonanza-races/store
     */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $name = trim((string)($_POST['race_name'] ?? ''));
        $start = trim((string)($_POST['start_date'] ?? ''));
        $end = trim((string)($_POST['end_date'] ?? ''));
        $target = (int)($_POST['target_bookings'] ?? 5);
        if ($name === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end) || $target <= 0) {
            $this->setFlash('error', 'Race name, valid window and a positive target are required.');
            return $this->redirect('/admin/sales/bonanza-races');
        }
        try {
            $this->db->execute(
                "INSERT INTO sales_races (tenant_id, race_name, start_date, end_date, target_bookings, prize_text, is_active, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, 1, ?)",
                [
                    (int)$this->tenantId(), mb_substr($name, 0, 150), $start, $end, $target,
                    mb_substr(trim((string)($_POST['prize_text'] ?? '')), 0, 255) ?: null,
                    $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null,
                ]
            );
            $this->setFlash('success', 'Race launched: ' . $name);
        } catch (\Throwable $e) {
            error_log('[BonanzaController::store] ' . $e->getMessage());
            $this->setFlash('error', 'Failed to launch race.');
        }
        return $this->redirect('/admin/sales/bonanza-races');
    }

    /**
     * GET /admin/sales/bonanza-races/{id}/leaderboard
     */
    public function leaderboard($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            $race = $this->db->fetchOne("SELECT * FROM sales_races WHERE id = ? LIMIT 1", [$id]);
        } catch (\Throwable $e) {
            error_log('[BonanzaController::leaderboard] ' . $e->getMessage());
            $race = null;
        }
        if (!$race) {
            $this->setFlash('error', 'Race not found');
            return $this->redirect('/admin/sales/bonanza-races');
        }
        $this->render('admin/sales/bonanza_leaderboard', [
            'page_title' => 'Leaderboard — ' . ($race['race_name'] ?? $id),
            'race' => $race,
            'standings' => self::standings($this->db, (string)$race['start_date'], (string)$race['end_date'], 10),
        ]);
    }
}
