<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class UserController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function profile()
    {
        // Get user profile
    }

    /**
     * Resolve a sponsor by referral code, customer ID, or phone
     * Endpoint: GET /api/user/resolve-sponsor?ref=xxx
     */
    public function resolveSponsor()
    {
        header('Content-Type: application/json');
        
        $ref = $_GET['ref'] ?? '';
        if (empty($ref)) {
            echo json_encode(['success' => false, 'message' => 'No reference provided']);
            exit;
        }

        $db = Database::getInstance()->getPdo();
        
        // Try exact match on referral_code, then customer_id, then id
        $stmt = $db->prepare("SELECT id, name, role, phone FROM users WHERE (referral_code = ? OR customer_id = ? OR id = ?) AND status = 'active' LIMIT 1");
        $stmt->execute([$ref, $ref, $ref]);
        $user = $stmt->fetch();

        if ($user) {
            // Mask phone for privacy
            $phone = $user['phone'] ?? '';
            $maskedPhone = '';
            if (strlen($phone) >= 10) {
                $maskedPhone = substr($phone, 0, 2) . '******' . substr($phone, -2);
            }
            
            echo json_encode([
                'success' => true, 
                'name' => $user['name'],
                'role' => ucfirst($user['role']),
                'masked_phone' => $maskedPhone
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Sponsor not found or inactive']);
        }
        exit;
    }

    public function update()
    {
        // Update profile
    }

    public function preferences()
    {
        // Notification preferences
    }

    public function updatePreferences()
    {
        // Update preferences
    }

    public function bankAccounts()
    {
        // Bank accounts
    }

    public function saveBankAccount()
    {
        // Save bank account
    }

    public function deleteBankAccount($id)
    {
        // Delete bank account
    }

    public function addresses()
    {
        // Addresses
    }

    public function saveAddress()
    {
        // Save address
    }

    public function deleteAddress($id)
    {
        // Delete address
    }

    public function paymentHistory()
    {
        // Payment history
    }

    public function deleteAccount()
    {
        // Delete account
    }
}