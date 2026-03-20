<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database;
use Exception;

/**
 * LegalPagesController - Admin controller for managing legal pages
 * 
 * This controller handles CRUD operations for Terms and Conditions
 * and Privacy Policy pages, allowing admin users to update content.
 */
class LegalPagesController extends BaseController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * Display legal pages management dashboard
     */
    public function index()
    {
        // Check if user is admin
        if (!isset($_SESSION['admin_id'])) {
            $this->redirect('/admin/login');
            return;
        }

        // Get current legal page content from database or files
        $terms_content = $this->getTermsContent();
        $privacy_content = $this->getPrivacyContent();

        $data = [
            'page_title' => 'Legal Pages Management - Admin',
            'terms_content' => $terms_content,
            'privacy_content' => $privacy_content,
            'last_updated' => date('Y-m-d H:i:s')
        ];

        $this->render('admin/legal_pages/index', $data);
    }

    /**
     * Update Terms and Conditions
     */
    public function updateTerms()
    {
        if (!isset($_SESSION['admin_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $content = $_POST['content'] ?? '';
            $title = $_POST['title'] ?? 'Terms and Conditions';

            // Validate content
            if (empty($content)) {
                $this->jsonResponse(['success' => false, 'message' => 'Content cannot be empty']);
                return;
            }

            // Update content in database or file
            $success = $this->saveTermsContent($content, $title);

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Terms and Conditions updated successfully',
                    'last_updated' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update content']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Update Privacy Policy
     */
    public function updatePrivacy()
    {
        if (!isset($_SESSION['admin_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $content = $_POST['content'] ?? '';
            $title = $_POST['title'] ?? 'Privacy Policy';

            // Validate content
            if (empty($content)) {
                $this->jsonResponse(['success' => false, 'message' => 'Content cannot be empty']);
                return;
            }

            // Update content in database or file
            $success = $this->savePrivacyContent($content, $title);

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Privacy Policy updated successfully',
                    'last_updated' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update content']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get Terms and Conditions content
     */
    private function getTermsContent()
    {
        try {
            // Try to get from database first
            $query = "SELECT content, title, updated_at FROM legal_pages WHERE page_type = 'terms' LIMIT 1";
            $result = $this->db->fetch($query);

            if ($result) {
                return [
                    'content' => $result['content'],
                    'title' => $result['title'],
                    'updated_at' => $result['updated_at']
                ];
            }

            // Fallback to default content
            return [
                'content' => $this->getDefaultTermsContent(),
                'title' => 'Terms and Conditions',
                'updated_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'content' => $this->getDefaultTermsContent(),
                'title' => 'Terms and Conditions',
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Get Privacy Policy content
     */
    private function getPrivacyContent()
    {
        try {
            // Try to get from database first
            $query = "SELECT content, title, updated_at FROM legal_pages WHERE page_type = 'privacy' LIMIT 1";
            $result = $this->db->fetch($query);

            if ($result) {
                return [
                    'content' => $result['content'],
                    'title' => $result['title'],
                    'updated_at' => $result['updated_at']
                ];
            }

            // Fallback to default content
            return [
                'content' => $this->getDefaultPrivacyContent(),
                'title' => 'Privacy Policy',
                'updated_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'content' => $this->getDefaultPrivacyContent(),
                'title' => 'Privacy Policy',
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Save Terms and Conditions content
     */
    private function saveTermsContent($content, $title)
    {
        try {
            // Check if record exists
            $query = "SELECT id FROM legal_pages WHERE page_type = 'terms' LIMIT 1";
            $result = $this->db->fetch($query);

            if ($result) {
                // Update existing record
                $query = "UPDATE legal_pages SET content = ?, title = ?, updated_at = ? WHERE page_type = 'terms'";
                $this->db->execute($query, [$content, $title, date('Y-m-d H:i:s')]);
            } else {
                // Insert new record
                $query = "INSERT INTO legal_pages (page_type, content, title, updated_at) VALUES (?, ?, ?, ?)";
                $this->db->execute($query, ['terms', $content, $title, date('Y-m-d H:i:s')]);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error saving terms content: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save Privacy Policy content
     */
    private function savePrivacyContent($content, $title)
    {
        try {
            // Check if record exists
            $query = "SELECT id FROM legal_pages WHERE page_type = 'privacy' LIMIT 1";
            $result = $this->db->fetch($query);

            if ($result) {
                // Update existing record
                $query = "UPDATE legal_pages SET content = ?, title = ?, updated_at = ? WHERE page_type = 'privacy'";
                $this->db->execute($query, [$content, $title, date('Y-m-d H:i:s')]);
            } else {
                // Insert new record
                $query = "INSERT INTO legal_pages (page_type, content, title, updated_at) VALUES (?, ?, ?, ?)";
                $this->db->execute($query, ['privacy', $content, $title, date('Y-m-d H:i:s')]);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error saving privacy content: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get default Terms and Conditions content
     */
    private function getDefaultTermsContent()
    {
        return "<h1>Terms and Conditions</h1>
<p>Welcome to APS Dream Home. These terms and conditions outline the rules and regulations for the use of our website and services.</p>
<p>By accessing this website we assume you accept these terms and conditions. Do not continue to use APS Dream Home if you do not agree to take all of the terms and conditions stated on this page.</p>";
    }

    /**
     * Get default Privacy Policy content
     */
    private function getDefaultPrivacyContent()
    {
        return "<h1>Privacy Policy</h1>
<p>At APS Dream Home, accessible from apsdreamhome.com, one of our main priorities is the privacy of our visitors. This Privacy Policy document contains types of information that is collected and recorded by APS Dream Home and how we use it.</p>
<p>If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact us.</p>";
    }
}
