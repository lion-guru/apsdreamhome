<?php

namespace App\Http\Controllers;

use App\Services\Land\PlottingService;
use App\Services\SystemLogger as Logger;
use App\Models\LandProject;
use App\Models\Plot;

class LandController extends BaseController
{
    private PlottingService $plottingService;
    private $logger;

    public function __construct(PlottingService $plottingService, Logger $logger)
    {
        parent::__construct();
        $this->plottingService = $plottingService;
        $this->logger = $logger;
    }

    /**
     * Display land management dashboard
     */
    public function dashboard()
    {
        try {
            $stats = $this->plottingService->getPlottingStats();

            return $this->view('land.dashboard', [
                'stats' => $stats,
                'page_title' => 'Land Management Dashboard - APS Dream Home'
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to load land dashboard", ['error' => $e->getMessage()]);
            return $this->response(['success' => false, 'message' => 'Dashboard error'], 500);
        }
    }

    /**
     * Create new land project
     */
    public function createProject()
    {
        try {
            $data = $_REQUEST;
            $documents = isset($_FILES['documents']) ? $_FILES['documents'] : [];

            $result = $this->plottingService->createProject($data, $documents);

            if ($result['success']) {
                return $this->response([
                    'success' => true,
                    'message' => $result['message'],
                    'project_id' => $result['project_id']
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to create project", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to create project'
            ], 500);
        }
    }

    /**
     * Subdivide land into plots
     */
    public function subdivideLand($projectId)
    {
        try {
            $data = $_REQUEST;

            $result = $this->plottingService->subdivideLand((int)$projectId, $data);

            if ($result['success']) {
                return $this->response([
                    'success' => true,
                    'message' => $result['message'],
                    'plots' => $result['plots']
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to subdivide land", ['project_id' => $projectId, 'error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to subdivide land'
            ], 500);
        }
    }

    /**
     * Reserve plot
     */
    public function reservePlot($plotId)
    {
        try {
            $data = $_REQUEST;

            $result = $this->plottingService->reservePlot((int)$plotId, $data, $this->logger);

            if ($result['success']) {
                return $this->response([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to reserve plot", [
                'plot_id' => $plotId,
                'error' => $e->getMessage()
            ]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to reserve plot'
            ], 500);
        }
    }

    /**
     * Sell plot
     */
    public function sellPlot($plotId)
    {
        try {
            $data = $_REQUEST;

            $result = $this->plottingService->sellPlot((int)$plotId, $data, $this->logger);

            if ($result['success']) {
                return $this->response([
                    'success' => true,
                    'message' => $result['message'],
                    'sale_id' => $result['sale_id']
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to sell plot", [
                'plot_id' => $plotId,
                'error' => $e->getMessage()
            ]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to sell plot'
            ], 500);
        }
    }

    /**
     * Get project details
     */
    public function getProject($projectId)
    {
        try {
            $project = $this->plottingService->getProject((int)$projectId);

            if ($project) {
                return $this->response([
                    'success' => true,
                    'data' => $project
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to get project", ['project_id' => $projectId, 'error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to get project'
            ], 500);
        }
    }

    /**
     * Get plot details
     */
    public function getPlot($plotId)
    {
        try {
            $plot = $this->plottingService->getPlot((int)$plotId);

            if ($plot) {
                return $this->response([
                    'success' => true,
                    'data' => $plot
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => 'Plot not found'
                ], 404);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to get plot", ['plot_id' => $plotId, 'error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to get plot'
            ], 500);
        }
    }

    /**
     * Get available plots
     */
    public function getAvailablePlots()
    {
        try {
            $filters = $this->request->all();
            $plots = $this->plottingService->getAvailablePlots($filters);

            return $this->response([
                'success' => true,
                'data' => $plots
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get available plots", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to get available plots'
            ], 500);
        }
    }

    /**
     * Update plot status
     */
    public function updatePlotStatus($plotId)
    {
        try {
            $data = $_REQUEST;

            $result = $this->plottingService->updatePlotStatus((int)$plotId, $data['status']);

            if ($result['success']) {
                return $this->response([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to update plot status", ['plot_id' => $plotId, 'error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to update plot status'
            ], 500);
        }
    }
}
