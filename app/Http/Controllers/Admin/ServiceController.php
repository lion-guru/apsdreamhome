<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class ServiceController extends AdminController
{
    public function index()
    {
        $page = (int)($_GET['page'] ?? 1);
        $serviceType = $_GET['service'] ?? '';
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE 1=1";
        $params = [];

        if ($serviceType) {
            $where .= " AND si.service_type = ?";
            $params[] = $serviceType;
        }

        if ($status) {
            $where .= " AND si.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $where .= " AND (l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ?)";
            $s = '%' . $search . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM service_interests si LEFT JOIN leads l ON si.lead_id = l.id $where";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        $totalPages = ceil($total / $perPage);

        // Get service interests
        $sql = "SELECT si.*, l.name, l.phone, l.email, l.status as lead_status,
                p.site_name as property_name
                FROM service_interests si
                LEFT JOIN leads l ON si.lead_id = l.id
                LEFT JOIN sites p ON si.property_id = p.id
                $where
                ORDER BY si.created_at DESC
                LIMIT $perPage OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $services = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get counts by type
        $counts = $this->getServiceCounts();

        // Service type labels
        $serviceLabels = [
            'home_loan' => 'Home Loan',
            'legal' => 'Legal Help',
            'registry' => 'Registry',
            'mutation' => 'Mutation',
            'interior' => 'Interior Design',
            'home_insurance' => 'Home Insurance',
            'property_tax' => 'Property Tax',
            'rental_agreement' => 'Rental Agreement',
            'Tenant_verification' => 'Tenant Verification'
        ];

        $data = [
            'services' => $services,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'counts' => $counts,
            'serviceType' => $serviceType,
            'status' => $status,
            'search' => $search,
            'serviceLabels' => $serviceLabels
        ];

        $this->render('admin/services/index', $data);
    }

    private function getServiceCounts()
    {
        $counts = [];
        $types = ['home_loan', 'legal', 'registry', 'mutation', 'interior', 'home_insurance', 'property_tax', 'rental_agreement', 'Tenant_verification'];
        
        foreach ($types as $type) {
            try {
                $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM service_interests WHERE service_type = ?");
                $stmt->execute([$type]);
                $counts[$type] = $stmt->fetch()['cnt'];
            } catch (\Exception $e) {
                $counts[$type] = 0;
            }
        }
        
        return $counts;
    }

    public function show($id = null)
    {
        if (!$id) {
            $this->redirect('/admin/services');
            return;
        }

        $stmt = $this->db->prepare("SELECT si.*, l.name, l.phone, l.email, l.status as lead_status
                                    FROM service_interests si
                                    LEFT JOIN leads l ON si.lead_id = l.id
                                    WHERE si.id = ?");
        $stmt->execute([$id]);
        $service = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$service) {
            $this->setFlash('error', 'Service interest not found');
            $this->redirect('/admin/services');
            return;
        }

        // Get lead details if exists
        if ($service['lead_id']) {
            try {
                $leadStmt = $this->db->prepare("SELECT * FROM leads WHERE id = ?");
                $leadStmt->execute([$service['lead_id']]);
                $service['lead'] = $leadStmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {}
        }

        // Get property details if exists
        if ($service['property_id']) {
            try {
                $propStmt = $this->db->prepare("SELECT * FROM sites WHERE id = ?");
                $propStmt->execute([$service['property_id']]);
                $service['property'] = $propStmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {}
        }

        $data = ['service' => $service];
        $this->render('admin/services/view', $data);
    }

    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $status = $_POST['status'] ?? 'new';
            $notes = $_POST['notes'] ?? '';

            try {
                $stmt = $this->db->prepare("UPDATE service_interests SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$status, $notes, $id]);
                $this->setFlash('success', 'Status updated successfully');
            } catch (\Exception $e) {
                $this->setFlash('error', 'Failed to update status');
            }
        }
        $this->redirect('/admin/services');
    }
}
