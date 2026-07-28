<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;
use App\Traits\TenantAwareTrait;

class FinancialController extends PageController
{
    use TenantAwareTrait;
    public function financialServices()
    {
        return parent::financialServices();
    }

    public function financialContact()
    {
        return parent::financialContact();
    }

    public function bank()
    {
        return parent::bank();
    }
}