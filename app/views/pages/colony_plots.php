<?php $colony = $colony ?? []; $plots = $plots ?? []; $totalPlots = $totalPlots ?? 0; $current_page = $current_page ?? 1; $total_pages = $total_pages ?? 1; ?>
<style>
.plot-card { border:1px solid #e2e8f0; border-radius:12px; padding:20px; background:#fff; transition:all .2s; height:100%; }
.plot-card:hover { box-shadow:0 4px 20px rgba(0,0,0,0.1); transform:translateY(-2px); }
.plot-card .plot-no { font-size:1.1rem; font-weight:700; color:#1e293b; }
.plot-card .price { font-size:1.3rem; font-weight:700; color:#2563eb; }
.plot-card .detail { color:#64748b; font-size:.9rem; }
.plot-badge { position:absolute; top:12px; right:12px; }
</style>

<div class="bg-light py-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/projects">Projects</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/colony/<?php echo htmlspecialchars($colony['slug'] ?? ''); ?>"><?php echo htmlspecialchars($colony['name'] ?? ''); ?></a></li>
                <li class="breadcrumb-item active">Available Plots</li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1"><i class="fas fa-map-marked-alt text-primary me-2"></i>Available Plots</h3>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($colony['name'] ?? ''); ?> &bull; <?php echo $totalPlots; ?> plot(s) available</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/colony/<?php echo htmlspecialchars($colony['slug'] ?? ''); ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Project</a>
        </div>

        <?php if (empty($plots)): ?>
        <div class="text-center py-5">
            <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
            <h5>No Plots Currently Available</h5>
            <p class="text-muted">All plots in this colony may be sold or booked. Contact us for upcoming releases.</p>
            <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-primary">Contact Us</a>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($plots as $p): ?>
            <div class="col-md-4 col-sm-6">
                <div class="plot-card position-relative">
                    <span class="plot-badge badge bg-success">Available</span>
                    <div class="plot-no mb-1">Plot <?php echo htmlspecialchars($p['plot_number'] ?? 'N/A'); ?></div>
                    <div class="detail mb-2">
                        <?php if ($p['block'] ?? ''): ?><span class="me-3"><i class="fas fa-layer-group me-1"></i><?php echo htmlspecialchars($p['block']); ?></span><?php endif; ?>
                        <span><i class="fas fa-vector-square me-1"></i><?php echo $p['area_sqft'] ?? 0; ?> sqft</span>
                    </div>
                    <?php if ($p['area_sqm'] ?? 0): ?><div class="detail"><?php echo $p['area_sqm']; ?> sqm</div><?php endif; ?>
                    <div class="price mt-2">₹<?php echo number_format($p['total_price'] ?? 0); ?></div>
                    <div class="detail mt-1">₹<?php echo number_format($p['price_per_sqft'] ?? 0); ?>/sqft</div>
                    <a href="<?php echo BASE_URL; ?>/contact?subject=Enquiry%20for%20Plot%20<?php echo urlencode($p['plot_number'] ?? ''); ?>%20-%20<?php echo urlencode($colony['name'] ?? ''); ?>" class="btn btn-sm btn-primary w-100 mt-3"><i class="fas fa-phone me-1"></i>Enquire</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav><ul class="pagination">
                <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a>
                </li>
            </ul></nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
