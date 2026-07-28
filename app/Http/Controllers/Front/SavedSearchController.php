<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\SavedSearchService;
use App\Services\EmailService;
use App\Traits\TenantAwareTrait;

/**
 * Front-end controller for saved searches.
 *
 * Phase 56+: complements Phase 44 (admin-saved-searches) by giving
 * customers a friendly, full-featured UI for:
 *   - saving a property search
 *   - listing / executing saved searches
 *   - toggling email alerts
 *   - managing alert history
 */
class SavedSearchController extends BaseController
{
    use TenantAwareTrait;
    private SavedSearchService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SavedSearchService();
    }

    /**
     * GET /user/saved-searches
     * List of saved searches for the logged-in customer.
     */
    public function index($request = null)
    {
        $this->requireCustomerLogin();

        $userId = (int)$_SESSION['user_id'];
        $searches = [];
        $alertLog = [];
        $stats = ['my_searches' => 0, 'public_searches' => 0, 'favorites' => 0, 'alerts_enabled' => 0];

        try {
            $searches = $this->service->getUserSearches($userId, 'user_properties');
            $stats = $this->service->getStats($userId, $_SESSION['role'] ?? 'customer');
            $stats['alerts_enabled'] = count(array_filter($searches, fn($s) => (int)($s['email_alerts'] ?? 0) === 1));
            $alertLog = $this->service->getAlertLog($userId, 25);
        } catch (\Throwable $e) {
            error_log("SavedSearchController::index: " . $e->getMessage());
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/saved_searches', [
            'page_title' => 'Saved Searches - APS Dream Home',
            'page_description' => 'Manage your saved property searches and email alerts',
            'user' => $this->getUser(),
            'searches' => $searches,
            'alertLog' => $alertLog,
            'stats' => $stats,
        ]);
    }

    /**
     * POST /user/saved-searches
     * Save current search filters as a named saved search.
     * Accepts JSON for AJAX / fetch, form-encoded for normal POST.
     */
    public function store($request = null)
    {
        $this->requireCustomerLogin();
        $userId = (int)$_SESSION['user_id'];

        $isAjax = $this->wantsJson();

        // Pull filters from either JSON body or POST
        $payload = $this->readPayload();
        $name = trim($payload['name'] ?? '');
        $filters = $payload['filters'] ?? ($payload['params'] ?? []);
        $emailAlerts = !empty($payload['email_alerts']);
        $description = $payload['description'] ?? null;

        if (is_string($filters)) {
            $decoded = json_decode($filters, true);
            if (is_array($decoded)) $filters = $decoded;
        }
        if (!is_array($filters)) $filters = [];

        if ($name === '' || empty(array_filter($filters, fn($v) => $v !== '' && $v !== null && $v !== 0))) {
            return $this->respond($isAjax, ['success' => false, 'error' => 'Name and at least one filter are required'], '/user/saved-searches');
        }

        try {
            $id = $this->service->saveSearch($userId, $name, $filters, $description, $emailAlerts ? 1 : 0);

            // Hot-path: invalidate cached saved-searches count for this user.
            \App\Services\Cache\HotPathCacheService::invalidateUserSavedSearches($userId);

            if ($isAjax) {
                $this->json(['success' => true, 'id' => $id, 'name' => $name, 'redirect' => '/user/saved-searches']);
                return;
            }
            $this->setFlash('success', 'Search saved! ' . ($emailAlerts ? 'You will receive email alerts when new properties match.' : ''));
        } catch (\Throwable $e) {
            error_log("SavedSearchController::store: " . $e->getMessage());
            return $this->respond($isAjax, ['success' => false, 'error' => 'Failed to save: ' . $e->getMessage()], '/properties');
        }

        $this->redirect('/user/saved-searches');
    }

    /**
     * PUT/POST /user/saved-searches/{id}
     * Update an existing saved search (rename, toggle email alerts, change filters).
     */
    public function update($id, $request = null)
    {
        $this->requireCustomerLogin();
        $userId = (int)$_SESSION['user_id'];
        $isAjax = $this->wantsJson();

        $payload = $this->readPayload();

        try {
            $role = $this->service->resolveUserRole($userId);
            $existing = $this->service->get((int)$id);
            if (!$existing || (int)$existing['user_id'] !== $userId) {
                return $this->respond($isAjax, ['success' => false, 'error' => 'Search not found'], '/user/saved-searches');
            }

            $data = [];
            if (isset($payload['name']) && trim($payload['name']) !== '') {
                $data['name'] = trim($payload['name']);
            }
            if (isset($payload['description'])) {
                $data['description'] = $payload['description'];
            }
            if (isset($payload['filters'])) {
                $f = $payload['filters'];
                if (is_string($f)) {
                    $decoded = json_decode($f, true);
                    if (is_array($decoded)) $f = $decoded;
                }
                if (is_array($f)) $data['filters'] = $f;
            }

            $updated = $this->service->update((int)$id, $userId, $role, $data);

            // Handle email_alerts toggle separately (it's not in the allowed update list)
            if (array_key_exists('email_alerts', $payload)) {
                $this->service->toggleAlerts((int)$id, $userId, !empty($payload['email_alerts']));
            }

            if ($isAjax) {
                $this->json(['success' => $updated, 'id' => (int)$id]);
                return;
            }
            $this->setFlash('success', 'Saved search updated.');
        } catch (\Throwable $e) {
            error_log("SavedSearchController::update: " . $e->getMessage());
            return $this->respond($isAjax, ['success' => false, 'error' => 'Update failed: ' . $e->getMessage()], '/user/saved-searches');
        }

        $this->redirect('/user/saved-searches');
    }

    /**
     * DELETE/POST /user/saved-searches/{id}/delete
     * Delete a saved search.
     */
    public function destroy($id, $request = null)
    {
        $this->requireCustomerLogin();
        $userId = (int)$_SESSION['user_id'];
        $isAjax = $this->wantsJson() || ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'DELETE';

        try {
            $role = $this->service->resolveUserRole($userId);
            $deleted = $this->service->delete((int)$id, $userId, $role);

            // Hot-path: invalidate cached saved-searches count for this user.
            \App\Services\Cache\HotPathCacheService::invalidateUserSavedSearches($userId);

            if ($isAjax) {
                $this->json(['success' => $deleted]);
                return;
            }
            $this->setFlash($deleted ? 'success' : 'error', $deleted ? 'Saved search deleted.' : 'Could not delete that search.');
        } catch (\Throwable $e) {
            error_log("SavedSearchController::destroy: " . $e->getMessage());
            return $this->respond($isAjax, ['success' => false, 'error' => $e->getMessage()], '/user/saved-searches');
        }

        $this->redirect('/user/saved-searches');
    }

    /**
     * GET /user/saved-searches/{id}/execute
     * Run a saved search and render the properties page filtered by it.
     */
    public function execute($id, $request = null)
    {
        $this->requireCustomerLogin();
        $userId = (int)$_SESSION['user_id'];
        $isAjax = $this->wantsJson();

        try {
            $existing = $this->service->get((int)$id);
            if (!$existing || (int)$existing['user_id'] !== $userId) {
                if ($isAjax) {
                    $this->json(['success' => false, 'error' => 'Search not found'], 404);
                    return;
                }
                $this->setFlash('error', 'Saved search not found.');
                $this->redirect('/user/saved-searches');
                return;
            }

            $filters = is_array($existing['filters'] ?? null) ? $existing['filters'] : (json_decode($existing['filters'] ?? '{}', true) ?: []);

            // Optionally support an "open in properties page" mode via ?to=properties
            $to = $_GET['to'] ?? null;
            if ($to === 'properties') {
                $query = http_build_query($filters);
                $this->redirect('/properties?' . $query);
                return;
            }

            $matches = $this->service->matchProperties($filters, 50, 0);
            $this->service->recordRun((int)$id, count($matches));

            if ($isAjax) {
                $this->json([
                    'success' => true,
                    'search' => $existing,
                    'matches' => $matches,
                    'count' => count($matches)
                ]);
                return;
            }

            // HTML mode: render the saved search form/result view
            $this->layout = 'layouts/customer';
            $this->render('pages/user/saved_search_results', [
                'page_title' => 'Search: ' . ($existing['name'] ?? 'Saved') . ' - APS Dream Home',
                'user' => $this->getUser(),
                'search' => $existing,
                'matches' => $matches,
                'count' => count($matches),
            ]);
        } catch (\Throwable $e) {
            error_log("SavedSearchController::execute: " . $e->getMessage());
            if ($isAjax) {
                $this->json(['success' => false, 'error' => $e->getMessage()], 500);
                return;
            }
            $this->setFlash('error', 'Could not run search.');
            $this->redirect('/user/saved-searches');
        }
    }

    /**
     * POST /user/saved-searches/{id}/alerts
     * Toggle email alerts for a saved search.
     */
    public function toggleAlerts($id, $request = null)
    {
        $this->requireCustomerLogin();
        $userId = (int)$_SESSION['user_id'];
        $isAjax = $this->wantsJson();

        $payload = $this->readPayload();
        $enabled = !empty($payload['email_alerts']) || !empty($payload['enabled']);

        try {
            $existing = $this->service->get((int)$id);
            if (!$existing || (int)$existing['user_id'] !== $userId) {
                return $this->respond($isAjax, ['success' => false, 'error' => 'Search not found'], '/user/saved-searches');
            }

            $ok = $this->service->toggleAlerts((int)$id, $userId, $enabled);

            if ($isAjax) {
                $this->json(['success' => $ok, 'email_alerts' => $enabled ? 1 : 0]);
                return;
            }
            $this->setFlash('success', $enabled ? 'Email alerts enabled.' : 'Email alerts disabled.');
        } catch (\Throwable $e) {
            error_log("SavedSearchController::toggleAlerts: " . $e->getMessage());
            return $this->respond($isAjax, ['success' => false, 'error' => $e->getMessage()], '/user/saved-searches');
        }

        $this->redirect('/user/saved-searches');
    }

    /**
     * GET/POST /user/saved-searches/manage-alerts
     * Manage alerts page (list + history + toggle).
     */
    public function manageAlerts($request = null)
    {
        $this->requireCustomerLogin();
        $userId = (int)$_SESSION['user_id'];

        $searches = [];
        $alertLog = [];
        try {
            $searches = $this->service->getUserSearches($userId, 'user_properties');
            $alertLog = $this->service->getAlertLog($userId, 50);
        } catch (\Throwable $e) {
            error_log("SavedSearchController::manageAlerts: " . $e->getMessage());
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/manage_alerts', [
            'page_title' => 'Manage Alerts - APS Dream Home',
            'user' => $this->getUser(),
            'searches' => $searches,
            'alertLog' => $alertLog,
        ]);
    }

    /**
     * GET /api/saved-searches/autocomplete?q=
     * Typeahead endpoint used by the header quick-search bar.
     * Returns top 8 distinct property names + locations + types + plots + projects
     * matching the query.
     */
    public function autocomplete($request = null)
    {
        $this->jsonHeader();
        $q = trim($_GET['q'] ?? '');

        if (strlen($q) < 2) {
            echo json_encode(['success' => true, 'results' => []]);
            return;
        }

        $results = [];
        try {
            $like = '%' . $q . '%';
            $start = $q . '%';
            $stmt = $this->db->prepare("
                (SELECT id, name AS label, 'property' AS type, address AS sub, 1 AS ord FROM user_properties
                 WHERE status='approved' AND name LIKE ? LIMIT 5)
                UNION ALL
                (SELECT id, address AS label, 'address' AS type, name AS sub, 2 AS ord FROM user_properties
                 WHERE status='approved' AND address LIKE ? LIMIT 3)
                UNION ALL
                (SELECT id, name AS label, 'location' AS type, '' AS sub, 3 AS ord FROM cities
                 WHERE name LIKE ? LIMIT 3)
                UNION ALL
                (SELECT 0 AS id, property_type AS label, 'type' AS type, '' AS sub, 4 AS ord FROM user_properties
                 WHERE status='approved' AND property_type LIKE ? AND property_type != ''
                 GROUP BY property_type LIMIT 3)
                UNION ALL
                (SELECT p.id, c.name AS label, 'plot' AS type, CONCAT(c.name, ' - ', p.block) AS sub, 5 AS ord FROM plots p
                 JOIN colonies c ON p.colony_id = c.id
                 WHERE c.is_active = 1 AND p.status = 'available' AND (c.name LIKE ? OR p.block LIKE ?) LIMIT 3)
                UNION ALL
                (SELECT s.id, s.site_name AS label, 'project' AS type, CONCAT(s.city, ', ', s.state) AS sub, 6 AS ord FROM sites s
                 WHERE s.status IN ('active','under_development') AND s.site_name LIKE ? LIMIT 3)
                ORDER BY ord, label
                LIMIT 8
            ");
            $stmt->execute([$like, $like, $start, $start, $like, $like, $start]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Build URL
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            foreach ($rows as $r) {
                $url = $baseUrl . '/properties';
                if ($r['type'] === 'property') $url .= '?q=' . urlencode($r['label']);
                elseif ($r['type'] === 'address') $url .= '?q=' . urlencode($r['label']);
                elseif ($r['type'] === 'location') $url .= '?location=' . urlencode($r['label']);
                elseif ($r['type'] === 'type') $url .= '?type=' . urlencode($r['label']);
                elseif ($r['type'] === 'plot') $url = $baseUrl . '/colony/' . urlencode($r['sub'] ?? '') . '/plots';
                elseif ($r['type'] === 'project') $url = $baseUrl . '/projects';
                $results[] = [
                    'label' => $r['label'],
                    'type' => $r['type'],
                    'sub' => $r['sub'] ?? '',
                    'url' => $url,
                ];
            }
        } catch (\Throwable $e) {
            error_log("SavedSearchController::autocomplete: " . $e->getMessage());
        }

        echo json_encode(['success' => true, 'results' => $results]);
    }

    /**
     * Cron-callable: send all pending alerts. Returns stats array.
     * GET /user/saved-searches/cron-alerts?key=CRON_SECRET  (or POST)
     */
    public function cronAlerts($request = null)
    {
        $this->jsonHeader();
        $providedKey = $_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '';
        $expectedKey = getenv('CRON_SECRET') ?: 'dev-cron-key';

        if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
            if (!hash_equals($expectedKey, $providedKey)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                return;
            }
        }

        $stats = $this->service->sendAlerts();
        echo json_encode(['success' => true, 'stats' => $stats]);
    }

    // -----------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------

    private function requireCustomerLogin()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            if ($this->wantsJson()) {
                $this->json(['success' => false, 'error' => 'login_required', 'redirect' => '/login'], 401);
                exit;
            }
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
        $role = $_SESSION['role'] ?? '';
        if ($role !== '' && $role !== 'customer') {
            if ($this->wantsJson()) {
                $this->json(['success' => false, 'error' => 'forbidden'], 403);
                exit;
            }
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }

    private function getUser(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: [];
    }

    private function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $ct = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? '';
        $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return stripos($accept, 'json') !== false
            || stripos($ct, 'json') !== false
            || strtolower($xrw) === 'xmlhttprequest'
            || (isset($_GET['format']) && $_GET['format'] === 'json');
    }

    private function jsonHeader(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
    }

    private function readPayload(): array
    {
        $payload = $_POST;
        $ct = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($ct, 'json') !== false) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $payload = array_merge($payload, $decoded);
            }
        }
        if (!empty($payload['_method'])) {
            $_SERVER['REQUEST_METHOD'] = strtoupper($payload['_method']);
        }
        return $payload;
    }

    private function respond(bool $isAjax, array $payload, string $fallbackRedirect): void
    {
        if ($isAjax) {
            $code = !empty($payload['success']) ? 200 : 400;
            $this->json($payload, $code);
            return;
        }
        $this->setFlash(!empty($payload['success']) ? 'success' : 'error', $payload['error'] ?? ($payload['message'] ?? ''));
        $this->redirect($fallbackRedirect);
    }
}
