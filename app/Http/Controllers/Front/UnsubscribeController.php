<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

/**
 * Marketing unsubscribe endpoint (S772).
 * GET shows an explicit confirmation form; POST performs the opt-out.
 * A GET never mutates - see the standing cross-origin/destructive-GET rule.
 */
class UnsubscribeController extends BaseController
{
    private const CHANNELS = ['email', 'sms', 'whatsapp', 'push', 'multi'];

    public function index()
    {
        $channel = (string)($_GET['c'] ?? 'email');
        $target = trim((string)($_GET['t'] ?? ''));
        if (!in_array($channel, self::CHANNELS, true) || $target === '' || strlen($target) > 150) {
            return $this->render('pages/unsubscribe_invalid', ['page_title' => 'Unsubscribe']);
        }
        return $this->render('pages/unsubscribe', [
            'page_title' => 'Unsubscribe',
            'channel' => $channel,
            'target' => $target,
        ]);
    }

    public function store()
    {
        $this->validateCsrfOrFail();
        $channel = (string)($_POST['channel'] ?? 'email');
        $target = trim((string)($_POST['target'] ?? ''));
        if (!in_array($channel, self::CHANNELS, true) || $target === '' || strlen($target) > 150) {
            return $this->render('pages/unsubscribe_invalid', ['page_title' => 'Unsubscribe']);
        }
        $isEmail = (strpos($target, '@') !== false);
        $db = Database::getInstance();
        $db->insert('marketing_unsubscribes', [
            'email' => $isEmail ? $target : null,
            'phone' => $isEmail ? null : $target,
            'channel' => $channel,
            'reason' => 'Unsubscribed via email link',
            'ip_address' => substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 60),
            'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            'tenant_id' => 1,
        ]);
        return $this->render('pages/unsubscribe_done', [
            'page_title' => 'Unsubscribed',
            'channel' => $channel,
        ]);
    }
}
