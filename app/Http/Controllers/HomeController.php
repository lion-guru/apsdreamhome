<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        error_log("HomeController::index() started");

        // Get featured properties
        try {
            $propertyModel = new \App\Models\Property();
            error_log("Property model instantiated");
            $properties = $propertyModel->getFeaturedProperties();
            error_log("Properties fetched: " . count($properties));
        } catch (\Throwable $e) {
            error_log("Error in HomeController::index: " . $e->getMessage());
            $properties = [];
        }

        // Render the home view using BaseController's render method (uses layout)
        $this->data['title'] = 'Welcome to APS Dream Home';
        $this->data['description'] = 'Find your dream property with us.';
        $this->data['properties'] = $properties;

        error_log("Calling render('home/index')");
        return $this->render('home/index', [], null, false);
    }

    public function projects()
    {
        $projectModel = new \App\Models\Project();
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

        $this->view('home/projects', [
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
        $propertyModel = new \App\Models\Property();
        $properties = $propertyModel->getFeaturedProperties();

        $this->view('home/properties', [
            'title' => 'Featured Properties - APS Dream Home',
            'properties' => $properties,
            'is_featured' => true
        ]);
    }

    public function propertyDetail($id)
    {
        $propertyModel = new \App\Models\Property();
        $property = $propertyModel->getPropertyById($id);

        if (!$property) {
            // Handle not found
            $this->view('errors/404');
            return;
        }

        $this->view('home/property_detail', [
            'title' => $property->title . ' - APS Dream Home',
            'property' => $property
        ]);
    }

    public function sitemap()
    {
        $this->view('home/sitemap');
    }

    public function privacy()
    {
        $this->view('home/privacy');
    }

    public function terms()
    {
        $this->view('home/terms');
    }

    public function blogShow($slug)
    {
        // TODO: Implement blog show
        $this->redirect('/news');
    }
}
