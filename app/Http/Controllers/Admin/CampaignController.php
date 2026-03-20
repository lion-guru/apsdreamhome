<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\CampaignService;
use Exception;

class CampaignController extends BaseController
{
    private $campaignService;

    public function __construct()
    {
        parent::__construct();
        $this->campaignService = new CampaignService();
    }

    /**
     * Display campaigns management page
     */
    public function index()
    {
        $this->middleware('admin.auth');

        $campaigns = $this->campaignService->getActiveCampaigns();

        $this->data['campaigns'] = $campaigns;
        $this->data['page_title'] = 'Campaign Management - APS Dream Home';

        $this->render('admin/campaigns/index');
    }

    /**
     * Display create campaign form
     */
    public function create()
    {
        $this->middleware('admin.auth');

        $this->data['page_title'] = 'Create Campaign - APS Dream Home';
        $this->data['campaign_types'] = ['general', 'offer', 'promotion', 'announcement'];
        $this->data['target_audiences'] = ['all', 'customers', 'agents', 'employees', 'admin'];

        $this->render('admin/campaigns/create');
    }

    /**
     * Store new campaign
     */
    public function store()
    {
        $this->middleware('admin.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/campaigns');
        }

        try {
            $campaignData = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'type' => $_POST['type'] ?? 'general',
                'target_audience' => $_POST['target_audience'] ?? 'all',
                'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
                'end_date' => $_POST['end_date'] ?? null,
                'budget' => $_POST['budget'] ?? 0,
                'expected_revenue' => $_POST['expected_revenue'] ?? 0,
                'status' => 'planned',
                'created_by' => $_SESSION['admin_id'] ?? 1
            ];

            // Validate required fields
            if (empty($campaignData['name'])) {
                $this->data['error'] = 'Campaign name is required';
                return $this->create();
            }

            $campaignId = $this->campaignService->createCampaign($campaignData);

            if ($campaignId) {
                $this->data['success'] = 'Campaign created successfully!';
                return $this->index();
            } else {
                $this->data['error'] = 'Failed to create campaign';
                return $this->create();
            }
        } catch (Exception $e) {
            error_log("Error creating campaign: " . $e->getMessage());
            $this->data['error'] = 'An error occurred while creating the campaign';
            return $this->create();
        }
    }

    /**
     * Display edit campaign form
     */
    public function edit($campaignId)
    {
        $this->middleware('admin.auth');

        // Get campaign details
        $campaign = $this->getCampaignById($campaignId);

        if (!$campaign) {
            $this->data['error'] = 'Campaign not found';
            return $this->index();
        }

        $this->data['campaign'] = $campaign;
        $this->data['page_title'] = 'Edit Campaign - APS Dream Home';
        $this->data['campaign_types'] = ['general', 'offer', 'promotion', 'announcement'];
        $this->data['target_audiences'] = ['all', 'customers', 'agents', 'employees', 'admin'];

        $this->render('admin/campaigns/edit');
    }

    /**
     * Update campaign
     */
    public function update($campaignId)
    {
        $this->middleware('admin.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/campaigns');
        }

        try {
            $campaignData = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'type' => $_POST['type'] ?? 'general',
                'target_audience' => $_POST['target_audience'] ?? 'all',
                'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
                'end_date' => $_POST['end_date'] ?? null,
                'budget' => $_POST['budget'] ?? 0,
                'expected_revenue' => $_POST['expected_revenue'] ?? 0,
                'status' => $_POST['status'] ?? 'planned'
            ];

            // Validate required fields
            if (empty($campaignData['name'])) {
                $this->data['error'] = 'Campaign name is required';
                return $this->edit($campaignId);
            }

            $result = $this->updateCampaign($campaignId, $campaignData);

            if ($result) {
                $this->data['success'] = 'Campaign updated successfully!';
                return $this->index();
            } else {
                $this->data['error'] = 'Failed to update campaign';
                return $this->edit($campaignId);
            }
        } catch (Exception $e) {
            error_log("Error updating campaign: " . $e->getMessage());
            $this->data['error'] = 'An error occurred while updating the campaign';
            return $this->edit($campaignId);
        }
    }

    /**
     * Delete campaign
     */
    public function delete($campaignId)
    {
        $this->middleware('admin.auth');

        try {
            $result = $this->deleteCampaign($campaignId);

            if ($result) {
                $this->data['success'] = 'Campaign deleted successfully!';
            } else {
                $this->data['error'] = 'Failed to delete campaign';
            }
        } catch (Exception $e) {
            error_log("Error deleting campaign: " . $e->getMessage());
            $this->data['error'] = 'An error occurred while deleting the campaign';
        }

        return $this->index();
    }

    /**
     * Display campaign analytics
     */
    public function analytics($campaignId)
    {
        $this->middleware('admin.auth');

        $campaign = $this->getCampaignById($campaignId);

        if (!$campaign) {
            $this->data['error'] = 'Campaign not found';
            return $this->index();
        }

        $this->data['campaign'] = $campaign;
        $this->data['page_title'] = 'Campaign Analytics - APS Dream Home';

        $this->render('admin/campaigns/analytics');
    }

    /**
     * Get campaign by ID
     */
    private function getCampaignById($campaignId)
    {
        try {
            $query = "SELECT * FROM campaigns WHERE campaign_id = ?";
            return $this->db->fetch($query, [$campaignId]);
        } catch (Exception $e) {
            error_log("Error getting campaign: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update campaign in database
     */
    private function updateCampaign($campaignId, $data)
    {
        try {
            $setClause = [];
            $params = [];

            foreach ($data as $key => $value) {
                if ($key !== 'campaign_id') {
                    $setClause[] = "$key = ?";
                    $params[] = $value;
                }
            }

            $params[] = $campaignId;

            $query = "UPDATE campaigns SET " . implode(', ', $setClause) . " WHERE campaign_id = ?";

            $this->db->execute($query, $params);
            return true;
        } catch (Exception $e) {
            error_log("Error updating campaign: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete campaign from database
     */
    private function deleteCampaign($campaignId)
    {
        try {
            // Delete campaign members first
            $this->db->execute("DELETE FROM campaign_members WHERE campaign_id = ?", [$campaignId]);

            // Delete campaign
            $this->db->execute("DELETE FROM campaigns WHERE campaign_id = ?", [$campaignId]);

            return true;
        } catch (Exception $e) {
            error_log("Error deleting campaign: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Launch campaign
     */
    public function launch($campaignId)
    {
        $this->middleware('admin.auth');

        try {
            $result = $this->updateCampaign($campaignId, ['status' => 'active']);

            if ($result) {
                // Create notifications for target audience
                $campaign = $this->getCampaignById($campaignId);
                if ($campaign) {
                    $this->createCampaignNotifications($campaign);
                }

                $this->data['success'] = 'Campaign launched successfully!';
            } else {
                $this->data['error'] = 'Failed to launch campaign';
            }
        } catch (Exception $e) {
            error_log("Error launching campaign: " . $e->getMessage());
            $this->data['error'] = 'An error occurred while launching the campaign';
        }

        return $this->index();
    }

    /**
     * Create notifications for campaign
     */
    private function createCampaignNotifications($campaign)
    {
        try {
            // Get target users based on campaign audience
            $targetUsers = $this->getTargetUsers($campaign['target_audience']);

            foreach ($targetUsers as $user) {
                $this->campaignService->createNotification(
                    $user['id'],
                    $campaign['name'],
                    $campaign['description'],
                    'campaign',
                    $campaign['campaign_id']
                );
            }
        } catch (Exception $e) {
            error_log("Error creating campaign notifications: " . $e->getMessage());
        }
    }

    /**
     * Get target users based on audience
     */
    private function getTargetUsers($targetAudience)
    {
        try {
            $query = "SELECT id FROM users";
            $params = [];

            switch ($targetAudience) {
                case 'customers':
                    $query .= " WHERE role = 'customer'";
                    break;
                case 'agents':
                    $query .= " WHERE role = 'associate'";
                    break;
                case 'employees':
                    $query .= " WHERE role = 'employee'";
                    break;
                case 'admin':
                    $query .= " WHERE role = 'admin'";
                    break;
                case 'all':
                default:
                    // No filter for all users
                    break;
            }

            return $this->db->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Error getting target users: " . $e->getMessage());
            return [];
        }
    }
}
