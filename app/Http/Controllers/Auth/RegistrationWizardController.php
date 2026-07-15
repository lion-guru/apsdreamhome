<?php
/**
 * Multi-step Registration Wizard Controller
 * Handles 4-step registration with OTP verification and abandoned cart recovery
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

class RegistrationWizardController extends BaseController
{
    protected $db;
    private const STEPS = ['step1', 'step2', 'step3', 'step4'];
    private const STEP_TITLES = [
        'step1' => 'Account Basics',
        'step2' => 'Profile Details',
        'step3' => 'Preferences',
        'step4' => 'Verification',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    public function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Resolve current wizard state (session + DB)
     */
    private function getState(): array
    {
        if (!isset($_SESSION['wizard']['session_id'])) {
            $_SESSION['wizard']['session_id'] = session_id();
        }
        $sessionId = $_SESSION['wizard']['session_id'];
        $row = $this->db->fetchOne(
            "SELECT * FROM incomplete_registrations WHERE session_id = ? ORDER BY id DESC LIMIT 1",
            [$sessionId]
        );
        if (!$row) {
            return [
                'session_id' => $sessionId,
                'current_step' => 'step1',
                'progress_percent' => 25,
                'form_data' => [],
            ];
        }
        $formData = !empty($row['form_data']) ? json_decode($row['form_data'], true) : [];
        if (!is_array($formData)) $formData = [];
        return [
            'id' => (int)$row['id'],
            'session_id' => $sessionId,
            'current_step' => $row['current_step'] ?? 'step1',
            'progress_percent' => (int)($row['progress_percent'] ?? 25),
            'form_data' => $formData,
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
        ];
    }

    /**
     * Persist wizard state to DB
     */
    private function saveState(string $step, int $progress, array $formData, ?string $email = null, ?string $phone = null): int
    {
        $state = $this->getState();
        $payload = json_encode($formData, JSON_UNESCAPED_UNICODE);
        $sessionId = $state['session_id'];

        if (!empty($state['id'])) {
            $this->db->execute(
                "UPDATE incomplete_registrations
                 SET current_step = ?, progress_percent = ?, form_data = ?,
                     email = COALESCE(?, email), phone = COALESCE(?, phone),
                     last_activity_at = NOW(), source = COALESCE(source, 'web_wizard')
                 WHERE id = ?",
                [$step, $progress, $payload, $email, $phone, $state['id']]
            );
            return $state['id'];
        }

        $this->db->execute(
            "INSERT INTO incomplete_registrations
                (session_id, email, phone, form_data, current_step, progress_percent, last_activity_at, source)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), 'web_wizard')",
            [$sessionId, $email, $phone, $payload, $step, $progress]
        );
        return (int)$this->db->lastInsertId();
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
            'errors' => $_SESSION['wizard_errors'] ?? [],
            'old' => $_SESSION['wizard_old'] ?? [],
            'page_title' => self::STEP_TITLES[$step] ?? 'Register',
        ], $extra);
        unset($_SESSION['wizard_errors'], $_SESSION['wizard_old']);

        $this->layout = false;
        ob_start();
        extract($data);
        $viewPath = __DIR__ . '/../../../views/auth/registration/' . $step . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "Step view not found: $step";
        }
        echo ob_get_clean();
    }

    public function step1()
    {
        $this->renderStep('step1');
    }

    public function saveStep1()
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $errors = [];

        if ($name === '' || strlen($name) < 2) $errors[] = 'Name must be at least 2 characters';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) $errors[] = 'Valid phone is required';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
        if ($password !== $confirm) $errors[] = 'Passwords do not match';

        if ($existing = $this->db->fetchOne("SELECT id FROM users WHERE email = ?", [$email])) {
            $errors[] = 'Email already registered';
        }

        if (!empty($errors)) {
            $_SESSION['wizard_errors'] = $errors;
            $_SESSION['wizard_old'] = compact('name', 'email', 'phone');
            header('Location: ' . BASE_URL . '/register/step1');
            exit;
        }

        $state = $this->getState();
        $formData = array_merge($state['form_data'], [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $this->saveState('step2', 50, $formData, $email, $phone);
        header('Location: ' . BASE_URL . '/register/step2');
        exit;
    }

    public function step2()
    {
        $state = $this->getState();
        if (empty($state['form_data']['email'])) {
            header('Location: ' . BASE_URL . '/register/step1');
            exit;
        }
        $this->renderStep('step2');
    }

    public function saveStep2()
    {
        $city = trim($_POST['city'] ?? '');
        $occupation = trim($_POST['occupation'] ?? '');
        $income = trim($_POST['income_range'] ?? '');
        $errors = [];
        if ($city === '') $errors[] = 'City is required';
        if ($occupation === '') $errors[] = 'Occupation is required';
        if ($income === '') $errors[] = 'Income range is required';

        if (!empty($errors)) {
            $_SESSION['wizard_errors'] = $errors;
            $_SESSION['wizard_old'] = compact('city', 'occupation', 'income');
            header('Location: ' . BASE_URL . '/register/step2');
            exit;
        }

        $state = $this->getState();
        $formData = array_merge($state['form_data'], compact('city', 'occupation', 'income_range'));
        $this->saveState('step3', 75, $formData);
        header('Location: ' . BASE_URL . '/register/step3');
        exit;
    }

    public function step3()
    {
        $state = $this->getState();
        if (empty($state['form_data']['email'])) {
            header('Location: ' . BASE_URL . '/register/step1');
            exit;
        }
        $this->renderStep('step3');
    }

    public function saveStep3()
    {
        $state = $this->getState();
        $propertyType = $_POST['property_type'] ?? '';
        $budget = trim($_POST['budget_range'] ?? '');
        $location = trim($_POST['location_preference'] ?? '');
        $formData = array_merge($state['form_data'], [
            'property_type' => $propertyType,
            'budget_range' => $budget,
            'location_preference' => $location,
        ]);
        $this->saveState('step4', 100, $formData);
        header('Location: ' . BASE_URL . '/register/step4');
        exit;
    }

    public function step4()
    {
        $state = $this->getState();
        if (empty($state['form_data']['email'])) {
            header('Location: ' . BASE_URL . '/register/step1');
            exit;
        }
        $this->renderStep('step4');
    }

    public function saveStep4()
    {
        $state = $this->getState();
        $captcha = trim($_POST['captcha'] ?? '');
        $errors = [];
        if ($captcha === '' || strcasecmp($captcha, $_SESSION['wizard_captcha'] ?? '') !== 0) {
            $errors[] = 'Invalid captcha';
        }
        if (empty($state['form_data']['email_otp_verified']) && empty($state['form_data']['phone_otp_verified'])) {
            $errors[] = 'Please verify at least one of email or phone OTP';
        }
        if (!empty($errors)) {
            $_SESSION['wizard_errors'] = $errors;
            header('Location: ' . BASE_URL . '/register/step4');
            exit;
        }
        $this->complete();
    }

    public function resendOtp()
    {
        $type = $_POST['type'] ?? '';
        $state = $this->getState();
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['wizard_otp'][$type] = $otp;
        $_SESSION['wizard_otp_ts'][$type] = time();

        $formData = $state['form_data'];
        $formData['pending_otp_' . $type] = $otp;
        $this->saveState($state['current_step'], $state['progress_percent'], $formData);

        if ($type === 'email' && !empty($state['email'])) {
            $this->logOtpSend('email', $state['email'], $otp);
        } elseif ($type === 'phone' && !empty($state['phone'])) {
            $this->logOtpSend('sms', $state['phone'], $otp);
        }
        header('Location: ' . BASE_URL . '/register/step4?resent=' . urlencode($type));
        exit;
    }

    private function logOtpSend(string $gateway, string $recipient, string $otp): void
    {
        try {
            $this->db->execute(
                "INSERT INTO gateway_logs (gateway, action, recipient, status, request_body, response_body, created_at)
                 VALUES (?, 'otp_send', ?, 'success', ?, ?, NOW())",
                [
                    $gateway,
                    $recipient,
                    json_encode(['type' => $gateway]),
                    json_encode(['sent' => true, 'masked_otp' => substr($otp, 0, 2) . '****'])
                ]
            );
        } catch (\Throwable $e) {
            error_log('[RegistrationWizard] OTP log failed: ' . $e->getMessage());
        }
    }

    public function verifyOtp()
    {
        $type = $_POST['type'] ?? '';
        $code = trim($_POST['code'] ?? '');
        $state = $this->getState();
        $expected = $_SESSION['wizard_otp'][$type] ?? $state['form_data']['pending_otp_' . $type] ?? '';
        $ts = $_SESSION['wizard_otp_ts'][$type] ?? 0;

        if ($expected === '' || $code !== $expected) {
            $_SESSION['wizard_errors'] = ['Invalid OTP code'];
            header('Location: ' . BASE_URL . '/register/step4');
            exit;
        }
        if (time() - $ts > 600) {
            $_SESSION['wizard_errors'] = ['OTP expired, please resend'];
            header('Location: ' . BASE_URL . '/register/step4');
            exit;
        }

        $formData = $state['form_data'];
        $formData[$type . '_otp_verified'] = true;
        unset($formData['pending_otp_' . $type]);
        $this->saveState('step4', 100, $formData);
        header('Location: ' . BASE_URL . '/register/step4?verified=' . urlencode($type));
        exit;
    }

    public function complete()
    {
        $state = $this->getState();
        if (empty($state['form_data']['email']) || empty($state['form_data']['password_hash'])) {
            header('Location: ' . BASE_URL . '/register/step1');
            exit;
        }
        $data = $state['form_data'];
        try {
            $this->db->execute(
                "INSERT INTO users (name, email, phone, password, role, status, city, address, created_at)
                 VALUES (?, ?, ?, ?, 'customer', 'active', ?, ?, NOW())",
                [
                    $data['name'],
                    $data['email'],
                    $data['phone'],
                    $data['password_hash'],
                    $data['city'] ?? null,
                    $data['location_preference'] ?? null,
                ]
            );
            $userId = (int)$this->db->lastInsertId();
            if (!empty($state['id'])) {
                $this->db->execute(
                    "UPDATE incomplete_registrations SET recovered_at = NOW(), recovered_user_id = ? WHERE id = ?",
                    [$userId, $state['id']]
                );
            }
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $data['name'];
            $_SESSION['user_email'] = $data['email'];
            $_SESSION['user_phone'] = $data['phone'];
            $_SESSION['role'] = 'customer';
            unset($_SESSION['wizard'], $_SESSION['wizard_otp'], $_SESSION['wizard_otp_ts'], $_SESSION['wizard_captcha']);
            header('Location: ' . BASE_URL . '/user/dashboard?registered=1');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['wizard_errors'] = ['Registration failed: ' . $e->getMessage()];
            header('Location: ' . BASE_URL . '/register/step4');
            exit;
        }
    }

    public function skip()
    {
        $state = $this->getState();
        $current = $state['current_step'];
        $nextStep = [
            'step1' => 'step2',
            'step2' => 'step3',
            'step3' => 'step4',
            'step4' => 'complete',
        ][$current] ?? 'complete';

        $progress = ['step1' => 25, 'step2' => 50, 'step3' => 75, 'step4' => 100][$nextStep] ?? 100;
        $this->saveState($nextStep, $progress, $state['form_data']);

        if ($nextStep === 'complete') {
            $this->complete();
        } else {
            header('Location: ' . BASE_URL . '/register/' . $nextStep);
            exit;
        }
    }
}
