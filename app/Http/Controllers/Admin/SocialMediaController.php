<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\SocialMediaService;

class SocialMediaController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SocialMediaService();
    }

    public function index()
    {
        $this->requireAdmin();
        $filters = [
            'platform' => $_GET['platform'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];
        $accounts = $this->service->getAccounts($filters);
        $platforms = ['facebook', 'instagram', 'linkedin', 'whatsapp', 'google', 'twitter'];
        
        $this->render('admin/social_media/index', [
            'page_title' => 'Social Media Accounts',
            'accounts' => $accounts,
            'platforms' => $platforms,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $platforms = [
            'facebook' => 'Facebook / Instagram',
            'linkedin' => 'LinkedIn',
            'whatsapp' => 'WhatsApp Business',
            'google' => 'Google My Business',
            'twitter' => 'Twitter / X',
        ];
        $this->render('admin/social_media/form', [
            'page_title' => 'Connect Social Account',
            'platforms' => $platforms,
            'account' => null,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/social-media');

        try {
            $id = $this->service->createAccount([
                'user_id' => $_SESSION['admin_id'] ?? 1,
                'platform' => $_POST['platform'] ?? '',
                'account_id' => $_POST['account_id'] ?? '',
                'account_name' => $_POST['account_name'] ?? '',
                'account_type' => $_POST['account_type'] ?? 'business_page',
                'access_token' => $_POST['access_token'] ?? null,
                'refresh_token' => $_POST['refresh_token'] ?? null,
                'token_expires_at' => $_POST['token_expires_at'] ?? null,
                'status' => 'connected',
            ]);

            $_SESSION['success'] = 'Social media account connected successfully!';
            $this->redirect('/admin/social-media');
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to connect account: ' . $e->getMessage();
            $this->redirect('/admin/social-media/create');
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $account = $this->service->getAccount((int)$id);
        if (!$account) { $this->redirect('/admin/social-media'); return; }

        $platforms = [
            'facebook' => 'Facebook / Instagram',
            'linkedin' => 'LinkedIn',
            'whatsapp' => 'WhatsApp Business',
            'google' => 'Google My Business',
            'twitter' => 'Twitter / X',
        ];
        $this->render('admin/social_media/form', [
            'page_title' => 'Edit Social Account',
            'platforms' => $platforms,
            'account' => $account,
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/social-media');

        try {
            $this->service->updateAccount((int)$id, [
                'account_name' => $_POST['account_name'] ?? '',
                'account_type' => $_POST['account_type'] ?? 'business_page',
                'access_token' => $_POST['access_token'] ?? null,
                'refresh_token' => $_POST['refresh_token'] ?? null,
                'token_expires_at' => $_POST['token_expires_at'] ?? null,
                'status' => $_POST['status'] ?? 'connected',
            ]);
            $_SESSION['success'] = 'Account updated successfully!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to update: ' . $e->getMessage();
        }
        $this->redirect('/admin/social-media');
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $this->service->deleteAccount((int)$id);
        $_SESSION['success'] = 'Account deleted';
        $this->redirect('/admin/social-media');
    }

    public function syncLeads($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'POST required'], 405);
            return;
        }

        try {
            $result = $this->service->syncLeads((int)$id, ['since' => $_POST['since'] ?? null]);
            $this->jsonResponse($result);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function leads($accountId = null)
    {
        $this->requireAdmin();
        $filters = [
            'account_id' => $accountId ? (int)$accountId : ($_GET['account_id'] ?? ''),
            'status' => $_GET['status'] ?? '',
            'platform' => $_GET['platform'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = $this->service->getLeads($filters, $page, 25);
        $accounts = $this->service->getAccounts();

        $this->render('admin/social_media/leads', [
            'page_title' => 'Social Media Leads',
            'leads' => $result['data'],
            'pagination' => [
                'page' => $result['page'],
                'limit' => $result['limit'],
                'total' => $result['total'],
                'pages' => ceil($result['total'] / $result['limit']),
            ],
            'filters' => $filters,
            'accounts' => $accounts,
        ]);
    }

    public function updateLeadStatus()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonResponse(['success' => false], 405); return; }

        $leadId = (int)($_POST['lead_id'] ?? 0);
        $status = $_POST['status'] ?? 'new';
        $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;

        if ($leadId) {
            $this->service->updateLeadStatus($leadId, $status, $assignedTo);
            if ($assignedTo) $this->service->assignLead($leadId, $assignedTo);
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid lead ID'], 400);
        }
    }

    public function campaigns($accountId)
    {
        $this->requireAdmin();
        $account = $this->service->getAccount((int)$accountId);
        if (!$account) { $this->redirect('/admin/social-media'); return; }

        $campaigns = $this->service->getCampaigns((int)$accountId);
        $this->render('admin/social_media/campaigns', [
            'page_title' => 'Campaigns - ' . $account['account_name'],
            'account' => $account,
            'campaigns' => $campaigns,
        ]);
    }

    public function createCampaign($accountId)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect("/admin/social-media/campaigns/$accountId"); return; }

        $id = $this->service->createCampaign((int)$accountId, [
            'platform_campaign_id' => $_POST['platform_campaign_id'] ?? '',
            'platform' => $_POST['platform'] ?? 'facebook',
            'name' => $_POST['name'] ?? '',
            'objective' => $_POST['objective'] ?? 'lead_generation',
            'status' => $_POST['status'] ?? 'active',
            'daily_budget' => !empty($_POST['daily_budget']) ? (float)$_POST['daily_budget'] : null,
            'lifetime_budget' => !empty($_POST['lifetime_budget']) ? (float)$_POST['lifetime_budget'] : null,
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'targeting' => $_POST['targeting'] ?? null,
            'creative_preview' => $_POST['creative_preview'] ?? null,
        ]);
        $_SESSION['success'] = 'Campaign created!';
        $this->redirect("/admin/social-media/campaigns/$accountId");
    }

    public function insights($accountId)
    {
        $this->requireAdmin();
        $account = $this->service->getAccount((int)$accountId);
        if (!$account) { $this->redirect('/admin/social-media'); return; }

        $period = $_GET['period'] ?? '7d';
        $insights = $this->service->getAccountInsights((int)$accountId, $period);

        $this->render('admin/social_media/insights', [
            'page_title' => 'Insights - ' . $account['account_name'],
            'account' => $account,
            'insights' => $insights,
            'period' => $period,
        ]);
    }

    public function campaignsAll()
    {
        $this->requireAdmin();
        $accounts = $this->service->getAccounts();
        $campaigns = $this->service->getAllCampaigns();
        $this->render('admin/social_media/campaigns', [
            'page_title' => 'All Campaigns',
            'account' => null,
            'accounts' => $accounts,
            'campaigns' => $campaigns,
        ]);
    }

    public function insightsAll()
    {
        $this->requireAdmin();
        $accounts = $this->service->getAccounts();
        $period = $_GET['period'] ?? '7d';
        $insights = $this->service->getAllInsights($period);
        $this->render('admin/social_media/insights', [
            'page_title' => 'All Insights',
            'account' => null,
            'accounts' => $accounts,
            'insights' => $insights,
            'period' => $period,
        ]);
    }

    public function settings()
    {
        $this->requireAdmin();
        $db = \App\Core\Database\Database::getInstance();
        $rows = $db->fetchAll("SELECT content_key, content_value FROM site_content WHERE section = 'social_media'") ?: [];
        $configs = [];
        foreach ($rows as $r) $configs[$r['content_key']] = $r['content_value'];

        $this->render('admin/social_media/settings', [
            'page_title' => 'Social Media Settings',
            'configs' => $configs,
        ]);
    }

    public function saveSettings()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/social-media/settings'); return; }

        $db = \App\Core\Database\Database::getInstance();
        $configs = [
            'fb_app_id' => $_POST['fb_app_id'] ?? '',
            'fb_app_secret' => $_POST['fb_app_secret'] ?? '',
            'li_client_id' => $_POST['li_client_id'] ?? '',
            'li_client_secret' => $_POST['li_client_secret'] ?? '',
            'wa_phone_id' => $_POST['wa_phone_id'] ?? '',
            'wa_token' => $_POST['wa_token'] ?? '',
        ];

        foreach ($configs as $key => $value) {
            $db->execute(
                "INSERT INTO site_content (section, content_key, content_value, content_type, content_group) VALUES ('social_media', ?, ?, 'text', 'social_media') ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)",
                [$key, $value]
            );
        }

        $_SESSION['success'] = 'Settings saved!';
        $this->redirect('/admin/social-media/settings');
    }
}