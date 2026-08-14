ï»¿<?php
$pageTitle = 'Document AI Review Queue';
$jobs = $jobs ?? [];
$stats = $stats ?? [];
$filters = $filters ?? [];
$current_page = $current_page ?? 1;
$total_pages = $total_pages ?? 1;
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-1"><i class="fas fa-file-alt me-2"></i>Document AI Review Queue</h2>
            <p class="text-muted mb-0">Review and approve AI-extracted document data</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="<?= BASE_URL ?>/admin/ai/document-extraction" class="btn btn-outline-primary">
                    <i class="fas fa-upload me-1"></i>New Extraction
                </a>
                <a href="<?= BASE_URL ?>/admin/ai/templates" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-1"></i>Templates
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="queued" <?= ($filters['status'] ?? '') === 'queued' ? 'selected' : '' ?>>Queued</option>
                        <option value="processing" <?= ($filters['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="corrected" <?= ($filters['status'] ?? '') === 'corrected' ? 'selected' : '' ?>>Corrected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Document Type</label>
                    <select name="document_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="sale_deed" <?= ($filters['document_type'] ?? '') === 'sale_deed' ? 'selected' : '' ?>>Sale Deed</option>
                        <option value="agreement_to_sell" <?= ($filters['document_type'] ?? '') === 'agreement_to_sell' ? 'selected' : '' ?>>Agreement to Sell</option>
                        <option value="gift_deed" <?= ($filters['document_type'] ?? '') === 'gift_deed' ? 'selected' : '' ?>>Gift Deed</option>
                        <option value="lease_deed" <?= ($filters['document_type'] ?? '') === 'lease_deed' ? 'selected' : '' ?>>Lease Deed</option>
                        <option value="mortgage_deed" <?= ($filters['document_type'] ?? '') === 'mortgage_deed' ? 'selected' : '' ?>>Mortgage Deed</option>
                        <option value="power_of_attorney" <?= ($filters['document_type'] ?? '') === 'power_of_attorney' ? 'selected' : '' ?>>Power of Attorney</option>
                        <option value="property_tax_receipt" <?= ($filters['document_type'] ?? '') === 'property_tax_receipt' ? 'selected' : '' ?>>Property Tax Receipt</option>
                        <option value="mutation_certificate" <?= ($filters['document_type'] ?? '') === 'mutation_certificate' ? 'selected' : '' ?>>Mutation Certificate</option>
                        <option value="encumbrance_certificate" <?= ($filters['document_type'] ?? '') === 'encumbrance_certificate' ? 'selected' : '' ?>>Encumbrance Certificate</option>
                        <option value="khata_certificate" <?= ($filters['document_type'] ?? '') === 'khata_certificate' ? 'selected' : '' ?>>Khata Certificate</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Engine</label>
                    <select name="engine" class="form-select">
                        <option value="">All Engines</option>
                        <option value="mock" <?= ($filters['engine'] ?? '') === 'mock' ? 'selected' : '' ?>>Mock (Testing)</option>
                        <option value="tesseract_ocr" <?= ($filters['engine'] ?? '') === 'tesseract_ocr' ? 'selected' : '' ?>>Tesseract OCR</option>
                        <option value="google_document_ai" <?= ($filters['engine'] ?? '') === 'google_document_ai' ? 'selected' : '' ?>>Google Document AI</option>
                        <option value="azure_form_recognizer" <?= ($filters['engine'] ?? '') === 'azure_form_recognizer' ? 'selected' : '' ?>>Azure Form Recognizer</option>
                        <option value="aws_textract" <?= ($filters['engine'] ?? '') === 'aws_textract' ? 'selected' : '' ?>>AWS Textract</option>
                        <option value="custom_ml" <?= ($filters['engine'] ?? '') === 'custom_ml' ? 'selected' : '' ?>>Custom ML</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="w-100">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                    </div>
                </div>
                <div class="col-12">
                    <a href="<?= BASE_URL ?>/admin/ai/document-extraction" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times me-1"></i>Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Pending Review</div>
                            <div class="fs-3 fw-bold"><?= $stats['pending_review'] ?? 0 ?></div>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Approved Today</div>
                            <div class="fs-3 fw-bold"><?= $stats['approved_today'] ?? 0 ?></div>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Corrected Today</div>
                            <div class="fs-3 fw-bold"><?= $stats['corrected_today'] ?? 0 ?></div>
                        </div>
                        <i class="fas fa-edit fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Avg Confidence</div>
                            <div class="fs-3 fw-bold"><?= $stats['avg_confidence'] ?? 0 ?>%</div>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Extraction Jobs</h5>
            <span class="badge bg-primary"><?= count($jobs) ?> jobs</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($jobs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No extraction jobs found</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Document</th>
                                <th>Type</th>
                                <th>Engine</th>
                                <th>Status</th>
                                <th>Confidence</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><code><?= $job['id'] ?></code></td>
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($job['original_filename'] ?? '') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($job['document_type'] ?? '') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($job['document_type'] ?? '') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($job['extraction_engine'] ?? '') ?></span>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'queued' => 'secondary',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'failed' => 'danger',
                                        'approved' => 'success',
                                        'corrected' => 'warning',
                                    ];
                                    $color = $statusColors[$job['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst($job['status']) ?></span>
                                </td>
                                <td>
                                    <?php if (($job['confidence_score'] ?? 0) > 0): ?>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" class="style-29939">
                                                <div class="progress-bar bg-<?= ($job['confidence_score'] >= 90) ? 'success' : (($job['confidence_score'] >= 70) ? 'warning' : 'danger') ?>" 
                                                     class="style-59464"></div>
                                            </div>
                                            <small><?= $job['confidence_score'] ?>%</small>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">â€”</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= date('d M Y H:i', strtotime($job['created_at'])) ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/ai/document-extraction/<?= $job['id'] ?>" class="btn btn-outline-primary" title="Review">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (($job['status'] ?? '') === 'completed'): ?>
                                            <button type="button" class="btn btn-outline-success" onclick="reviewJob(<?= $job['id'] ?>, 'approve')" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" onclick="reviewJob(<?= $job['id'] ?>, 'correct')" title="Correct">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (($job['status'] ?? '') === 'failed'): ?>
                                            <button type="button" class="btn btn-outline-primary" onclick="retryJob(<?= $job['id'] ?>)" title="Retry">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($jobs) && ($total_pages ?? 1) > 1): ?>
            <div class="card-footer bg-white border-0">
                <nav aria-label="Jobs pagination">
                    <ul class="pagination pagination-sm mb-0 justify-content-center">
                        <li class="page-item <?= ($current_page ?? 1) <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => ($current_page ?? 1) - 1])) ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= ($total_pages ?? 1); $i++): ?>
                            <li class="page-item <?= ($current_page ?? 1) === $i ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($current_page ?? 1) >= ($total_pages ?? 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => ($current_page ?? 1) + 1])) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-clipboard-check me-2"></i>Review Extraction</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reviewModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="approveBtn" onclick="submitReview('approve')"><i class="fas fa-check me-1"></i>Approve</button>
                <button type="button" class="btn btn-warning" id="correctBtn" onclick="submitReview('correct')"><i class="fas fa-edit me-1"></i>Correct & Approve</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentJobId = null;
let currentAction = null;

function reviewJob(jobId, action) {
    currentJobId = jobId;
    currentAction = action;
    
    const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
    modal.show();
    
    loadReviewData(jobId);
}

async function loadReviewData(jobId) {
    const container = document.getElementById('reviewModalBody');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>';
    
    try {
        const response = await fetch('<?= BASE_URL ?>/api/document-ai/job/${jobId}');
        const result = await response.json();
        
        if (result.success) {
            renderReviewForm(result.job);
        } else {
            container.innerHTML = '<div class="alert alert-danger">Failed to load job data</div>';
        }
    } catch (e) {
        container.innerHTML = '<div class="alert alert-danger">Error loading data</div>';
    }
}

function renderReviewForm(job) {
    const container = document.getElementById('reviewModalBody');
    const data = job.extracted_data ? JSON.parse(job.extracted_data) : {};
    const template = job.template || [];
    
    let html = `
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Document:</strong> ${job.original_filename}
            </div>
            <div class="col-md-6">
                <strong>Type:</strong> ${job.document_type}
            </div>
            <div class="col-md-6">
                <strong>Engine:</strong> ${job.extraction_engine}
            </div>
            <div class="col-md-6">
                <strong>Confidence:</strong> ${job.confidence_score}%
            </div>
        </div>
        <hr>
        <form id="reviewForm">
    <?php echo CSRFProtection::csrfField(); ?>
            <input type="hidden" name="job_id" value="${job.id}">
            <input type="hidden" name="action" id="reviewAction" value="${currentAction}">
    `;
    
    if (template.length > 0) {
        html += '<div class="row">';
        template.forEach(field => {
            const value = data[field.field_name] || '';
            const required = field.is_required ? 'required' : '';
            html += `
                <div class="col-md-6 mb-3">
                    <label class="form-label">${field.field_label} ${field.is_required ? '<span class="text-danger">*</span>' : ''}</label>
            `;
            
            switch (field.field_type) {
                case 'date':
                    html += `<input type="date" name="${field.field_name}" class="form-control" value="${value}" ${required}>`;
                    break;
                case 'number':
                    html += `<input type="number" name="${field.field_name}" class="form-control" value="${value}" ${required}>`;
                    break;
                case 'currency':
                    html += `<input type="text" name="${field.field_name}" class="form-control" value="${value}" ${required} placeholder="â‚¹">`;
                    break;
                case 'percentage':
                    html += `<input type="number" step="0.01" name="${field.field_name}" class="form-control" value="${value}" ${required} placeholder="%">`;
                    break;
                case 'select':
                    html += `<select name="${field.field_name}" class="form-select" ${required}><option value="">Select...</option></select>`;
                    break;
                case 'address':
                    html += `<textarea name="${field.field_name}" class="form-control" rows="2" ${required}>${value}</textarea>`;
                    break;
                case 'aadhaar':
                    html += `<input type="text" name="${field.field_name}" class="form-control" value="${value}" ${required} placeholder="XXXX-XXXX-1234" maxlength="14">`;
                    break;
                case 'pan':
                    html += `<input type="text" name="${field.field_name}" class="form-control" value="${value}" ${required} placeholder="ABCDE1234F" maxlength="10" class="style-36130">`;
                    break;
                case 'phone':
                    html += `<input type="tel" name="${field.field_name}" class="form-control" value="${value}" ${required} placeholder="+91">`;
                    break;
                case 'email':
                    html += `<input type="email" name="${field.field_name}" class="form-control" value="${value}" ${required}>`;
                    break;
                case 'area':
                    html += `<input type="text" name="${field.field_name}" class="form-control" value="${value}" ${required} placeholder="sq ft / sq m / acres">`;
                    break;
                default:
                    html += `<input type="text" name="${field.field_name}" class="form-control" value="${value}" ${required}>`;
            }
            
            html += `
                </div>
            `;
        });
        html += '</div>';
    } else {
        // Fallback: show all extracted fields
        html += '<div class="row">';
        Object.entries(data).forEach(([key, value]) => {
            html += `
                <div class="col-md-6 mb-3">
                    <label class="form-label">${key}</label>
                    <input type="text" name="${key}" class="form-control" value="${value}">
                </div>
            `;
        });
        html += '</div>';
    }
    
    html += `
        </form>
    `;
    
    container.innerHTML = html;
}

function submitReview(action) {
    currentAction = action;
    const form = document.getElementById('reviewForm');
    const formData = new FormData(form);
    formData.append('action', action);
    formData.append('reviewer_id', <?= $_SESSION['admin_id'] ?? 1 ?>);
    
    fetch('<?= BASE_URL ?>/api/document-ai/review/${currentJobId}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    })
    .catch(() => alert('Error submitting review'));
}

function retryJob(jobId) {
    if (confirm('Retry this failed job?')) {
        fetch('<?= BASE_URL ?>/api/document-ai/process/${jobId}', { method: 'POST' })
            .then(r => r.json())
            .then(result => {
                if (result.success) location.reload();
                else alert('Error: ' + result.error);
            });
    }
}
</script>