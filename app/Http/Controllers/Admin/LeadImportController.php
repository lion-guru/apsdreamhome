<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;

/**
 * Lead Import Controller — CSV Import for CRM Leads
 */
class LeadImportController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show CSV upload form
     */
    public function importForm()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/import', [
            'page_title' => 'Import Leads from CSV',
            'current_page' => 'leads',
        ]);
    }

    /**
     * Preview imported CSV before committing
     */
    public function previewImport()
    {
        $this->requireAdmin();

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Please upload a valid CSV file.';
            header('Location: ' . BASE_URL . '/admin/leads/import');
            exit;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) {
            $_SESSION['error'] = 'Unable to read the uploaded file.';
            header('Location: ' . BASE_URL . '/admin/leads/import');
            exit;
        }

        // Read header
        $headers = fgetcsv($handle);
        if (!$headers) {
            $_SESSION['error'] = 'CSV file is empty or has no headers.';
            header('Location: ' . BASE_URL . '/admin/leads/import');
            exit;
        }

        // Normalize headers
        $headers = array_map('strtolower', array_map('trim', $headers));

        // Map CSV columns to lead fields
        $columnMap = [
            'name'               => 'name',
            'email'              => 'email',
            'phone'              => 'phone',
            'source'             => 'source',
            'budget'             => 'budget',
            'budget_range'       => 'budget_range',
            'property_interest'  => 'property_interest',
            'location_preference' => 'location_preference',
            'notes'              => 'notes',
            'priority'           => 'priority',
            'status'             => 'status',
            'company'            => 'company',
            'city'               => 'city',
            'state'              => 'state',
        ];

        // Read rows (max 500 for preview)
        $rows = [];
        $rowNum = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if ($rowNum > 500) break;

            $mapped = ['_row' => $rowNum, '_errors' => []];
            foreach ($headers as $idx => $header) {
                $fieldName = $columnMap[$header] ?? null;
                if ($fieldName && isset($row[$idx])) {
                    $mapped[$fieldName] = trim($row[$idx]);
                }
            }

            // Validate required fields
            if (empty($mapped['name'])) {
                $mapped['_errors'][] = 'Name is required';
            }
            if (empty($mapped['phone']) && empty($mapped['email'])) {
                $mapped['_errors'][] = 'Phone or email is required';
            }

            // Set defaults
            if (empty($mapped['source'])) $mapped['source'] = 'csv_import';
            if (empty($mapped['priority'])) $mapped['priority'] = 'medium';
            if (empty($mapped['status'])) $mapped['status'] = 'new';

            $rows[] = $mapped;
        }
        fclose($handle);

        // Store in session for commit
        $_SESSION['import_rows'] = $rows;
        $_SESSION['import_headers'] = $headers;

        return $this->render('admin/leads/import_preview', [
            'page_title' => 'Preview Import',
            'rows' => $rows,
            'headers' => $headers,
            'total_rows' => count($rows),
            'error_rows' => count(array_filter($rows, fn($r) => !empty($r['_errors']))),
            'current_page' => 'leads',
        ]);
    }

    /**
     * Commit imported leads to database
     */
    public function commitImport()
    {
        $this->requireAdmin();

        $rows = $_SESSION['import_rows'] ?? [];
        if (empty($rows)) {
            $_SESSION['error'] = 'No import data found. Please upload again.';
            header('Location: ' . BASE_URL . '/admin/leads/import');
            exit;
        }

        $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'];
        $imported = 0;
        $skipped = 0;
        $errors = [];

        $db = Database::getInstance();
        $leadModel = new \App\Models\Lead\Lead();

        foreach ($rows as $row) {
            // Skip rows with errors
            if (!empty($row['_errors'])) {
                $skipped++;
                continue;
            }

            try {
                $leadNumber = 'CRM-' . strtoupper(substr(uniqid(), -6));

                $leadData = [
                    'lead_number'        => $leadNumber,
                    'name'               => $row['name'] ?? '',
                    'email'              => $row['email'] ?? '',
                    'phone'              => $row['phone'] ?? '',
                    'source'             => $row['source'] ?? 'csv_import',
                    'budget'             => (float)($row['budget'] ?? 0),
                    'budget_range'       => $row['budget_range'] ?? '',
                    'property_interest'  => $row['property_interest'] ?? '',
                    'location_preference' => $row['location_preference'] ?? '',
                    'notes'              => $row['notes'] ?? '',
                    'priority'           => $row['priority'] ?? 'medium',
                    'status'             => $row['status'] ?? 'new',
                    'company'            => $row['company'] ?? '',
                    'city'               => $row['city'] ?? '',
                    'state'              => $row['state'] ?? '',
                    'assigned_to'        => $adminId,
                    'created_by'         => $adminId,
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                ];

                $leadId = $db->insert('leads', $leadData);

                if ($leadId) {
                    // Log activity
                    try {
                        $db->insert('lead_activities', [
                            'lead_id'       => $leadId,
                            'activity_type' => 'imported',
                            'description'   => 'Lead imported via CSV by admin',
                            'created_by'    => $adminId,
                            'created_at'    => date('Y-m-d H:i:s'),
                        ]);
                    } catch (\Throwable $e) {
                        // Activity logging is non-critical
                    }

                    // Auto-score the lead
                    try {
                        $scorer = new \App\Services\CRM\LeadScoringService();
                        $scorer->calculateScore($leadId);
                    } catch (\Throwable $e) {
                        // Scoring is non-critical
                    }

                    $imported++;
                } else {
                    $skipped++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Row {$row['_row']}: " . $e->getMessage();
                $skipped++;
            }
        }

        // Clear session
        unset($_SESSION['import_rows'], $_SESSION['import_headers']);

        $_SESSION['success'] = "Import complete: {$imported} leads imported, {$skipped} skipped.";
        if (!empty($errors)) {
            $_SESSION['import_errors'] = $errors;
        }

        header('Location: ' . BASE_URL . '/admin/leads/import?imported=' . $imported . '&skipped=' . $skipped);
        exit;
    }
}
