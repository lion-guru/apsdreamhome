<?php
$employee = $employee ?? null;
$manager = $manager ?? null;
$subordinates = $subordinates ?? [];
$department_members = $department_members ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.org-card { border: 1px solid #e2e8f0; border-radius: 14px; transition: all 0.2s; }
.org-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
.org-avatar { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 700; color: #fff; }
.org-line { position: relative; }
.org-line::before { content: ''; position: absolute; left: 50%; top: 0; width: 2px; height: 30px; background: #cbd5e1; transform: translateX(-50%); }
.org-line::after { content: ''; position: absolute; left: 50%; top: 30px; width: 60%; height: 2px; background: #cbd5e1; transform: translateX(-50%); }
.member-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.member-card:hover { border-color: #3b82f6; box-shadow: 0 4px 12px rgba(59,130,246,0.08); }
.dept-badge { font-size: 0.75rem; padding: 4px 12px; border-radius: 20px; background: #eff6ff; color: #2563eb; font-weight: 600; }
.level-badge { font-size: 0.7rem; padding: 2px 8px; border-radius: 20px; }
</style>

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="mb-1 fw-bold"><i class="fas fa-sitemap me-2 text-primary"></i>Reporting Structure</h4>
        <p class="text-muted mb-0 small">Your team hierarchy and department overview</p>
    </div>

    <?php if (!$employee): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-sitemap fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted">No Employee Data</h5>
                <p class="text-muted small">Your employee profile is not set up yet.</p>
            </div>
        </div>
    <?php else: ?>
        <!-- Manager Section -->
        <div class="text-center mb-4">
            <?php if ($manager): ?>
                <p class="text-muted small fw-semibold text-uppercase mb-3"><i class="fas fa-arrow-up me-1"></i>Reports To</p>
                <div class="d-inline-block">
                    <div class="card org-card shadow-sm">
                        <div class="card-body px-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="org-avatar" class="style-69457">
                                    <?= strtoupper(substr($manager['name'] ?? 'M', 0, 1)) ?>
                                </div>
                                <div class="text-start">
                                    <h5 class="mb-0 fw-bold"><?= htmlspecialchars($manager['name'] ?? '') ?></h5>
                                    <div class="text-muted small"><?= htmlspecialchars($manager['designation'] ?? '') ?></div>
                                    <?php if (!empty($manager['email'])): ?>
                                        <div class="small"><i class="fas fa-envelope me-1 text-muted"></i><?= htmlspecialchars($manager['email']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="org-line my-3" class="style-20185"></div>
            <?php else: ?>
                <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i>No manager assigned</p>
            <?php endif; ?>
        </div>

        <!-- Self Card -->
        <div class="text-center mb-4">
            <div class="d-inline-block">
                <div class="card org-card shadow-sm border-primary border-2">
                    <div class="card-body px-4 py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="org-avatar" class="style-53966">
                                <?= strtoupper(substr($employee['name'] ?? 'E', 0, 1)) ?>
                            </div>
                            <div class="text-start">
                                <h5 class="mb-0 fw-bold"><?= htmlspecialchars($employee['name'] ?? '') ?></h5>
                                <div class="text-muted small"><?= htmlspecialchars($employee['designation'] ?? '') ?></div>
                                <?php if (!empty($employee['department'])): ?>
                                    <span class="dept-badge mt-1"><?= htmlspecialchars($employee['department']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subordinates -->
        <?php if (!empty($subordinates)): ?>
            <div class="org-line mb-4" class="style-20185"></div>
            <p class="text-muted small fw-semibold text-uppercase text-center mb-3"><i class="fas fa-arrow-down me-1"></i>Team Members (<?= count($subordinates) ?>)</p>
            <div class="row g-3 mb-4 justify-content-center">
                <?php foreach ($subordinates as $sub):
                    $score = (int)($sub['performance_score'] ?? 0);
                    $scoreColor = $score >= 75 ? '#10b981' : ($score >= 50 ? '#f59e0b' : '#ef4444');
                    $levelColor = match(true) { ($sub['designation'] ?? '') === 'Director' => 'danger', ($sub['designation'] ?? '') === 'Manager' => 'primary', ($sub['designation'] ?? '') === 'Senior' => 'info', default => 'secondary' };
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card member-card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="org-avatar" class="style-24231">
                                        <?= strtoupper(substr($sub['name'] ?? 'T', 0, 1)) ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($sub['name'] ?? '') ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($sub['designation'] ?? '') ?></small>
                                            </div>
                                            <span class="level-badge bg-<?= $levelColor ?> bg-opacity-10 text-<?= $levelColor ?>"><?= ucfirst(htmlspecialchars($sub['designation'] ?? '')) ?></span>
                                        </div>
                                        <div class="d-flex gap-3 mt-2">
                                            <div class="small">
                                                <span class="text-muted">Tasks:</span>
                                                <strong><?= (int)($sub['tasks_completed'] ?? 0) ?>/<?= (int)($sub['total_tasks'] ?? 0) ?></strong>
                                            </div>
                                            <div class="small">
                                                <span class="text-muted">Score:</span>
                                                <strong class="style-90257"><?= $score ?>%</strong>
                                            </div>
                                        </div>
                                        <div class="progress mt-1" class="style-70208">
                                            <div class="progress-bar" class="style-99082"></div>
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <?php if (!empty($sub['email'])): ?>
                                                <a href="mailto:<?= htmlspecialchars($sub['email']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2" class="style-20558"><i class="fas fa-envelope"></i></a>
                                            <?php endif; ?>
                                            <?php if (!empty($sub['phone'])): ?>
                                                <a href="tel:<?= htmlspecialchars($sub['phone']) ?>" class="btn btn-sm btn-outline-success py-0 px-2" class="style-20558"><i class="fas fa-phone"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Department Members -->
        <?php if (!empty($department_members)): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-building me-2 text-primary"></i>Department Members (<?= count($department_members) ?>)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($department_members as $m): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="org-avatar" class="style-48194">
                                                    <?= strtoupper(substr($m['name'] ?? 'M', 0, 1)) ?>
                                                </div>
                                                <strong class="small"><?= htmlspecialchars($m['name'] ?? '') ?></strong>
                                            </div>
                                        </td>
                                        <td><small><?= htmlspecialchars($m['designation'] ?? '') ?></small></td>
                                        <td>
                                            <?php if (!empty($m['email'])): ?>
                                                <a href="mailto:<?= htmlspecialchars($m['email']) ?>" class="small text-decoration-none"><i class="fas fa-envelope me-1 text-muted"></i><?= htmlspecialchars($m['email']) ?></a>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $st = strtolower($m['status'] ?? 'active');
                                            $stColor = $st === 'active' ? 'success' : ($st === 'inactive' ? 'secondary' : 'warning');
                                            ?>
                                            <span class="badge bg-<?= $stColor ?> bg-opacity-10 text-<?= $stColor ?>"><?= ucfirst(htmlspecialchars($st)) ?></span>
                                        </td>
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
