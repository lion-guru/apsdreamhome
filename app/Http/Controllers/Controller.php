<?php

namespace App\Http\Controllers;

use App\Services\AuthService;

abstract class Controller extends BaseController
{
    protected AuthService $auth;
    protected array $data = [];
    protected string $layout = 'base';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->auth = new AuthService();
    }

    /**
     * Render a view template
     */
    public function view($view, $data = [], $layout = null): void
    {
        $this->render($view, $data, $layout);
    }
}
