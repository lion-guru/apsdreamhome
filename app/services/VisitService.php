<?php

namespace App\Services;

/**
 * Property Visit Scheduling Service
 */
class VisitService
{
    private $db;
    private $pdo;

    public function __construct($db = null)
    {
        if ($db === null) {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
        } elseif (is_object($db) && method_exists($db, 'getPdo')) {
            $db = $db->getPdo();
        }
        $this->db = $db;
        $this->pdo = $db;
    }

    public function getAvailableSlots(string $fromDate = null, string $toDate = null, ?int $propertyId = null, int $limit = 30): array
    {
        $sql = "SELECT * FROM visit_time_slots WHERE is_available = 1 AND current_bookings < max_bookings";
        $params = [];
        if ($fromDate) {
            $sql .= " AND date >= ?";
            $params[] = $fromDate;
        } else {
            $sql .= " AND date >= CURDATE()";
        }
        if ($toDate) {
            $sql .= " AND date <= ?";
            $params[] = $toDate;
        }
        if ($propertyId) {
            $sql .= " AND (property_id = ? OR property_id IS NULL)";
            $params[] = $propertyId;
        }
        $sql .= " ORDER BY date ASC, time_slot ASC LIMIT ?";
        $params[] = $limit;
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getSlotsByDate(string $date, ?int $propertyId = null): array
    {
        $sql = "SELECT * FROM visit_time_slots WHERE date = ? AND is_available = 1 AND current_bookings < max_bookings";
        $params = [$date];
        if ($propertyId) {
            $sql .= " AND (property_id = ? OR property_id IS NULL)";
            $params[] = $propertyId;
        }
        $sql .= " ORDER BY time_slot ASC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function bookVisit(array $data): array
    {
        $required = ['property_id', 'visit_date', 'visit_time', 'customer_name', 'customer_email', 'customer_phone'];
        foreach ($required as $r) {
            if (empty($data[$r])) return ['success' => false, 'error' => "Missing: $r"];
        }
        try {
            $this->pdo->beginTransaction();
            $slotStmt = $this->pdo->prepare("SELECT id, current_bookings, max_bookings FROM visit_time_slots WHERE date = ? AND time_slot = ? AND (property_id = ? OR property_id IS NULL) AND is_available = 1 FOR UPDATE");
            $slotStmt->execute([$data['visit_date'], $data['visit_time'], $data['property_id']]);
            $slot = $slotStmt->fetch();
            if (!$slot) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Time slot not available'];
            }
            if ($slot['current_bookings'] >= $slot['max_bookings']) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Time slot is fully booked'];
            }
            $visitStmt = $this->pdo->prepare("INSERT INTO property_visits
                (customer_id, property_id, customer_name, customer_email, customer_phone, visit_date, visit_time, visit_type, status, notes, tenant_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', ?, ?)");
            $visitStmt->execute([
                $data['customer_id'] ?? null,
                $data['property_id'],
                $data['customer_name'],
                $data['customer_email'],
                $data['customer_phone'],
                $data['visit_date'] . ' ' . $data['visit_time'],
                $data['visit_time'],
                $data['visit_type'] ?? 'site_visit',
                $data['notes'] ?? null,
                $this->getTenantId()
            ]);
            $visitId = (int)$this->pdo->lastInsertId();
            $this->pdo->prepare("UPDATE visit_time_slots SET current_bookings = current_bookings + 1 WHERE id = ? AND tenant_id = ?")->execute([$slot['id'], $this->getTenantId()]);
            $this->pdo->commit();

            // Send site visit confirmation email
            try {
                $customerId = $data['customer_id'] ?? 0;
                if ($customerId > 0) {
                    $emailSvc = new \App\Services\EmailTemplateService();
                    $propertyTitle = '';
                    try {
                        $pstmt = $this->pdo->prepare("SELECT title FROM user_properties WHERE id = ?");
                        $pstmt->execute([$data['property_id']]);
                        $prow = $pstmt->fetch();
                        $propertyTitle = $prow['title'] ?? '';
                    } catch (\Throwable $e) { error_log($e->getMessage()); }
                    $emailSvc->sendSiteVisitConfirmed($customerId, [
                        'property_name' => $propertyTitle,
                        'visit_date' => date('d M Y', strtotime($data['visit_date'])),
                        'visit_time' => date('h:i A', strtotime($data['visit_time'])),
                        'address' => '',
                        'what_to_bring' => 'Valid photo ID (Aadhaar/PAN), Property documents if available',
                    ]);
                }
            } catch (\Throwable $e) {
                error_log("[VisitService::bookVisit] email failed: " . $e->getMessage());
            }

            return ['success' => true, 'visit_id' => $visitId, 'visit_date' => $data['visit_date'], 'visit_time' => $data['visit_time']];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['success' => false, 'error' => 'Booking failed: ' . $e->getMessage()];
        }
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT v.*, p.name as property_title, p.address as property_address
            FROM property_visits v
            LEFT JOIN user_properties p ON p.id = v.property_id
            WHERE v.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT v.*, p.name as property_title
            FROM property_visits v
            LEFT JOIN user_properties p ON p.id = v.property_id
            WHERE v.customer_id = ?
            ORDER BY v.visit_date DESC");
        $stmt->execute([$userId]);
        try {
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getAll(string $status = '', string $date = '', int $limit = 100): array
    {
        $sql = "SELECT v.*, p.name as property_title, p.address as property_address,
                COALESCE(u.name, v.customer_name) as display_name
                FROM property_visits v
                LEFT JOIN user_properties p ON p.id = v.property_id
                LEFT JOIN users u ON u.id = v.customer_id";
        $params = [];
        $where = [];
        if ($status) { $where[] = "v.status = ?"; $params[] = $status; }
        if ($date) { $where[] = "DATE(v.visit_date) = ?"; $params[] = $date; }
        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY v.visit_date ASC LIMIT ?";
        $params[] = $limit;
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function updateStatus(int $id, string $status, ?string $notes = null): bool
    {
        $sql = "UPDATE property_visits SET status = ?";
        $params = [$status];
        if ($notes !== null) { $sql .= ", notes = ?"; $params[] = $notes; }
        $sql .= " WHERE id = ? AND tenant_id = ?";
        $params[] = $id;
        $params[] = $this->getTenantId();
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function cancel(int $id, string $reason): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE property_visits SET status = 'cancelled', cancellation_reason = ? WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$reason, $id, $this->getTenantId()]);
            $this->pdo->prepare("UPDATE visit_time_slots SET current_bookings = GREATEST(0, current_bookings - 1) WHERE date = DATE((SELECT visit_date FROM property_visits WHERE id = ?)) AND time_slot = (SELECT visit_time FROM property_visits WHERE id = ?)")->execute([$id, $id]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function reschedule(int $id, string $newDate, string $newTime): array
    {
        try {
            $this->pdo->beginTransaction();
            $current = $this->getById($id);
            if (!$current) { $this->pdo->rollBack(); return ['success' => false, 'error' => 'Visit not found']; }
            $stmt = $this->pdo->prepare("SELECT id, current_bookings, max_bookings FROM visit_time_slots WHERE date = ? AND time_slot = ? AND is_available = 1 FOR UPDATE");
            $stmt->execute([$newDate, $newTime]);
            $slot = $stmt->fetch();
            if (!$slot) { $this->pdo->rollBack(); return ['success' => false, 'error' => 'New time not available']; }
            if ($slot['current_bookings'] >= $slot['max_bookings']) { $this->pdo->rollBack(); return ['success' => false, 'error' => 'New slot full']; }
            $this->pdo->prepare("UPDATE visit_time_slots SET current_bookings = GREATEST(0, current_bookings - 1) WHERE date = DATE(?) AND time_slot = ?")->execute([$current['visit_date'], $current['visit_time']]);
            $this->pdo->prepare("UPDATE property_visits SET visit_date = ?, visit_time = ?, status = 'rescheduled' WHERE id = ? AND tenant_id = ?")->execute([$newDate . ' ' . $newTime, $newTime, $id, $this->getTenantId()]);
            $this->pdo->prepare("UPDATE visit_time_slots SET current_bookings = current_bookings + 1 WHERE id = ? AND tenant_id = ?")->execute([$slot['id'], $this->getTenantId()]);
            $this->pdo->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['success' => false, 'error' => 'Reschedule failed: ' . $e->getMessage()];
        }
    }

    public function submitFeedback(int $visitId, array $data): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO visit_feedback (visit_id, user_id, rating, agent_rating, property_rating, would_recommend, comments, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $visitId,
            $data['user_id'] ?? null,
            $data['rating'],
            $data['agent_rating'] ?? null,
            $data['property_rating'] ?? null,
            !empty($data['would_recommend']) ? 1 : 0,
            $data['comments'] ?? null,
            $this->getTenantId()
        ]);
        $this->pdo->prepare("UPDATE property_visits SET feedback_rating = ?, feedback_comments = ? WHERE id = ? AND tenant_id = ?")->execute([$data['rating'], $data['comments'] ?? null, $visitId, $this->getTenantId()]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getStats(): array
    {
        $stats = [
            'total' => 0, 'scheduled' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0,
            'today' => 0, 'this_week' => 0, 'avg_rating' => 0, 'available_slots' => 0
        ];
        try {
            $stats['total'] = (int)$this->pdo->query("SELECT COUNT(*) FROM property_visits")->fetchColumn();
            foreach (['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'] as $s) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM property_visits WHERE status = ?");
                $stmt->execute([$s]);
                $stats[$s] = (int)$stmt->fetchColumn();
            }
            $stats['today'] = (int)$this->pdo->query("SELECT COUNT(*) FROM property_visits WHERE DATE(visit_date) = CURDATE()")->fetchColumn();
            $stats['this_week'] = (int)$this->pdo->query("SELECT COUNT(*) FROM property_visits WHERE YEARWEEK(visit_date) = YEARWEEK(CURDATE())")->fetchColumn();
            $stats['avg_rating'] = round((float)$this->pdo->query("SELECT AVG(feedback_rating) FROM property_visits WHERE feedback_rating IS NOT NULL")->fetchColumn(), 1);
            $stats['available_slots'] = (int)$this->pdo->query("SELECT COUNT(*) FROM visit_time_slots WHERE date >= CURDATE() AND is_available = 1 AND current_bookings < max_bookings")->fetchColumn();
        } catch (\Throwable $e) {
        // ignore
        error_log($e->getMessage());
        }
        return $stats;
    }

    private function getTenantId(): int
    {
        if (class_exists('\App\Core\Middleware\TenantContext')) {
            try {
                return \App\Core\Middleware\TenantContext::getId();
            } catch (\Throwable $e) {
                return 1;
            }
        }
        return 1;
    }
}
