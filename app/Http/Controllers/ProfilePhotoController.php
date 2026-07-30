<?php
/**
 * ProfilePhotoController — Handles profile photo upload for all roles.
 *
 * Features:
 * - Single endpoint for ALL user roles (admin, customer, associate, agent, employee)
 * - Image validation (type, size, dimensions)
 * - Saves to public/uploads/profiles/
 * - Updates users.profile_image column
 * - Deletes old photo on replacement
 * - Returns JSON for AJAX upload + redirect fallback
 */

namespace App\Http\Controllers;

require_once __DIR__ . '/BaseController.php';

use App\Core\Database\Database;
use \App\Traits\TenantAwareTrait;

class ProfilePhotoController extends BaseController
{
    use TenantAwareTrait;

    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
    private const MAX_WIDTH = 1024;
    private const MAX_HEIGHT = 1024;
    private const UPLOAD_DIR = 'public/uploads/profiles/';

    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Handle profile photo upload (POST)
     */
    public function upload()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            parent::jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            $errorCode = $_FILES['profile_image']['error'] ?? -1;
            $message = 'Upload failed';
            if ($errorCode === UPLOAD_ERR_NO_FILE) $message = 'No file selected';
            elseif ($errorCode === UPLOAD_ERR_INI_SIZE || $errorCode === UPLOAD_ERR_FORM_SIZE) $message = 'File too large (max 2MB)';
            parent::jsonResponse(['success' => false, 'message' => $message], 400);
            exit;
        }

        $file = $_FILES['profile_image'];

        // Validate type
        if (!in_array($file['type'], self::ALLOWED_TYPES, true)) {
            parent::jsonResponse(['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP'], 400);
            exit;
        }

        // Validate size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            parent::jsonResponse(['success' => false, 'message' => 'File too large. Maximum 2MB allowed'], 400);
            exit;
        }

        // Validate dimensions
        $imgInfo = @getimagesize($file['tmp_name']);
        if (!$imgInfo) {
            parent::jsonResponse(['success' => false, 'message' => 'Invalid image file'], 400);
            exit;
        }
        if ($imgInfo[0] > self::MAX_WIDTH || $imgInfo[1] > self::MAX_HEIGHT) {
            parent::jsonResponse(['success' => false, 'message' => 'Image too large. Maximum 1024x1024 pixels'], 400);
            exit;
        }

        // Security: verify it's really an image
        $safeMime = mime_content_type($file['tmp_name']);
        if (!in_array($safeMime, self::ALLOWED_TYPES, true)) {
            parent::jsonResponse(['success' => false, 'message' => 'File is not a valid image'], 400);
            exit;
        }

        try {
            $db = Database::getInstance();

            // Delete old photo if exists
            $oldPhoto = $db->fetchColumn("SELECT profile_image FROM users WHERE id = ?", [$userId]);
            if ($oldPhoto) {
                $oldPath = __DIR__ . '/../../' . self::UPLOAD_DIR . basename($oldPhoto);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            // Save new photo
            $ext = match ($safeMime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                default => 'jpg',
            };
            $filename = 'profile_' . $userId . '_' . time() . '.' . $ext;
            $destPath = __DIR__ . '/../../' . self::UPLOAD_DIR . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                parent::jsonResponse(['success' => false, 'message' => 'Failed to save file'], 500);
                exit;
            }

            // Update DB
            $tid = (int)$this->tenantId();
            $db->query(
                "UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?",
                ['uploads/profiles/' . $filename, $userId, $tid]
            );

            $photoUrl = BASE_URL . '/' . self::UPLOAD_DIR . $filename;
            $_SESSION['profile_image'] = $photoUrl;

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            if ($isAjax) {
                parent::jsonResponse([
                    'success' => true,
                    'message' => 'Profile photo updated',
                    'photo_url' => $photoUrl,
                ]);
                exit;
            }

            $_SESSION['success'] = 'Profile photo updated successfully';
            $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/user/profile';
            header('Location: ' . $referer);
            exit;

        } catch (\Exception $e) {
            error_log("ProfilePhoto upload error: " . $e->getMessage());
            parent::jsonResponse(['success' => false, 'message' => 'Upload failed'], 500);
            exit;
        }
    }

    /**
     * Delete profile photo (POST)
     */
    public function delete()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            parent::jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        try {
            $db = Database::getInstance();
            $oldPhoto = $db->fetchColumn("SELECT profile_image FROM users WHERE id = ?", [$userId]);

            if ($oldPhoto) {
                $oldPath = __DIR__ . '/../../' . self::UPLOAD_DIR . basename($oldPhoto);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $tid = (int)$this->tenantId();
            $db->query("UPDATE users SET profile_image = NULL, updated_at = NOW() WHERE id = ? AND tenant_id = ?", [$userId, $tid]);
            unset($_SESSION['profile_image']);

            parent::jsonResponse(['success' => true, 'message' => 'Profile photo removed']);
            exit;

        } catch (\Exception $e) {
            error_log("ProfilePhoto delete error: " . $e->getMessage());
            parent::jsonResponse(['success' => false, 'message' => 'Delete failed'], 500);
            exit;
        }
    }

}
