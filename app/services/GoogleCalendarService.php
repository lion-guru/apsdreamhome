<?php
/**
 * GoogleCalendarService — Google Calendar sync via raw OAuth2 + REST API v3.
 *
 * No SDK dependency (consistent with S3Storage's inline SigV4 approach).
 * OAuth2 flow: getAuthUrl() → user consents → callback exchanges code for tokens.
 * Tokens stored in google_calendar_tokens. Access tokens auto-refresh via refresh_token.
 *
 * Configuration (env vars):
 *   GOOGLE_CLIENT_ID       required
 *   GOOGLE_CLIENT_SECRET   required
 *   GOOGLE_REDIRECT_URI    required (e.g. http://localhost/apsdreamhome/admin/calendar-sync/callback)
 */
namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;

class GoogleCalendarService
{
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $tenantId;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->clientId = getenv('GOOGLE_CLIENT_ID') ?: '';
        $this->clientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
        $this->redirectUri = getenv('GOOGLE_REDIRECT_URI') ?: (defined('BASE_URL') ? BASE_URL . '/admin/calendar-sync/callback' : '');
        $this->tenantId = (int)TenantContext::getId();
    }

    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret) && !empty($this->redirectUri);
    }

    public function getAuthUrl(string $state = ''): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/userinfo.email',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];
        if ($state) $params['state'] = $state;
        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
    }

    /**
     * Exchange auth code for tokens. Returns ['email'=>, 'access_token'=>, 'refresh_token'=>].
     */
    public function exchangeCode(string $code): array
    {
        $response = $this->httpPost('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
        ]);
        if (empty($response['access_token'])) {
            throw new Exception('Google token exchange failed: ' . json_encode($response));
        }
        return $response;
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        $response = $this->httpPost('https://oauth2.googleapis.com/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);
        if (empty($response['access_token'])) {
            throw new Exception('Google token refresh failed: ' . json_encode($response));
        }
        return $response;
    }

    public function getUserEmail(string $accessToken): ?string
    {
        $response = $this->httpGet('https://www.googleapis.com/oauth2/v2/userinfo', $accessToken);
        return $response['email'] ?? null;
    }

    /* ---------------------------------------------------------------- Tokens */

    public function saveTokens(int $userId, array $tokens, ?string $email = null): void
    {
        $expiresAt = !empty($tokens['expires_in'])
            ? date('Y-m-d H:i:s', time() + (int)$tokens['expires_in'] - 60)
            : null;
        $existing = $this->db->fetchOne(
            "SELECT id FROM google_calendar_tokens WHERE tenant_id = ? AND user_id = ?",
            [$this->tenantId, $userId]
        );
        if ($existing) {
            $this->db->update('google_calendar_tokens', [
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'scope' => $tokens['scope'] ?? null,
                'expires_at' => $expiresAt,
                'email' => $email,
            ], ['id' => $existing['id']]);
        } else {
            $this->db->insert('google_calendar_tokens', [
                'tenant_id' => $this->tenantId,
                'user_id' => $userId,
                'email' => $email,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'scope' => $tokens['scope'] ?? null,
                'expires_at' => $expiresAt,
            ]);
        }
    }

    public function getTokens(int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM google_calendar_tokens WHERE tenant_id = ? AND user_id = ?",
            [$this->tenantId, $userId]
        ) ?: null;
    }

    public function disconnect(int $userId): bool
    {
        return (bool)$this->db->delete('google_calendar_tokens', ['tenant_id' => $this->tenantId, 'user_id' => $userId]);
    }

    /**
     * Get a valid access token (auto-refresh if expired).
     */
    public function getValidAccessToken(int $userId): ?string
    {
        $tokens = $this->getTokens($userId);
        if (!$tokens) return null;
        if (!empty($tokens['expires_at']) && strtotime($tokens['expires_at']) > time() + 60) {
            return $tokens['access_token'];
        }
        if (empty($tokens['refresh_token'])) return null;
        try {
            $new = $this->refreshAccessToken($tokens['refresh_token']);
            $this->saveTokens($userId, $new, $tokens['email'] ?? null);
            return $new['access_token'];
        } catch (Exception $e) {
            return null;
        }
    }

    /* ---------------------------------------------------------------- Calendar API */

    public function listEvents(int $userId, string $calendarId = 'primary', ?string $timeMin = null, ?string $timeMax = null, int $maxResults = 50): array
    {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) throw new Exception('Not connected to Google Calendar');
        $params = ['maxResults' => $maxResults, 'singleEvents' => 'true', 'orderBy' => 'startTime'];
        if ($timeMin) $params['timeMin'] = $timeMin;
        if ($timeMax) $params['timeMax'] = $timeMax;
        $response = $this->httpGet("https://www.googleapis.com/calendar/v3/calendars/" . urlencode($calendarId) . "/events", $accessToken, $params);
        return $response['items'] ?? [];
    }

    public function createEvent(int $userId, array $event, string $calendarId = 'primary'): array
    {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) throw new Exception('Not connected to Google Calendar');
        return $this->httpPost("https://www.googleapis.com/calendar/v3/calendars/" . urlencode($calendarId) . "/events", $event, $accessToken) ?: [];
    }

    public function deleteEvent(int $userId, string $eventId, string $calendarId = 'primary'): bool
    {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) throw new Exception('Not connected to Google Calendar');
        return $this->httpDelete("https://www.googleapis.com/calendar/v3/calendars/" . urlencode($calendarId) . "/events/" . urlencode($eventId), $accessToken);
    }

    /**
     * Sync CRM meetings to Google Calendar. Returns count of events created.
     */
    public function syncCrmMeetings(int $userId): int
    {
        $meetings = $this->db->fetchAll(
            "SELECT * FROM crm_meetings WHERE tenant_id = ? AND user_id = ? AND meeting_date >= ? AND google_event_id IS NULL ORDER BY meeting_date ASC LIMIT 20",
            [$this->tenantId, $userId, date('Y-m-d')]
        ) ?: [];
        $created = 0;
        foreach ($meetings as $m) {
            $start = $m['meeting_date'] . ' ' . ($m['meeting_time'] ?? '09:00:00');
            $end = date('Y-m-d H:i:s', strtotime($start . ' +1 hour'));
            $event = [
                'summary' => $m['title'] ?? 'CRM Meeting',
                'description' => $m['notes'] ?? '',
                'start' => ['dateTime' => date('c', strtotime($start)), 'timeZone' => 'Asia/Kolkata'],
                'end' => ['dateTime' => date('c', strtotime($end)), 'timeZone' => 'Asia/Kolkata'],
            ];
            try {
                $result = $this->createEvent($userId, $event);
                if (!empty($result['id'])) {
                    $this->db->update('crm_meetings', ['google_event_id' => $result['id']], ['id' => $m['id']]);
                    $created++;
                }
            } catch (Exception $e) {
                // best-effort, continue
            }
        }
        return $created;
    }

    /* ---------------------------------------------------------------- HTTP helpers */

    private function httpGet(string $url, string $accessToken, array $params = []): array
    {
        if ($params) $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT => 30,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body, true) ?: [];
    }

    private function httpPost(string $url, array $data, ?string $accessToken = null): array
    {
        $ch = curl_init($url);
        $headers = ['Content-Type: application/x-www-form-urlencoded'];
        if ($accessToken) $headers[] = 'Authorization: Bearer ' . $accessToken;
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => is_array($data) ? json_encode($data) : $data,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body, true) ?: [];
    }

    private function httpDelete(string $url, string $accessToken): bool
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT => 30,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }
}
