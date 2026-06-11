<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class VendorController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();

        try {
            $search = $_GET['search'] ?? '';
            $typeFilter = $_GET['type'] ?? '';
            $statusFilter = $_GET['status'] ?? '';

            $where = [];
            $params = [];

            if (!empty($search)) {
                $where[] = "(vendor_name LIKE ? OR contact_person LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($typeFilter)) {
                $where[] = "vendor_type = ?";
                $params[] = $typeFilter;
            }

            if (!empty($statusFilter)) {
                $where[] = "status = ?";
                $params[] = $statusFilter;
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $total = $this->db->fetch("SELECT COUNT(*) as count FROM vendors $whereClause", $params)['count'] ?? 0;

            $vendors = $this->db->fetchAll("SELECT * FROM vendors $whereClause ORDER BY created_at DESC", $params) ?? [];

            $stats = [
                'total' => $this->db->fetch("SELECT COUNT(*) as count FROM vendors")['count'] ?? 0,
                'active' => $this->db->fetch("SELECT COUNT(*) as count FROM vendors WHERE status = 'active'")['count'] ?? 0,
                'inactive' => $this->db->fetch("SELECT COUNT(*) as count FROM vendors WHERE status = 'inactive'")['count'] ?? 0,
                'blacklisted' => $this->db->fetch("SELECT COUNT(*) as count FROM vendors WHERE status = 'blacklisted'")['count'] ?? 0,
            ];
        } catch (\Exception $e) {
            $vendors = [];
            $stats = ['total' => 0, 'active' => 0, 'inactive' => 0, 'blacklisted' => 0];
            $this->setFlash('error', 'Error loading vendors: ' . $e->getMessage());
        }

        return $this->render('admin/vendors/index', [
            'vendors' => $vendors,
            'stats' => $stats,
            'search' => $search,
            'typeFilter' => $typeFilter,
            'statusFilter' => $statusFilter,
            'page_title' => 'Vendor Management',
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/vendors/create', [
            'page_title' => 'Add New Vendor',
        ]);
    }

    public function store()
    {
        $this->requireAdmin();

        try {
            $data = [
                'vendor_name'       => $_POST['vendor_name'] ?? '',
                'vendor_type'       => $_POST['vendor_type'] ?? 'other',
                'entity_type'       => $_POST['entity_type'] ?? 'individual',
                'contact_person'    => $_POST['contact_person'] ?? null,
                'email'             => $_POST['email'] ?? null,
                'phone'             => $_POST['phone'] ?? null,
                'address'           => $_POST['address'] ?? null,
                'city'              => $_POST['city'] ?? null,
                'state'             => $_POST['state'] ?? null,
                'gst_number'        => $_POST['gst_number'] ?? null,
                'gstin'             => $_POST['gstin'] ?? $_POST['gst_number'] ?? null,
                'pan_number'        => $_POST['pan_number'] ?? null,
                'is_tds_applicable' => isset($_POST['is_tds_applicable']) ? 1 : 1,
                'bank_name'         => $_POST['bank_name'] ?? null,
                'bank_account'      => $_POST['bank_account'] ?? null,
                'ifsc_code'         => $_POST['ifsc_code'] ?? null,
                'payment_terms'     => $_POST['payment_terms'] ?? '30_days',
                'contract_start'    => !empty($_POST['contract_start']) ? $_POST['contract_start'] : null,
                'contract_end'      => !empty($_POST['contract_end']) ? $_POST['contract_end'] : null,
                'status'            => $_POST['status'] ?? 'active',
                'notes'             => $_POST['notes'] ?? null,
                'created_by'        => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null,
            ];

            if (empty($data['vendor_name'])) {
                $this->setFlash('error', 'Vendor name is required');
                $this->redirect('/admin/vendors/create');
            }

            // Use MoneyWorkflowService for TDS auto-detection
            try {
                $svc = new \App\Services\Accounting\MoneyWorkflowService();
                $vendorId = $svc->createVendor($data);
            } catch (\Throwable $e) {
                // Fallback: direct insert without auto-detection
                $data['tds_section'] = '194C';
                $data['kyc_status'] = 'pending';
                $this->db->insert('vendors', $data);
                $vendorId = (int)$this->db->lastInsertId();
            }

            $this->setFlash('success', 'Vendor created successfully (TDS: ' . ($data['tds_section'] ?? '194C') . ')');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error creating vendor: ' . $e->getMessage());
        }

        $this->redirect('/admin/vendors');
    }

    public function show($id)
    {
        $this->requireAdmin();

        try {
            $vendor = $this->db->fetch("SELECT v.*, u.name as created_by_name FROM vendors v LEFT JOIN users u ON v.created_by = u.id WHERE v.id = ?", [$id]);

            if (!$vendor) {
                $this->setFlash('error', 'Vendor not found');
                $this->redirect('/admin/vendors');
            }

            $contracts = $this->db->fetchAll("SELECT * FROM purchase_orders WHERE vendor_id = ? ORDER BY created_at DESC LIMIT 20", [$id]) ?? [];
        } catch (\Exception $e) {
            $vendor = null;
            $contracts = [];
            $this->setFlash('error', 'Error loading vendor: ' . $e->getMessage());
        }

        return $this->render('admin/vendors/show', [
            'vendor' => $vendor,
            'contracts' => $contracts,
            'page_title' => 'Vendor Details',
        ]);
    }

    public function edit($id)
    {
        $this->requireAdmin();

        try {
            $vendor = $this->db->fetch("SELECT * FROM vendors WHERE id = ?", [$id]);

            if (!$vendor) {
                $this->setFlash('error', 'Vendor not found');
                $this->redirect('/admin/vendors');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading vendor: ' . $e->getMessage());
            $this->redirect('/admin/vendors');
        }

        return $this->render('admin/vendors/edit', [
            'vendor' => $vendor,
            'page_title' => 'Edit Vendor',
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();

        try {
            $data = [
                'vendor_name'       => $_POST['vendor_name'] ?? '',
                'vendor_type'       => $_POST['vendor_type'] ?? 'other',
                'entity_type'       => $_POST['entity_type'] ?? 'individual',
                'contact_person'    => $_POST['contact_person'] ?? null,
                'email'             => $_POST['email'] ?? null,
                'phone'             => $_POST['phone'] ?? null,
                'address'           => $_POST['address'] ?? null,
                'city'              => $_POST['city'] ?? null,
                'state'             => $_POST['state'] ?? null,
                'gst_number'        => $_POST['gst_number'] ?? null,
                'gstin'             => $_POST['gstin'] ?? $_POST['gst_number'] ?? null,
                'pan_number'        => $_POST['pan_number'] ?? null,
                'tds_section'       => $_POST['tds_section'] ?? '194C',
                'is_tds_applicable' => isset($_POST['is_tds_applicable']) ? 1 : 1,
                'bank_name'         => $_POST['bank_name'] ?? null,
                'bank_account'      => $_POST['bank_account'] ?? null,
                'ifsc_code'         => $_POST['ifsc_code'] ?? null,
                'payment_terms'     => $_POST['payment_terms'] ?? '30_days',
                'contract_start'    => !empty($_POST['contract_start']) ? $_POST['contract_start'] : null,
                'contract_end'      => !empty($_POST['contract_end']) ? $_POST['contract_end'] : null,
                'status'            => $_POST['status'] ?? 'active',
                'rating'            => $_POST['rating'] ?? 0,
                'notes'             => $_POST['notes'] ?? null,
            ];

            if (empty($data['vendor_name'])) {
                $this->setFlash('error', 'Vendor name is required');
                $this->redirect('/admin/vendors/edit/' . $id);
            }

            // Auto-detect TDS section if entity_type changed
            $entityType = $data['entity_type'] ?? 'individual';
            $validEntityTypes = ['individual', 'company', 'partnership', 'proprietorship'];
            if (!in_array($entityType, $validEntityTypes)) {
                $vt = strtolower($data['vendor_type'] ?? 'other');
                $entityType = in_array($vt, ['contractor', 'transport']) ? 'individual' : 'company';
                $data['entity_type'] = $entityType;
            }
            $data['tds_section'] = '194C';

            $this->db->update('vendors', $data, ['id' => $id]);
            $this->setFlash('success', 'Vendor updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error updating vendor: ' . $e->getMessage());
        }

        $this->redirect('/admin/vendors');
    }

    public function delete($id)
    {
        $this->requireAdmin();

        try {
            $this->db->update('vendors', ['status' => 'inactive'], ['id' => $id]);
            $this->setFlash('success', 'Vendor deactivated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error deactivating vendor: ' . $e->getMessage());
        }

        $this->redirect('/admin/vendors');
    }

    public function contracts($id)
    {
        $this->requireAdmin();

        try {
            $vendor = $this->db->fetch("SELECT id, vendor_name, vendor_type FROM vendors WHERE id = ?", [$id]);
            $contracts = $this->db->fetchAll("SELECT * FROM purchase_orders WHERE vendor_id = ? ORDER BY created_at DESC", [$id]) ?? [];
        } catch (\Exception $e) {
            $vendor = null;
            $contracts = [];
            $this->setFlash('error', 'Error loading contracts: ' . $e->getMessage());
        }

        return $this->render('admin/vendors/contracts', [
            'vendor' => $vendor,
            'contracts' => $contracts,
            'page_title' => 'Vendor Contracts',
        ]);
    }
}
