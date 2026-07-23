<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <span>Registry & NOC Records</span>
        <a href="<?= BASE_URL ?>/admin/legal/noc-eligibility" class="btn btn-sm btn-primary">
            <i class="fas fa-search"></i> Check Eligibility
        </a>
    </div>
    <div class="aps-cp-card-body">
        <!-- Registries Tab -->
        <ul class="nav nav-tabs mb-3" id="legalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="registries-tab" data-bs-toggle="tab" data-bs-target="#registries" type="button" role="tab">
                    <i class="fas fa-file-contract"></i> Registries (<?= count($registries ?? []) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nocs-tab" data-bs-toggle="tab" data-bs-target="#nocs" type="button" role="tab">
                    <i class="fas fa-file-signature"></i> NOCs (<?= count($nocs ?? []) ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="registries" role="tabpanel">
                <?php if (empty($registries)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-contract fa-3x text-muted mb-3" style="opacity:0.2"></i>
                    <h5 class="text-muted">No registry records found</h5>
                    <p class="text-muted mb-3">Registry records are created when property bookings reach the registration stage. Check eligibility to get started.</p>
                    <a href="<?= BASE_URL ?>/admin/legal/noc-eligibility" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Check Eligibility
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Plot</th>
                                <th>Reg. No.</th>
                                <th>Sub-Registrar Office</th>
                                <th>Reg. Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registries as $r): ?>
                            <tr>
                                <td><?= (int)$r['id'] ?></td>
                                <td><?= htmlspecialchars($r['booking_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($r['customer_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($r['plot_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($r['registration_no'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($r['sub_registrar_office'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($r['registration_date'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>₹<?= number_format((float)($r['total_registry_cost'] ?? 0), 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= match($r['status'] ?? '') { 'completed' => 'success', 'pending' => 'warning', 'failed' => 'danger', default => 'secondary' } ?>">
                                        <?= htmlspecialchars($r['status'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/legal/registry-show/<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="nocs" role="tabpanel">
                <?php if (empty($nocs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-signature fa-3x text-muted mb-3" style="opacity:0.2"></i>
                    <h5 class="text-muted">No NOC records found</h5>
                    <p class="text-muted mb-3">No Objection Certificates are required during property transfer processes. Check eligibility to request an NOC.</p>
                    <a href="<?= BASE_URL ?>/admin/legal/noc-eligibility" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Check Eligibility
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Plot</th>
                                <th>Type</th>
                                <th>Requested</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nocs as $n): ?>
                            <tr>
                                <td><?= (int)$n['id'] ?></td>
                                <td><?= htmlspecialchars($n['booking_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($n['customer_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($n['plot_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($n['noc_type'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($n['created_at'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge bg-<?= match($n['status'] ?? '') { 'approved' => 'success', 'pending' => 'warning', 'blocked' => 'danger', 'rejected' => 'danger', default => 'secondary' } ?>">
                                        <?= htmlspecialchars($n['status'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/legal/noc-show/<?= (int)$n['id'] ?>" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
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
