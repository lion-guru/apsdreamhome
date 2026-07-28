<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;
use App\Traits\TenantAwareTrait;

class ServiceController extends PageController
{
    use TenantAwareTrait;
    public function services()
    {
        return parent::services();
    }

    public function financialServices()
    {
        return parent::financialServices();
    }

    public function legalServices()
    {
        return parent::legalServices();
    }

    public function interiorDesign()
    {
        return parent::interiorDesign();
    }

    public function constructionServices()
    {
        return parent::constructionServices();
    }

    public function documents()
    {
        return parent::documents();
    }

    public function resell()
    {
        return parent::resell();
    }
}