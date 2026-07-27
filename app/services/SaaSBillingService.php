<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Services\Gateway\RazorpayService;

/**
 * SaaSBillingService — Subscription billing lifecycle for SaaS tenants.
 * Ties RazorpayService + subscription_plans + tenant_subscriptions together.
 */
class SaaSBillingService
{
    private ?\PDO $pdo;
    private RazorpayService $razorpay;
    private TenantService $tenantService;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
        $this->razorpay = new RazorpayService();
        $this->tenantService = TenantService::getInstance();
    }

    // ── Subscription Lifecycle ──────────────────────────────

    /**
     * Subscribe a tenant to a plan. Creates Razorpay customer + subscription.
     */
    public function subscribeTenant(int $tenantId, int $planId, string $billingCycle = 'monthly'): array
    {
        try {
            $tenant = $this->tenantService->getById($tenantId);
            if (!$tenant) return ['success' => false, 'error' => 'Tenant not found'];

            $plan = $this->getPlan($planId);
            if (!$plan) return ['success' => false, 'error' => 'Plan not found'];

            // Free plan = no Razorpay needed
            if ($plan['slug'] === 'free' || ($plan['price_monthly'] == 0 && $plan['price_yearly'] == 0)) {
                return $this->activateFreePlan($tenantId, $planId, $billingCycle);
            }

            // Create or get Razorpay customer
            $customerResult = $this->razorpay->createCustomer(
                $tenant['contact_name'] ?? $tenant['name'],
                $tenant['contact_email'] ?? '',
                $tenant['contact_phone'] ?? ''
            );

            $razorpayCustomerId = null;
            if ($customerResult['success'] && !empty($customerResult['data']['id'])) {
                $razorpayCustomerId = $customerResult['data']['id'];
            }

            // Create Razorpay plan (idempotent — check if exists first)
            $razorpayPlanId = $this->getRazorpayPlanId($planId, $billingCycle);
            if (!$razorpayPlanId) {
                $price = $billingCycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
                $planResult = $this->razorpay->createPlan(
                    [
                        'name'     => $plan['name'] . ' (' . $billingCycle . ')',
                        'amount'   => (float)$price,
                        'currency' => $plan['currency'] ?? 'INR',
                    ],
                    $billingCycle === 'yearly' ? 'yearly' : 'monthly',
                    $billingCycle === 'yearly' ? 12 : 1
                );

                if (!$planResult['success']) {
                    return ['success' => false, 'error' => 'Failed to create Razorpay plan: ' . ($planResult['error'] ?? 'Unknown')];
                }
                $razorpayPlanId = $planResult['data']['id'] ?? null;
                $this->storeRazorpayPlanMapping($planId, $billingCycle, $razorpayPlanId);
            }

            // Create Razorpay subscription
            $amount = $billingCycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
            $subResult = $this->razorpay->createSubscription($razorpayPlanId, $razorpayCustomerId ?? '');

            $razorpaySubId = null;
            if ($subResult['success'] && !empty($subResult['data']['id'])) {
                $razorpaySubId = $subResult['data']['id'];
            }

            // Store subscription in DB
            $now = date('Y-m-d H:i:s');
            $periodEnd = $billingCycle === 'yearly'
                ? date('Y-m-d H:i:s', strtotime('+1 year'))
                : date('Y-m-d H:i:s', strtotime('+1 month'));

            // Wrap in transaction to prevent race conditions
            $this->pdo->beginTransaction();
            try {
                // Cancel any existing active subscription for this tenant
                $this->deactivateExistingSubscription($tenantId);

                $st = $this->pdo->prepare("
                    INSERT INTO tenant_subscriptions
                    (tenant_id, plan_id, status, billing_cycle, amount, razorpay_subscription_id, razorpay_customer_id,
                     current_period_start, current_period_end, created_at, updated_at)
                    VALUES (?, ?, 'active', ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $st->execute([
                    $tenantId, $planId, $billingCycle, $amount,
                    $razorpaySubId, $razorpayCustomerId, $now, $periodEnd,
                ]);

                $subscriptionId = (int)$this->pdo->lastInsertId();

                // Update tenant plan
                $this->tenantService->update($tenantId, [
                    'plan_id'          => $planId,
                    'max_users'        => $plan['max_users'],
                    'max_leads'        => $plan['max_leads'],
                    'max_properties'   => $plan['max_properties'],
                    'max_associates'   => $plan['max_associates'] ?? 0,
                    'storage_limit_mb' => $plan['storage_limit_mb'] ?? 100,
                    'status'           => 'active',
                ]);

                $this->pdo->commit();
            } catch (\Throwable $txErr) {
                $this->pdo->rollBack();
                throw $txErr;
            }

            return [
                'success'           => true,
                'subscription_id'   => $subscriptionId,
                'razorpay_sub_id'   => $razorpaySubId,
                'plan_name'         => $plan['name'],
                'amount'            => $amount,
                'billing_cycle'     => $billingCycle,
                'period_end'        => $periodEnd,
            ];
        } catch (\Throwable $e) {
            error_log('SaaSBillingService::subscribeTenant error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Activate a free plan (no payment needed).
     */
    private function activateFreePlan(int $tenantId, int $planId, string $billingCycle): array
    {
        $this->deactivateExistingSubscription($tenantId);

        $plan = $this->getPlan($planId);
        $periodEnd = date('Y-m-d H:i:s', strtotime('+10 years')); // effectively permanent

        $st = $this->pdo->prepare("
            INSERT INTO tenant_subscriptions
            (tenant_id, plan_id, status, billing_cycle, amount, razorpay_subscription_id, razorpay_customer_id,
             current_period_start, current_period_end, created_at, updated_at)
            VALUES (?, ?, 'active', ?, 0, NULL, NULL, NOW(), ?, NOW(), NOW())
        ");
        $st->execute([$tenantId, $planId, $billingCycle, $periodEnd]);

        $this->tenantService->update($tenantId, [
            'plan_id'          => $planId,
            'max_users'        => $plan['max_users'] ?? 1,
            'max_leads'        => $plan['max_leads'] ?? 50,
            'max_properties'   => $plan['max_properties'] ?? 10,
            'max_associates'   => $plan['max_associates'] ?? 0,
            'storage_limit_mb' => $plan['storage_limit_mb'] ?? 100,
            'status'           => 'active',
        ]);

        return [
            'success'         => true,
            'subscription_id' => (int)$this->pdo->lastInsertId(),
            'plan_name'       => $plan['name'] ?? 'Free',
            'amount'          => 0,
            'billing_cycle'   => $billingCycle,
            'period_end'      => $periodEnd,
        ];
    }

    /**
     * Cancel a tenant's subscription.
     */
    public function cancelSubscription(int $tenantId): array
    {
        try {
            $sub = $this->getActiveSubscription($tenantId);
            if (!$sub) return ['success' => false, 'error' => 'No active subscription found'];

            // Cancel on Razorpay if exists
            if (!empty($sub['razorpay_subscription_id'])) {
                $this->razorpay->cancelSubscription($sub['razorpay_subscription_id']);
            }

            // Update DB
            $st = $this->pdo->prepare("
                UPDATE tenant_subscriptions
                SET status = 'cancelled', cancelled_at = NOW(), updated_at = NOW()
                WHERE id = ? AND tenant_id = ?
            ");
            $st->execute([$sub['id'], $tenantId]);

            // Downgrade to free plan
            $freePlan = $this->getPlanBySlug('free');
            if ($freePlan) {
                $this->tenantService->update($tenantId, [
                    'plan_id'          => $freePlan['id'],
                    'max_users'        => $freePlan['max_users'],
                    'max_leads'        => $freePlan['max_leads'],
                    'max_properties'   => $freePlan['max_properties'],
                    'status'           => 'active',
                ]);
            }

            return ['success' => true, 'message' => 'Subscription cancelled. Downgraded to Free plan.'];
        } catch (\Throwable $e) {
            error_log('SaaSBillingService::cancelSubscription error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Change plan (upgrade/downgrade).
     */
    public function changePlan(int $tenantId, int $newPlanId, string $billingCycle = null): array
    {
        try {
            $sub = $this->getActiveSubscription($tenantId);
            $newPlan = $this->getPlan($newPlanId);
            if (!$newPlan) return ['success' => false, 'error' => 'Plan not found'];

            // If no existing subscription, create new one
            if (!$sub) {
                return $this->subscribeTenant($tenantId, $newPlanId, $billingCycle ?? 'monthly');
            }

            $cycle = $billingCycle ?? $sub['billing_cycle'] ?? 'monthly';

            // If new plan is free, cancel current
            if ($newPlan['slug'] === 'free') {
                return $this->cancelSubscription($tenantId);
            }

            // Update Razorpay subscription if exists
            if (!empty($sub['razorpay_subscription_id']) && !empty($sub['razorpay_plan_id'])) {
                // Cancel old and create new (Razorpay doesn't support inline plan change)
                $this->razorpay->cancelSubscription($sub['razorpay_subscription_id']);
                $this->deactivateExistingSubscription($tenantId);
                return $this->subscribeTenant($tenantId, $newPlanId, $cycle);
            }

            // No Razorpay sub (was free plan) — deactivate + create new in transaction
            $this->pdo->beginTransaction();
            try {
                $this->deactivateExistingSubscription($tenantId);
                $this->pdo->commit();
            } catch (\Throwable $txErr) {
                $this->pdo->rollBack();
                throw $txErr;
            }
            return $this->subscribeTenant($tenantId, $newPlanId, $cycle);
        } catch (\Throwable $e) {
            error_log('SaaSBillingService::changePlan error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Webhook Handler ────────────────────────────────────

    /**
     * Process Razorpay webhook events for subscriptions.
     */
    public function handleWebhook(array $payload, string $signature = null): array
    {
        try {
            // Verify signature — REJECT if secret not configured or signature missing
            $webhookSecret = $this->getWebhookSecret();
            if (!$webhookSecret) {
                error_log('SaaSBillingService::handleWebhook — webhook secret not configured, rejecting');
                return ['success' => false, 'error' => 'Webhook secret not configured'];
            }
            if (!$signature) {
                error_log('SaaSBillingService::handleWebhook — missing signature header');
                return ['success' => false, 'error' => 'Missing webhook signature'];
            }
            $valid = $this->razorpay->verifyWebhookSignature(
                json_encode($payload), $signature
            );
            if (!$valid) {
                return ['success' => false, 'error' => 'Invalid webhook signature'];
            }

            $event = $payload['event'] ?? '';
            $subData = $payload['payload']['subscription']['entity'] ?? [];

            switch ($event) {
                case 'subscription.activated':
                    $this->onSubscriptionActivated($subData);
                    break;
                case 'subscription.charged':
                    $this->onSubscriptionCharged($subData);
                    break;
                case 'subscription.cancelled':
                    $this->onSubscriptionCancelled($subData);
                    break;
                case 'subscription.paused':
                    $this->onSubscriptionPaused($subData);
                    break;
                case 'subscription.halted':
                    $this->onSubscriptionHalted($subData);
                    break;
                case 'subscription.expired':
                    $this->onSubscriptionExpired($subData);
                    break;
                default:
                    // Log unhandled events
                    error_log('SaaSBillingService: unhandled webhook event: ' . $event);
            }

            return ['success' => true, 'event' => $event];
        } catch (\Throwable $e) {
            error_log('SaaSBillingService::handleWebhook error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function onSubscriptionActivated(array $sub): void
    {
        $this->updateSubscriptionStatus($sub['id'], 'active');
    }

    private function onSubscriptionCharged(array $sub): void
    {
        $this->updateSubscriptionStatus($sub['id'], 'active');

        // Extend period
        if (!empty($sub['current_end'])) {
            $st = $this->pdo->prepare("UPDATE tenant_subscriptions SET current_period_end = FROM_UNIXTIME(?), updated_at = NOW() WHERE razorpay_subscription_id = ?");
            $st->execute([$sub['current_end'], $sub['id']]);
        }
    }

    private function onSubscriptionCancelled(array $sub): void
    {
        $this->updateSubscriptionStatus($sub['id'], 'cancelled');
    }

    private function onSubscriptionPaused(array $sub): void
    {
        $this->updateSubscriptionStatus($sub['id'], 'past_due');
    }

    private function onSubscriptionHalted(array $sub): void
    {
        $this->updateSubscriptionStatus($sub['id'], 'past_due');
    }

    private function onSubscriptionExpired(array $sub): void
    {
        $this->updateSubscriptionStatus($sub['id'], 'cancelled');

        // Downgrade tenant to free
        $row = $this->pdo->prepare("SELECT tenant_id FROM tenant_subscriptions WHERE razorpay_subscription_id = ?");
        $row->execute([$sub['id']]);
        $subscription = $row->fetch(\PDO::FETCH_ASSOC);
        if ($subscription) {
            $freePlan = $this->getPlanBySlug('free');
            if ($freePlan) {
                $this->tenantService->update($subscription['tenant_id'], [
                    'plan_id'        => $freePlan['id'],
                    'max_users'      => $freePlan['max_users'],
                    'max_leads'      => $freePlan['max_leads'],
                    'max_properties' => $freePlan['max_properties'],
                ]);
            }
        }
    }

    private function updateSubscriptionStatus(string $razorpaySubId, string $status): void
    {
        $st = $this->pdo->prepare("UPDATE tenant_subscriptions SET status = ?, updated_at = NOW() WHERE razorpay_subscription_id = ?");
        $st->execute([$status, $razorpaySubId]);
    }

    // ── Queries ─────────────────────────────────────────────

    /**
     * Get active subscription for a tenant.
     */
    public function getActiveSubscription(int $tenantId): ?array
    {
        $st = $this->pdo->prepare("
            SELECT ts.*, sp.name as plan_name, sp.price_monthly, sp.price_yearly
            FROM tenant_subscriptions ts
            LEFT JOIN subscription_plans sp ON sp.id = ts.plan_id
            WHERE ts.tenant_id = ? AND ts.status IN ('active','trialing')
            ORDER BY ts.created_at DESC LIMIT 1
        ");
        $st->execute([$tenantId]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all subscriptions for a tenant.
     */
    public function getSubscriptions(int $tenantId): array
    {
        $st = $this->pdo->prepare("
            SELECT ts.*, sp.name as plan_name
            FROM tenant_subscriptions ts
            LEFT JOIN subscription_plans sp ON sp.id = ts.plan_id
            WHERE ts.tenant_id = ?
            ORDER BY ts.created_at DESC
        ");
        $st->execute([$tenantId]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get all plans.
     */
    public function getPlans(): array
    {
        $st = $this->pdo->query("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY price_monthly ASC");
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get a specific plan by ID.
     */
    public function getPlan(int $planId): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM subscription_plans WHERE id = ? AND is_active = 1");
        $st->execute([$planId]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get plan by slug.
     */
    public function getPlanBySlug(string $slug): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM subscription_plans WHERE slug = ? AND is_active = 1");
        $st->execute([$slug]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get billing stats for admin dashboard.
     */
    public function getBillingStats(): array
    {
        $stats = [];

        $stats['total_subscriptions'] = (int)$this->pdo->query("SELECT COUNT(*) FROM tenant_subscriptions")->fetchColumn();
        $stats['active_subscriptions'] = (int)$this->pdo->query("SELECT COUNT(*) FROM tenant_subscriptions WHERE status = 'active'")->fetchColumn();
        $stats['cancelled_subscriptions'] = (int)$this->pdo->query("SELECT COUNT(*) FROM tenant_subscriptions WHERE status = 'cancelled'")->fetchColumn();
        $stats['past_due_subscriptions'] = (int)$this->pdo->query("SELECT COUNT(*) FROM tenant_subscriptions WHERE status = 'past_due'")->fetchColumn();

        $stats['total_mrr'] = (float)$this->pdo->query("SELECT COALESCE(SUM(amount),0) FROM tenant_subscriptions WHERE status = 'active' AND billing_cycle = 'monthly'")->fetchColumn();
        $stats['total_arr'] = (float)$this->pdo->query("SELECT COALESCE(SUM(amount),0) FROM tenant_subscriptions WHERE status = 'active' AND billing_cycle = 'yearly'")->fetchColumn();

        $stats['revenue_this_month'] = (float)$this->pdo->query("
            SELECT COALESCE(SUM(amount),0) FROM tenant_subscriptions
            WHERE status = 'active' AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
        ")->fetchColumn();

        // Revenue by plan
        $stats['by_plan'] = $this->pdo->query("
            SELECT sp.name, sp.slug, COUNT(ts.id) as count, COALESCE(SUM(ts.amount),0) as revenue
            FROM subscription_plans sp
            LEFT JOIN tenant_subscriptions ts ON ts.plan_id = sp.id AND ts.status = 'active'
            WHERE sp.is_active = 1
            GROUP BY sp.id, sp.name, sp.slug
            ORDER BY sp.price_monthly ASC
        ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return $stats;
    }

    /**
     * Get revenue trend (last 6 months).
     */
    public function getRevenueTrend(): array
    {
        $st = $this->pdo->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                   COUNT(*) as new_subscriptions,
                   SUM(amount) as revenue
            FROM tenant_subscriptions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ");
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    // ── Helpers ─────────────────────────────────────────────

    private function deactivateExistingSubscription(int $tenantId): void
    {
        $st = $this->pdo->prepare("
            UPDATE tenant_subscriptions SET status = 'cancelled', cancelled_at = NOW(), updated_at = NOW()
            WHERE tenant_id = ? AND status IN ('active','trialing')
        ");
        $st->execute([$tenantId]);
    }

    private function getWebhookSecret(): ?string
    {
        $st = $this->pdo->query("SELECT config_value FROM service_configs WHERE service_name = 'razorpay' AND config_key = 'webhook_secret'");
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        return $row ? $row['config_value'] : null;
    }

    private function getRazorpayPlanId(int $planId, string $billingCycle): ?string
    {
        // Check if we already have a Razorpay plan mapping
        // Use a simple naming convention: aps_plan_{planId}_{cycle}
        // Store in service_configs as razorpay_plan_{planId}_{cycle}
        $key = "razorpay_plan_{$planId}_{$billingCycle}";
        $st = $this->pdo->prepare("SELECT config_value FROM service_configs WHERE service_name = 'razorpay' AND config_key = ?");
        $st->execute([$key]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        return $row ? $row['config_value'] : null;
    }

    private function storeRazorpayPlanMapping(int $planId, string $billingCycle, string $razorpayPlanId): void
    {
        $key = "razorpay_plan_{$planId}_{$billingCycle}";
        try {
            $st = $this->pdo->prepare("
                INSERT INTO service_configs (service_name, config_key, config_value, config_type, updated_at)
                VALUES ('razorpay', ?, ?, 'text', NOW())
                ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = NOW()
            ");
            $st->execute([$key, $razorpayPlanId]);
        } catch (\Throwable $e) {
            error_log('SaaSBillingService::storeRazorpayPlanMapping error: ' . $e->getMessage());
        }
    }
}
