<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-user me-2 text-info"></i>Lead Details</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5><?= htmlspecialchars($lead['name'] ?? '') ?></h5>
                    <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($lead['phone'] ?? '') ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($lead['email'] ?? '') ?></p>
                    <p class="mb-1"><strong>Source:</strong> <?= htmlspecialchars($lead['source'] ?? '') ?></p>
                    <p class="mb-0"><strong>Status:</strong> <span class="badge bg-info"><?= htmlspecialchars($lead['status'] ?? '') ?></span></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6>Notes</h6>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($lead['notes'] ?? 'No notes')) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>