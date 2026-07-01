<?php
$page_title = $page_title ?? 'Buyer Dashboard';
$base = defined('BASE_URL') ? BASE_URL : '';
$interests = $interests ?? [];
$matched = $matched ?? [];
$total_interests = $total_interests ?? 0;
$total_matched = $total_matched ?? 0;
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<style>
    .buyer-card { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; margin-bottom: 12px; }
    .buyer-card h6 { font-weight: 700; color: #0d9488; margin-bottom: 10px; }
    .stat-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; }
    .match-card { border-left: 4px solid #10b981; }
    .match-card img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; }
</style>

<div class="container-fluid px-3 py-3" style="max-width: 700px; margin: 0 auto;">
    <h5 class="fw-bold mb-3"><i class="fas fa-home me-2 text-primary"></i>Buyer Dashboard</h5>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show py-2"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row g-2 mb-3">
        <div class="col-6">
            <div class="buyer-card text-center">
                <div style="font-size:1.8rem;font-weight:700;color:#6366f1;"><?= $total_interests ?></div>
                <div class="text-muted small">My Requirements</div>
            </div>
        </div>
        <div class="col-6">
            <div class="buyer-card text-center">
                <div style="font-size:1.8rem;font-weight:700;color:#10b981;"><?= $total_matched ?></div>
                <div class="text-muted small">Matched Properties</div>
            </div>
        </div>
    </div>

    <!-- Submit Interest -->
    <div class="buyer-card">
        <h6><i class="fas fa-plus-circle me-2"></i>Post Your Requirement</h6>
        <form action="<?= $base ?>/buyer/interest/submit" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label fw-bold small">Property Type *</label>
                    <select name="property_type" class="form-select form-select-sm" required>
                        <option value="plot">Plot</option>
                        <option value="house">House</option>
                        <option value="flat">Flat</option>
                        <option value="shop">Shop</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small">Bedrooms</label>
                    <select name="bedrooms_needed" class="form-select form-select-sm">
                        <option value="0">Any</option>
                        <option value="1">1 BHK</option>
                        <option value="2">2 BHK</option>
                        <option value="3">3 BHK</option>
                        <option value="4">4+ BHK</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small">Budget Min (₹)</label>
                    <input type="number" name="budget_min" class="form-control form-control-sm" placeholder="e.g. 1000000">
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small">Budget Max (₹)</label>
                    <input type="number" name="budget_max" class="form-control form-control-sm" placeholder="e.g. 5000000">
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small">Preferred Location</label>
                    <input type="text" name="preferred_location" class="form-control form-control-sm" placeholder="e.g. Mathura">
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small">Area (sq.ft.)</label>
                    <input type="number" name="area_min" class="form-control form-control-sm" placeholder="Min area">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold small">Requirements</label>
                    <textarea name="requirements" class="form-control form-control-sm" rows="2" placeholder="Specific requirements..."></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100 mt-3 py-2" style="font-weight:700;">
                <i class="fas fa-paper-plane me-2"></i>Submit Requirement
            </button>
        </form>
    </div>

    <!-- Matched Properties -->
    <?php if (!empty($matched)): ?>
        <div class="buyer-card">
            <h6><i class="fas fa-check-circle me-2 text-success"></i>Matched Properties</h6>
            <?php foreach ($matched as $m): ?>
                <div class="d-flex gap-3 p-2 mb-2 border rounded match-card">
                    <?php if ($m['image']): ?>
                        <img src="<?= $base ?>/assets/images/<?= htmlspecialchars($m['image']) ?>" alt="Property">
                    <?php else: ?>
                        <div style="width:80px;height:80px;background:#f3f4f6;border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-home fa-2x text-muted"></i></div>
                    <?php endif; ?>
                    <div class="flex-grow-1">
                        <div class="fw-bold small"><?= htmlspecialchars($m['title'] ?? $m['property_type']) ?></div>
                        <div class="text-muted" style="font-size:0.75rem;"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($m['address'] ?? '') ?></div>
                        <div class="fw-bold text-primary mt-1">₹<?= number_format((float)$m['price']) ?></div>
                        <a href="<?= $base ?>/properties/<?= $m['id'] ?>" class="btn btn-outline-primary btn-sm mt-1" style="font-size:0.75rem;">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- My Requirements -->
    <?php if (!empty($interests)): ?>
        <div class="buyer-card">
            <h6><i class="fas fa-list me-2"></i>My Requirements</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Type</th><th>Budget</th><th>Location</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($interests as $i): ?>
                            <tr>
                                <td class="small fw-bold"><?= ucfirst($i['property_type']) ?></td>
                                <td class="small">₹<?= number_format((float)$i['budget_min']) ?> - ₹<?= number_format((float)$i['budget_max']) ?></td>
                                <td class="small"><?= htmlspecialchars($i['preferred_location'] ?: '-') ?></td>
                                <td><span class="badge bg-<?= $i['status'] === 'matched' ? 'success' : ($i['status'] === 'active' ? 'primary' : 'secondary') ?>"><?= ucfirst($i['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
