<?php
/**
 * Bank API Controller
 * For IFSC code lookup and bank autocomplete
 */

namespace App\Http\Controllers\Api;

use App\Core\Controller;

class BankController extends Controller
{
    protected $db;
    
    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }
    
    /**
     * Search banks
     * GET /api/banks/search?q=bank_name
     */
    public function search()
    {
        $search = $_GET['q'] ?? '';
        
        $sql = "SELECT id, name, short_name FROM banks WHERE is_active = 1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (name LIKE ? OR short_name LIKE ?)";
            $params = ["%$search%", "%$search%"];
        }
        
        $sql .= " ORDER BY name LIMIT 30";
        
        $banks = $this->db->fetchAll($sql, $params);
        
        $this->jsonResponse($banks);
    }
    
    /**
     * Get bank branches
     * GET /api/banks/{id}/branches
     */
    public function branches($bankId)
    {
        $search = $_GET['q'] ?? '';
        
        $sql = "SELECT id, ifsc, branch, city, district, state, pincode, address
                FROM bank_branches 
                WHERE bank_id = ? AND is_active = 1";
        $params = [intval($bankId)];
        
        if ($search) {
            $sql .= " AND (branch LIKE ? OR city LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY branch LIMIT 50";
        
        $branches = $this->db->fetchAll($sql, $params);
        
        $this->jsonResponse($branches);
    }
    
    /**
     * Lookup by IFSC Code
     * GET /api/banks/ifsc/{ifsc}
     */
    public function byIfsc($ifsc)
    {
        $ifsc = strtoupper(trim($ifsc));
        
        if (empty($ifsc) || strlen($ifsc) < 8) {
            $this->errorResponse('Valid IFSC code required (e.g., SBIN0001234)', 400);
        }
        
        $sql = "SELECT bb.ifsc, bb.branch, bb.address, bb.city, bb.district, bb.state, bb.pincode,
                       b.id as bank_id, b.name as bank_name, b.short_name as bank_short
                FROM bank_branches bb
                LEFT JOIN banks b ON bb.bank_id = b.id
                WHERE bb.ifsc = ? AND bb.is_active = 1";
        
        $result = $this->db->fetch($sql, [$ifsc]);
        
        if ($result) {
            $this->jsonResponse([
                'found' => true,
                'ifsc' => $result['ifsc'],
                'bank_name' => $result['bank_name'],
                'bank_short' => $result['bank_short'],
                'branch' => $result['branch'],
                'address' => $result['address'],
                'city' => $result['city'],
                'district' => $result['district'],
                'state' => $result['state'],
                'pincode' => $result['pincode']
            ]);
        } else {
            // Try to find partial match
            $sql2 = "SELECT b.name as bank_name FROM banks b 
                     WHERE b.is_active = 1 AND b.short_name = ?";
            $bank = $this->db->fetch($sql2, [substr($ifsc, 0, 4)]);
            
            $this->jsonResponse([
                'found' => false,
                'ifsc' => $ifsc,
                'message' => 'IFSC code not found. Please enter bank details manually.',
                'suggested_bank' => $bank ? $bank['bank_name'] : null,
                'manual_entry_required' => true
            ]);
        }
    }
    
    /**
     * Get all branches (for autocomplete)
     * GET /api/banks/branches?q=search
     */
    public function searchBranches()
    {
        $search = $_GET['q'] ?? '';
        
        if (strlen($search) < 3) {
            $this->errorResponse('Minimum 3 characters required', 400);
        }
        
        $sql = "SELECT bb.id, bb.ifsc, bb.branch, bb.city, b.name as bank_name
                FROM bank_branches bb
                LEFT JOIN banks b ON bb.bank_id = b.id
                WHERE bb.is_active = 1 AND (
                    bb.ifsc LIKE ? OR bb.branch LIKE ? OR b.name LIKE ? OR bb.city LIKE ?
                )
                ORDER BY bb.branch
                LIMIT 30";
        
        $searchParam = "%$search%";
        $branches = $this->db->fetchAll($sql, [$searchParam, $searchParam, $searchParam, $searchParam]);
        
        $this->jsonResponse($branches);
    }
    
    /**
     * Validate account number format
     * GET /api/banks/validate-account
     */
    public function validateAccount()
    {
        $account = $_GET['account'] ?? '';
        
        if (empty($account) || !is_numeric($account)) {
            $this->errorResponse('Valid account number required', 400);
        }
        
        $length = strlen($account);
        
        // Most Indian banks have 9-18 digit account numbers
        if ($length < 8 || $length > 20) {
            $this->jsonResponse([
                'valid' => false,
                'message' => 'Account number should be 8-20 digits'
            ]);
        }
        
        $this->jsonResponse([
            'valid' => true,
            'account_length' => $length,
            'message' => 'Account number format looks valid'
        ]);
    }
    
    /**
     * Helper: JSON response
     */
    private function jsonResponse($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Helper: Error response
     */
    private function errorResponse($message, $code = 400)
    {
        $this->jsonResponse(['error' => true, 'message' => $message], $code);
    }
}
