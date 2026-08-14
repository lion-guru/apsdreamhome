<?php $pageTitle = $page_title ?? 'Blockchain Certificate'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-certificate me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center p-5">
            <div class="fs-1 text-primary mb-3"><i class="fas fa-certificate"></i></div>
            <h4 class="fw-bold">Blockchain Verified Property</h4>
            <p class="text-muted">This property has been verified and registered on the blockchain</p>
            <hr class="w-50 mx-auto">
            <div class="row justify-content-center">
                <div class="col-md-6 text-start">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <tr><th>Property</th><td><?= htmlspecialchars($property['title'] ?? '-') ?></td></tr>
                        <tr><th>City</th><td><?= htmlspecialchars($property['city'] ?? '-') ?></td></tr>
                        <tr><th>Status</th><td><span class="badge bg-success">VERIFIED</span></td></tr>
                        <tr><th>Verified On</th><td><?= htmlspecialchars($verification['verification_date'] ?? '-') ?></td></tr>
                        <tr><th>Blockchain Hash</th><td class="style-85847"><code><?= htmlspecialchars($verification['blockchain_hash'] ?? '-') ?></code></td></tr>
                        <tr><th>Transaction Hash</th><td class="style-85847"><code><?= htmlspecialchars($verification['transaction_hash'] ?? '-') ?></code></td></tr>
                    </table></div>
                </div>
            </div>
            <a href="<?= ($base ?? BASE_URL) ?>blockchain/explorer/<?= $property['id'] ?? 0 ?>" class="btn btn-outline-primary mt-3"><i class="fas fa-external-link-alt me-1"></i>View on Explorer</a>
            <a href="<?= ($base ?? BASE_URL) ?>blockchain/certificate-pdf/<?= $property['id'] ?? 0 ?>" class="btn btn-primary mt-3 ms-2"><i class="fas fa-download me-1"></i>Download PDF</a>
        </div>
    </div>
</div>
