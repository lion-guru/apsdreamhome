<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;
use App\Traits\TenantAwareTrait;

class ProjectController extends PageController
{
    use TenantAwareTrait;
    public function projects()
    {
        return parent::projects();
    }

    public function projectDetails($slug = null)
    {
        return parent::projectDetails($slug);
    }

    public function colonies()
    {
        return parent::colonies();
    }

    public function colonyDetail($slug = null)
    {
        return parent::colonyDetail($slug);
    }

    public function colonyPlots($slug = null)
    {
        return parent::colonyPlots($slug);
    }

    public function suyodayColony()
    {
        return parent::suyodayColony();
    }

    public function raghunatNagri()
    {
        return parent::raghunatNagri();
    }

    public function brajRadhaNagri()
    {
        return parent::brajRadhaNagri();
    }

    public function budhBiharColony()
    {
        return parent::budhBiharColony();
    }

    public function awadhpuri()
    {
        return parent::awadhpuri();
    }

    public function budhaCity()
    {
        return parent::budhaCity();
    }

    public function suyodayColonyPage()
    {
        return parent::suyodayColonyPage();
    }

    public function projectsByLocation($location = null)
    {
        return parent::projectsByLocation($location);
    }

    public function location($slug = null)
    {
        return parent::location($slug);
    }
}