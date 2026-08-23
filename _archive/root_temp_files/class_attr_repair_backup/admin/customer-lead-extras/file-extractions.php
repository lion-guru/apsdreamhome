ï»¿<?php
// Session started by controller
$page_title = 'Lead File Extractions';
$page_description = 'View extracted data from lead documents and files';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Lead File Extractions</h1>
            <p class="text-muted">View extracted data from lead documents and files</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-file-download fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Extractions</h6>
                            <h3 class="mb-0"><?php echo count($fileExtractions); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Files Processed</h6>
                            <h3 class="mb-0"><?php echo count(array_unique(array_column($fileExtractions, 'file_name'))); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-database fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Records Extracted</h6>
                            <h3 class="mb-0"><?php echo array_sum(array_column($fileExtractions, 'extracted_count')); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-filter fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Extraction Methods</h6>
                            <h3 class="mb-0"><?php echo count(array_unique(array_column($fileExtractions, 'extraction_method'))); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">File Extractions</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Original Name</th>
                            <th>Extracted Count</th>
                            <th>Method</th>
                            <th>Created By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fileExtractions)): ?>
                            <?php foreach ($fileExtractions as $extraction): ?>
                                <tr>
                                    <td>
                                        <div class="text-truncate" class="style-3881" title="<?php echo htmlspecialchars($extraction['file_name'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($extraction['file_name'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" class="style-3881" title="<?php echo htmlspecialchars($extraction['original_name'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($extraction['original_name'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo (int)$extraction['extracted_count']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($extraction['extraction_method'] ?? 'Unknown'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($extraction['created_by_name'] ?? 'Unknown'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y H:i', strtotime($extraction['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary view-details" data-id="<?php echo e($extraction['id']); ?>">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No file extractions found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for viewing extraction details -->
<div class="modal fade" id="extractionDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Extraction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="extractionDetailsBody">
                <!-- Details will be loaded here via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View details button click handler
    const viewDetailsButtons = document.querySelectorAll('.view-details');
    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const extractionId = this.getAttribute('data-id');
            
            // Show loading state
            const modalBody = document.getElementById('extractionDetailsBody');
            modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            
            // Show modal
            const extractionDetailsModal = new bootstrap.Modal(document.getElementById('extractionDetailsModal'));
            extractionDetailsModal.show();
            
            // Fetch extraction details via AJAX
            fetch('<?php echo BASE_URL; ?>/admin/api/lead-file-extraction/' + extractionId)
                .then(response => response.json())
                .then(data => {
                .catch(err => console.error('Request failed:', err));
                    if (data.success) {
                        modalBody.innerHTML = `
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>File Name:</strong> ${data.extraction.file_name || 'N/A'}
                                </div>
                                <div class="col-md-6">
                                    <strong>Original Name:</strong> ${data.extraction.original_name || 'N/A'}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Extraction Method:</strong> ${data.extraction.extraction_method || 'N/A'}
                                </div>
                                <div class="col-md-6">
                                    <strong>Extracted Count:</strong> ${data.extraction.extracted_count || 0}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Created By:</strong> ${data.extraction.created_by_name || 'Unknown'}
                                </div>
                                <div class="col-md-6">
                                    <strong>Created At:</strong> ${new Date(data.extraction.created_at).toLocaleString()}
                                </div>
                            </div>
                            <?php if (!empty($extraction['extracted_data'])): ?>
                            <div class="mb-4">
                                <strong>Extracted Data:</strong>
                                <pre class="bg-light p-3 rounded" class="style-52319">${JSON.stringify(data.extraction.extracted_data, null, 2)}</pre>
                            </div>
                            <?php endif; ?>
                        `;
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Error loading extraction details: ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading extraction details: ${error.message}</div>`;
                });
        });
    });
});
</script>