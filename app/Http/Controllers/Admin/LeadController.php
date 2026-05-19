<?php
/**
 * Lead Management Controller
 * CRM: Leads, Enquiries, Follow-ups
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

class LeadController extends BaseController
{
    /**
     * All leads list
     */
    public function index()
    {
        $this->requireAdmin();
        $leads = \App\Models\Lead::all();
        return $this->render('admin/leads/index', ['leads' => $leads]);
    }
    
    /**
     * Create new lead
     */
    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/create', []);
    }
    
    /**
     * Store lead
     */
    public function store()
    {
        $this->requireAdmin();
        $this->redirect('/admin/leads');
    }
    
    /**
     * View lead
     */
    public function show($id)
    {
        $this->requireAdmin();
        $lead = \App\Models\Lead::find($id);
        return $this->render('admin/leads/show', ['lead' => $lead]);
    }
    
    /**
     * Lead sources
     */
    public function sources()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/sources', []);
    }
    
    /**
     * Lead status management
     */
    public function status()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/status', []);
    }
    
    /**
     * Follow-ups
     */
    public function followups()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/followups', []);
    }
    
    /**
     * Lead scoring/prioritization
     */
    public function scoring()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/scoring', []);
    }
    
    /**
     * Bulk actions
     */
    public function bulk()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/bulk', []);
    }
    
    /**
     * Import leads
     */
    public function import()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/import', []);
    }
    
    /**
     * Lead analysis/reports
     */
    public function analysis()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/analysis', []);
    }
}