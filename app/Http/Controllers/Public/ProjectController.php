<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;
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
        $this->data['page_title'] = 'Raghunath Nagri Gorakhpur - APS Dream Homes';

        $this->data['amenities'] = $this->getProjectAmenities('amenities_gorakhpur');
        $this->data['videos'] = $this->getProjectVideos('project_videos_gorakhpur');

        return $this->render('pages/gorakhpur-raghunath-nagri');
    }

    /**
     * Suryoday Colony Gorakhpur Landing Page
     */
    public function suryodayColony()
    {
        $this->data['page_title'] = 'Suryoday Colony Gorakhpur - APS Dream Homes';

        $this->data['amenities'] = $this->getProjectAmenities('amenities_suryoday');
        $this->data['videos'] = $this->getProjectVideos('project_videos_suryoday');

        return $this->render('pages/gorakhpur-suryoday-colony');
    }

    /**
     * Ganga Nagri Varanasi Landing Page
     */
    public function gangaNagri()
    {
        $this->data['page_title'] = 'Ganga Nagri Varanasi - APS Dream Homes';

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
        $db = \App\Core\Database::getInstance();
        $id = (int)$id;

        try {
            // Get project details
            $sql = "SELECT * FROM projects WHERE id = ? AND status = 'active'";
            $project = $db->fetch($sql, [$id]);

            if (!$project) {
                return $this->redirect('/projects');
            }

            // Get related projects (same location)
            $sql = "SELECT id, name, image_path, location, status FROM projects 
                    WHERE location = ? AND id != ? AND status = 'active' LIMIT 3";
            $relatedProjects = $db->fetchAll($sql, [$project['location'], $id]);

            $this->data['project'] = $project;
            $this->data['relatedProjects'] = $relatedProjects;
            $this->data['page_title'] = ($project['name'] ?? 'Project Details') . ' - APS Dream Homes';
            
            return $this->render('pages/project-details');

        } catch (Exception $e) {
            logger()->error("Error loading project details: " . $e->getMessage());
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
