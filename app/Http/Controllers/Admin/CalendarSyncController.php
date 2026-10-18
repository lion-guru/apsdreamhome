<?php
/**
 * CalendarSyncController — Google Calendar sync (Gap 23).
 *
 * OAuth2 connect/callback/disconnect + CRM meeting sync + event listing.
 * Routes: /admin/calendar-sync (index/connect/callback/disconnect/sync/list-events)
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\GoogleCalendarService;
use Exception;

class CalendarSyncController extends AdminController
{
    protected $calendarService;

    public function __construct()
    {
        parent::__construct();
        $this->calendarService = new GoogleCalendarService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $userId = $_SESSION['user_id'] ?? 1;
            $tokens = $this->calendarService->getTokens($userId);
            $configured = $this->calendarService->isConfigured();
            return $this->render('admin/calendar_sync/index', [
                'page_title' => 'Calendar Sync - APS Dream Home',
                'active_page' => 'calendar_sync',
                'configured' => $configured,
                'connected' => !empty($tokens),
                'email' => $tokens['email'] ?? null,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function connect()
    {
        $this->requireAdmin();
        if (!$this->calendarService->isConfigured()) {
            $this->setFlash('error', 'Google Calendar is not configured. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI in .env.');
            return $this->redirect('/admin/calendar-sync');
        }
        $state = bin2hex(random_bytes(8));
        $_SESSION['google_oauth_state'] = $state;
        return $this->redirect($this->calendarService->getAuthUrl($state));
    }

    public function callback()
    {
        $this->requireAdmin();
        try {
            $code = $_GET['code'] ?? '';
            $state = $_GET['state'] ?? '';
            if (empty($code)) {
                $this->setFlash('error', 'Authorization failed: no code returned.');
                return $this->redirect('/admin/calendar-sync');
            }
            if (!empty($_SESSION['google_oauth_state']) && $_SESSION['google_oauth_state'] !== $state) {
                $this->setFlash('error', 'Invalid OAuth state.');
                return $this->redirect('/admin/calendar-sync');
            }
            unset($_SESSION['google_oauth_state']);

            $tokens = $this->calendarService->exchangeCode($code);
            $email = $this->calendarService->getUserEmail($tokens['access_token']);
            $userId = $_SESSION['user_id'] ?? 1;
            $this->calendarService->saveTokens($userId, $tokens, $email);
            $this->logActivity('calendar_connected', ['email' => $email]);
            $this->setFlash('success', 'Google Calendar connected' . ($email ? ' as ' . $email : '') . '.');
            return $this->redirect('/admin/calendar-sync');
        } catch (Exception $e) {
            $this->setFlash('error', 'Connection failed: ' . $e->getMessage());
            return $this->redirect('/admin/calendar-sync');
        }
    }

    public function disconnect()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $userId = $_SESSION['user_id'] ?? 1;
            $this->calendarService->disconnect($userId);
            $this->logActivity('calendar_disconnected', []);
            return $this->jsonResponse(['success' => true, 'message' => 'Disconnected']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function sync()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $userId = $_SESSION['user_id'] ?? 1;
            $created = $this->calendarService->syncCrmMeetings($userId);
            $this->logActivity('calendar_sync', ['created' => $created]);
            return $this->jsonResponse([
                'success' => true,
                'message' => "Synced {$created} meeting(s) to Google Calendar",
                'created' => $created,
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function listEvents()
    {
        $this->requireAdmin();
        try {
            $userId = $_SESSION['user_id'] ?? 1;
            $timeMin = $_GET['time_min'] ?? date('c', strtotime('today'));
            $timeMax = $_GET['time_max'] ?? date('c', strtotime('+30 days'));
            $events = $this->calendarService->listEvents($userId, 'primary', $timeMin, $timeMax, 50);
            return $this->render('admin/calendar_sync/list_events', [
                'page_title' => 'Calendar Events - APS Dream Home',
                'active_page' => 'calendar_sync',
                'events' => $events,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/calendar-sync');
        }
    }
}
