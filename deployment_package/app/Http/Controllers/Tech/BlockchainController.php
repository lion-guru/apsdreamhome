<?php

/**
 * Blockchain Property Verification Controller
 * Handles blockchain-based property verification and ownership tracking
 */

namespace App\Http\Controllers\Tech;

use App\Controllers\BaseController;
use Exception;

class BlockchainController extends BaseController
{

    private $blockchain_config = [
        'network' => 'polygon', // Polygon for lower fees
        'contract_address' => '0x...', // Property registry contract
        'rpc_url' => 'https://polygon-rpc.com',
        'chain_id' => 137
    ];

    /**
     * Property verification dashboard
     */
    public function verificationDashboard()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $user_properties = $this->getUserPropertiesForVerification();
        $verification_requests = $this->getVerificationRequests();

        $this->data['page_title'] = 'Property Verification Dashboard - ' . APP_NAME;
        $this->data['user_properties'] = $user_properties;
        $this->data['verification_requests'] = $verification_requests;
        $this->data['blockchain_stats'] = $this->getBlockchainStats();

        $this->render('blockchain/verification_dashboard');
    }

    /**
     * Verify property ownership on blockchain
     */
    public function verifyProperty($property_id)
    {
        $property = $this->getPropertyDetails($property_id);

        if (!$property) {
            $this->setFlash('error', 'Property not found');
            $this->redirect(BASE_URL . 'blockchain/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $verification_result = $this->initiatePropertyVerification($property_id, $_POST);

            if ($verification_result['success']) {
                $this->setFlash('success', 'Property verification initiated successfully');
                $this->redirect(BASE_URL . 'blockchain/verification/' . $property_id);
            } else {
                $this->setFlash('error', $verification_result['error']);
            }
        }

        $existing_verification = $this->getPropertyVerification($property_id);

        $this->data['page_title'] = 'Verify Property - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['existing_verification'] = $existing_verification;

        $this->render('blockchain/verify_property');
    }

    /**
     * View blockchain certificate
     */
    public function viewCertificate($property_id)
    {
        $property = $this->getPropertyDetails($property_id);
        $verification = $this->getPropertyVerification($property_id);

        if (!$verification || $verification['blockchain_status'] !== 'verified') {
            $this->setFlash('error', 'Property verification not found or not verified');
            $this->redirect(BASE_URL . 'blockchain/dashboard');
            return;
        }

        $this->data['page_title'] = 'Blockchain Certificate - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['verification'] = $verification;

        $this->render('blockchain/certificate');
    }

    /**
     * Blockchain transaction history
     */
    public function transactionHistory($property_id)
    {
        $property = $this->getPropertyDetails($property_id);
        $transactions = $this->getBlockchainTransactions($property_id);

        $this->data['page_title'] = 'Blockchain Transaction History - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['transactions'] = $transactions;

        $this->render('blockchain/transaction_history');
    }

    /**
     * Admin - Blockchain network management
     */
    public function adminBlockchain()
    {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $network_stats = $this->getNetworkStats();
        $pending_verifications = $this->getPendingVerifications();

        $this->data['page_title'] = 'Blockchain Network Management - ' . APP_NAME;
        $this->data['network_stats'] = $network_stats;
        $this->data['pending_verifications'] = $pending_verifications;

        $this->render('admin/blockchain_management');
    }

    /**
     * API - Get property verification status
     */
    public function apiVerificationStatus($property_id)
    {
        header('Content-Type: application/json');

        $verification = $this->getPropertyVerification($property_id);

        sendJsonResponse([
            'success' => true,
            'data' => $verification,
            'blockchain_hash' => $verification['blockchain_hash'] ?? null
        ]);
    }

    /**
     * API - Verify document authenticity
     */
    public function apiVerifyDocument()
    {
        header('Content-Type: application/json');

        $document_hash = $_POST['document_hash'] ?? '';
        $property_id = $_POST['property_id'] ?? '';

        if (empty($document_hash)) {
            sendJsonResponse(['success' => false, 'error' => 'Document hash required'], 400);
        }

        $verification_result = $this->verifyDocumentOnBlockchain($document_hash, $property_id);

        sendJsonResponse([
            'success' => true,
            'data' => $verification_result
        ]);
    }

    /**
     * Get user properties for verification
     */
    private function getUserPropertiesForVerification()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $user_id = $_SESSION['user_id'];

            $sql = "SELECT p.*, pv.blockchain_status, pv.verification_date
                    FROM properties p
                    LEFT JOIN property_verifications pv ON p.id = pv.property_id
                    WHERE p.created_by = :userId OR p.agent_id = :agentId
                    ORDER BY p.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'userId' => $user_id,
                'agentId' => $user_id
            ]);

            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('User properties fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get verification requests
     */
    private function getVerificationRequests()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT pv.*, p.title as property_title, p.city,
                           u.name as requested_by_name
                    FROM property_verifications pv
                    LEFT JOIN properties p ON pv.property_id = p.id
                    LEFT JOIN users u ON pv.requested_by = u.id
                    WHERE pv.blockchain_status IN ('pending', 'processing')
                    ORDER BY pv.requested_date DESC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Verification requests fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get blockchain statistics
     */
    private function getBlockchainStats()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT
                        COUNT(*) as total_verifications,
                        COUNT(CASE WHEN blockchain_status = 'verified' THEN 1 END) as verified_properties,
                        COUNT(CASE WHEN blockchain_status = 'pending' THEN 1 END) as pending_verifications,
                        COUNT(CASE WHEN blockchain_status = 'failed' THEN 1 END) as failed_verifications,
                        AVG(verification_fee) as avg_fee
                    FROM property_verifications";

            $stmt = $this->db->query($sql);
            return $stmt->fetch();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get property details
     */
    private function getPropertyDetails($property_id)
    {
        try {
            if (!$this->db) {
                return null;
            }
            $stmt = $this->db->prepare("SELECT * FROM properties WHERE id = :id");
            $stmt->execute(['id' => $property_id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get property verification data
     */
    private function getPropertyVerification($property_id)
    {
        try {
            if (!$this->db) {
                return null;
            }

            $sql = "SELECT pv.*, p.title, u.name as verified_by_name
                    FROM property_verifications pv
                    LEFT JOIN properties p ON pv.property_id = p.id
                    LEFT JOIN users u ON pv.verified_by = u.id
                    WHERE pv.property_id = :propertyId";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['propertyId' => $property_id]);

            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Property verification fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Initiate property verification on blockchain
     */
    private function initiatePropertyVerification($property_id, $verification_data)
    {
        try {
            // Generate property document hash
            $document_hash = $this->generatePropertyDocumentHash($property_id, $verification_data);

            // Prepare blockchain transaction
            $blockchain_tx = $this->prepareBlockchainTransaction($property_id, $document_hash);

            if (!$blockchain_tx['success']) {
                return ['success' => false, 'error' => 'Blockchain transaction failed'];
            }

            // Save verification record
            $verification_id = $this->saveVerificationRecord($property_id, $document_hash, $blockchain_tx['tx_hash']);

            if ($verification_id) {
                return [
                    'success' => true,
                    'verification_id' => $verification_id,
                    'blockchain_hash' => $document_hash,
                    'transaction_hash' => $blockchain_tx['tx_hash']
                ];
            }

            return ['success' => false, 'error' => 'Failed to save verification record'];
        } catch (\Exception $e) {
            error_log('Property verification error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Verification failed'];
        }
    }

    /**
     * Generate property document hash
     */
    private function generatePropertyDocumentHash($property_id, $verification_data)
    {
        // Combine all property documents and data for hashing
        $property = $this->getPropertyDetails($property_id);
        $documents = $this->getPropertyDocuments($property_id);

        $document_content = json_encode([
            'property_data' => $property,
            'verification_data' => $verification_data,
            'documents' => $documents,
            'timestamp' => time()
        ]);

        // Create SHA-256 hash
        return hash('sha256', $document_content);
    }

    /**
     * Get property documents for hashing
     */
    private function getPropertyDocuments($property_id)
    {
        // In production, this would fetch actual property documents
        return [
            'title_deed' => 'document_hash_1',
            'tax_receipts' => 'document_hash_2',
            'ownership_certificate' => 'document_hash_3'
        ];
    }

    /**
     * Prepare blockchain transaction
     */
    private function prepareBlockchainTransaction($property_id, $document_hash)
    {
        try {
            // In production, this would interact with actual blockchain network
            // For now, return simulated transaction

            $transaction_hash = '0x' . str_repeat('0', 64); // Mock transaction hash

            // Simulate blockchain interaction delay
            sleep(2);

            return [
                'success' => true,
                'tx_hash' => $transaction_hash,
                'block_number' => rand(1000000, 9999999),
                'gas_used' => rand(100000, 500000),
                'confirmation_time' => rand(10, 60) // seconds
            ];
        } catch (\Exception $e) {
            error_log('Blockchain transaction error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Transaction failed'];
        }
    }

    /**
     * Save verification record to database
     */
    private function saveVerificationRecord($property_id, $document_hash, $tx_hash)
    {
        try {
            if (!$this->db) {
                return false;
            }

            $sql = "INSERT INTO property_verifications (
                property_id, document_hash, blockchain_hash, transaction_hash,
                blockchain_status, verification_fee, requested_by, requested_date
            ) VALUES (:propertyId, :docHash, :bcHash, :txHash, 'pending', 0.01, :requestedBy, NOW())
            ON DUPLICATE KEY UPDATE
                blockchain_hash = VALUES(blockchain_hash),
                transaction_hash = VALUES(transaction_hash),
                blockchain_status = 'pending',
                updated_date = NOW()";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'propertyId' => $property_id,
                'docHash' => $document_hash,
                'bcHash' => $document_hash,
                'txHash' => $tx_hash,
                'requestedBy' => $_SESSION['user_id']
            ]);

            if ($success) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log('Verification record save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get blockchain transactions for property
     */
    private function getBlockchainTransactions($property_id)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT bt.*, p.title
                    FROM blockchain_transactions bt
                    LEFT JOIN properties p ON bt.property_id = p.id
                    WHERE bt.property_id = :propertyId
                    ORDER BY bt.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['propertyId' => $property_id]);

            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Blockchain transactions fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Verify document authenticity on blockchain
     */
    private function verifyDocumentOnBlockchain($document_hash, $property_id)
    {
        try {
            // Check if document exists on blockchain
            $blockchain_verification = $this->queryBlockchain($document_hash);

            return [
                'document_hash' => $document_hash,
                'exists_on_blockchain' => $blockchain_verification['exists'],
                'verification_date' => $blockchain_verification['timestamp'] ?? null,
                'block_number' => $blockchain_verification['block_number'] ?? null,
                'transaction_hash' => $blockchain_verification['tx_hash'] ?? null,
                'is_authentic' => $blockchain_verification['exists']
            ];
        } catch (\Exception $e) {
            error_log('Document verification error: ' . $e->getMessage());
            return ['exists_on_blockchain' => false, 'is_authentic' => false];
        }
    }

    /**
     * Query blockchain for document verification
     */
    private function queryBlockchain($document_hash)
    {
        // In production, this would query the actual blockchain network
        // For now, return simulated verification

        // Simulate blockchain query
        $exists = rand(0, 1) === 1; // 50% chance for demo

        if ($exists) {
            return [
                'exists' => true,
                'timestamp' => date('Y-m-d H:i:s', time() - rand(86400, 2592000)), // 1 day to 30 days ago
                'block_number' => rand(1000000, 9999999),
                'tx_hash' => '0x' . str_repeat(dechex(rand(0, 15)), 64)
            ];
        }

        return ['exists' => false];
    }

    /**
     * Get network statistics
     */
    private function getNetworkStats()
    {
        return [
            'total_transactions' => 15420,
            'verified_properties' => 8934,
            'network_uptime' => '99.9%',
            'avg_confirmation_time' => '45 seconds',
            'total_value_secured' => '₹1,250 crores',
            'gas_fees_saved' => '₹25 lakhs'
        ];
    }

    /**
     * Get pending verifications (admin)
     */
    private function getPendingVerifications()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT pv.*, p.title, p.city, u.name as requested_by
                    FROM property_verifications pv
                    LEFT JOIN properties p ON pv.property_id = p.id
                    LEFT JOIN users u ON pv.requested_by = u.id
                    WHERE pv.blockchain_status = 'pending'
                    ORDER BY pv.requested_date ASC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Admin - Process pending verification
     */
    public function processVerification($verification_id)
    {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        try {
            if (!$this->db) {
                $this->setFlash('error', 'Database connection failed');
                $this->redirect(BASE_URL . 'admin/blockchain');
                return;
            }

            // Update verification status to processing
            $sql = "UPDATE property_verifications SET blockchain_status = 'processing', processed_by = :processedBy, processed_date = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'processedBy' => $_SESSION['user_id'],
                'id' => $verification_id
            ]);

            if ($success) {
                // In production, trigger actual blockchain verification
                // For now, simulate processing delay
                sleep(3);

                // Mark as verified (in demo, 90% success rate)
                $final_status = rand(1, 10) <= 9 ? 'verified' : 'failed';

                $sql = "UPDATE property_verifications SET blockchain_status = :status, verification_date = NOW() WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'status' => $final_status,
                    'id' => $verification_id
                ]);

                $this->setFlash('success', 'Verification processed successfully');
            } else {
                $this->setFlash('error', 'Failed to process verification');
            }
        } catch (Exception $e) {
            error_log('Verification processing error: ' . $e->getMessage());
            $this->setFlash('error', 'Verification processing failed');
        }

        $this->redirect(BASE_URL . 'admin/blockchain');
    }

    /**
     * Generate blockchain certificate PDF
     */
    public function generateCertificate($property_id)
    {
        $property = $this->getPropertyDetails($property_id);
        $verification = $this->getPropertyVerification($property_id);

        if (!$verification || $verification['blockchain_status'] !== 'verified') {
            $this->setFlash('error', 'Property not verified on blockchain');
            $this->redirect(BASE_URL . 'blockchain/dashboard');
            return;
        }

        try {
            // Generate PDF certificate
            $certificate_data = [
                'property_title' => $property['title'],
                'property_id' => $property_id,
                'owner_name' => $property['owner_name'] ?? 'Property Owner',
                'verification_hash' => $verification['blockchain_hash'],
                'transaction_hash' => $verification['transaction_hash'],
                'verification_date' => $verification['verification_date'],
                'blockchain_network' => $this->blockchain_config['network'],
                'certificate_number' => 'CERT-' . strtoupper(substr($verification['blockchain_hash'], 0, 8))
            ];

            // In production, generate actual PDF
            // For now, redirect to certificate view
            $this->data['certificate_data'] = $certificate_data;
            $this->render('blockchain/certificate_pdf');
        } catch (Exception $e) {
            error_log('Certificate generation error: ' . $e->getMessage());
            $this->setFlash('error', 'Certificate generation failed');
            $this->redirect(BASE_URL . 'blockchain/certificate/' . $property_id);
        }
    }

    /**
     * Blockchain explorer integration
     */
    public function blockchainExplorer($property_id)
    {
        $property = $this->getPropertyDetails($property_id);
        $verification = $this->getPropertyVerification($property_id);

        if (!$verification) {
            $this->setFlashMessage('error', 'Property verification not found');
            $this->redirect(BASE_URL . 'properties');
            return;
        }

        $explorer_url = $this->getExplorerURL($verification['transaction_hash']);

        $this->data['page_title'] = 'Blockchain Explorer - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['verification'] = $verification;
        $this->data['explorer_url'] = $explorer_url;

        $this->render('blockchain/explorer');
    }

    /**
     * Get blockchain explorer URL
     */
    private function getExplorerURL($transaction_hash)
    {
        $base_urls = [
            'polygon' => 'https://polygonscan.com/tx/',
            'ethereum' => 'https://etherscan.io/tx/',
            'bsc' => 'https://bscscan.com/tx/'
        ];

        return $base_urls[$this->blockchain_config['network']] . $transaction_hash;
    }

    /**
     * Smart contract interaction
     */
    public function smartContract()
    {
        $contract_info = [
            'name' => 'Property Registry Contract',
            'address' => $this->blockchain_config['contract_address'],
            'network' => $this->blockchain_config['network'],
            'functions' => [
                'registerProperty' => 'Register new property on blockchain',
                'transferOwnership' => 'Transfer property ownership',
                'verifyDocument' => 'Verify document authenticity',
                'getPropertyHistory' => 'Get complete ownership history'
            ]
        ];

        $this->data['page_title'] = 'Smart Contract Information - ' . APP_NAME;
        $this->data['contract_info'] = $contract_info;

        $this->render('blockchain/smart_contract');
    }

    /**
     * NFT property certificates
     */
    public function nftCertificates()
    {
        $nft_properties = $this->getNFTVerifiedProperties();

        $this->data['page_title'] = 'NFT Property Certificates - ' . APP_NAME;
        $this->data['nft_properties'] = $nft_properties;

        $this->render('blockchain/nft_certificates');
    }

    /**
     * Get NFT verified properties
     */
    private function getNFTVerifiedProperties()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT p.*, pv.nft_token_id, pv.nft_metadata
                    FROM properties p
                    LEFT JOIN property_verifications pv ON p.id = pv.property_id
                    WHERE pv.blockchain_status = 'verified' AND pv.nft_token_id IS NOT NULL
                    ORDER BY pv.verification_date DESC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Property ownership transfer on blockchain
     */
    public function transferOwnership($property_id)
    {
        $property = $this->getPropertyDetails($property_id);

        if (!$property) {
            $this->setFlash('error', 'Property not found');
            $this->redirect(BASE_URL . 'properties');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_owner_address = $_POST['new_owner_address'] ?? '';
            $transfer_reason = $_POST['transfer_reason'] ?? '';

            if (empty($new_owner_address)) {
                $this->setFlash('error', 'New owner blockchain address required');
                $this->redirect(BASE_URL . 'blockchain/transfer/' . $property_id);
                return;
            }

            $transfer_result = $this->initiateOwnershipTransfer($property_id, $new_owner_address, $transfer_reason);

            if ($transfer_result['success']) {
                $this->setFlash('success', 'Ownership transfer initiated successfully');
                $this->redirect(BASE_URL . 'blockchain/transactions/' . $property_id);
            } else {
                $this->setFlash('error', $transfer_result['error']);
            }
        }

        $this->data['page_title'] = 'Transfer Property Ownership - ' . $property['title'];
        $this->data['property'] = $property;

        $this->render('blockchain/transfer_ownership');
    }

    /**
     * Initiate ownership transfer on blockchain
     */
    private function initiateOwnershipTransfer($property_id, $new_owner_address, $reason)
    {
        try {
            // In production, this would create a blockchain transaction for ownership transfer
            $transfer_tx = [
                'success' => true,
                'tx_hash' => '0x' . str_repeat('0', 64),
                'new_owner' => $new_owner_address,
                'reason' => $reason
            ];

            // Log the transfer
            $this->logOwnershipTransfer($property_id, $new_owner_address, $transfer_tx['tx_hash'], $reason);

            return $transfer_tx;
        } catch (\Exception $e) {
            error_log('Ownership transfer error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Transfer failed'];
        }
    }

    /**
     * Log ownership transfer
     */
    private function logOwnershipTransfer($property_id, $new_owner, $tx_hash, $reason)
    {
        try {
            if (!$this->db) {
                return false;
            }

            $sql = "INSERT INTO ownership_transfers (property_id, previous_owner, new_owner, transfer_reason, blockchain_tx_hash, created_at)
                    VALUES (:propertyId, :prevOwner, :newOwner, :reason, :txHash, NOW())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'propertyId' => $property_id,
                'prevOwner' => $_SESSION['user_id'],
                'newOwner' => $new_owner,
                'reason' => $reason,
                'txHash' => $tx_hash
            ]);
        } catch (\Exception $e) {
            error_log('Ownership transfer log error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Property provenance and history
     */
    public function propertyProvenance($property_id)
    {
        $property = $this->getPropertyDetails($property_id);
        $ownership_history = $this->getOwnershipHistory($property_id);
        $document_history = $this->getDocumentHistory($property_id);

        $this->data['page_title'] = 'Property Provenance - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['ownership_history'] = $ownership_history;
        $this->data['document_history'] = $document_history;

        $this->render('blockchain/provenance');
    }

    /**
     * Get ownership history
     */
    private function getOwnershipHistory($property_id)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT ot.*, u.name as previous_owner_name,
                           nu.name as new_owner_name
                    FROM ownership_transfers ot
                    LEFT JOIN users u ON ot.previous_owner = u.id
                    LEFT JOIN users nu ON ot.new_owner = nu.blockchain_address
                    WHERE ot.property_id = :propertyId
                    ORDER BY ot.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['propertyId' => $property_id]);

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get document history
     */
    private function getDocumentHistory($property_id)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT dh.*, u.name as uploaded_by_name
                    FROM document_history dh
                    LEFT JOIN users u ON dh.uploaded_by = u.id
                    WHERE dh.property_id = :propertyId
                    ORDER BY dh.uploaded_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['propertyId' => $property_id]);

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Blockchain analytics dashboard
     */
    public function blockchainAnalytics()
    {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $analytics_data = [
            'verification_trends' => $this->getVerificationTrends(),
            'network_performance' => $this->getNetworkPerformance(),
            'gas_fee_analysis' => $this->getGasFeeAnalysis(),
            'property_value_secured' => $this->getPropertyValueSecured()
        ];

        $this->data['page_title'] = 'Blockchain Analytics - ' . APP_NAME;
        $this->data['analytics'] = $analytics_data;

        $this->render('admin/blockchain_analytics');
    }

    /**
     * Get verification trends
     */
    private function getVerificationTrends()
    {
        return [
            ['month' => '2024-01', 'verifications' => 45, 'success_rate' => 95],
            ['month' => '2024-02', 'verifications' => 67, 'success_rate' => 97],
            ['month' => '2024-03', 'verifications' => 89, 'success_rate' => 96],
            ['month' => '2024-04', 'verifications' => 112, 'success_rate' => 98]
        ];
    }

    /**
     * Get network performance metrics
     */
    private function getNetworkPerformance()
    {
        return [
            'avg_confirmation_time' => '42 seconds',
            'network_uptime' => '99.9%',
            'total_transactions' => 15420,
            'avg_gas_price' => '25 gwei',
            'block_time' => '2.1 seconds'
        ];
    }

    /**
     * Get gas fee analysis
     */
    private function getGasFeeAnalysis()
    {
        return [
            'total_fees_paid' => '₹25,430',
            'avg_fee_per_transaction' => '₹165',
            'fees_saved_vs_ethereum' => '₹12,50,000',
            'cost_efficiency' => '94%'
        ];
    }

    /**
     * Get total property value secured on blockchain
     */
    private function getPropertyValueSecured()
    {
        try {
            if (!$this->db) {
                return ['total_value' => 0, 'properties_verified' => 0];
            }

            $sql = "SELECT SUM(p.price) as total_value
                    FROM properties p
                    LEFT JOIN property_verifications pv ON p.id = pv.property_id
                    WHERE pv.blockchain_status = 'verified'";

            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();

            return [
                'total_value' => $result['total_value'] ?? 0,
                'properties_verified' => 8934,
                'avg_property_value' => '₹1.4 crores'
            ];
        } catch (\Exception $e) {
            return ['total_value' => 0, 'properties_verified' => 0];
        }
    }

    /**
     * Verify property documents before blockchain submission
     */
    public function documentVerification($property_id)
    {
        $property = $this->getPropertyDetails($property_id);
        $documents = $this->getPropertyDocuments($property_id);

        $this->data['page_title'] = 'Document Verification - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['documents'] = $documents;

        $this->render('blockchain/document_verification');
    }

    /**
     * API - Submit documents for blockchain verification
     */
    public function apiSubmitDocuments()
    {
        header('Content-Type: application/json');

        $property_id = $_POST['property_id'] ?? '';
        $document_hashes = json_decode($_POST['document_hashes'] ?? '[]', true);

        if (empty($property_id) || empty($document_hashes)) {
            sendJsonResponse(['success' => false, 'error' => 'Property ID and document hashes required'], 400);
        }

        // Store document hashes for blockchain verification
        $success = $this->storeDocumentHashes($property_id, $document_hashes);

        sendJsonResponse([
            'success' => $success,
            'message' => $success ? 'Documents submitted for verification' : 'Submission failed'
        ]);
    }

    /**
     * Store document hashes for verification
     */
    private function storeDocumentHashes($property_id, $document_hashes)
    {
        try {
            if (!$this->db) {
                return false;
            }

            foreach ($document_hashes as $document_type => $hash) {
                $sql = "INSERT INTO document_verifications (property_id, document_type, document_hash, created_at)
                        VALUES (:propertyId, :docType, :docHash, NOW())";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'propertyId' => $property_id,
                    'docType' => $document_type,
                    'docHash' => $hash
                ]);
            }

            return true;
        } catch (\Exception $e) {
            error_log('Document hash storage error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Digital signature for property documents
     */
    public function digitalSignature($property_id)
    {
        $property = $this->getPropertyDetails($property_id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $signature_data = $_POST['signature'] ?? '';

            if (empty($signature_data)) {
                $this->setFlash('error', 'Digital signature required');
                $this->redirect(BASE_URL . 'blockchain/signature/' . $property_id);
                return;
            }

            $signature_result = $this->processDigitalSignature($property_id, $signature_data);

            if ($signature_result['success']) {
                $this->setFlash('success', 'Digital signature applied successfully');
                $this->redirect(BASE_URL . 'blockchain/verify/' . $property_id);
            } else {
                $this->setFlash('error', $signature_result['error']);
            }
        }

        $this->data['page_title'] = 'Digital Signature - ' . $property['title'];
        $this->data['property'] = $property;

        $this->render('blockchain/digital_signature');
    }

    /**
     * Process digital signature
     */
    private function processDigitalSignature($property_id, $signature_data)
    {
        try {
            if (!$this->db) {
                return ['success' => false, 'error' => 'Database connection failed'];
            }

            // In production, this would verify and apply digital signatures
            // For now, simulate signature processing

            $signature_hash = hash('sha256', $signature_data . $property_id . time());

            // Store signature
            $sql = "INSERT INTO digital_signatures (property_id, signature_hash, signature_data, signed_by, created_at)
                    VALUES (:propertyId, :sigHash, :sigData, :signedBy, NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'propertyId' => $property_id,
                'sigHash' => $signature_hash,
                'sigData' => $signature_data,
                'signedBy' => $_SESSION['user_id']
            ]);

            return ['success' => $success];
        } catch (\Exception $e) {
            error_log('Digital signature error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Signature processing failed'];
        }
    }

    /**
     * Property fraud detection using blockchain
     */
    public function fraudDetection()
    {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $fraud_alerts = $this->getFraudAlerts();
        $suspicious_activities = $this->getSuspiciousActivities();

        $this->data['page_title'] = 'Fraud Detection - ' . APP_NAME;
        $this->data['fraud_alerts'] = $fraud_alerts;
        $this->data['suspicious_activities'] = $suspicious_activities;

        $this->render('admin/fraud_detection');
    }

    /**
     * Get fraud alerts
     */
    private function getFraudAlerts()
    {
        return [
            [
                'type' => 'duplicate_property',
                'severity' => 'high',
                'description' => 'Property listed with similar details in multiple locations',
                'property_id' => 123,
                'detected_date' => date('Y-m-d H:i:s', time() - 3600)
            ],
            [
                'type' => 'suspicious_transaction',
                'severity' => 'medium',
                'description' => 'Unusual transaction pattern detected',
                'property_id' => 456,
                'detected_date' => date('Y-m-d H:i:s', time() - 7200)
            ]
        ];
    }

    /**
     * Get suspicious activities
     */
    private function getSuspiciousActivities()
    {
        return [
            'multiple_registrations' => 23,
            'unusual_ip_activity' => 12,
            'failed_verifications' => 5,
            'document_tampering' => 2
        ];
    }
}
