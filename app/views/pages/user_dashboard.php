<?php
$page_title = $page_title ?? __('dash_page_title', null, 'My Dashboard');
$current_page = 'dashboard';

$stats = $stats ?? ['total_properties' => 0, 'active_inquiries' => 0, 'total_bookings' => 0, 'total_inquiries' => 0, 'total_tickets' => 0, 'open_tickets' => 0];
$properties = $properties ?? [];
$inquiries = $inquiries ?? [];
$bookings = $bookings ?? [];
$user = $user ?? [];
$userDocuments = $userDocuments ?? [];
$recentPayments = $recentPayments ?? [];
$savedCount = $savedCount ?? 0;
$twoFactorEnabled = $twoFactorEnabled ?? false;
$kycStatus = $kycStatus ?? 'not_started';
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-hand-sparkles me-2"></i><?= __('dash_welcome_back', ['name' => htmlspecialchars($_SESSION['user_name'] ?? $user['name'] ?? '')], 'Welcome back, %s!') ?></h2>
            <p><?= __('dash_hero_subtitle', null, 'Manage your properties, track inquiries, bookings and payments — all in one place.') ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0">
            <div class="aps-cp-hero-actions justify-content-md-end">
                <a href="<?= BASE_URL ?>/list-property" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i><?= __('dash_btn_post_property', null, 'Post Property') ?>
                </a>
                <a href="<?= BASE_URL ?>/properties" class="btn btn-outline-light">
                    <i class="fas fa-search me-2"></i><?= __('dash_btn_browse', null, 'Browse') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--blue">
            <div class="aps-cp-stat-icon"><i class="fas fa-building"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value" data-aps-count="<?= (int)$stats['total_properties'] ?>">0</div>
                <div class="aps-cp-stat-label">
                    <?= __('dash_stat_my_properties', null, 'My Properties') ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--green">
            <div class="aps-cp-stat-icon"><i class="fas fa-envelope"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value" data-aps-count="<?= (int)$stats['active_inquiries'] ?>">0</div>
                <div class="aps-cp-stat-label">
                    <?= __('dash_stat_active_inquiries', null, 'Active Inquiries') ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--orange">
            <div class="aps-cp-stat-icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value" data-aps-count="<?= (int)$stats['total_bookings'] ?>">0</div>
                <div class="aps-cp-stat-label">
                    <?= __('dash_stat_my_purchases', null, 'My Purchases') ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--purple">
            <div class="aps-cp-stat-icon"><i class="fas fa-chart-line"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value" data-aps-count="<?= (int)$stats['total_inquiries'] ?>">0</div>
                <div class="aps-cp-stat-label">
                    <?= __('dash_stat_total_inquiries', null, 'Total Inquiries') ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--red">
            <div class="aps-cp-stat-icon"><i class="fas fa-headset"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">
                    <span data-aps-count="<?= (int)$stats['open_tickets'] ?>">0</span>
                    <span class="text-muted fs-6">/<span data-aps-count="<?= (int)$stats['total_tickets'] ?>">0</span></span>
                </div>
                <div class="aps-cp-stat-label">
                    <?= __('dash_stat_open_tickets', null, 'Open Tickets') ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--indigo">
            <div class="aps-cp-stat-icon"><i class="fas fa-bookmark"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value" data-aps-count="<?= (int)$savedCount ?>">0</div>
                <div class="aps-cp-stat-label">
                    <?= __('dash_stat_saved_searches', null, 'Saved Searches') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$dashBookings = $bookings ?? [];
$dashActiveEmis = 0;
$dashTotalPending = 0;
$dashTotalPaid = 0;
foreach ($dashBookings as $db) {
    $dbStatus = $db['status'] ?? '';
    if ($dbStatus === 'emi_active' || $dbStatus === 'partially_paid') {
        $dashActiveEmis++;
    }
    $dbTotal = (float)($db['total_plot_value'] ?? $db['total_amount'] ?? 0);
    $dbPaid = (float)($db['amount'] ?? $db['token_paid'] ?? 0);
    $dashTotalPaid += $dbPaid;
    $dashTotalPending += max(0, $dbTotal - $dbPaid);
}
$dashBookingCount = count($dashBookings);
?>
<?php if ($dashBookingCount > 0): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="aps-cp-card" class="style-77408">
            <div class="aps-cp-card-header" class="style-61577">
                <h5><i class="fas fa-file-invoice-dollar" class="style-23621"></i> <?= __('dash_my_bookings', null, 'My Bookings') ?></h5>
                <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-sm btn-outline-primary"><?= __('dash_btn_view_all', null, 'View All') ?></a>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3 text-center">
                    <div class="col-md-3 col-6">
                        <div class="bg-white rounded-3 p-3 border">
                            <div class="fw-bold fs-4 text-primary" data-aps-count="<?= $dashBookingCount ?>">0</div>
                            <small class="text-muted"><?= __('dash_total_bookings', null, 'Total Bookings') ?></small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="bg-white rounded-3 p-3 border">
                            <div class="fw-bold fs-4 text-amber" class="style-62159" data-aps-count="<?= $dashActiveEmis ?>">0</div>
                            <small class="text-muted"><?= __('dash_active_emis', null, 'Active EMIs') ?></small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="bg-white rounded-3 p-3 border">
                            <div class="fw-bold fs-4 text-success">₹<?= number_format($dashTotalPaid) ?></div>
                            <small class="text-muted"><?= __('dash_total_paid', null, 'Total Paid') ?></small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="bg-white rounded-3 p-3 border">
                            <div class="fw-bold fs-4 text-danger">₹<?= number_format($dashTotalPending > 0 ? $dashTotalPending : 0) ?></div>
                            <small class="text-muted"><?= __('dash_pending_amount', null, 'Pending Amount') ?></small>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <a href="<?= BASE_URL ?>/user/bookings/new" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i><?= __('dash_btn_book_plot', null, 'Book a Plot') ?>
                    </a>
                    <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i><?= __('dash_btn_view_all_bookings', null, 'View All Bookings') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">

        <?php if (!empty($bookings)): ?>
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-file-invoice-dollar text-success"></i> <?= __('dash_section_my_purchases', null, 'My Purchases') ?></h5>
                <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-sm btn-outline-primary"><?= __('dash_btn_view_all', null, 'View All') ?></a>
            </div>
            <div class="aps-cp-card-body p-0">
                <div class="table-responsive">
                    <table class="aps-cp-table">
                        <thead>
                            <tr>
                                <th><?= __('dash_th_plot', null, 'Plot') ?></th>
                                <th><?= __('dash_th_colony', null, 'Colony') ?></th>
                                <th><?= __('dash_th_amount', null, 'Amount') ?></th>
                                <th><?= __('dash_th_token_paid', null, 'Token Paid') ?></th>
                                <th><?= __('dash_th_status', null, 'Status') ?></th>
                                <th><?= __('dash_th_date', null, 'Date') ?></th>
                                <th class="text-end"><?= __('dash_th_action', null, 'Action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b):
                                $bStatus = $b['status'] ?? 'pending';
                                $badgeClass = match($bStatus) {
                                    'confirmed', 'completed' => 'success',
                                    'cancelled' => 'danger',
                                    'pending' => 'warning',
                                    default => 'secondary'
                                };
                                $tokenPaid = (float)($b['amount'] ?? 0);
                                $totalAmt = (float)($b['total_amount'] ?? 0);
                                $tokenRequired = $totalAmt * 0.25;
                            ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($b['plot_number'] ?? $b['property_id'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars($b['colony_name'] ?? 'N/A') ?></td>
                                <td>₹<?= number_format($totalAmt) ?></td>
                                <td>
                                    ₹<?= number_format($tokenPaid) ?>
                                    <?php if ($tokenPaid > 0 && $tokenRequired > 0): ?>
                                        <br><small class="text-muted"><?= round(($tokenPaid / $tokenRequired) * 100) ?>% <?= __('dash_pct_of_token', null, 'of token') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($bStatus) ?></span>
                                    <?php if ($bStatus === 'pending'): ?>
                                        <br><small class="text-warning"><?= __('dash_awaiting_approval', null, 'Awaiting approval') ?></small>
                                    <?php elseif ($bStatus === 'confirmed'): ?>
                                        <br><small class="text-success"><?= __('dash_approved', null, 'Approved') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($b['created_at'] ?? $b['booking_date'] ?? 'now')) ?></td>
                                <td class="text-end">
                                    <?php if ($bStatus === 'pending' && $tokenRequired > $tokenPaid): ?>
                                        <a href="<?= BASE_URL ?>/booking/<?= (int)$b['id'] ?>/pay" class="aps-cp-icon-btn" title="<?= __('dash_btn_pay_token', null, 'Pay Token') ?>" class="style-10106">
                                            <i class="fas fa-credit-card me-1"></i><?= __('dash_btn_pay_token', null, 'Pay Token') ?>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/booking/<?= (int)$b['id'] ?>/confirmation" class="aps-cp-icon-btn" title="<?= __('dash_btn_view', null, 'View') ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-file-invoice-dollar text-success"></i> <?= __('dash_section_my_purchases', null, 'My Purchases') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="aps-cp-empty">
                    <div class="aps-cp-empty-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    <h5><?= __('dash_no_bookings_title', null, 'No purchases yet') ?></h5>
                    <p><?= __('dash_no_bookings_yet', null, 'Start browsing properties to make your first booking.') ?></p>
                    <a href="<?= BASE_URL ?>/user/bookings/new" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i><?= __('dash_btn_book_plot', null, 'Book a Plot') ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($paymentSummary) && (($paymentSummary['total_overdue'] ?? 0) > 0 || ($paymentSummary['total_accrued_penalties'] ?? 0) > 0)): ?>
        <div class="aps-cp-card mb-4" class="style-14478">
            <div class="aps-cp-card-header" class="style-83182">
                <h5><i class="fas fa-exclamation-triangle text-danger"></i> Payment Alerts</h5>
                <span class="badge bg-danger"><?= ($paymentSummary['total_overdue'] ?? 0) ?> overdue</span>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-4">
                        <div class="aps-cp-stat aps-cp-stat--red" class="style-83240">
                            <div class="aps-cp-stat-body">
                                <div class="aps-cp-stat-value" class="style-30322">₹<?= number_format($paymentSummary['total_overdue_amount'] ?? 0) ?></div>
                                <div class="aps-cp-stat-label">Overdue Amount</div>
                            </div>
                        </div>
                    </div>
                    <?php if (($paymentSummary['total_accrued_penalties'] ?? 0) > 0): ?>
                    <div class="col-sm-4">
                        <div class="aps-cp-stat" class="style-84622">
                            <div class="aps-cp-stat-body">
                                <div class="aps-cp-stat-value" class="style-98499">₹<?= number_format($paymentSummary['total_accrued_penalties'] ?? 0) ?></div>
                                <div class="aps-cp-stat-label">Accrued Penalties (18% p.a.)</div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (($paymentSummary['worst_overdue_days'] ?? 0) > 0): ?>
                    <div class="col-sm-4">
                        <div class="aps-cp-stat" class="style-90504">
                            <div class="aps-cp-stat-body">
                                <div class="aps-cp-stat-value" class="style-39987"><?= (int)($paymentSummary['worst_overdue_days'] ?? 0) ?> days</div>
                                <div class="aps-cp-stat-label">Longest Overdue</div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($paymentSummary['overdue_installments'])): ?>
                <div class="table-responsive">
                    <table class="aps-cp-table aps-cp-table--compact">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>Plot</th>
                                <th>Installment</th>
                                <th>Due Date</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Penalty</th>
                                <th>Days</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paymentSummary['overdue_installments'] as $inst): ?>
                            <tr>
                                <td><small><?= htmlspecialchars($inst['booking_number'] ?? '') ?></small></td>
                                <td><small><?= htmlspecialchars($inst['plot_number'] ?? '') ?></small></td>
                                <td>#<?= (int)($inst['installment_no'] ?? 0) ?></td>
                                <td><?= date('d M Y', strtotime($inst['due_date'] ?? 'now')) ?></td>
                                <td class="text-end">₹<?= number_format((float)($inst['amount'] ?? 0)) ?></td>
                                <td class="text-end text-danger">₹<?= number_format((float)($inst['accrued_penalty'] ?? 0)) ?></td>
                                <td><span class="badge bg-danger"><?= (int)($inst['days_overdue'] ?? 0) ?>d</span></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/user/installments/<?= (int)($inst['id'] ?? 0) ?>/pay" class="btn btn-sm btn-success">
                                        <i class="fas fa-credit-card me-1"></i>Pay
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php if (!empty($paymentSummary['nach_mandate'])): ?>
                <div class="d-flex align-items-center gap-2 mt-3 p-2" class="style-62608">
                    <i class="fas fa-university text-success"></i>
                    <span class="text-success fw-semibold">NACH Auto-Debit Active</span>
                    <span class="text-muted ms-auto">
                        Next debit: <?= date('d M Y', strtotime($paymentSummary['nach_mandate']['next_debit_date'] ?? 'now')) ?>
                        &mdash; ₹<?= number_format((float)($paymentSummary['nach_mandate']['mandate_amount'] ?? 0)) ?>/mo
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-building text-primary"></i> <?= __('dash_section_my_properties', null, 'My Properties') ?></h5>
                <a href="<?= BASE_URL ?>/user/properties" class="btn btn-sm btn-outline-primary"><?= __('dash_btn_view_all', null, 'View All') ?></a>
            </div>
            <div class="aps-cp-card-body">
                <?php if (empty($properties)): ?>
                    <div class="aps-cp-empty">
                        <div class="aps-cp-empty-icon"><i class="fas fa-building"></i></div>
                        <h5><?= __('dash_no_properties_title', null, 'No properties listed') ?></h5>
                        <p><?= __('dash_no_properties_yet', null, 'Post your first property for free.') ?></p>
                        <a href="<?= BASE_URL ?>/list-property" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i><?= __('dash_btn_post_property', null, 'Post Property') ?>
                        </a>
                    </div>
                <?php else: ?>
                <div class="row g-3">
                    <?php foreach (array_slice($properties, 0, 4) as $property): ?>
                        <div class="col-md-6">
                            <div class="aps-cp-card" class="style-49683">
                                <div class="d-flex gap-3 p-3">
                                    <div class="aps-cp-stat-icon" class="style-99228">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <h6 class="mb-1 text-truncate"><?= htmlspecialchars($property['property_type'] ?? $property['title'] ?? __('dash_fallback_property', null, 'Property')) ?></h6>
                                        <p class="text-muted mb-1 small text-truncate"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($property['address'] ?? $property['location'] ?? '') ?></p>
                                        <p class="mb-2"><strong>₹<?= number_format($property['price'] ?? 0) ?></strong></p>
                                        <span class="badge bg-<?= ($property['status'] ?? '') === 'approved' || ($property['status'] ?? '') === 'active' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($property['status'] ?? 'pending') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-envelope text-success"></i> <?= __('dash_section_recent_inquiries', null, 'Recent Inquiries') ?></h5>
                <a href="<?= BASE_URL ?>/user/inquiries" class="btn btn-sm btn-outline-primary"><?= __('dash_btn_view_all', null, 'View All') ?></a>
            </div>
            <div class="aps-cp-card-body p-0">
                <?php if (empty($inquiries)): ?>
                    <div class="aps-cp-empty">
                        <div class="aps-cp-empty-icon"><i class="fas fa-envelope"></i></div>
                        <h5><?= __('dash_no_inquiries_title', null, 'No inquiries yet') ?></h5>
                        <p><?= __('dash_no_inquiries_yet', null, 'Start exploring and reach out to property owners.') ?></p>
                        <a href="<?= BASE_URL ?>/properties" class="btn btn-primary"><?= __('dash_browse_properties', null, 'Browse Properties') ?></a>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="aps-cp-table">
                        <thead>
                            <tr>
                                <th><?= __('dash_th_subject', null, 'Subject') ?></th>
                                <th><?= __('dash_th_type', null, 'Type') ?></th>
                                <th><?= __('dash_th_status', null, 'Status') ?></th>
                                <th><?= __('dash_th_date', null, 'Date') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $inquiry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($inquiry['subject'] ?? $inquiry['message'] ?? __('dash_fallback_inquiry', null, 'Inquiry')) ?></td>
                                    <td><?= htmlspecialchars($inquiry['type'] ?? __('dash_fallback_general', null, 'General')) ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($inquiry['status'] ?? '') === 'replied' ? 'success' : (($inquiry['status'] ?? '') === 'pending' ? 'warning' : 'info') ?>">
                                            <?= ucfirst($inquiry['status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d', strtotime($inquiry['created_at'] ?? 'now')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">

        <?php
        $invStats = $investor_stats ?? ['level' => 'Bronze', 'next_level' => 'Silver', 'progress_pct' => 0, 'next_threshold' => 50000, 'total_invested' => 0];
        $lvl = $invStats['level'] ?? 'Bronze';
        $lvlColor = match($lvl) { 'Diamond' => 'indigo', 'Platinum' => 'purple', 'Gold' => 'orange', 'Silver' => 'secondary', default => 'primary' };
        ?>
        <div class="aps-cp-card mb-4" class="style-77408">
            <div class="aps-cp-card-header" class="style-95171">
                <h5><i class="fas fa-trophy" class="style-92996"></i> <?= __('dash_investor_level', null, 'Investor Level') ?></h5>
            </div>
            <div class="aps-cp-card-body text-center">
                <div class="display-5 fw-bold mb-1" class="style-12445"><?= htmlspecialchars($lvl) ?></div>
                <small class="text-muted d-block mb-3"><?= __('dash_total_invested', null, 'Total Invested') ?>: ₹<?= number_format((float)($invStats['total_invested'] ?? 0)) ?></small>
                <div class="aps-cp-progress" class="style-51045">
                    <div class="aps-cp-progress-bar" class="style-9161"></div>
                </div>
                <p class="text-muted small mt-2 mb-0"><?= sprintf(__('dash_invest_more_format', null, 'Invest ₹%%s more to reach %%s'), number_format((float)($invStats['next_threshold'] ?? 50000)), htmlspecialchars($invStats['next_level'] ?? 'Silver')) ?></strong></p>
                <a href="<?= BASE_URL ?>/user/investment-plans" class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-primary mt-3"><i class="fas fa-arrow-up"></i> <?= __('dash_btn_upgrade', null, 'Upgrade') ?></a>
            </div>
        </div>

        <?php if (!empty($referral_code)): ?>
        <div class="aps-cp-card mb-4" class="style-77104">
            <div class="aps-cp-card-header" class="style-39246">
                <h5><i class="fas fa-gift text-warning"></i> <?= __('dash_refer_earn_title', null, 'Refer & Earn') ?></h5>
                <a href="<?= BASE_URL ?>/user/referral" class="btn btn-sm btn-outline-warning"><?= __('dash_btn_view_all', null, 'View All') ?></a>
            </div>
            <div class="aps-cp-card-body">
                <div class="text-center mb-3">
                    <div class="display-6 fw-bold text-warning mb-1" class="style-28340" id="dashRefCode">
                        <?= htmlspecialchars($referral_code) ?>
                    </div>
                    <small class="text-muted"><?= __('dash_your_referral_code', null, 'Your Referral Code') ?></small>
                </div>
                <div class="row text-center g-2 mb-3">
                    <div class="col-6">
                        <div class="bg-white rounded-3 p-2 border">
                            <div class="fw-bold text-primary fs-5" data-aps-count="<?= (int)$referral_count ?>">0</div>
                            <small class="text-muted"><?= __('dash_referrals', null, 'Referrals') ?></small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-white rounded-3 p-2 border">
                            <div class="fw-bold text-success fs-5">₹<?= number_format((float)($referral_earnings ?? 0), 2) ?></div>
                            <small class="text-muted"><?= __('dash_earnings', null, 'Earnings') ?></small>
                        </div>
                    </div>
                </div>
                <?php if (!empty($referral_link)): ?>
                <div class="input-group input-group-sm mb-2">
                    <input type="text" class="form-control" id="refLink" value="<?= htmlspecialchars($referral_link) ?>" readonly>
                    <button class="btn btn-outline-warning" type="button" onclick="dashCopyRef()" aria-label="<?= __('dash_copy_referral_link', null, 'Copy referral link') ?>">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <a href="https://wa.me/?text=<?= urlencode(sprintf(__('dash_share_whatsapp_msg', null, 'Join APS Dream Home! Use my referral code: %s Register here: %s'), $referral_code, $referral_link)) ?>" target="_blank" class="aps-cp-icon-btn" class="style-66842" title="<?= __('dash_share_whatsapp', null, 'Share on WhatsApp') ?>"><i class="fab fa-whatsapp"></i></a>
                    <a href="sms:?body=<?= urlencode(sprintf(__('dash_share_sms_msg', null, 'Use my referral code %s to register at APS Dream Home: %s'), $referral_code, $referral_link)) ?>" class="aps-cp-icon-btn" class="style-95721" title="<?= __('dash_share_sms', null, 'Share via SMS') ?>"><i class="fas fa-sms"></i></a>
                    <a href="mailto:?subject=<?= urlencode(__('dash_share_email_subject', null, 'Join APS Dream Home')) ?>&body=<?= urlencode(sprintf(__('dash_share_email_body', null, 'Use my referral code %s to register: %s'), $referral_code, $referral_link)) ?>" class="aps-cp-icon-btn" title="<?= __('dash_share_email', null, 'Share via Email') ?>"><i class="fas fa-envelope"></i></a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <script>
        function dashCopyRef() {
            var el = document.getElementById('refLink');
            if (el) {
                navigator.clipboard.writeText(el.value).then(function() {
                    var btn = el.nextElementSibling;
                    if (btn) { btn.innerHTML = '<i class="fas fa-check"></i>'; setTimeout(function(){ btn.innerHTML = '<i class="fas fa-copy"></i>'; }, 2000); }
                });
            }
        }
        </script>
        <?php endif; ?>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-bolt text-primary"></i> <?= __('dash_section_quick_actions', null, 'Quick Actions') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/list-property" class="aps-cp-quick-action">
                        <div class="aps-cp-quick-action-icon"><i class="fas fa-plus"></i></div>
                        <div class="aps-cp-quick-action-body">
                            <p class="aps-cp-quick-action-title"><?= __('dash_btn_post_new_property', null, 'Post New Property') ?></p>
                            <p class="aps-cp-quick-action-desc"><?= __('dash_quick_post_desc', null, 'List your property for free') ?></p>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>/user/bookings" class="aps-cp-quick-action">
                        <div class="aps-cp-quick-action-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div class="aps-cp-quick-action-body">
                            <p class="aps-cp-quick-action-title"><?= __('dash_btn_my_bookings', null, 'My Bookings') ?></p>
                            <p class="aps-cp-quick-action-desc"><?= __('dash_quick_bookings_desc', null, 'Track purchase & payment status') ?></p>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>/user/inquiries" class="aps-cp-quick-action">
                        <div class="aps-cp-quick-action-icon"><i class="fas fa-envelope"></i></div>
                        <div class="aps-cp-quick-action-body">
                            <p class="aps-cp-quick-action-title"><?= __('dash_btn_my_inquiries', null, 'My Inquiries') ?></p>
                            <p class="aps-cp-quick-action-desc"><?= __('dash_quick_inquiries_desc', null, 'See responses from owners') ?></p>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>/user/favorites" class="aps-cp-quick-action">
                        <div class="aps-cp-quick-action-icon"><i class="fas fa-heart"></i></div>
                        <div class="aps-cp-quick-action-body">
                            <p class="aps-cp-quick-action-title"><?= __('dash_btn_my_favorites', null, 'My Favorites') ?></p>
                            <p class="aps-cp-quick-action-desc"><?= __('dash_quick_favorites_desc', null, 'Saved properties you love') ?></p>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>/user/saved-searches" class="aps-cp-quick-action">
                        <div class="aps-cp-quick-action-icon"><i class="fas fa-search"></i></div>
                        <div class="aps-cp-quick-action-body">
                            <p class="aps-cp-quick-action-title"><?= __('dash_btn_saved_searches', null, 'Saved Searches') ?>
                                <?php if ($savedCount > 0): ?>
                                <span class="aps-cp-quick-action-badge"><?= $savedCount ?></span>
                                <?php endif; ?>
                            </p>
                            <p class="aps-cp-quick-action-desc"><?= __('dash_quick_saved_desc', null, 'Get alerts for new properties') ?></p>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>/user/saved-searches/manage-alerts" class="aps-cp-quick-action">
                        <div class="aps-cp-quick-action-icon"><i class="fas fa-bell"></i></div>
                        <div class="aps-cp-quick-action-body">
                            <p class="aps-cp-quick-action-title"><?= __('dash_btn_manage_email_alerts', null, 'Email Alerts') ?></p>
                            <p class="aps-cp-quick-action-desc"><?= __('dash_quick_alerts_desc', null, 'Manage notification preferences') ?></p>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>/user/referral" class="aps-cp-quick-action">
                        <div class="aps-cp-quick-action-icon"><i class="fas fa-gift"></i></div>
                        <div class="aps-cp-quick-action-body">
                            <p class="aps-cp-quick-action-title"><?= __('dash_btn_refer_earn', null, 'Refer & Earn') ?></p>
                            <p class="aps-cp-quick-action-desc"><?= __('dash_quick_refer_desc', null, 'Earn rewards per signup') ?></p>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>/user/tickets" class="aps-cp-quick-action">
                        <div class="aps-cp-quick-action-icon"><i class="fas fa-headset"></i></div>
                        <div class="aps-cp-quick-action-body">
                            <p class="aps-cp-quick-action-title"><?= __('dash_btn_my_tickets', null, 'My Tickets') ?></p>
                            <p class="aps-cp-quick-action-desc"><?= __('dash_quick_tickets_desc', null, 'Get help from support') ?></p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <?php
        $dashOpenTickets = (int)($stats['open_tickets'] ?? 0);
        $dashTotalTickets = (int)($stats['total_tickets'] ?? 0);
        ?>
        <div class="aps-cp-card mb-4" class="style-38523">
            <div class="aps-cp-card-header" class="style-70066">
                <h5><i class="fas fa-headset" class="style-54781"></i> <?= __('dash_need_help', null, 'Need Help?') ?></h5>
            </div>
            <div class="aps-cp-card-body text-center">
                <div class="d-flex justify-content-center gap-4 mb-3">
                    <div>
                        <div class="fw-bold fs-4" class="style-3908" data-aps-count="<?= $dashOpenTickets ?>">0</div>
                        <small class="text-muted"><?= __('dash_stat_open_tickets', null, 'Open Tickets') ?></small>
                    </div>
                    <div>
                        <div class="fw-bold fs-4 text-primary" data-aps-count="<?= $dashTotalTickets ?>">0</div>
                        <small class="text-muted"><?= __('dash_total_tickets_help', null, 'Total Tickets') ?></small>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/user/support/create" class="btn btn-success btn-sm w-100">
                    <i class="fas fa-plus me-1"></i><?= __('dash_btn_create_ticket', null, 'Create Support Ticket') ?>
                </a>
                <?php if ($dashTotalTickets > 0): ?>
                <a href="<?= BASE_URL ?>/user/support" class="btn btn-outline-success btn-sm w-100 mt-2">
                    <i class="fas fa-list me-1"></i><?= __('dash_btn_view_tickets', null, 'View All Tickets') ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-concierge-bell text-info"></i> <?= __('dash_section_our_services', null, 'Our Services') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="<?= BASE_URL ?>/properties" class="aps-cp-service">
                            <div class="aps-cp-service-icon"><i class="fas fa-home"></i></div>
                            <div class="aps-cp-service-title"><?= __('dash_service_buy', null, 'Buy') ?></div>
                            <div class="aps-cp-service-desc"><?= __('dash_service_buy_desc', null, 'Find your dream property') ?></div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= BASE_URL ?>/list-property" class="aps-cp-service">
                            <div class="aps-cp-service-icon"><i class="fas fa-building"></i></div>
                            <div class="aps-cp-service-title"><?= __('dash_service_sell', null, 'Sell') ?></div>
                            <div class="aps-cp-service-desc"><?= __('dash_service_sell_desc', null, 'List your property') ?></div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= BASE_URL ?>/services" class="aps-cp-service">
                            <div class="aps-cp-service-icon"><i class="fas fa-hand-holding-usd"></i></div>
                            <div class="aps-cp-service-title"><?= __('dash_service_services', null, 'Services') ?></div>
                            <div class="aps-cp-service-desc"><?= __('dash_service_services_desc', null, 'Loan, Legal & more') ?></div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= BASE_URL ?>/contact" class="aps-cp-service">
                            <div class="aps-cp-service-icon"><i class="fas fa-headset"></i></div>
                            <div class="aps-cp-service-title"><?= __('dash_service_support', null, 'Support') ?></div>
                            <div class="aps-cp-service-desc"><?= __('dash_service_support_desc', null, 'Get help') ?></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-user text-info"></i> <?= __('dash_section_account_info', null, 'Account Info') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-flex flex-column gap-2 small">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user text-muted me-2" class="style-18746"></i>
                        <strong class="me-2"><?= __('dash_label_name', null, 'Name') ?>:</strong>
                        <span class="text-truncate"><?= htmlspecialchars($user['name'] ?? $_SESSION['user_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-envelope text-muted me-2" class="style-18746"></i>
                        <strong class="me-2"><?= __('dash_label_email', null, 'Email') ?>:</strong>
                        <span class="text-truncate"><?= htmlspecialchars($user['email'] ?? $_SESSION['user_email'] ?? 'N/A') ?></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-phone text-muted me-2" class="style-18746"></i>
                        <strong class="me-2"><?= __('dash_label_phone', null, 'Phone') ?>:</strong>
                        <span class="text-truncate"><?= htmlspecialchars($user['phone'] ?? $_SESSION['user_phone'] ?? 'N/A') ?></span>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/user/profile" class="btn btn-outline-primary btn-sm w-100 mt-3">
                    <i class="fas fa-edit me-1"></i><?= __('dash_btn_edit_profile', null, 'Edit Profile') ?>
                </a>
            </div>
        </div>

        <div class="aps-cp-card" class="style-86209">
            <div class="aps-cp-card-header" class="style-17078">
                <h5><i class="fas fa-bell text-primary"></i> <?= __('dash_section_notifications', null, 'Notifications') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <p class="text-muted small mb-3"><?= __('dash_push_desc', null, 'Get notified about booking updates, payment reminders, and offers.') ?></p>
                <button id="push-toggle" class="btn btn-sm btn-primary w-100" onclick="PushNotifications.subscribe()">
                    <i class="fas fa-bell me-1"></i> <?= __('dash_btn_enable_push', null, 'Enable Notifications') ?>
                </button>
            </div>
        </div>

        <div class="aps-cp-card" class="style-16398">
            <div class="aps-cp-card-header" class="style-17078">
                <h5><i class="fas fa-shield-alt <?= $twoFactorEnabled ? 'text-success' : 'text-warning' ?>"></i> <?= __('dash_section_security', null, 'Security') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <?php if ($twoFactorEnabled): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="aps-cp-stat-icon" class="style-49988"><i class="fas fa-check-circle"></i></div>
                        <div class="ms-3">
                            <p class="mb-0 fw-bold text-success"><?= __('dash_2fa_enabled', null, '2FA Enabled') ?></p>
                            <small class="text-muted"><?= __('dash_2fa_protected', null, 'Your account is protected') ?></small>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/user/two-factor" class="btn btn-sm btn-outline-success w-100">
                        <i class="fas fa-cog me-1"></i> <?= __('dash_btn_manage_2fa', null, 'Manage 2FA') ?>
                    </a>
                <?php else: ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="aps-cp-stat-icon" class="style-22946"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="ms-3">
                            <p class="mb-0 fw-bold text-warning"><?= __('dash_2fa_disabled', null, '2FA Not Enabled') ?></p>
                            <small class="text-muted"><?= __('dash_2fa_extra_security', null, 'Add an extra layer of security') ?></small>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/user/two-factor" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-shield-alt me-1"></i> <?= __('dash_btn_enable_2fa', null, 'Enable 2FA') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $kycBg = match($kycStatus) {
            'approved' => 'linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%)',
            'pending' => 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)',
            'rejected' => 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)',
            default => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)'
        };
        $kycIcon = match($kycStatus) {
            'approved' => 'fas fa-check-circle',
            'pending' => 'fas fa-clock',
            'rejected' => 'fas fa-times-circle',
            default => 'fas fa-id-card'
        };
        $kycColor = match($kycStatus) {
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'primary'
        };
        $kycLabel = match($kycStatus) {
            'approved' => __('dash_kyc_verified', null, 'KYC Verified'),
            'pending' => __('dash_kyc_under_review', null, 'KYC Under Review'),
            'rejected' => __('dash_kyc_rejected', null, 'KYC Rejected'),
            default => __('dash_kyc_not_completed', null, 'KYC Not Completed')
        };
        $kycDesc = match($kycStatus) {
            'approved' => __('dash_kyc_verified_desc', null, 'Your identity is verified. All features are unlocked.'),
            'pending' => __('dash_kyc_pending_desc', null, 'Your documents are being reviewed. This usually takes 1-2 business days.'),
            'rejected' => __('dash_kyc_rejected_desc', null, 'Your KYC was rejected. Please re-submit with correct documents.'),
            default => __('dash_kyc_not_completed_desc', null, 'Complete your KYC to unlock property bookings, loans, and payouts.')
        };
        ?>
        <div class="aps-cp-card mt-4" class="style-259">
            <div class="aps-cp-card-header" class="style-17078">
                <h5><i class="fas fa-id-card text-<?= $kycColor ?>"></i> <?= $kycLabel ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="aps-cp-stat-icon" class="style-86186"><i class="<?= $kycIcon ?>"></i></div>
                    <div class="ms-3">
                        <p class="mb-0 fw-bold text-<?= $kycColor ?>"><?= $kycLabel ?></p>
                        <small class="text-muted"><?= $kycDesc ?></small>
                    </div>
                </div>
                <?php if ($kycStatus !== 'approved'): ?>
                <a href="<?= BASE_URL ?>/user/kyc" class="btn btn-sm btn-<?= $kycStatus === 'rejected' ? 'warning' : 'primary' ?> w-100">
                    <i class="fas fa-<?= $kycStatus === 'rejected' ? 'redo' : 'upload' ?> me-1"></i>
                    <?= $kycStatus === 'rejected' ? __('dash_btn_resubmit_kyc', null, 'Re-submit KYC') : __('dash_btn_complete_kyc', null, 'Complete KYC') ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
