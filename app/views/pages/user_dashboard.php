<?php
$page_title = $page_title ?? 'My Dashboard';
$current_page = 'dashboard';

// Data from controller (with safe fallbacks)
$stats = $stats ?? ['total_properties' => 0, 'active_inquiries' => 0, 'total_bookings' => 0, 'total_inquiries' => 0];
$properties = $properties ?? [];
$inquiries = $inquiries ?? [];
$bookings = $bookings ?? [];
$user = $user ?? [];
?>

<!-- Welcome Banner -->
<div class="card border-0 shadow-sm mb-4 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? $user['name'] ?? 'Customer'); ?>!</h4>
                <p class="mb-0 opacity-75">Manage your properties, track inquiries and purchases all in one place.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>Post Property
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-building"></i></div>
            <div class="stat-value"><?php echo $stats['total_properties']; ?></div>
            <div class="stat-label">My Properties</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-envelope"></i></div>
            <div class="stat-value"><?php echo $stats['active_inquiries']; ?></div>
            <div class="stat-label">Active Inquiries</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-file-invoice"></i></div>
            <div class="stat-value"><?php echo $stats['total_bookings']; ?></div>
            <div class="stat-label">My Purchases</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-chart-bar"></i></div>
            <div class="stat-value"><?php echo $stats['total_inquiries']; ?></div>
            <div class="stat-label">Total Inquiries</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- My Purchases (Bookings) -->
        <?php if (!empty($bookings)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-file-invoice text-success me-2"></i>My Purchases</h5>
                    <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Plot</th>
                                <th>Colony</th>
                                <th>Amount</th>
                                <th>Token Paid</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                            <?php
                            $bStatus = $b['status'] ?? 'pending';
                            $badgeClass = match($bStatus) {
                                'confirmed','completed' => 'success',
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
                                        <br><small class="text-muted"><?= round(($tokenPaid / $tokenRequired) * 100) ?>% of token</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($bStatus) ?></span>
                                    <?php if ($bStatus === 'pending'): ?>
                                        <br><small class="text-warning">Awaiting approval</small>
                                    <?php elseif ($bStatus === 'confirmed'): ?>
                                        <br><small class="text-success">Approved</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($b['created_at'] ?? $b['booking_date'] ?? 'now')) ?></td>
                                <td>
                                    <?php if ($bStatus === 'pending' && $tokenRequired > $tokenPaid): ?>
                                        <a href="<?= BASE_URL ?>/booking/<?= $b['id'] ?>/pay" class="btn btn-success btn-sm"><i class="fas fa-credit-card me-1"></i>Pay Token</a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/booking/<?= $b['id'] ?>/confirmation" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>/booking/<?= $b['id'] ?>/receipt" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- My Properties -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-building text-primary me-2"></i>My Properties</h5>
                    <a href="<?php echo BASE_URL; ?>/user/properties" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($properties)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-building fa-3x mb-3"></i>
                        <p>No properties listed yet. Post your first property!</p>
                        <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-primary btn-sm">Post Property</a>
                    </div>
                <?php else: ?>
                <div class="row g-3">
                    <?php foreach (array_slice($properties, 0, 4) as $property): ?>
                        <div class="col-md-6">
                            <div class="property-card border rounded-3 p-3">
                                <div class="d-flex gap-3">
                                    <div class="property-image bg-light rounded-3" style="width: 80px; height: 80px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-home fa-2x text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($property['property_type'] ?? $property['title'] ?? 'Property'); ?></h6>
                                        <p class="text-muted mb-1 small"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($property['address'] ?? $property['location'] ?? ''); ?></p>
                                        <p class="mb-2"><strong>₹<?php echo number_format($property['price'] ?? 0); ?></strong></p>
                                        <span class="badge bg-<?php echo ($property['status'] ?? '') === 'approved' || ($property['status'] ?? '') === 'active' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($property['status'] ?? 'pending'); ?>
                                        </span>
                                        <?php if (($property['status'] ?? '') === 'approved' && !empty($property['id'])): ?>
                                        <a href="<?php echo BASE_URL; ?>/listing/<?php echo $property['id']; ?>" class="btn btn-sm btn-outline-primary mt-1" target="_blank">
                                            <i class="fas fa-external-link-alt"></i> View
                                        </a>
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

        <!-- Recent Inquiries -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-envelope text-success me-2"></i>Recent Inquiries</h5>
                    <a href="<?php echo BASE_URL; ?>/user/inquiries" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($inquiries)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-envelope fa-3x mb-3"></i>
                        <p>No inquiries yet.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $inquiry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inquiry['subject'] ?? $inquiry['message'] ?? 'Inquiry'); ?></td>
                                    <td><?php echo htmlspecialchars($inquiry['type'] ?? 'General'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($inquiry['status'] ?? '') === 'replied' ? 'success' : (($inquiry['status'] ?? '') === 'pending' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($inquiry['status'] ?? 'pending'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d', strtotime($inquiry['created_at'] ?? 'now')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

        <!-- Recent Payments -->
        <div class="card shadow-sm mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Recent Payments</h5>
                <a href="<?= BASE_URL ?>/user/payments" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Date</th><th>Transaction</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if (!empty($recentPayments)): foreach ($recentPayments as $pmt): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($pmt['created_at'] ?? '')) ?></td>
                                    <td><small><?= htmlspecialchars(substr($pmt['transaction_id'] ?? $pmt['receipt'] ?? '', 0, 20)) ?></small></td>
                                    <td><strong>₹<?= number_format($pmt['amount'] ?? 0, 2) ?></strong></td>
                                    <td><span class="badge bg-<?= ($pmt['status'] ?? '') === 'completed' ? 'success' : (($pmt['status'] ?? '') === 'pending' ? 'warning' : 'secondary') ?>"><?= htmlspecialchars($pmt['status'] ?? 'N/A') ?></span></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No payments yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <?php if (!empty($referral_code)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-gift text-danger me-2"></i>Refer & Earn</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="display-6 fw-bold text-danger mb-1"><?= htmlspecialchars($referral_code) ?></div>
                    <small class="text-muted">Your Referral Code</small>
                </div>
                <div class="row text-center g-2 mb-3">
                    <div class="col-6">
                        <div class="bg-light rounded-3 p-2">
                            <div class="fw-bold text-primary"><?= (int)$referral_count ?></div>
                            <small class="text-muted">Referrals</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded-3 p-2">
                            <div class="fw-bold text-success">₹<?= number_format($referral_earnings, 2) ?></div>
                            <small class="text-muted">Earnings</small>
                        </div>
                    </div>
                </div>
                <?php if (!empty($referral_link)): ?>
                <div class="input-group input-group-sm mb-2">
                    <input type="text" class="form-control" id="refLink" value="<?= htmlspecialchars($referral_link) ?>" readonly>
                    <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('refLink').value);this.innerHTML='Copied!';setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i>',2000)"><i class="fas fa-copy"></i></button>
                </div>
                <small class="text-muted d-block mb-2">Share this link to earn rewards</small>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="https://wa.me/?text=<?= urlencode('Join APS Dream Home using my referral code: ' . $referral_code . ' - ' . $referral_link) ?>" target="_blank" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($referral_link) ?>" target="_blank" class="btn btn-sm btn-primary"><i class="fab fa-facebook"></i></a>
                    <a href="mailto:?subject=Join APS Dream Home&body=Use my referral code <?= $referral_code ?> to register: <?= urlencode($referral_link) ?>" class="btn btn-sm btn-secondary"><i class="fas fa-envelope"></i></a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-user-check text-purple me-2"></i>Account Info</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Post New Property
                    </a>
                    <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                    <a href="<?php echo BASE_URL; ?>/plots" class="btn btn-outline-success">
                        <i class="fas fa-vector-square me-2"></i>Browse Plots
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/inquiries" class="btn btn-outline-success">
                        <i class="fas fa-envelope me-2"></i>My Inquiries
                    </a>
                    <a href="<?php echo BASE_URL; ?>/payment/history" class="btn btn-outline-success">
                        <i class="fas fa-credit-card me-1"></i> Payment History
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/profile" class="btn btn-outline-info">
                        <i class="fas fa-user me-2"></i>Edit Profile
                    </a>
                    <a href="<?= BASE_URL ?>/dashboard/favorites" class="btn btn-outline-danger">
                        <i class="fas fa-heart me-2"></i>My Favorites
                    </a>
                    <a href="<?= BASE_URL ?>/user/book-site-visit" class="btn btn-outline-info">
                        <i class="fas fa-calendar-check me-1"></i> Book Site Visit
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-concierge-bell text-info me-2"></i>Our Services</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="<?php echo BASE_URL; ?>/properties" class="text-decoration-none">
                            <div class="service-card text-center p-3 border rounded-3 h-100">
                                <div class="service-icon mb-2 text-primary"><i class="fas fa-home fa-2x"></i></div>
                                <h6 class="mb-1">Buy</h6>
                                <small class="text-muted">Find your dream property</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo BASE_URL; ?>/list-property" class="text-decoration-none">
                            <div class="service-card text-center p-3 border rounded-3 h-100">
                                <div class="service-icon mb-2 text-success"><i class="fas fa-building fa-2x"></i></div>
                                <h6 class="mb-1">Sell</h6>
                                <small class="text-muted">List your property</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo BASE_URL; ?>/services" class="text-decoration-none">
                            <div class="service-card text-center p-3 border rounded-3 h-100">
                                <div class="service-icon mb-2 text-info"><i class="fas fa-hand-holding-usd fa-2x"></i></div>
                                <h6 class="mb-1">Services</h6>
                                <small class="text-muted">Loan, Legal & more</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo BASE_URL; ?>/contact" class="text-decoration-none">
                            <div class="service-card text-center p-3 border rounded-3 h-100">
                                <div class="service-icon mb-2 text-warning"><i class="fas fa-headset fa-2x"></i></div>
                                <h6 class="mb-1">Support</h6>
                                <small class="text-muted">Get help</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-user-check text-purple me-2"></i>Account Info</h5>
            </div>
            <div class="card-body">
                <div class="small">
                    <p class="mb-2"><strong><i class="fas fa-user me-1"></i>Name:</strong> <?php echo htmlspecialchars($user['name'] ?? $_SESSION['user_name'] ?? 'N/A'); ?></p>
                    <p class="mb-2"><strong><i class="fas fa-envelope me-1"></i>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? $_SESSION['user_email'] ?? 'N/A'); ?></p>
                    <p class="mb-0"><strong><i class="fas fa-phone me-1"></i>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? $_SESSION['user_phone'] ?? 'N/A'); ?></p>
                </div>
                <a href="<?php echo BASE_URL; ?>/user/profile" class="btn btn-outline-primary btn-sm w-100 mt-3">
                    <i class="fas fa-edit me-1"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.1); border:1px solid #e2e8f0; height:100%; }
.stat-icon { width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; margin-bottom:15px; }
.stat-icon.blue { background:rgba(59,130,246,0.1); color:#3b82f6; }
.stat-icon.green { background:rgba(16,185,129,0.1); color:#10b981; }
.stat-icon.orange { background:rgba(245,158,11,0.1); color:#f59e0b; }
.stat-icon.purple { background:rgba(139,92,246,0.1); color:#8b5cf6; }
.stat-value { font-size:1.75rem; font-weight:700; color:#1e293b; margin-bottom:5px; }
.stat-label { font-size:0.875rem; color:#64748b; }
.property-card { transition:all 0.2s ease; }
.property-card:hover { box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.service-card { transition:all 0.2s ease; }
.service-card:hover { box-shadow:0 4px 12px rgba(0,0,0,0.1); transform:translateY(-2px); }
</style>
