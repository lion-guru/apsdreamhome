<?php $pageTitle = 'Blockchain Certificate PDF'; $cd = $certificate_data ?? []; ?>
<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm mx-auto" style="max-width:800px">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="fs-1 text-primary mb-2"><i class="fas fa-certificate"></i></div>
                <h3 class="fw-bold">Certificate of Blockchain Verification</h3>
                <p class="text-muted">Powered by <?= strtoupper(htmlspecialchars($cd['blockchain_network'] ?? 'POLYGON')) ?></p>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Certificate No:</strong> <?= htmlspecialchars($cd['certificate_number'] ?? '-') ?></p>
                    <p><strong>Property:</strong> <?= htmlspecialchars($cd['property_title'] ?? '-') ?></p>
                    <p><strong>Property ID:</strong> #<?= $cd['property_id'] ?? 0 ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Owner:</strong> <?= htmlspecialchars($cd['owner_name'] ?? '-') ?></p>
                    <p><strong>Verified On:</strong> <?= htmlspecialchars($cd['verification_date'] ?? '-') ?></p>
                    <p><strong>Network:</strong> <?= htmlspecialchars($cd['blockchain_network'] ?? '-') ?></p>
                </div>
            </div>
            <hr>
            <p><strong>Verification Hash:</strong></p>
            <p class="bg-light p-2 rounded"><code><?= htmlspecialchars($cd['verification_hash'] ?? '-') ?></code></p>
            <p><strong>Transaction Hash:</strong></p>
            <p class="bg-light p-2 rounded"><code><?= htmlspecialchars($cd['transaction_hash'] ?? '-') ?></code></p>
            <div class="text-center mt-4 text-muted small">This certificate is generated automatically and stored on the blockchain</div>
        </div>
    </div>
</div>
