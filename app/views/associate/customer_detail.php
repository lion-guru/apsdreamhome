<?php
/**
 * Associate Customer Detail Page
 * Full customer info: bookings, plots, payments, site visits
 */
$page_title = $page_title ?? 'Customer Detail';
$current_page = 'my-customers';
$customer = $customer ?? null;
$bookings = $bookings ?? [];
$payments = $payments ?? [];
$receipts = $receipts ?? [];
$isAssociate = ($customer['is_associate'] ?? 0) == 1;
?>

<?php if (!$customer): ?>
    <div class="alert alert-danger">Customer not found.</div>
<?php else: ?>
    <!-- Customer Header -->
    <div class="card border-0 shadow-sm mb-4" style="<?= $isAssociate ? 'border-left: 4px solid #10b981 !important;' : '' ?>">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 60px; height: 60px; background: <?= $isAssociate ? '#dcfce7' : '#f3f4f6' ?>; color: <?= $isAssociate ? '#10b981' : '#6b7280' ?>; font-size: 1.5rem;">
                            <i class="fas fa-<?= $isAssociate ? 'user-tie' : 'user' ?>"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?= htmlspecialchars($customer['name'] ?? 'N/A') ?></h4>
                            <span class="text-muted">Customer since <?= date('d M Y', strtotime($customer['created_at'] ?? '')) ?></span>
                            <?php if ($isAssociate): ?>
                                <span class="badge bg-success ms-2"><i class="fas fa-check me-1"></i>Associate</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Phone</small>
                            <a href="tel:<?= $customer['phone'] ?>" class="text-decoration-none fw-bold">
                                <i class="fas fa-phone me-1"></i><?= htmlspecialchars($customer['phone'] ?? 'N/A') ?>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Email</small>
                            <a href="mailto:<?= $customer['email'] ?>" class="text-decoration-none">
                                <?= htmlspecialchars($customer['email'] ?? 'N/A') ?>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Address</small>
                            <span><?= htmlspecialchars($customer['address'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="tel:<?= $customer['phone'] ?>" class="btn btn-primary btn-sm mb-1">
                        <i class="fas fa-phone me-1"></i>Call
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $customer['phone'] ?? '') ?>" class="btn btn-success btn-sm mb-1" target="_blank">
                        <i class="fab fa-whatsapp me-1"></i>WhatsApp
                    </a>
                    <?php if (!$isAssociate): ?>
                        <button class="btn btn-warning btn-sm mb-1" onclick="inviteAsAssociate()">
                            <i class="fas fa-user-plus me-1"></i>Invite as Associate
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Business Summary -->
    <div class="row g-3 mb-4">
        <?php
        $totalBusiness = array_sum(array_column($bookings, 'total_plot_value'));
        $totalPaid = array_sum(array_column($receipts, 'amount'));
        $pendingAmount = $totalBusiness - $totalPaid;
        ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1">Total Bookings</div>
                    <h3 class="text-primary mb-0"><?= count($bookings) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1">Total Business</div>
                    <h3 class="text-success mb-0">₹<?= number_format($totalBusiness) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1">Amount Paid</div>
                    <h3 class="text-info mb-0">₹<?= number_format($totalPaid) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1">Pending</div>
                    <h3 class="text-danger mb-0">₹<?= number_format($pendingAmount) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-file-contract me-2 text-primary"></i>Bookings (<?= count($bookings) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No bookings found for this customer.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking #</th>
                                <th>Plot</th>
                                <th>Colony</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <?php
                                $bookingPaid = 0;
                                foreach ($receipts as $r) {
                                    if ($r['booking_id'] == $b['id']) $bookingPaid += $r['amount'];
                                }
                                $statusClass = match(strtolower($b['status'] ?? '')) {
                                    'confirmed', 'completed', 'fully_paid' => 'success',
                                    'partially_paid', 'emi_active' => 'warning',
                                    'pending_approval' => 'info',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($b['booking_number'] ?? $b['id']) ?></strong></td>
                                    <td>Plot #<?= htmlspecialchars($b['plot_number'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($b['colony_name'] ?? 'N/A') ?></td>
                                    <td>₹<?= number_format($b['total_plot_value'] ?? 0) ?></td>
                                    <td class="<?= $bookingPaid > 0 ? 'text-success' : 'text-danger' ?>">₹<?= number_format($bookingPaid) ?></td>
                                    <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $b['status'] ?? '')) ?></span></td>
                                    <td><?= date('d M Y', strtotime($b['created_at'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment History -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-receipt me-2 text-success"></i>Payment History (<?= count($receipts) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($receipts)): ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No payments recorded yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt #</th>
                                <th>Booking</th>
                                <th>Amount</th>
                                <th>Mode</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receipts as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r['receipt_number'] ?? 'N/A') ?></strong></td>
                                    <td>#<?= $r['booking_id'] ?? 'N/A' ?></td>
                                    <td><strong class="text-success">₹<?= number_format($r['amount'] ?? 0) ?></strong></td>
                                    <td><span class="badge bg-light text-dark"><?= ucfirst($r['payment_mode'] ?? 'N/A') ?></span></td>
                                    <td><?= date('d M Y', strtotime($r['receipt_date'] ?? $r['created_at'] ?? '')) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = match(strtolower($r['status'] ?? '')) {
                                            'completed', 'verified' => 'success',
                                            'pending' => 'warning',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($r['status'] ?? 'N/A') ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Invite Modal -->
    <div class="modal fade" id="inviteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Invite as Associate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Invite <strong><?= htmlspecialchars($customer['name'] ?? '') ?></strong> to become an associate!</p>
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-gift me-1"></i>Joining Benefits:</h6>
                        <ul class="mb-0 small">
                            <li><strong>5% commission</strong> on every plot sale</li>
                            <li><strong>Free Mobile</strong> at Associate rank</li>
                            <li><strong>Tablet, Laptop, Tour, Bike, Bullet, Car</strong> at higher ranks</li>
                            <li>Build team and earn from team sales</li>
                            <li>Full training and support</li>
                        </ul>
                    </div>
                    <div class="alert alert-success">
                        <h6 class="alert-heading"><i class="fas fa-chart-line me-1"></i>Earning Potential:</h6>
                        <table class="table table-sm mb-0">
                            <tr><td>Sell 1 plot (₹10L)</td><td class="text-end fw-bold">₹50,000</td></tr>
                            <tr><td>Build team of 5 → Senior Associate</td><td class="text-end fw-bold">₹70,000/lakh</td></tr>
                            <tr><td>Top rank → Site Manager</td><td class="text-end fw-bold">₹2,00,000/lakh + Car</td></tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $customer['phone'] ?? '') ?>?text=<?= urlencode('Hi! I\'ve been earning well with APS Dream Home. You should join as an associate too! Use my referral code: ' . ($_SESSION['referral_code'] ?? '') . '\nJoin here: ' . BASE_URL . '/register?ref=' . ($_SESSION['referral_code'] ?? '')) ?>" 
                       class="btn btn-success" target="_blank">
                        <i class="fab fa-whatsapp me-1"></i>Send WhatsApp Invite
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function inviteAsAssociate() {
        new bootstrap.Modal(document.getElementById('inviteModal')).show();
    }
    </script>
<?php endif; ?>
