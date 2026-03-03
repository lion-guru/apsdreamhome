<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        if (!isset($_SESSION["user_id"])) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
        
        $this->data["title"] = "Dashboard - APS Dream Home";
        $this->render("user/dashboard", $this->data, "layouts/base");
    }
}