<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Plot;
use App\Core\Database;

class PlotController extends AdminController
{
    protected $plotModel;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);

        $this->plotModel = $this->model('Plot');
    }

    /**
     * List all plots
     */
    public function index()
    {
        $plots = $this->plotModel->getAllPlots();
        return $this->render('admin/plots/index', [
            'plots' => $plots,
            'page_title' => $this->mlSupport->translate('Plot Management') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Show create plot form
     */
    public function create()
    {
        return $this->render('admin/plots/create', [
            'page_title' => $this->mlSupport->translate('Add New Plot') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Store new plot
     */
    public function store()
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect('/admin/plots/create');
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->redirect('/admin/plots/create');
        }

        $data = $this->request->post();

        if (empty($data['plot_number'])) {
            $this->setFlash('error', $this->mlSupport->translate('Plot number is required.'));
            return $this->redirect('/admin/plots/create');
        }

        // Explicitly define fillable fields for security
        $fillableFields = [
            'project_id',
            'plot_number',
            'size',
            'size_unit',
            'price',
            'status',
            'facing',
            'dimension',
            'description'
        ];

        $plotData = [];
        foreach ($fillableFields as $field) {
            if (isset($data[$field])) {
                $plotData[$field] = h($data[$field]);
            }
        }

        $plotData['created_by'] = $this->session->get('user_id') ?? 1;
        $plotData['created_at'] = \date('Y-m-d H:i:s');
        $plotData['updated_at'] = \date('Y-m-d H:i:s');

        // Handle checkboxes
        $plotData['corner_plot'] = isset($data['corner_plot']) ? 1 : 0;
        $plotData['park_facing'] = isset($data['park_facing']) ? 1 : 0;
        $plotData['road_facing'] = isset($data['road_facing']) ? 1 : 0;

        $plotId = $this->plotModel->create($plotData);

        if ($plotId) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }
            $this->logActivity('Plot Creation', 'Created plot: ' . h($data['plot_number']) . ' (ID: ' . $plotId . ')');
            $this->setFlash('success', $this->mlSupport->translate('Plot created successfully.'));
            return $this->redirect('/admin/plots');
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to create plot.'));
            return $this->redirect('/admin/plots/create');
        }
    }

    /**
     * Show edit plot form
     */
    public function edit($id)
    {
        $id = \intval($id);
        $plot = $this->plotModel->find($id);
        if (!$plot) {
            $this->setFlash('error', $this->mlSupport->translate('Plot not found.'));
            return $this->redirect('/admin/plots');
        }

        return $this->render('admin/plots/edit', [
            'plot' => $plot,
            'page_title' => $this->mlSupport->translate('Edit Plot') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Update plot
     */
    public function update($id)
    {
        $id = \intval($id);

        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect("/admin/plots/edit/$id");
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->redirect("/admin/plots/edit/$id");
        }

        $data = $this->request->post();

        if (empty($data['plot_number'])) {
            $this->setFlash('error', $this->mlSupport->translate('Plot number is required.'));
            return $this->redirect("/admin/plots/edit/$id");
        }

        // Explicitly define fillable fields for security
        $fillableFields = [
            'project_id',
            'plot_number',
            'size',
            'size_unit',
            'price',
            'status',
            'facing',
            'dimension',
            'description'
        ];

        $plotData = [];
        foreach ($fillableFields as $field) {
            if (isset($data[$field])) {
                $plotData[$field] = h($data[$field]);
            }
        }

        $plotData['updated_at'] = \date('Y-m-d H:i:s');
        $plotData['corner_plot'] = isset($data['corner_plot']) ? 1 : 0;
        $plotData['park_facing'] = isset($data['park_facing']) ? 1 : 0;
        $plotData['road_facing'] = isset($data['road_facing']) ? 1 : 0;

        $updated = $this->plotModel->update($id, $plotData);

        if ($updated) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }
            $this->logActivity('Plot Update', 'Updated plot: ' . h($data['plot_number']) . ' (ID: ' . $id . ')');
            $this->setFlash('success', $this->mlSupport->translate('Plot updated successfully.'));
            return $this->redirect('/admin/plots');
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to update plot.'));
            return $this->redirect("/admin/plots/edit/$id");
        }
    }

    /**
     * Delete plot
     */
    public function destroy($id)
    {
        $id = \intval($id);

        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect('/admin/plots');
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
            return $this->redirect('/admin/plots');
        }

        $deleted = $this->plotModel->delete($id);
        if ($deleted) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }
            $this->logActivity('Plot Deletion', 'Deleted plot with ID: ' . $id);
            $this->setFlash('success', $this->mlSupport->translate('Plot deleted successfully.'));
            return $this->redirect('/admin/plots');
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to delete plot.'));
            return $this->redirect('/admin/plots');
        }
    }
}
