<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;
use App\Traits\TenantAwareTrait;

class AssociateController extends PageController
{
    use TenantAwareTrait;
    public function mlmDashboard()
    {
        return parent::mlmDashboard();
    }

    public function becomeAssociate()
    {
        return parent::becomeAssociate();
    }
}