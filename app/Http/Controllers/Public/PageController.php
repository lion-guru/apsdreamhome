<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;

/**
 * PageController Controller
 * Handles PageController related operations
 */
class PageController extends BaseController
{
    /**
     * Home method
     * @return void
     */
    public function home()
    {
        // TODO: Implement home functionality
        return $this->view('home');
    }
    
    /**
     * About method
     * @return void
     */
    public function about()
    {
        $this->data = [
            'title' => 'About Us - APS Dream Home',
            'description' => 'Learn about APS Dream Home - Leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction.',
            'company_info' => [
                'name' => 'APS Dream Homes Pvt Ltd',
                'established' => '2017',
                'experience' => '8+ Years',
                'projects' => '50+',
                'properties' => '500+',
                'happy_families' => '2000+',
                'registration_no' => 'U70109UP2022PTC163047'
            ],
            'mission' => 'To provide transparent and hassle-free real estate services with a focus on customer satisfaction and quality construction.',
            'vision' => 'To become the most trusted real estate developer in Uttar Pradesh by delivering excellence in every project.',
            'values' => [
                'Transparency',
                'Quality',
                'Customer Satisfaction',
                'Integrity',
                'Innovation'
            ],
            'team' => [
                (object)[
                    'name' => 'Amit Kumar Singh',
                    'position' => 'Managing Director',
                    'experience' => '15+ Years',
                    'description' => 'Leading the company with vision and expertise in real estate development.'
                ],
                (object)[
                    'name' => 'Priya Singh',
                    'position' => 'Operations Head',
                    'experience' => '10+ Years',
                    'description' => 'Managing day-to-day operations with focus on efficiency and quality.'
                ],
                (object)[
                    'name' => 'Rahul Verma',
                    'position' => 'Technical Director',
                    'experience' => '12+ Years',
                    'description' => 'Ensuring technical excellence and innovation in construction.'
                ]
            ]
        ];
        
        $this->render('about/index', $this->data, 'layouts/base');
    }

    /**
     * Contact method
     * @return void
     */
    public function contact()
    {
        // TODO: Implement contact functionality
        return $this->view('contact');
    }

    /**
     * Properties method
     * @return void
     */
    public function properties()
    {
        // TODO: Implement properties functionality
        return $this->view('properties');
    }

    /**
     * Services method
     * @return void
     */
    public function services()
    {
        // TODO: Implement services functionality
        return $this->view('services');
    }

    /**
     * Resell method
     * @return void
     */
    public function resell()
    {
        $data = [
            'page_title' => 'Resale Properties - APS Dream Home',
            'page_description' => 'Find the best resale properties in Gorakhpur, Lucknow and across Uttar Pradesh. Verified properties with clear documentation.',
            'resale_properties' => [
                [
                    'id' => 1,
                    'title' => '3BHK Apartment in Gomti Nagar',
                    'location' => 'Gomti Nagar, Lucknow',
                    'price' => 6500000,
                    'original_price' => 7500000,
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area' => 1450,
                    'age' => 5,
                    'type' => 'apartment',
                    'status' => 'available',
                    'description' => 'Well-maintained 3BHK apartment in prime location with modern amenities.',
                    'features' => ['Parking', 'Security', 'Power Backup', 'Lift'],
                    'images' => ['resale1.jpg', 'resale2.jpg'],
                    'listed_date' => '2026-02-15',
                    'views' => 245
                ],
                [
                    'id' => 2,
                    'title' => 'Independent House in Aliganj',
                    'location' => 'Aliganj, Lucknow',
                    'price' => 8500000,
                    'original_price' => 9500000,
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'area' => 2200,
                    'age' => 8,
                    'type' => 'house',
                    'status' => 'available',
                    'description' => 'Spacious independent house with garden and parking space.',
                    'features' => ['Garden', 'Parking', 'Security', 'Water Supply'],
                    'images' => ['resale3.jpg', 'resale4.jpg'],
                    'listed_date' => '2026-02-20',
                    'views' => 189
                ],
                [
                    'id' => 3,
                    'title' => '2BHK Flat in Indira Nagar',
                    'location' => 'Indira Nagar, Lucknow',
                    'price' => 4200000,
                    'original_price' => 4800000,
                    'bedrooms' => 2,
                    'bathrooms' => 2,
                    'area' => 980,
                    'age' => 3,
                    'type' => 'apartment',
                    'status' => 'available',
                    'description' => 'Modern 2BHK flat with excellent connectivity and amenities.',
                    'features' => ['Parking', 'Security', 'Lift', 'Community Hall'],
                    'images' => ['resale5.jpg', 'resale6.jpg'],
                    'listed_date' => '2026-02-25',
                    'views' => 156
                ]
            ],
            'resale_stats' => [
                'total_properties' => 150,
                'avg_savings' => 12,
                'verified_properties' => 145,
                'happy_customers' => 2800
            ],
            'resale_benefits' => [
                [
                    'icon' => 'fa-check-circle',
                    'title' => 'Legal Verification',
                    'description' => 'All resale properties are legally verified and documentation is clear.'
                ],
                [
                    'icon' => 'fa-money-bill',
                    'title' => 'Best Prices',
                    'description' => 'Get the best deals on resale properties with transparent pricing.'
                ],
                [
                    'icon' => 'fa-home',
                    'title' => 'Immediate Possession',
                    'description' => 'Most resale properties are ready for immediate possession.'
                ],
                [
                    'icon' => 'fa-shield-alt',
                    'title' => 'Quality Assurance',
                    'description' => 'Thorough inspection ensures quality and condition of properties.'
                ]
            ],
            'process_steps' => [
                [
                    'step' => 1,
                    'title' => 'Property Selection',
                    'description' => 'Choose from our verified list of resale properties.'
                ],
                [
                    'step' => 2,
                    'title' => 'Site Visit',
                    'description' => 'Visit the property with our expert team.'
                ],
                [
                    'step' => 3,
                    'title' => 'Documentation',
                    'description' => 'Complete all legal documentation smoothly.'
                ],
                [
                    'step' => 4,
                    'title' => 'Registration',
                    'description' => 'Final registration and possession handover.'
                ]
            ]
        ];

        return $this->render('resell/index', $data, 'layouts/base');
    }

}
