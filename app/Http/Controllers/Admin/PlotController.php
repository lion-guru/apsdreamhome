<?php
/**
 * Plot Management Controller
 * Primary Business: Plot Cutting, Booking, Selling
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class PlotController extends AdminController
{
    /**
     * List all plots
     */
    public function index()
    {
        $this->requireAdmin();
        $plots = \App\Models\Plot::all();
        return $this->render('admin/plots/index', ['plots' => $plots]);
    }
    
    /**
     * Create new plot
     */
    public function create()
    {
        $this->requireAdmin();
        $colonies = \App\Models\Colony::all();
        return $this->render('admin/plots/create', ['colonies' => $colonies]);
    }
    
    /**
     * Store new plot
     */
    public function store()
    {
        $this->requireAdmin();
        // Save plot
        $this->redirect('/admin/plots');
    }
    
    /**
     * View plot details
     */
    public function show($id)
    {
        $this->requireAdmin();
        $plot = \App\Models\Plot::find($id);
        return $this->render('admin/plots/show', ['plot' => $plot]);
    }
    
    /**
     * Edit plot
     */
    public function edit($id)
    {
        $this->requireAdmin();
        $plot = \App\Models\Plot::find($id);
        return $this->render('admin/plots/edit', ['plot' => $plot]);
    }
    
    /**
     * Update plot
     */
    public function update($id)
    {
        $this->requireAdmin();
        $this->redirect('/admin/plots');
    }
    
    /**
     * Delete plot
     */
    public function destroy($id)
    {
        $this->requireAdmin();
        $this->redirect('/admin/plots');
    }
    
    /**
     * Plot categories/groups
     */
    public function categories()
    {
        $this->requireAdmin();
        return $this->render('admin/plots/categories', []);
    }
    
    /**
     * Plot cutting/sector mapping
     */
    public function cutting()
    {
        $this->requireAdmin();
        return $this->render('admin/plots/cutting', []);
    }
    
    /**
     * Plot availability status
     */
    public function availability()
    {
        $this->requireAdmin();
        return $this->render('admin/plots/availability', []);
    }
    
    /**
     * Plot booking
     */
    public function book($id)
    {
        $this->requireAdmin();
        return $this->render('admin/plots/book', []);
    }
    
    /**
     * Plot transfer/registry
     */
    public function transfer($id)
    {
        $this->requireAdmin();
        return $this->render('admin/plots/transfer', []);
    }
}