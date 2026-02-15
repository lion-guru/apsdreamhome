<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">Welcome back, <?php echo h($user->uname); ?>!</h2>
            <p class="text-muted">Here's your professional overview as a <span class="badge bg-primary"><?php echo ucfirst($professional_type); ?></span></p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-plus me-1"></i> Quick Action
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/property/add"><i class="fas fa-building me-2"></i> Add Listing</a></li>
                    <li><a class="dropdown-item" href="/leads/add"><i class="fas fa-user-plus me-2"></i> New Lead</a></li>
                    <li><a class="dropdown-item" href="/visits/schedule"><i class="fas fa-calendar-plus me-2"></i> Schedule Visit</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                            <i class="fas fa-users text-primary fa-lg"></i>
                        </div>
                        <h6 class="card-title mb-0">Total Leads</h6>
                    </div>
                    <h3 class="mb-0"><?php echo $stats['total_leads']; ?></h3>
                    <small class="text-success"><i class="fas fa-arrow-up me-1"></i> New this week</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning bg-opacity-10 p-2 rounded me-3">
                            <i class="fas fa-star text-warning fa-lg"></i>
                        </div>
                        <h6 class="card-title mb-0">Active Listings</h6>
                    </div>
                    <h3 class="mb-0"><?php echo $stats['active_listings']; ?></h3>
                    <small class="text-muted">Live on portal</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 p-2 rounded me-3">
                            <i class="fas fa-rupee-sign text-success fa-lg"></i>
                        </div>
                        <h6 class="card-title mb-0">Total Earned</h6>
                    </div>
                    <h3 class="mb-0">₹<?php echo number_format($accounting['total_earned'], 2); ?></h3>
                    <small class="text-muted">Paid to bank</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-danger bg-opacity-10 p-2 rounded me-3">
                            <i class="fas fa-wallet text-danger fa-lg"></i>
                        </div>
                        <h6 class="card-title mb-0">Pending</h6>
                    </div>
                    <h3 class="mb-0">₹<?php echo number_format($accounting['pending_commission'], 2); ?></h3>
                    <small class="text-warning">Awaiting clearance</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Recent Leads -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Recent Leads</h5>
                    <a href="/leads" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Name</th>
                                    <th>Interest</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_leads)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No leads found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_leads as $lead): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold"><?php echo h($lead['name']); ?></div>
                                            <small class="text-muted"><?php echo h($lead['phone']); ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-dark"><?php echo h($lead['property_interest'] ?? 'General'); ?></span></td>
                                        <td><span class="badge bg-<?php echo $lead['status'] === 'new' ? 'info' : 'secondary'; ?>"><?php echo ucfirst($lead['status']); ?></span></td>
                                        <td><?php echo date('d M, Y', strtotime($lead['created_at'])); ?></td>
                                        <td class="text-end pe-3">
                                            <a href="/leads/view/<?php echo $lead['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Lekha-Jhokha (Accounting) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Lekha-Jhokha (Recent Transactions)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Transaction ID</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($accounting['recent_transactions'])): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No transactions recorded.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($accounting['recent_transactions'] as $tx): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold">#<?php echo $tx['id']; ?></td>
                                        <td class="text-success fw-bold">₹<?php echo number_format($tx['amount'], 2); ?></td>
                                        <td><span class="badge bg-<?php echo $tx['status'] === 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($tx['status']); ?></span></td>
                                        <td><?php echo date('d M, Y', strtotime($tx['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Tools -->
        <div class="col-lg-4">
            <!-- AI Professional Assistant -->
            <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="fas fa-robot me-2"></i> AI Assistant</h5>
                    <p class="small">Get property descriptions, lead scoring, and market insights instantly.</p>
                    <a href="/ai/hub" class="btn btn-light btn-sm w-100">Open AI Hub</a>
                </div>
            </div>

            <!-- System Alerts & Notifications -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Recent Alerts</h5>
                    <?php if (!empty($system_alerts)): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo count($system_alerts); ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($system_alerts)): ?>
                            <li class="list-group-item text-center py-4 text-muted small">No new alerts.</li>
                        <?php else: ?>
                            <?php foreach ($system_alerts as $alert): ?>
                            <li class="list-group-item border-0 px-3 py-2">
                                <div class="d-flex align-items-start">
                                    <div class="bg-<?php echo $alert['type'] ?? 'info'; ?> bg-opacity-10 p-2 rounded me-3 mt-1">
                                        <i class="fas fa-bell text-<?php echo $alert['type'] ?? 'info'; ?> small"></i>
                                    </div>
                                    <div>
                                        <div class="small fw-bold"><?php echo h($alert['title']); ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?php echo h($alert['message']); ?></div>
                                        <small class="text-muted" style="font-size: 0.7rem;"><?php echo date('d M, h:i A', strtotime($alert['created_at'])); ?></small>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Upcoming Visits -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Upcoming Visits</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($upcoming_visits)): ?>
                            <li class="list-group-item text-center py-4 text-muted">No scheduled visits.</li>
                        <?php else: ?>
                            <?php foreach ($upcoming_visits as $visit): ?>
                            <li class="list-group-item border-0 px-3 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold"><?php echo h($visit['name']); ?></div>
                                        <small class="text-muted"><i class="far fa-clock me-1"></i> <?php echo date('d M, h:i A', strtotime($visit['visit_date'] . ' ' . $visit['visit_time'])); ?></small>
                                    </div>
                                    <span class="badge bg-info bg-opacity-10 text-info">Upcoming</span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Specialized Tools based on Role -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Specialized Tools</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php if ($professional_type === 'builder'): ?>
                            <a href="/projects/inventory" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-boxes text-primary me-2"></i> Inventory Management
                            </a>
                            <a href="/construction/workflow" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-tasks text-primary me-2"></i> Project Workflow
                            </a>
                        <?php elseif ($professional_type === 'contractor'): ?>
                            <a href="/expenses/manage" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-calculator text-primary me-2"></i> Expense Tracker
                            </a>
                            <a href="/labor/management" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-hard-hat text-primary me-2"></i> Labor Records
                            </a>
                        <?php else: ?>
                            <a href="/marketing/whatsapp" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fab fa-whatsapp text-success me-2"></i> WhatsApp Marketing
                            </a>
                            <a href="/referrals" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-handshake text-primary me-2"></i> Referral Program
                            </a>
                        <?php endif; ?>
                        <a href="/documents" class="list-group-item list-group-item-action border-0 px-0">
                            <i class="fas fa-file-contract text-primary me-2"></i> Document Vault
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
