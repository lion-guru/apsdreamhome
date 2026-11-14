<?php
/**
 * Admin MLM Real Estate Career Rewards Desk View
 */
$claims = $claims ?? [];
$stats = $stats ?? [];
$statusFilter = $statusFilter ?? 'all';
$csrfToken = $_SESSION['csrf_token'] ?? '';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="m-0 text-dark fw-bold">
                        <i class="fas fa-trophy text-warning me-2"></i> Career Rank Rewards & Handover Desk
                    </h1>
                    <p class="text-muted small mb-0">
                        Review, approve, dispatch, and record physical handover ceremonies or wallet cash conversions for associate rank rewards.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/mlm-realestate/allowances" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-gas-pump me-1"></i> Office & Travel Allowances
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/mlm/withdrawals" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-money-check-alt me-1"></i> Wallet Withdrawals
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- FLASH ALERTS -->
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- STATS CARDS -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-2">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="text-muted small text-uppercase fw-semibold">Pending Claims</div>
                        <div class="h3 mb-0 fw-bold text-warning mt-1"><?php echo (int)($stats['count_claimed'] ?? 0); ?></div>
                        <small class="text-muted">Awaiting Action</small>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="text-muted small text-uppercase fw-semibold">Approved</div>
                        <div class="h3 mb-0 fw-bold text-primary mt-1"><?php echo (int)($stats['count_approved'] ?? 0); ?></div>
                        <small class="text-muted">Ready for Dispatch</small>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="text-muted small text-uppercase fw-semibold">In Transit</div>
                        <div class="h3 mb-0 fw-bold text-info mt-1"><?php echo (int)($stats['count_dispatched'] ?? 0); ?></div>
                        <small class="text-muted">Dispatched</small>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="text-muted small text-uppercase fw-semibold">Delivered</div>
                        <div class="h3 mb-0 fw-bold text-success mt-1"><?php echo (int)($stats['count_delivered'] ?? 0); ?></div>
                        <small class="text-muted">Ceremony Handover</small>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="text-muted small text-uppercase fw-semibold">Wallet Cash</div>
                        <div class="h3 mb-0 fw-bold text-purple mt-1" style="color: #7c3aed;"><?php echo (int)($stats['count_wallet'] ?? 0); ?></div>
                        <small class="text-muted">Disbursed to Wallet</small>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="text-muted small text-uppercase fw-semibold">Total Rewards Value</div>
                        <div class="h4 mb-0 fw-bold text-dark mt-1">₹<?php echo number_format((float)($stats['total_reward_value'] ?? 0)); ?></div>
                        <small class="text-muted">Combined Pool</small>
                    </div>
                </div>
            </div>

            <!-- FILTER TABS & MAIN TABLE -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <ul class="nav nav-pills card-header-pills">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $statusFilter === 'all' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm-realestate/rewards?status=all">
                                All Records
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $statusFilter === 'claimed' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm-realestate/rewards?status=claimed">
                                Claims Pending Review
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $statusFilter === 'approved' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm-realestate/rewards?status=approved">
                                Approved
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $statusFilter === 'dispatched' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm-realestate/rewards?status=dispatched">
                                Dispatched
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $statusFilter === 'delivered' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm-realestate/rewards?status=delivered">
                                Handed Over / Delivered
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th scope="col" class="ps-3">ID</th>
                                    <th scope="col">Associate Details</th>
                                    <th scope="col">Reward & Value</th>
                                    <th scope="col">Delivery Mode</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Fulfillment Logistics / Handover</th>
                                    <th scope="col" class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <?php if (empty($claims)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No career reward records found for this filter.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($claims as $c): ?>
                                        <?php
                                        $id = (int)$c['id'];
                                        $cStatus = $c['claim_status'];
                                        ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-muted">#<?php echo $id; ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['associate_name']); ?></div>
                                                <div class="text-muted small">
                                                    <i class="fas fa-phone-alt me-1"></i><?php echo htmlspecialchars($c['associate_phone'] ?? '—'); ?> |
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($c['associate_rank'] ?? $c['rank_slug']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['reward_name']); ?></div>
                                                <span class="badge bg-warning text-dark fw-bold">₹<?php echo number_format((float)$c['reward_value']); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($c['delivery_type'] === 'wallet_cash_equivalent'): ?>
                                                    <span class="badge bg-purple text-white" style="background-color: #7c3aed;">💳 Wallet Cash</span>
                                                <?php elseif ($c['delivery_type'] === 'courier_dispatch'): ?>
                                                    <span class="badge bg-info text-dark">📦 Courier</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">🏢 Office Handover</span>
                                                <?php endif; ?>
                                                <?php if (!empty($c['shipping_address'])): ?>
                                                    <div class="small text-muted mt-1 text-truncate" style="max-width: 180px;" title="<?php echo htmlspecialchars($c['shipping_address']); ?>">
                                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i><?php echo htmlspecialchars($c['shipping_address']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($cStatus === 'eligible'): ?>
                                                    <span class="badge bg-secondary">Eligible (Unclaimed)</span>
                                                <?php elseif ($cStatus === 'claimed'): ?>
                                                    <span class="badge bg-warning text-dark">Claim Submitted</span>
                                                <?php elseif ($cStatus === 'approved'): ?>
                                                    <span class="badge bg-primary">Approved</span>
                                                <?php elseif ($cStatus === 'dispatched'): ?>
                                                    <span class="badge bg-info text-dark">Dispatched</span>
                                                <?php elseif ($cStatus === 'delivered'): ?>
                                                    <span class="badge bg-success">Delivered / Handed Over</span>
                                                <?php elseif ($cStatus === 'converted_to_wallet'): ?>
                                                    <span class="badge bg-dark">Converted to Wallet</span>
                                                <?php elseif ($cStatus === 'rejected'): ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($cStatus); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($c['tracking_number'])): ?>
                                                    <div><strong>Tracking:</strong> <?php echo htmlspecialchars($c['tracking_number']); ?> (<?php echo htmlspecialchars($c['courier_partner'] ?? ''); ?>)</div>
                                                <?php endif; ?>
                                                <?php if (!empty($c['serial_number'])): ?>
                                                    <div><strong>Serial / IMEI:</strong> <?php echo htmlspecialchars($c['serial_number']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($c['handover_event'])): ?>
                                                    <div><strong>Event:</strong> <?php echo htmlspecialchars($c['handover_event']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($c['admin_notes'])): ?>
                                                    <div class="text-muted small"><strong>Notes:</strong> <?php echo htmlspecialchars($c['admin_notes']); ?></div>
                                                <?php endif; ?>
                                                <?php if (empty($c['tracking_number']) && empty($c['serial_number']) && empty($c['handover_event']) && empty($c['admin_notes'])): ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($cStatus === 'claimed'): ?>
                                                        <form method="POST" action="<?php echo BASE_URL; ?>/admin/mlm-realestate/rewards/<?php echo $id; ?>/approve" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                            <button type="submit" class="btn btn-outline-success btn-sm" title="Approve Claim">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if (in_array($cStatus, ['claimed', 'approved'], true)): ?>
                                                        <button type="button" class="btn btn-outline-info btn-sm"
                                                                data-bs-toggle="modal" data-bs-target="#dispatchModal<?php echo $id; ?>" title="Dispatch via Courier">
                                                            <i class="fas fa-shipping-fast"></i>
                                                        </button>

                                                        <button type="button" class="btn btn-outline-purple btn-sm text-white" style="background-color: #7c3aed;"
                                                                data-bs-toggle="modal" data-bs-target="#cashModal<?php echo $id; ?>" title="Convert & Pay Cash to Wallet">
                                                            <i class="fas fa-wallet"></i> Cash
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if (in_array($cStatus, ['approved', 'dispatched'], true)): ?>
                                                        <button type="button" class="btn btn-outline-success btn-sm"
                                                                data-bs-toggle="modal" data-bs-target="#deliverModal<?php echo $id; ?>" title="Mark Handed Over / Delivered">
                                                            <i class="fas fa-handshake"></i> Handover
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if (in_array($cStatus, ['claimed', 'approved'], true)): ?>
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                                data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $id; ?>" title="Reject Claim">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- DISPATCH MODAL -->
                                                <div class="modal fade" id="dispatchModal<?php echo $id; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered text-start">
                                                        <div class="modal-content border-0 shadow">
                                                            <form method="POST" action="<?php echo BASE_URL; ?>/admin/mlm-realestate/rewards/<?php echo $id; ?>/dispatch">
                                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                                <div class="modal-header bg-info text-dark">
                                                                    <h6 class="modal-title fw-bold">Dispatch Reward #<?php echo $id; ?>: <?php echo htmlspecialchars($c['reward_name']); ?></h6>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body p-3">
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Courier / Logistics Partner</label>
                                                                        <input aria-label="Courier partner" type="text" name="courier_partner" class="form-control form-control-sm" placeholder="e.g. Blue Dart, DTDC, Company Vehicle" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Tracking / Waybill / AWB Number</label>
                                                                        <input aria-label="Tracking number" type="text" name="tracking_number" class="form-control form-control-sm" placeholder="e.g. BD789456123IN" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Device Serial / IMEI / Vehicle Chassis / Tour PNR</label>
                                                                        <input aria-label="Serial number" type="text" name="serial_number" class="form-control form-control-sm" placeholder="e.g. IMEI / Chassis Number">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Admin Dispatch Notes</label>
                                                                        <textarea aria-label="Admin notes" name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="Dispatch instructions or remarks..."></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light p-2">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-info btn-sm fw-bold">Confirm Dispatch</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- DELIVER / HANDOVER MODAL -->
                                                <div class="modal fade" id="deliverModal<?php echo $id; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered text-start">
                                                        <div class="modal-content border-0 shadow">
                                                            <form method="POST" action="<?php echo BASE_URL; ?>/admin/mlm-realestate/rewards/<?php echo $id; ?>/deliver">
                                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                                <div class="modal-header bg-success text-white">
                                                                    <h6 class="modal-title fw-bold">Record Handover Ceremony #<?php echo $id; ?></h6>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body p-3">
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Event / Handover Venue</label>
                                                                        <input aria-label="Handover event" type="text" name="handover_event" class="form-control form-control-sm" value="Gorakhpur Head Office Ceremony" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Ceremony Photo URL / File Path (Optional)</label>
                                                                        <input aria-label="Handover photo" type="text" name="handover_photo" class="form-control form-control-sm" placeholder="uploads/ceremony_2026.jpg">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Remarks & Handover Notes</label>
                                                                        <textarea aria-label="Admin notes" name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="Keys / Device officially handed over in presence of Management..."></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light p-2">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-success btn-sm fw-bold">Confirm Handover & Delivered</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- CONVERT TO CASH MODAL -->
                                                <div class="modal fade" id="cashModal<?php echo $id; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered text-start">
                                                        <div class="modal-content border-0 shadow">
                                                            <form method="POST" action="<?php echo BASE_URL; ?>/admin/mlm-realestate/rewards/<?php echo $id; ?>/cash">
                                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                                <div class="modal-header text-white" style="background-color: #7c3aed;">
                                                                    <h6 class="modal-title fw-bold">Convert Reward to Wallet Cash #<?php echo $id; ?></h6>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body p-3">
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Cash Equivalent Amount (₹)</label>
                                                                        <input aria-label="Cash amount" type="number" step="0.01" name="cash_amount" class="form-control form-control-sm"
                                                                               value="<?php echo (float)$c['reward_value']; ?>" required>
                                                                        <div class="form-text small">This amount will be credited directly to associate's wallet.</div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Ledger Memo</label>
                                                                        <textarea aria-label="Notes" name="notes" class="form-control form-control-sm" rows="2">Cash equivalent disbursed for <?php echo htmlspecialchars($c['reward_name']); ?></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light p-2">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-sm fw-bold text-white" style="background-color: #7c3aed;">Disburse Cash to Wallet</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- REJECT MODAL -->
                                                <div class="modal fade" id="rejectModal<?php echo $id; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered text-start">
                                                        <div class="modal-content border-0 shadow">
                                                            <form method="POST" action="<?php echo BASE_URL; ?>/admin/mlm-realestate/rewards/<?php echo $id; ?>/reject">
                                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h6 class="modal-title fw-bold">Reject Reward Claim #<?php echo $id; ?></h6>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body p-3">
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Reason for Rejection</label>
                                                                        <textarea aria-label="Reason" name="reason" class="form-control form-control-sm" rows="3" required placeholder="Specify why the claim was rejected (e.g. team legs criteria not met, unverified bookings)..."></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light p-2">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger btn-sm fw-bold">Confirm Rejection</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

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
    </section>
</div>

