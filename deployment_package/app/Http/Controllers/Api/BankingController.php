<?php

namespace App\Http\Controllers\Api;

use \Exception;
use App\Helpers\BankingSecurity;

class BankingController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Get KYC status and details
     */
    public function getKycStatus()
    {
        try {
            $user = $this->auth->user();

            $details = $this->db->fetch("SELECT * FROM kyc_details WHERE user_id = ?", [$user->id]);
            $docs = $this->db->fetchAll("SELECT doc_type, verification_status, uploaded_at FROM kyc_documents WHERE user_id = ?", [$user->id]);

            return $this->jsonSuccess([
                'status' => $details['overall_status'] ?? 'not_submitted',
                'details' => $details ?: null,
                'documents' => $docs
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Submit KYC details
     */
    public function submitKyc()
    {
        if ($this->request()->method() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $user = $this->auth->user();

            $panNumber = $this->request()->get('pan_number');
            $aadhaarNumber = $this->request()->get('aadhaar_number');

            if (empty($panNumber) && empty($aadhaarNumber)) {
                return $this->jsonError('PAN or Aadhaar number is required', 400);
            }

            if (!empty($panNumber) && !BankingSecurity::validatePAN($panNumber)) {
                return $this->jsonError('Invalid PAN format', 400);
            }

            if (!empty($aadhaarNumber) && !BankingSecurity::validateAadhaar($aadhaarNumber)) {
                return $this->jsonError('Invalid Aadhaar format', 400);
            }

            $encryptedPan = !empty($panNumber) ? BankingSecurity::encrypt($panNumber) : null;
            $encryptedAadhaar = !empty($aadhaarNumber) ? BankingSecurity::encrypt($aadhaarNumber) : null;

            // Check if exists
            $existing = $this->db->fetch("SELECT id FROM kyc_details WHERE user_id = ?", [$user->id]);

            if ($existing) {
                $sql = "UPDATE kyc_details SET encrypted_pan_number = ?, encrypted_aadhaar_number = ?, overall_status = 'pending', updated_at = NOW() WHERE user_id = ?";
                $this->db->execute($sql, [$encryptedPan, $encryptedAadhaar, $user->id]);
            } else {
                $sql = "INSERT INTO kyc_details (user_id, encrypted_pan_number, encrypted_aadhaar_number, overall_status, created_at) VALUES (?, ?, ?, 'pending', NOW())";
                $this->db->execute($sql, [$user->id, $encryptedPan, $encryptedAadhaar]);
            }

            return $this->jsonSuccess(null, 'KYC details submitted successfully');
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get banking details
     */
    public function getBankingDetails()
    {
        try {
            $user = $this->auth->user();

            $sql = "SELECT * FROM banking_details WHERE user_id = ? ORDER BY is_primary DESC LIMIT 1";
            $row = $this->db->fetch($sql, [$user->id]);

            if ($row) {
                $row['account_number'] = BankingSecurity::decrypt($row['encrypted_account_number']);
                unset($row['encrypted_account_number']);
            }

            return $this->jsonSuccess($row);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Save banking details
     */
    public function saveBankingDetails()
    {
        if ($this->request()->method() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $user = $this->auth->user();

            $bankName = $this->request()->input('bank_name');
            $accountNumber = $this->request()->input('account_number');
            $ifscCode = $this->request()->input('ifsc_code');

            if (empty($bankName) || empty($accountNumber) || empty($ifscCode)) {
                return $this->jsonError('Missing required fields', 400);
            }

            if (!BankingSecurity::validateIFSC($ifscCode)) {
                return $this->jsonError('Invalid IFSC code format', 400);
            }

            $encryptedAccount = BankingSecurity::encrypt($accountNumber);

            $sql = "INSERT INTO banking_details (user_id, bank_name, encrypted_account_number, ifsc_code, created_at) VALUES (?, ?, ?, ?, NOW())";
            $this->db->execute($sql, [$user->id, $bankName, $encryptedAccount, \strtoupper($ifscCode)]);

            BankingSecurity::logAction($user->id, 'ADD_BANK_DETAILS', null, ['bank' => $bankName, 'ifsc' => $ifscCode]);

            return $this->jsonSuccess(null, 'Banking details saved successfully', 201);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
