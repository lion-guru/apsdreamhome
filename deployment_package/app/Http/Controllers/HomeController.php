<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $data;

    public function index()
    {
        // Other logic and data assignments

        $this->data = [/*...data assignments...*/];

        // Add missing render call
        $this->render('home/index', $this->data, 'layouts/base');
    }
}