<style>
.inv-card { border-radius: 16px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.06); transition: 0.3s; }
.inv-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
.status-badge { padding: 0.3rem 0.8rem; border-radius: 50px; font-size: 0.78rem; font-weight: 600; }
.status-available { background: #dbeafe; color: #1d4ed8; }
.status-reserved { background: #fef3c7; color: #d97706; }
.status-booked { background: #e0e7ff; color: #4338ca; }
.status-sold { background: #d1fae5; color: #059669; }
.status-under_development { background: #f3e8ff; color: #0f766e; }
.status-hold { background: #fce7f3; color: #be185d; }
.status-blocked { background: #fef2f2; color: #b91c1c; }
.status-under_construction { background: #fff7ed; color: #c2410c; }

</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-cubes me-2"></i>Plot Inventory</h4>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/admin/erp/plot-profit" class="btn btn-outline-primary btn-sm"><i class="fas fa-chart-line me-1"></i>P&L Report</a>
        <a href="<?php echo BASE_URL; ?>/admin/erp/land-mapping" class="btn btn-outline-secondary btn-sm"><i class="fas fa-map me-1"></i>Land Mapping</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php $colors = ['available'=>'primary', 'reserved'=>'warning', 'booked'=>'info', 'sold'=>'success', 'under_development'=>'info']; ?>
    <?php foreach (['available','reserved','booked','sold','under_development'] as $s): ?>
    <div class="col-md-2 col-6">
        <div class="card inv-card border-start border-4 border-<?php echo e($colors[$s]); ?>">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold"><?php echo (int)($stats[$s] ?? 0); ?></div>
                <div class="text-muted small text-uppercase"><?php echo ucfirst(str_replace('_', ' ', $s)); ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <div class="col-md-2 col-6">
        <div class="card inv-card border-start border-4 border-dark">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold"><?php echo (int)($stats['total'] ?? 0); ?></div>
                <div class="text-muted small text-uppercase">Total</div>
            </div>
        </div>
    </div>
</div>

<div class="card inv-card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <form class="d-flex gap-2" method="GET">
    <?php echo CSRFProtection::csrfField(); ?>
            <select name="status" class="form-select form-select-sm" class="style-68062" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="available" <?php echo ($_GET['status']??'')==='available'?'selected':''; ?>>Available</option>
                <option value="reserved" <?php echo ($_GET['status']??'')==='reserved'?'selected':''; ?>>Reserved</option>
                <option value="booked" <?php echo ($_GET['status']??'')==='booked'?'selected':''; ?>>Booked</option>
                <option value="sold" <?php echo ($_GET['status']??'')==='sold'?'selected':''; ?>>Sold</option>
            </select>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search plot #, colony..." value="<?php echo htmlspecialchars($_GET['search']??''); ?>">
            <button class="btn btn-sm btn-outline-primary" aria-label="Search"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Plot #</th>
                    <th>Colony</th>
                    <th>Block</th>
                    <th>Area (sqft)</th>
                    <th>Price (₹)</th>
                    <th>Status</th>
                    <th>Customer</th>
                    <th>Paid (₹)</th>
                    <th>Booking</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($plots)): ?>
                <tr><td colspan="9" class="text-center py-4 text-muted">No plots found.</td></tr>
                <?php else: ?>
                <?php foreach ($plots as $p): ?>
                <tr>
                    <td class="fw-semibold"><?php echo htmlspecialchars($p['plot_number'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($p['colony_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($p['block'] ?? ($p['sector'] ?? '-')); ?></td>
                    <td><?php echo number_format((float)($p['area_sqft'] ?? 0)); ?></td>
                    <td>₹<?php echo number_format((float)($p['total_price'] ?? 0), 0); ?></td>
                    <td><span class="status-badge status-<?php echo $p['status'] ?? 'available'; ?>"><?php echo ucfirst(str_replace('_', ' ', $p['status'] ?? 'available')); ?></span></td>
                    <td>
                        <?php if (!empty($p['customer_name'])): ?>
                        <span class="fw-semibold"><?php echo htmlspecialchars($p['customer_name'] ?? ''); ?></span><br>
                        <small class="text-muted"><?php echo htmlspecialchars($p['customer_phone'] ?? ''); ?></small>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>₹<?php echo number_format((float)($p['amount_paid'] ?? 0), 0); ?></td>
                    <td>
                        <?php if (!empty($p['booking_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/bookings/<?php echo e($p['booking_id']); ?>" class="btn btn-sm btn-outline-info">
                            <?php echo htmlspecialchars($p['booking_number'] ?? 'BK#' . $p['booking_id']); ?>
                            <?php if (($p['booking_status'] ?? '') === 'confirmed'): ?>
                            <i class="fas fa-check-circle text-success ms-1"></i>
                            <?php endif; ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
