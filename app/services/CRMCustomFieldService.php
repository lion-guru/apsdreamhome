<?php
/**
 * CRMCustomFieldService — Admin-configurable custom fields for leads
 */
namespace App\Services;

use App\Core\Database;

class CRMCustomFieldService
{
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllFields() {
        try {
            return $this->db->fetchAll("SELECT * FROM crm_custom_fields ORDER BY section ASC, order_index ASC") ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getActiveFields() {
        try {
            return $this->db->fetchAll("SELECT * FROM crm_custom_fields WHERE is_active = 1 ORDER BY section ASC, order_index ASC") ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getFieldById($id) {
        try {
            return $this->db->fetch("SELECT * FROM crm_custom_fields WHERE id = ?", [$id]);
        } catch (\Exception $e) { return null; }
    }

    public function createField($data) {
        try {
            $this->db->query(
                "INSERT INTO crm_custom_fields (field_name, field_label, field_type, options_json, is_required, is_searchable, section, order_index, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['field_name'], $data['field_label'], $data['field_type'] ?? 'text',
                    !empty($data['options']) ? json_encode($data['options']) : null,
                    $data['is_required'] ?? 0, $data['is_searchable'] ?? 1,
                    $data['section'] ?? 'general', $data['order_index'] ?? 0, $data['is_active'] ?? 1
                ]
            );
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function updateField($id, $data) {
        try {
            $fields = []; $params = [];
            foreach (['field_name','field_label','field_type','is_required','is_searchable','section','order_index','is_active'] as $f) {
                if (array_key_exists($f, $data)) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
            }
            if (isset($data['options'])) { $fields[] = "options_json = ?"; $params[] = json_encode($data['options']); }
            if (empty($fields)) return ['success' => false, 'error' => 'No fields'];
            $params[] = $id;
            $this->db->query("UPDATE crm_custom_fields SET " . implode(', ', $fields) . " WHERE id = ?", $params);
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function deleteField($id) {
        try {
            $this->db->query("DELETE FROM crm_lead_custom_values WHERE custom_field_id = ?", [$id]);
            $this->db->query("DELETE FROM crm_custom_fields WHERE id = ?", [$id]);
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function getLeadCustomValues($leadId) {
        try {
            $rows = $this->db->fetchAll(
                "SELECT cv.*, cf.field_name, cf.field_label, cf.field_type, cf.options_json
                 FROM crm_lead_custom_values cv
                 JOIN crm_custom_fields cf ON cf.id = cv.custom_field_id
                 WHERE cv.lead_id = ? ORDER BY cf.section ASC, cf.order_index ASC",
                [$leadId]
            ) ?: [];
            $result = [];
            foreach ($rows as $r) { $result[$r['field_name']] = $r; }
            return $result;
        } catch (\Exception $e) { return []; }
    }

    public function saveLeadCustomValues($leadId, $data) {
        try {
            $fields = $this->getActiveFields();
            foreach ($fields as $field) {
                $val = $data['cf_' . $field['field_name']] ?? null;
                if ($field['is_required'] && empty($val)) continue;
                $existing = $this->db->fetch(
                    "SELECT id FROM crm_lead_custom_values WHERE lead_id = ? AND custom_field_id = ?",
                    [$leadId, $field['id']]
                );
                if ($existing) {
                    $this->db->query("UPDATE crm_lead_custom_values SET field_value = ? WHERE id = ?", [$val, $existing['id']]);
                } else {
                    $this->db->query(
                        "INSERT INTO crm_lead_custom_values (lead_id, custom_field_id, field_value) VALUES (?, ?, ?)",
                        [$leadId, $field['id'], $val]
                    );
                }
            }
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function getFieldsBySection($section = 'general') {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM crm_custom_fields WHERE is_active = 1 AND section = ? ORDER BY order_index ASC",
                [$section]
            ) ?: [];
        } catch (\Exception $e) { return []; }
    }
}
