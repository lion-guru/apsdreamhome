<?php
namespace App\Http\Controllers;

/**
 * Gallery Controller
 * Manage photo gallery and property showcase
 */
class GalleryController extends BaseController
{
    /**
     * Gallery index page
     */
    public function index()
    {
        $this->render('pages/gallery', [
            'page_title' => 'Gallery - APS Dream Home',
            'page_description' => 'Explore our completed projects and property showcase'
        ]);
    }
    
    /**
     * Project gallery
     */
    public function project($projectId)
    {
        $this->render('pages/project-gallery', [
            'page_title' => 'Project Gallery - APS Dream Home',
            'page_description' => 'View detailed images of our completed projects',
            'project_id' => $projectId
        ]);
    }
}
