<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\BaseController;

/**
 * PropertyController Controller
 * Handles PropertyController related operations
 */
class PropertyController extends BaseController
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
     * Create method
     * @return void
     */
    public function create()
    {
        // TODO: Implement create functionality
        return $this->view('create');
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
     * Edit method
     * @return void
     */
    public function edit()
    {
        // TODO: Implement edit functionality
        return $this->view('edit');
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
