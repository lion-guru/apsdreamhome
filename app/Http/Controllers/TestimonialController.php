<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

/**
 * TestimonialController Controller
 * Handles Testimonial related operations
 */
class TestimonialController extends BaseController
{
    /**
     * Index method - Show testimonials
     * @return void
     */
    public function index()
    {
        $this->render('testimonials/index', [
            'page_title' => 'Testimonials - APS Dream Home',
            'page_description' => 'Read what our satisfied customers have to say about APS Dream Home',
            'testimonials' => [
                [
                    'id' => 1,
                    'name' => 'Ramesh Kumar',
                    'property' => '3BHK Apartment, Gomti Nagar',
                    'content' => 'Excellent service and transparent dealing. Got my dream home within budget. Highly recommended!',
                    'rating' => 5,
                    'date' => '2024-01-15',
                    'image' => '/assets/images/customer-1.jpg'
                ],
                [
                    'id' => 2,
                    'name' => 'Priya Singh',
                    'property' => '2BHK Villa, Hazratganj',
                    'content' => 'Professional team and quality construction. The entire process was smooth and hassle-free.',
                    'rating' => 5,
                    'date' => '2024-01-20',
                    'image' => '/assets/images/customer-2.jpg'
                ],
                [
                    'id' => 3,
                    'name' => 'Amit Verma',
                    'property' => 'Commercial Space, Vibhuti Khand',
                    'content' => 'Great investment opportunity. APS Dream Home delivered exactly what they promised.',
                    'rating' => 5,
                    'date' => '2024-02-01',
                    'image' => '/assets/images/customer-3.jpg'
                ],
                [
                    'id' => 4,
                    'name' => 'Sunita Sharma',
                    'property' => '1BHK Apartment, Alambagh',
                    'content' => 'Amazing experience from start to finish. Team is very supportive and understanding.',
                    'rating' => 4,
                    'date' => '2024-02-10',
                    'image' => '/assets/images/customer-4.jpg'
                ],
                [
                    'id' => 5,
                    'name' => 'Rahul Gupta',
                    'property' => 'Plot in Gomti Nagar',
                    'content' => 'Transparent pricing and clear documentation. Best real estate experience ever.',
                    'rating' => 5,
                    'date' => '2024-02-15',
                    'image' => '/assets/images/customer-5.jpg'
                ]
            ]
        ], 'layouts/base');
    }
}
