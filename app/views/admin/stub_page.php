<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-cog me-2"></i><?= htmlspecialchars($page_title ?? 'Page') ?></h4>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted"><?= htmlspecialchars($page_title ?? '') ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($page_message ?? 'This section is under development.') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
