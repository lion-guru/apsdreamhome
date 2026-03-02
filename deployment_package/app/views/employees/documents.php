<?php
/**
 * Employee Documents View
 * Shows employee documents and allows uploads
 */
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-alt me-2"></i>My Documents</h2>
        <button class="btn btn-primary" onclick="showUploadModal()">
            <i class="fas fa-upload me-2"></i>Upload Document
        </button>
    </div>

    <!-- Document Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $totalApproved = count(array_filter($documents, function($d) {
                                    return $d['status'] === 'approved';
                                }));
                                echo $totalApproved;
                                ?>
                            </h4>
                            <small>Approved</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $totalPending = count(array_filter($documents, function($d) {
                                    return $d['status'] === 'pending';
                                }));
                                echo $totalPending;
                                ?>
                            </h4>
                            <small>Pending</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $totalRejected = count(array_filter($documents, function($d) {
                                    return $d['status'] === 'rejected';
                                }));
                                echo $totalRejected;
                                ?>
                            </h4>
                            <small>Rejected</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $totalDocuments = count($documents);
                                echo $totalDocuments;
                                ?>
                            </h4>
                            <small>Total Documents</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents List -->
    <div class="row">
        <?php if (empty($documents)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No documents found. Upload your first document to get started.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($documents as $document): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card document-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-file-<?= $this->getDocumentIcon($document['document_type'] ?? 'pdf') ?> me-2"></i>
                                <?= htmlspecialchars($document['document_name'] ?? 'Untitled Document') ?>
                            </h6>
                            <span class="badge bg-<?= $this->getDocumentStatusBadgeClass($document['status'] ?? 'pending') ?>">
                                <?= ucfirst($document['status'] ?? 'pending') ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="document-info mb-3">
                                <p class="mb-2">
                                    <strong>Type:</strong>
                                    <span class="badge bg-info">
                                        <?= htmlspecialchars($document['document_type_name'] ?? 'N/A') ?>
                                    </span>
                                </p>
                                <p class="mb-2">
                                    <strong>Size:</strong>
                                    <?= $this->formatFileSize($document['file_size'] ?? 0) ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Uploaded:</strong>
                                    <?= date('M d, Y', strtotime($document['uploaded_date'])) ?>
                                </p>
                                <?php if (!empty($document['expiry_date'])): ?>
                                    <p class="mb-2">
                                        <strong>Expires:</strong>
                                        <?= date('M d, Y', strtotime($document['expiry_date'])) ?>
                                        <?php if (strtotime($document['expiry_date']) < time()): ?>
                                            <span class="badge bg-danger ms-2">Expired</span>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- Document Description -->
                            <?php if (!empty($document['description'])): ?>
                                <p class="card-text text-muted mb-3">
                                    <?= htmlspecialchars(substr($document['description'], 0, 100)) ?>
                                    <?php if (strlen($document['description']) > 100): ?>...<?php endif; ?>
                                </p>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="document-actions">
                                <button class="btn btn-sm btn-primary" onclick="viewDocument(<?= $document['document_id'] ?>)">
                                    <i class="fas fa-eye me-1"></i>View
                                </button>
                                <button class="btn btn-sm btn-success" onclick="downloadDocument(<?= $document['document_id'] ?>)">
                                    <i class="fas fa-download me-1"></i>Download
                                </button>
                                <?php if (($document['status'] ?? 'pending') === 'pending'): ?>
                                    <button class="btn btn-sm btn-warning" onclick="updateDocumentStatus(<?= $document['document_id'] ?>, 'approved')">
                                        <i class="fas fa-check me-1"></i>Approve
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteDocument(<?= $document['document_id'] ?>)">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>

                        <!-- Document Footer -->
                        <div class="card-footer text-muted">
                            <small>
                                <i class="fas fa-user me-1"></i>
                                Uploaded by: <?= htmlspecialchars($document['uploaded_by_name'] ?? 'You') ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/employee/documents/upload" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="document_type" class="form-label">Document Type *</label>
                                <select class="form-select" id="document_type" name="document_type" required>
                                    <option value="">Select Document Type</option>
                                    <?php foreach ($document_types as $type): ?>
                                        <option value="<?= $type['document_type_id'] ?>">
                                            <?= htmlspecialchars($type['document_type_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="document_file" class="form-label">File *</label>
                                <input type="file" class="form-control" id="document_file" name="document_file"
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                <div class="form-text">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB)</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="document_name" class="form-label">Document Name *</label>
                        <input type="text" class="form-control" id="document_name" name="document_name"
                               placeholder="Enter document name" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Brief description of the document..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date (if applicable)</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="is_confidential" class="form-label">Confidential</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_confidential" name="is_confidential">
                                    <label class="form-check-label" for="is_confidential">
                                        Mark as confidential document
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showUploadModal() {
    $('#uploadModal').modal('show');
}

function viewDocument(documentId) {
    // In a real implementation, you would open the document in a viewer
    window.open(`/employee/documents/view/${documentId}`, '_blank');
}

function downloadDocument(documentId) {
    // In a real implementation, you would download the document
    window.location.href = `/employee/documents/download/${documentId}`;
}

function updateDocumentStatus(documentId, status) {
    if (confirm(`Are you sure you want to mark this document as ${status}?`)) {
        // In a real implementation, you would submit a form to update status
        alert(`Document ${status} feature would be implemented here.`);
    }
}

function deleteDocument(documentId) {
    if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        // In a real implementation, you would submit a form to delete the document
        alert('Document deletion feature would be implemented here.');
    }
}

// File size validation
document.getElementById('document_file')?.addEventListener('change', function() {
    const file = this.files[0];
    if (file && file.size > 10 * 1024 * 1024) { // 10MB limit
        alert('File size must be less than 10MB');
        this.value = '';
    }
});
</script>

<style>
.stats-card {
    border: none;
    border-radius: 10px;
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.document-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.document-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.document-info {
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 10px;
}

.document-actions {
    margin-top: 15px;
}

.document-actions .btn {
    margin-right: 5px;
    margin-bottom: 5px;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.8em;
}
</style>
