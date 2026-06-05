<?php

namespace App\Http\Controllers\Admin;

use App\Services\Experimentation\ExperimentService;
use Throwable;

/**
 * Admin UI + JSON API for the A/B testing framework.
 *
 * Routes (all admin-only, except /api/ab/variant which is public AJAX):
 *   GET  /admin/experiments              - list
 *   GET  /admin/experiments/create       - new form
 *   POST /admin/experiments/store        - persist
 *   GET  /admin/experiments/{id}         - results dashboard (chart + chi-square)
 *   POST /admin/experiments/{id}/end     - mark ended (winner optional)
 *   POST /admin/experiments/{id}/delete  - delete experiment + events
 *   GET  /api/ab/variant/{name}          - AJAX variant lookup
 *   POST /api/ab/track                   - AJAX event tracker
 */
class ExperimentController extends AdminController
{
    /** @var ExperimentService */
    private $svc;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->svc = new ExperimentService();
        } catch (Throwable $e) {
            error_log('ExperimentController init failed: ' . $e->getMessage());
            $this->svc = null;
        }
    }

    /**
     * Most API endpoints accept POST with CSRF; the JS-fired `/api/ab/track`
     * is exempt so any visitor (logged-in or not) can ship click data.
     */
    protected function skipCsrfProtection(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($uri, '/api/ab/') !== false;
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $experiments = $this->svc ? $this->svc->listExperiments() : [];
        } catch (Throwable $e) {
            error_log('ExperimentController::index: ' . $e->getMessage());
            $experiments = [];
            $this->setFlash('error', 'Failed to load experiments: ' . $e->getMessage());
        }

        $this->render('admin/experiments/index', [
            'page_title'  => 'A/B Experiments',
            'experiments' => $experiments,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $this->render('admin/experiments/create', [
            'page_title' => 'Create A/B Experiment',
            'csrf_token' => $this->getCsrfToken(),
        ]);
    }

    public function store()
    {
        $this->requireAdmin();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $traffic = max(1, min(100, (int) ($_POST['traffic_allocation'] ?? 100)));

        // Two input styles supported:
        //   1) variants[][name] + variants[][weight]  (form repeater)
        //   2) variants_json  (raw JSON string)
        $variants = [];
        if (!empty($_POST['variants']) && is_array($_POST['variants'])) {
            foreach ($_POST['variants'] as $v) {
                $vn = trim($v['name'] ?? '');
                if ($vn === '') continue;
                $variants[] = [
                    'name'   => $vn,
                    'weight' => max(1, (int) ($v['weight'] ?? 50)),
                ];
            }
        } elseif (!empty($_POST['variants_json'])) {
            $decoded = json_decode($_POST['variants_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $v) {
                    if (!empty($v['name'])) {
                        $variants[] = [
                            'name'   => trim($v['name']),
                            'weight' => max(1, (int) ($v['weight'] ?? 50)),
                        ];
                    }
                }
            }
        }

        // Validation
        if ($name === '' || !preg_match('/^[a-z0-9_\-]+$/i', $name)) {
            $this->setFlash('error', 'Experiment name is required and must be alphanumeric (with - or _).');
            $this->redirect('/admin/experiments/create');
            return;
        }
        if (count($variants) < 2) {
            $this->setFlash('error', 'At least 2 variants are required.');
            $this->redirect('/admin/experiments/create');
            return;
        }

        try {
            $id = $this->svc->createExperiment($name, $variants, $traffic, $description);
            $this->setFlash('success', "Experiment '{$name}' created (ID #{$id}).");
            $this->redirect('/admin/experiments/' . $id);
        } catch (Throwable $e) {
            error_log('ExperimentController::store: ' . $e->getMessage());
            $msg = $e->getMessage();
            if (stripos($msg, 'duplicate') !== false || stripos($msg, '1062') !== false) {
                $msg = "An experiment named '{$name}' already exists.";
            }
            $this->setFlash('error', 'Failed to create experiment: ' . $msg);
            $this->redirect('/admin/experiments/create');
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        $id = (int) $id;
        $exp = $this->svc ? $this->svc->getExperimentById($id) : null;
        if (!$exp) {
            $this->setFlash('error', 'Experiment not found.');
            $this->redirect('/admin/experiments');
            return;
        }

        try {
            $stats = $this->svc->getStats($exp['name']);
        } catch (Throwable $e) {
            $stats = ['error' => $e->getMessage()];
        }

        $this->render('admin/experiments/show', [
            'page_title' => "Experiment: " . $exp['name'],
            'experiment' => $exp,
            'stats'      => $stats,
            'csrf_token' => $this->getCsrfToken(),
        ]);
    }

    public function end($id)
    {
        $this->requireAdmin();
        $id = (int) $id;
        $exp = $this->svc ? $this->svc->getExperimentById($id) : null;
        if (!$exp) {
            $this->setFlash('error', 'Experiment not found.');
            $this->redirect('/admin/experiments');
            return;
        }

        $winner = trim($_POST['winner'] ?? '') ?: null;

        try {
            $this->svc->endExperiment($exp['name'], $winner);
            $msg = $winner ? "Experiment ended. Winner: {$winner}" : 'Experiment ended.';
            $this->setFlash('success', $msg);
        } catch (Throwable $e) {
            $this->setFlash('error', 'Failed to end experiment: ' . $e->getMessage());
        }
        $this->redirect('/admin/experiments/' . $id);
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $id = (int) $id;
        try {
            $ok = $this->svc && $this->svc->deleteExperiment($id);
            $this->setFlash($ok ? 'success' : 'error', $ok ? 'Experiment deleted.' : 'Failed to delete experiment.');
        } catch (Throwable $e) {
            $this->setFlash('error', 'Delete failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/experiments');
    }

    // ──────────────────────────────────────────────────────────
    //   JSON API
    // ──────────────────────────────────────────────────────────

    /**
     * GET /api/ab/variant/{name}
     * Returns { name, variant, user_id } — public so JS can read it.
     */
    public function getVariant($name)
    {
        $name = (string) $name;
        $userId = $this->resolveUserId();

        try {
            $variant = $this->svc ? $this->svc->getVariant($name, $userId) : null;
        } catch (Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }

        // Persist into session so subsequent page loads stay consistent
        if ($variant !== null) {
            if (!isset($_SESSION['experiments']) || !is_array($_SESSION['experiments'])) {
                $_SESSION['experiments'] = [];
            }
            $_SESSION['experiments'][$name] = $variant;
        }

        return $this->jsonResponse([
            'success'    => true,
            'experiment' => $name,
            'variant'    => $variant,
            'user_id'    => $userId,
        ]);
    }

    /**
     * POST /api/ab/track
     * Body: experiment_name, variant, event_type, metadata?
     */
    public function track()
    {
        // POST body or JSON
        $payload = $_POST;
        if (empty($payload)) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) $payload = $decoded;
        }

        $name    = trim($payload['experiment_name'] ?? $payload['experiment'] ?? '');
        $variant = trim($payload['variant'] ?? '');
        $event   = trim($payload['event_type'] ?? 'click');
        $meta    = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];

        if ($name === '' || $variant === '') {
            return $this->jsonResponse(['success' => false, 'error' => 'experiment_name and variant required'], 400);
        }

        $userId = $this->resolveUserId();

        try {
            $ok = $this->svc && $this->svc->trackEvent($name, $variant, $event, $userId, $meta);
        } catch (Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }

        return $this->jsonResponse(['success' => (bool) $ok]);
    }

    /** Resolve current user (same scheme as ExperimentMiddleware). */
    private function resolveUserId(): int
    {
        if (!empty($_SESSION['user_id']))  return (int) $_SESSION['user_id'];
        if (!empty($_SESSION['admin_id'])) return -1 * (int) $_SESSION['admin_id'];
        if (!isset($_SESSION['_ab_visitor_id'])) {
            $sid = session_id() ?: bin2hex(random_bytes(8));
            $_SESSION['_ab_visitor_id'] = abs(crc32('anon:' . $sid)) % 1000000 + 1000000;
        }
        return (int) $_SESSION['_ab_visitor_id'];
    }
}
