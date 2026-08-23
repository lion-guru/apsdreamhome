<?php
$employee = $employee ?? null;
$manager = $manager ?? null;
$subordinates = $subordinates ?? [];
$department_members = $department_members ?? [];

function rpInitials($name) {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $p) $initials .= strtoupper($p[0] ?? '');
    return $initials;
}
function rpPerformanceColor($score) {
    if ($score >= 80) return 'success';
    if ($score >= 60) return 'info';
    if ($score >= 40) return 'warning';
    return 'danger';
}
function rpLevelBadge($role) {
    $map = ['director' => 'danger', 'manager' => 'primary', 'executive' => 'info', 'senior' => 'warning', 'junior' => 'secondary'];
    $lower = strtolower($role ?? '');
    foreach ($map as $k => $v) { if (strpos($lower, $k) !== false) return $v; }
    return 'secondary';
}
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.emp-rp-org { display: flex; flex-direction: column; align-items: center; gap: 0; }
.emp-rp-node { background: #fff; border: 2px solid #e2e8f0; border-radius: 14px; padding: 16px 24px; text-align: center; min-width: 200px; max-width: 280px; transition: all 0.2s; position: relative; }
.emp-rp-node:hover { border-color: #7c2d12; box-shadow: 0 4px 12px rgba(124,45,18,0.1); transform: translateY(-2px); }
.emp-rp-node.manager { border-color: #3b82f6; background: linear-gradient(135deg, #eff6ff 0%, #fff 100%); }
.emp-rp-node.self { border-color: #7c2d12; background: linear-gradient(135deg, #fef3c7 0%, #fff 100%); box-shadow: 0 4px 15px rgba(124,45,18,0.15); }
.emp-rp-connector { width: 2px; height: 30px; background: #cbd5e1; margin: 0 auto; }
.emp-rp-branch { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; position: relative; }
.emp-rp-branch::before { content: ''; position: absolute; top: 0; left: 25%; right: 25%; height: 2px; background: #cbd5e1; }
.emp-rp-avatar { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; color: #fff; margin: 0 auto 8px; }
.emp-rp-sub-card { border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; transition: all 0.2s; background: #fff; }
.emp-rp-sub-card:hover { border-color: #7c2d12; box-shadow: 0 2px 10px rgba(0,0,0,0.06); transform: translateY(-1px); }
.emp-rp-dept-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; transition: all 0.2s; }
.emp-rp-dept-card:hover { background: #f8fafc; }
.emp-rp-progress { height: 5px; border-radius: 3px; background: #e2e8f0; overflow: hidden; }
.emp-rp-progress-fill { height: 100%; border-radius: 3px; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-sitemap me-2 text-primary"></i>Reporting Structure</h4>
            <p class="text-muted mb-0 small">Your team hierarchy and department</p>
        </div>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
    </div>

    <?php if (!$employee): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-user-slash fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted">Employee Data Not Found</h5>
                <p class="text-muted small">Your employee profile could not be loaded.</p>
            </div>
        </div>
    <?php else: ?>

        <!-- Org Chart: Manager â†' You â†' Subordinates -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-bottom-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-project-diagram me-2 text-primary"></i>Org Chart</h6>
            </div>
            <div class="card-body">
                <div class="emp-rp-org">
                    <?php if ($manager): ?>
                        <div class="emp-rp-node manager">
                            <div class="emp-rp-avatar style-5818"><?= rpInitials($manager['name']) ?></div>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($manager['name'] ?? '') ?></div>
                            <div class="text-primary small fw-semibold"><?= htmlspecialchars($manager['designation'] ?? $manager['role'] ?? '') ?></div>
                            <div class="text-muted style-20558"><?= htmlspecialchars($manager['email'] ?? '') ?></div>
                        </div>
                        <div class="emp-rp-connector"></div>
                        <div class="text-center mb-1"><small class="text-muted"><i class="fas fa-arrow-down me-1"></i>Reports to</small></div>
                        <div class="emp-rp-connector"></div>
                    <?php endif; ?>

                    <!-- Self -->
                    <div class="emp-rp-node self">
                        <div class="emp-rp-avatar style-63151"><?= rpInitials($employee['name']) ?></div>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($employee['name'] ?? '') ?></div>
                        <div class="text-primary small fw-semibold"><?= htmlspecialchars($employee['designation'] ?? $employee['role'] ?? '') ?></div>
                        <div class="text-muted style-20558"><?= htmlspecialchars($employee['department'] ?? '') ?></div>
                        <span class="badge bg-primary bg-opacity-10 text-primary mt-1">You</span>
                    </div>

                    <?php if (!empty($subordinates)): ?>
                        <div class="emp-rp-connector"></div>
                        <div class="text-center mb-1"><small class="text-muted"><i class="fas fa-arrow-down me-1"></i><?= count($subordinates) ?> Direct Report<?= count($subordinates) > 1 ? 's' : '' ?></small></div>
                        <div class="emp-rp-connector"></div>
                        <div class="emp-rp-branch mt-1">
                            <?php foreach ($subordinates as $sub): ?>
                                <div class="emp-rp-node style-68650">
                                    <div class="emp-rp-avatar style-50771"><?= rpInitials($sub['name']) ?></div>
                                    <div class="fw-semibold text-dark small"><?= htmlspecialchars($sub['name'] ?? '') ?></div>
                                    <div class="text-muted style-68658"><?= htmlspecialchars($sub['designation'] ?? '') ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Manager Details -->
            <?php if ($manager): ?>
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-transparent border-bottom-0 pt-3">
                            <h6 class="fw-bold mb-0"><i class="fas fa-user-tie me-2 text-primary"></i>My Manager</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="emp-rp-avatar style-23656">
                                    <?= rpInitials($manager['name']) ?>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold"><?= htmlspecialchars($manager['name'] ?? '') ?></h5>
                                    <div class="text-primary fw-semibold"><?= htmlspecialchars($manager['designation'] ?? '') ?></div>
                                    <span class="badge bg-<?= rpLevelBadge($manager['role'] ?? '') ?> bg-opacity-10 text-<?= rpLevelBadge($manager['role'] ?? '') ?>"><?= htmlspecialchars($manager['role'] ?? '') ?></span>
                                </div>
                            </div>
                            <div class="bg-light rounded-3 p-3">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-muted small mb-1"><i class="fas fa-envelope me-1"></i>Email</div>
                                        <div class="fw-semibold small"><?= htmlspecialchars($manager['email'] ?? '—') ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small mb-1"><i class="fas fa-phone me-1"></i>Phone</div>
                                        <div class="fw-semibold small"><?= htmlspecialchars($manager['phone'] ?? '—') ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small mb-1"><i class="fas fa-building me-1"></i>Department</div>
                                        <div class="fw-semibold small"><?= htmlspecialchars($employee['department'] ?? '—') ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small mb-1"><i class="fas fa-id-badge me-1"></i>Employee ID</div>
                                        <div class="fw-semibold small">#<?= $manager['id'] ?? '—' ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- My Profile Card -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-bottom-0 pt-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-id-card me-2 text-primary"></i>My Position</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="emp-rp-avatar style-29278">
                                <?= rpInitials($employee['name']) ?>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold"><?= htmlspecialchars($employee['name'] ?? '') ?></h5>
                                <div class="text-primary fw-semibold"><?= htmlspecialchars($employee['designation'] ?? '') ?></div>
                                <span class="badge bg-primary">You</span>
                            </div>
                        </div>
                        <div class="bg-light rounded-3 p-3">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="text-muted small mb-1"><i class="fas fa-building me-1"></i>Department</div>
                                    <div class="fw-semibold small"><?= htmlspecialchars($employee['department'] ?? '—') ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small mb-1"><i class="fas fa-id-badge me-1"></i>Role</div>
                                    <div class="fw-semibold small"><?= htmlspecialchars($employee['role'] ?? '—') ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small mb-1"><i class="fas fa-envelope me-1"></i>Email</div>
                                    <div class="fw-semibold small"><?= htmlspecialchars($employee['email'] ?? '—') ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small mb-1"><i class="fas fa-phone me-1"></i>Phone</div>
                                    <div class="fw-semibold small"><?= htmlspecialchars($employee['phone'] ?? '—') ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small mb-1"><i class="fas fa-users me-1"></i>Direct Reports</div>
                                    <div class="fw-semibold small"><?= count($subordinates) ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small mb-1"><i class="fas fa-signal me-1"></i>Status</div>
                                    <span class="badge bg-<?= ($employee['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars(ucfirst($employee['status'] ?? 'Active')) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subordinates / Team -->
        <?php if (!empty($subordinates)): ?>
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-transparent border-bottom-0 pt-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-users me-2 text-primary"></i>My Team (<?= count($subordinates) ?> members)</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($subordinates as $sub):
                            $perf = (int)($sub['performance_score'] ?? 0);
                            $completed = (int)($sub['tasks_completed'] ?? 0);
                            $total = (int)($sub['total_tasks'] ?? 0);
                        ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="emp-rp-sub-card h-100">
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="emp-rp-avatar style-36664"><?= rpInitials($sub['name']) ?></div>
                                        <div class="min-width-0">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($sub['name'] ?? '') ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($sub['designation'] ?? '') ?></div>
                                            <span class="badge bg-<?= ($sub['status'] ?? '') === 'active' ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= ($sub['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>" class="style-56522"><?= htmlspecialchars(ucfirst($sub['status'] ?? 'Active')) ?></span>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-4 text-center">
                                            <div class="fw-bold text-dark fs-5"><?= $total ?></div>
                                            <div class="text-muted style-68658">Tasks</div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="fw-bold text-success fs-5"><?= $completed ?></div>
                                            <div class="text-muted style-68658">Done</div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="fw-bold text-<?= rpPerformanceColor($perf) ?> fs-5"><?= $perf ?>%</div>
                                            <div class="text-muted style-68658">Score</div>
                                        </div>
                                    </div>
                                    <div class="emp-rp-progress mb-2">
                                        <div class="emp-rp-progress-fill bg-<?= rpPerformanceColor($perf) ?>" class="style-89770"></div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <div class="text-muted small flex-grow-1"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($sub['email'] ?? '—') ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Department Members -->
        <?php if (!empty($department_members)): ?>
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-transparent border-bottom-0 pt-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-building me-2 text-primary"></i>Department Colleagues (<?= count($department_members) ?>)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($department_members as $m): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="emp-rp-avatar style-64432"><?= rpInitials($m['name']) ?></div>
                                                <span class="fw-semibold"><?= htmlspecialchars($m['name'] ?? '') ?></span>
                                            </div>
                                        </td>
                                        <td class="small"><?= htmlspecialchars($m['designation'] ?? '—') ?></td>
                                        <td><span class="badge bg-<?= rpLevelBadge($m['role'] ?? '') ?> bg-opacity-10 text-<?= rpLevelBadge($m['role'] ?? '') ?>"><?= htmlspecialchars($m['role'] ?? '—') ?></span></td>
                                        <td class="small text-muted"><?= htmlspecialchars($m['email'] ?? '—') ?></td>
                                        <td><span class="badge bg-<?= ($m['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars(ucfirst($m['status'] ?? 'Active')) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
