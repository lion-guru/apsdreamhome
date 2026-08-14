<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fas fa-database me-2"></i> Extracted Leads</h3>
        <div>
            <a href="<?= BASE_URL ?>/admin/voice-users/dashboard" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <h6 class="text-white-50 small">Total Extracted</h6>
                    <h3 class="mb-0 fw-bold"><?= $total_extracted ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <h6 class="text-white-50 small">Verified</h6>
                    <h3 class="mb-0 fw-bold"><?= $verified ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <h6 class="text-white-50 small">Converted to Leads</h6>
                    <h3 class="mb-0 fw-bold"><?= $converted_to_leads ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <h6 class="text-dark-50 small">Pending Review</h6>
                    <h3 class="mb-0 fw-bold"><?= $pending_review ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/voice-users/extracted-leads">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Interest Level</label>
                        <select name="interest_level" class="form-select">
                            <option value="">All</option>
                            <option value="hot" <?= ($filters['interest_level'] ?? '') === 'hot' ? 'selected' : '' ?>>Hot</option>
                            <option value="warm" <?= ($filters['interest_level'] ?? '') === 'warm' ? 'selected' : '' ?>>Warm</option>
                            <option value="cold" <?= ($filters['interest_level'] ?? '') === 'cold' ? 'selected' : '' ?>>Cold</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Timeline</label>
                        <select name="timeline" class="form-select">
                            <option value="">All</option>
                            <option value="immediate" <?= ($filters['timeline'] ?? '') === 'immediate' ? 'selected' : '' ?>>Immediate</option>
                            <option value="1_month" <?= ($filters['timeline'] ?? '') === '1_month' ? 'selected' : '' ?>>1 Month</option>
                            <option value="3_months" <?= ($filters['timeline'] ?? '') === '3_months' ? 'selected' : '' ?>>3 Months</option>
                            <option value="6_months" <?= ($filters['timeline'] ?? '') === '6_months' ? 'selected' : '' ?>>6 Months</option>
                            <option value="not_sure" <?= ($filters['timeline'] ?? '') === 'not_sure' ? 'selected' : '' ?>>Not Sure</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Min Quality Score</label>
                        <select name="quality_min" class="form-select">
                            <option value="">Any</option>
                            <option value="80" <?= ($filters['quality_min'] ?? '') == 80 ? 'selected' : '' ?>>80+</option>
                            <option value="60" <?= ($filters['quality_min'] ?? '') == 60 ? 'selected' : '' ?>>60+</option>
                            <option value="40" <?= ($filters['quality_min'] ?? '') == 40 ? 'selected' : '' ?>>40+</option>
                            <option value="20" <?= ($filters['quality_min'] ?? '') == 20 ? 'selected' : '' ?>>20+</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
                        <a href="<?= BASE_URL ?>/admin/voice-users/extracted-leads" class="btn btn-outline-secondary btn-sm">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="card">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-bold">Extracted Leads</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Name</th>
                            <th class="small">Phone</th>
                            <th class="small">Budget</th>
                            <th class="small">Location</th>
                            <th class="small text-center">Interest</th>
                            <th class="small text-center">Timeline</th>
                            <th class="small text-center">Quality</th>
                            <th class="small text-center">Verified</th>
                            <th class="small text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leads)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No extracted leads found</td></tr>
                        <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td class="fw-medium"><?= htmlspecialchars($lead['extracted_name'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($lead['extracted_phone'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($lead['extracted_budget'] ?? '-') ?></td>
                            <td class="small"><?= htmlspecialchars($lead['extracted_location'] ?? '-') ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= ($lead['interest_level'] ?? '') === 'hot' ? 'danger' : (($lead['interest_level'] ?? '') === 'warm' ? 'warning' : 'info') ?>">
                                    <?= ucfirst($lead['interest_level'] ?? 'cold') ?>
                                </span>
                            </td>
                            <td class="text-center small"><?= ucfirst(str_replace('_', ' ', $lead['buying_timeline'] ?? '-')) ?></td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="progress" class="style-2489">
                                        <?php $score = (int)($lead['quality_score'] ?? 0); ?>
                                        <div class="progress-bar bg-<?= $score >= 70 ? 'success' : ($score >= 40 ? 'warning' : 'danger') ?>" class="style-12479"></div>
                                    </div>
                                    <small class="ms-1"><?= $score ?></small>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($lead['is_verified'] ?? 0): ?>
                                <span class="badge bg-success">Verified</span>
                                <?php elseif ($lead['auto_created_lead_id'] ?? 0): ?>
                                <span class="badge bg-info">Converted</span>
                                <?php else: ?>
                                <span class="badge bg-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (!($lead['auto_created_lead_id'] ?? 0)): ?>
                                <button class="btn btn-sm btn-success convert-lead-btn" data-id="<?= $lead['id'] ?>" data-name="<?= htmlspecialchars($lead['extracted_name'] ?? '') ?>">
                                    <i class="fas fa-user-plus"></i> Convert
                                </button>
                                <?php else: ?>
                                <a href="<?= BASE_URL ?>/admin/leads/<?= $lead['auto_created_lead_id'] ?>" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.convert-lead-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var name = this.dataset.name;
            if (!confirm('Convert "' + name + '" to a lead in the CRM?')) return;
            var self = this;
            self.disabled = true;
            self.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Converting...';
            var formData = new FormData();
            formData.append('extracted_id', id);
            fetch('<?= BASE_URL ?>/admin/voice-users/ajax/convert-lead', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var tr = self.closest('tr');
                    if (tr) {
                        var td = tr.querySelector('td:last-child');
                        td.innerHTML = '<span class="badge bg-success">Converted</span>';
                    }
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(function(err) {
                alert('Network error: ' + err.message);
            })
            .finally(function() {
                self.disabled = false;
                self.innerHTML = '<i class="fas fa-user-plus"></i> Convert';
            });
        });
    });
});
</script>
