<?php
$page_title = $page_title ?? __('assoc_browse_title', [], 'Browse Properties');
$properties = $properties ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$current_filters = $current_filters ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="fas fa-search text-primary me-2"></i><?= __('assoc_browse_title', [], 'Browse Properties') ?></h4>
            <small class="text-muted"><?= __('assoc_browse_count', ['count' => number_format($total)], '%count% properties available') ?></small>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= $base ?>/associate/browse" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" name="q" placeholder="<?= __('assoc_browse_search', [], 'Search properties...') ?>" value="<?= htmlspecialchars($current_filters['q'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="type">
                        <option value=""><?= __('assoc_browse_all_types', [], 'All Types') ?></option>
                        <?php foreach (['plot','house','flat','shop','farmhouse','villa','land'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($current_filters['type'] ?? '') === $t ? 'selected' : '' ?>><?= __("assoc_browse_type_$t", [], ucfirst($t)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="listing">
                        <option value=""><?= __('assoc_browse_all_listings', [], 'Buy & Rent') ?></option>
                        <option value="sell" <?= ($current_filters['listing'] ?? '') === 'sell' ? 'selected' : '' ?>><?= __('assoc_browse_for_sale', [], 'For Sale') ?></option>
                        <option value="rent" <?= ($current_filters['listing'] ?? '') === 'rent' ? 'selected' : '' ?>><?= __('assoc_browse_for_rent', [], 'For Rent') ?></option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="sort">
                        <option value="newest" <?= ($current_filters['sort'] ?? 'newest') === 'newest' ? 'selected' : '' ?>><?= __('assoc_browse_newest', [], 'Newest First') ?></option>
                        <option value="price_low" <?= ($current_filters['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>><?= __('assoc_browse_price_low', [], 'Price: Low to High') ?></option>
                        <option value="price_high" <?= ($current_filters['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>><?= __('assoc_browse_price_high', [], 'Price: High to Low') ?></option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button>
                </div>
                <div class="col-md-1">
                    <a href="<?= $base ?>/associate/browse" class="btn btn-outline-secondary btn-sm w-100"><?= __('assoc_browse_reset', [], 'Reset') ?></a>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($properties)): ?>
    <div class="row">
        <?php foreach ($properties as $property): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 property-card" style="transition: transform 0.2s, box-shadow 0.2s;">
                <div class="position-relative">
                    <?php
                        $imgSrc = $base . '/assets/images/properties/' . htmlspecialchars($property['image'] ?? '');
                        if (empty($property['image'])) {
                            $imgSrc = $base . '/assets/images/placeholder/property.svg';
                        }
                    ?>
                    <img src="<?= $imgSrc ?>" class="card-img-top" alt="<?= htmlspecialchars($property['name'] ?? '') ?>"
                         style="height:200px;object-fit:cover;"
                         onerror="this.src='<?= $base ?>/assets/images/placeholder/property.svg'">
                    <span class="position-absolute top-0 end-0 badge bg-<?= ($property['listing_type'] ?? 'sell') === 'rent' ? 'info' : 'success' ?> m-2">
                        <?= ucfirst($property['listing_type'] ?? __('assoc_browse_sell', [], 'Sell')) ?>
                    </span>
                </div>
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title fw-bold"><?= htmlspecialchars($property['name'] ?? __('assoc_browse_untitled', [], 'Untitled')) ?></h6>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($property['address'] ?? '') ?>
                    </p>
                    <div class="row small text-center border-top border-bottom py-2 mb-3 g-1">
                        <?php if (!empty($property['area_sqft'])): ?>
                        <div class="col">
                            <i class="fas fa-vector-square text-muted"></i><br>
                            <strong><?= number_format($property['area_sqft']) ?></strong> <?= __('assoc_browse_sqft', [], 'sqft') ?>
                        </div>
                        <?php endif; ?>
                        <div class="col">
                            <i class="fas fa-home text-muted"></i><br>
                            <strong><?= ucfirst($property['property_type'] ?? __('assoc_browse_plot', [], 'Plot')) ?></strong>
                        </div>
                        <?php if (!empty($property['bedrooms'])): ?>
                        <div class="col">
                            <i class="fas fa-bed text-muted"></i><br>
                            <strong><?= (int)$property['bedrooms'] ?></strong> BHK
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-success fw-bold fs-5">&#8377;<?= number_format($property['price'] ?? 0) ?></span>
                            <button class="btn btn-sm btn-primary interest-btn"
                                    data-id="<?= $property['id'] ?>"
                                    data-name="<?= htmlspecialchars($property['name'] ?? '') ?>"
                                    onclick="showInterestModal(this)">
                                <i class="fas fa-hand-pointer me-1"></i><?= __('assoc_browse_interested', [], "I'm Interested") ?>
                            </button>
                        </div>
                        <small class="text-muted"><i class="fas fa-eye me-1"></i><?= (int)($property['views'] ?? 0) ?> <?= __('assoc_browse_views', [], 'views') ?></small>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav aria-label="<?= __('assoc_browse_pagination', [], 'Property pagination') ?>" class="mt-3">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($current_filters, ['page' => $page - 1])) ?>">
                        <i class="fas fa-chevron-left"></i> <?= __('assoc_browse_prev', [], 'Prev') ?>
                    </a>
                </li>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($current_filters, ['page' => $i])) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($current_filters, ['page' => $page + 1])) ?>">
                        <?= __('assoc_browse_next', [], 'Next') ?> <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <h5 class="text-muted"><?= __('assoc_browse_no_results', [], 'No properties found') ?></h5>
            <p class="text-muted"><?= __('assoc_browse_no_results_desc', [], 'Try adjusting your filters') ?></p>
            <a href="<?= $base ?>/associate/browse" class="btn btn-primary"><?= __('assoc_browse_view_all', [], 'View All Properties') ?></a>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="interestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-bottom modal-dialog-centered" style="max-width:480px;margin:0 auto;">
        <div class="modal-content" style="border-radius:16px 16px 0 0;">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h6 class="fw-bold mb-0"><?= __('assoc_browse_im_interested', [], "I'm Interested") ?></h6>
                    <small class="text-muted" id="interestPropertyName"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="interestForm" onsubmit="submitInterest(event)">
                    <input type="hidden" name="property_id" id="interestPropertyId">
                    <input type="hidden" name="source" value="associate_browse">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold"><?= __('assoc_browse_phone', [], 'Phone Number') ?> *</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210" required
                               value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold"><?= __('assoc_browse_name', [], 'Your Name') ?></label>
                        <input type="text" name="name" class="form-control" placeholder="<?= __('assoc_browse_name_placeholder', [], 'Enter your name') ?>"
                               value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold"><?= __('assoc_browse_budget', [], 'Budget Range') ?></label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ([__('assoc_browse_budget_1', [], 'Under 10L'),__('assoc_browse_budget_2', [], '10L-25L'),__('assoc_browse_budget_3', [], '25L-50L'),__('assoc_browse_budget_4', [], '50L-1Cr'),__('assoc_browse_budget_5', [], '1Cr+')] as $budget): ?>
                            <button type="button" class="btn btn-outline-primary btn-sm budget-chip" onclick="selectBudget(this)"><?= $budget ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="budget" id="interestBudget">
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="interestSubmitBtn">
                        <i class="fas fa-paper-plane me-1"></i><?= __('assoc_browse_submit', [], 'Submit Interest') ?>
                    </button>
                </form>
                <div id="interestSuccess" class="text-center py-3" style="display:none;">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <h6 class="fw-bold"><?= __('assoc_browse_success', [], 'Interest Recorded!') ?></h6>
                    <p class="text-muted small mb-0"><?= __('assoc_browse_success_desc', [], 'Our team will contact you shortly.') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.property-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.12) !important; }
.budget-chip.active { background: #c2410c; color: #fff; border-color: #c2410c; }
@media (min-width: 576px) {
    .modal-dialog-bottom { align-items: flex-end; }
    .modal-dialog-bottom .modal-content { border-radius: 16px 16px 0 0; }
}
</style>

<script>
function showInterestModal(btn) {
    document.getElementById('interestPropertyId').value = btn.dataset.id;
    document.getElementById('interestPropertyName').textContent = btn.dataset.name;
    document.getElementById('interestForm').style.display = 'block';
    document.getElementById('interestSuccess').style.display = 'none';
    new bootstrap.Modal(document.getElementById('interestModal')).show();
}

function selectBudget(el) {
    document.querySelectorAll('.budget-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('interestBudget').value = el.textContent;
}

function submitInterest(e) {
    e.preventDefault();
    const form = document.getElementById('interestForm');
    const btn = document.getElementById('interestSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i><?= __('assoc_browse_submitting', [], 'Submitting...') ?>';

    const fd = new FormData(form);
    fetch('<?= $base ?>/property/interest', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                form.style.display = 'none';
                document.getElementById('interestSuccess').style.display = 'block';
                setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('interestModal')).hide(), 2500);
            } else {
                alert(data.message || '<?= __('assoc_browse_error', [], 'Something went wrong. Please try again.') ?>');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i><?= __('assoc_browse_submit', [], 'Submit Interest') ?>';
            }
        })
        .catch(() => {
            alert('<?= __('assoc_browse_network_error', [], 'Network error. Please try again.') ?>');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i><?= __('assoc_browse_submit', [], 'Submit Interest') ?>';
        });
}
</script>
