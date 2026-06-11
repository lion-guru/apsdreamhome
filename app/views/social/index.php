<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-share-alt me-2"></i><?= ($page_title ?? 'Social Media Tools') ?></h4>
        <div class="d-flex gap-2">
            <a href="<?= ($base ?? BASE_URL) ?>social/share-property" class="btn btn-primary btn-sm"><i class="fas fa-share me-1"></i>Share Property</a>
            <a href="<?= ($base ?? BASE_URL) ?>admin/social-analytics" class="btn btn-outline-info btn-sm"><i class="fas fa-chart-bar me-1"></i>Analytics</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fab fa-facebook-f fa-2x mb-1"></i>
                    <h5 class="mb-0">Facebook</h5>
                    <small>Share properties on Facebook</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <i class="fab fa-whatsapp fa-2x mb-1"></i>
                    <h5 class="mb-0">WhatsApp</h5>
                    <small>Share via WhatsApp</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <i class="fab fa-twitter fa-2x mb-1"></i>
                    <h5 class="mb-0">Twitter</h5>
                    <small>Post property tweets</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body text-center">
                    <i class="fab fa-linkedin-in fa-2x mb-1"></i>
                    <h5 class="mb-0">LinkedIn</h5>
                    <small>Professional network sharing</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h6 class="mb-0"><i class="fas fa-share-alt me-2"></i>Social Sharing Platforms</h6></div>
        <div class="card-body aps-cp-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border h-100">
                        <div class="card-body text-center">
                            <i class="fab fa-facebook-f fa-3x text-primary mb-2"></i>
                            <h6>Facebook</h6>
                            <p class="small text-muted">Share properties to Facebook timeline and groups</p>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(($base ?? BASE_URL)) ?>" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-external-link-alt me-1"></i>Share Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border h-100">
                        <div class="card-body text-center">
                            <i class="fab fa-whatsapp fa-3x text-success mb-2"></i>
                            <h6>WhatsApp</h6>
                            <p class="small text-muted">Directly share properties on WhatsApp</p>
                            <a href="https://wa.me/?text=Check%20out%20properties%20at%20<?= urlencode(($base ?? BASE_URL)) ?>" target="_blank" class="btn btn-sm btn-success"><i class="fas fa-external-link-alt me-1"></i>Share Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border h-100">
                        <div class="card-body text-center">
                            <i class="fab fa-twitter fa-3x text-info mb-2"></i>
                            <h6>Twitter</h6>
                            <p class="small text-muted">Tweet about properties and listings</p>
                            <a href="https://twitter.com/intent/tweet?text=<?= urlencode('Browse amazing properties at ') ?>&url=<?= urlencode(($base ?? BASE_URL)) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-external-link-alt me-1"></i>Tweet Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
