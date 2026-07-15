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