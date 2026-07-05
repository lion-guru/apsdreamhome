<?php
$page_title = $page_title ?? __('assoc_cust_title', [], 'My Customers');
$current_page = 'my-customers';
$customers = $customers ?? [];
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #14b8a6 100%); color: #fff;">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold"><?= count($customers) ?></div>
                <div class="small opacity-75"><?= __('assoc_cust_total', [], 'Total Customers') ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <?php $totalBiz = array_sum(array_column($customers, 'total_business')); ?>
                <div class="fs-2 fw-bold text-success">₹<?= number_format($totalBiz) ?></div>
                <div class="small text-muted"><?= __('assoc_cust_total_biz', [], 'Total Business') ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <?php $totalBookings = array_sum(array_column($customers, 'booking_count')); ?>
                <div class="fs-2 fw-bold text-primary"><?= $totalBookings ?></div>
                <div class="small text-muted"><?= __('assoc_cust_total_bookings', [], 'Total Bookings') ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <?php $converted = count(array_filter($customers, fn($c) => ($c['is_associate'] ?? 0) == 1)); ?>
                <div class="fs-2 fw-bold text-warning"><?= $converted ?></div>
                <div class="small text-muted"><?= __('assoc_cust_became_assoc', [], 'Became Associates') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-address-book me-2 text-primary"></i><?= __('assoc_cust_title', [], 'My Customers') ?></h5>
        <div>
            <input type="text" id="searchCustomer" class="form-control form-control-sm" placeholder="<?= __('assoc_cust_search', [], 'Search customers...') ?>" style="width: 200px;" oninput="filterCustomers()">
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($customers)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted"><?= __('assoc_cust_empty', [], 'No customers yet') ?></h5>
                <p class="text-muted"><?= __('assoc_cust_empty_desc', [], 'Your customers will appear here once you make sales.') ?></p>
            </div>
        <?php else: ?>
            <div class="row g-3" id="customerList">
                <?php foreach ($customers as $c): ?>
                    <?php
                    $isAssociate = ($c['is_associate'] ?? 0) == 1;
                    $totalPaid = $c['total_paid'] ?? 0;
                    $pendingAmount = ($c['total_business'] ?? 0) - $totalPaid;
                    ?>
                    <div class="col-md-6 col-lg-4 customer-card" data-name="<?= strtolower($c['name'] ?? '') ?>" data-phone="<?= $c['phone'] ?? '' ?>">
                        <div class="card border h-100 <?= $isAssociate ? 'border-success' : '' ?>" style="<?= $isAssociate ? 'border-left: 4px solid #10b981 !important;' : '' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" 
                                             style="width: 45px; height: 45px; background: <?= $isAssociate ? '#dcfce7' : '#f3f4f6' ?>; color: <?= $isAssociate ? '#10b981' : '#6b7280' ?>;">
                                            <i class="fas fa-<?= $isAssociate ? 'user-tie' : 'user' ?>"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($c['name'] ?? __('assoc_cust_na', [], 'N/A')) ?></h6>
                                            <small class="text-muted"><?= __('assoc_cust_since', [], 'Since') ?> <?= date('M Y', strtotime($c['registered_date'] ?? '')) ?></small>
                                        </div>
                                    </div>
                                    <?php if ($isAssociate): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i><?= __('assoc_cust_associate', [], 'Associate') ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <?php if (!empty($c['phone'])): ?>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-phone text-muted me-2" style="width: 16px;"></i>
                                            <a href="tel:<?= $c['phone'] ?>" class="text-decoration-none"><?= htmlspecialchars($c['phone']) ?></a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($c['email'])): ?>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-envelope text-muted me-2" style="width: 16px;"></i>
                                            <a href="mailto:<?= $c['email'] ?>" class="text-decoration-none small"><?= htmlspecialchars($c['email']) ?></a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="p-2 rounded text-center" style="background: #f8fafc;">
                                            <div class="small text-muted"><?= __('assoc_cust_bookings_label', [], 'Bookings') ?></div>
                                            <div class="fw-bold text-primary"><?= $c['booking_count'] ?? 0 ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded text-center" style="background: #f8fafc;">
                                            <div class="small text-muted"><?= __('assoc_cust_business_label', [], 'Business') ?></div>
                                            <div class="fw-bold text-success">₹<?= number_format($c['total_business'] ?? 0) ?></div>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($c['plots'])): ?>
                                    <div class="mb-3">
                                        <div class="small text-muted mb-1"><i class="fas fa-map me-1"></i><?= __('assoc_cust_properties_label', [], 'Properties') ?>:</div>
                                        <?php foreach (array_slice($c['plots'], 0, 2) as $plot): ?>
                                            <div class="d-flex justify-content-between small">
                                                <span><?= __('assoc_cust_plot', [], 'Plot') ?> #<?= htmlspecialchars($plot['plot_number'] ?? '') ?></span>
                                                <span class="text-muted"><?= htmlspecialchars($plot['colony_name'] ?? '') ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($c['plots']) > 2): ?>
                                            <small class="text-primary">+<?= count($c['plots']) - 2 ?> <?= __('assoc_cust_more', [], 'more') ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($pendingAmount > 0): ?>
                                    <div class="alert alert-warning py-1 px-2 mb-3">
                                        <small><i class="fas fa-exclamation-triangle me-1"></i>₹<?= number_format($pendingAmount) ?> <?= __('assoc_cust_pending', [], 'pending') ?></small>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($c['last_booking_date'])): ?>
                                    <div class="small text-muted mb-3">
                                        <i class="fas fa-calendar me-1"></i><?= __('assoc_cust_last_booking', [], 'Last booking') ?>: <?= date('d M Y', strtotime($c['last_booking_date'])) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex flex-wrap gap-1">
                                    <?php if (!empty($c['phone'])): ?>
                                        <a href="tel:<?= $c['phone'] ?>" class="btn btn-outline-primary btn-sm" title="<?= __('assoc_cust_call', [], 'Call') ?>">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $c['phone']) ?>" class="btn btn-outline-success btn-sm" target="_blank" title="<?= __('assoc_cust_whatsapp', [], 'WhatsApp') ?>">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/associate/customer/<?= $c['id'] ?>" class="btn btn-outline-info btn-sm" title="<?= __('assoc_cust_view_details', [], 'View Full Details') ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (!$isAssociate): ?>
                                        <button class="btn btn-outline-warning btn-sm" onclick="inviteAsAssociate(<?= $c['id'] ?>, '<?= htmlspecialchars($c['name'] ?? '') ?>', '<?= htmlspecialchars($c['phone'] ?? '') ?>')" title="<?= __('assoc_cust_invite_assoc', [], 'Invite as Associate') ?>">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="inviteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i><?= __('assoc_cust_invite_title', [], 'Invite as Associate') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><?= __('assoc_cust_invite_intro', [], 'Invite') ?> <strong id="inviteName"></strong> <?= __('assoc_cust_invite_intro2', [], 'to become an associate and earn together!') ?></p>
                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i><?= __('assoc_cust_invite_benefits', [], 'What they\'ll get') ?>:</h6>
                    <ul class="mb-0 small">
                        <li><strong><?= __('assoc_cust_invite_b1', [], '5% commission') ?></strong> <?= __('assoc_cust_invite_b1d', [], 'on every plot sale they make') ?></li>
                        <li><strong><?= __('assoc_cust_invite_b2', [], 'Free Mobile') ?></strong> <?= __('assoc_cust_invite_b2d', [], 'when they achieve Associate rank') ?></li>
                        <li><strong><?= __('assoc_cust_invite_b3', [], 'Tablet, Laptop, Tour, Bike, Bullet, Car') ?></strong> <?= __('assoc_cust_invite_b3d', [], 'at higher ranks') ?></li>
                        <li><?= __('assoc_cust_invite_b4', [], 'Build their own team and earn from team sales') ?></li>
                        <li><?= __('assoc_cust_invite_b5', [], 'Full training and support from APS Dream Home') ?></li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold"><?= __('assoc_cust_invite_message', [], 'Personal Message') ?> (<?= __('assoc_cust_optional', [], 'Optional') ?>)</label>
                    <textarea id="inviteMessage" class="form-control" rows="3" placeholder="<?= __('assoc_cust_invite_placeholder', [], 'Hi! I\'ve been working with APS Dream Home and it\'s been great. You should check it out too!') ?>"><?= __('assoc_cust_invite_default_msg', [], 'Hi! I\'ve been working with APS Dream Home and earning good commissions. You should join as an associate too - let me know if you\'re interested!') ?></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('assoc_cust_cancel', [], 'Cancel') ?></button>
                <a id="inviteWhatsapp" href="#" class="btn btn-success" target="_blank">
                    <i class="fab fa-whatsapp me-1"></i><?= __('assoc_cust_send_invite', [], 'Send WhatsApp Invite') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function filterCustomers() {
    const search = document.getElementById('searchCustomer').value.toLowerCase();
    document.querySelectorAll('.customer-card').forEach(card => {
        const name = card.dataset.name || '';
        const phone = card.dataset.phone || '';
        card.style.display = (name.includes(search) || phone.includes(search)) ? '' : 'none';
    });
}

function inviteAsAssociate(id, name, phone) {
    document.getElementById('inviteName').textContent = name;
    const message = document.getElementById('inviteMessage').value;
    const url = `https://wa.me/${phone.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(message + '\n\n<?= __('assoc_cust_join_link', [], 'Join here') ?>: ' + window.location.origin + '/register?ref=' + '<?= $_SESSION['referral_code'] ?? '' ?>')}`;
    document.getElementById('inviteWhatsapp').href = url;
    new bootstrap.Modal(document.getElementById('inviteModal')).show();
}
</script>
