<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

/**
 * ResellController Controller
 * Handles Property Resale related operations
 */
class ResellController extends BaseController
{
    /**
     * Index method - Show resell properties
     * @return void
     */
    public function index()
    {
        $this->render('resell/index', [
            'page_title' => 'Resell Properties - APS Dream Home',
            'page_description' => 'Find resale properties with great value and investment potential',
            'resell_properties' => [
                [
                    'id' => 1,
                    'title' => '3BHK Apartment in Gomti Nagar',
                    'location' => 'Gomti Nagar, Lucknow',
                    'original_price' => '₹85 Lakhs',
                    'resell_price' => '₹75 Lakhs',
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area' => 1500,
                    'age' => '3 years',
                    'reason' => 'Owner relocating abroad',
                    'image' => '/assets/images/resell/apartment-1.jpg',
                    'featured' => true
                ],
                [
                    'id' => 2,
                    'title' => 'Independent House in Indira Nagar',
                    'location' => 'Indira Nagar, Lucknow',
                    'original_price' => '₹1.5 Crore',
                    'resell_price' => '₹1.35 Crore',
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'area' => 2000,
                    'age' => '5 years',
                    'reason' => 'Upgrading to larger property',
                    'image' => '/assets/images/resell/house-1.jpg',
                    'featured' => true
                ],
                [
                    'id' => 3,
                    'title' => '2BHK Apartment in Alambagh',
                    'location' => 'Alambagh, Lucknow',
                    'original_price' => '₹45 Lakhs',
                    'resell_price' => '₹38 Lakhs',
                    'bedrooms' => 2,
                    'bathrooms' => 2,
                    'area' => 1000,
                    'age' => '2 years',
                    'reason' => 'Financial requirement',
                    'image' => '/assets/images/resell/apartment-2.jpg',
                    'featured' => false
                ],
                [
                    'id' => 4,
                    'title' => 'Commercial Space in Vibhuti Khand',
                    'location' => 'Vibhuti Khand, Gomti Nagar',
                    'original_price' => '₹1.2 Crore',
                    'resell_price' => '₹95 Lakhs',
                    'bedrooms' => 0,
                    'bathrooms' => 2,
                    'area' => 1200,
                    'age' => '4 years',
                    'reason' => 'Business expansion',
                    'image' => '/assets/images/resell/commercial-1.jpg',
                    'featured' => false
                ]
            ]
        ], 'layouts/base');
    }
}
