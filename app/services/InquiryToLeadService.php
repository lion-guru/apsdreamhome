<?php
/**
 * InquiryToLeadService — Auto-wire inquiries into CRM leads
 * When an inquiry is created (property form, project inquiry, contact form, etc.),
 * this service creates/updates a lead in the `leads` table for CRM tracking.
 */

namespace App\Services;

use App\Core\Database;
use App\Core\Middleware\TenantContext;

use \App\Traits\ServiceTenantTrait;

class InquiryToLeadService
{
    use \App\Traits\ServiceTenantTrait;
    /**
     * Create or update a lead from inquiry data.
     * Uses phone number as dedup key — won't create duplicates.
     *
     * @param array $data Inquiry data: name, phone, email, message, type, property_id, project_id, etc.
     * @return int|null Lead ID
     */
    public static function wireFromInquiry(array $data): ?int
    {
        try {
            $db = Database::getInstance();
            $tid = class_exists('\App\Core\Middleware\TenantContext') ? (int)TenantContext::getId() : 1;
            $phone = trim($data['phone'] ?? '');
            $email = trim($data['email'] ?? '');
            $name = trim($data['name'] ?? '');

            // Need at least phone or email to dedup
            if (empty($phone) && empty($email)) return null;

            // Map inquiry type to CRM source
            $sourceMap = [
                'property_inquiry' => 'website',
                'project' => 'website',
                'contact' => 'website',
                'callback' => 'call_in',
                'whatsapp' => 'whatsapp',
                'walk_in' => 'walk_in',
                'referral' => 'referral',
                'associate' => 'associate',
            ];
            $inquiryType = $data['type'] ?? 'property_inquiry';
            $source = $sourceMap[$inquiryType] ?? 'website';

            // Check if lead already exists with same phone or email
            $existingLead = null;
            if (!empty($phone)) {
                $existingLead = $db->fetchOne("SELECT id FROM leads WHERE phone = ? AND deleted_at IS NULL" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at DESC LIMIT 1", $tid > 1 ? [$phone, $tid] : [$phone]);
            }
            if (!$existingLead && !empty($email)) {
                $existingLead = $db->fetchOne("SELECT id FROM leads WHERE email = ? AND deleted_at IS NULL" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at DESC LIMIT 1", $tid > 1 ? [$email, $tid] : [$email]);
            }

            if ($existingLead) {
                // Update existing lead with new inquiry info
                $leadId = $existingLead['id'];
                $updates = [];
                $params = [];

                if (!empty($name)) {
                    $updates[] = "name = COALESCE(NULLIF(?, ''), name)";
                    $params[] = $name;
                }
                if (!empty($email)) {
                    $updates[] = "email = COALESCE(NULLIF(?, ''), email)";
                    $params[] = $email;
                }
                if (!empty($data['property_id']) || !empty($data['property_interest'])) {
                    $interest = $data['property_interest'] ?? $data['property_name'] ?? '';
                    if ($interest) {
                        $updates[] = "property_interest = COALESCE(NULLIF(?, ''), property_interest)";
                        $params[] = $interest;
                    }
                }
                if (!empty($data['message'])) {
                    $updates[] = "notes = CONCAT(COALESCE(notes,''), CHAR(10), CHAR(10), '[', NOW(), '] Inquiry: ', ?)";
                    $params[] = mb_substr($data['message'], 0, 500);
                }
                $updates[] = "last_activity_date = NOW()";
                $updates[] = "updated_at = NOW()";

                if (!empty($updates)) {
                $params[] = $leadId;
                $db->query("UPDATE leads SET " . implode(', ', $updates) . " WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? $params : $params);
                }

                // Log activity
                try {
                $db->query(
                    "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at" . ($tid > 1 ? ", tenant_id" : "") . ") VALUES (?, 'inquiry', ?, ?, NOW()" . ($tid > 1 ? ", ?" : "") . ")",
                    $tid > 1 ? [$leadId, "New {$inquiryType} inquiry received", $data['created_by'] ?? null, $tid] : [$leadId, "New {$inquiryType} inquiry received", $data['created_by'] ?? null]
                );
                } catch (\Exception $e) { error_log($e->getMessage()); }

                return $leadId;
            }

            // Create new lead
            $leadNumber = 'CR-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $db->query(
                "INSERT INTO leads (lead_number, name, email, phone, source, status, priority, property_interest,
                    location_preference, notes, budget, budget_range, assigned_to, created_by, created_at, updated_at" . ($tid > 1 ? ", tenant_id" : "") . ")
                 VALUES (?, ?, ?, ?, ?, 'new', 'medium', ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()" . ($tid > 1 ? ", ?" : "") . ")",
                $tid > 1
                    ? [
                        $leadNumber, $name, $email ?: null, $phone, $source,
                        $data['property_interest'] ?? $data['property_name'] ?? null,
                        $data['location_preference'] ?? $data['location'] ?? null,
                        mb_substr($data['message'] ?? '', 0, 500),
                        $data['budget'] ?? null,
                        $data['budget_range'] ?? null,
                        $data['assigned_to'] ?? null,
                        $data['created_by'] ?? null,
                        $tid,
                    ]
                    : [
                        $leadNumber, $name, $email ?: null, $phone, $source,
                        $data['property_interest'] ?? $data['property_name'] ?? null,
                        $data['location_preference'] ?? $data['location'] ?? null,
                        mb_substr($data['message'] ?? '', 0, 500),
                        $data['budget'] ?? null,
                        $data['budget_range'] ?? null,
                        $data['assigned_to'] ?? null,
                        $data['created_by'] ?? null,
                    ]
            );
            $leadId = $db->lastInsertId();

            // Log creation activity
            try {
                $db->query(
                    "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at" . ($tid > 1 ? ", tenant_id" : "") . ") VALUES (?, 'created', ?, ?, NOW()" . ($tid > 1 ? ", ?" : "") . ")",
                    $tid > 1 ? [$leadId, "Lead auto-created from {$inquiryType} inquiry", $data['created_by'] ?? null, $tid] : [$leadId, "Lead auto-created from {$inquiryType} inquiry", $data['created_by'] ?? null]
                );
            } catch (\Exception $e) { error_log($e->getMessage()); }

            return $leadId;
        } catch (\Exception $e) {
            error_log('InquiryToLeadService::wireFromInquiry error: ' . $e->getMessage());
            return null;
        }
    }
}
