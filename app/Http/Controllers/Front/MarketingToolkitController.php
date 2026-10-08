<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\MarketingToolkitService;

/**
 * Marketing Toolkit — watermark, banner generator, AI content writer.
 * Used by: associate, agent, admin, builder, telecaller.
 */
class MarketingToolkitController extends BaseController
{
    private $toolkit;

    public function __construct()
    {
        parent::__construct();
        $this->toolkit = new MarketingToolkitService($this->db);
    }

    private function requireLogin()
    {
        @session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Toolkit dashboard page
     */
    public function index()
    {
        $this->requireLogin();

        $stats = [];
        try {
            $stats = $this->toolkit->getUsageStats((int)$_SESSION['user_id']);
        } catch (\Throwable $e) {
            error_log('MarketingToolkit::index stats: ' . $e->getMessage());
        }

        $this->layout = 'layouts/customer';
        // Use associate layout for associate/agent
        $role = $_SESSION['role'] ?? 'customer';
        if (in_array($role, ['associate', 'agent'], true)) {
            $this->layout = 'layouts/' . $role;
        }

        $this->render('pages/marketing/toolkit', [
            'page_title' => 'Marketing Toolkit - APS Dream Home',
            'stats' => $stats,
            'current_page' => 'marketing',
        ]);
    }

    /**
     * Add watermark to uploaded photo (POST, multipart)
     */
    public function watermark()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return $this->jsonResponse(['success' => false, 'message' => 'Please upload a photo'], 400);
        }

        // Validate image
        $finfo = @getimagesize($_FILES['photo']['tmp_name']);
        if (!$finfo || !in_array($finfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Please upload a valid image (JPG/PNG/GIF/WebP)'], 400);
        }

        if ($_FILES['photo']['size'] > 10 * 1024 * 1024) {
            return $this->jsonResponse(['success' => false, 'message' => 'Image must be under 10MB'], 400);
        }

        $position = $_POST['position'] ?? 'bottom-right';
        $opacity = max(10, min(100, (int)($_POST['opacity'] ?? 80)));

        $result = $this->toolkit->addWatermark($_FILES['photo']['tmp_name'], null, $opacity, $position);
        return $this->jsonResponse($result, $result['success'] ? 200 : 500);
    }

    /**
     * Generate branded price banner (POST, multipart or property_id)
     */
    public function banner()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        $sourcePath = null;

        // Option 1: uploaded photo
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $finfo = @getimagesize($_FILES['photo']['tmp_name']);
            if (!$finfo) {
                return $this->jsonResponse(['success' => false, 'message' => 'Invalid image'], 400);
            }
            $sourcePath = $_FILES['photo']['tmp_name'];
        }
        // Option 2: existing property photo by ID
        elseif (!empty($_POST['property_id'])) {
            $propertyId = (int)$_POST['property_id'];
            try {
                $stmt = $this->db->prepare("SELECT image FROM user_properties WHERE id = ? LIMIT 1");
                $stmt->execute([$propertyId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row && !empty($row['image'])) {
                    $imgPath = (defined('APS_ROOT') ? APS_ROOT : dirname(__DIR__, 3)) . '/' . ltrim($row['image'], '/');
                    if (file_exists($imgPath)) {
                        $sourcePath = $imgPath;
                    }
                }
            } catch (\Throwable $e) {
                error_log('MarketingToolkit::banner property lookup: ' . $e->getMessage());
            }
        }

        if (!$sourcePath) {
            return $this->jsonResponse(['success' => false, 'message' => 'Please upload a photo or select a property'], 400);
        }

        $data = [
            'price' => trim($_POST['price'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'offer' => trim($_POST['offer'] ?? ''),
            'property_type' => trim($_POST['property_type'] ?? ''),
            'phone' => trim($_POST['phone'] ?? $_SESSION['user_phone'] ?? '+91 73092 68077'),
        ];

        $result = $this->toolkit->generateBanner($sourcePath, $data);

        // Add WhatsApp share link
        if ($result['success']) {
            $shareText = "🏡 {$data['location']} - {$data['price']}"
                . ($data['offer'] ? "\n🎁 {$data['offer']}" : "")
                . "\n\nAPS Dream Home\n📞 {$data['phone']}";
            $result['whatsapp_share'] = $this->toolkit->getWhatsAppShareLink($shareText);
        }

        return $this->jsonResponse($result, $result['success'] ? 200 : 500);
    }

    /**
     * AI content writer (POST, JSON)
     */
    public function aiWriter()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $data = [
            'property_type' => trim($input['property_type'] ?? 'प्लॉट'),
            'price' => trim($input['price'] ?? ''),
            'location' => trim($input['location'] ?? 'गोरखपुर'),
            'offer' => trim($input['offer'] ?? ''),
            'size_sqft' => trim($input['size_sqft'] ?? ''),
        ];

        $result = $this->toolkit->generatePostContent($data);

        // Add WhatsApp share link with full post
        if ($result['success']) {
            $fullPost = $result['headline'] . "\n\n" . $result['body'] . "\n\n" . $result['hashtags'];
            $result['whatsapp_share'] = $this->toolkit->getWhatsAppShareLink($fullPost);
        }

        // Log usage
        try {
            $this->toolkit->getUsageStats((int)$_SESSION['user_id']); // ensures table exists
        } catch (\Throwable $e) {
            // ignore
        }

        return $this->jsonResponse($result);
    }

    /**
     * Get WhatsApp share link for arbitrary text (POST)
     */
    public function whatsappShare()
    {
        $this->requireLogin();

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $message = trim($input['message'] ?? '');
        $phone = trim($input['phone'] ?? '');

        if ($message === '') {
            return $this->jsonResponse(['success' => false, 'message' => 'Message is required'], 400);
        }

        return $this->jsonResponse([
            'success' => true,
            'share_url' => $this->toolkit->getWhatsAppShareLink($message, $phone),
        ]);
    }
}