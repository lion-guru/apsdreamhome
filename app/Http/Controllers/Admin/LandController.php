<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\NotificationService;

class LandController extends AdminController
{
    protected $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('csrf', ['only' => ['store', 'update', 'delete']]);
        $this->notificationService = new NotificationService();
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

        $farmer_name = trim($this->request->post('farmer_name'));
        $farmer_mobile = trim($this->request->post('farmer_mobile'));

        if (empty($farmer_name) || empty($farmer_mobile)) {
            $this->setFlash('error', $this->mlSupport->translate('Farmer name and mobile are required.'));
            $this->redirect('admin/land/create');
            return;
        }

        $bank_name = trim($this->request->post('bank_name'));
        $account_number = trim($this->request->post('account_number'));
        $bank_ifsc = trim($this->request->post('bank_ifsc'));
        $site_name = trim($this->request->post('site_name'));
        $land_area = floatval($this->request->post('land_area') ?? 0);
        $total_land_price = floatval($this->request->post('total_land_price') ?? 0);
        $gata_number = trim($this->request->post('gata_number'));
        $district = trim($this->request->post('district'));
        $tehsil = trim($this->request->post('tehsil'));
        $city = trim($this->request->post('city'));
        $gram = trim($this->request->post('gram'));
        $land_manager_name = trim($this->request->post('land_manager_name'));
        $land_manager_mobile = trim($this->request->post('land_manager_mobile'));
        $agreement_status = trim($this->request->post('agreement_status') ?? 'Pending');

        // File Upload
        $file_path = "";
        $land_paper = $this->request->files('land_paper');
        if (isset($land_paper['error']) && $land_paper['error'] === UPLOAD_ERR_OK) {
            $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($land_paper['name'], PATHINFO_EXTENSION));

            if (in_array($file_extension, $allowed_extensions)) {
                $upload_dir = 'uploads/land_papers/';
                $app_root = defined('APP_ROOT') ? APP_ROOT : dirname(dirname(dirname(dirname(__DIR__))));
                if (!is_dir($app_root . '/' . $upload_dir)) {
                    mkdir($app_root . '/' . $upload_dir, 0755, true);
                }

                $file_name = uniqid('land_', true) . '.' . $file_extension;
                $target_path = $app_root . '/' . $upload_dir . $file_name;

                if (move_uploaded_file($land_paper['tmp_name'], $target_path)) {
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

            // Send notification
            try {
                $adminEmail = getenv('MAIL_ADMIN') ?: 'admin@apsdreamhome.com';
                $subject = "New Land Record Added: " . $farmer_name;
                $message = "A new land record has been added.\n\n";
                $message .= "Farmer: " . $farmer_name . "\n";
                $message .= "Site: " . $site_name . "\n";
                $message .= "Area: " . $land_area . "\n";
                $message .= "Total Price: " . $total_land_price . "\n";
                $message .= "Added by: " . ($_SESSION['username'] ?? 'Admin');

                $this->notificationService->sendEmail(
                    $adminEmail,
                    $subject,
                    $message,
                    'land_record_created'
                );
            } catch (\Exception $e) {
                // Log error but don't fail the request
                error_log("Failed to send land record notification: " . $e->getMessage());
            }

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

        $farmer_name = trim($this->request->post('farmer_name') ?? '');
        $farmer_mobile = trim($this->request->post('farmer_mobile') ?? '');

        if (empty($farmer_name) || empty($farmer_mobile)) {
            $this->setFlash('error', $this->mlSupport->translate('Farmer name and mobile are required.'));
            $this->redirect("admin/land/edit/$id");
            return;
        }

        $bank_name = trim($this->request->post('bank_name') ?? '');
        $account_number = trim($this->request->post('account_number') ?? '');
        $bank_ifsc = trim($this->request->post('bank_ifsc') ?? '');
        $site_name = trim($this->request->post('site_name') ?? '');
        $land_area = floatval($this->request->post('land_area') ?? 0);
        $total_land_price = floatval($this->request->post('total_land_price') ?? 0);
        $total_paid_amount = floatval($this->request->post('total_paid_amount') ?? 0);
        $amount_pending = $total_land_price - $total_paid_amount;
        $gata_number = trim($this->request->post('gata_number') ?? '');
        $district = trim($this->request->post('district') ?? '');
        $tehsil = trim($this->request->post('tehsil') ?? '');
        $city = trim($this->request->post('city') ?? '');
        $gram = trim($this->request->post('gram') ?? '');
        $land_manager_name = trim($this->request->post('land_manager_name') ?? '');
        $land_manager_mobile = trim($this->request->post('land_manager_mobile') ?? '');
        $agreement_status = trim($this->request->post('agreement_status') ?? 'Pending');

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
        $land_record = $this->db->fetchOne("SELECT * FROM kisaan_land_management WHERE id = ?", [$id]);

        if (!$land_record) {
            $this->setFlash('error', $this->mlSupport->translate('Record not found.'));
            $this->redirect('admin/land');
            return;
        }

        // Ensure transactions table exists
        $this->ensureTransactionsTable();

        $transactions = $this->db->fetchAll("SELECT * FROM kisaan_land_transactions WHERE land_record_id = ? ORDER BY date DESC", [$id]);

        $this->render('admin/land/transactions/index', [
            'land_record' => $land_record,
            'transactions' => $transactions,
            'page_title' => $this->mlSupport->translate('Land Transactions')
        ]);
    }

    public function createTransaction()
    {
        $kisan_id = $this->request->get('kisan_id');
        $this->render('admin/land/transactions/create', [
            'kisan_id' => $kisan_id,
            'page_title' => $this->mlSupport->translate('Add Transaction')
        ]);
    }

    public function storeTransaction()
    {
        $kisan_id = intval($this->request->post('kisan_id'));
        $amount = floatval($this->request->post('amount'));
        $date = $this->request->post('date');
        $description = trim($this->request->post('description'));

        if (!$kisan_id || $amount <= 0 || empty($date)) {
            $this->setFlash('error', $this->mlSupport->translate('Please fill all required fields correctly.'));
            $this->redirect('admin/land/transactions/create');
            return;
        }

        // Ensure transactions table exists
        $this->ensureTransactionsTable();

        try {
            $this->db->execute(
                "INSERT INTO kisaan_land_transactions (land_record_id, amount, date, description, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$kisan_id, $amount, $date, $description]
            );

            // Update total paid and pending
            $this->updatePaymentStatus($kisan_id);

            $this->setFlash('success', $this->mlSupport->translate('Transaction added successfully.'));
            $this->redirect("admin/land/transactions/$kisan_id");
        } catch (\Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error adding transaction: ') . $e->getMessage());
            $this->redirect('admin/land/transactions/create');
        }
    }

    protected function updatePaymentStatus($id)
    {
        $total_paid = $this->db->fetchColumn("SELECT SUM(amount) FROM kisaan_land_transactions WHERE land_record_id = ?", [$id]);
        $land_record = $this->db->fetchOne("SELECT total_land_price FROM kisaan_land_management WHERE id = ?", [$id]);

        if ($land_record) {
            $pending = $land_record['total_land_price'] - $total_paid;
            $this->db->execute(
                "UPDATE kisaan_land_management SET total_paid_amount = ?, amount_pending = ? WHERE id = ?",
                [$total_paid, $pending, $id]
            );
        }
    }

    protected function ensureTransactionsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS kisaan_land_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            land_record_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            date DATE NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (land_record_id) REFERENCES kisaan_land_management(id) ON DELETE CASCADE
        )";
        $this->db->execute($sql);
    }
}
