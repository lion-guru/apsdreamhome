<?php

namespace App\Http\Controllers\Backup;

use App\Services\Backup\BackupIntegrityService;
use Psr\Log\LoggerInterface;

class BackupIntegrityController
{
    private BackupIntegrityService $backupIntegrityService;
    private LoggerInterface $logger;

    public function __construct(BackupIntegrityService $backupIntegrityService, LoggerInterface $logger)
    {
        $this->backupIntegrityService = $backupIntegrityService;
        $this->logger = $logger;
    }

    /**
     * Verify backup integrity
     */
    public function verify()
    {
        try {
            $backupFile = request()->input('backup_file');

            if (!$backupFile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file parameter is required'
                ], 400);
            }

            $result = $this->backupIntegrityService->verifyBackupIntegrity($backupFile);

            return response()->json([
                'success' => true,
                'verification' => $result
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to verify backup integrity", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify backup integrity'
            ], 500);
        }
    }

    /**
     * Get verification history
     */
    public function getHistory()
    {
        try {
            $limit = request()->input('limit', 50);
            $history = $this->backupIntegrityService->getVerificationHistory((int)$limit);

            return response()->json([
                'success' => true,
                'history' => $history,
                'total' => count($history)
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get verification history", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get verification history'
            ], 500);
        }
    }

    /**
     * Get backup statistics
     */
    public function getStatistics()
    {
        try {
            $stats = $this->backupIntegrityService->getBackupStatistics();

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get backup statistics", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get backup statistics'
            ], 500);
        }
    }

    /**
     * Schedule backup verification
     */
    public function schedule()
    {
        try {
            $backupFile = request()->input('backup_file');
            $scheduleTime = request()->input('schedule_time');

            if (!$backupFile || !$scheduleTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file and schedule time are required'
                ], 400);
            }

            if ($this->backupIntegrityService->scheduleVerification($backupFile, $scheduleTime)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup verification scheduled successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to schedule backup verification'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to schedule backup verification", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule backup verification'
            ], 500);
        }
    }

    /**
     * Get scheduled verifications
     */
    public function getScheduled()
    {
        try {
            $scheduled = $this->backupIntegrityService->getScheduledVerifications();

            return response()->json([
                'success' => true,
                'scheduled' => $scheduled,
                'total' => count($scheduled)
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get scheduled verifications", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get scheduled verifications'
            ], 500);
        }
    }

    /**
     * Export verification report
     */
    public function export()
    {
        try {
            $filters = request()->only(['status', 'date_from', 'date_to']);
            $filename = $this->backupIntegrityService->exportVerificationReport($filters);

            return response()->json([
                'success' => true,
                'message' => 'Verification report exported successfully',
                'filename' => $filename
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to export verification report", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to export verification report'
            ], 500);
        }
    }

    /**
     * Clean old verification records
     */
    public function cleanup()
    {
        try {
            $daysToKeep = request()->input('days_to_keep', 30);
            $deleted = $this->backupIntegrityService->cleanupOldRecords((int)$daysToKeep);

            return response()->json([
                'success' => true,
                'message' => 'Old verification records cleaned up successfully',
                'deleted_count' => $deleted
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to cleanup old records", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup old records'
            ], 500);
        }
    }

    /**
     * Backup integrity management page
     */
    public function management()
    {
        try {
            $stats = $this->backupIntegrityService->getBackupStatistics();
            $history = $this->backupIntegrityService->getVerificationHistory(10);
            $scheduled = $this->backupIntegrityService->getScheduledVerifications();
            
            return view('backup.integrity_management', [
                'stats' => $stats,
                'history' => $history,
                'scheduled' => $scheduled,
                'page_title' => 'Backup Integrity Management - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load backup integrity management", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }

    /**
     * Upload and verify backup
     */
    public function uploadAndVerify()
    {
        try {
            if (!isset($_FILES['backup_file'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No backup file uploaded'
                ], 400);
            }

            $file = $_FILES['backup_file'];
            
            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return response()->json([
                    'success' => false,
                    'message' => 'File upload error: ' . $file['error']
                ], 400);
            }

            // Check file size
            if ($file['size'] < 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'File is too small to be a valid backup'
                ], 400);
            }

            // Move uploaded file to backup directory
            $backupPath = 'storage/backups/uploads/';
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '_' . basename($file['name']);
            $targetPath = $backupPath . $filename;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to move uploaded file'
                ], 500);
            }

            // Verify the uploaded backup
            $verification = $this->backupIntegrityService->verifyBackupIntegrity($targetPath);

            return response()->json([
                'success' => true,
                'message' => 'Backup uploaded and verified successfully',
                'filename' => $filename,
                'verification' => $verification
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to upload and verify backup", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload and verify backup'
            ], 500);
        }
    }

    /**
     * Get backup verification details
     */
    public function getDetails()
    {
        try {
            $verificationId = request()->input('verification_id');

            if (!$verificationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification ID is required'
                ], 400);
            }

            // Get verification details from database
            $verification = $this->db->fetchOne(
                "SELECT * FROM backup_integrity_checks WHERE id = ?",
                [$verificationId]
            );

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification not found'
                ], 404);
            }

            $details = [
                'id' => $verification['id'],
                'backup_file' => $verification['backup_file'],
                'checksum' => $verification['checksum'],
                'file_size' => $verification['file_size'],
                'verification_status' => $verification['verification_status'],
                'verification_details' => json_decode($verification['verification_details'], true) ?: [],
                'verification_time' => $verification['verification_time']
            ];

            return response()->json([
                'success' => true,
                'details' => $details
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get verification details", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get verification details'
            ], 500);
        }
    }

    /**
     * Re-run verification
     */
    public function reverify()
    {
        try {
            $verificationId = request()->input('verification_id');

            if (!$verificationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification ID is required'
                ], 400);
            }

            // Get original verification details
            $original = $this->db->fetchOne(
                "SELECT backup_file FROM backup_integrity_checks WHERE id = ?",
                [$verificationId]
            );

            if (!$original) {
                return response()->json([
                    'success' => false,
                    'message' => 'Original verification not found'
                ], 404);
            }

            // Re-run verification
            $newVerification = $this->backupIntegrityService->verifyBackupIntegrity($original['backup_file']);

            return response()->json([
                'success' => true,
                'message' => 'Verification re-run successfully',
                'verification' => $newVerification
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to re-run verification", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to re-run verification'
            ], 500);
        }
    }

    /**
     * Delete verification record
     */
    public function delete()
    {
        try {
            $verificationId = request()->input('verification_id');

            if (!$verificationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification ID is required'
                ], 400);
            }

            $deleted = $this->db->execute(
                "DELETE FROM backup_integrity_checks WHERE id = ?",
                [$verificationId]
            );

            if ($deleted > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Verification record deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification record not found'
                ], 404);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to delete verification record", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete verification record'
            ], 500);
        }
    }

    /**
     * Get backup integrity dashboard
     */
    public function dashboard()
    {
        try {
            $stats = $this->backupIntegrityService->getBackupStatistics();
            $recentVerifications = $this->backupIntegrityService->getVerificationHistory(5);
            $scheduledVerifications = $this->backupIntegrityService->getScheduledVerifications();

            return response()->json([
                'success' => true,
                'dashboard' => [
                    'stats' => $stats,
                    'recent_verifications' => $recentVerifications,
                    'scheduled_verifications' => $scheduledVerifications,
                    'last_updated' => date('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get backup integrity dashboard", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get backup integrity dashboard'
            ], 500);
        }
    }
}
