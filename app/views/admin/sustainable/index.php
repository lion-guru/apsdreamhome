<?php
$page_title = $page_title ?? 'Sustainable Tech';
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-leaf me-2 text-success"></i>Sustainable Tech & Green Real Estate</h2>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body"><h6 class="text-uppercase small">Certifications</h6><h3><?= $cert_count ?? 0 ?></h3></div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body"><h6 class="text-uppercase small">Green Features</h6><h3><?= $feature_count ?? 0 ?></h3></div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body"><h6 class="text-uppercase small">Energy Audits</h6><h3><?= $audit_count ?? 0 ?></h3></div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body"><h6 class="text-uppercase small">Carbon Credits</h6><h3><?= number_format($total_credits ?? 0, 1) ?></h3></div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card border-success h-100">
            <div class="card-header bg-success text-white"><i class="fas fa-link me-1"></i> Quick Links</div>
            <div class="card-body d-grid gap-2">
                <a href="<?= BASE_URL ?>/admin/sustainable/certifications" class="btn btn-outline-success"><i class="fas fa-certificate me-1"></i> Manage Certifications</a>
                <a href="<?= BASE_URL ?>/admin/sustainable/features" class="btn btn-outline-info"><i class="fas fa-seedling me-1"></i> Green Features Catalog</a>
                <a href="<?= BASE_URL ?>/admin/sustainable/audits" class="btn btn-outline-primary"><i class="fas fa-bolt me-1"></i> Energy Audits</a>
                <a href="<?= BASE_URL ?>/admin/sustainable/carbon" class="btn btn-outline-warning"><i class="fas fa-globe me-1"></i> Carbon Credit Ledger</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-chart-pie me-1"></i> Environmental Impact</div>
            <div class="card-body">
                <div class="d-flex justify-content-between border-bottom py-2"><span>CO₂ saved by features (kg/yr total)</span><strong><?= number_format($total_co2_features ?? 0) ?></strong></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span>Total carbon credits</span><strong><?= number_format($total_credits ?? 0, 1) ?></strong></div>
                <div class="d-flex justify-content-between py-2"><span>Credit portfolio value</span><strong>₹<?= number_format($total_value ?? 0, 2) ?></strong></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Recent Energy Audits</h6></div>
            <div class="card-body p-0">
                <?php if (empty($audits)): ?>
                    <p class="text-muted text-center py-3">No audits yet.</p>
                <?php else: ?>
                    <table class="table mb-0"><tbody>
                    <?php foreach ($audits as $a): ?>
                        <tr><td><strong><?= htmlspecialchars($a['project_name'] ?? 'Unnamed') ?></strong><br><small class="text-muted"><?= htmlspecialchars($a['audit_date'] ?? '') ?></small></td>
                        <td class="text-end"><span class="badge bg-primary"><?= $a['energy_score'] ?? '—' ?> score</span></td></tr>
                    <?php endforeach; ?>
                    </tbody></table>
                <?php endif; ?>
                <div class="card-footer"><a href="<?= BASE_URL ?>/admin/sustainable/audits" class="btn btn-sm btn-outline-primary">View all</a></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Recent Carbon Credits</h6></div>
            <div class="card-body p-0">
                <?php if (empty($carbon)): ?>
                    <p class="text-muted text-center py-3">No credits yet.</p>
                <?php else: ?>
                    <table class="table mb-0"><tbody>
                    <?php foreach ($carbon as $c): ?>
                        <tr><td><strong><?= htmlspecialchars($c['credit_type'] ?? 'General') ?></strong><br><small class="text-muted"><?= htmlspecialchars($c['credit_date'] ?? '') ?></small></td>
                        <td class="text-end"><span class="badge bg-warning"><?= number_format($c['credits_earned'] ?? 0, 1) ?> t</span></td></tr>
                    <?php endforeach; ?>
                    </tbody></table>
                <?php endif; ?>
                <div class="card-footer"><a href="<?= BASE_URL ?>/admin/sustainable/carbon" class="btn btn-sm btn-outline-warning">View ledger</a></div>
            </div>
        </div>
    </div>
</div>
