<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\SustainableTechService;

class SustainableTechController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SustainableTechService();
    }

    public function index()
    {
        $this->requireAdmin();
        $certs = $this->service->getCertifications();
        $features = $this->service->getFeatures();
        $audits = $this->service->getAudits([], 1, 5)['data'];
        $carbon = $this->service->getCarbonLedger([], 1, 5);

        $totalCo2Features = array_sum(array_map(fn($f) => (float)($f['co2_saved_kg_yr'] ?? 0), $features));
        $totalCredits = $carbon['summary']['total_credits'] ?? 0;
        $totalValue = $carbon['summary']['total_value'] ?? 0;

        $this->render('admin/sustainable/index', [
            'page_title' => 'Sustainable Tech',
            'cert_count' => count($certs),
            'feature_count' => count($features),
            'audit_count' => $this->service->getAudits([], 1, 1)['total'],
            'credit_count' => $carbon['total'],
            'total_co2_features' => $totalCo2Features,
            'total_credits' => $totalCredits,
            'total_value' => $totalValue,
            'audits' => $audits,
            'carbon' => $carbon['data'],
        ]);
    }

    // ==================== CERTIFICATIONS ====================

    public function certifications()
    {
        $this->requireAdmin();
        $certs = $this->service->getCertifications();
        $this->render('admin/sustainable/certifications', [
            'page_title' => 'Green Certifications',
            'certs' => $certs,
        ]);
    }

    public function certificationForm($id = null)
    {
        $this->requireAdmin();
        $cert = $id ? $this->service->getCertification((int)$id) : null;
        $this->render('admin/sustainable/certification_form', [
            'page_title' => $cert ? 'Edit Certification' : 'Add Certification',
            'cert' => $cert,
        ]);
    }

    public function certificationSave()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/sustainable/certifications');

        $id = $this->service->saveCertification([
            'id' => (int)($_POST['id'] ?? 0) ?: null,
            'name' => $_POST['name'] ?? '',
            'code' => $_POST['code'] ?? null,
            'authority' => $_POST['authority'] ?? null,
            'level' => $_POST['level'] ?? null,
            'description' => $_POST['description'] ?? null,
            'icon' => $_POST['icon'] ?? null,
            'color' => $_POST['color'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $_SESSION['success'] = 'Certification saved.';
        $this->redirect('/admin/sustainable/certifications');
    }

    public function certificationDelete($id)
    {
        $this->requireAdmin();
        $this->service->deleteCertification((int)$id);
        $_SESSION['success'] = 'Certification deleted.';
        $this->redirect('/admin/sustainable/certifications');
    }

    // ==================== GREEN FEATURES ====================

    public function features()
    {
        $this->requireAdmin();
        $category = $_GET['category'] ?? '';
        $features = $this->service->getFeatures($category ? ['category' => $category] : []);
        $this->render('admin/sustainable/features', [
            'page_title' => 'Green Features',
            'features' => $features,
            'category' => $category,
        ]);
    }

    public function featureForm($id = null)
    {
        $this->requireAdmin();
        $feature = $id ? $this->service->getFeature((int)$id) : null;
        $this->render('admin/sustainable/feature_form', [
            'page_title' => $feature ? 'Edit Feature' : 'Add Feature',
            'feature' => $feature,
        ]);
    }

    public function featureSave()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/sustainable/features');

        $this->service->saveFeature([
            'id' => (int)($_POST['id'] ?? 0) ?: null,
            'name' => $_POST['name'] ?? '',
            'category' => $_POST['category'] ?? 'energy',
            'description' => $_POST['description'] ?? null,
            'co2_saved_kg_yr' => !empty($_POST['co2_saved_kg_yr']) ? (float)$_POST['co2_saved_kg_yr'] : 0,
            'cost_estimate' => !empty($_POST['cost_estimate']) ? (float)$_POST['cost_estimate'] : null,
            'icon' => $_POST['icon'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $_SESSION['success'] = 'Feature saved.';
        $this->redirect('/admin/sustainable/features');
    }

    public function featureDelete($id)
    {
        $this->requireAdmin();
        $this->service->deleteFeature((int)$id);
        $_SESSION['success'] = 'Feature deleted.';
        $this->redirect('/admin/sustainable/features');
    }

    // ==================== ENERGY AUDITS ====================

    public function audits()
    {
        $this->requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = $this->service->getAudits([], $page, 20);
        $this->render('admin/sustainable/audits', [
            'page_title' => 'Energy Audits',
            'audits' => $result['data'],
            'pagination' => [
                'page' => $result['page'],
                'pages' => ceil($result['total'] / $result['limit']),
                'total' => $result['total'],
            ],
        ]);
    }

    public function auditForm($id = null)
    {
        $this->requireAdmin();
        $audit = $id ? $this->service->getAudit((int)$id) : null;
        $this->render('admin/sustainable/audit_form', [
            'page_title' => $audit ? 'Edit Audit' : 'New Energy Audit',
            'audit' => $audit,
        ]);
    }

    public function auditSave()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/sustainable/audits');

        $this->service->saveAudit([
            'id' => (int)($_POST['id'] ?? 0) ?: null,
            'project_id' => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
            'project_name' => $_POST['project_name'] ?? null,
            'audit_date' => !empty($_POST['audit_date']) ? $_POST['audit_date'] : null,
            'auditor_name' => $_POST['auditor_name'] ?? null,
            'energy_score' => !empty($_POST['energy_score']) ? (float)$_POST['energy_score'] : null,
            'annual_kwh' => !empty($_POST['annual_kwh']) ? (float)$_POST['annual_kwh'] : null,
            'solar_capacity_kwp' => !empty($_POST['solar_capacity_kwp']) ? (float)$_POST['solar_capacity_kwp'] : null,
            'water_savings_kl' => !empty($_POST['water_savings_kl']) ? (float)$_POST['water_savings_kl'] : null,
            'renewable_pct' => !empty($_POST['renewable_pct']) ? (float)$_POST['renewable_pct'] : 0,
            'estimated_co2_tonnes_yr' => !empty($_POST['estimated_co2_tonnes_yr']) ? (float)$_POST['estimated_co2_tonnes_yr'] : null,
            'notes' => $_POST['notes'] ?? null,
            'recommendations' => !empty($_POST['recommendations']) ? array_filter(array_map('trim', explode("\n", $_POST['recommendations']))) : null,
            'status' => $_POST['status'] ?? 'draft',
        ]);
        $_SESSION['success'] = 'Audit saved.';
        $this->redirect('/admin/sustainable/audits');
    }

    public function auditDelete($id)
    {
        $this->requireAdmin();
        $this->service->deleteAudit((int)$id);
        $_SESSION['success'] = 'Audit deleted.';
        $this->redirect('/admin/sustainable/audits');
    }

    // ==================== CARBON CREDITS ====================

    public function carbon()
    {
        $this->requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = $this->service->getCarbonLedger([], $page, 20);
        $this->render('admin/sustainable/carbon', [
            'page_title' => 'Carbon Credit Ledger',
            'entries' => $result['data'],
            'summary' => $result['summary'],
            'pagination' => [
                'page' => $result['page'],
                'pages' => ceil($result['total'] / $result['limit']),
                'total' => $result['total'],
            ],
        ]);
    }

    public function carbonSave()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/sustainable/carbon');

        $credits = !empty($_POST['credits_earned']) ? (float)$_POST['credits_earned'] : 0;
        $rate = !empty($_POST['value_per_credit']) ? (float)$_POST['value_per_credit'] : 0;

        $this->service->saveCarbonEntry([
            'id' => (int)($_POST['id'] ?? 0) ?: null,
            'reference_type' => $_POST['reference_type'] ?? 'project',
            'reference_id' => !empty($_POST['reference_id']) ? (int)$_POST['reference_id'] : null,
            'credit_type' => $_POST['credit_type'] ?? null,
            'credits_earned' => $credits,
            'credit_date' => !empty($_POST['credit_date']) ? $_POST['credit_date'] : date('Y-m-d'),
            'value_per_credit' => $rate,
            'total_value' => $credits * $rate,
            'verified' => isset($_POST['verified']) ? 1 : 0,
            'notes' => $_POST['notes'] ?? null,
        ]);
        $_SESSION['success'] = 'Carbon entry saved.';
        $this->redirect('/admin/sustainable/carbon');
    }

    public function carbonDelete($id)
    {
        $this->requireAdmin();
        $this->service->deleteCarbonEntry((int)$id);
        $_SESSION['success'] = 'Carbon entry deleted.';
        $this->redirect('/admin/sustainable/carbon');
    }
}
