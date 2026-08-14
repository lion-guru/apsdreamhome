ï»¿<?php
$configs = $configs ?? [];
$rates = $rates ?? [];
$circle_rates = $circle_rates ?? [];
$states = $states ?? [];
$state_filter = $state_filter ?? '';
$total_circle_rates = $total_circle_rates ?? 0;
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-rupee-sign me-2 text-success"></i>Stamp Duty & Circle Rate Config</h4>
            <p class="text-muted mb-0">Configure state-wise stamp duty rates and area-wise circle rates for property valuation</p>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#stampDutyTab">
                <i class="fas fa-percentage me-1"></i>Stamp Duty Rates
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#circleRatesTab">
                <i class="fas fa-map-marked-alt me-1"></i>Circle Rates
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Stamp Duty Tab -->
        <div class="tab-pane fade show active" id="stampDutyTab">
            <!-- Quick Add Form -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add / Update Stamp Duty Rate</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/tools/stamp-duty/save" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="col-md-2">
                            <label class="form-label">State Code</label>
                            <input type="text" name="state_code" class="form-control" placeholder="UP" maxlength="2" required class="style-36130">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Property Type</label>
                            <select name="property_type" class="form-select">
                                <option value="residential">Residential</option>
                                <option value="commercial">Commercial</option>
                                <option value="industrial">Industrial</option>
                                <option value="agricultural">Agricultural</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Stamp Rate (%)</label>
                            <input type="number" name="stamp_rate" class="form-control" step="0.1" min="0" max="20" required placeholder="7.0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Registration (%)</label>
                            <input type="number" name="registration_rate" class="form-control" step="0.1" min="0" max="5" value="1.0">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing Configs Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Configured States (<?= count($configs) ?> rates)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($configs)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-rupee-sign fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No stamp duty configurations found</p>
                            <p class="text-muted small">Add rates using the form above or run the setup script</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>State</th>
                                        <th>Property Type</th>
                                        <th>Stamp Rate</th>
                                        <th>Registration</th>
                                        <th>Total Buyer Cost</th>
                                        <th>Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($configs as $c): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($c['state_code']) ?></strong></td>
                                        <td><span class="badge bg-info"><?= ucfirst($c['property_type']) ?></span></td>
                                        <td><span class="text-primary fw-bold"><?= $c['stamp_rate'] ?>%</span></td>
                                        <td><?= $c['registration_rate'] ?>%</td>
                                        <td><span class="text-success fw-bold"><?= ($c['stamp_rate'] + $c['registration_rate']) ?>%</span></td>
                                        <td><small class="text-muted"><?= date('d M Y', strtotime($c['updated_at'] ?? $c['created_at'] ?? '')) ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Circle Rates Tab -->
        <div class="tab-pane fade" id="circleRatesTab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Circle Rates (<?= number_format($total_circle_rates) ?> entries)</h5>
                    <div class="btn-group btn-group-sm">
                        <a href="<?= BASE_URL ?>/admin/tools/stamp-duty" class="btn btn-<?= $state_filter === '' ? 'primary' : 'outline-primary' ?>">All States</a>
                        <?php foreach ($states as $s): ?>
                            <a href="<?= BASE_URL ?>/admin/tools/stamp-duty?state=<?= urlencode($s) ?>" class="btn btn-<?= $state_filter === $s ? 'primary' : 'outline-primary' ?>"><?= $s ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($circle_rates)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-map fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No circle rates found. Run <code>fix_circle_rates.php</code> to seed data.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>State</th>
                                        <th>District</th>
                                        <th>Area Type</th>
                                        <th>Rate/sqft (â‚¹)</th>
                                        <th>Rate/sqm (â‚¹)</th>
                                        <th>Effective From</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($circle_rates as $cr): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($cr['state_code']) ?></strong></td>
                                        <td><?= htmlspecialchars($cr['district']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($cr['area_type']) ?></span></td>
                                        <td class="text-success fw-bold">â‚¹<?= number_format($cr['rate_per_sqft'] ?? 0) ?></td>
                                        <td>â‚¹<?= number_format($cr['rate_per_sqm'] ?? 0) ?></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($cr['effective_from'] ?? '') ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
