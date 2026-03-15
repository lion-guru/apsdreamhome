<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Http\Controllers;

use App\Core\Security;

require_once __DIR__ . '/BaseController.php';

/**
 * GalleryController - Handles photo and video gallery
 */
class GalleryController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Index method - Show gallery
     */
    public function index()
    {
        $category = Security::sanitize($_GET['category']) ?? 'all';

        // Sample gallery data with Google Images/YouTube
        $gallery_items = [
            [
                'id' => 1,
                'title' => 'APS Gardenia - Exterior View',
                'type' => 'image',
                'category' => 'completed',
                'url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800',
                'thumbnail' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400',
                'description' => 'Beautiful exterior view of APS Gardenia residential project'
            ],
            [
                'id' => 2,
                'title' => 'APS Heights - Lobby Area',
                'type' => 'image',
                'category' => 'completed',
                'url' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800',
                'thumbnail' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=400',
                'description' => 'Modern lobby area of APS Heights commercial project'
            ],
            [
                'id' => 3,
                'title' => 'APS Gardenia Construction Video',
                'type' => 'video',
                'category' => 'ongoing',
                'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'thumbnail' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
                'description' => 'Construction progress video of APS Gardenia project'
            ],
            [
                'id' => 4,
                'title' => 'APS Residency - Apartment Interior',
                'type' => 'image',
                'category' => 'completed',
                'url' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=800',
                'thumbnail' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=400',
                'description' => 'Modern interior design of APS Residency apartments'
            ],
            [
                'id' => 5,
                'title' => 'APS Plaza - Commercial Space',
                'type' => 'image',
                'category' => 'ongoing',
                'url' => 'https://images.unsplash.com/photo-1497366214041-937c73f5ca5c?w=800',
                'thumbnail' => 'https://images.unsplash.com/photo-1497366214041-937c73f5ca5c?w=400',
                'description' => 'Commercial spaces available at APS Plaza'
            ],
            [
                'id' => 6,
                'title' => 'Project Virtual Tour',
                'type' => 'video',
                'category' => 'completed',
                'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'thumbnail' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
                'description' => 'Complete virtual tour of our completed projects'
            ]
        ];

        // Filter by category
        if ($category !== 'all') {
            $gallery_items = array_filter($gallery_items, function ($item) use ($category) {
                return $item['category'] === $category;
            });
        }

        $this->render('gallery/index', [
            'page_title' => 'Gallery - APS Dream Home',
            'page_description' => 'Explore our project gallery with photos and videos',
            'gallery_items' => $gallery_items,
            'current_category' => $category
        ], 'layouts/base');
    }

    /**
     * Show project-specific gallery
     */
    public function project($projectId)
    {
        // Sample project gallery data with Google Images/YouTube
        $project_galleries = [
            1 => [
                'project_name' => 'APS Gardenia',
                'project_description' => 'Luxury residential apartments in Gomti Nagar with modern amenities',
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800', 'title' => 'Main Entrance'],
                    ['url' => 'https://images.unsplash.com/photo-1600566753376-12c10ab43890?w=800', 'title' => 'Garden Area'],
                    ['url' => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800', 'title' => 'Swimming Pool'],
                    ['url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=800', 'title' => 'Gymnasium'],
                    ['url' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800', 'title' => 'Club House']
                ],
                'videos' => [
                    ['url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'title' => 'Project Overview'],
                    ['url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'title' => 'Virtual Tour']
                ],
                'location' => [
                    'lat' => 26.8467,
                    'lng' => 80.9462,
                    'address' => 'Gomti Nagar, Lucknow, Uttar Pradesh'
                ]
            ],
            2 => [
                'project_name' => 'APS Heights',
                'project_description' => 'Premium commercial spaces in Hazratganj with modern infrastructure',
                'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800', 'title' => 'Building Exterior'],
                    ['url' => 'https://images.unsplash.com/photo-1497366214041-937c73f5ca5c?w=800', 'title' => 'Office Spaces'],
                    ['url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800', 'title' => 'Conference Rooms'],
                    ['url' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800', 'title' => 'Parking Area'],
                    ['url' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800', 'title' => 'Food Court']
                ],
                'videos' => [
                    ['url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'title' => 'Commercial Spaces Tour']
                ],
                'location' => [
                    'lat' => 26.8567,
                    'lng' => 80.9362,
                    'address' => 'Hazratganj, Lucknow, Uttar Pradesh'
                ]
            ]
        ];

        $project_gallery = $project_galleries[$projectId] ?? null;

        if (!$project_gallery) {
            $this->setFlash('error', 'Project not found');
            $this->redirect('/gallery');
            return;
        }

        $this->render('gallery/project', [
            'page_title' => $project_gallery['project_name'] . ' Gallery - APS Dream Home',
            'page_description' => 'View photos and videos of ' . $project_gallery['project_name'],
            'project' => $project_gallery,
            'project_id' => $projectId
        ], 'layouts/base');
    }
}
