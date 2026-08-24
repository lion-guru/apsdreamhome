<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Demand Letter Service
 * Handles demand letter templates ({{var}} substitution) and generation
 */
class DemandLetterService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function createDemandLetterTemplate(array $data): int
    {
        $tid = TenantContext::getId();

        $payload = [
            'template_name'        => trim($data['template_name'] ?? ''),
            'template_type'        => $data['template_type'] ?? 'general',
            'subject_template'     => $data['subject_template'] ?? '',
            'body_template'        => $data['body_template'] ?? '',
            'variables'            => $data['variables'] ?? [], // JSON array of expected {{vars}}
            'active'               => isset($data['active']) ? (int)$data['active'] : 1,
            'tenant_id'            => $tid,
        ];
        $this->db->insert('demand_letter_templates', $payload);
        return (int)$this->db->lastInsertId();
    }

    public function getDemandLetterTemplates(bool $activeOnly = false): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = $tid > 1 ? [$tid] : [];
        if ($activeOnly) {
            $where .= " AND active = 1";
        }
        return $this->db->fetchAll("SELECT * FROM demand_letter_templates $where ORDER BY template_name", $params) ?: [];
    }

    public function getDemandLetterTemplate(int $id): ?array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM demand_letter_templates WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchOne($sql, $tid > 1 ? [$id, $tid] : [$id]) ?: null;
    }

    public function updateDemandLetterTemplate(int $id, array $data): bool
    {
        $tid = TenantContext::getId();
        $allowed = ['template_name', 'template_type', 'subject_template', 'body_template', 'variables', 'active'];
        $updates = [];
        $params = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "$field = ?";
                $params[] = is_array($data[$field]) ? json_encode($data[$field]) : $data[$field];
            }
        }
        if (empty($updates)) return false;
        $params[] = $id;
        if ($tid > 1) $params[] = $tid;
        $sql = "UPDATE demand_letter_templates SET " . implode(', ', $updates) . " WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, $params);
    }

    public function deleteDemandLetterTemplate(int $id): bool
    {
        $tid = TenantContext::getId();
        $sql = "DELETE FROM demand_letter_templates WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, $tid > 1 ? [$id, $tid] : [$id]);
    }

    public function generateDemandLetter(int $bookingId, string $type): array
    {
        // type: 'payment_reminder', 'emi_due', 'penalty_notice', 'registry_reminder', 'nps_survey'
        $tid = TenantContext::getId();

        // Get template for this type
        $stmt = $this->db->fetchOne("SELECT * FROM demand_letter_templates WHERE template_type = ? AND active = 1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$type, $tid] : [$type]);
        if (!$stmt) {
            // Fallback to default template
            return $this->generateDefaultLetter($bookingId, $type);
        }

        // Get booking data for variable substitution
        $booking = $this->getBookingData($bookingId);
        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Substitute variables
        $subject = $this->substituteVariables($stmt['subject_template'], $booking);
        $body = $this->substituteVariables($stmt['body_template'], $booking);

        return [
            'success' => true,
            'subject' => $subject,
            'body'    => $body,
            'template_id' => $stmt['id'],
        ];
    }

    private function generateDefaultLetter(int $bookingId, string $type): array
    {
        $booking = $this->getBookingData($bookingId);
        if (!$booking) throw new Exception('Booking not found');

        $templates = [
            'payment_reminder' => [
                'subject' => 'Payment Reminder - Booking #{{booking_number}}',
                'body'    => 'Dear {{customer_name}},\n\nThis is a reminder for your upcoming payment of ₹{{amount_due}} due on {{due_date}} for booking {{booking_number}}.\n\nPlease ensure timely payment to avoid penalties.\n\nRegards,\nAPS Dream Home'
            ],
            'emi_due' => [
                'subject' => 'EMI Due Notice - Booking #{{booking_number}}',
                'body'    => 'Dear {{customer_name}},\n\nYour EMI of ₹{{emi_amount}} is due on {{due_date}} for booking {{booking_number}}.\n\nPlease make the payment to avoid late charges.\n\nRegards,\nAPS Dream Home'
            ],
            'penalty_notice' => [
                'subject' => 'Penalty Notice - Booking #{{booking_number}}',
                'body'    => 'Dear {{customer_name}},\n\nYour payment for booking {{booking_number}} is overdue. A penalty of ₹{{penalty_amount}} has been applied.\n\nPlease clear the dues immediately.\n\nRegards,\nAPS Dream Home'
            ],
        ];

        $template = $templates[$type] ?? $templates['payment_reminder'];
        return [
            'success' => true,
            'subject' => $this->substituteVariables($template['subject'], $booking),
            'body'    => $this->substituteVariables($template['body'], $booking),
            'template_id' => 0,
        ];
    }

    private function getBookingData(int $bookingId): ?array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT pb.*, c.name as customer_name, c.email, c.phone, pl.plot_number, pl.area_sqft, col.name as colony_name
                FROM plot_bookings pb
                JOIN customers c ON c.id = pb.customer_id
                JOIN plots pl ON pl.id = pb.plot_id
                JOIN colonies col ON col.id = pl.colony_id
                WHERE pb.id = ?" . ($tid > 1 ? " AND pb.tenant_id = ?" : "");
        return $this->db->fetchOne($sql, $tid > 1 ? [$bookingId, $tid] : [$bookingId]) ?: null;
    }

    private function substituteVariables(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        // Add common computed variables
        $template = str_replace('{{amount_due}}', $data['amount_due'] ?? '0', $template);
        $template = str_replace('{{emi_amount}}', $data['emi_amount'] ?? '0', $template);
        $template = str_replace('{{due_date}}', $data['due_date'] ?? date('d-m-Y'), $template);
        $template = str_replace('{{penalty_amount}}', $data['penalty_amount'] ?? '0', $template);
        return $template;
    }
}