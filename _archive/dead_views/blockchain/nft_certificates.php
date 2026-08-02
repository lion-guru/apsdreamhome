<?php $pageTitle = $page_title ?? 'NFT Property Certificates'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-image me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <?php if (!empty($nft_properties)): ?>
            <?php foreach ($nft_properties as $np): ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="fs-1 text-primary mb-2"><i class="fas fa-certificate"></i></div>
                            <h5 class="card-title"><?= htmlspecialchars($np['title'] ?? '-') ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars($np['city'] ?? '') ?></p>
                            <span class="badge bg-success mb-2">NFT Minted</span>
                            <p class="small mb-0">Token ID: <code><?= htmlspecialchars($np['nft_token_id'] ?? '-') ?></code></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="fs-1 text-muted mb-3"><i class="fas fa-image"></i></div>
                        <h5>No NFT Certificates Yet</h5>
                        <p class="text-muted">Properties verified on blockchain will appear here as NFTs</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
