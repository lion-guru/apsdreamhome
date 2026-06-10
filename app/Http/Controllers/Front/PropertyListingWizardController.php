<?php
/**
 * Multi-step Property Listing Wizard Controller
 * 8-step flow: basics → location → dimensions → pricing → amenities → images → review → contact → publish
 */

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use UploadValidator;

class PropertyListingWizardController extends BaseController
{
    protected $db;
    private const STEPS = [
        'step1' => 'Basics',
        'step2' => 'Location',
        'step3' => 'Dimensions',
        'step4' => 'Pricing',
        'step5' => 'Amenities',
        'step6' => 'Images',
        'step7' => 'Review',
        'step8' => 'Contact',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    private function getDraftId(): string
    {
        if (!isset($_SESSION['listing_wizard_id'])) {
            $_SESSION['listing_wizard_id'] = 'draft_' . session_id() . '_' . time();
        }
        return $_SESSION['listing_wizard_id'];
    }

    private function getState(): array
    {
        $draftId = $this->getDraftId();
        $row = $this->db->fetchOne(
            "SELECT * FROM user_properties WHERE id = ? OR admin_notes = ? LIMIT 1",
            [0, $draftId]
        );
        // We persist as a special row with admin_notes = draft_id and id = 0 (rejected by auto-increment).
        // Instead use a separate in-memory + a simple file-based draft pattern.
        $file = sys_get_temp_dir() . '/listing_draft_' . md5($draftId) . '.json';
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if (is_array($data)) return $data + ['current_step' => 'step1', 'progress_percent' => 12];
        }
        return ['current_step' => 'step1', 'progress_percent' => 12, 'form_data' => []];
    }

    private function saveState(string $step, int $progress, array $formData): void
    {
        $draftId = $this->getDraftId();
        $file = sys_get_temp_dir() . '/listing_draft_' . md5($draftId) . '.json';
        $payload = ['current_step' => $step, 'progress_percent' => $progress, 'form_data' => $formData, 'draft_id' => $draftId, 'updated_at' => time()];
        file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    private function clearDraft(): void
    {
        $draftId = $this->getDraftId();
        $file = sys_get_temp_dir() . '/listing_draft_' . md5($draftId) . '.json';
        if (file_exists($file)) @unlink($file);
        unset($_SESSION['listing_wizard_id']);
    }

    private function renderStep(string $step, array $extra = [])
    {
        $state = $this->getState();
        $data = array_merge([
            'csrf_token' => $this->getCsrfToken(),
            'state' => $state,
            'step' => $step,
            'step_num' => (int)substr($step, 4),
            'progress' => $state['progress_percent'],
            'errors' => $_SESSION['listing_errors'] ?? [],
            'old' => $_SESSION['listing_old'] ?? [],
            'page_title' => self::STEPS[$step] ?? 'List Property',
        ], $extra);
        unset($_SESSION['listing_errors'], $_SESSION['listing_old']);
        $this->layout = false;
        ob_start();
        extract($data);
        $viewPath = __DIR__ . '/../../../views/list-property/' . $step . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "Step view not found: $step";
        }
        echo ob_get_clean();
    }

    public function step1() { $this->renderStep('step1'); }
    public function step2() { $this->renderStep('step2'); }
    public function step3() { $this->renderStep('step3'); }
    public function step4() { $this->renderStep('step4'); }
    public function step5() { $this->renderStep('step5'); }
    public function step6() { $this->renderStep('step6'); }
    public function step7() { $this->renderStep('step7'); }
    public function step8() { $this->renderStep('step8'); }

    public function saveStep1()
    {
        $propertyType = $_POST['property_type'] ?? '';
        $listingType = $_POST['listing_type'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $errors = [];
        $validTypes = ['plot', 'flat', 'house', 'shop', 'farmhouse', 'land', 'apartment', 'villa'];
        if (!in_array($propertyType, $validTypes, true)) $errors[] = 'Valid property type required';
        if (!in_array($listingType, ['sell', 'rent'], true)) $errors[] = 'Valid listing type required';
        if (strlen($title) < 5) $errors[] = 'Title must be at least 5 chars';

        if (!empty($errors)) {
            $_SESSION['listing_errors'] = $errors;
            $_SESSION['listing_old'] = compact('propertyType', 'listingType', 'title');
            header('Location: ' . BASE_URL . '/list-property/step1');
            exit;
        }
        $state = $this->getState();
        $formData = array_merge($state['form_data'], compact('propertyType', 'listingType', 'title'));
        $this->saveState('step2', 25, $formData);
        header('Location: ' . BASE_URL . '/list-property/step2');
        exit;
    }

    public function saveStep2()
    {
        $state = $_POST;
        $state_val = trim($_POST['state'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');
        $errors = [];
        if ($city === '') $errors[] = 'City is required';
        if ($address === '') $errors[] = 'Address is required';
        if ($pincode !== '' && !preg_match('/^[0-9]{6}$/', $pincode)) $errors[] = 'Pincode must be 6 digits';
        if (!empty($errors)) {
            $_SESSION['listing_errors'] = $errors;
            $_SESSION['listing_old'] = compact('state_val', 'district', 'city', 'address', 'pincode');
            header('Location: ' . BASE_URL . '/list-property/step2');
            exit;
        }
        $cur = $this->getState();
        $formData = array_merge($cur['form_data'], [
            'state' => $state_val, 'district' => $district, 'city' => $city,
            'address' => $address, 'pincode' => $pincode,
        ]);
        $this->saveState('step3', 37, $formData);
        header('Location: ' . BASE_URL . '/list-property/step3');
        exit;
    }

    public function saveStep3()
    {
        $area = (int)($_POST['area'] ?? 0);
        $width = (float)($_POST['width'] ?? 0);
        $length = (float)($_POST['length'] ?? 0);
        $facing = trim($_POST['facing'] ?? '');
        $roadWidth = (float)($_POST['road_width'] ?? 0);
        $errors = [];
        if ($area <= 0) $errors[] = 'Area must be greater than 0';
        if ($width > 0 && $length > 0 && abs($width * $length - $area) > max(50, $area * 0.2)) {
            // soft warn, not error
        }
        if (!empty($errors)) {
            $_SESSION['listing_errors'] = $errors;
            header('Location: ' . BASE_URL . '/list-property/step3');
            exit;
        }
        $cur = $this->getState();
        $formData = array_merge($cur['form_data'], compact('area', 'width', 'length', 'facing', 'roadWidth'));
        $this->saveState('step4', 50, $formData);
        header('Location: ' . BASE_URL . '/list-property/step4');
        exit;
    }

    public function saveStep4()
    {
        $price = (float)($_POST['price'] ?? 0);
        $priceType = trim($_POST['price_type'] ?? 'lakh');
        $negotiable = isset($_POST['negotiable']) ? 1 : 0;
        $emiAvailable = isset($_POST['emi_available']) ? 1 : 0;
        $errors = [];
        if ($price <= 0) $errors[] = 'Price must be greater than 0';
        if (!empty($errors)) {
            $_SESSION['listing_errors'] = $errors;
            header('Location: ' . BASE_URL . '/list-property/step4');
            exit;
        }
        $cur = $this->getState();
        $formData = array_merge($cur['form_data'], compact('price', 'priceType', 'negotiable', 'emiAvailable'));
        $this->saveState('step5', 62, $formData);
        header('Location: ' . BASE_URL . '/list-property/step5');
        exit;
    }

    public function saveStep5()
    {
        $amenities = $_POST['amenities'] ?? [];
        if (!is_array($amenities)) $amenities = [];
        $amenities = array_map('strval', $amenities);
        $cur = $this->getState();
        $formData = array_merge($cur['form_data'], ['amenities' => $amenities]);
        $this->saveState('step6', 75, $formData);
        header('Location: ' . BASE_URL . '/list-property/step6');
        exit;
    }

    public function saveStep6()
    {
        $images = $_POST['uploaded_images'] ?? [];
        if (!is_array($images)) $images = [];
        if (count($images) > 10) $images = array_slice($images, 0, 10);
        $cur = $this->getState();
        $formData = array_merge($cur['form_data'], ['images' => $images]);
        $this->saveState('step7', 87, $formData);
        header('Location: ' . BASE_URL . '/list-property/step7');
        exit;
    }

    public function saveStep7()
    {
        $cur = $this->getState();
        $this->saveState('step8', 100, $cur['form_data']);
        header('Location: ' . BASE_URL . '/list-property/step8');
        exit;
    }

    public function saveStep8()
    {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $agree = isset($_POST['agree_tc']);
        $errors = [];
        if ($name === '') $errors[] = 'Name is required';
        if (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) $errors[] = 'Valid phone is required';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required';
        if (!$agree) $errors[] = 'You must agree to the Terms & Conditions';
        if (!empty($errors)) {
            $_SESSION['listing_errors'] = $errors;
            header('Location: ' . BASE_URL . '/list-property/step8');
            exit;
        }
        $cur = $this->getState();
        $formData = array_merge($cur['form_data'], compact('name', 'phone', 'email'));
        $this->saveState('step8', 100, $formData);
        $this->publish();
    }

    public function saveDraft()
    {
        $cur = $this->getState();
        $cur['form_data']['_draft_saved_at'] = date('Y-m-d H:i:s');
        $this->saveState($cur['current_step'] ?? 'step1', $cur['progress_percent'] ?? 12, $cur['form_data']);
        $_SESSION['listing_draft_msg'] = 'Draft saved successfully at ' . date('H:i:s');
        header('Location: ' . BASE_URL . '/list-property/' . ($cur['current_step'] ?? 'step1') . '?draft=1');
        exit;
    }

    public function publish()
    {
        $cur = $this->getState();
        $d = $cur['form_data'];
        if (empty($d['title']) || empty($d['propertyType']) || empty($d['listingType']) || empty($d['price'])) {
            $_SESSION['listing_errors'] = ['Missing required fields. Please complete all steps.'];
            header('Location: ' . BASE_URL . '/list-property/step1');
            exit;
        }
        try {
            $this->db->execute(
                "INSERT INTO user_properties
                    (name, phone, email, property_type, listing_type, address, location, area_sqft, price, price_type, description, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
                [
                    $d['name'] ?? 'Anonymous',
                    $d['phone'] ?? '',
                    $d['email'] ?? null,
                    $d['propertyType'],
                    $d['listingType'],
                    $d['address'] ?? '',
                    $d['city'] ?? '',
                    (int)($d['area'] ?? 0),
                    (float)($d['price'] ?? 0),
                    $d['priceType'] ?? 'lakh',
                    $this->buildDescription($d),
                ]
            );
            $newId = (int)$this->db->lastInsertId();
            $this->clearDraft();
            $_SESSION['listing_published_id'] = $newId;
            header('Location: ' . BASE_URL . '/list-property/step8?published=' . $newId);
            exit;
        } catch (\Throwable $e) {
            $_SESSION['listing_errors'] = ['Publish failed: ' . $e->getMessage()];
            header('Location: ' . BASE_URL . '/list-property/step8');
            exit;
        }
    }

    private function buildDescription(array $d): string
    {
        $lines = [];
        $lines[] = ($d['title'] ?? 'Property') . ' - ' . ucfirst($d['propertyType'] ?? '') . ' for ' . ucfirst($d['listingType'] ?? '');
        if (!empty($d['address'])) $lines[] = 'Address: ' . $d['address'];
        if (!empty($d['city'])) $lines[] = 'Location: ' . $d['city'] . (!empty($d['district']) ? ', ' . $d['district'] : '') . (!empty($d['state']) ? ', ' . $d['state'] : '');
        if (!empty($d['area'])) $lines[] = 'Area: ' . (int)$d['area'] . ' sqft';
        if (!empty($d['facing'])) $lines[] = 'Facing: ' . $d['facing'];
        if (!empty($d['amenities']) && is_array($d['amenities'])) {
            $lines[] = 'Amenities: ' . implode(', ', $d['amenities']);
        }
        if (!empty($d['price'])) $lines[] = 'Price: ₹' . number_format((float)$d['price']) . ' ' . ($d['priceType'] ?? 'lakh') . (!empty($d['negotiable']) ? ' (Negotiable)' : '');
        return implode("\n", $lines);
    }

    /**
     * AJAX image upload endpoint
     */
    public function uploadImage()
    {
        if (empty($_FILES['image']['tmp_name'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'No file']);
            return;
        }
        $file = $_FILES['image'];
        $v = \UploadValidator::validate($file, ['types' => 'images', 'max_size' => 5]);
        if (!$v['valid']) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $v['error']]);
            return;
        }
        $safeName = \UploadValidator::safeFilename($file['name']);
        $mime = mime_content_type($file['tmp_name']);
        $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $ext = $extMap[$mime] ?? pathinfo($safeName, PATHINFO_EXTENSION);
        $dir = __DIR__ . '/../../../public/uploads/property-draft/';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $filename = 'draft_' . session_id() . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target = $dir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Upload failed']);
            return;
        }
        echo json_encode([
            'ok' => true,
            'url' => BASE_URL . '/uploads/property-draft/' . $filename,
            'filename' => $filename,
            'size' => filesize($target),
        ]);
    }
}
