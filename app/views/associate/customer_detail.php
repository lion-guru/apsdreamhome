<?php
$page_title = $page_title ?? __('assoc_cd_title', [], 'Customer Detail');
$current_page = 'my-customers';
$customer = $customer ?? null;
$bookings = $bookings ?? [];
$payments = $payments ?? [];
$receipts = $receipts ?? [];
$isAssociate = ($customer['is_associate'] ?? 0) == 1;
?>

<?php if (!$customer): ?>
    <div class="alert alert-danger"><?= __('assoc_cd_not_found', [], 'Customer not found.') ?></div>
<?php else: ?>
    <div class="card border-0 shadow-sm mb-4" class="style-64392">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             class="style-89487">
                            <i class="fas fa-<?= $isAssociate ? 'user-tie' : 'user' ?>"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?= htmlspecialchars($customer['name'] ?? __('assoc_cd_na', [], 'N/A')) ?></h4>
                            <span class="text-muted"><?= __('assoc_cd_customer_since', [], 'Customer since') ?> <?= date('d M Y', strtotime($customer['created_at'] ?? '')) ?></span>
                            <?php if ($isAssociate): ?>
                                <span class="badge bg-success ms-2"><i class="fas fa-check me-1"></i><?= __('assoc_cd_associate', [], 'Associate') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block"><?= __('assoc_cd_phone', [], 'Phone') ?></small>
                            <a href="tel:<?= $customer['phone'] ?>" class="text-decoration-none fw-bold">
                                <i class="fas fa-phone me-1"></i><?= htmlspecialchars($customer['phone'] ?? __('assoc_cd_na', [], 'N/A')) ?>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block"><?= __('assoc_cd_email', [], 'Email') ?></small>
                            <a href="mailto:<?= $customer['email'] ?>" class="text-decoration-none">
                                <?= htmlspecialchars($customer['email'] ?? __('assoc_cd_na', [], 'N/A')) ?>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block"><?= __('assoc_cd_address', [], 'Address') ?></small>
                            <span><?= htmlspecialchars($customer['address'] ?? __('assoc_cd_na', [], 'N/A')) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="tel:<?= $customer['phone'] ?>" class="btn btn-primary btn-sm mb-1">
                        <i class="fas fa-phone me-1"></i><?= __('assoc_cd_call', [], 'Call') ?>
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $customer['phone'] ?? '') ?>" class="btn btn-success btn-sm mb-1" target="_blank">
                        <i class="fab fa-whatsapp me-1"></i>WhatsApp
                    </a>
                    <?php if (!$isAssociate): ?>
                        <button class="btn btn-warning btn-sm mb-1" onclick="inviteAsAssociate()">
                            <i class="fas fa-user-plus me-1"></i><?= __('assoc_cd_invite', [], 'Invite as Associate') ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php
        $totalBusiness = array_sum(array_column($bookings, 'total_plot_value'));
        $totalPaid = array_sum(array_column($receipts, 'amount'));
        $pendingAmount = $totalBusiness - $totalPaid;
        ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1"><?= __('assoc_cd_total_bookings', [], 'Total Bookings') ?></div>
                    <h3 class="text-primary mb-0"><?= count($bookings) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1"><?= __('assoc_cd_total_business', [], 'Total Business') ?></div>
                    <h3 class="text-success mb-0">â‚¹<?= number_format($totalBusiness) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1"><?= __('assoc_cd_amount_paid', [], 'Amount Paid') ?></div>
                    <h3 class="text-info mb-0">â‚¹<?= number_format($totalPaid) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1"><?= __('assoc_cd_pending', [], 'Pending') ?></div>
                    <h3 class="text-danger mb-0">â‚¹<?= number_format($pendingAmount) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-file-contract me-2 text-primary"></i><?= __('assoc_cd_bookings_title', ['count' => count($bookings)], 'Bookings (%count%)') ?></h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-0"><?= __('assoc_cd_no_bookings', [], 'No bookings found for this customer.') ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('assoc_cd_th_booking', [], 'Booking #') ?></th>
                                <th><?= __('assoc_cd_th_plot', [], 'Plot') ?></th>
                                <th><?= __('assoc_cd_th_colony', [], 'Colony') ?></th>
                                <th><?= __('assoc_cd_th_amount', [], 'Amount') ?></th>
                                <th><?= __('assoc_cd_th_paid', [], 'Paid') ?></th>
                                <th><?= __('assoc_cd_th_status', [], 'Status') ?></th>
                                <th><?= __('assoc_cd_th_date', [], 'Date') ?></th>
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
                                    <td><?= __('assoc_cd_plot', [], 'Plot') ?> #<?= htmlspecialchars($b['plot_number'] ?? __('assoc_cd_na', [], 'N/A')) ?></td>
                                    <td><?= htmlspecialchars($b['colony_name'] ?? __('assoc_cd_na', [], 'N/A')) ?></td>
                                    <td>â‚¹<?= number_format($b['total_plot_value'] ?? 0) ?></td>
                                    <td class="<?= $bookingPaid > 0 ? 'text-success' : 'text-danger' ?>">â‚¹<?= number_format($bookingPaid) ?></td>
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

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-receipt me-2 text-success"></i><?= __('assoc_cd_payment_history', ['count' => count($receipts)], 'Payment History (%count%)') ?></h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($receipts)): ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-0"><?= __('assoc_cd_no_payments', [], 'No payments recorded yet.') ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('assoc_cd_th_receipt', [], 'Receipt #') ?></th>
                                <th><?= __('assoc_cd_th_booking_ref', [], 'Booking') ?></th>
                                <th><?= __('assoc_cd_th_amount', [], 'Amount') ?></th>
                                <th><?= __('assoc_cd_th_mode', [], 'Mode') ?></th>
                                <th><?= __('assoc_cd_th_date', [], 'Date') ?></th>
                                <th><?= __('assoc_cd_th_status', [], 'Status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receipts as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r['receipt_number'] ?? __('assoc_cd_na', [], 'N/A')) ?></strong></td>
                                    <td>#<?= $r['booking_id'] ?? __('assoc_cd_na', [], 'N/A') ?></td>
                                    <td><strong class="text-success">â‚¹<?= number_format($r['amount'] ?? 0) ?></strong></td>
                                    <td><span class="badge bg-light text-dark"><?= ucfirst($r['payment_mode'] ?? __('assoc_cd_na', [], 'N/A')) ?></span></td>
                                    <td><?= date('d M Y', strtotime($r['receipt_date'] ?? $r['created_at'] ?? '')) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = match(strtolower($r['status'] ?? '')) {
                                            'completed', 'verified' => 'success',
                                            'pending' => 'warning',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($r['status'] ?? __('assoc_cd_na', [], 'N/A')) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="inviteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i><?= __('assoc_cd_invite_title', [], 'Invite as Associate') ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><?= __('assoc_cd_invite_intro', ['name' => htmlspecialchars($customer['name'] ?? '')], 'Invite %name% to become an associate!') ?></p>
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-gift me-1"></i><?= __('assoc_cd_invite_benefits', [], 'Joining Benefits') ?>:</h6>
                        <ul class="mb-0 small">
                            <li><strong><?= __('assoc_cd_invite_b1', [], '5% commission') ?></strong> <?= __('assoc_cd_invite_b1d', [], 'on every plot sale') ?></li>
                            <li><strong><?= __('assoc_cd_invite_b2', [], 'Free Mobile') ?></strong> <?= __('assoc_cd_invite_b2d', [], 'at Associate rank') ?></li>
                            <li><strong><?= __('assoc_cd_invite_b3', [], 'Tablet, Laptop, Tour, Bike, Bullet, Car') ?></strong> <?= __('assoc_cd_invite_b3d', [], 'at higher ranks') ?></li>
                            <li><?= __('assoc_cd_invite_b4', [], 'Build team and earn from team sales') ?></li>
                            <li><?= __('assoc_cd_invite_b5', [], 'Full training and support') ?></li>
                        </ul>
                    </div>
                    <div class="alert alert-success">
                        <h6 class="alert-heading"><i class="fas fa-chart-line me-1"></i><?= __('assoc_cd_invite_earning', [], 'Earning Potential') ?>:</h6>
                        <table class="table table-sm mb-0">
                            <tr><td><?= __('assoc_cd_invite_row1', [], 'Sell 1 plot (â‚¹10L)') ?></td><td class="text-end fw-bold">â‚¹50,000</td></tr>
                            <tr><td><?= __('assoc_cd_invite_row2', [], 'Build team of 5 â†’ Senior Associate') ?></td><td class="text-end fw-bold">â‚¹70,000/<?= __('assoc_cd_lakh', [], 'lakh') ?></td></tr>
                            <tr><td><?= __('assoc_cd_invite_row3', [], 'Top rank â†’ Site Manager') ?></td><td class="text-end fw-bold">â‚¹2,00,000/<?= __('assoc_cd_lakh', [], 'lakh') ?> + Car</td></tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('assoc_cd_cancel', [], 'Cancel') ?></button>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $customer['phone'] ?? '') ?>?text=<?= urlencode(__('assoc_cd_whatsapp_text', ['code' => ($_SESSION['referral_code'] ?? ''), 'url' => BASE_URL], "Hi! I've been earning well with APS Dream Home. You should join as an associate too! Use my referral code: %code%\nJoin here: %url%/register?ref=%code%")) ?>" 
                       class="btn btn-success" target="_blank">
                        <i class="fab fa-whatsapp me-1"></i><?= __('assoc_cd_send_invite', [], 'Send WhatsApp Invite') ?>
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
