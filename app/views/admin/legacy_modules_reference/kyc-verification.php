<?php
require_once __DIR__ . '/core/init.php';

// Admin authentication check - handled by init.php, but we require super_admin for KYC
if (!isAuthenticated() || !isAdmin() || getAuthSubRole() !== 'super_admin') {
    header("Location: index.php");
    exit();
}

// Handle KYC verification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => 'Invalid request'];

    try {
        // Validate CSRF
        if (!validateCsrfToken()) {
            throw new Exception('Invalid security token. Please refresh the page.');
        }

        $action = $_POST['action'] ?? '';
        $kyc_id = $_POST['kyc_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $notes = $_POST['verification_notes'] ?? '';

        // Validate inputs
        if (!$kyc_id || !$status || !in_array($status, ['Verified', 'Rejected'])) {
            throw new InvalidArgumentException('Invalid verification parameters');
        }

        $db = \App\Core\App::database();

        // Prepare update statement
        $update_query = "UPDATE kyc_verification 
            SET status = ?, 
                verified_by = ?, 
                verification_notes = ?, 
                verified_at = NOW() 
            WHERE id = ?";
        
        if ($db->update($update_query, [$status, $_SESSION['admin_id'], $notes, $kyc_id])) {
            // Fetch associate details for notification
            $notify_query = "
                SELECT kv.associate_id, u.uname, u.uemail, u.uphone, kv.status 
                FROM kyc_verification kv
                JOIN user u ON kv.associate_id = u.uid
                WHERE kv.id = ?
            ";
            $notify_result = $db->fetch($notify_query, [$kyc_id]);

            // Send notification (SMS/Email)
            if ($notify_result) {
                require_once(__DIR__ . "/../includes/notification_manager.php");
                require_once(__DIR__ . "/../includes/email_service.php");
                
                $emailService = new EmailService();
                $notificationManager = new NotificationManager($db->getConnection(), $emailService);

                $status_message = $status === 'Verified' 
                    ? "Your KYC documents have been verified successfully." 
                    : "Your KYC documents were rejected. Please review and resubmit.";
                
                $notificationManager->send([
                    'user_id' => $notify_result['associate_id'] ?? 0,
                    'email' => $notify_result['uemail'],
                    'phone' => $notify_result['uphone'],
                    'template' => 'KYC_STATUS',
                    'data' => [
                        'status_message' => $status_message,
                        'notes' => $notes ?: 'No specific notes provided.'
                    ],
                    'channels' => ['db', 'email', 'sms']
                ]);
            }

            $response = [
                'status' => 'success', 
                'message' => "KYC verification {$status} successfully"
            ];
        } else {
            $response = [
                'status' => 'error', 
                'message' => 'Failed to update KYC status'
            ];
        }
    } catch (Exception $e) {
        error_log("KYC Verification Error: " . $e->getMessage());
        $response = [
            'status' => 'error', 
            'message' => $e->getMessage()
        ];
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Fetch pending KYC verifications
$kyc_query = "
    SELECT 
        kv.id, 
        kv.associate_id, 
        u.uname, 
        u.uemail, 
        u.uphone,
        kv.aadhar_doc, 
        kv.pan_doc, 
        kv.address_doc, 
        kv.submitted_at
    FROM kyc_verification kv
    JOIN user u ON kv.associate_id = u.uid
    WHERE kv.status = 'Pending'
    ORDER BY kv.submitted_at ASC
";

$db = \App\Core\App::database();
$pending_kyc_list = $db->fetchAll($kyc_query) ?: [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KYC Verification | Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .kyc-document-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .kyc-document-preview:hover {
            transform: scale(1.1);
        }
        .document-modal-content {
            max-width: 80vw;
            max-height: 90vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4">KYC Verification</h1>
        
        <?php if (empty($pending_kyc_list)): ?>
            <div class="alert alert-info">
                No pending KYC verifications at the moment.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($pending_kyc_list as $kyc): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><?php echo h($kyc['uname']); ?></h5>
                                <small class="text-muted"><?php echo h($kyc['uemail']); ?></small>
                            </div>
                            <div class="card-body">
                                <div class="documents-preview">
                                    <div class="row">
                                        <div class="col-4">
                                            <img src="../uploads/kyc/<?php echo $kyc['associate_id'] . '/' . h($kyc['aadhar_doc']); ?>" 
                                                 class="kyc-document-preview" 
                                                 data-document-type="Aadhar Card"
                                                 data-document-src="../uploads/kyc/<?php echo $kyc['associate_id'] . '/' . h($kyc['aadhar_doc']); ?>">
                                        </div>
                                        <div class="col-4">
                                            <img src="../uploads/kyc/<?php echo $kyc['associate_id'] . '/' . h($kyc['pan_doc']); ?>" 
                                                 class="kyc-document-preview" 
                                                 data-document-type="PAN Card"
                                                 data-document-src="../uploads/kyc/<?php echo $kyc['associate_id'] . '/' . h($kyc['pan_doc']); ?>">
                                        </div>
                                        <div class="col-4">
                                            <img src="../uploads/kyc/<?php echo $kyc['associate_id'] . '/' . h($kyc['address_doc']); ?>" 
                                                 class="kyc-document-preview" 
                                                 data-document-type="Address Proof"
                                                 data-document-src="../uploads/kyc/<?php echo $kyc['associate_id'] . '/' . h($kyc['address_doc']); ?>">
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="verification-actions">
                                    <form class="kyc-verification-form">
                                        <?php echo getCsrfField(); ?>
                                        <input type="hidden" name="kyc_id" value="<?php echo $kyc['id']; ?>">
                                        <textarea name="verification_notes" class="form-control mb-2" placeholder="Verification notes (optional)"></textarea>
                                        <div class="d-flex justify-content-between">
                                            <button type="submit" name="status" value="Verified" class="btn btn-success">
                                                <i class="fas fa-check"></i> Verify
                                            </button>
                                            <button type="submit" name="status" value="Rejected" class="btn btn-danger">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Document Preview Modal -->
    <div class="modal fade" id="documentPreviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="documentModalImage" class="img-fluid document-modal-content">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Document preview modal
        const documentPreviews = document.querySelectorAll('.kyc-document-preview');
        const documentModal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
        const documentModalLabel = document.getElementById('documentModalLabel');
        const documentModalImage = document.getElementById('documentModalImage');

        documentPreviews.forEach(preview => {
            preview.addEventListener('click', function() {
                const documentType = this.dataset.documentType;
                const documentSrc = this.dataset.documentSrc;

                documentModalLabel.textContent = documentType;
                documentModalImage.src = documentSrc;
                documentModal.show();
            });
        });

        // KYC Verification Form Submission
        const verificationForms = document.querySelectorAll('.kyc-verification-form');
        
        verificationForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'verify_kyc');

                fetch('kyc-verification.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        location.reload(); // Refresh page
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An unexpected error occurred');
                });
            });
        });
    });
    </script>
</body>
</html>

