<?php
/**
 * Testimonials Admin Controller
 * Official members can manage reviews with approval workflow
 */

namespace App\Http\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database\Database;
use App\Services\CoreFunctionsServiceCustom;

class TestimonialsAdminController extends AdminController
{
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        
        if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
    }
    
    /**
     * List all testimonials with filtering
     */
    public function index()
    {
        $status = $_GET['status'] ?? 'all';
        $featured = $_GET['featured'] ?? null;
        
        try {
            $sql = "SELECT t.*, u.name as reviewed_by_name 
                    FROM testimonials t 
                    LEFT JOIN users u ON t.reviewed_by = u.id 
                    WHERE 1=1";
            $params = [];
            
            if ($status !== 'all') {
                $sql .= " AND t.status = ?";
                $params[] = $status;
            }
            
            if ($featured !== null) {
                $sql .= " AND t.featured = ?";
                $params[] = $featured ? 1 : 0;
            }
            
            $sql .= " ORDER BY t.submitted_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $testimonials = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get stats
            $stats = $this->getTestimonialStats();
            
            $this->view('admin/testimonials/index', [
                'page_title' => 'Testimonials Management',
                'testimonials' => $testimonials,
                'stats' => $stats,
                'statuses' => ['pending', 'approved', 'rejected'],
                'current_status' => $status
            ]);
        } catch (\Exception $e) {
            $this->view('admin/testimonials/index', [
                'page_title' => 'Testimonials Management',
                'testimonials' => [],
                'error' => 'Failed to load testimonials'
            ]);
        }
    }
    
    /**
     * View single testimonial
     */
    public function show($id)
    {
        try {
            $sql = "SELECT t.*, u.name as reviewed_by_name 
                    FROM testimonials t 
                    LEFT JOIN users u ON t.reviewed_by = u.id 
                    WHERE t.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $testimonial = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$testimonial) {
                header('Location: ' . BASE_URL . '/admin/testimonials');
                exit;
            }
            
            $this->view('admin/testimonials/view', [
                'page_title' => 'View Testimonial',
                'testimonial' => $testimonial,
                'statuses' => ['pending', 'approved', 'rejected']
            ]);
        } catch (\Exception $e) {
            header('Location: ' . BASE_URL . '/admin/testimonials');
            exit;
        }
    }
    
    /**
     * Update testimonial status
     */
    public function updateStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonFail('Invalid request', 405);
        }
        
        try {
            $status = $_POST['status'] ?? 'pending';
            $notes = $_POST['notes'] ?? '';
            $featured = isset($_POST['featured']) ? 1 : 0;
            
            $sql = "UPDATE testimonials SET 
                    status = ?, notes = ?, featured = ?, 
                    reviewed_at = NOW(), reviewed_by = ? 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $status, $notes, $featured,
                $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1,
                $id
            ]);
            
            return $this->jsonRespond([
                'success' => true,
                'message' => 'Testimonial ' . $status . ' successfully'
            ]);
        } catch (\Exception $e) {
            return $this->jsonFail('Failed to update: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete testimonial
     */
    public function delete($id)
    {
        try {
            $sql = "DELETE FROM testimonials WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            return $this->jsonRespond(['success' => true, 'message' => 'Deleted']);
        } catch (\Exception $e) {
            return $this->jsonFail('Failed to delete', 500);
        }
    }
    
    /**
     * Get statistics
     */
    private function getTestimonialStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) as featured
                FROM testimonials";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    private function jsonRespond($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    private function jsonFail($message, $code = 400)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}
