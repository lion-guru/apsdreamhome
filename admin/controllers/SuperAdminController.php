<?php
/**
 * Super Admin Controller
 * Handles all super admin functionality including visual editing and content management
 */ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/config/super-admin-config.php';

class SuperAdminController {
    private $db;
    private $permissions;
    
    public function __construct() {
        global $con;
        $this->db = $con;
        $this->permissions = $this->getCurrentUserPermissions();
    }
    
    /**
     * Get current user's permissions
     */
    private function getCurrentUserPermissions() {
        global $SUPER_ADMIN_PERMISSIONS, $ADMIN_PERMISSIONS, $EDITOR_PERMISSIONS;
        
        if(isset($_SESSION['usertype'])) {
            switch($_SESSION['usertype']) {
                case 'super_admin':
                    return $SUPER_ADMIN_PERMISSIONS;
                case 'admin':
                    return $ADMIN_PERMISSIONS;
                case 'editor':
                    return $EDITOR_PERMISSIONS;
                default:
                    return [];
            }
        }
        return [];
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission) {
        return isset($this->permissions[$permission]) && $this->permissions[$permission] === true;
    }
    
    /**
     * Update page content through visual editor
     */
    public function updatePageContent($pageId, $content, $layout = null) {
        if(!$this->hasPermission('manage_content')) {
            return ['success' => false, 'message' => 'Permission denied'];
        }
        
        try {
            // Backup current content
            $this->backupContent($pageId);
            
            // Update content
            $stmt = $this->db->prepare("UPDATE pages SET content = ?, layout = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssi", $content, $layout, $pageId);
            $result = $stmt->execute();
            
            if($result) {
                return ['success' => true, 'message' => 'Content updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update content'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Backup page content before changes
     */
    private function backupContent($pageId) {
        if(!CONTENT_BACKUP_ENABLED) return;
        
        try {
            $stmt = $this->db->prepare("INSERT INTO content_backups (page_id, content, created_at) SELECT id, content, NOW() FROM pages WHERE id = ?");
            $stmt->bind_param("i", $pageId);
            $stmt->execute();
            
            // Remove old backups
            $this->cleanOldBackups($pageId);
        } catch(Exception $e) {
            error_log("Backup failed: " . $e->getMessage());
        }
    }
    
    /**
     * Clean old backups keeping only recent versions
     */
    private function cleanOldBackups($pageId) {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM content_backups 
                WHERE page_id = ? 
                AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM content_backups 
                        WHERE page_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT ?
                    ) tmp
                )"
            );
            $maxVersions = MAX_CONTENT_VERSIONS;
            $stmt->bind_param("iii", $pageId, $pageId, $maxVersions);
            $stmt->execute();
        } catch(Exception $e) {
            error_log("Cleanup failed: " . $e->getMessage());
        }
    }
    
    /**
     * Restore content from backup
     */
    public function restoreContent($backupId) {
        if(!$this->hasPermission('manage_content')) {
            return ['success' => false, 'message' => 'Permission denied'];
        }
        
        try {
            $stmt = $this->db->prepare(
                "UPDATE pages p 
                JOIN content_backups b ON b.page_id = p.id 
                SET p.content = b.content 
                WHERE b.id = ?"
            );
            $stmt->bind_param("i", $backupId);
            $result = $stmt->execute();
            
            if($result) {
                return ['success' => true, 'message' => 'Content restored successfully'];
            }
            return ['success' => false, 'message' => 'Failed to restore content'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Update user permissions
     */
    public function updateUserPermissions($userId, $permissions) {
        if(!$this->hasPermission('manage_roles')) {
            return ['success' => false, 'message' => 'Permission denied'];
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE users SET permissions = ? WHERE id = ?");
            $permissionsJson = json_encode($permissions);
            $stmt->bind_param("si", $permissionsJson, $userId);
            $result = $stmt->execute();
            
            if($result) {
                return ['success' => true, 'message' => 'Permissions updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update permissions'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Upload and manage media files
     */
    public function uploadMedia($file) {
        if(!$this->hasPermission('manage_media')) {
            return ['success' => false, 'message' => 'Permission denied'];
        }
        
        try {
            // Validate file
            $fileSize = $file['size'];
            $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if($fileSize > MAX_FILE_UPLOAD_SIZE) {
                return ['success' => false, 'message' => 'File size exceeds limit'];
            }
            
            if(!in_array($fileType, explode(',', ALLOWED_FILE_TYPES))) {
                return ['success' => false, 'message' => 'File type not allowed'];
            }
            
            // Generate unique filename
            $filename = uniqid() . '.' . $fileType;
            $uploadPath = dirname(__DIR__) . '/uploads/' . $filename;
            
            if(move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Save to database
                $stmt = $this->db->prepare("INSERT INTO media (filename, type, size, uploaded_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("ssi", $filename, $fileType, $fileSize);
                $stmt->execute();
                
                return ['success' => true, 'message' => 'File uploaded successfully', 'filename' => $filename];
            }
            
            return ['success' => false, 'message' => 'Failed to upload file'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}