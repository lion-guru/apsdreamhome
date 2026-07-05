<?php $current_page = $current_page ?? 'documents'; ?>
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-folder-open text-primary me-2"></i><?= __('assoc_doc_title', [], 'Document Locker') ?></h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= __('assoc_doc_desc', [], 'Upload and manage your important documents here.') ?>
                    </div>
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                        <p class="text-muted"><?= __('assoc_doc_empty', [], 'No documents uploaded yet.') ?></p>
                        <p class="text-muted small"><?= __('assoc_doc_empty_desc', [], 'Documents you upload will appear here for easy access.') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
