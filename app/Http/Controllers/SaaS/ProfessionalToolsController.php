<?php

namespace App\Http\Controllers\SaaS;

use App\Http\Controllers\BaseController;
use Exception;

class ProfessionalToolsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Inventory Management for Builders
     */
    public function inventory()
    {
        $user = $this->auth->user();
        $data = [
            'page_title' => 'Inventory Management',
            'user' => $user,
            'projects' => $this->model('Project')->all(['where' => ['builder_id' => $user->uid]]),
            'plots' => $this->model('Plot')->all(['limit' => 20]) // Generic for now
        ];
        return $this->render('saas/tools/inventory', $data);
    }

    /**
     * Construction Workflow for Builders/Contractors
     */
    public function workflow()
    {
        $user = $this->auth->user();
        $data = [
            'page_title' => 'Construction Workflow',
            'user' => $user,
            // Mock data for workflow steps
            'workflows' => [
                ['id' => 1, 'name' => 'Foundation', 'status' => 'completed', 'progress' => 100],
                ['id' => 2, 'name' => 'Framing', 'status' => 'in_progress', 'progress' => 45],
                ['id' => 3, 'name' => 'Roofing', 'status' => 'pending', 'progress' => 0]
            ]
        ];
        return $this->render('saas/tools/workflow', $data);
    }

    /**
     * Expense Tracker (Lekha-Jhokha)
     */
    public function expenses()
    {
        $user = $this->auth->user();
        $data = [
            'page_title' => 'Expense Tracker',
            'user' => $user,
            'expenses' => [] // Should connect to an Expense model if exists
        ];
        return $this->render('saas/tools/expenses', $data);
    }

    /**
     * Labor Management
     */
    public function labor()
    {
        $user = $this->auth->user();
        $data = [
            'page_title' => 'Labor Management',
            'user' => $user,
            'labor_records' => []
        ];
        return $this->render('saas/tools/labor', $data);
    }

    /**
     * WhatsApp Marketing Tool
     */
    public function whatsapp()
    {
        $user = $this->auth->user();
        $data = [
            'page_title' => 'WhatsApp Marketing',
            'user' => $user,
            'templates' => [
                ['name' => 'New Property Launch', 'content' => 'Hi, we just launched a new project...'],
                ['name' => 'Payment Reminder', 'content' => 'Dear Customer, this is a reminder...']
            ]
        ];
        return $this->render('saas/tools/whatsapp', $data);
    }

    /**
     * Referral Program
     */
    public function referrals()
    {
        $user = $this->auth->user();
        $data = [
            'page_title' => 'Referral Program',
            'user' => $user,
            'referrals' => $this->model('Referral')->all(['where' => ['referred_by' => $user->uid]])
        ];
        return $this->render('saas/tools/referrals', $data);
    }

    /**
     * Document Vault
     */
    public function documents()
    {
        $user = $this->auth->user();
        $data = [
            'page_title' => 'Document Vault',
            'user' => $user,
            'documents' => []
        ];
        return $this->render('saas/tools/documents', $data);
    }
}
