<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\BaseController;

/**
 * PropertyController
 * Handles property related operations
 */
class PropertyController extends BaseController
{
    /**
     * Index method - List all properties
     * @return void
     */
    public function index()
    {
        $data = [
            'page_title' => 'Properties - APS Dream Home',
            'page_description' => 'Browse our extensive collection of residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh.',
            'properties' => [
                [
                    'id' => 1,
                    'title' => 'Luxury Apartment in Gomti Nagar',
                    'location' => 'Gomti Nagar, Lucknow',
                    'price' => 7500000,
                    'type' => 'apartment',
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area' => '1500 Sq.ft',
                    'featured' => true,
                    'image' => 'images/properties/luxury-apartment-1.jpg',
                    'description' => 'Spacious 3BHK luxury apartment with modern amenities and prime location in Gomti Nagar.',
                    'amenities' => ['Parking', 'Swimming Pool', 'Gym', 'Security', 'Power Backup'],
                    'status' => 'ready-to-move'
                ],
                [
                    'id' => 2,
                    'title' => 'Modern Villa in Hazratganj',
                    'location' => 'Hazratganj, Lucknow',
                    'price' => 12000000,
                    'type' => 'villa',
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'area' => '2000 Sq.ft',
                    'featured' => true,
                    'image' => 'images/properties/modern-villa-1.jpg',
                    'description' => 'Elegant 4BHK villa with private garden and premium finishing in heart of Hazratganj.',
                    'amenities' => ['Private Garden', 'Swimming Pool', 'Gym', 'Security', 'Power Backup', 'Servant Room'],
                    'status' => 'ready-to-move'
                ]
            ]
        ];

        return $this->render('properties/index', $data, 'layouts/base');
    }

    /**
     * Show method - Display single property
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        $data = [
            'page_title' => 'Property Details - APS Dream Home',
            'page_description' => 'View detailed information about this property including features, amenities, and contact details.'
        ];

        return $this->render('properties/show', $data, 'layouts/base');
    }
}
