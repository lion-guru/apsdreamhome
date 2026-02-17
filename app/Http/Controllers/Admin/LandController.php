<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class LandController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('csrf', ['only' => ['store', 'update', 'delete']]);
    }

    public function index()
    {
        $this->data['page_title'] = $this->mlSupport->translate('Kissan Land Records');
        $this->data['land_records'] = $this->db->fetchAll("SELECT * FROM kisaan_land_management ORDER BY id DESC");

        $this->render('admin/land/index');
    }

    public function create()
    {
        $this->data['page_title'] = $this->mlSupport->translate('Add New Land Record');
        $this->render('admin/land/create');
    }

    public function store()
    {
        // CSRF check handled by middleware

        $farmer_name = trim($_POST['farmer_name'] ?? '');
        $farmer_mobile = trim($_POST['farmer_mobile'] ?? '');

        if (empty($farmer_name) || empty($farmer_mobile)) {
            $this->setFlash('error', $this->mlSupport->translate('Farmer name and mobile are required.'));
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
                    $this->setFlash('error', $this->mlSupport->translate('Failed to upload file.'));
                    $this->redirect('admin/land/create');
                    return;
                }
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Invalid file type.'));
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

            $this->setFlash('success', $this->mlSupport->translate('Land record added successfully.'));
            $this->redirect('admin/land');
        } catch (\Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error adding record: ') . $e->getMessage());
            $this->redirect('admin/land/create');
        }
    }

    public function edit($id)
    {
        $id = intval($id);
        $land_record = $this->db->fetchOne("SELECT * FROM kisaan_land_management WHERE id = ?", [$id]);

        if (!$land_record) {
            $this->setFlash('error', $this->mlSupport->translate('Record not found.'));
            $this->redirect('admin/land');
            return;
        }

        $this->data['page_title'] = $this->mlSupport->translate('Edit Land Record');
        $this->data['land_record'] = $land_record;
        $this->render('admin/land/edit');
    }

    public function update($id)
    {
        $id = intval($id);

        $farmer_name = trim($_POST['farmer_name'] ?? '');
        $farmer_mobile = trim($_POST['farmer_mobile'] ?? '');

        if (empty($farmer_name) || empty($farmer_mobile)) {
            $this->setFlash('error', $this->mlSupport->translate('Farmer name and mobile are required.'));
            $this->redirect("admin/land/edit/$id");
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
            farmer_name = ?, farmer_mobile = ?, bank_name = ?, account_number = ?, bank_ifsc = ?,
            site_name = ?, land_area = ?, total_land_price = ?, total_paid_amount = ?, amount_pending = ?,
            gata_number = ?, district = ?, tehsil = ?, city = ?, gram = ?,
            land_manager_name = ?, land_manager_mobile = ?, agreement_status = ?
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

            $this->setFlash('success', $this->mlSupport->translate('Land record updated successfully.'));
            $this->redirect('admin/land');
        } catch (\Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error updating record: ') . $e->getMessage());
            $this->redirect("admin/land/edit/$id");
        }
    }

    public function destroy($id)
    {
        $id = intval($id);

        try {
            $this->db->execute("DELETE FROM kisaan_land_management WHERE id = ?", [$id]);

            // Log notification logic if needed (migrated from legacy)
            // ...

            $this->setFlash('success', $this->mlSupport->translate('Record deleted successfully.'));
        } catch (\Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error deleting record: ') . $e->getMessage());
        }

        $this->redirect('admin/land');
    }

    public function transactions($id)
    {
        $id = intval($id);
        $this->data['page_title'] = $this->mlSupport->translate('Land Transactions');

        // Fetch land record details
        $this->data['land_record'] = $this->db->fetchOne("SELECT * FROM kisaan_land_management WHERE id = ?", [$id]);

        if (!$this->data['land_record']) {
            $this->setFlash('error', $this->mlSupport->translate('Land record not found.'));
            $this->redirect('admin/land');
            return;
        }

        // Fetch transactions
        $this->data['transactions'] = $this->db->fetchAll("SELECT * FROM transactions WHERE kisaan_id = ? ORDER BY date DESC", [$id]);

        $this->render('admin/land/transactions/index');
    }

    public function createTransaction()
    {
        $this->data['page_title'] = $this->mlSupport->translate('Add Transaction');
        $this->data['kisan_id'] = $_GET['kisan_id'] ?? '';
        $this->render('admin/land/transactions/create');
    }

    public function storeTransaction()
    {
        $kisan_id = intval($_POST['kisan_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $description = trim($_POST['description'] ?? '');

        if ($kisan_id <= 0 || $amount <= 0 || empty($description)) {
            $this->setFlash('error', $this->mlSupport->translate('Please fill in all required fields.'));
            $this->redirect('admin/land/transactions/create');
            return;
        }

        $sql = "INSERT INTO transactions (kisaan_id, amount, date, description) VALUES (?, ?, ?, ?)";

        try {
            $this->db->execute($sql, [$kisan_id, $amount, $date, $description]);
            $this->setFlash('success', $this->mlSupport->translate('Transaction added successfully.'));
            $this->redirect('admin/land');
        } catch (\Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error adding transaction: ') . $e->getMessage());
            $this->redirect('admin/land/transactions/create');
        }
    }
}
