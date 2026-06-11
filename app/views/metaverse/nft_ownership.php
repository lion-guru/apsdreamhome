<div class="container-fluid py-4">
    <?php $property = $property ?? []; $nft_data = $nft_data ?? []; ?>
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">NFT Ownership</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-certificate me-3 text-primary"></i><?= ($page_title ?? 'NFT Ownership') ?></h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-file-contract me-2 text-primary"></i>NFT Certificate</h5>
                </div>
                <div class="card-body text-center py-5">
                    <i class="fas fa-certificate fa-6x text-primary mb-4"></i>
                    <h3>Property Ownership NFT</h3>
                    <h5><?= ($property['title'] ?? 'Property') ?></h5>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Token ID</small>
                            <strong>#<?= ($nft_data['token_id'] ?? 'PROP-' . ($property['id'] ?? '000')) ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Blockchain</small>
                            <strong><?= ($nft_data['blockchain'] ?? 'Ethereum') ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Contract</small>
                            <strong><?= substr($nft_data['contract_address'] ?? '0x0000...0000', 0, 10) ?>...</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>NFT ownership provides verifiable proof of property ownership in the metaverse.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Property Info</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <h5><?= ($property['title'] ?? 'Property') ?></h5>
                    <p class="text-muted"><?= ($property['location'] ?? '') ?></p>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Price</span><strong>₹<?= number_format($property['price'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Type</span><strong><?= ucfirst($property['property_type'] ?? 'N/A') ?></strong></div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt me-2 text-primary"></i>Actions</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <button class="btn btn-primary w-100 mb-2" onclick="alert('Transfer initiated')"><i class="fas fa-exchange-alt me-2"></i>Transfer NFT</button>
                    <button class="btn btn-outline-primary w-100 mb-2" onclick="alert('Verification requested')"><i class="fas fa-search me-2"></i>Verify Ownership</button>
                    <button class="btn btn-outline-info w-100" onclick="alert('NFT metadata would be displayed')"><i class="fas fa-download me-2"></i>Download Metadata</button>
                </div>
            </div>
        </div>
    </div>
</div>
