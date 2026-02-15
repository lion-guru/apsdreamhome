<?php

/**
 * Company Projects Controller
 * Handles company projects and portfolio display
 */

namespace App\Http\Controllers\Property;

use App\Http\Controllers\BaseController;
use Exception;
use PDO;

class CompanyProjectsController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display company projects portfolio
     */
    public function index()
    {
        // Get company projects from database
        $company_projects = [];
        try {
            if ($this->db) {
                $projects_query = "
                    SELECT
                        cp.*,
                        p.title as property_title,
                        p.price,
                        p.image_url,
                        p.location,
                        p.type,
                        COUNT(cp.id) as project_count
                    FROM company_projects cp
                    LEFT JOIN properties p ON cp.property_id = p.id
                    GROUP BY cp.id
                    ORDER BY cp.created_at DESC
                ";
                $stmt = $this->db->prepare($projects_query);
                $stmt->execute();
                $company_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log('Company projects fetch error: ' . $e->getMessage());
        }

        // Get project statistics
        $project_stats = [
            'total' => count($company_projects),
            'completed' => 0,
            'ongoing' => 0,
            'upcoming' => 0,
            'total_value' => 0
        ];

        foreach ($company_projects as $project) {
            if ($project['status'] == 'completed') $project_stats['completed']++;
            if ($project['status'] == 'ongoing') $project_stats['ongoing']++;
            if ($project['status'] == 'upcoming') $project_stats['upcoming']++;
            $project_stats['total_value'] += $project['budget'] ?? 0;
        }

        // Load the company projects view
        $this->render('pages/company_projects', [
            'company_projects' => $company_projects,
            'project_stats' => $project_stats,
            'page_title' => 'Company Projects & Portfolio - APS Dream Homes'
        ]);
    }

    /**
     * Get project details by ID
     */
    public function projectDetails($projectId)
    {
        try {
            if ($this->db) {
                $project_query = "
                    SELECT cp.*, p.*
                    FROM company_projects cp
                    LEFT JOIN properties p ON cp.property_id = p.id
                    WHERE cp.id = :projectId
                ";
                $stmt = $this->db->prepare($project_query);
                $stmt->execute(['projectId' => $projectId]);
                $project = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($project) {
                    $this->render('pages/project_details', [
                        'project' => $project,
                        'page_title' => $project['title'] . ' - Project Details'
                    ]);
                } else {
                    $this->render('errors/404', [
                        'message' => 'Project not found'
                    ]);
                }
            }
        } catch (Exception $e) {
            error_log('Project details fetch error: ' . $e->getMessage());
            $this->render('errors/500', [
                'message' => 'Error loading project details'
            ]);
        }
    }

    /**
     * Filter projects by status/type
     */
    public function filterProjects()
    {
        $filter = $_GET['filter'] ?? 'all';
        $type = $_GET['type'] ?? 'all';

        try {
            if (!$this->db) {
                throw new Exception("Database connection failed");
            }

            $where_conditions = [];
            $params = [];

            if ($filter !== 'all') {
                $where_conditions[] = "cp.status = :filter";
                $params['filter'] = $filter;
            }

            if ($type !== 'all') {
                $where_conditions[] = "cp.project_type = :type";
                $params['type'] = $type;
            }

            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

            $projects_query = "
                SELECT cp.*, p.*
                FROM company_projects cp
                LEFT JOIN properties p ON cp.property_id = p.id
                {$where_clause}
                ORDER BY cp.created_at DESC
            ";

            $stmt = $this->db->prepare($projects_query);
            $stmt->execute($params);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'projects' => $projects,
                'count' => count($projects)
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
