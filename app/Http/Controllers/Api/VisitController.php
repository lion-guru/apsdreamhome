<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

/**
 * VisitController Controller
 * Handles VisitController related operations
 */
class VisitController extends BaseController
{
    /**
     * Index method
     * @return void
     */
    public function index()
    {
        // TODO: Implement index functionality
        return $this->view('index');
    }

    /**
     * Show method
     * @return void
     */
    public function show()
    {
        // TODO: Implement show functionality
        return $this->view('show');
    }

    /**
     * Store method
     * @return void
     */
    public function store()
    {
        // TODO: Implement store functionality
        return $this->view('store');
    }

    /**
     * Update method
     * @return void
     */
    public function update()
    {
        // TODO: Implement update functionality
        return $this->view('update');
    }

    /**
     * Destroy method
     * @return void
     */
    public function destroy()
    {
        // TODO: Implement destroy functionality
        return $this->view('destroy');
    }

}
