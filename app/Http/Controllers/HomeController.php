<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Project;
use App\Models\Property;

class HomeController extends BaseController
{
    public function index()
    {
        logger()->info('HomeController::index called');

        try {
            $propertyModel = new Property();
            $properties = $propertyModel->getFeaturedProperties();
        } catch (\Throwable $e) {
            $properties = [];
        }

        $this->data['title'] = 'Welcome to APS Dream Home';
        $this->data['description'] = 'Find your dream property with us.';
        $this->data['properties'] = $properties;
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        return $this->render('home/index', [], 'layouts/base', true);
    }

    public function projects()
    {
        $projectModel = new Project();
        $projects = $projectModel->getAllActiveProjects();

        // If no projects in DB, use fallback static data (from legacy colonies.php)
        if (empty($projects)) {
            $projects = [
                [
                    'id' => 1,
                    'project_name' => 'APS Dream City Gorakhpur',
                    'location' => 'Gorakhpur, Uttar Pradesh',
                    'total_area' => '50 Acres',
                    'developed_area' => '35 Acres',
                    'total_plots' => 450,
                    'available_plots' => 120,
                    'base_price' => 1500000,
                    'project_status' => 'Phase 2 Ongoing',
                    'amenities' => 'Club House,Swimming Pool,Gym,Children Play Area,24/7 Security,Power Backup',
                    'meta_image' => '/assets/images/colonies/dream-city-gorakhpur.jpg',
                    'description' => 'Premium residential colony with modern amenities and excellent connectivity.',
                    'highlights' => 'Prime Location,Modern Infrastructure,Investment Opportunity',
                    'project_code' => 'dream-city-gkp'
                ],
                [
                    'id' => 2,
                    'project_name' => 'APS Royal Residency',
                    'location' => 'Lucknow, Uttar Pradesh',
                    'total_area' => '25 Acres',
                    'developed_area' => '20 Acres',
                    'total_plots' => 200,
                    'available_plots' => 45,
                    'base_price' => 2500000,
                    'project_status' => 'Phase 1 Complete',
                    'amenities' => 'Community Hall,Jogging Track,Landscaped Gardens,Security,Water Supply',
                    'meta_image' => '/assets/images/colonies/royal-residency.jpg',
                    'description' => 'Luxury residential project in the heart of Lucknow with world-class facilities.',
                    'highlights' => 'Premium Location,High Appreciation,Modern Design',
                    'project_code' => 'royal-residency-lko'
                ],
                [
                    'id' => 3,
                    'project_name' => 'APS Green Valley',
                    'location' => 'Kunraghat, Gorakhpur',
                    'total_area' => '30 Acres',
                    'developed_area' => '15 Acres',
                    'total_plots' => 300,
                    'available_plots' => 80,
                    'base_price' => 1200000,
                    'project_status' => 'Development Started',
                    'amenities' => 'Green Spaces,Community Center,Playground,Security,Basic Infrastructure',
                    'meta_image' => '/assets/images/colonies/green-valley.jpg',
                    'description' => 'Eco-friendly residential colony with abundant green spaces and natural surroundings.',
                    'highlights' => 'Eco-Friendly,Affordable Luxury,Natural Environment',
                    'project_code' => 'green-valley-gkp'
                ]
            ];
        }

        return $this->render('pages/projects', [
            'title' => 'Our Colonies - APS Dream Home',
            'projects' => $projects
        ]);
    }

    public function project($projectCode)
    {
        // TODO: Implement single project view
        // For now redirect to projects list
        $this->redirect('/projects');
    }

    public function featuredProperties()
    {
        $propertyModel = new Property();
        $properties = $propertyModel->getFeaturedProperties();

        return $this->render('pages/properties', [
            'title' => 'Featured Properties - APS Dream Home',
            'properties' => $properties,
            'is_featured' => true,
            'extra_css' => '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">'
        ]);
    }

    public function propertyDetail($id)
    {
        $propertyModel = new Property();
        $property = $propertyModel->getPropertyById($id);

        if (!$property) {
            // Handle not found
            return $this->render('errors/404', [], 'layouts/base');
        }

        $property_images = $property->getImages();

        return $this->render('pages/property_detail', [
            'title' => $property->title . ' - APS Dream Home',
            'property' => $property,
            'property_images' => $property_images,
            'extra_css' => '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">'
        ]);
    }

    public function sitemap()
    {
        return $this->render('pages/sitemap');
    }

    public function privacy()
    {
        $breadcrumbs = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Privacy Policy']
        ];
        return $this->render('pages/privacy_policy', ['breadcrumbs' => $breadcrumbs]);
    }

    public function terms()
    {
        $breadcrumbs = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Terms of Service']
        ];
        return $this->render('pages/terms-of-service', ['breadcrumbs' => $breadcrumbs]);
    }

    public function blogShow($slug)
    {
        // TODO: Implement blog show
        $this->redirect('/news');
    }

    public function about()
    {
        return $this->render('pages/about', [
            'title' => 'About Us - APS Dream Home',
            'extra_css' => '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">'
        ]);
    }

    public function contact()
    {
        return $this->render('pages/contact', [
            'title' => 'Contact Us - APS Dream Home',
            'contact_info' => [
                'phone' => '+91-9554224022',
                'email' => 'contact@apsdreamhome.com',
                'address' => 'Gorakhpur, Uttar Pradesh',
                'hours' => '9:00 AM - 6:00 PM'
            ],
            'extra_css' => '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">'
        ]);
    }
}
