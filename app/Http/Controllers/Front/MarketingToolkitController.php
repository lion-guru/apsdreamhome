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

    /**
     * Save personal branding (POST)
     */
    public function saveBranding()
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $result = $this->toolkit->saveBranding((int)$_SESSION['user_id'], [
            'display_name' => $input['display_name'] ?? '',
            'phone' => $input['phone'] ?? '',
            'photo_path' => $input['photo_path'] ?? '',
            'tagline' => $input['tagline'] ?? '',
        ]);
        return $this->jsonResponse($result);
    }

    /**
     * Get personal branding (GET)
     */
    public function getBranding()
    {
        $this->requireLogin();
        $result = $this->toolkit->getBranding((int)$_SESSION['user_id']);
        return $this->jsonResponse(['success' => true, 'data' => $result]);
    }

    /**
     * Add sticker badge to photo (POST, multipart)
     */
    public function sticker()
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return $this->jsonResponse(['success' => false, 'message' => 'Please upload a photo'], 400);
        }
        $stickerType = $_POST['sticker_type'] ?? 'offer';
        $customText = trim($_POST['custom_text'] ?? '');
        $result = $this->toolkit->addSticker($_FILES['photo']['tmp_name'], $stickerType, $customText);
        if ($result['success']) {
            $result['whatsapp_share'] = $this->toolkit->getWhatsAppShareLink('Check this property from APS Dream Home!');
        }
        return $this->jsonResponse($result, $result['success'] ? 200 : 500);
    }

    /**
     * Make collage from 2-4 photos (POST, multipart)
     */
    public function collage()
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        $paths = [];
        if (!empty($_FILES['photos'])) {
            $files = $_FILES['photos'];
            // Normalize single vs multiple upload structure
            if (is_array($files['tmp_name'])) {
                foreach ($files['tmp_name'] as $i => $tmp) {
                    if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                        $paths[] = $tmp;
                    }
                }
            } elseif (($files['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $paths[] = $files['tmp_name'];
            }
        }
        if (count($paths) < 2) {
            return $this->jsonResponse(['success' => false, 'message' => 'Upload at least 2 photos'], 400);
        }
        $branding = $this->toolkit->getBranding((int)$_SESSION['user_id']);
        $result = $this->toolkit->makeCollage($paths, $_POST['layout'] ?? 'grid2x2', $branding);
        return $this->jsonResponse($result, $result['success'] ? 200 : 500);
    }

    /**
     * Generate festival post (POST)
     */
    public function festival()
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $branding = $this->toolkit->getBranding((int)$_SESSION['user_id']);
        $result = $this->toolkit->generateFestivalPost($input['festival'] ?? 'diwali', $branding);
        return $this->jsonResponse($result, $result['success'] ? 200 : 500);
    }

    /**
     * Generate QR code for referral link (POST)
     */
    public function qr()
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $text = trim($input['text'] ?? '');
        // Default: user's referral link
        if ($text === '') {
            try {
                $stmt = $this->db->prepare("SELECT referral_code FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([(int)$_SESSION['user_id']]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $refCode = $row['referral_code'] ?? '';
                $text = (defined('BASE_URL') ? BASE_URL : '') . '/register' . ($refCode ? '?ref=' . urlencode($refCode) : '');
            } catch (\Throwable $e) {
                $text = (defined('BASE_URL') ? BASE_URL : '') . '/register';
            }
        }
        $result = $this->toolkit->generateQR($text, 300);
        return $this->jsonResponse($result, $result['success'] ? 200 : 500);
    }

    /**
     * Add QR (referral link) overlay to photo (POST, multipart)
     */
    public function qrPhoto()
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return $this->jsonResponse(['success' => false, 'message' => 'Please upload a photo'], 400);
        }
        // Build referral link
        $qrText = trim($_POST['qr_text'] ?? '');
        if ($qrText === '') {
            try {
                $stmt = $this->db->prepare("SELECT referral_code FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([(int)$_SESSION['user_id']]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $refCode = $row['referral_code'] ?? '';
                $qrText = (defined('BASE_URL') ? BASE_URL : '') . '/register' . ($refCode ? '?ref=' . urlencode($refCode) : '');
            } catch (\Throwable $e) {
                $qrText = (defined('BASE_URL') ? BASE_URL : '') . '/register';
            }
        }
        $result = $this->toolkit->addQRToPhoto($_FILES['photo']['tmp_name'], $qrText, (int)$_SESSION['user_id']);
        if ($result['success']) {
            $result['whatsapp_share'] = $this->toolkit->getWhatsAppShareLink('Scan karo aur APS Dream Home se judo! ' . $qrText);
        }
        return $this->jsonResponse($result, $result['success'] ? 200 : 500);
    }

    /**
     * Make animated GIF slideshow (POST, multipart, 2-5 photos)
     */
    public function slideshow()
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        $paths = [];
        if (!empty($_FILES['photos'])) {
            $files = $_FILES['photos'];
            if (is_array($files['tmp_name'])) {
                foreach ($files['tmp_name'] as $i => $tmp) {
                    if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                        $paths[] = $tmp;
                    }
                }
            } elseif (($files['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $paths[] = $files['tmp_name'];
            }
        }
        if (count($paths) < 2) {
            return $this->jsonResponse(['success' => false, 'message' => 'Upload at least 2 photos'], 400);
        }
        $branding = $this->toolkit->getBranding((int)$_SESSION['user_id']);
        $result = $this->toolkit->makeSlideshow($paths, 150, $branding);
        return $this->jsonResponse($result, $result['success'] ? 200 : 500);
    }

    /**
     * Template gallery page
     */
    public function templates()
    {
        $this->requireLogin();

        $category = $_GET['category'] ?? '';
        $templates = $this->toolkit->listTemplates($category);

        $role = $_SESSION['role'] ?? 'customer';
        $this->layout = in_array($role, ['associate', 'agent'], true) ? 'layouts/' . $role : 'layouts/customer';

        $this->render('pages/marketing/templates', [
            'page_title' => 'Template Gallery - APS Dream Home',
            'templates' => $templates,
            'category' => $category,
            'current_page' => 'marketing',
        ]);
    }

    /**
     * List templates (JSON, for gallery filter)
     */
    public function listTemplates()
    {
        $this->requireLogin();
        $category = $_GET['category'] ?? '';
        return $this->jsonResponse(['success' => true, 'data' => $this->toolkit->listTemplates($category)]);
    }

    /**
     * Render template with photo + data (POST, multipart)
     */
    public function renderTemplate()
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        $slug = trim($_POST['slug'] ?? '');
        if ($slug === '') {
            return $this->jsonResponse(['success' => false, 'message' => 'Template required'], 400);
        }

        $photoPath = null;
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoPath = $_FILES['photo']['tmp_name'];
        }

        $data = [
            'price' => trim($_POST['price'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'offer' => trim($_POST['offer'] ?? ''),
            'property_type' => trim($_POST['property_type'] ?? ''),
        ];

        // Merge saved branding + referral code
        $branding = $this->toolkit->getBranding((int)$_SESSION['user_id']);
        try {
            $stmt = $this->db->prepare("SELECT referral_code FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([(int)$_SESSION['user_id']]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $branding['referral_code'] = $row['referral_code'] ?? '';
        } catch (\Throwable $e) {
            $branding['referral_code'] = '';
        }

        $result = $this->toolkit->renderTemplate($slug, $photoPath, $data, $branding);
        return $this->jsonResponse($result, $result['success'] ? 200 : 500);
    }

    /**
     * Admin: save template (POST, admin only)
     */
    public function saveTemplate()
    {
        $this->requireLogin();
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin', 'super_admin', 'manager'], true)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Admin only'], 403);
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $result = $this->toolkit->saveTemplate($input, (int)$_SESSION['user_id']);
        return $this->jsonResponse($result, $result['success'] ? 200 : 500);
    }

    /**
     * Generate digital visiting card (POST)
     */
    public function visitingCard()
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        // Merge with saved branding (POST overrides saved)
        $saved = $this->toolkit->getBranding((int)$_SESSION['user_id']);
        $branding = [
            'display_name' => trim($input['display_name'] ?? $saved['display_name'] ?? ''),
            'phone' => trim($input['phone'] ?? $saved['phone'] ?? ''),
            'tagline' => trim($input['tagline'] ?? $saved['tagline'] ?? 'Associate | APS Dream Home'),
            'referral_code' => '',
        ];
        try {
            $stmt = $this->db->prepare("SELECT referral_code FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([(int)$_SESSION['user_id']]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $branding['referral_code'] = $row['referral_code'] ?? '';
        } catch (\Throwable $e) {
            // ignore
        }

        $result = $this->toolkit->generateVisitingCard($branding);
        if ($result['success']) {
            $result['whatsapp_share'] = $this->toolkit->getWhatsAppShareLink("Mera digital visiting card — {$branding['display_name']}, {$branding['phone']}, APS Dream Home");
        }
        return $this->jsonResponse($result, $result['success'] ? 200 : 500);
    }
}