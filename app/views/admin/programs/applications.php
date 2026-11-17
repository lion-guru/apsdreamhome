<?php
/**
 * Admin — Program Applications (Student Internship + Nari Shakti)
 * Session: 681
 */
$applications = $applications ?? [];
$counts = $counts ?? [];
$program = $program ?? null;
$status = $status ?? null;
$base = BASE_URL ?? '';
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
$progLabels = ['student' => 'Student Internship', 'nari_shakti' => 'Nari Shakti'];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="mb-0 fs-4"><i class="fas fa-graduation-cap me-2 text-warning"></i>Program Applications</h2>
            <small class="text-muted">Student Internship Program + Nari Shakti registrations</small>
        </div>
        <a href="<?= $base ?>/admin/erp" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>ERP</a>
    </div>

    <?php if (!empty($_GET['saved'])): ?><div class="alert alert-success py-2">Application updated.</div><?php endif; ?>
    <?php if (!empty($_GET['error'])): ?><div class="alert alert-danger py-2">Could not save. Please try again.</div><?php endif; ?>

    <div class="row g-2 mb-3">
        <?php foreach (['pending' => 'warning', 'reviewing' => 'info', 'approved' => 'success', 'rejected' => 'danger'] as $st => $tone): ?>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm"><div class="card-body py-2 text-center">
                    <div class="fs-4 fw-bold text-<?= $tone ?>"><?= (int)($counts[$st] ?? 0) ?></div>
                    <div class="small text-muted text-uppercase"><?= $st ?></div>
                </div></div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="get" action="<?= $base ?>/admin/programs/applications" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label small">Program</label>
            <select name="program" class="form-select form-select-sm">
                <option value="">All Programs</option>
                <option value="student" <?= $program === 'student' ? 'selected' : '' ?>>Student Internship</option>
                <option value="nari_shakti" <?= $program === 'nari_shakti' ? 'selected' : '' ?>>Nari Shakti</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach (['pending', 'reviewing', 'approved', 'rejected'] as $st): ?>
                <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button></div>
        <div class="col-md-3"><a href="<?= $base ?>/admin/programs/applications" class="btn btn-sm btn-outline-secondary w-100">Clear</a></div>
    </form>

    <div class="card shadow-sm"><div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light"><tr>
                <th>Program</th><th>Name / Contact</th><th>Details</th><th>Motivation / Suggestion</th><th>Status</th><th>Received</th><th>Action</th>
            </tr></thead>
            <tbody>
            <?php if (!$applications): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No applications yet.</td></tr>
            <?php else: foreach ($applications as $a): ?>
                <tr>
                    <td><span class="badge bg-<?= $a->program_type === 'nari_shakti' ? 'danger' : 'warning text-dark' ?>"><?= htmlspecialchars($progLabels[$a->program_type] ?? $a->program_type) ?></span></td>
                    <td>
                        <strong><?= htmlspecialchars($a->full_name) ?></strong><br>
                        <a href="tel:<?= htmlspecialchars($a->phone) ?>" class="small text-decoration-none"><?= htmlspecialchars($a->phone) ?></a><br>
                        <a href="mailto:<?= htmlspecialchars($a->email) ?>" class="small text-decoration-none"><?= htmlspecialchars($a->email) ?></a><br>
                        <?php if ($a->city): ?><small class="text-muted"><?= htmlspecialchars($a->city) ?></small><?php endif; ?>
                    </td>
                    <td class="small">
                        <?php if ($a->college): ?><div><?= htmlspecialchars($a->college) ?></div><?php endif; ?>
                        <?php if ($a->course): ?><div><?= htmlspecialchars($a->course) ?></div><?php endif; ?>
                        <?php if ($a->current_year): ?><div class="text-muted">Year: <?= htmlspecialchars($a->current_year) ?></div><?php endif; ?>
                    </td>
                    <td class="small" style="max-width:340px">
                        <?php if ($a->motivation): ?><div><strong>Motivation:</strong> <?= nl2br(htmlspecialchars(mb_substr($a->motivation, 0, 400))) ?></div><?php endif; ?>
                        <?php if ($a->suggestion): ?><div class="mt-1"><strong>Suggestion:</strong> <?= nl2br(htmlspecialchars(mb_substr($a->suggestion, 0, 400))) ?></div><?php endif; ?>
                        <?php if ($a->admin_notes): ?><div class="mt-1 text-primary"><strong>Note:</strong> <?= nl2br(htmlspecialchars($a->admin_notes)) ?></div><?php endif; ?>
                    </td>
                    <td><span class="badge bg-<?= $a->status === 'approved' ? 'success' : ($a->status === 'rejected' ? 'danger' : ($a->status === 'reviewing' ? 'info text-dark' : 'warning text-dark')) ?>"><?= ucfirst($a->status) ?></span></td>
                    <td class="small text-muted"><?= date('d M Y<br>h:i A', strtotime($a->created_at)) ?></td>
                    <td>
                        <form method="post" action="<?= $base ?>/admin/programs/applications/<?= (int)$a->id ?>/status" class="d-flex flex-column gap-1">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <select name="status" class="form-select form-select-sm">
                                <?php foreach (['pending', 'reviewing', 'approved', 'rejected'] as $st): ?>
                                <option value="<?= $st ?>" <?= $a->status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="admin_notes" class="form-control form-control-sm" placeholder="Note (optional)" value="<?= htmlspecialchars($a->admin_notes ?? '') ?>" maxlength="500">
                            <button type="submit" class="btn btn-xs btn-sm btn-outline-primary"><i class="fas fa-save me-1"></i>Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div></div>
</div>
