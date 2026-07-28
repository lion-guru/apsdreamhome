<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class FarmerDashboardController extends BaseController
{
    use TenantAwareTrait;
    public function __construct()
    {
        parent::__construct();
    }

    private function requireFarmerLogin()
    {
        @session_start();
        if (!isset($_SESSION['farmer_id'])) {
            header('Location: ' . BASE_URL . '/farmer/login');
            exit;
        }
    }

    public function dashboard()
    {
        $this->requireFarmerLogin();
        $farmerId = $_SESSION['farmer_id'];

        $farmer = $this->db->fetch("SELECT * FROM farmers WHERE id = ?", [$farmerId]);
        $profile = $this->db->fetch("SELECT * FROM farmer_profiles WHERE farmer_id = ?", [$farmerId]);
        $landHoldings = $this->db->fetchAll("SELECT * FROM farmer_land_holdings WHERE farmer_id = ?", [$farmerId]);
        $transactions = $this->db->fetchAll("SELECT * FROM farmer_transactions WHERE farmer_id = ? ORDER BY created_at DESC LIMIT 5", [$farmerId]);
        $agreements = $this->db->fetchAll("SELECT * FROM farmer_agreements WHERE farmer_id = ?", [$farmerId]);

        $totalArea = 0;
        $totalAmountReceived = 0;
        $totalPending = 0;
        $activeAgreements = 0;
        foreach ($landHoldings as $lh) {
            $totalArea += (float)($lh['area'] ?? 0);
            if (($lh['acquisition_status'] ?? '') === 'acquired') {
                $totalAmountReceived += (float)($lh['payment_received'] ?? 0);
                $amount = (float)($lh['acquisition_amount'] ?? 0);
                $totalPending += $amount - (float)($lh['payment_received'] ?? 0);
            }
        }
        foreach ($agreements as $a) {
            if (($a['status'] ?? '') === 'active') $activeAgreements++;
        }

        $this->layout = 'layouts/base';
        $this->render('farmer/dashboard', [
            'page_title' => 'Farmer Dashboard - APS Dream Home',
            'farmer' => $farmer,
            'profile' => $profile,
            'land_holdings' => $landHoldings,
            'transactions' => $transactions,
            'agreements' => $agreements,
            'stats' => [
                'total_holdings' => count($landHoldings),
                'total_area' => $totalArea,
                'amount_received' => $totalAmountReceived,
                'pending_amount' => $totalPending,
                'active_agreements' => $activeAgreements,
                'total_transactions' => count($transactions),
            ],
        ]);
    }

    public function landHoldings()
    {
        $this->requireFarmerLogin();
        $farmerId = $_SESSION['farmer_id'];

        $landHoldings = $this->db->fetchAll("SELECT * FROM farmer_land_holdings WHERE farmer_id = ? ORDER BY created_at DESC", [$farmerId]);

        $this->layout = 'layouts/base';
        $this->render('farmer/land_holdings', [
            'page_title' => 'My Land Holdings - APS Dream Home',
            'land_holdings' => $landHoldings,
        ]);
    }

    public function payments()
    {
        $this->requireFarmerLogin();
        $farmerId = $_SESSION['farmer_id'];

        $payments = $this->db->fetchAll("SELECT * FROM farmer_transactions WHERE farmer_id = ? ORDER BY created_at DESC", [$farmerId]);

        $this->layout = 'layouts/base';
        $this->render('farmer/payments', [
            'page_title' => 'Payment History - APS Dream Home',
            'payments' => $payments,
        ]);
    }

    public function agreements()
    {
        $this->requireFarmerLogin();
        $farmerId = $_SESSION['farmer_id'];

        $agreements = $this->db->fetchAll("SELECT * FROM farmer_agreements WHERE farmer_id = ? ORDER BY created_at DESC", [$farmerId]);

        $this->layout = 'layouts/base';
        $this->render('farmer/agreements', [
            'page_title' => 'My Agreements - APS Dream Home',
            'agreements' => $agreements,
        ]);
    }

    public function agreementDownload($id)
    {
        $this->requireFarmerLogin();
        $farmerId = $_SESSION['farmer_id'];

        $agreement = $this->db->fetch("SELECT * FROM farmer_agreements WHERE id = ? AND farmer_id = ?", [(int)$id, $farmerId]);

        if (!$agreement) {
            $_SESSION['flash_error'] = 'Agreement not found';
            header('Location: ' . BASE_URL . '/farmer/agreements');
            exit;
        }

        $content = "Agreement #{$agreement['id']}\n";
        $content .= "Type: {$agreement['agreement_type']}\n";
        $content .= "Amount: ₹{$agreement['amount']}\n";
        $content .= "Status: {$agreement['status']}\n";
        $content .= "Period: {$agreement['start_date']} to {$agreement['end_date']}\n";

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="agreement_' . $agreement['id'] . '.txt"');
        echo $content;
        exit;
    }

    public function profile()
    {
        $this->requireFarmerLogin();
        $farmerId = $_SESSION['farmer_id'];

        $farmer = $this->db->fetch("SELECT * FROM farmers WHERE id = ?", [$farmerId]);
        $profile = $this->db->fetch("SELECT * FROM farmer_profiles WHERE farmer_id = ?", [$farmerId]);

        $error = '';
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if (empty($name)) {
                $error = 'Name is required';
            } else {
                $this->db->query("UPDATE farmers SET name = ?, email = ?, address = ? WHERE id = ?", [$name, $email, $address, $farmerId]);
                $_SESSION['farmer_name'] = $name;
                $_SESSION['farmer_email'] = $email;
                $success = true;
                $farmer['name'] = $name;
                $farmer['email'] = $email;
                $farmer['address'] = $address;
            }
        }

        $this->layout = 'layouts/base';
        $this->render('farmer/profile', [
            'page_title' => 'My Profile - APS Dream Home',
            'farmer' => $farmer,
            'profile' => $profile,
            'error' => $error,
            'success' => $success,
        ]);
    }

    public function updateProfile()
    {
        $this->profile();
    }
}
