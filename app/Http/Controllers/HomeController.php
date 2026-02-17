<?php

namespace App\Http\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        // Render the home view
        $this->view('home', [
            'title' => 'Welcome to APS Dream Home',
            'description' => 'Find your dream property with us.'
        ]);
    }
}
