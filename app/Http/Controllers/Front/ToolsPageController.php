<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;
use PDO;

/**
 * ToolsPageController
 * All calculators and tools (EMI, stamp duty, plot converter, plot size converter, construction cost, rental yield, rent vs buy, property tax, SIP vs real estate, GST, capital gains, home loan eligibility, property valuation, neighborhood, virtual tour, RERA lookup, title protection, insurance, NACH, agreements, how it works)
 */
class ToolsPageController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function emiCalculator()
    {
        $this->render('pages/emi_calculator', [
            'page_title' => 'EMI Calculator - APS Dream Home',
            'page_description' => 'Calculate your monthly EMI for home loans.',
        ]);
    }

    public function stampDutyCalculator()
    {
        $this->render('pages/stamp_duty_calculator', [
            'page_title' => 'Stamp Duty Calculator - APS Dream Home',
            'page_description' => 'Calculate stamp duty and registration charges.',
        ]);
    }

    public function plotConverter()
    {
        $this->render('pages/plot_converter', [
            'page_title' => 'Plot Size Converter - APS Dream Home',
            'page_description' => 'Convert between different plot size units.',
        ]);
    }

    public function plotSizeConverter()
    {
        $this->render('pages/plot_size_converter', [
            'page_title' => 'Plot Size Converter - APS Dream Home',
            'page_description' => 'Convert between different plot size units.',
        ]);
    }

    public function constructionCostEstimator()
    {
        $this->render('pages/construction_cost_estimator', [
            'page_title' => 'Construction Cost Estimator - APS Dream Home',
            'page_description' => 'Estimate your construction costs.',
        ]);
    }

    public function rentalYieldCalculator()
    {
        $this->render('pages/rental_yield_calculator', [
            'page_title' => 'Rental Yield Calculator - APS Dream Home',
            'page_description' => 'Calculate rental yield for your property.',
        ]);
    }

    public function rentVsBuyCalculator()
    {
        $this->render('pages/rent_vs_buy_calculator', [
            'page_title' => 'Rent vs Buy Calculator - APS Dream Home',
            'page_description' => 'Compare renting vs buying.',
        ]);
    }

    public function propertyTaxCalculator()
    {
        $this->render('pages/property_tax_calculator', [
            'page_title' => 'Property Tax Calculator - APS Dream Home',
            'page_description' => 'Calculate property tax.',
        ]);
    }

    public function sipVsRealEstateCalculator()
    {
        $this->render('pages/sip_vs_realestate_calculator', [
            'page_title' => 'SIP vs Real Estate Calculator - APS Dream Home',
            'page_description' => 'Compare SIP investment vs real estate.',
        ]);
    }

    public function gstCalculator()
    {
        $this->render('pages/gst_calculator', [
            'page_title' => 'GST Calculator - APS Dream Home',
            'page_description' => 'Calculate GST on property transactions.',
        ]);
    }

    public function capitalGainsCalculator()
    {
        $this->render('pages/capital_gains_calculator', [
            'page_title' => 'Capital Gains Calculator - APS Dream Home',
            'page_description' => 'Calculate capital gains tax on property sale.',
        ]);
    }

    public function propertyValuation()
    {
        $this->render('pages/property_valuation', [
            'page_title' => 'Property Valuation - APS Dream Home',
            'page_description' => 'Get estimated property value.',
        ]);
    }

    public function neighborhoodAnalysis()
    {
        $this->render('pages/neighborhood_analysis', [
            'page_title' => 'Neighborhood Analysis - APS Dream Home',
            'page_description' => 'Analyze neighborhood amenities and livability.',
        ]);
    }

    public function virtualTour()
    {
        $this->render('pages/virtual_tour', [
            'page_title' => 'Virtual Tour - APS Dream Home',
            'page_description' => 'Explore properties with virtual tours.',
        ]);
    }

    public function reraLookup()
    {
        $this->render('pages/rera_lookup', [
            'page_title' => 'RERA Lookup - APS Dream Home',
            'page_description' => 'Verify RERA registration of projects.',
        ]);
    }

    public function titleProtection()
    {
        $this->render('pages/title_protection', [
            'page_title' => 'Title Protection - APS Dream Home',
            'page_description' => 'Protect your property title.',
        ]);
    }

    public function propertyVerification()
    {
        $this->render('pages/property_verification', [
            'page_title' => 'Property Verification - APS Dream Home',
            'page_description' => 'Verify property documents and ownership.',
        ]);
    }

    public function insurance()
    {
        $this->render('pages/insurance', [
            'page_title' => 'Property Insurance - APS Dream Home',
            'page_description' => 'Insure your property investment.',
        ]);
    }

    public function nachMandate()
    {
        $this->render('pages/nach_mandate', [
            'page_title' => 'NACH Mandate - APS Dream Home',
            'page_description' => 'Set up NACH mandate for EMI payments.',
        ]);
    }

    public function agreements()
    {
        $this->render('pages/agreements', [
            'page_title' => 'Agreements & E-Sign - APS Dream Home',
            'page_description' => 'Digital agreements and e-signatures.',
        ]);
    }

    public function howItWorks()
    {
        $this->render('pages/how_it_works', [
            'page_title' => 'How It Works - APS Dream Home',
            'page_description' => 'How APS Dream Home works for you.',
        ]);
    }
}