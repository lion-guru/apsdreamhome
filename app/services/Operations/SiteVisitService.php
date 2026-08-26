<?php

namespace App\Services\Operations;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

/**
 * Site Visit Scheduler Service
 * Manage property visits, appointments, and calendar
 */
class SiteVisitService
{
    use ServiceTenantTrait;

    private $database;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Site visits table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Visit checklists
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Visit reminders
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Site availability calendar
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Visit analytics
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Schedule a site visit
     */
    public function scheduleVisit(array $data): array
    {
        try {
            // Check if slot is available
            if (!$this->isSlotAvailable($data['property_id'], $data['visit_date'], $data['visit_time'])) {
                return ['success' => false, 'error' => 'Selected time slot is not available'];
            }

            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ', tenant_id' : '';
            $tenantVal = $tid > 1 ? ', ?' : '';
            
            $sql = "INSERT INTO site_visits 
                (property_id, lead_id, user_id, visitor_name, visitor_phone, visitor_email,
                 visit_date, visit_time, duration_minutes, visit_type, assigned_to,
                 pickup_required, pickup_location, notes{$tenantCol}) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?{$tenantVal})";
            
            $params = [
                $data['property_id'],
                $data['lead_id'] ?? null,
                $data['user_id'] ?? null,
                $data['visitor_name'],
                $data['visitor_phone'],
                $data['visitor_email'] ?? null,
                $data['visit_date'],
                $data['visit_time'],
                $data['duration'] ?? 60,
                $data['visit_type'] ?? 'site_visit',
                $data['assigned_to'] ?? null,
                $data['pickup_required'] ?? 0,
                $data['pickup_location'] ?? null,
                $data['notes'] ?? null
            ];
            if ($tid > 1) {
                $params[] = $tid;
            }
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute($params);
            
            $visitId = $this->database->lastInsertId();
            
            // Create default checklist
            $this->createDefaultChecklist($visitId);
            
            // Schedule reminders
            $this->scheduleReminders($visitId, $data['visit_date'], $data['visit_time'], $data['visitor_phone'], $data['visitor_email']);
            
            // Send confirmation
            $this->sendConfirmation($visitId, $data);
            
            return [
                'success' => true,
                'visit_id' => $visitId,
                'message' => 'Visit scheduled successfully'
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Check if time slot is available
     */
    public function isSlotAvailable(int $propertyId, string $date, string $time): bool
    {
        $tid = $this->tenantId();
        // Check availability setting
        $availSql = "SELECT * FROM site_availability 
            WHERE property_id = ? AND available_date = ? 
            AND start_time <= ? AND end_time > ? AND is_blocked = 0" . ($tid > 1 ? " AND tenant_id = ?" : "");
        
        $availParams = [$propertyId, $date, $time, $time];
        if ($tid > 1) {
            $availParams[] = $tid;
        }
        
        $availStmt = $this->database->prepare($availSql);
        $availStmt->execute($availParams);
        $availability = $availStmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$availability) {
            // No specific availability set - allow if within business hours
            $hour = (int)substr($time, 0, 2);
            if ($hour < 9 || $hour >= 18) {
                return false;
            }
        }
        
        // Check existing bookings
        $bookingSql = "SELECT COUNT(*) as count FROM site_visits 
            WHERE property_id = ? AND visit_date = ? AND visit_time = ? 
            AND status NOT IN ('cancelled', 'no_show')" . ($tid > 1 ? " AND tenant_id = ?" : "");
        
        $bookingParams = [$propertyId, $date, $time];
        if ($tid > 1) {
            $bookingParams[] = $tid;
        }
        
        $bookingStmt = $this->database->prepare($bookingSql);
        $bookingStmt->execute($bookingParams);
        $existingBookings = $bookingStmt->fetch(\PDO::FETCH_ASSOC)['count'];
        
        $maxVisits = $availability['max_visits_per_slot'] ?? 1;
        
        return $existingBookings < $maxVisits;
    }
    
    /**
     * Get available slots
     */
    public function getAvailableSlots(int $propertyId, string $date): array
    {
        $slots = [];
        $tid = $this->tenantId();
        $businessHours = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00'];
        
        // Get availability for date
        $availSql = "SELECT * FROM site_availability 
            WHERE property_id = ? AND available_date = ? AND is_blocked = 0" . ($tid > 1 ? " AND tenant_id = ?" : "");
        
        $availParams = [$propertyId, $date];
        if ($tid > 1) {
            $availParams[] = $tid;
        }
        
        $availStmt = $this->database->prepare($availSql);
        $availStmt->execute($availParams);
        $availability = $availStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (!empty($availability)) {
            // Use configured slots
            foreach ($availability as $slot) {
                $isAvailable = $this->isSlotAvailable($propertyId, $date, $slot['start_time']);
                $slots[] = [
                    'time' => $slot['start_time'],
                    'available' => $isAvailable,
                    'max_visits' => $slot['max_visits_per_slot']
                ];
            }
        } else {
            // Use default business hours
            foreach ($businessHours as $time) {
                $isAvailable = $this->isSlotAvailable($propertyId, $date, $time);
                $slots[] = [
                    'time' => $time,
                    'available' => $isAvailable,
                    'max_visits' => 1
                ];
            }
        }
        
        return $slots;
    }
    
    /**
     * Create default checklist
     */
    private function createDefaultChecklist(int $visitId): void
    {
        $tid = $this->tenantId();
        $items = [
            ['name' => 'Verify site location and accessibility', 'category' => 'site'],
            ['name' => 'Check plot boundaries and dimensions', 'category' => 'site'],
            ['name' => 'Verify water supply availability', 'category' => 'amenities'],
            ['name' => 'Check electricity connection', 'category' => 'amenities'],
            ['name' => 'Verify approach road condition', 'category' => 'site'],
            ['name' => 'Check legal documents', 'category' => 'documentation'],
            ['name' => 'Verify security arrangements', 'category' => 'security'],
        ];
        
        $tenantCol = $tid > 1 ? ', tenant_id' : '';
        $tenantVal = $tid > 1 ? ', ?' : '';
        $sql = "INSERT INTO visit_checklists (visit_id, item_name, category{$tenantCol}) VALUES (?, ?, ?{$tenantVal})";
        $stmt = $this->database->prepare($sql);
        
        foreach ($items as $item) {
            $params = [$visitId, $item['name'], $item['category']];
            if ($tid > 1) {
                $params[] = $tid;
            }
            $stmt->execute($params);
        }
    }
    
    /**
     * Schedule reminders
     */
    private function scheduleReminders(int $visitId, string $date, string $time, string $phone, ?string $email): void
    {
        $visitDateTime = strtotime($date . ' ' . $time);
        
        // 24 hours before
        $reminder24h = date('Y-m-d H:i:s', $visitDateTime - (24 * 60 * 60));
        $this->createReminder($visitId, 'sms', $reminder24h);
        
        if ($email) {
            $this->createReminder($visitId, 'email', $reminder24h);
        }
        
        // 2 hours before
        $reminder2h = date('Y-m-d H:i:s', $visitDateTime - (2 * 60 * 60));
        $this->createReminder($visitId, 'whatsapp', $reminder2h);
    }
    
    /**
     * Create reminder record
     */
    private function createReminder(int $visitId, string $type, string $time): void
    {
        try {
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ', tenant_id' : '';
            $tenantVal = $tid > 1 ? ', ?' : '';
            $sql = "INSERT INTO visit_reminders (visit_id, reminder_type, reminder_time{$tenantCol}) VALUES (?, ?, ?{$tenantVal})";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        if (!isset($sql)) return;
        $stmt = $this->database->prepare($sql);
        $params = [$visitId, $type, $time];
        if ($tid > 1) {
            $params[] = $tid;
        }
        $stmt->execute($params);
    }
    
    /**
     * Send confirmation
     */
    private function sendConfirmation(int $visitId, array $data): void
    {
        $tid = $this->tenantId();
        // Get property details
        $propSql = "SELECT title, address, location FROM properties WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $propParams = [$data['property_id']];
        if ($tid > 1) {
            $propParams[] = $tid;
        }
        $propStmt = $this->database->prepare($propSql);
        $propStmt->execute($propParams);
        $property = $propStmt->fetch(\PDO::FETCH_ASSOC);
        
        $message = "Your site visit for {$property['title']} is confirmed on {$data['visit_date']} at {$data['visit_time']}. ";
        $message .= "Location: {$property['address']}. Visit ID: #{$visitId}";
        
        // Send SMS
        if (!empty($data['visitor_phone'])) {
            // $this->communicationService->sendSMS($data['visitor_phone'], $message);
        }
        
        // Send WhatsApp
        if (!empty($data['visitor_phone'])) {
            // $this->communicationService->sendWhatsApp($data['visitor_phone'], $message);
        }
    }
    
    /**
     * Get visits for date range
     */
    public function getVisits(string $dateFrom, string $dateTo, ?int $assignedTo = null, ?int $propertyId = null): array
    {
        $tid = $this->tenantId();
        $where = ['visit_date BETWEEN ? AND ?'];
        $params = [$dateFrom, $dateTo];
        
        if ($assignedTo) {
            $where[] = 'sv.assigned_to = ?';
            $params[] = $assignedTo;
        }
        
        if ($propertyId) {
            $where[] = 'sv.property_id = ?';
            $params[] = $propertyId;
        }
        
        if ($tid > 1) {
            $where[] = 'sv.tenant_id = ?';
            $params[] = $tid;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT sv.*, p.title as property_title, p.location as property_address,
            a.name as assigned_name, a.phone as assigned_phone
            FROM site_visits sv
            JOIN properties p ON sv.property_id = p.id
            LEFT JOIN users a ON sv.assigned_to = a.id
            WHERE {$whereClause}
            ORDER BY visit_date, visit_time";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get visit details
     */
    public function getVisit(int $visitId): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT sv.*, p.title as property_title, p.location as property_address,
            l.name as lead_name, l.email as lead_email, l.phone as lead_phone
            FROM site_visits sv
            JOIN properties p ON sv.property_id = p.id
            LEFT JOIN leads l ON sv.lead_id = l.id
            WHERE sv.id = ?" . ($tid > 1 ? " AND sv.tenant_id = ?" : "");
        
        $params = [$visitId];
        if ($tid > 1) {
            $params[] = $tid;
        }
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $visit = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$visit) {
            return null;
        }
        
        // Get checklist
        $checklistSql = "SELECT * FROM visit_checklists WHERE visit_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $checklistParams = [$visitId];
        if ($tid > 1) {
            $checklistParams[] = $tid;
        }
        $checklistStmt = $this->database->prepare($checklistSql);
        $checklistStmt->execute($checklistParams);
        $visit['checklist'] = $checklistStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return $visit;
    }
    
    /**
     * Update visit status
     */
    public function updateStatus(int $visitId, string $status, array $data = []): array
    {
        $validStatuses = ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'];
        
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'error' => 'Invalid status'];
        }
        
        $tid = $this->tenantId();
        $updates = ['status = ?'];
        $params = [$status];
        
        if (!empty($data['feedback'])) {
            $updates[] = 'feedback = ?';
            $params[] = $data['feedback'];
        }
        
        if (!empty($data['rating'])) {
            $updates[] = 'rating = ?';
            $params[] = $data['rating'];
        }
        
        if ($status === 'completed') {
            $updates[] = 'confirmation_sent = 1';
        }
        
        $params[] = $visitId;
        if ($tid > 1) {
            $params[] = $tid;
        }
        
        $sql = "UPDATE site_visits SET " . implode(', ', $updates) . " WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        // Update analytics
        $this->updateVisitAnalytics($visitId);
        
        return ['success' => true, 'message' => 'Status updated'];
    }
    
    /**
     * Update checklist item
     */
    public function updateChecklistItem(int $checklistId, bool $isCompleted, ?string $notes = null): array
    {
        $tid = $this->tenantId();
        $sql = "UPDATE visit_checklists SET is_completed = ?, notes = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$isCompleted ? 1 : 0, $notes, $checklistId];
        if ($tid > 1) {
            $params[] = $tid;
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        return ['success' => $stmt->rowCount() > 0];
    }
    
    /**
     * Reschedule visit
     */
    public function reschedule(int $visitId, string $newDate, string $newTime): array
    {
        $visit = $this->getVisit($visitId);
        
        if (!$visit) {
            return ['success' => false, 'error' => 'Visit not found'];
        }
        
        if (!$this->isSlotAvailable($visit['property_id'], $newDate, $newTime)) {
            return ['success' => false, 'error' => 'New time slot is not available'];
        }
        
        $tid = $this->tenantId();
        $sql = "UPDATE site_visits SET visit_date = ?, visit_time = ?, status = 'rescheduled' WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$newDate, $newTime, $visitId];
        if ($tid > 1) {
            $params[] = $tid;
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        // Update reminders
        $this->clearReminders($visitId);
        $this->scheduleReminders($visitId, $newDate, $newTime, $visit['visitor_phone'], $visit['visitor_email']);
        
        return ['success' => true, 'message' => 'Visit rescheduled'];
    }
    
    /**
     * Cancel visit
     */
    public function cancelVisit(int $visitId, string $reason): array
    {
        $tid = $this->tenantId();
        $sql = "UPDATE site_visits SET status = 'cancelled', notes = CONCAT(notes, '\\n\\nCancelled: ', ?) WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$reason, $visitId];
        if ($tid > 1) {
            $params[] = $tid;
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        // Clear reminders
        $this->clearReminders($visitId);
        
        return ['success' => true, 'message' => 'Visit cancelled'];
    }
    
    /**
     * Clear reminders
     */
    private function clearReminders(int $visitId): void
    {
        try {
            $tid = $this->tenantId();
            $sql = "DELETE FROM visit_reminders WHERE visit_id = ? AND is_sent = 0" . ($tid > 1 ? " AND tenant_id = ?" : "");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        if (!isset($sql)) return;
        $stmt = $this->database->prepare($sql);
        $params = [$visitId];
        if ($tid > 1) {
            $params[] = $tid;
        }
        $stmt->execute($params);
    }
    
    /**
     * Get calendar view
     */
    public function getCalendar(string $month, ?int $assignedTo = null): array
    {
        $yearMonth = $month . '-01';
        $startDate = date('Y-m-01', strtotime($yearMonth));
        $endDate = date('Y-m-t', strtotime($yearMonth));
        
        $visits = $this->getVisits($startDate, $endDate, $assignedTo);
        
        $calendar = [];
        foreach ($visits as $visit) {
            $date = $visit['visit_date'];
            if (!isset($calendar[$date])) {
                $calendar[$date] = [];
            }
            $calendar[$date][] = $visit;
        }
        
        return [
            'month' => $month,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'calendar' => $calendar,
            'total_visits' => count($visits),
            'by_status' => array_count_values(array_column($visits, 'status'))
        ];
    }
    
    /**
     * Update visit analytics
     */
    private function updateVisitAnalytics(int $visitId): void
    {
        $tid = $this->tenantId();
        $sql = "SELECT property_id, visit_date, status, rating FROM site_visits WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$visitId];
        if ($tid > 1) {
            $params[] = $tid;
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $visit = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$visit) return;
        
        // Update or insert analytics
        $tenantCol = $tid > 1 ? ', tenant_id' : '';
        $tenantVal = $tid > 1 ? ', ?' : '';
        $updateSql = "INSERT INTO visit_analytics 
            (property_id, visit_date, total_visits, completed_visits{$tenantCol}) 
            VALUES (?, ?, 1, ?{$tenantVal})
            ON DUPLICATE KEY UPDATE
            total_visits = total_visits + 1,
            completed_visits = completed_visits + VALUES(completed_visits)";
        
        $completed = ($visit['status'] === 'completed') ? 1 : 0;
        
        $updateParams = [$visit['property_id'], $visit['visit_date'], $completed];
        if ($tid > 1) {
            $updateParams[] = $tid;
        }
        
        $updateStmt = $this->database->prepare($updateSql);
        $updateStmt->execute($updateParams);
    }
    
    /**
     * Get site visit statistics
     */
    public function getStatistics(string $dateFrom, string $dateTo): array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        
        $sql = "SELECT 
            COUNT(*) as total_visits,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show,
            AVG(rating) as avg_rating,
            COUNT(DISTINCT property_id) as unique_properties,
            COUNT(DISTINCT assigned_to) as unique_agents
            FROM site_visits 
            WHERE visit_date BETWEEN ? AND ?{$tenantWhere}";
        
        $params = [$dateFrom, $dateTo];
        if ($tid > 1) {
            $params[] = $tid;
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Top properties
        $topSql = "SELECT p.title, COUNT(*) as visit_count
            FROM site_visits sv
            JOIN properties p ON sv.property_id = p.id
            WHERE sv.visit_date BETWEEN ? AND ?{$tenantWhere}
            GROUP BY sv.property_id
            ORDER BY visit_count DESC
            LIMIT 5";
        
        $topParams = [$dateFrom, $dateTo];
        if ($tid > 1) {
            $topParams[] = $tid;
        }
        $topStmt = $this->database->prepare($topSql);
        $topStmt->execute($topParams);
        $topProperties = $topStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'summary' => $stats,
            'top_properties' => $topProperties,
            'conversion_rate' => $stats['total_visits'] > 0 
                ? round(($stats['completed'] / $stats['total_visits']) * 100, 2) 
                : 0
        ];
    }
}
