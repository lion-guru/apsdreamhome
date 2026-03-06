<?php
/**
 * KYC Upload View - APS Dream Homes
 * Migrated from resources/views/Views/kyc-upload.php
 */

require_once __DIR__ . '/init.php';

// Security checks
if (!isset($_SESSION['uid']) || !isset($_SESSION['utype'])) {
    header("Location: login.php");
    exit();
}

$db = \App\Core\App::database();
$associate_id = $_SESSION['uid'];

// Handle KYC document upload (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = ['status' => 'error', 'message' => 'Unknown error'];

    try {
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

            $upload_dir = __DIR__ . "/../../../public/uploads/kyc/{$associate_id}/";
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

        if (empty($upload_errors)) {
            $db = \App\Core\App::database();
            $insert_query = "INSERT INTO kyc_verification
                (associate_id, aadhar_doc, pan_doc, address_doc, status, submitted_at)
                VALUES (?, ?, ?, ?, 'Pending', NOW())
                ON DUPLICATE KEY UPDATE
                aadhar_doc = ?, pan_doc = ?, address_doc = ?, status = 'Pending', submitted_at = NOW()";

            $success = $db->execute($insert_query, [
                $associate_id,
                $uploaded_files['aadhar_card'],
                $uploaded_files['pan_card'],
                $uploaded_files['address_proof'],
                $uploaded_files['aadhar_card'],
                $uploaded_files['pan_card'],
                $uploaded_files['address_proof']
            ]);

            if ($success) {
                $response = [
                    'status' => 'success',
                    'message' => 'KYC documents uploaded successfully. Verification in progress.'
                ];
            } else {
                $response = ['status' => 'error', 'message' => 'Failed to save KYC details to database'];
            }
        } else {
            $response = ['status' => 'error', 'message' => implode(', ', $upload_errors)];
        }
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Prepare view data (GET)
$page_title = 'KYC Upload | APS Dream Homes';
$layout = 'modern';

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white text-center py-4">
                <h2 class="mb-0 fw-bold">KYC दस्तावेज़ अपलोड</h2>
                <p class="mb-0 mt-2 opacity-75">अपने दस्तावेज़ सुरक्षित रूप से अपलोड करें</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    कृपया सुनिश्चित करें कि दस्तावेज़ स्पष्ट और पढ़ने योग्य हैं। अधिकतम फ़ाइल आकार: <strong>5MB</strong>। अनुमत प्रकार: <strong>JPG, PNG, PDF</strong>।
                </div>

                <form id="kycUploadForm" enctype="multipart/form-data" novalidate>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="upload-box p-3 border-2 border-dashed rounded-3 text-center bg-light">
                                <label for="aadhar_card" class="form-label fw-bold d-block mb-3">आधार कार्ड</label>
                                <div class="icon-placeholder mb-3">
                                    <i class="fas fa-id-card fa-3x text-muted"></i>
                                </div>
                                <input type="file" id="aadhar_card" name="aadhar_card" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" required>
                                <div class="file-preview mt-2 small text-truncate"></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="upload-box p-3 border-2 border-dashed rounded-3 text-center bg-light">
                                <label for="pan_card" class="form-label fw-bold d-block mb-3">पैन कार्ड</label>
                                <div class="icon-placeholder mb-3">
                                    <i class="fas fa-address-card fa-3x text-muted"></i>
                                </div>
                                <input type="file" id="pan_card" name="pan_card" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" required>
                                <div class="file-preview mt-2 small text-truncate"></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="upload-box p-3 border-2 border-dashed rounded-3 text-center bg-light">
                                <label for="address_proof" class="form-label fw-bold d-block mb-3">पता प्रमाण</label>
                                <div class="icon-placeholder mb-3">
                                    <i class="fas fa-file-invoice fa-3x text-muted"></i>
                                </div>
                                <input type="file" id="address_proof" name="address_proof" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" required>
                                <div class="file-preview mt-2 small text-truncate"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                            <i class="fas fa-cloud-upload-alt me-2"></i>अभी अपलोड करें
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.border-dashed {
    border-style: dashed !important;
}
.upload-box {
    transition: all 0.3s ease;
    cursor: pointer;
}
.upload-box:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa !important;
}
.icon-placeholder i {
    transition: all 0.3s ease;
}
.upload-box:hover .icon-placeholder i {
    color: #0d6efd !important;
    transform: scale(1.1);
}
.file-preview img {
    max-width: 100%;
    height: auto;
    border-radius: 5px;
    margin-top: 10px;
}
</style>

<?php
$content = ob_get_clean();

// Custom scripts
ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('kycUploadForm');
    const fileInputs = form.querySelectorAll('input[type="file"]');

    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            const previewContainer = this.parentElement.querySelector('.file-preview');
            previewContainer.innerHTML = '';

            if (file) {
                previewContainer.textContent = file.name;

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-fluid mt-2 rounded border';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const allFilled = Array.from(fileInputs).every(input => input.files.length > 0);
        if (!allFilled) {
            alert('कृपया सभी आवश्यक दस्तावेज़ अपलोड करें।');
            return;
        }

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>अपलोड हो रहा है...';

        fetch(window.location.pathname, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                window.location.href = '/dashboard';
            } else {
                alert(data.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('अपलोड के दौरान एक त्रुटि हुई। कृपया पुन: प्रयास करें।');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        });
    });
});
</script>
<?php
$scripts = ob_get_clean();

// Include layout
require_once __DIR__ . '/../layouts/' . $layout . '.php';
