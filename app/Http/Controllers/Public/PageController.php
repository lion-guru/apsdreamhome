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

}
