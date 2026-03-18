<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database;
use App\Core\Security;
use Exception;

class PlotManagementController extends BaseController
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * List plots for a specific site
     */
    public function index($siteId = null)
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';
            $siteId = $siteId ? intval($siteId) : intval($_GET['site_id'] ?? 0);
            
            $offset = ($page - 1) * 15;
            $where = ["1=1"];
            $params = [];

            if ($siteId > 0) {
                $where[] = "p.site_id = :site_id";
                $params['site_id'] = $siteId;
            }

            if (!empty($search)) {
                $where[] = "(p.plot_no LIKE :search OR p.plot_dimension LIKE :search OR p.plot_facing LIKE :search)";
                $params['search'] = '%' . $search . '%';
            }

            if (!empty($status)) {
                $where[] = "p.plot_status = :status";
                $params['status'] = $status;
            }

            $whereClause = implode(' AND ', $where);

            // Get plots with site information
            $sql = "SELECT p.*, s.site_name, s.location as site_location
                    FROM plot_master p
                    LEFT JOIN sites s ON p.site_id = s.id
                    WHERE $whereClause
                    ORDER BY p.plot_no
                    LIMIT :offset, :limit";

            $params['offset'] = $offset;
            $params['limit'] = 15;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $plots = $stmt->fetchAll();

            // Get total count
            $countSql = str_replace("SELECT p.*, s.site_name, s.location as site_location", "SELECT COUNT(*)", $sql);
            $countSql = preg_replace('/ORDER BY.*$/', '', $countSql);
            $countSql = preg_replace('/LIMIT.*$/', '', $countSql);
            
            $countParams = $params;
            unset($countParams['offset'], $countParams['limit']);
            
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($countParams);
            $total = $countStmt->fetch()['total'];

            // Get sites for dropdown
            $sites = $this->db->fetchAll("SELECT id, site_name, location FROM sites ORDER BY site_name");

            return $this->render('admin/plots/index', [
                'plots' => $plots,
                'sites' => $sites,
                'total' => $total,
                'current_page' => $page,
                'total_pages' => ceil($total / 15),
                'filters' => ['search' => $search, 'status' => $status, 'site_id' => $siteId]
            ]);

        } catch (Exception $e) {
            error_log("Plot listing error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load plots');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show add plot form
     */
    public function create($siteId = null)
    {
        try {
            $siteId = $siteId ? intval($siteId) : intval($_GET['site_id'] ?? 0);
            $sites = $this->db->fetchAll("SELECT id, site_name, location FROM sites ORDER BY site_name");
            
            return $this->render('admin/plots/create', [
                'sites' => $sites,
                'selected_site_id' => $siteId,
                'page_title' => 'Add New Plot - APS Dream Home'
            ]);

        } catch (Exception $e) {
            error_log("Plot create form error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load plot form');
            return $this->redirect('admin/plots');
        }
    }

    /**
     * Store new plot
     */
    public function store()
    {
        if ($this->method() !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect('admin/plots');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect('admin/plots');
        }

        try {
            $data = $this->post();
            
            $siteId = intval($data['site_id'] ?? 0);
            $plotNo = trim($data['plot_no'] ?? '');
            $area = floatval($data['area'] ?? 0);
            $availableArea = floatval($data['available_area'] ?? 0);
            $plotDimension = trim($data['plot_dimension'] ?? '');
            $plotFacing = trim($data['plot_facing'] ?? '');
            $plotPrice = floatval($data['plot_price'] ?? 0);
            $plotStatus = $data['plot_status'] ?? 'available';
            
            // Gata details
            $gataA = intval($data['gata_a'] ?? 0);
            $gataB = intval($data['gata_b'] ?? 0);
            $gataC = intval($data['gata_c'] ?? 0);
            $gataD = intval($data['gata_d'] ?? 0);
            $areaGataA = floatval($data['area_gata_a'] ?? 0);
            $areaGataB = floatval($data['area_gata_b'] ?? 0);
            $areaGataC = floatval($data['area_gata_c'] ?? 0);
            $areaGataD = floatval($data['area_gata_d'] ?? 0);

            // Validation
            if ($siteId <= 0 || empty($plotNo) || $area <= 0) {
                $this->setFlash('error', 'Please fill in all required fields');
                return $this->redirect('admin/plots/create');
            }

            // Check if plot number already exists for this site
            $existing = $this->db->fetchOne(
                "SELECT plot_id FROM plot_master WHERE site_id = ? AND plot_no = ? LIMIT 1", 
                [$siteId, $plotNo]
            );
            
            if ($existing) {
                $this->setFlash('error', 'Plot number already exists for this site');
                return $this->redirect('admin/plots/create');
            }

            // Check if site exists
            $site = $this->db->fetchOne("SELECT id FROM sites WHERE id = ? LIMIT 1", [$siteId]);
            if (!$site) {
                $this->setFlash('error', 'Invalid site selected');
                return $this->redirect('admin/plots/create');
            }

            $sql = "INSERT INTO plot_master (site_id, plot_no, area, available_area, plot_dimension, 
                           plot_facing, plot_price, plot_status, gata_a, gata_b, gata_c, gata_d,
                           area_gata_a, area_gata_b, area_gata_c, area_gata_d)
                    VALUES (:site_id, :plot_no, :area, :available_area, :plot_dimension,
                           :plot_facing, :plot_price, :plot_status, :gata_a, :gata_b, :gata_c, :gata_d,
                           :area_gata_a, :area_gata_b, :area_gata_c, :area_gata_d)";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'site_id' => $siteId,
                'plot_no' => $plotNo,
                'area' => $area,
                'available_area' => $availableArea,
                'plot_dimension' => $plotDimension,
                'plot_facing' => $plotFacing,
                'plot_price' => $plotPrice,
                'plot_status' => $plotStatus,
                'gata_a' => $gataA,
                'gata_b' => $gataB,
                'gata_c' => $gataC,
                'gata_d' => $gataD,
                'area_gata_a' => $areaGataA,
                'area_gata_b' => $areaGataB,
                'area_gata_c' => $areaGataC,
                'area_gata_d' => $areaGataD
            ]);

            if ($success) {
                $this->setFlash('success', 'Plot added successfully');
                return $this->redirect('admin/plots?site_id=' . $siteId);
            } else {
                $this->setFlash('error', 'Failed to add plot');
                return $this->redirect('admin/plots/create');
            }

        } catch (Exception $e) {
            error_log("Plot creation error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to add plot');
            return $this->redirect('admin/plots/create');
        }
    }

    /**
     * Show plot details
     */
    public function show($id)
    {
        try {
            $plotId = intval($id);
            if ($plotId <= 0) {
                $this->setFlash('error', 'Invalid plot ID');
                return $this->redirect('admin/plots');
            }

            // Get plot details with site information
            $sql = "SELECT p.*, s.site_name, s.location as site_location, s.city as site_city
                    FROM plot_master p
                    LEFT JOIN sites s ON p.site_id = s.id
                    WHERE p.plot_id = :plot_id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['plot_id' => $plotId]);
            $plot = $stmt->fetch();

            if (!$plot) {
                $this->setFlash('error', 'Plot not found');
                return $this->redirect('admin/plots');
            }

            // Get booking history for this plot
            $bookingsSql = "SELECT b.*, u.name as customer_name, u.email as customer_email
                           FROM bookings b
                           LEFT JOIN users u ON b.customer_id = u.id
                           WHERE b.plot_id = :plot_id
                           ORDER BY b.created_at DESC";
            
            $bookingsStmt = $this->db->prepare($bookingsSql);
            $bookingsStmt->execute(['plot_id' => $plotId]);
            $bookings = $bookingsStmt->fetchAll();

            return $this->render('admin/plots/show', [
                'plot' => $plot,
                'bookings' => $bookings,
                'page_title' => 'Plot Details - APS Dream Home'
            ]);

        } catch (Exception $e) {
            error_log("Plot show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load plot details');
            return $this->redirect('admin/plots');
        }
    }

    /**
     * Show edit plot form
     */
    public function edit($id)
    {
        try {
            $plotId = intval($id);
            if ($plotId <= 0) {
                $this->setFlash('error', 'Invalid plot ID');
                return $this->redirect('admin/plots');
            }

            $plot = $this->db->fetchOne("SELECT * FROM plot_master WHERE plot_id = ? LIMIT 1", [$plotId]);
            
            if (!$plot) {
                $this->setFlash('error', 'Plot not found');
                return $this->redirect('admin/plots');
            }

            $sites = $this->db->fetchAll("SELECT id, site_name, location FROM sites ORDER BY site_name");

            return $this->render('admin/plots/edit', [
                'plot' => $plot,
                'sites' => $sites,
                'page_title' => 'Edit Plot - APS Dream Home'
            ]);

        } catch (Exception $e) {
            error_log("Plot edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load plot for editing');
            return $this->redirect('admin/plots');
        }
    }

    /**
     * Update plot
     */
    public function update($id)
    {
        if ($this->method() !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect('admin/plots');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect('admin/plots');
        }

        try {
            $plotId = intval($id);
            if ($plotId <= 0) {
                $this->setFlash('error', 'Invalid plot ID');
                return $this->redirect('admin/plots');
            }

            $plot = $this->db->fetchOne("SELECT plot_id FROM plot_master WHERE plot_id = ? LIMIT 1", [$plotId]);
            if (!$plot) {
                $this->setFlash('error', 'Plot not found');
                return $this->redirect('admin/plots');
            }

            $data = $this->post();
            
            $sql = "UPDATE plot_master 
                    SET site_id = :site_id, plot_no = :plot_no, area = :area, available_area = :available_area,
                        plot_dimension = :plot_dimension, plot_facing = :plot_facing, plot_price = :plot_price,
                        plot_status = :plot_status, gata_a = :gata_a, gata_b = :gata_b, gata_c = :gata_c, gata_d = :gata_d,
                        area_gata_a = :area_gata_a, area_gata_b = :area_gata_b, area_gata_c = :area_gata_c, area_gata_d = :area_gata_d
                    WHERE plot_id = :plot_id";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'site_id' => intval($data['site_id']),
                'plot_no' => trim($data['plot_no']),
                'area' => floatval($data['area']),
                'available_area' => floatval($data['available_area']),
                'plot_dimension' => trim($data['plot_dimension']),
                'plot_facing' => trim($data['plot_facing']),
                'plot_price' => floatval($data['plot_price']),
                'plot_status' => $data['plot_status'],
                'gata_a' => intval($data['gata_a']),
                'gata_b' => intval($data['gata_b']),
                'gata_c' => intval($data['gata_c']),
                'gata_d' => intval($data['gata_d']),
                'area_gata_a' => floatval($data['area_gata_a']),
                'area_gata_b' => floatval($data['area_gata_b']),
                'area_gata_c' => floatval($data['area_gata_c']),
                'area_gata_d' => floatval($data['area_gata_d']),
                'plot_id' => $plotId
            ]);

            if ($success) {
                $this->setFlash('success', 'Plot updated successfully');
                return $this->redirect('admin/plots');
            } else {
                $this->setFlash('error', 'Failed to update plot');
                return $this->redirect("admin/plots/{$plotId}/edit");
            }

        } catch (Exception $e) {
            error_log("Plot update error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update plot');
            return $this->redirect("admin/plots/{$id}/edit");
        }
    }

    /**
     * Delete plot
     */
    public function destroy($id)
    {
        if ($this->method() !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect('admin/plots');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect('admin/plots');
        }

        try {
            $plotId = intval($id);
            if ($plotId <= 0) {
                $this->setFlash('error', 'Invalid plot ID');
                return $this->redirect('admin/plots');
            }

            $plot = $this->db->fetchOne("SELECT * FROM plot_master WHERE plot_id = ? LIMIT 1", [$plotId]);
            if (!$plot) {
                $this->setFlash('error', 'Plot not found');
                return $this->redirect('admin/plots');
            }

            // Check if plot has bookings
            $bookingCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM bookings WHERE plot_id = ?", [$plotId])['count'];

            if ($bookingCount > 0) {
                $this->setFlash('error', 'Cannot delete plot with existing bookings');
                return $this->redirect('admin/plots');
            }

            $success = $this->db->prepare("DELETE FROM plot_master WHERE plot_id = ?")->execute([$plotId]);

            if ($success) {
                $this->setFlash('success', 'Plot deleted successfully');
            } else {
                $this->setFlash('error', 'Failed to delete plot');
            }

            return $this->redirect('admin/plots');

        } catch (Exception $e) {
            error_log("Plot deletion error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to delete plot');
            return $this->redirect('admin/plots');
        }
    }

    /**
     * Check plot availability for booking
     */
    public function checkAvailability()
    {
        try {
            $siteId = intval($_GET['site_id'] ?? 0);
            $plotId = intval($_GET['plot_id'] ?? 0);

            if ($siteId > 0) {
                // Get available plots for site
                $sql = "SELECT plot_id, plot_no, area, plot_price, plot_dimension, plot_facing
                        FROM plot_master 
                        WHERE site_id = :site_id AND plot_status = 'available'
                        ORDER BY plot_no";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['site_id' => $siteId]);
                $plots = $stmt->fetchAll();

                return json_encode([
                    'success' => true,
                    'plots' => $plots
                ]);
            } elseif ($plotId > 0) {
                // Check specific plot availability
                $plot = $this->db->fetchOne(
                    "SELECT plot_status, plot_price FROM plot_master WHERE plot_id = ? LIMIT 1", 
                    [$plotId]
                );

                if ($plot) {
                    return json_encode([
                        'success' => true,
                        'available' => $plot['plot_status'] === 'available',
                        'price' => $plot['plot_price']
                    ]);
                } else {
                    return json_encode([
                        'success' => false,
                        'message' => 'Plot not found'
                    ]);
                }
            }

            return json_encode([
                'success' => false,
                'message' => 'Invalid parameters'
            ]);

        } catch (Exception $e) {
            error_log("Plot availability check error: " . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => 'Failed to check availability'
            ]);
        }
    }

    /**
     * Update plot status (for booking system)
     */
    public function updateStatus($id)
    {
        if ($this->method() !== 'POST') {
            return json_encode(['success' => false, 'message' => 'Invalid request method']);
        }

        try {
            $plotId = intval($id);
            $newStatus = $_POST['status'] ?? '';

            if ($plotId <= 0 || empty($newStatus)) {
                return json_encode(['success' => false, 'message' => 'Invalid parameters']);
            }

            $validStatuses = ['available', 'sold', 'reserved', 'under_process'];
            if (!in_array($newStatus, $validStatuses)) {
                return json_encode(['success' => false, 'message' => 'Invalid status']);
            }

            $success = $this->db->prepare("UPDATE plot_master SET plot_status = ? WHERE plot_id = ?")
                              ->execute([$newStatus, $plotId]);

            if ($success) {
                return json_encode([
                    'success' => true,
                    'message' => 'Plot status updated successfully'
                ]);
            } else {
                return json_encode([
                    'success' => false,
                    'message' => 'Failed to update plot status'
                ]);
            }

        } catch (Exception $e) {
            error_log("Plot status update error: " . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => 'Failed to update plot status'
            ]);
        }
    }
}
