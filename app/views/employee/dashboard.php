<?php $pageTitle = 'Employee Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee/dashboard">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Employee Dashboard</h4>
        <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i><?= date('l, F j, Y') ?></span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-tasks"></i></div>
                    <h3 class="fw-bold mb-1"><?= $totalTasks ?? 0 ?></h3>
                    <p class="text-muted mb-0">Total Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-building"></i></div>
                    <h3 class="fw-bold mb-1"><?= $totalProperties ?? 0 ?></h3>
                    <p class="text-muted mb-0">Properties</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="fas fa-envelope"></i></div>
                    <h3 class="fw-bold mb-1"><?= $totalInquiries ?? 0 ?></h3>
                    <p class="text-muted mb-0">Inquiries</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-calendar-check"></i></div>
                    <h3 class="fw-bold mb-1"><?= $upcomingVisits ?? 0 ?></h3>
                    <p class="text-muted mb-0">Upcoming Visits</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($recentActivity)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="list-group-item border-0 ps-0">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3"><span class="badge bg-light text-dark p-2"><i class="far fa-circle"></i></span></div>
                                        <div>
                                            <p class="mb-0 small"><?= htmlspecialchars($activity['description'] ?? '') ?></p>
                                            <small class="text-muted"><?= htmlspecialchars($activity['created_at'] ?? '') ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-inbox fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No recent activity</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-bell me-2"></i>Notifications</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($notifications)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notifications as $notif): ?>
                                <div class="list-group-item border-0 px-0">
                                    <small><?= htmlspecialchars($notif['message'] ?? '') ?></small>
                                    <br><small class="text-muted"><?= htmlspecialchars($notif['created_at'] ?? '') ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-check-circle fa-2x text-success mb-2"></i><p class="text-muted mb-0">All clear!</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
