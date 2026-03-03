<?php require_once 'app/views/layouts/associate_header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/associate/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">KYC Verification</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- KYC Status Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-id-card mr-2"></i>KYC Status Overview
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <?php
                                $kycStatus = $associate['kyc_status'] ?? 'pending';
                                $statusIcon = 'clock';
                                $statusColor = 'warning';

                                if ($kycStatus === 'verified') {
                                    $statusIcon = 'check-circle';
                                    $statusColor = 'success';
                                } elseif ($kycStatus === 'rejected') {
                                    $statusIcon = 'times-circle';
                                    $statusColor = 'danger';
                                }
                                ?>
                                <i class="fas fa-<?= $statusIcon ?> fa-4x text-<?= $statusColor ?> mb-3"></i>
                                <h5 class="text-<?= $statusColor ?>">
                                    <?= ucfirst($kycStatus) ?>
                                </h5>
                                <p class="text-muted">
                                    <?php
                                    switch ($kycStatus) {
                                        case 'verified':
                                            echo 'Your KYC is verified. You can request payouts.';
                                            break;
                                        case 'rejected':
                                            echo 'Your KYC has been rejected. Please resubmit.';
                                            break;
                                        default:
                                            echo 'Your KYC is currently pending verification.';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="kyc-requirements">
                                <h6 class="font-weight-bold mb-3">Documents Required for KYC:</h6>

                                <div class="requirement-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-id-card text-primary mr-3"></i>
                                        <div class="flex-grow-1">
                                            <strong>Aadhaar Card</strong>
                                            <br>
                                            <small class="text-muted">Front and Back side photos</small>
                                        </div>
                                        <span class="badge badge-info">Required</span>
                                    </div>
                                </div>

                                <div class="requirement-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-passport text-success mr-3"></i>
                                        <div class="flex-grow-1">
                                            <strong>PAN Card</strong>
                                            <br>
                                            <small class="text-muted">Upload clear photo</small>
                                        </div>
                                        <span class="badge badge-info">Required</span>
                                    </div>
                                </div>

                                <div class="requirement-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-university text-warning mr-3"></i>
                                        <div class="flex-grow-1">
                                            <strong>Bank Details</strong>
                                            <br>
                                            <small class="text-muted">Photo of Cancelled Cheque or Passbook</small>
                                        </div>
                                        <span class="badge badge-secondary">Optional</span>
                                    </div>
                                </div>

                                <div class="requirement-item">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-camera text-info mr-3"></i>
                                        <div class="flex-grow-1">
                                            <strong>Selfie with ID</strong>
                                            <br>
                                            <small class="text-muted">अपना चेहरा और ID प्रूफ साथ में फोटो</small>
                                        </div>
                                        <span class="badge badge-info">जरूरी</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($kycStatus !== 'verified'): ?>
    <!-- KYC Upload Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-upload mr-2"></i>KYC डॉक्यूमेंट्स अपलोड करें
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/associate/submit-kyc" enctype="multipart/form-data" id="kycForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="aadhar_front" class="form-label">
                                        आधार कार्ड (फ्रंट साइड) <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-file">
                                        <input type="file"
                                               class="custom-file-input"
                                               id="aadhar_front"
                                               name="aadhar_front"
                                               accept="image/*"
                                               required>
                                        <label class="custom-file-label" for="aadhar_front">फाइल चुनें</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        JPG, PNG फॉर्मेट में, मैक्सिमम 2MB साइज
                                    </small>
                                    <div class="preview mt-2" id="aadhar_front_preview"></div>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="aadhar_back" class="form-label">
                                        आधार कार्ड (बैक साइड) <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-file">
                                        <input type="file"
                                               class="custom-file-input"
                                               id="aadhar_back"
                                               name="aadhar_back"
                                               accept="image/*"
                                               required>
                                        <label class="custom-file-label" for="aadhar_back">फाइल चुनें</label>
                                    </div>
                                    <div class="preview mt-2" id="aadhar_back_preview"></div>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="pan_card" class="form-label">
                                        पैन कार्ड <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-file">
                                        <input type="file"
                                               class="custom-file-input"
                                               id="pan_card"
                                               name="pan_card"
                                               accept="image/*"
                                               required>
                                        <label class="custom-file-label" for="pan_card">फाइल चुनें</label>
                                    </div>
                                    <div class="preview mt-2" id="pan_card_preview"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="bank_proof" class="form-label">
                                        बैंक प्रूफ (ऑप्शनल)
                                    </label>
                                    <div class="custom-file">
                                        <input type="file"
                                               class="custom-file-input"
                                               id="bank_proof"
                                               name="bank_proof"
                                               accept="image/*">
                                        <label class="custom-file-label" for="bank_proof">फाइल चुनें</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        कैंसिल्ड चेक या पासबुक की फोटो
                                    </small>
                                    <div class="preview mt-2" id="bank_proof_preview"></div>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="selfie_with_id" class="form-label">
                                        सेल्फी विद ID प्रूफ <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-file">
                                        <input type="file"
                                               class="custom-file-input"
                                               id="selfie_with_id"
                                               name="selfie_with_id"
                                               accept="image/*"
                                               required>
                                        <label class="custom-file-label" for="selfie_with_id">फाइल चुनें</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        अपना चेहरा और कोई एक ID प्रूफ साथ में फोटो
                                    </small>
                                    <div class="preview mt-2" id="selfie_with_id_preview"></div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>इम्पॉर्टेंट:</strong> सभी डॉक्यूमेंट्स क्लियर और रीडेबल होने चाहिए।
                            सबमिट करने के बाद 24-48 घंटों में वेरिफिकेशन हो जाएगा।
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-upload mr-2"></i>KYC डॉक्यूमेंट्स सबमिट करें
                            </button>
                            <button type="reset" class="btn btn-secondary btn-lg ml-2">
                                <i class="fas fa-redo mr-2"></i>रिसेट करें
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- KYC History -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-2"></i>KYC सबमिशन हिस्ट्री
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($associate['kyc_documents'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>डॉक्यूमेंट टाइप</th>
                                        <th>सबमिशन डेट</th>
                                        <th>स्टेटस</th>
                                        <th>एक्शन</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $documents = json_decode($associate['kyc_documents'], true) ?? [];
                                    foreach ($documents as $type => $filename):
                                    ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-alt mr-2"></i>
                                                <?= ucfirst(str_replace('_', ' ', $type)) ?>
                                            </td>
                                            <td>
                                                <?= date('d M Y') ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($filename) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($kycStatus === 'verified'): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check mr-1"></i>वेरिफाइड
                                                    </span>
                                                <?php elseif ($kycStatus === 'rejected'): ?>
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times mr-1"></i>रिजेक्टेड
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock mr-1"></i>पेंडिंग
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="viewDocument('<?= $type ?>', '<?= $filename ?>')">
                                                    <i class="fas fa-eye mr-1"></i>व्यू
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-file-upload fa-3x text-muted mb-3"></i>
                            <p class="text-muted">अभी तक कोई KYC डॉक्यूमेंट सबमिट नहीं किया गया</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">डॉक्यूमेंट प्रीव्यू</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="documentPreview" src="" alt="Document Preview" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input preview
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.id + '_preview';
            const previewDiv = document.getElementById(previewId);

            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewDiv.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-height: 150px;">';
                };

                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // Custom file input label update
    document.querySelectorAll('.custom-file-input').forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'फाइल चुनें';
            const label = this.nextElementSibling;
            label.textContent = fileName;
        });
    });
});

function viewDocument(type, filename) {
    document.getElementById('documentPreview').src = '/uploads/kyc/' + filename;
    $('#documentModal').modal('show');
}

// Form validation
document.getElementById('kycForm').addEventListener('submit', function(e) {
    const requiredFields = ['aadhar_front', 'aadhar_back', 'pan_card', 'selfie_with_id'];
    let isValid = true;

    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.files || !input.files[0]) {
            alert('कृपया सभी जरूरी डॉक्यूमेंट्स अपलोड करें');
            isValid = false;
        }
    });

    if (!isValid) {
        e.preventDefault();
    }
});
</script>

<style>
.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.requirement-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 15px;
}

.requirement-item .fas {
    width: 30px;
}

.custom-file {
    position: relative;
    display: inline-block;
    width: 100%;
    height: calc(2.25rem + 2px);
    margin-bottom: 0;
}

.custom-file-input {
    position: relative;
    z-index: 2;
    width: 100%;
    height: calc(2.25rem + 2px);
    margin: 0;
    opacity: 0;
}

.custom-file-label {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    z-index: 1;
    height: calc(2.25rem + 2px);
    padding: 0.375rem 0.75rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 2px solid #e3e6f0;
    border-radius: 0.25rem;
    cursor: pointer;
}

.custom-file-label::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 3;
    display: block;
    height: calc(calc(2.25rem + 2px) - 1px * 2);
    padding: 0.375rem 0.75rem;
    line-height: 1.5;
    color: #495057;
    content: "Browse";
    background-color: #e9ecef;
    border-left: 1px solid #ced4da;
    border-radius: 0 0.25rem 0.25rem 0;
}

.preview img {
    max-width: 100%;
    height: auto;
    border-radius: 5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.8em;
}

.alert {
    border-radius: 8px;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
}
</style>

<?php require_once 'app/views/layouts/associate_footer.php'; ?>
