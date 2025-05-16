<?php
session_start();
require_once(__DIR__ . "/includes/config/config.php");
require_once(__DIR__ . "/includes/functions/common-functions.php");
include 'includes/base_template.php';

// Security checks
if (!isset($_SESSION['uid']) || !isset($_SESSION['usertype'])) {
    header("Location: login.php");
    exit();
}

$associate_id = $_SESSION['uid'];

// Fetch existing KYC details with comprehensive error handling
$existing_kyc = [
    'status' => 'Not Submitted',
    'last_submission' => null,
    'verification_attempts' => 0
];

try {
    // Check for existing KYC submissions
    $kyc_query = "
        SELECT 
            status, 
            submitted_at, 
            (SELECT COUNT(*) FROM kyc_verification WHERE associate_id = ?) as verification_attempts
        FROM kyc_verification 
        WHERE associate_id = ? 
        ORDER BY submitted_at DESC 
        LIMIT 1
    ";
    
    $stmt = $con->prepare($kyc_query);
    
    if (!$stmt) {
        throw new RuntimeException('Failed to prepare KYC query: ' . $con->error);
    }
    
    $stmt->bind_param('ii', $associate_id, $associate_id);
    
    if (!$stmt->execute()) {
        throw new RuntimeException('Failed to execute KYC query: ' . $stmt->error);
    }
    
    $kyc_result = $stmt->get_result();
    
    if ($kyc_result->num_rows > 0) {
        $existing_kyc = $kyc_result->fetch_assoc();
        
        // Additional validation and restrictions
        $last_submission = strtotime($existing_kyc['submitted_at']);
        $current_time = time();
        $days_since_last_submission = floor(($current_time - $last_submission) / (60 * 60 * 24));
        
        // Implement submission cooldown or retry limits
        if ($existing_kyc['status'] === 'Pending' && $days_since_last_submission < 7) {
            $existing_kyc['submission_blocked'] = true;
            $existing_kyc['block_reason'] = "You can submit again after 7 days of your last submission.";
        }
        
        if ($existing_kyc['verification_attempts'] >= 3) {
            $existing_kyc['submission_blocked'] = true;
            $existing_kyc['block_reason'] = "Maximum verification attempts reached. Contact support.";
        }
    }
    
    $stmt->close();
} catch (RuntimeException $e) {
    // Log detailed error for admin review
    error_log("KYC Fetch Critical Error: " . $e->getMessage() . 
        " | Associate ID: {$associate_id} | Timestamp: " . date('Y-m-d H:i:s') . 
        " | Database Error: " . $con->error
    );
    
    // Set error state
    $existing_kyc['fetch_error'] = true;
    $existing_kyc['error_message'] = $e->getMessage();
} catch (Exception $e) {
    // Catch any unexpected errors
    error_log("KYC Unexpected Error: " . $e->getMessage());
    $existing_kyc['unexpected_error'] = true;
}

// Prepare error handling for template
$kyc_upload_config = [
    'max_file_size' => 5 * 1024 * 1024, // 5MB
    'allowed_types' => ['image/jpeg', 'image/png', 'application/pdf'],
    'max_submissions' => 3,
    'submission_cooldown_days' => 7
];

// Handle KYC document upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = ['status' => 'error', 'message' => 'Unknown error'];

    try {
        // Validate and process uploaded documents
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        $documents = [
            'aadhar_card' => $_FILES['aadhar_card'] ?? null,
            'pan_card' => $_FILES['pan_card'] ?? null,
            'address_proof' => $_FILES['address_proof'] ?? null
        ];

        $upload_errors = [];
        $uploaded_files = [];

        foreach ($documents as $doc_type => $file) {
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                $upload_errors[] = ucfirst(str_replace('_', ' ', $doc_type)) . " not uploaded";
                continue;
            }

            if (!in_array($file['type'], $allowed_types)) {
                $upload_errors[] = ucfirst(str_replace('_', ' ', $doc_type)) . " invalid file type";
                continue;
            }

            if ($file['size'] > $max_file_size) {
                $upload_errors[] = ucfirst(str_replace('_', ' ', $doc_type)) . " exceeds 5MB limit";
                continue;
            }

            $upload_dir = __DIR__ . "/uploads/kyc/{$associate_id}/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $filename = $doc_type . '_' . time() . '_' . basename($file['name']);
            $upload_path = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $uploaded_files[$doc_type] = $filename;
            } else {
                $upload_errors[] = "Failed to upload " . ucfirst(str_replace('_', ' ', $doc_type));
            }
        }

        // If no upload errors, save KYC details
        if (empty($upload_errors)) {
            $insert_query = "INSERT INTO kyc_verification 
                (associate_id, aadhar_doc, pan_doc, address_doc, status, submitted_at) 
                VALUES (?, ?, ?, ?, 'Pending', NOW())
                ON DUPLICATE KEY UPDATE 
                aadhar_doc = ?, pan_doc = ?, address_doc = ?, status = 'Pending', submitted_at = NOW()";
            
            $stmt = $con->prepare($insert_query);
            $stmt->bind_param(
                'issssss', 
                $associate_id, 
                $uploaded_files['aadhar_card'], 
                $uploaded_files['pan_card'], 
                $uploaded_files['address_proof'],
                $uploaded_files['aadhar_card'], 
                $uploaded_files['pan_card'], 
                $uploaded_files['address_proof']
            );

            if ($stmt->execute()) {
                $response = [
                    'status' => 'success', 
                    'message' => 'KYC documents uploaded successfully. Verification in progress.'
                ];
            } else {
                $response = [
                    'status' => 'error', 
                    'message' => 'Failed to save KYC details'
                ];
            }
            $stmt->close();
        } else {
            $response = [
                'status' => 'error', 
                'message' => implode(', ', $upload_errors)
            ];
        }
    } catch (Exception $e) {
        error_log("KYC Upload Error: " . $e->getMessage());
        $response = [
            'status' => 'error', 
            'message' => 'An unexpected error occurred'
        ];
    }

    // Send JSON response for AJAX
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Prepare content for base template
ob_start();
?>

<div class="kyc-upload-container">
    <div class="kyc-header">
        <h2>\u0915\u094d\u092f\u0942 \u0905\u092a\u0932\u094b\u0921</h2>
        <p>\u0905\u092a\u0928\u0947 \u0926\u0938\u094d\u0924\u093e\u0935\u0947\u091c\u093c \u0905\u092a\u0932\u094b\u0921 \u0915\u0930\u0947\u0902</p>
    </div>

    <form id="kycUploadForm" enctype="multipart/form-data" novalidate>
        <div class="document-upload-section">
            <div class="upload-card">
                <label for="aadhar_card">\u0906\u0927\u093e\u0930 \u0915\u093e\u0930\u094d\u0921</label>
                <input type="file" 
                    id="aadhar_card" 
                    name="aadhar_card" 
                    accept=".jpg,.jpeg,.png,.pdf" 
                    required 
                    data-validate="file"
                    data-max-size="5242880"
                    data-allowed-types="image/jpeg,image/png,application/pdf"
                >
                <div class="file-preview"></div>
            </div>

            <div class="upload-card">
                <label>\u092a\u0948\u0928 \u0915\u093e\u0930\u094d\u0921</label>
                <input type="file" 
                    id="pan_card" 
                    name="pan_card" 
                    accept=".jpg,.jpeg,.png,.pdf" 
                    required 
                    data-validate="file"
                    data-max-size="5242880"
                    data-allowed-types="image/jpeg,image/png,application/pdf"
                >
                <div class="file-preview"></div>
            </div>

            <div class="upload-card">
                <label>\u092a\u0924\u093e \u092a\u094d\u0930\u092e\u093e\u0923</label>
                <input type="file" 
                    id="address_proof" 
                    name="address_proof" 
                    accept=".jpg,.jpeg,.png,.pdf" 
                    required 
                    data-validate="file"
                    data-max-size="5242880"
                    data-allowed-types="image/jpeg,image/png,application/pdf"
                >
                <div class="file-preview"></div>
            </div>
        </div>

        <div class="upload-actions">
            <button type="submit" class="btn btn-primary">\u0905\u092a\u0932\u094b\u0921 \u0915\u0930\u0947\u0902</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('kycUploadForm');
    const fileInputs = form.querySelectorAll('input[type="file"]');

    // File preview and validation
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = this.files[0];
            const previewContainer = this.nextElementSibling;
            
            // Reset preview
            previewContainer.innerHTML = '';

            if (file) {
                // Validate file type
                const allowedTypes = this.dataset.allowedTypes.split(',');
                if (!allowedTypes.includes(file.type)) {
                    showToast('\u0917\u0932\u0924 \u092b\u093e\u0907\u0932 \u092a\u094d\u0930\u0915\u093e\u0930', 'error');
                    this.value = ''; // Clear input
                    return;
                }

                // Validate file size
                const maxSize = parseInt(this.dataset.maxSize);
                if (file.size > maxSize) {
                    showToast('\u092b\u093e\u0907\u0932 \u0906\u0915\u093e\u0930 \u0905\u0927\u093f\u0915 \u0939\u0948', 'error');
                    this.value = ''; // Clear input
                    return;
                }

                // Create preview
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.textContent = file.name;
                }
            }
        });
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all inputs are filled
        const allFilled = Array.from(fileInputs).every(input => input.files.length > 0);
        
        if (!allFilled) {
            showToast('\u0915\u0943\u092a\u092f\u093e \u0938\u092d\u0940 \u0926\u0938\u094d\u0924\u093e\u0935\u0947\u091c\u093c \u0905\u092a\u0932\u094b\u0921 \u0915\u0930\u0947\u0902', 'warning');
            return;
        }

        const formData = new FormData(form);

        fetch('kyc-upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast(data.message, 'success');
                setTimeout(() => {
                    window.location.href = 'customer_dashboard.php';
                }, 2000);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('\u0905\u092a\u0932\u094b\u0921 \u0935\u093f\u092b\u0932 \u0930\u0939\u093e \u0939\u0948', 'error');
        });
    });

    // Toast notification function
    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toastContainer.removeChild(toast), 300);
            }, 3000);
        }, 10);
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
        return container;
    }
});
</script>

<style>
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --success-color: #2ecc71;
    --error-color: #e74c3c;
    --warning-color: #f39c12;
}

.kyc-upload-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.kyc-header {
    text-align: center;
    margin-bottom: 2rem;
}

.document-upload-section {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.upload-card {
    border: 2px dashed #e0e0e0;
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
    transition: border-color 0.3s ease;
}

.upload-card:hover {
    border-color: var(--secondary-color);
}

.upload-card input[type="file"] {
    display: block;
    width: 100%;
    margin-top: 0.5rem;
}

.file-preview {
    margin-top: 1rem;
    max-height: 150px;
    overflow: hidden;
}

.file-preview img {
    max-width: 100%;
    max-height: 150px;
    object-fit: cover;
    border-radius: 0.25rem;
}

.upload-actions {
    margin-top: 1.5rem;
    text-align: center;
}

#toast-container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 1000;
}

.toast {
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-radius: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.toast.show {
    opacity: 1;
}

.toast-success {
    background-color: var(--success-color);
    color: white;
}

.toast-error {
    background-color: var(--error-color);
    color: white;
}

.toast-warning {
    background-color: var(--warning-color);
    color: white;
}

@media (max-width: 768px) {
    .document-upload-section {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
// Capture output for base template
$page_content = ob_get_clean();
$page_title = "KYC Upload | APS Dream Homes";
$page_description = "Upload your KYC documents securely";

// Include base template
include 'includes/base_template.php';
?>
