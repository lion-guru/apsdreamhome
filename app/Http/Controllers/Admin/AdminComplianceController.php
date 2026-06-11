<?php

namespace App\Http\Controllers\Admin;

class AdminComplianceController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();

        $reraFilings = [];
        $totalRera = $pendingRera = $acceptedRera = 0;
        $pendingKyc = $totalKyc = $verifiedKyc = 0;
        $gstReturns = [];
        $totalGstFiled = $pendingGst = 0;
        $tdsSummary = [];
        $totalTdsAmount = 0;
        $pendingTds = 0;

        try {
            $reraFilings = $this->db->query("SELECT r.*, c.name as colony_name FROM rera_compliance_log r LEFT JOIN colonies c ON r.project_colony_id = c.id ORDER BY r.year DESC, r.quarter DESC")->fetchAll(\PDO::FETCH_ASSOC);
            $totalRera = count($reraFilings);
            $pendingRera = (int)($this->db->query("SELECT COUNT(*) FROM rera_compliance_log WHERE status = 'pending'")->fetchColumn());
            $acceptedRera = (int)($this->db->query("SELECT COUNT(*) FROM rera_compliance_log WHERE status = 'accepted'")->fetchColumn());
            $pendingKyc = (int)($this->db->query("SELECT COUNT(*) FROM kyc_requests WHERE status = 'pending'")->fetchColumn());
            $totalKyc = (int)($this->db->query("SELECT COUNT(*) FROM kyc_requests")->fetchColumn());
            $verifiedKyc = (int)($this->db->query("SELECT COUNT(*) FROM kyc_requests WHERE status = 'approved'")->fetchColumn());
            $gstReturns = $this->db->query("SELECT * FROM gst_returns ORDER BY return_period DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
            $totalGstFiled = (int)($this->db->query("SELECT COUNT(*) FROM gst_returns WHERE filing_status = 'filed'")->fetchColumn());
            $pendingGst = (int)($this->db->query("SELECT COUNT(*) FROM gst_returns WHERE filing_status IN ('draft','pending')")->fetchColumn());
            $tdsSummary = $this->db->query("SELECT status, COUNT(*) as cnt, COALESCE(SUM(total_tds),0) as total FROM tds_register GROUP BY status")->fetchAll(\PDO::FETCH_ASSOC);
            $totalTdsAmount = (float)($this->db->query("SELECT COALESCE(SUM(total_tds),0) FROM tds_register")->fetchColumn());
            $pendingTds = (int)($this->db->query("SELECT COUNT(*) FROM tds_register WHERE status = 'pending'")->fetchColumn());
        } catch (\Exception $e) {
            error_log('AdminComplianceController::index error: ' . $e->getMessage());
        }

        return $this->render('admin/compliance/index', [
            'page_title' => 'Compliance - APS Dream Home',
            'page_heading' => 'Compliance Management',
            'reraFilings' => $reraFilings,
            'totalRera' => $totalRera,
            'pendingRera' => $pendingRera,
            'acceptedRera' => $acceptedRera,
            'pendingKyc' => $pendingKyc,
            'totalKyc' => $totalKyc,
            'verifiedKyc' => $verifiedKyc,
            'gstReturns' => $gstReturns,
            'totalGstFiled' => $totalGstFiled,
            'pendingGst' => $pendingGst,
            'tdsSummary' => $tdsSummary,
            'totalTdsAmount' => $totalTdsAmount,
            'pendingTds' => $pendingTds,
        ]);
    }
}
