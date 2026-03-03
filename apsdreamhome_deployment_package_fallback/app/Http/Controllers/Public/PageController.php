<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;

/**
 * PageController Controller
 * Handles PageController related operations
 */
class PageController extends BaseController
{
    /**
     * Home method
     * @return void
     */
    public function home()
    {
        // TODO: Implement home functionality
        return $this->view('home');
    }

    /**
     * About method
     * @return void
     */
    public function about()
    {
        // TODO: Implement about functionality
        return $this->view('about');
    }

    /**
     * Contact method
     * @return void
     */
    public function contact()
    {
        // TODO: Implement contact functionality
        return $this->view('contact');
    }

    /**
     * Properties method
     * @return void
     */
    public function properties()
    {
        // TODO: Implement properties functionality
        return $this->view('properties');
    }

    /**
     * Services method
     * @return void
     */
    public function services()
    {
        // TODO: Implement services functionality
        return $this->view('services');
    }

}
