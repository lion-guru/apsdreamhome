<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-8">
            <a href="<?= BASE_URL ?>/admin/payout-batches" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <h2 style="color:#e0e0e0;"><i class="fas fa-money-check-alt me-2" style="color:#28a745;"></i> <?= htmlspecialchars($batch['batch_name']) ?></h2>
            <small style="color:#888;">Created <?= date('d M Y H:i', strtotime($batch['created_at'])) ?> by <?= htmlspecialchars($batch['created_by_name'] ?? 'Admin') ?></small>
        </div>
        <div class="col-4 text-end">
            <!-- Action Buttons Based on Status -->
            <?php if ($batch['status'] === 'draft'): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/payout-batches/submit/<?= $batch['id'] ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane me-1"></i> Submit for Approval</button>
                </form>
            <?php elseif ($batch['status'] === 'pending_approval'): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/payout-batches/approve/<?= $batch['id'] ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve this payout batch?')"><i class="fas fa-check me-1"></i> Approve</button>
                </form>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fas fa-times me-1"></i> Reject</button>
            <?php elseif ($batch['status'] === 'approved'): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/payout-batches/process/<?= $batch['id'] ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-info" onclick="return confirm('Start processing this batch?')"><i class="fas fa-play me-1"></i> Start Processing</button>
                </form>
                <form method="POST" action="<?= BASE_URL ?>/admin/payout-batches/export/<?= $batch['id'] ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-outline-success"><i class="fas fa-download me-1"></i> Bank Export</button>
                </form>
            <?php elseif ($batch['status'] === 'processing'): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/payout-batches/export/<?= $batch['id'] ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-outline-success"><i class="fas fa-download me-1"></i> Bank Export</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status Banner -->
    <?php
    $statusColors = [
        'draft' => '#6c757d', 'pending_approval' => '#ffc107', 'approved' => '#28a745',
        'processing' => '#17a2b8', 'completed' => '#20c997', 'rejected' => '#dc3545',
    ];
    $sc = $statusColors[$batch['status']] ?? '#6c757d';
    ?>
    <div class="alert mb-4" style="background:<?= $sc ?>15;border:1px solid <?= $sc ?>40;color:<?= $sc ?>;">
        <i class="fas fa-info-circle me-2"></i>
        Status: <strong><?= str_replace('_', ' ', ucfirst($batch['status'])) ?></strong>
        <?php if ($batch['approved_by_name']): ?>
            — Approved by <?= htmlspecialchars($batch['approved_by_name']) ?> on <?= date('d M Y H:i', strtotime($batch['approved_at'])) ?>
        <?php endif; ?>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
                <div class="card-body text-center">
                    <h3 style="color:#28a745;"><?= number_format($batch['total_entries']) ?></h3>
                    <small style="color:#888;">Total Entries</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
                <div class="card-body text-center">
                    <h3 style="color:#ffc107;">₹<?= number_format((float)$batch['total_amount']) ?></h3>
                    <small style="color:#888;">Gross Amount</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
                <div class="card-body text-center">
                    <?php
                    $entries = $entries ?? [];
                    $totalTds = array_sum(array_map(function($e) { return (float)($e['tds_amount'] ?? 0); }, $entries));
                    $totalNet = array_sum(array_map(function($e) { return (float)($e['net_amount'] ?? 0); }, $entries));
                    ?>
                    <h3 style="color:#dc3545;">₹<?= number_format($totalTds) ?></h3>
                    <small style="color:#888;">TDS Deducted</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
                <div class="card-body text-center">
                    <h3 style="color:#20c997;">₹<?= number_format($totalNet) ?></h3>
                    <small style="color:#888;">Net Payout</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-Populate (for draft batches) -->
    <?php if ($batch['status'] === 'draft'): ?>
        <div class="card mb-4" style="background:rgba(40,167,69,0.05);border:1px solid rgba(40,167,69,0.3);">
            <div class="card-header" style="background:rgba(40,167,69,0.1);border-bottom:1px solid rgba(40,167,69,0.3);">
                <h5 style="color:#28a745;margin:0;"><i class="fas fa-magic me-2"></i> Auto-populate with Pending Entries</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/payout-batches/populate/<?= $batch['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <label style="color:#ccc;font-size:0.85rem;">Type Filter</label>
                            <select name="populate_type" class="form-select form-select-sm" style="background:#1a1a1a;border:#444;color:#ccc;">
                                <option value="">All Types</option>
                                <option value="direct_sale">Direct Sale</option>
                                <option value="override">Override</option>
                                <option value="rank_bonus">Rank Bonus</option>
                                <option value="level_bonus">Level Bonus</option>
                                <option value="matching_bonus">Matching Bonus</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label style="color:#ccc;font-size:0.85rem;">Date From</label>
                            <input type="date" name="populate_from" class="form-control form-control-sm" style="background:#1a1a1a;border:#444;color:#ccc;">
                        </div>
                        <div class="col-md-3">
                            <label style="color:#ccc;font-size:0.85rem;">Date To</label>
                            <input type="date" name="populate_to" class="form-control form-control-sm" style="background:#1a1a1a;border:#444;color:#ccc;">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-plus me-1"></i> Add Entries</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Entries Table -->
    <div class="card" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
        <div class="card-header" style="background:rgba(255,255,255,0.05);border-bottom:1px solid #444;">
            <h5 style="color:#28a745;margin:0;"><i class="fas fa-list me-2"></i> Payout Entries (<?= number_format($total_entries) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($entries)): ?>
                <div class="text-center p-4">
                    <i class="fas fa-inbox fa-2x" style="color:#555;"></i>
                    <p style="color:#888;margin-top:10px;">No entries in this batch yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr style="border-bottom:1px solid #444;">
                                <th style="color:#28a745;">#</th>
                                <th style="color:#28a745;">Beneficiary</th>
                                <th style="color:#28a745;">Type</th>
                                <th style="color:#28a745;">Gross (₹)</th>
                                <th style="color:#28a745;">TDS (₹)</th>
                                <th style="color:#28a745;">Net (₹)</th>
                                <th style="color:#28a745;">Status</th>
                                <th style="color:#28a745;">Ref</th>
                                <?php if ($batch['status'] === 'processing'): ?>
                                    <th style="color:#28a745;">Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $e): ?>
                                <tr style="border-bottom:1px solid #333;">
                                    <td><?= $e['id'] ?></td>
                                    <td><?= htmlspecialchars($e['beneficiary_name'] ?? 'User #' . $e['beneficiary_user_id']) ?></td>
                                    <td><span class="badge bg-info" style="font-size:0.7rem;"><?= $e['commission_type'] ?? 'N/A' ?></span></td>
                                    <td>₹<?= number_format((float)$e['amount']) ?></td>
                                    <td style="color:#dc3545;">₹<?= number_format((float)$e['tds_amount']) ?></td>
                                    <td style="color:#28a745;font-weight:bold;">₹<?= number_format((float)$e['net_amount']) ?></td>
                                    <td>
                                        <?php
                                        $eColors = ['pending' => '#ffc107', 'processing' => '#17a2b8', 'completed' => '#28a745', 'failed' => '#dc3545', 'cancelled' => '#6c757d'];
                                        $ec = $eColors[$e['status']] ?? '#6c757d';
                                        ?>
                                        <span style="color:<?= $ec ?>;font-weight:bold;"><?= ucfirst($e['status']) ?></span>
                                    </td>
                                    <td><small style="color:#888;"><?= htmlspecialchars($e['payment_reference'] ?? '-') ?></small></td>
                                    <?php if ($batch['status'] === 'processing'): ?>
                                        <td>
                                            <?php if (in_array($e['status'], ['pending', 'processing'])): ?>
                                                <button class="btn btn-xs btn-outline-success" onclick="completeEntry(<?= $e['id'] ?>, '<?= $e['beneficiary_name'] ?>')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="border-top:2px solid #444;">
                                <td colspan="3"><strong style="color:#28a745;">Total</strong></td>
                                <td><strong>₹<?= number_format(array_sum(array_map(fn($e) => (float)$e['amount'], $entries))) ?></strong></td>
                                <td><strong style="color:#dc3545;">₹<?= number_format(array_sum(array_map(fn($e) => (float)$e['tds_amount'], $entries))) ?></strong></td>
                                <td><strong style="color:#28a745;">₹<?= number_format(array_sum(array_map(fn($e) => (float)$e['net_amount'], $entries))) ?></strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if ($entry_total_pages > 1): ?>
                    <div class="d-flex justify-content-between align-items-center p-3" style="border-top:1px solid #444;">
                        <small style="color:#888;">Page <?= $entry_page ?> of <?= $entry_total_pages ?></small>
                        <div>
                            <?php for ($i = max(1, $entry_page - 2); $i <= min($entry_total_pages, $entry_page + 2); $i++): ?>
                                <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i === $entry_page ? 'btn-success' : 'btn-outline-secondary' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#222;border:1px solid #444;">
            <div class="modal-header" style="border-bottom:1px solid #444;">
                <h5 class="modal-title" style="color:#dc3545;">Reject Batch</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/payout-batches/reject/<?= $batch['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-body">
                    <label style="color:#ccc;">Reason for rejection</label>
                    <textarea name="reason" class="form-control" rows="3" required style="background:#1a1a1a;border:#444;color:#ccc;"></textarea>
                </div>
                <div class="modal-footer" style="border-top:1px solid #444;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Entry Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#222;border:1px solid #444;">
            <div class="modal-header" style="border-bottom:1px solid #444;">
                <h5 class="modal-title" style="color:#28a745;">Mark Payment Complete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/payout-batches/complete-entry">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="batch_id" value="<?= $batch['id'] ?>">
                <input type="hidden" name="entry_id" id="completeEntryId">
                <div class="modal-body">
                    <p style="color:#ccc;">Mark payment for: <strong id="completeEntryName"></strong></p>
                    <label style="color:#ccc;">Payment Reference (UTR/Ref No)</label>
                    <input type="text" name="payment_ref" class="form-control" placeholder="e.g. UTR123456789" style="background:#1a1a1a;border:#444;color:#ccc;">
                </div>
                <div class="modal-footer" style="border-top:1px solid #444;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark Complete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function completeEntry(id, name) {
    document.getElementById('completeEntryId').value = id;
    document.getElementById('completeEntryName').textContent = name;
    new bootstrap.Modal(document.getElementById('completeModal')).show();
}
</script>
