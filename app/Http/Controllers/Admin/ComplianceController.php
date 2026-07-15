<?php

namespace App\Http\Controllers\Admin;

use App\Services\Security\ComplianceService;

class ComplianceController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ComplianceService();
    }

    public function index()
    {
        $this->requireAdmin();

        $data = $this->service->calculateComplianceScore();

        return $this->render('admin/compliance/scorecard', [
            'page_title'       => 'Compliance Scorecard',
            'overall'          => $data['overall'],
            'areas'            => $data['areas'],
            'last_checked'     => $data['last_checked'],
            'recommendations'  => array_slice($data['recommendations'], 0, 5),
            'area_labels'      => $this->service->getAreaLabels(),
            'area_icons'       => $this->service->getAreaIcons(),
            'weights'          => $this->service->getWeights(),
            'trend'            => $this->service->getComplianceTrend(),
        ]);
    }

    public function area(string $area)
    {
        $this->requireAdmin();

        $labels = $this->service->getAreaLabels();
        if (!isset($labels[$area])) {
            $this->setFlash('error', 'Unknown compliance area');
            return $this->redirect('/admin/compliance-scorecard');
        }

        $result = $this->service->getComplianceArea($area);
        $allAreas = [];
        foreach (array_keys($this->service->getWeights()) as $key) {
            $allAreas[$key] = $this->service->getComplianceArea($key);
        }
        $overall = 0;
        foreach ($this->service->getWeights() as $k => $w) {
            $overall += ($allAreas[$k]['score'] ?? 0) * $w;
        }

        return $this->render('admin/compliance/area', [
            'page_title'   => $labels[$area] . ' — Compliance',
            'area_key'     => $area,
            'area_label'   => $labels[$area],
            'area_icon'    => $this->service->getAreaIcons()[$area] ?? 'fas fa-check-circle',
            'area_weight'  => $this->service->getWeights()[$area] ?? 0,
            'result'       => $result,
            'overall'      => (int)round($overall),
            'area_labels'  => $labels,
            'all_areas'    => $allAreas,
        ]);
    }

    public function recommendations()
    {
        $this->requireAdmin();

        $all = $this->service->getPrioritizedRecommendations();
        $labels = $this->service->getAreaLabels();

        $grouped = [];
        foreach ($all as $rec) {
            $grouped[$rec['area_key']][] = $rec;
        }

        return $this->render('admin/compliance/recommendations', [
            'page_title'   => 'Compliance Recommendations',
            'recommendations' => $all,
            'grouped'      => $grouped,
            'area_labels'  => $labels,
            'area_icons'   => $this->service->getAreaIcons(),
        ]);
    }
}
