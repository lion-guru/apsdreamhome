<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        $this->data = [
            "title" => "Welcome to APS Dream Home",
            "description" => "Find your dream home with APS Dream Home"
        ];
        $this->render("home/index", $this->data, "layouts/base");
    }
    
    public function about()
    {
        $this->data = [
            "title" => "About Us - APS Dream Home",
            "description" => "Learn about APS Dream Home"
        ];
        $this->render("home/about", $this->data, "layouts/base");
    }
    
    public function contact()
    {
        $this->data = [
            "title" => "Contact Us - APS Dream Home",
            "description" => "Get in touch with APS Dream Home"
        ];
        $this->render("home/contact", $this->data, "layouts/base");
    }
    
    public function properties()
    {
        $this->data = [
            "title" => "Properties - APS Dream Home",
            "description" => "Browse our properties"
        ];
        $this->render("properties/index", $this->data, "layouts/base");
    }
}
