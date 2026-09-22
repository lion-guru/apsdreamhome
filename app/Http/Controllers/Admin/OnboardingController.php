<?php
/**
 * Admin Onboarding Wizard — Multi-step setup: Company → Team → Colonies → Plots → Leads
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class OnboardingController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    private function pdo(): \PDO
    {
        return $this->db->getConnection();
    }

    /**
     * Step 1: Company Setup
     */
    public function index()
    {
        $this->requireAdmin();
        $step = max(1, (int)($_GET['step'] ?? 1));
        $completedSteps = $this->getCompletedSteps();

        return $this->render('admin/onboarding/index', [
            'page_title' => 'Setup Wizard',
            'page_description' => 'Configure your APS Dream Home platform in 5 steps',
            'current_step' => $step,
            'completed_steps' => $completedSteps,
            'steps' => [
                1 => ['key' => 'company', 'title' => 'Company Setup', 'icon' => 'fa-building', 'description' => 'Company name, address, GSTIN, logo, primary colors'],
                2 => ['key' => 'team', 'title' => 'Team & Roles', 'icon' => 'fa-users', 'description' => 'Admin users, managers, sales team, roles & permissions'],
                3 => ['key' => 'colonies', 'title' => 'Colonies & Projects', 'icon' => 'fa-map-marked-alt', 'description' => 'Add colonies, phases, amenities, location details'],
                4 => ['key' => 'plots', 'title' => 'Plots & Inventory', 'icon' => 'fa-th-large', 'description' => 'Plot cutting, pricing, PLC, availability status'],
                5 => ['key' => 'leads', 'title' => 'Lead Sources', 'icon' => 'fa-user-plus', 'description' => 'IndiaMart, JustDial, Meta, website forms, referral channels'],
            ],
        ]);
    }

    /**
     * Save step data and return next step
     */
    public function saveStep()
    {
        $this->requireAdmin();
        $input = $this->getPostInput();
        $step = max(1, (int)($input['step'] ?? 1));
        $data = $input['data'] ?? [];

        try {
            $db = $this->pdo();
            $tid = (int)$this->tenantId();

            switch ($step) {
                case 1: // Company
                    $this->saveCompany($db, $tid, $data);
                    break;
                case 2: // Team
                    $this->saveTeam($db, $tid, $data);
                    break;
                case 3: // Colonies
                    $this->saveColonies($db, $tid, $data);
                    break;
                case 4: // Plots
                    $this->savePlots($db, $tid, $data);
                    break;
                case 5: // Leads
                    $this->saveLeadSources($db, $tid, $data);
                    break;
            }

            // Mark step as completed
            $this->markStepCompleted($step);

            $nextStep = min($step + 1, 5);
            $isComplete = $step >= 5;

            $this->json([
                'success' => true,
                'step' => $step,
                'next_step' => $nextStep,
                'is_complete' => $isComplete,
                'redirect' => $isComplete ? '/admin/erp' : '/admin/onboarding?step=' . $nextStep,
            ]);
        } catch (\Throwable $e) {
            error_log('OnboardingController::saveStep error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Complete onboarding
     */
    public function complete()
    {
        $this->requireAdmin();
        $this->markAllStepsCompleted();
        $this->json(['success' => true, 'redirect' => '/admin/erp']);
    }

    /**
     * Get readiness metrics for live dashboard
     */
    public function readiness()
    {
        $this->requireAdmin();
        $db = $this->pdo();
        $tid = (int)$this->tenantId();

        $metrics = [
            'company' => (bool)$db->fetchOne("SELECT 1 FROM company_settings WHERE tenant_id = ? LIMIT 1", [$tid]),
            'team' => (int)$db->fetchOne("SELECT COUNT(*) as c FROM users WHERE tenant_id = ? AND role IN ('admin','manager','sales_manager')", [$tid])['c'] > 0,
            'colonies' => (int)$db->fetchOne("SELECT COUNT(*) as c FROM colonies WHERE tenant_id = ? AND status = 'active'", [$tid])['c'] > 0,
            'plots' => (int)$db->fetchOne("SELECT COUNT(*) as c FROM plots WHERE tenant_id = ? AND status = 'available'", [$tid])['c'] > 0,
            'leads' => (int)$db->fetchOne("SELECT COUNT(*) as c FROM lead_sources WHERE is_active = 1", [])['c'] > 0,
        ];

        $score = (int)(array_sum(array_map('intval', $metrics)) / count($metrics) * 100);

        $this->json([
            'success' => true,
            'metrics' => $metrics,
            'score' => $score,
            'ready' => $score >= 80,
        ]);
    }

    private function getCompletedSteps(): array
    {
        $db = $this->pdo();
        $tid = (int)$this->tenantId();

        return [
            1 => (bool)$db->fetchOne("SELECT 1 FROM company_settings WHERE tenant_id = ? LIMIT 1", [$tid]),
            2 => (int)$db->fetchOne("SELECT COUNT(*) as c FROM users WHERE tenant_id = ? AND role IN ('admin','manager','sales_manager')", [(int)$this->tenantId()])['c'] > 0,
            3 => (int)$db->fetchOne("SELECT COUNT(*) as c FROM colonies WHERE tenant_id = ? AND status = 'active'", [$tid])['c'] > 0,
            4 => (int)$db->fetchOne("SELECT COUNT(*) as c FROM plots WHERE tenant_id = ? AND status = 'available'", [$tid])['c'] > 0,
            5 => (int)$db->fetchOne("SELECT COUNT(*) as c FROM lead_sources WHERE is_active = 1", [])['c'] > 0,
        ];
    }

    private function saveCompany($db, int $tid, array $data): void
    {
        $sql = "
            INSERT INTO company_settings (tenant_id, company_name, gstin, address, city, state, pincode, phone, email, logo_url, primary_color, secondary_color, created_at, updated_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
            ON DUPLICATE KEY UPDATE
                company_name = VALUES(company_name), gstin = VALUES(gstin), address = VALUES(address),
                city = VALUES(city), state = VALUES(state), pincode = VALUES(pincode),
                phone = VALUES(phone), email = VALUES(email), logo_url = VALUES(logo_url),
                primary_color = VALUES(primary_color), secondary_color = VALUES(secondary_color),
                updated_at = NOW()
        ";
        $db->execute($sql, [
            $tid,
            $data['company_name'] ?? '',
            $data['gstin'] ?? '',
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['pincode'] ?? '',
            $data['phone'] ?? '',
            $data['email'] ?? '',
            $data['logo_url'] ?? '',
            $data['primary_color'] ?? '#0a192f',
            $data['secondary_color'] ?? '#d4af37',
        ]);
    }

    private function saveTeam($db, int $tid, array $data): void
    {
        $users = $data['users'] ?? [];
        foreach ($users as $user) {
            if (empty($user['email'])) continue;
            $password = $user['password'] ?? 'Aps@2026';
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $db->execute("
                INSERT INTO users (tenant_id, name, email, phone, password, role, status, created_at, updated_at)
                VALUES (?,?,?,?,?,?, 'active', NOW(), NOW())
                ON DUPLICATE KEY UPDATE name = VALUES(name), role = VALUES(role), updated_at = NOW()
            ", [$tid, $user['name'] ?? '', $user['email'], $user['phone'] ?? '', $hashed, $user['role'] ?? 'manager']);
        }
    }

    private function saveColonies($db, int $tid, array $data): void
    {
        $colonies = $data['colonies'] ?? [];
        foreach ($colonies as $colony) {
            if (empty($colony['name'])) continue;
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $colony['name']));
            $db->execute("
                INSERT INTO colonies (tenant_id, name, slug, address, city, state, pincode, latitude, longitude, status, created_at, updated_at)
                VALUES (?,?,?,?,?,?,?,?,?, 'active', NOW(), NOW())
                ON DUPLICATE KEY UPDATE address = VALUES(address), city = VALUES(city), status = VALUES(status), updated_at = NOW()
            ", [$tid, $colony['name'], $slug, $colony['address'] ?? '', $colony['city'] ?? '', $colony['state'] ?? '', $colony['pincode'] ?? '', $colony['latitude'] ?? null, $colony['longitude'] ?? null]);
        }
    }

    private function savePlots($db, int $tid, array $data): void
    {
        $plots = $data['plots'] ?? [];
        foreach ($plots as $plot) {
            if (empty($plot['plot_number']) || empty($plot['colony_id'])) continue;
            $db->execute("
                INSERT INTO plots (tenant_id, colony_id, plot_number, block, area_sqft, width_ft, length_ft, facing, corner_plot, base_price_per_sqft, status, created_at, updated_at)
                VALUES (?,?,?,?,?,?,?,?,?,?, 'available', NOW(), NOW())
                ON DUPLICATE KEY UPDATE block = VALUES(block), area_sqft = VALUES(area_sqft), base_price_per_sqft = VALUES(base_price_per_sqft), updated_at = NOW()
            ", [$tid, $plot['colony_id'], $plot['plot_number'], $plot['block'] ?? '', $plot['area_sqft'] ?? 0, $plot['width_ft'] ?? 0, $plot['length_ft'] ?? 0, $plot['facing'] ?? '', (int)($plot['corner_plot'] ?? 0), $plot['base_price_per_sqft'] ?? 0]);
        }
    }

    private function saveLeadSources($db, int $tid, array $data): void
    {
        $sources = $data['sources'] ?? [];
        foreach ($sources as $source) {
            if (empty($source['name'])) continue;
            $db->execute("
                INSERT INTO lead_sources (name, description, is_active, color, icon, sort_order)
                VALUES (?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE description = VALUES(description), is_active = VALUES(is_active), updated_at = NOW()
            ", [$source['name'], $source['description'] ?? '', (int)($source['is_active'] ?? 1), $source['color'] ?? '#3b82f6', $source['icon'] ?? 'fa-globe', $source['sort_order'] ?? 0]);
        }
    }

    private function markStepCompleted(int $step): void
    {
        $db = $this->pdo();
        $tid = (int)$this->tenantId();
        $key = 'onboarding_step_' . $step;
        $db->execute("
            INSERT INTO onboarding_progress (tenant_id, step_key, completed_at)
            VALUES (?,?, NOW())
            ON DUPLICATE KEY UPDATE completed_at = NOW()
        ", [$tid, $key]);
    }

    private function markAllStepsCompleted(): void
    {
        $db = $this->pdo();
        $tid = (int)$this->tenantId();
        for ($i = 1; $i <= 5; $i++) {
            $this->markStepCompleted($i);
        }
    }
}