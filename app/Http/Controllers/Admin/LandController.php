<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

class LandController extends BaseController
{

    public function index()
    {
        $land_records = $this->db->fetchAll("SELECT * FROM kisaan_land_management ORDER BY id DESC");

        $data = [
            'page_title' => 'Kissan Land Records',
            'land_records' => $land_records
        ];

        $this->view('admin/land/index', $data);
    }

    public function create()
    {
        $data = [
            'page_title' => 'Add New Land Record'
        ];
        $this->view('admin/land/create', $data);
    }

    public function store()
    {
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect('admin/land/create');
            return;
        }

        $farmer_name = trim($_POST['farmer_name'] ?? '');
        $farmer_mobile = trim($_POST['farmer_mobile'] ?? '');

        if (empty($farmer_name) || empty($farmer_mobile)) {
            $this->setFlash('error', 'Farmer name and mobile are required.');
            $this->redirect('admin/land/create');
            return;
        }

        $bank_name = trim($_POST['bank_name'] ?? '');
        $account_number = trim($_POST['account_number'] ?? '');
        $bank_ifsc = trim($_POST['bank_ifsc'] ?? '');
        $site_name = trim($_POST['site_name'] ?? '');
        $land_area = floatval($_POST['land_area'] ?? 0);
        $total_land_price = floatval($_POST['total_land_price'] ?? 0);
        $gata_number = trim($_POST['gata_number'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $tehsil = trim($_POST['tehsil'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $gram = trim($_POST['gram'] ?? '');
        $land_manager_name = trim($_POST['land_manager_name'] ?? '');
        $land_manager_mobile = trim($_POST['land_manager_mobile'] ?? '');
        $agreement_status = trim($_POST['agreement_status'] ?? 'Pending');

        // File Upload
        $file_path = "";
        if (isset($_FILES['land_paper']) && $_FILES['land_paper']['error'] === UPLOAD_ERR_OK) {
            $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($_FILES['land_paper']['name'], PATHINFO_EXTENSION));

            if (in_array($file_extension, $allowed_extensions)) {
                $upload_dir = 'uploads/land_papers/';
                if (!is_dir(ABSPATH . '/' . $upload_dir)) {
                    mkdir(ABSPATH . '/' . $upload_dir, 0755, true);
                }

                $file_name = uniqid('land_', true) . '.' . $file_extension;
                $target_path = ABSPATH . '/' . $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['land_paper']['tmp_name'], $target_path)) {
                    $file_path = $upload_dir . $file_name;
                } else {
                    $this->setFlash('error', 'Failed to upload file.');
                    $this->redirect('admin/land/create');
                    return;
                }
            } else {
                $this->setFlash('error', 'Invalid file type.');
                $this->redirect('admin/land/create');
                return;
            }
        }

        $sql = "INSERT INTO kisaan_land_management (
            farmer_name, farmer_mobile, bank_name, account_number, bank_ifsc, 
            site_name, land_area, total_land_price, gata_number, district, 
            tehsil, city, gram, land_paper, land_manager_name, 
            land_manager_mobile, agreement_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $this->db->execute($sql, [
                $farmer_name,
                $farmer_mobile,
                $bank_name,
                $account_number,
                $bank_ifsc,
                $site_name,
                $land_area,
                $total_land_price,
                $gata_number,
                $district,
                $tehsil,
                $city,
                $gram,
                $file_path,
                $land_manager_name,
                $land_manager_mobile,
                $agreement_status
            ]);

            $this->setFlash('success', 'Land record added successfully.');
            $this->redirect('admin/land');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error adding record: ' . $e->getMessage());
            $this->redirect('admin/land/create');
        }
    }

    public function edit($id)
    {
        $land_record = $this->db->fetchOne("SELECT * FROM kisaan_land_management WHERE id = ?", [$id]);

        if (!$land_record) {
            $this->setFlash('error', 'Record not found.');
            $this->redirect('admin/land');
            return;
        }

        $data = [
            'page_title' => 'Edit Land Record',
            'record' => $land_record
        ];

        $this->view('admin/land/edit', $data);
    }

    public function update($id)
    {
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect('admin/land/edit/' . $id);
            return;
        }

        $farmer_name = trim($_POST['farmer_name'] ?? '');
        $farmer_mobile = trim($_POST['farmer_mobile'] ?? '');

        if (empty($farmer_name) || empty($farmer_mobile)) {
            $this->setFlash('error', 'Farmer name and mobile are required.');
            $this->redirect('admin/land/edit/' . $id);
            return;
        }

        $bank_name = trim($_POST['bank_name'] ?? '');
        $account_number = trim($_POST['account_number'] ?? '');
        $bank_ifsc = trim($_POST['bank_ifsc'] ?? '');
        $site_name = trim($_POST['site_name'] ?? '');
        $land_area = floatval($_POST['land_area'] ?? 0);
        $total_land_price = floatval($_POST['total_land_price'] ?? 0);
        $total_paid_amount = floatval($_POST['total_paid_amount'] ?? 0);
        $amount_pending = $total_land_price - $total_paid_amount;
        $gata_number = trim($_POST['gata_number'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $tehsil = trim($_POST['tehsil'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $gram = trim($_POST['gram'] ?? '');
        $land_manager_name = trim($_POST['land_manager_name'] ?? '');
        $land_manager_mobile = trim($_POST['land_manager_mobile'] ?? '');
        $agreement_status = trim($_POST['agreement_status'] ?? 'Pending');

        $sql = "UPDATE kisaan_land_management SET
            farmer_name = ?, farmer_mobile = ?, bank_name = ?, account_number = ?, 
            bank_ifsc = ?, site_name = ?, land_area = ?, total_land_price = ?, 
            total_paid_amount = ?, amount_pending = ?, gata_number = ?, district = ?, 
            tehsil = ?, city = ?, gram = ?, land_manager_name = ?, 
            land_manager_mobile = ?, agreement_status = ?
            WHERE id = ?";

        try {
            $this->db->execute($sql, [
                $farmer_name,
                $farmer_mobile,
                $bank_name,
                $account_number,
                $bank_ifsc,
                $site_name,
                $land_area,
                $total_land_price,
                $total_paid_amount,
                $amount_pending,
                $gata_number,
                $district,
                $tehsil,
                $city,
                $gram,
                $land_manager_name,
                $land_manager_mobile,
                $agreement_status,
                $id
            ]);

            $this->setFlash('success', 'Land record updated successfully.');
            $this->redirect('admin/land');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error updating record: ' . $e->getMessage());
            $this->redirect('admin/land/edit/' . $id);
        }
    }

    public function destroy($id)
    {
        if (!$this->validateCsrfToken()) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
            exit;
        }

        try {
            $this->db->execute("DELETE FROM kisaan_land_management WHERE id = ?", [$id]);
            echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully.']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting record: ' . $e->getMessage()]);
        }
    }
}
