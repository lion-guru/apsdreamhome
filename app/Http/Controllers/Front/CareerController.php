<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;
use App\Traits\TenantAwareTrait;

class CareerController extends PageController
{
    use TenantAwareTrait;
    public function careers()
    {
        return parent::careers();
    }

    public function careerApply()
    {
        return parent::careerApply();
    }

    public function submitCareerApplication()
    {
        return parent::submitCareerApplication();
    }

    public function careerJobs()
    {
        return parent::careerJobs();
    }

    public function careerJobDetails($id = null)
    {
        return parent::careerJobDetails($id);
    }

    public function opportunity()
    {
        return parent::opportunity();
    }
}