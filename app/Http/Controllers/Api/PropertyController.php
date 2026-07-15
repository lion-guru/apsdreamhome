<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class PropertyController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function properties()
    {
        // Browse properties
    }

    public function property($id)
    {
        // Property detail
    }

    public function search()
    {
        // Search properties
    }

    public function similar($id)
    {
        // Similar properties
    }

    public function colonyProperties($colonyId)
    {
        // Colony properties
    }

    public function getFeatured()
    {
        // Featured properties
    }

    public function marketplace()
    {
        // Marketplace
    }

    public function premium()
    {
        // Premium properties
    }

    public function getTypes()
    {
        // Property types
    }

    public function getCities()
    {
        // Cities
    }
}