<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;

class ToolController extends PageController
{
    public function stampDutyCalculator()
    {
        $data = [
            'page_title' => 'Stamp Duty Calculator - APS Dream Home',
            'page_description' => 'Calculate stamp duty for property purchase',
        ];
        $this->render('pages/tools/stamp_duty_calculator', $data);
    }

    public function plotSizeConverter()
    {
        $data = [
            'page_title' => 'Plot Size Converter - APS Dream Home',
            'page_description' => 'Convert plot size between units',
        ];
        $this->render('pages/tools/plot_converter', $data);
    }

    public function plotConverter()
    {
        return $this->plotSizeConverter();
    }

    public function valuationCalculator()
    {
        $data = [
            'page_title' => 'Property Valuation Calculator - APS Dream Home',
            'page_description' => 'Estimate property value',
        ];
        $this->render('pages/tools/valuation_calculator', $data);
    }

    public function homeLoanEligibility()
    {
        $data = [
            'page_title' => 'Home Loan Eligibility Calculator - APS Dream Home',
            'page_description' => 'Calculate your home loan eligibility',
        ];
        $this->render('pages/tools/loan_eligibility', $data);
    }

    public function propertyValuation()
    {
        $data = [
            'page_title' => 'Property Valuation - APS Dream Home',
            'page_description' => 'Get property valuation',
        ];
        $this->render('pages/tools/property_valuation', $data);
    }

    public function rentVsBuy()
    {
        $data = [
            'page_title' => 'Rent vs Buy Calculator - APS Dream Home',
            'page_description' => 'Compare renting vs buying',
        ];
        $this->render('pages/tools/rent_vs_buy', $data);
    }

    public function sipVsRealestate()
    {
        $data = [
            'page_title' => 'SIP vs Real Estate Calculator - APS Dream Home',
            'page_description' => 'Compare SIP returns vs real estate investment',
        ];
        $this->render('pages/tools/sip_vs_realestate', $data);
    }

    public function capitalGains()
    {
        $data = [
            'page_title' => 'Capital Gains Tax Calculator - APS Dream Home',
            'page_description' => 'Calculate capital gains tax on property sale',
        ];
        $this->render('pages/tools/capital_gains', $data);
    }

    public function gstCalculator()
    {
        $data = [
            'page_title' => 'GST Calculator - APS Dream Home',
            'page_description' => 'Calculate GST on property purchase',
        ];
        $this->render('pages/tools/gst_calculator', $data);
    }

    public function constructionCostEstimator()
    {
        $data = [
            'page_title' => 'Construction Cost Estimator - APS Dream Home',
            'page_description' => 'Estimate construction costs',
        ];
        $this->render('pages/tools/construction_cost', $data);
    }

    public function rentalYieldCalculator()
    {
        $data = [
            'page_title' => 'Rental Yield Calculator - APS Dream Home',
            'page_description' => 'Calculate rental yield on property',
        ];
        $this->render('pages/tools/rental_yield', $data);
    }

    public function propertyTaxCalculator()
    {
        $data = [
            'page_title' => 'Property Tax Calculator - APS Dream Home',
            'page_description' => 'Calculate property tax',
        ];
        $this->render('pages/tools/property_tax', $data);
    }

    public function calc()
    {
        $data = [
            'page_title' => 'Calculators - APS Dream Home',
            'page_description' => 'All financial calculators',
        ];
        $this->render('pages/calc', $data);
    }

    public function toolsHub()
    {
        $data = [
            'page_title' => 'Tools Hub - APS Dream Home',
            'page_description' => 'All free tools and calculators',
        ];
        $this->render('pages/tools/hub', $data);
    }

    public function partnerTools()
    {
        $data = [
            'page_title' => 'Partner Tools - APS Dream Home',
            'page_description' => 'Tools for property partners',
        ];
        $this->render('pages/tools/partner_tools', $data);
    }

    public function reraLookup()
    {
        $data = [
            'page_title' => 'RERA Lookup - APS Dream Home',
            'page_description' => 'Check RERA registration of projects',
        ];
        $this->render('pages/rera_lookup', $data);
    }
}