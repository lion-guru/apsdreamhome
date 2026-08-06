<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;
use App\Traits\TenantAwareTrait;

class PropertyController extends PageController
{
    use TenantAwareTrait;
    public function properties()
    {
        return parent::properties();
    }

    public function propertyDetails($id = null)
    {
        return parent::propertyDetails($id);
    }

    public function plot()
    {
        return parent::plot();
    }

    public function plotsAvailability()
    {
        return parent::plotsAvailability();
    }

    public function listProperty()
    {
        return parent::listProperty();
    }

    public function handlePropertyListing()
    {
        return parent::handlePropertyListing();
    }

    public function userPropertyDetail($id = null)
    {
        return parent::userPropertyDetail($id);
    }

    public function propertyInterest()
    {
        return parent::propertyInterest();
    }

    public function propertyInquiry()
    {
        return parent::propertyInquiry();
    }

    public function getFeaturedProperties()
    {
        return parent::getFeaturedProperties();
    }

    public function buyProperty()
    {
        return parent::buyProperty();
    }

    public function sellProperty()
    {
        return parent::sellProperty();
    }

    public function rentProperty()
    {
        return parent::rentProperty();
    }

    public function investProperty()
    {
        return parent::investProperty();
    }

    public function plotSizeConverter()
    {
        return parent::plotSizeConverter();
    }

    public function plotConverter()
    {
        return parent::plotConverter();
    }
}