<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Core\Middleware\TenantContext;

/**
 * User mini-CRM — "My Contacts" for property-posting customers.
 * Their listings' inquiries are THEIR leads; stored in user_contacts
 * (never in `leads`, keeping the company pipeline clean). Owner-scoped:
 * every query carries user_id, so users can never see each other's rows.
 */
class UserContactsController extends BaseController
{
    private function requireCustomer(): void
    {
        @session_start();
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
            exit;
        }
    }

    private function normalizePhone($phone)
    {
        $d = preg_replace('/\D/', '', (string)$phone);
        if (strlen($d) === 12 && substr($d, 0, 2) === '91') return substr($d, -10);
        if (strlen($d) === 11 && $d[0] === '0') return substr($d, -10);
        return $d;
    }

    /**
     * GET /user/contacts — own contacts + stage counts.
     */
    public function myContacts()
    {
        $this->requireCustomer();
        $userId = (int)$_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId];
        if ($tid > 1) $params[] = $tid;

        $contacts = $db->fetchAll(
            "SELECT * FROM user_contacts WHERE user_id = ?{$tidSql} ORDER BY updated_at DESC",
            $params
        ) ?: [];
        $counts = ['new' => 0, 'contacted' => 0, 'closed' => 0];
        foreach ($contacts as $c) {
            if (isset($counts[$c['stage']])) $counts[$c['stage']]++;
        }

        $this->layout = 'layouts/customer';
        $this->render('customer/my_contacts', [
            'page_title' => 'My Contacts - APS Dream Home',
            'page_description' => 'Your buyers, renters and inquiries',
            'contacts' => $contacts,
            'counts' => $counts,
        ]);
    }

    /**
     * POST /user/contacts/store — manual contact add (name + phone required).
     */
    public function storeContact()
    {
        $this->requireCustomer();
        $userId = (int)$_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/contacts');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $phone = $this->normalizePhone($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($name === '' || !preg_match('/^[6-9]\d{9}$/', $phone)) {
            $_SESSION['error'] = 'Name and a valid 10-digit mobile are required';
            $this->redirect('/user/contacts');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        // One row per phone per owner (re-add updates instead of duplicating).
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId, $phone];
        if ($tid > 1) $params[] = $tid;
        $existing = $db->fetchOne("SELECT id FROM user_contacts WHERE user_id = ? AND phone = ?{$tidSql} LIMIT 1", $params);
        if ($existing) {
            $up = [$name, ($email !== '' ? $email : null), trim($_POST['property_ref'] ?? '') ?: null, trim($_POST['notes'] ?? '') ?: null, $existing['id'], $userId];
            if ($tid > 1) $up[] = $tid;
            $db->execute("UPDATE user_contacts SET name = ?, email = COALESCE(?, email), property_ref = COALESCE(?, property_ref), notes = ?, updated_at = NOW() WHERE id = ? AND user_id = ?{$tidSql}", $up);
            $_SESSION['success'] = 'Contact updated!';
        } else {
            $db->insert('user_contacts', [
                'user_id' => $userId,
                'name' => mb_substr($name, 0, 150),
                'phone' => $phone,
                'email' => ($email !== '' ? mb_substr($email, 0, 150) : null),
                'property_ref' => (trim($_POST['property_ref'] ?? '') !== '' ? mb_substr(trim($_POST['property_ref']), 0, 150) : null),
                'notes' => (trim($_POST['notes'] ?? '') !== '' ? trim($_POST['notes']) : null),
                'stage' => 'new',
                'tenant_id' => $tid,
            ]);
            $_SESSION['success'] = 'Contact added!';
        }
        $this->redirect('/user/contacts');
    }

    /**
     * POST /user/contacts/{id}/stage — move pipeline stage (owner-scoped).
     */
    public function updateStage($id)
    {
        $this->requireCustomer();
        $userId = (int)$_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/contacts');
            return;
        }

        $stage = $_POST['stage'] ?? '';
        if (!in_array($stage, ['new', 'contacted', 'closed'], true)) {
            $_SESSION['error'] = 'Invalid stage';
            $this->redirect('/user/contacts');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$stage, $id, $userId];
        if ($tid > 1) $params[] = $tid;
        $stmt = $db->prepare("UPDATE user_contacts SET stage = ?, updated_at = NOW() WHERE id = ? AND user_id = ?{$tidSql}");
        $stmt->execute($params);

        $_SESSION[$stmt->rowCount() > 0 ? 'success' : 'error'] = $stmt->rowCount() > 0
            ? 'Contact moved to ' . $stage . '!'
            : 'Contact not found';
        $this->redirect('/user/contacts');
    }
}
