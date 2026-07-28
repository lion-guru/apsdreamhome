<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\InsuranceService;
use App\Services\InvestmentService;
use App\Services\AddressService;
use App\Traits\TenantAwareTrait;

class PortalController extends BaseController
{
    use TenantAwareTrait;
    public function insurance()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { $this->redirect('/login?redirect=/user/insurance'); return; }
        $svc = new InsuranceService();
        $data = [
            'page_title' => 'Insurance - APS Dream Home',
            'current_page' => 'insurance',
            'plans' => $svc->listPlans(),
            'policies' => $svc->getUserPolicies($userId),
            'stats' => $svc->getStats($userId),
        ];
        $this->layout = 'layouts/customer';
        $this->render('pages/user/insurance', $data);
    }

    public function insuranceEnrol()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { $this->json(['success' => false, 'error' => 'Not authenticated'], 401); return; }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) { $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403); return; }
        $planId = (int)($_POST['plan_id'] ?? 0);
        $svc = new InsuranceService();
        $result = $svc->enrol($userId, $planId, $_POST);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    public function investmentPlans()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { $this->redirect('/login?redirect=/user/investment-plans'); return; }
        $svc = new InvestmentService();
        $stats = $svc->getStats($userId);
        $data = [
            'page_title' => 'Investment Plans - APS Dream Home',
            'current_page' => 'investment',
            'plans' => $svc->listPlans(),
            'investments' => $svc->getUserInvestments($userId),
            'stats' => $stats,
        ];
        $this->layout = 'layouts/customer';
        $this->render('pages/user/investment_plans', $data);
    }

    public function invest()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { $this->json(['success' => false, 'error' => 'Not authenticated'], 401); return; }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) { $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403); return; }
        $planId = (int)($_POST['plan_id'] ?? 0);
        $svc = new InvestmentService();
        $result = $svc->invest($userId, $planId, $_POST);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    public function address()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { $this->redirect('/login?redirect=/user/address'); return; }
        $svc = new AddressService();
        $data = [
            'page_title' => 'My Address - APS Dream Home',
            'current_page' => 'address',
            'addresses' => $svc->listForUser($userId),
        ];
        $this->layout = 'layouts/customer';
        $this->render('pages/user/address', $data);
    }

    public function addressCreate()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { $this->json(['success' => false, 'error' => 'Not authenticated'], 401); return; }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) { $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403); return; }
        $svc = new AddressService();
        $result = $svc->create($userId, $_POST);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    public function addressUpdate()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { $this->json(['success' => false, 'error' => 'Not authenticated'], 401); return; }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) { $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403); return; }
        $id = (int)($_POST['id'] ?? 0);
        $svc = new AddressService();
        $result = $svc->update($id, $userId, $_POST);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    public function addressDelete()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { $this->json(['success' => false, 'error' => 'Not authenticated'], 401); return; }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) { $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403); return; }
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $svc = new AddressService();
        $result = $svc->delete($id, $userId);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    public function addressSetPrimary()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { $this->json(['success' => false, 'error' => 'Not authenticated'], 401); return; }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) { $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403); return; }
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $svc = new AddressService();
        $result = $svc->setPrimary($id, $userId);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    public function pincodeLookup()
    {
        $pincode = preg_replace('/\D/', '', $_GET['pincode'] ?? $_POST['pincode'] ?? '');
        if (strlen($pincode) < 4) {
            $this->json(['found' => false, 'message' => 'Invalid pincode']);
            return;
        }
        $svc = new AddressService();
        $result = $svc->lookupByPincode($pincode);
        if ($result) {
            $this->json(['found' => true, 'data' => $result]);
        } else {
            $this->json(['found' => false, 'pincode' => $pincode, 'message' => 'No data. Enter manually.']);
        }
    }
}
