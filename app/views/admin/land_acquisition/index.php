<?php
/**
 * Farmer Land Acquisition & Bainama Milestones Desk Admin Index View
 */
$acquisitions = $acquisitions ?? [];
$stats = $stats ?? [];
$colonies = $colonies ?? [];
$current_colony = $current_colony ?? null;
$current_status = $current_status ?? 'all';
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-mountain text-primary me-2"></i>Farmer Land Acquisition &amp; Bainama Desk</h3>
            <p class="text-muted small mb-0">Agricultural land aggregation, Section 143 non-agri mutation tracking &amp; staged farmer compensation (Purvanchal Zone)</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAcquisitionModal">
                <i class="fas fa-plus me-1"></i>New Land Acquisition Deal
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Total Land Acquired</div>
                <div class="fs-3 fw-bold text-primary mt-1"><?= number_format((float)($stats['total_bigha'] ?? 0), 2) ?> <span class="fs-6 text-muted">Bigha</span></div>
                <div class="text-muted small mt-1"><?= number_format((float)($stats['total_sqft'] ?? 0), 0) ?> sq.ft across <?= (int)($stats['total_deals'] ?? 0) ?> deals</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Total Deal Value</div>
                <div class="fs-3 fw-bold text-dark mt-1">₹<?= number_format((float)($stats['total_committed'] ?? 0), 2) ?></div>
                <div class="text-muted small mt-1">Total agreed acquisition consideration</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Total Disbursed</div>
                <div class="fs-3 fw-bold text-success mt-1">₹<?= number_format((float)($stats['total_disbursed'] ?? 0), 2) ?></div>
                <div class="text-muted small mt-1">Paid through RTGS / NEFT vouchers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Pending Milestone Dues</div>
                <div class="fs-3 fw-bold text-warning mt-1">₹<?= number_format((float)($stats['total_pending'] ?? 0), 2) ?></div>
                <div class="text-muted small mt-1"><?= (int)($stats['completed'] ?? 0) ?> deeds registered</div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="/admin/farmer-acquisitions" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label for="filter_colony" class="visually-hidden">Target Township Colony</label>
                    <select name="colony_id" id="filter_colony" class="form-select form-select-sm">
                        <option value="">All Township Colonies</option>
                        <?php foreach ($colonies as $col): ?>
                            <option value="<?= (int)$col['id'] ?>" <?= ($current_colony == $col['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($col['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_status" class="visually-hidden">Status</label>
                    <select name="status" id="filter_status" class="form-select form-select-sm">
                        <option value="all" <?= ($current_status === 'all') ? 'selected' : '' ?>>All Statuses</option>
                        <option value="bayana_agreement" <?= ($current_status === 'bayana_agreement') ? 'selected' : '' ?>>Bayana Agreement</option>
                        <option value="mutation_in_progress" <?= ($current_status === 'mutation_in_progress') ? 'selected' : '' ?>>Sec 143 Mutation</option>
                        <option value="possession_taken" <?= ($current_status === 'possession_taken') ? 'selected' : '' ?>>Possession Taken</option>
                        <option value="registry_completed" <?= ($current_status === 'registry_completed') ? 'selected' : '' ?>>Registry Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
                <?php if ($current_colony || $current_status !== 'all'): ?>
                    <div class="col-md-2">
                        <a href="/admin/farmer-acquisitions" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Acquisitions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Deal Token</th>
                            <th>Mauza / Village &amp; Gata</th>
                            <th>Farmer / Landholder</th>
                            <th>Area (Bigha / Sq.Ft)</th>
                            <th>Total Deal Value</th>
                            <th>Disbursed</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($acquisitions)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-handshake-slash fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No land acquisition deals found matching criteria.
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newAcquisitionModal">
                                            Register First Land Acquisition
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($acquisitions as $deal): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold font-monospace text-dark"><?= htmlspecialchars($deal['deal_token']) ?></div>
                                        <div class="small text-muted"><?= date('d M Y', strtotime($deal['agreement_date'])) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">Gata #<?= htmlspecialchars($deal['gata_number']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($deal['mauza_village']) ?>, <?= htmlspecialchars($deal['tehsil']) ?></div>
                                        <?php if (!empty($deal['colony_name'])): ?>
                                            <span class="badge bg-light text-primary border mt-1"><?= htmlspecialchars($deal['colony_name']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($deal['farmer_name']) ?></div>
                                        <div class="small text-muted"><i class="fas fa-phone-alt me-1 text-secondary"></i><?= htmlspecialchars($deal['farmer_phone']) ?></div>
                                        <?php if (!empty($deal['khatauni_verified'])): ?>
                                            <span class="badge bg-success-subtle text-success small"><i class="fas fa-check-circle me-1"></i>Khatauni Verified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold fs-6 text-dark"><?= (float)$deal['area_bigha'] ?> Bigha</div>
                                        <div class="small text-muted"><?= number_format((float)$deal['area_sqft'], 0) ?> sq.ft</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">₹<?= number_format((float)$deal['total_deal_amount'], 2) ?></div>
                                        <div class="small text-muted">@ ₹<?= number_format((float)$deal['rate_per_bigha'], 2) ?>/Bigha</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-success">₹<?= number_format((float)$deal['paid_amount'], 2) ?></div>
                                        <?php 
                                            $pct = ($deal['total_deal_amount'] > 0) ? round(($deal['paid_amount'] / $deal['total_deal_amount']) * 100, 1) : 0;
                                        ?>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pct ?>%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($deal['status'] === 'registry_completed'): ?>
                                            <span class="badge bg-success"><i class="fas fa-file-contract me-1"></i>Registry Done</span>
                                        <?php elseif ($deal['status'] === 'possession_taken'): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-flag me-1"></i>Possession Taken</span>
                                        <?php elseif ($deal['status'] === 'mutation_in_progress'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-sync-alt me-1"></i>Sec 143 Mutation</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-handshake me-1"></i>Bayana Agreement</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="/admin/farmer-acquisitions/<?= $deal['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-list-ol me-1"></i>Milestones
                                        </a>
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

<!-- New Acquisition Modal -->
<div class="modal fade" id="newAcquisitionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/admin/farmer-acquisitions">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-handshake text-primary me-2"></i>Register New Farmer Land Acquisition Deal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="acq_colony" class="form-label fw-semibold">Target Expansion Township Colony</label>
                            <select name="target_colony_id" id="acq_colony" class="form-select">
                                <option value="">Select Colony (Optional)...</option>
                                <?php foreach ($colonies as $col): ?>
                                    <option value="<?= (int)$col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="acq_gata" class="form-label fw-semibold">Gata / Khasra Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="acq_gata" name="gata_number" placeholder="e.g. Gata 244/1 &amp; 245" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="acq_mauza" class="form-label fw-semibold">Mauza / Village <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="acq_mauza" name="mauza_village" placeholder="e.g. जंगल कौड़िया" required>
                        </div>
                        <div class="col-md-4">
                            <label for="acq_tehsil" class="form-label fw-semibold">Tehsil</label>
                            <input type="text" class="form-control" id="acq_tehsil" name="tehsil" value="Sadar">
                        </div>
                        <div class="col-md-4">
                            <label for="acq_fasli" class="form-label">Fasli Record Year</label>
                            <input type="text" class="form-control" id="acq_fasli" name="fasli_year" value="1431-1436 Fasli">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="acq_bigha" class="form-label fw-semibold">Land Area in Bigha <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" class="form-control" id="acq_bigha" name="area_bigha" placeholder="e.g. 2.5000" required>
                            <div class="form-text small">1 Pucca Bigha = 27,225 sq.ft</div>
                        </div>
                        <div class="col-md-6">
                            <label for="acq_rate" class="form-label fw-semibold">Agreed Rate per Bigha (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="acq_rate" name="rate_per_bigha" placeholder="e.g. 4500000" required>
                        </div>
                    </div>

                    <hr class="text-muted">
                    <h6 class="fw-bold text-primary mb-3">Farmer / Landholder Identity &amp; Banking Details</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="acq_farmer_name" class="form-label fw-semibold">Farmer Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="acq_farmer_name" name="farmer_name" placeholder="e.g. राम प्रसाद मौर्य" required>
                        </div>
                        <div class="col-md-4">
                            <label for="acq_farmer_phone" class="form-label fw-semibold">Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="acq_farmer_phone" name="farmer_phone" placeholder="e.g. 9839123456" required>
                        </div>
                        <div class="col-md-4">
                            <label for="acq_farmer_aadhaar" class="form-label">Aadhaar Number</label>
                            <input type="text" class="form-control" id="acq_farmer_aadhaar" name="farmer_aadhaar" placeholder="e.g. 1234 5678 9012">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="acq_bank_acc" class="form-label">Bank Account No (RTGS)</label>
                            <input type="text" class="form-control" id="acq_bank_acc" name="bank_account_no" placeholder="e.g. 302910123456">
                        </div>
                        <div class="col-md-4">
                            <label for="acq_bank_ifsc" class="form-label">IFSC Code</label>
                            <input type="text" class="form-control" id="acq_bank_ifsc" name="bank_ifsc" placeholder="e.g. SBIN0001234">
                        </div>
                        <div class="col-md-4">
                            <label for="acq_bank_name" class="form-label">Bank Name</label>
                            <input type="text" class="form-control" id="acq_bank_name" name="bank_name" placeholder="e.g. SBI Gorakhpur">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="acq_khatauni" name="khatauni_verified" value="1" checked>
                        <label class="form-check-label fw-semibold" for="acq_khatauni">
                            Bhulekh UP Online Khatauni verified &amp; clear of bank encumbrance
                        </label>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="acq_agree_date" class="form-label">Agreement (Bayana) Date</label>
                            <input type="date" class="form-control" id="acq_agree_date" name="agreement_date" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="acq_target_date" class="form-label">Target Bainama Registry Date</label>
                            <input type="date" class="form-control" id="acq_target_date" name="target_registry_date" value="<?= date('Y-m-d', strtotime('+6 months')) ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Land Acquisition Deal</button>
                </div>
            </div>
        </form>
    </div>
</div>
