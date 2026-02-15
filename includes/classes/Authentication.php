<?php
require_once 'SessionManager.php';

class Authentication
{
    private $db;
    private $sessionManager;

    public function __construct()
    {
        $this->db = \App\Core\App::database();
        $this->sessionManager = new SessionManager();
    }

    public function login(string $email, string $password): array
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        // First check associates table
        $user = $this->db->fetch('SELECT * FROM associates WHERE email = :email', ['email' => $email]);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $userData = [
                    'uid' => $user['uid'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'utype' => 'associate'
                ];
                $this->sessionManager->login($userData);
                return ['success' => true, 'utype' => 'associate'];
            }
        }

        // Check users table
        $user = $this->db->fetch('SELECT * FROM users WHERE email = :email', ['email' => $email]);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $userData = [
                    'uid' => $user['uid'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'utype' => $user['utype']
                ];
                $this->sessionManager->login($userData);
                return ['success' => true, 'utype' => $user['utype']];
            }
        }

        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    public function registerAssociate(array $data): array
    {
        if (!$this->validateAssociateData($data)) {
            return ['success' => false, 'message' => 'Invalid data provided'];
        }

        // Check if email exists
        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Validate sponsor ID if provided
        if (!empty($data['sponsor_id'])) {
            $sponsorValidation = $this->validateSponsorId($data['sponsor_id']);
            if (!$sponsorValidation['valid']) {
                return ['success' => false, 'message' => $sponsorValidation['message']];
            }
        }

        // Generate new associate ID
        $newUid = $this->generateAssociateId();
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $params = [
            'uid' => $newUid,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $hashedPassword,
            'sponsor_id' => $data['sponsor_id']
        ];

        if ($this->db->execute('INSERT INTO associates (uid, name, email, phone, password, sponsor_id) VALUES (:uid, :name, :email, :phone, :password, :sponsor_id)', $params)) {
            return ['success' => true, 'message' => 'Registration successful', 'uid' => $newUid];
        }

        return ['success' => false, 'message' => 'Registration failed'];
    }

    public function registerUser(array $data): array
    {
        if (!$this->validateUserData($data)) {
            return ['success' => false, 'message' => 'Invalid data provided'];
        }

        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $params = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $hashedPassword,
            'utype' => $data['utype']
        ];

        if ($this->db->execute('INSERT INTO users (name, email, phone, password, utype) VALUES (:name, :email, :phone, :password, :utype)', $params)) {
            return ['success' => true, 'message' => 'Registration successful'];
        }

        return ['success' => false, 'message' => 'Registration failed'];
    }

    private function validateAssociateData(array $data): bool
    {
        return (
            isset($data['name']) &&
            isset($data['email']) &&
            isset($data['phone']) &&
            isset($data['password']) &&
            filter_var($data['email'], FILTER_VALIDATE_EMAIL) &&
            strlen($data['phone']) === 10 &&
            ctype_digit($data['phone']) &&
            strlen($data['password']) >= 8
        );
    }

    private function validateUserData(array $data): bool
    {
        return (
            isset($data['name']) &&
            isset($data['email']) &&
            isset($data['phone']) &&
            isset($data['password']) &&
            isset($data['utype']) &&
            $this->sessionManager->isValidUserType($data['utype']) &&
            filter_var($data['email'], FILTER_VALIDATE_EMAIL) &&
            strlen($data['phone']) === 10 &&
            ctype_digit($data['phone']) &&
            strlen($data['password']) >= 8
        );
    }

    private function emailExists(string $email): bool
    {
        // Check in associates table
        $result = $this->db->fetch('SELECT email FROM associates WHERE email = :email', ['email' => $email]);
        if ($result) {
            return true;
        }

        // Check in users table
        $result = $this->db->fetch('SELECT email FROM users WHERE email = :email', ['email' => $email]);
        return (bool)$result;
    }

    private function validateSponsorId(string $sponsorId): array
    {
        // Check sponsor ID format (APS followed by 6 digits)
        if (!preg_match('/^APS\d{6}$/', $sponsorId)) {
            return ['valid' => false, 'message' => 'Invalid sponsor ID format. Must be APS followed by 6 digits'];
        }

        // Check if sponsor exists and is active
        $sponsor = $this->db->fetch('SELECT uid, status FROM associates WHERE uid = :uid', ['uid' => $sponsorId]);

        if (!$sponsor) {
            return ['valid' => false, 'message' => 'Sponsor ID does not exist'];
        }

        if ($sponsor['status'] !== 'active') {
            return ['valid' => false, 'message' => 'Sponsor account is not active'];
        }

        return ['valid' => true, 'message' => ''];
    }

    private function generateAssociateId(): string
    {
        $lastId = $this->db->fetchColumn('SELECT uid FROM associates ORDER BY associate_id DESC LIMIT 1');

        if ($lastId) {
            $numericPart = intval(substr($lastId, 3)) + 1;
        } else {
            $numericPart = 1;
        }

        return sprintf('APS%06d', $numericPart);
    }

    public function logout(): void
    {
        $this->sessionManager->logout();
    }
}
