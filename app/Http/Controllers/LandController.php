<?php

namespace App\Http\Controllers;

use App\Services\Land\PlottingService;
use App\Models\LandProject;
use App\Models\Plot;
use Psr\Log\LoggerInterface;

class LandController
{
    private PlottingService $plottingService;
    private LoggerInterface $logger;

    public function __construct(PlottingService $plottingService, LoggerInterface $logger)
    {
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
            
            return view('land.dashboard', [
                'stats' => $stats,
                'page_title' => 'Land Management Dashboard - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load land dashboard", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }

    /**
     * Create new land project
     */
    public function createProject()
    {
        try {
            $data = request()->all();
            $documents = request()->files('documents', []);

            $result = $this->plottingService->createProject($data, $documents);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'project_id' => $result['project_id']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to create project", ['error' => $e->getMessage()]);
            return response()->json([
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
            $data = request()->all();

            $result = $this->plottingService->subdivideLand((int)$projectId, $data);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'subdivision_id' => $result['subdivision_id'],
                    'plots_generated' => $result['plots_generated']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to subdivide land", [
                'project_id' => $projectId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to subdivide land'
            ], 500);
        }
    }

    /**
     * Create new plot
     */
    public function createPlot($projectId)
    {
        try {
            $data = request()->all();

            $result = $this->plottingService->createPlot((int)$projectId, $data);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'plot_id' => $result['plot_id'],
                    'plot_number' => $result['plot_number']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to create plot", [
                'project_id' => $projectId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create plot'
            ], 500);
        }
    }

    /**
     * Reserve plot
     */
    public function reservePlot($plotId)
    {
        try {
            $customerData = request()->all();
            $paymentData = request()->input('payment_data', []);

            $result = $this->plottingService->reservePlot((int)$plotId, $customerData, $paymentData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'reservation_id' => $result['reservation_id']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to reserve plot", [
                'plot_id' => $plotId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
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
            $saleData = request()->all();

            $result = $this->plottingService->sellPlot((int)$plotId, $saleData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'sale_id' => $result['sale_id']
                ]);
            } else {
                return response()->json([
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to sell plot'
            ], 500);
        }
    }

    /**
     * Get project details
     */
    public function getProject($id)
    {
        try {
            $project = $this->plottingService->getProject((int)$id);
            
            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'project' => $project
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get project", ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get project'
            ], 500);
        }
    }

    /**
     * Get plot details
     */
    public function getPlot($id)
    {
        try {
            $plot = $this->plottingService->getPlot((int)$id);
            
            if (!$plot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plot not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'plot' => $plot
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get plot", ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get plot'
            ], 500);
        }
    }

    /**
     * Get projects list
     */
    public function getProjects()
    {
        try {
            $filters = request()->all();
            $projects = $this->plottingService->getProjects($filters);

            return response()->json([
                'success' => true,
                'projects' => $projects,
                'total' => count($projects)
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get projects", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get projects'
            ], 500);
        }
    }

    /**
     * Get plots list
     */
    public function getPlots()
    {
        try {
            $filters = request()->all();
            $plots = $this->plottingService->getPlots($filters);

            return response()->json([
                'success' => true,
                'plots' => $plots,
                'total' => count($plots)
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get plots", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get plots'
            ], 500);
        }
    }

    /**
     * Get plotting statistics
     */
    public function getStats()
    {
        try {
            $filters = request()->all();
            $stats = $this->plottingService->getPlottingStats($filters);

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get stats", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics'
            ], 500);
        }
    }

    /**
     * Project details page
     */
    public function projectDetails($id)
    {
        try {
            $project = $this->plottingService->getProject((int)$id);
            
            if (!$project) {
                return redirect('/land/dashboard')->with('error', 'Project not found');
            }

            return view('land.project-details', [
                'project' => $project,
                'page_title' => 'Project Details - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load project details", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return view('errors.500');
        }
    }

    /**
     * Plot details page
     */
    public function plotDetails($id)
    {
        try {
            $plot = $this->plottingService->getPlot((int)$id);
            
            if (!$plot) {
                return redirect('/land/dashboard')->with('error', 'Plot not found');
            }

            return view('land.plot-details', [
                'plot' => $plot,
                'page_title' => 'Plot Details - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load plot details", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return view('errors.500');
        }
    }

    /**
     * Export projects to CSV
     */
    public function exportProjects()
    {
        try {
            $filters = request()->all();
            $projects = $this->plottingService->getProjects($filters);

            $csvData = [];
            $csvData[] = ['ID', 'Name', 'Location', 'Status', 'Total Area', 'Total Plots', 'Sold Plots', 'Available Plots', 'Completion %', 'Created Date'];

            foreach ($projects as $project) {
                $csvData[] = [
                    $project['id'],
                    $project['name'],
                    $project['location'],
                    $project['status'],
                    $project['total_area'],
                    $project['total_plots'],
                    $project['sold_plots'],
                    $project['available_plots'],
                    round($project['completion_percentage'], 2) . '%',
                    $project['created_at']
                ];
            }

            $filename = 'land_projects_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($output, $row);
            }
            fclose($output);

        } catch (\Exception $e) {
            $this->logger->error("Failed to export projects", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to export projects'
            ], 500);
        }
    }

    /**
     * Export plots to CSV
     */
    public function exportPlots()
    {
        try {
            $filters = request()->all();
            $plots = $this->plottingService->getPlots($filters);

            $csvData = [];
            $csvData[] = ['ID', 'Plot Number', 'Project', 'Type', 'Size (sqm)', 'Price/sqm', 'Total Price', 'Status', 'Facing', 'Created Date'];

            foreach ($plots as $plot) {
                $csvData[] = [
                    $plot['id'],
                    $plot['plot_number'],
                    $plot['project_name'],
                    $plot['plot_type'],
                    $plot['size_sq_meters'],
                    $plot['price_per_sq_meter'],
                    $plot['total_price'],
                    $plot['status'],
                    $plot['facing_direction'] ?? '',
                    $plot['created_at']
                ];
            }

            $filename = 'land_plots_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($output, $row);
            }
            fclose($output);

        } catch (\Exception $e) {
            $this->logger->error("Failed to export plots", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to export plots'
            ], 500);
        }
    }

    /**
     * Get project analytics
     */
    public function getProjectAnalytics($projectId)
    {
        try {
            $project = LandProject::find($projectId);
            
            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $analytics = [
                'statistics' => $project->statistics_summary,
                'plot_distribution' => $project->plot_distribution_by_type,
                'monthly_sales_trend' => $project->monthly_sales_trend,
                'top_performing_types' => $project->top_performing_plot_types,
                'critical_issues' => $project->critical_issues,
                'recommendations' => $project->recommendations
            ];

            return response()->json([
                'success' => true,
                'analytics' => $analytics
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get project analytics", [
                'project_id' => $projectId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get analytics'
            ], 500);
        }
    }

    /**
     * Get land market insights
     */
    public function getMarketInsights()
    {
        try {
            $projects = LandProject::all();
            
            $insights = [
                'total_projects' => $projects->count(),
                'active_projects' => $projects->where('status', 'development')->count(),
                'completed_projects' => $projects->where('status', 'handover')->count(),
                'total_plots' => $projects->sum(function($project) {
                    return $project->plots()->count();
                }),
                'total_revenue' => $projects->sum('total_revenue'),
                'average_roi' => $projects->avg('roi_percentage'),
                'project_types' => $projects->groupBy('project_type')->map->count(),
                'regional_distribution' => $projects->groupBy('location')->map->count(),
                'delayed_projects' => $projects->filter(fn($project) => $project->isDelayed())->count()
            ];

            return response()->json([
                'success' => true,
                'insights' => $insights
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get market insights", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get market insights'
            ], 500);
        }
    }

    /**
     * Land management settings page
     */
    public function settings()
    {
        try {
            return view('land.settings', [
                'page_title' => 'Land Management Settings - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load land settings", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }
}
