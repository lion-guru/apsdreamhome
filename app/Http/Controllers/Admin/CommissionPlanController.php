<?php
namespace App\Http\Controllers\Admin;

use App\Services\CommissionPlanService;
use App\Services\CommissionSimulator;

class CommissionPlanController extends AdminController
{
    /** @var CommissionPlanService */
    private $planService;

    /** @var CommissionSimulator */
    private $simulator;

    public function __construct()
    {
        parent::__construct();
        $pdo = \App\Core\Database\Database::getInstance()->getConnection();
        $this->planService = new CommissionPlanService($pdo);
        $this->simulator = new CommissionSimulator($pdo);
    }

    /* ── LIST ── */
    public function index()
    {
        $this->requireAdmin();
        $plans = $this->planService->getAllPlans();
        $activePlan = $this->planService->getActivePlan();
        $stats = $this->planService->getStats();
        $coherence = $this->planService->getRankRateCoherence();
        $this->render('admin/commission/plans/index', compact('plans', 'activePlan', 'stats', 'coherence'));
    }

    /* ── CREATE ── */
    public function create()
    {
        $this->requireAdmin();
        $this->render('admin/commission/plans/create', []);
    }

    /* ── STORE ── */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        if (empty($_POST['plan_name']) || empty($_POST['plan_code'])) {
            $_SESSION['error'] = 'Plan name and code are required';
            $this->redirect('/admin/commission-plans/create');
            return;
        }

        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $this->planService->createPlan($_POST, $userId);
            $_SESSION['success'] = "Commission plan '{$_POST['plan_name']}' created as v1 with 7 default levels";
            $this->redirect('/admin/commission-plans');
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::store error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to create plan: ' . $e->getMessage();
            $this->redirect('/admin/commission-plans/create');
        }
    }

    /* ── EDIT ── */
    public function edit($id)
    {
        $this->requireAdmin();
        $plan = $this->planService->getPlanById((int)$id);
        if (!$plan) {
            $_SESSION['error'] = 'Plan not found';
            $this->redirect('/admin/commission-plans');
            return;
        }
        $versions = $this->planService->getPlanVersions($plan['plan_code']);
        $this->render('admin/commission/plans/edit', compact('plan', 'versions'));
    }

    /* ── UPDATE ── */
    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $this->planService->updatePlan((int)$id, $_POST, $userId);
            $_SESSION['success'] = 'Plan updated successfully';
            $this->redirect('/admin/commission-plans/edit/' . $id);
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::update error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update: ' . $e->getMessage();
            $this->redirect('/admin/commission-plans/edit/' . $id);
        }
    }

    /* ── CLONE AS NEW VERSION ── */
    public function cloneVersion($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $overrides = [
                'effective_date' => $_POST['effective_date'] ?? date('Y-m-d'),
                'description' => $_POST['description'] ?? null,
            ];
            $newId = $this->planService->clonePlanAsNewVersion((int)$id, $overrides, $userId);
            $_SESSION['success'] = 'New version created from this plan';
            $this->redirect('/admin/commission-plans/edit/' . $newId);
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::cloneVersion error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to clone: ' . $e->getMessage();
            $this->redirect('/admin/commission-plans/edit/' . $id);
        }
    }

    /* ── ACTIVATE ── */
    public function activate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $this->planService->activatePlan((int)$id, $userId);
            $_SESSION['success'] = 'Commission plan activated';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/commission-plans');
    }

    /* ── DEACTIVATE ── */
    public function deactivate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $this->planService->deactivatePlan((int)$id, $userId);
            $_SESSION['success'] = 'Commission plan deactivated';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/commission-plans');
    }

    /* ── DELETE ── */
    public function delete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $this->planService->deletePlan((int)$id, $userId);
            $_SESSION['success'] = 'Plan deleted';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/commission-plans');
    }

    /* ── HISTORY (Audit Log) ── */
    public function history()
    {
        $this->requireAdmin();
        $planId = (int)($_GET['plan_id'] ?? 0);
        $auditLog = $planId
            ? $this->planService->getAuditLog($planId)
            : $this->planService->getFullAuditLog();
        $plans = $this->planService->getAllPlans();
        $this->render('admin/commission/plans/history', compact('auditLog', 'plans', 'planId'));
    }

    /* ── COMPARE ── */
    public function compare()
    {
        $this->requireAdmin();
        $planIdA = (int)($_GET['plan_a'] ?? 0);
        $planIdB = (int)($_GET['plan_b'] ?? 0);
        $comparison = null;
        if ($planIdA && $planIdB) {
            $comparison = $this->planService->comparePlans($planIdA, $planIdB);
        }
        $plans = $this->planService->getAllPlans();
        $this->render('admin/commission/plans/compare', compact('comparison', 'plans', 'planIdA', 'planIdB'));
    }

    /* ── SIMULATOR (Advanced What-If) ── */
    public function simulator()
    {
        $this->requireAdmin();
        $plans = $this->planService->getAllPlans();
        $activePlan = $this->planService->getActivePlan();
        $result = null;

        // Fetch saved presets
        $presets = [];
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = \App\Core\Middleware\TenantContext::getId();
            $stmt = $pdo->prepare("SELECT * FROM commission_simulator_presets WHERE tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$tid]);
            $presets = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::simulator presets error: ' . $e->getMessage());
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $saleAmount = (float)($_POST['sale_amount'] ?? 1500000);
            $planId = (int)($_POST['plan_id'] ?? ($activePlan['id'] ?? 0));
            $rankIdx = (int)($_POST['rank_index'] ?? 0);

            $mode = $_POST['sim_mode'] ?? 'single';

            if ($mode === 'compare') {
                $planIdB = (int)($_POST['plan_id_b'] ?? 0);
                $result = $this->simulator->comparePlans($saleAmount, $planId, $planIdB, $rankIdx);
            } elseif ($mode === 'bulk') {
                $result = $this->simulator->bulkSimulate($saleAmount, $planId);
            } elseif ($mode === 'sensitivity') {
                $result = $this->simulator->sensitivityAnalysis($planId, $rankIdx);
            } elseif ($mode === 'referral_sweep') {
                $result = $this->simulator->referralSweep(
                    (float)($_POST['candidate_pct'] ?? 2.0),
                    max(1, (int)($_POST['window_days'] ?? 90))
                );
                $result['sim_kind'] = 'referral_sweep';
            } elseif ($mode === 'wallet_sweep') {
                $result = $this->simulator->walletSweep(
                    (float)($_POST['candidate_l1'] ?? 20.0),
                    (float)($_POST['candidate_l2'] ?? 5.0),
                    max(1, (int)($_POST['window_days'] ?? 90))
                );
                $result['sim_kind'] = 'wallet_sweep';
            } else {
                $result = $this->simulator->simulateSale($saleAmount, $planId, $rankIdx);
            }
        }

        $this->render('admin/commission/plans/simulator', compact('plans', 'activePlan', 'result', 'presets'));
    }

    /* ── APPLY WALLET SWEEP (bulk package pct update, audited) ── */
    public function applyWalletPct()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/admin/commission-plans/simulator');
            return;
        }

        try {
            $l1 = min(max((float)($_POST['candidate_l1'] ?? 20.0), 0.0), 30.0);
            $l2 = min(max((float)($_POST['candidate_l2'] ?? 5.0), 0.0), max(0.0, 30.0 - $l1));
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $before = $pdo->query(
                "SELECT COUNT(*) AS n, GROUP_CONCAT(DISTINCT referral_pct_l1) AS l1s, GROUP_CONCAT(DISTINCT referral_pct_l2) AS l2s
                 FROM wallet_activation_packages WHERE is_active = 1"
            )->fetch(\PDO::FETCH_ASSOC) ?: [];
            $count = (int)($before['n'] ?? 0);
            if ($count <= 0) {
                $_SESSION['error'] = 'No active wallet packages to update.';
                $this->redirect('/admin/commission-plans/simulator');
                return;
            }
            $stmt = $pdo->prepare(
                "UPDATE wallet_activation_packages SET referral_pct_l1 = ?, referral_pct_l2 = ?, updated_at = NOW() WHERE is_active = 1"
            );
            $stmt->execute([$l1, $l2]);
            $cfg = \App\Services\ServiceConfigService::getInstance();
            $cfg->auditManual(
                'wallet_packages',
                'referral_pct_l1/l2 (bulk)',
                'L1 in (' . ($before['l1s'] ?? '?') . ') L2 in (' . ($before['l2s'] ?? '?') . ") on {$count} package(s)",
                "L1={$l1}% L2={$l2}% on {$count} package(s)",
                'Applied from commission simulator wallet sweep'
            );
            $_SESSION['success'] = "Applied L1={$l1}% L2={$l2}% to {$count} package(s). Past activations unchanged.";
            $this->redirect('/admin/commission-plans/simulator');
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::applyWalletPct error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to apply: ' . $e->getMessage();
            $this->redirect('/admin/commission-plans/simulator');
        }
    }

    /* ── PRESETS: Save ── */
    public function savePreset()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $simMode = $_POST['sim_mode'] ?? 'single';
        $paramsJson = $_POST['params_json'] ?? '{}';

        if (!$name) {
            $_SESSION['error'] = 'Preset name is required';
            $this->redirect('/admin/commission-plans/simulator');
            return;
        }

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = \App\Core\Middleware\TenantContext::getId();
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;

            $stmt = $pdo->prepare(
                "INSERT INTO commission_simulator_presets (name, description, sim_mode, params_json, tenant_id, created_by) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $description, $simMode, $paramsJson, $tid, $userId]);

            $_SESSION['success'] = "Preset '{$name}' saved";
        } catch (\Throwable $e) {
            if ($e->getCode() === 23000) {
                $_SESSION['error'] = 'A preset with this name already exists';
            } else {
                error_log('CommissionPlanController::savePreset error: ' . $e->getMessage());
                $_SESSION['error'] = 'Failed to save preset: ' . $e->getMessage();
            }
        }
        $this->redirect('/admin/commission-plans/simulator');
    }

    /* ── PRESETS: Load (AJAX) ── */
    public function getPreset($id)
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = \App\Core\Middleware\TenantContext::getId();
            $stmt = $pdo->prepare("SELECT * FROM commission_simulator_presets WHERE id = ? AND tenant_id = ? LIMIT 1");
            $stmt->execute([(int)$id, $tid]);
            $preset = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$preset) {
                echo json_encode(['success' => false, 'message' => 'Preset not found']);
                return;
            }

            echo json_encode(['success' => true, 'preset' => $preset]);
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::getPreset error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to load preset']);
        }
    }

    /* ── PRESETS: Delete ── */
    public function deletePreset($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        header('Content-Type: application/json');

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = \App\Core\Middleware\TenantContext::getId();
            $stmt = $pdo->prepare("DELETE FROM commission_simulator_presets WHERE id = ? AND tenant_id = ?");
            $stmt->execute([(int)$id, $tid]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Preset deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Preset not found']);
            }
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::deletePreset error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to delete preset']);
        }
    }

    /* ── CALCULATOR ── */
    public function calculator()
    {
        $this->requireAdmin();
        $plans = $this->planService->getAllPlans();
        $activePlan = $this->planService->getActivePlan();
        $levels = $activePlan ? $this->planService->getLevelsForPlan($activePlan['id']) : [];
        $csrf_token = $_SESSION['csrf_token'] ?? '';
        $this->render('admin/commission/plans/calculator', compact('plans', 'activePlan', 'levels', 'csrf_token'));
    }

    /* ── AJAX: Load Levels ── */
    public function getLevels()
    {
        $this->requireAdmin();
        $planId = (int)($_GET['plan_id'] ?? 0);
        try {
            $levels = $this->planService->getLevelsForPlan($planId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'levels' => $levels]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /* ── AJAX: Simulate ── */
    public function ajaxSimulate()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $saleAmount = (float)($_GET['sale_amount'] ?? 1500000);
        $planId = (int)($_GET['plan_id'] ?? 0);
        $rankIdx = (int)($_GET['rank_index'] ?? 0);

        if (!$planId) {
            $activePlan = $this->planService->getActivePlan();
            $planId = $activePlan['id'] ?? 0;
        }

        $result = $this->simulator->simulateSale($saleAmount, $planId, $rankIdx);
        echo json_encode($result);
    }

    /* ── AI PLAN COACH ──────────────────────────────────────────────── */

    /**
     * AI Plan Coach: answer questions about the commission form, or design a whole
     * plan for the admin. Domain-scoped prompt, chat endpoint rate-limited, no keys leaked.
     */
    public function aiCoach()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        if (!is_array($input)) {
            parse_str($raw, $parsedPost);
            $input = $parsedPost;
        }

        $message = trim((string)($input['message'] ?? ''));
        $mode    = (string)($input['mode'] ?? 'chat');
        $state   = [];
        $stateJson = (string)($input['state'] ?? '');
        if ($stateJson !== '') {
            $decoded = json_decode($stateJson, true);
            if (is_array($decoded)) {
                $state = $decoded;
            }
        }

        if ($message === '') {
            $this->jsonResponse(['success' => false, 'error' => 'Kuch likhein — question type karein.'], 400);
            return;
        }
        if (mb_strlen($message) > 800) {
            $message = mb_substr($message, 0, 800);
        }

        if ($mode === 'design') {
            $prompt = $this->buildDesignPrompt();
            $engine = 'none';
            try {
                $result = \App\Services\AI\FreeAIEngines::getInstance()->generate(
                    $prompt . "\n\nRequest: " . $message . "\n\nJSON:",
                    ['max_tokens' => 1100, 'temperature' => 0.4],
                    'chat'
                );
                $text = trim((string)($result['text'] ?? ''));
                $engine = $result['engine'] ?? 'unknown';
            } catch (\Throwable $e) {
                error_log('aiCoach design error: ' . $e->getMessage());
                $text = '';
            }
            $suggestion = $this->parseDesignSuggestion($text);
            if ($suggestion !== null) {
                $this->jsonResponse(['success' => true, 'suggestion' => $suggestion, 'engine' => $engine]);
                return;
            }
            $this->jsonResponse([
                'success' => true,
                'answer'  => $text,
                'note'    => 'Design output JSON format me nahi aaya — manu tab tak suggested values help text se set karo.',
                'engine'  => $engine,
            ]);
            return;
        }

        $text = '';
        $engine = null;
        try {
            $prompt = $this->buildCoachPrompt($message, $state);
            $result = \App\Services\AI\FreeAIEngines::getInstance()->generate(
                $prompt,
                ['max_tokens' => 650, 'temperature' => 0.7],
                'chat'
            );
            $text = trim((string)($result['text'] ?? ''));
            $engine = $result['engine'] ?? null;
        } catch (\Throwable $e) {
            error_log('aiCoach error: ' . $e->getMessage());
        }

        if ($text === '') {
            $text = "AI abhi unavailable hai — har field ke neeche help text hai, wahan se padho. Ya 2 minute baad try karo.";
        }
        $this->jsonResponse(['success' => true, 'answer' => $text, 'engine' => $engine]);
    }

    /** System prompt for the free-form coach chat. */
    private function buildCoachPrompt(string $message, array $state): string
    {
        $stateText = $this->renderState($state);

        return <<<TXT
You are "APS Plan Coach" — an AI expert inside the APS Dream Home admin panel. The admin (CEO / network owner) is creating a commission plan for the real-estate plot business and is NOT an expert — answer like you're over WhatsApp, in Hinglish (Hindi+English mix), SHORT (max 3-4 sentences), and ALWAYS give a CONCRETE recommendation when asked "kya rakhein / kya karein / kya sahi hai".

Company context:
- Townships (Gorakhpur / Kushinagar / Lucknow / Varanasi): Suryoday Colony & Raghunath Nagri (~250 booked each), Budh Bihar Colony, Lakeview Residency.
- Rank ladder is fixed: Associate → Sr. Associate → BDM → Sr. BDM → Vice President → President → Site Manager.
- The trigger is a booking/sale — every booking pays commission from THIS plan's numbers.
- Plan is saved as draft → activate later (Plans List → Activate button). When active, MLM engine snapshots THIS plan; every booking's direct/team/bonus math uses these exact values.
- Cap math: Global Cap 20% of ₹15,00,000 = ₹3,00,000 maximum total commission the company pays on that sale, no matter what slabs say.

Field dictionary (use exactly these names):
- plan_name: display name (e.g. "Standard Plan v2 — April 2026")
- plan_code: UPPERCASE unique code (e.g. STD, 2026-RE) — audit/logging uses this
- plan_type: hybrid (recommended: direct + team overrides), unilevel, binary, matrix
- effective_date: payouts switch to new plan on this date (format YYYY-MM-DD)
- description: one-line why this plan changed
- global_cap_pct: HARD cap on TOTAL commission cost per sale. Typical 10-25. Default 20.
- track_a_pct: slab-difference (upper-slab minus junior-slab) differential paid to senior upline on a sale. Typical 10-20. Must be <= global_cap_pct. Default 15.
- track_b_pct: roll-up override paid to the upline manager on the team's sale, typically 1-5. Default 3.
- track_c_pct: milestone/GBV bonus % — one-time booster for crossing GBV. Default 2.
- royalty_pool_pct: company-funded pool shared across network monthly. Default 2.
- same_level_override_gen1: % paid when YOUR direct recruit reaches your same rank. Default 2.
- same_level_override_gen2: same for gen-2 recruit, usually half of gen1. Default 1.
- levels[]: 7 rows [name, order, direct_commission, team_commission, level_bonus, matching_bonus, leadership_bonus, performance_bonus, monthly_target(₹ GBV)]
  direct ascending: 5/7/10/12/15/18/20; team ascending: 2/3/4/5/6/7/8; GBV ascending: ₹10L / ₹35L / ₹70L / ₹1.5Cr / ₹3Cr / ₹5Cr / ₹999Cr (₹1,00,00,000 = ₹1 crore)
- monthly_target = GBV (Group Business Volume) — minimum monthly group sales to HOLD the rank; ₹1,00,00,000 = ₹1 crore (Site Manager = ₹5 crore).

If asked to DESIGN a whole plan, recommend exactly this safe starter:
direct 5/7/10/12/15/18/20, team 2/3/4/5/6/7/8, level_bonus 0/2/3/4/5/6/7, matching 0/5/8/10/12/15/18, leadership 0/0/1/2/3/4/5, performance 0/0/0/1/2/3/5, GBV 1000000/3500000/7000000/15000000/30000000/50000000/999999999, global_cap 20, track_a 15, track_b 3, track_c 2, royalty 2, G1 2, G2 1 — and say "activate karne se pehle owner se approve karwa lo".

Current form state (may be partial):
{$stateText}

Question: {$message}
Answer:
TXT;
    }

    /** Prompt for design mode — output must be parseable JSON. */
    private function buildDesignPrompt(): string
    {
        return <<<TXT
You are "APS Plan Coach". Design a NEW compensation plan for APS Dream Home real-estate plot business. Respond with EXACTLY ONE valid JSON object — no commentary, no markdown fences, no text before or after.

Schema (every key required):
{"global_cap_pct":20, "track_a_pct":15, "track_b_pct":3, "track_c_pct":2, "royalty_pool_pct":2, "same_level_override_gen1":2, "same_level_override_gen2":1, "plan_name":"Standard Plan v1", "plan_type":"hybrid", "effective_date":"YYYY-MM-DD", "description":"one line", "levels":[["Associate",1,5,2,0,0,0,0,1000000],["Sr. Associate",2,7,3,2,5,0,0,3500000],["BDM",3,10,4,3,8,1,0,7000000],["Sr. BDM",4,12,5,4,10,2,1,15000000],["Vice President",5,15,6,5,12,3,2,30000000],["President",6,18,7,6,15,4,3,50000000],["Site Manager",7,20,8,7,18,5,5,999999999]]}

Constraints:
- global_cap_pct: number 5..30 (float 0.5 step)
- track_a_pct: number <= global_cap_pct
- track_b_pct, track_c_pct: number 0..10
- royalty_pool_pct, same_level_override_gen1, same_level_override_gen2: number 0..10
- effective_date: YYYY-MM-DD format
- levels: exactly 7 rows, ascending order 1..7, ascending direct_commission, ascending team_commission, ascending monthly_target (numbers)
- plan_code NOT required

Request:

TXT;
    }

    /** Render the current form state compactly for the AI. */
    private function renderState(array $state): string
    {
        if (!$state) {
            return "(blank form — admin hasn't touched anything yet)";
        }
        $lines = [];
        foreach ($state as $k => $v) {
            $k = (string)$k;
            if (is_array($v)) {
                $pairs = [];
                foreach ($v as $i => $row) {
                    if (is_array($row)) {
                        $pairs[] = "levels[{$i}][" . implode(',', $row) . "]";
                    }
                }
                $lines[] = $k . ': ' . implode(' | ', $pairs);
            } elseif (is_string($v)) {
                $lines[] = "{$k}: " . (mb_strlen($v) > 120 ? mb_substr($v, 0, 120) . '…' : $v);
            }
        }
        return $lines ? implode("\n", $lines) : '(blank form)';
    }

    /**
     * Parse the design-mode JSON output. Lenient on shape, strict on values.
     * Returns array keyed by form names, or null if unusable.
     */
    private function parseDesignSuggestion(string $text): ?array
    {
        $text = trim($text);
        // strip code fences if the model added them anyway
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/im', '', $text);
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        $json = substr($text, $start, $end - $start + 1);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return null;
        }

        $numeric = ['global_cap_pct', 'track_a_pct', 'track_b_pct', 'track_c_pct', 'royalty_pool_pct', 'same_level_override_gen1', 'same_level_override_gen2'];
        $suggestion = [];
        foreach ($numeric as $key) {
            if (isset($data[$key]) && is_numeric($data[$key])) {
                $suggestion[$key] = max(0, min(100, (float)$data[$key]));
            }
        }
        if (isset($data['plan_name']) && is_string($data['plan_name'])) {
            $suggestion['plan_name'] = mb_substr(trim($data['plan_name']), 0, 120);
        }
        if (isset($data['plan_type']) && is_string($data['plan_type'])) {
            $valid = ['hybrid', 'unilevel', 'binary', 'matrix'];
            $type = strtolower(trim($data['plan_type']));
            $suggestion['plan_type'] = in_array($type, $valid, true) ? $type : 'hybrid';
        }
        if (isset($data['effective_date']) && is_string($data['effective_date'])) {
            $ts = strtotime($data['effective_date'] . ' 00:00:00');
            $suggestion['effective_date'] = $ts ? date('Y-m-d', $ts) : date('Y-m-d');
        }
        if (isset($data['description']) && is_string($data['description'])) {
            $suggestion['description'] = mb_substr(trim($data['description']), 0, 200);
        }

        if (isset($data['levels']) && is_array($data['levels'])) {
            $levels = [];
            foreach (array_slice($data['levels'], 0, 7) as $i => $row) {
                if (!is_array($row)) {
                    continue;
                }
                $out = [];
                for ($j = 0; $j <= 8; $j++) {
                    $v = $row[$j] ?? null;
                    if ($j === 0) {
                        $out[] = mb_substr(preg_replace('/[^A-Za-z0-9\.\-\s]/', '', (string)$v), 0, 60);
                    } elseif ($j === 1) {
                        $out[] = max(1, min(30, (int)$v));
                    } else {
                        $out[] = is_numeric($v) ? max(0, min($j === 8 ? 1000000000 : 100, (float)$v)) : 0;
                    }
                }
                $levels[] = $out;
            }
            if ($levels) {
                $suggestion['levels'] = $levels;
            }
        }

        return $suggestion ?: null;
    }
}
