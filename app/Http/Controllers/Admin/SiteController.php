<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database;
use App\Core\Security;
use Exception;

class SiteController extends BaseController
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * List all sites
     */
    public function index()
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';
            $type = $_GET['type'] ?? '';
            
            $offset = ($page - 1) * 10;
            $where = ["1=1"];
            $params = [];

            if (!empty($search)) {
                $where[] = "(s.site_name LIKE :search OR s.location LIKE :search OR s.city LIKE :search)";
                $params['search'] = '%' . $search . '%';
            }

            if (!empty($status)) {
                $where[] = "s.status = :status";
                $params['status'] = $status;
            }

            if (!empty($type)) {
                $where[] = "s.site_type = :type";
                $params['type'] = $type;
            }

            $whereClause = implode(' AND ', $where);

            // Get sites with counts
            $sql = "SELECT s.*, 
                           COUNT(p.plot_id) as total_plots,
                           SUM(CASE WHEN p.plot_status = 'available' THEN 1 ELSE 0 END) as available_plots,
                           SUM(CASE WHEN p.plot_status = 'sold' THEN 1 ELSE 0 END) as sold_plots,
                           COUNT(pr.id) as total_properties
                    FROM sites s
                    LEFT JOIN plot_master p ON s.id = p.site_id
                    LEFT JOIN properties pr ON s.id = pr.site_id
                    WHERE $whereClause
                    GROUP BY s.id
                    ORDER BY s.created_at DESC
                    LIMIT :offset, :limit";

            $params['offset'] = $offset;
            $params['limit'] = 10;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $sites = $stmt->fetchAll();

            // Get total count
            $countSql = str_replace("SELECT s.*, COUNT(p.plot_id) as total_plots", "SELECT COUNT(DISTINCT s.id)", $sql);
            $countSql = preg_replace('/GROUP BY s\.id.*$/', '', $countSql);
            $countSql = preg_replace('/LIMIT.*$/', '', $countSql);
            
            $countParams = $params;
            unset($countParams['offset'], $countParams['limit']);
            
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($countParams);
            $total = $countStmt->fetch()['total'];

            return $this->render('admin/sites/index', [
                'sites' => $sites,
                'total' => $total,
                'current_page' => $page,
                'total_pages' => ceil($total / 10),
                'filters' => ['search' => $search, 'status' => $status, 'type' => $type]
            ]);

        } catch (Exception $e) {
            error_log("Site listing error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load sites');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show create site form
     */
    public function create()
    {
        return $this->render('admin/sites/create', [
            'page_title' => 'Add New Site - APS Dream Home'
        ]);
    }

    /**
     * Store new site
     */
    public function store()
    {
        if ($this->method() !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect('admin/sites');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect('admin/sites');
        }

        try {
            $data = $this->post();
            
            $siteName = trim($data['site_name'] ?? '');
            $location = trim($data['location'] ?? '');
            $city = trim($data['city'] ?? '');
            $state = trim($data['state'] ?? '');
            $pincode = trim($data['pincode'] ?? '');
            $totalArea = floatval($data['total_area'] ?? 0);
            $siteType = $data['site_type'] ?? 'residential';
            $status = $data['status'] ?? 'planning';
            $description = trim($data['description'] ?? '');
            $amenities = trim($data['amenities'] ?? '');
            $latitude = floatval($data['latitude'] ?? 0);
            $longitude = floatval($data['longitude'] ?? 0);

            // Validation
            if (empty($siteName) || empty($location) || $totalArea <= 0) {
                $this->setFlash('error', 'Please fill in all required fields');
                return $this->redirect('admin/sites/create');
            }

            $sql = "INSERT INTO sites (site_name, location, city, state, pincode, total_area, 
                           site_type, status, description, amenities, latitude, longitude, created_at, updated_at)
                    VALUES (:site_name, :location, :city, :state, :pincode, :total_area,
                           :site_type, :status, :description, :amenities, :latitude, :longitude, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'site_name' => $siteName,
                'location' => $location,
                'city' => $city,
                'state' => $state,
                'pincode' => $pincode,
                'total_area' => $totalArea,
                'site_type' => $siteType,
                'status' => $status,
                'description' => $description,
                'amenities' => $amenities,
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);

            if ($success) {
                $this->setFlash('success', 'Site created successfully');
                return $this->redirect('admin/sites');
            } else {
                $this->setFlash('error', 'Failed to create site');
                return $this->redirect('admin/sites/create');
            }

        } catch (Exception $e) {
            error_log("Site creation error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to create site');
            return $this->redirect('admin/sites/create');
        }
    }

    /**
     * Show site details
     */
    public function show($id)
    {
        try {
            $siteId = intval($id);
            if ($siteId <= 0) {
                $this->setFlash('error', 'Invalid site ID');
                return $this->redirect('admin/sites');
            }

            // Get site details with plot and property counts
            $sql = "SELECT s.*, 
                           COUNT(p.plot_id) as total_plots,
                           SUM(CASE WHEN p.plot_status = 'available' THEN 1 ELSE 0 END) as available_plots,
                           SUM(CASE WHEN p.plot_status = 'sold' THEN 1 ELSE 0 END) as sold_plots,
                           COUNT(pr.id) as total_properties
                    FROM sites s
                    LEFT JOIN plot_master p ON s.id = p.site_id
                    LEFT JOIN properties pr ON s.id = pr.site_id
                    WHERE s.id = :id
                    GROUP BY s.id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $siteId]);
            $site = $stmt->fetch();

            if (!$site) {
                $this->setFlash('error', 'Site not found');
                return $this->redirect('admin/sites');
            }

            // Get plots for this site
            $plotsSql = "SELECT * FROM plot_master WHERE site_id = :site_id ORDER BY plot_no";
            $plotsStmt = $this->db->prepare($plotsSql);
            $plotsStmt->execute(['site_id' => $siteId]);
            $plots = $plotsStmt->fetchAll();

            // Get properties for this site
            $propertiesSql = "SELECT * FROM properties WHERE site_id = :site_id ORDER BY created_at DESC";
            $propertiesStmt = $this->db->prepare($propertiesSql);
            $propertiesStmt->execute(['site_id' => $siteId]);
            $properties = $propertiesStmt->fetchAll();

            return $this->render('admin/sites/show', [
                'site' => $site,
                'plots' => $plots,
                'properties' => $properties,
                'page_title' => 'Site Details - APS Dream Home'
            ]);

        } catch (Exception $e) {
            error_log("Site show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load site details');
            return $this->redirect('admin/sites');
        }
    }

    /**
     * Show edit site form
     */
    public function edit($id)
    {
        try {
            $siteId = intval($id);
            if ($siteId <= 0) {
                $this->setFlash('error', 'Invalid site ID');
                return $this->redirect('admin/sites');
            }

            $site = $this->db->fetchOne("SELECT * FROM sites WHERE id = ? LIMIT 1", [$siteId]);
            
            if (!$site) {
                $this->setFlash('error', 'Site not found');
                return $this->redirect('admin/sites');
            }

            return $this->render('admin/sites/edit', [
                'site' => $site,
                'page_title' => 'Edit Site - APS Dream Home'
            ]);

        } catch (Exception $e) {
            error_log("Site edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load site for editing');
            return $this->redirect('admin/sites');
        }
    }

    /**
     * Update site
     */
    public function update($id)
    {
        if ($this->method() !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect('admin/sites');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect('admin/sites');
        }

        try {
            $siteId = intval($id);
            if ($siteId <= 0) {
                $this->setFlash('error', 'Invalid site ID');
                return $this->redirect('admin/sites');
            }

            $site = $this->db->fetchOne("SELECT id FROM sites WHERE id = ? LIMIT 1", [$siteId]);
            if (!$site) {
                $this->setFlash('error', 'Site not found');
                return $this->redirect('admin/sites');
            }

            $data = $this->post();
            
            $sql = "UPDATE sites 
                    SET site_name = :site_name, location = :location, city = :city, state = :state,
                        pincode = :pincode, total_area = :total_area, site_type = :site_type,
                        status = :status, description = :description, amenities = :amenities,
                        latitude = :latitude, longitude = :longitude, updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'site_name' => trim($data['site_name']),
                'location' => trim($data['location']),
                'city' => trim($data['city']),
                'state' => trim($data['state']),
                'pincode' => trim($data['pincode']),
                'total_area' => floatval($data['total_area']),
                'site_type' => $data['site_type'],
                'status' => $data['status'],
                'description' => trim($data['description']),
                'amenities' => trim($data['amenities']),
                'latitude' => floatval($data['latitude']),
                'longitude' => floatval($data['longitude']),
                'id' => $siteId
            ]);

            if ($success) {
                $this->setFlash('success', 'Site updated successfully');
                return $this->redirect('admin/sites');
            } else {
                $this->setFlash('error', 'Failed to update site');
                return $this->redirect("admin/sites/{$siteId}/edit");
            }

        } catch (Exception $e) {
            error_log("Site update error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update site');
            return $this->redirect("admin/sites/{$id}/edit");
        }
    }

    /**
     * Delete site
     */
    public function destroy($id)
    {
        if ($this->method() !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect('admin/sites');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect('admin/sites');
        }

        try {
            $siteId = intval($id);
            if ($siteId <= 0) {
                $this->setFlash('error', 'Invalid site ID');
                return $this->redirect('admin/sites');
            }

            $site = $this->db->fetchOne("SELECT * FROM sites WHERE id = ? LIMIT 1", [$siteId]);
            if (!$site) {
                $this->setFlash('error', 'Site not found');
                return $this->redirect('admin/sites');
            }

            // Check if site has plots or properties
            $plotCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM plot_master WHERE site_id = ?", [$siteId])['count'];
            $propertyCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM properties WHERE site_id = ?", [$siteId])['count'];

            if ($plotCount > 0 || $propertyCount > 0) {
                $this->setFlash('error', 'Cannot delete site with existing plots or properties');
                return $this->redirect('admin/sites');
            }

            $success = $this->db->prepare("DELETE FROM sites WHERE id = ?")->execute([$siteId]);

            if ($success) {
                $this->setFlash('success', 'Site deleted successfully');
            } else {
                $this->setFlash('error', 'Failed to delete site');
            }

            return $this->redirect('admin/sites');

        } catch (Exception $e) {
            error_log("Site deletion error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to delete site');
            return $this->redirect('admin/sites');
        }
    }
}
