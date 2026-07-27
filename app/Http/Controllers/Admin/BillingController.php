<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\SaaSBillingService;
use App\Services\TenantService;

/**
 * BillingController — Super Admin panel for SaaS subscription billing & plans.
 */
class BillingController extends AdminController
{
    private SaaSBillingService $billing;
    private TenantService $tenants;

    public function __construct()
    {
        parent::__construct();
        $this->billing = new SaaSBillingService();
        $this->tenants = TenantService::getInstance();
    }

    /**
     * GET /admin/billing — Billing dashboard (MRR, ARR, revenue, subscriptions)
     */
    public function dashboard()
    {
        $this->requireAdmin();

        $stats   = $this->billing->getBillingStats();
        $trend   = $this->billing->getRevenueTrend();
        $plans   = $this->billing->getPlans();
        $tenants = $this->tenants->list(['per_page' => 100])['tenants'];

        // Build subscription list with tenant names
        $subscriptions = [];
        foreach ($tenants as $t) {
            $sub = $this->billing->getActiveSubscription((int)$t['id']);
            if ($sub) {
                $sub['tenant_name'] = $t['name'];
                $sub['tenant_id']   = $t['id'];
                $subscriptions[]    = $sub;
            }
        }

        return $this->render('admin/billing/dashboard', [
            'stats'         => $stats,
            'trend'         => $trend,
            'plans'         => $plans,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * GET /admin/billing/plans — Plan management
     */
    public function plans()
    {
        $this->requireAdmin();

        $plans  = $this->billing->getPlans();
        $stats  = $this->billing->getBillingStats();

        return $this->render('admin/billing/plans', [
            'plans'      => $plans,
            'by_plan'    => $stats['by_plan'] ?? [],
        ]);
    }

    /**
     * GET /admin/billing/subscribe/{tenantId} — Subscription form
     */
    public function subscribe(int $tenantId)
    {
        $this->requireAdmin();

        $tenant = $this->tenants->getById($tenantId);
        if (!$tenant) {
            $_SESSION['flash_error'] = 'Tenant not found.';
            header('Location: /admin/billing');
            exit;
        }

        $plans   = $this->billing->getPlans();
        $current = $this->billing->getActiveSubscription($tenantId);

        return $this->render('admin/billing/subscribe', [
            'tenant'  => $tenant,
            'plans'   => $plans,
            'current' => $current,
        ]);
    }

    /**
     * POST /admin/billing/subscribe/{tenantId} — Process subscription
     */
    public function processSubscription(int $tenantId)
    {
        $this->requireAdmin();

        $planId        = (int)($_POST['plan_id'] ?? 0);
        $billingCycle  = in_array($_POST['billing_cycle'] ?? '', ['monthly', 'yearly'])
            ? $_POST['billing_cycle'] : 'monthly';

        if (!$planId) {
            $_SESSION['flash_error'] = 'Please select a plan.';
            header("Location: /admin/billing/subscribe/{$tenantId}");
            exit;
        }

        $result = $this->billing->subscribeTenant($tenantId, $planId, $billingCycle);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Subscription activated: {$result['plan_name']} (₹{$result['amount']}/{$result['billing_cycle']}).";
        } else {
            $_SESSION['flash_error'] = 'Subscription failed. Please check the application logs.';
        }

        header("Location: /admin/billing/subscribe/{$tenantId}");
        exit;
    }

    /**
     * POST /admin/billing/cancel/{tenantId} — Cancel subscription
     */
    public function cancelSubscription(int $tenantId)
    {
        $this->requireAdmin();

        $result = $this->billing->cancelSubscription($tenantId);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'] ?? 'Subscription cancelled.';
        } else {
            $_SESSION['flash_error'] = 'Cancel failed. Please check the application logs.';
        }

        header("Location: /admin/billing/subscribe/{$tenantId}");
        exit;
    }

    /**
     * POST /admin/billing/change-plan/{tenantId} — Change plan
     */
    public function changePlan(int $tenantId)
    {
        $this->requireAdmin();

        $planId       = (int)($_POST['new_plan_id'] ?? 0);
        $billingCycle = in_array($_POST['billing_cycle'] ?? '', ['monthly', 'yearly', ''])
            ? ($_POST['billing_cycle'] ?: null) : null;

        if (!$planId) {
            $_SESSION['flash_error'] = 'Please select a plan.';
            header("Location: /admin/billing/subscribe/{$tenantId}");
            exit;
        }

        $result = $this->billing->changePlan($tenantId, $planId, $billingCycle);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Plan changed to {$result['plan_name']}.";
        } else {
            $_SESSION['flash_error'] = 'Plan change failed. Please check the application logs.';
        }

        header("Location: /admin/billing/subscribe/{$tenantId}");
        exit;
    }

    /**
     * GET /admin/billing/invoices/{tenantId} — Billing history
     */
    public function invoices(int $tenantId)
    {
        $this->requireAdmin();

        $tenant = $this->tenants->getById($tenantId);
        if (!$tenant) {
            $_SESSION['flash_error'] = 'Tenant not found.';
            header('Location: /admin/billing');
            exit;
        }

        $subscriptions = $this->billing->getSubscriptions($tenantId);

        return $this->render('admin/billing/invoices', [
            'tenant'        => $tenant,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * POST /admin/billing/webhook — Razorpay webhook handler (NO auth, signature verified inside)
     */
    public function webhook()
    {
        $payload   = json_decode(file_get_contents('php://input'), true) ?? [];
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? $_SERVER['HTTP_RAZORPAY_SIGNATURE'] ?? null;

        $result = $this->billing->handleWebhook($payload, $signature);

        header('Content-Type: application/json');
        if ($result['success']) {
            http_response_code(200);
            echo json_encode(['status' => 'ok']);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $result['error'] ?? 'Unknown']);
        }
        exit;
    }
}
