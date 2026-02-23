<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;
use App\Models\Project;
use App\Core\Database\Model;
use Exception;

class ProjectController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Raghunath Nagri Gorakhpur Landing Page
     */
    public function raghunathNagri()
    {
        $slug = 'gorakhpur-raghunath-nagri';
        $project = Project::query()->where('slug', $slug)->first();

        // If project not found in DB, we can use fallback data or redirect.
        // For now, we assume check_db.php has seeded it.

        $this->data['page_title'] = ($project['name'] ?? 'Raghunath Nagri') . ' - APS Dream Homes';
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Projects', 'url' => BASE_URL . 'projects'],
            ['title' => 'Raghunath Nagri', 'url' => BASE_URL . 'projects/gorakhpur-raghunath-nagri']
        ];
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        $this->data['project'] = $project;
        $this->data['amenities'] = $this->getProjectAmenities('amenities_gorakhpur');
        $this->data['videos'] = $this->getProjectVideos('project_videos_gorakhpur');

        return $this->render('pages/gorakhpur-raghunath-nagri');
    }

    /**
     * Suryoday Colony Gorakhpur Landing Page
     */
    public function suryodayColony()
    {
        $slug = 'gorakhpur-suryoday-colony';
        $project = Project::query()->where('slug', $slug)->first();

        $this->data['page_title'] = ($project['name'] ?? 'Suryoday Colony') . ' - APS Dream Homes';
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Projects', 'url' => BASE_URL . 'projects'],
            ['title' => 'Suryoday Colony', 'url' => BASE_URL . 'projects/gorakhpur-suryoday-colony']
        ];
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        $this->data['project'] = $project;
        $this->data['amenities'] = $this->getProjectAmenities('amenities_suryoday');
        $this->data['videos'] = $this->getProjectVideos('project_videos_suryoday');

        return $this->render('pages/gorakhpur-suryoday-colony');
    }

    /**
     * Ganga Nagri Varanasi Landing Page
     */
    public function gangaNagri()
    {
        $slug = 'varanasi-ganga-nagri';
        $project = Project::query()->where('slug', $slug)->first();

        $this->data['page_title'] = ($project['name'] ?? 'Ganga Nagri') . ' - APS Dream Homes';
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Projects', 'url' => BASE_URL . 'projects'],
            ['title' => 'Ganga Nagri', 'url' => BASE_URL . 'projects/varanasi-ganga-nagri']
        ];
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        $this->data['project'] = $project;
        $this->data['amenities'] = $this->getProjectAmenities('amenities_varanasi');
        $this->data['videos'] = $this->getProjectVideos('project_videos_varanasi');

        return $this->render('pages/varanasi-ganga-nagri');
    }

    /**
     * Show project details
     * 
     * @param int $id
     */
    public function show($id)
    {
        $id = (int)$id;

        try {
            // Get project details
            $project = Project::find($id);

            if (!$project || $project['status'] !== 'active') {
                return $this->redirect('/projects');
            }

            // Get related projects (same location)
            $relatedProjects = Project::query()
                ->where('location', $project['location'])
                ->where('id', '!=', $id)
                ->where('status', 'active')
                ->limit(3)
                ->get();

            $this->data['project'] = $project;
            $this->data['relatedProjects'] = $relatedProjects;
            $this->data['page_title'] = ($project['name'] ?? 'Project Details') . ' - APS Dream Homes';

            $this->data['breadcrumbs'] = [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Projects', 'url' => BASE_URL . 'projects'],
                ['title' => $project['name'], 'url' => BASE_URL . 'projects/' . $id]
            ];
            $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

            return $this->render('pages/project-details');
        } catch (Exception $e) {
            error_log("Error loading project details: " . $e->getMessage());
            return $this->redirect('/projects');
        }
    }

    private function getProjectAmenities($table)
    {
        try {
            return Model::query()
                ->from($table)
                ->select('title', 'image', 'alt_text')
                ->orderBy('id', 'ASC')
                ->get();
        } catch (Exception $e) {
            return [
                ['title' => '24/7 Security', 'image' => 'amenities/1.jpg', 'alt_text' => '24/7 Security'],
                ['title' => 'Clubhouse', 'image' => 'amenities/2.jpg', 'alt_text' => 'Clubhouse'],
                ['title' => 'Gymnasium', 'image' => 'amenities/3.jpg', 'alt_text' => 'Gymnasium'],
                ['title' => 'Swimming Pool', 'image' => 'amenities/4.jpg', 'alt_text' => 'Swimming Pool'],
                ['title' => "Children's Play Area", 'image' => 'amenities/5.jpg', 'alt_text' => "Children's Play Area"],
                ['title' => 'Landscaped Gardens', 'image' => 'amenities/6.jpg', 'alt_text' => 'Landscaped Gardens'],
                ['title' => 'Power Backup', 'image' => 'amenities/7.jpg', 'alt_text' => 'Power Backup'],
            ];
        }
    }

    private function getProjectVideos($table)
    {
        try {
            return Model::query()
                ->from($table)
                ->select('title', 'youtube_id')
                ->orderBy('id', 'ASC')
                ->get();
        } catch (Exception $e) {
            return [
                ['title' => 'Project Overview', 'youtube_id' => 'BhUOvYwfIcQ'],
                ['title' => 'Location Walkthrough', 'youtube_id' => '8aR_447wdnQ'],
                ['title' => 'Amenities Showcase', 'youtube_id' => 'VhGCJ6P_PjU'],
                ['title' => 'Customer Testimonials', 'youtube_id' => 'BhUOvYwfIcQ'],
            ];
        }
    }
}
