<?php $page_title = $page_title ?? 'Email & SMS Templates'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i>Email & SMS Templates</h2>
            <p class="text-muted mb-0">Create reusable templates for bulk outreach</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/crm/templates/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Template</a>
    </div>


    <ul class="nav nav-pills mb-4" id="templateTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#email-tab"><i class="fas fa-envelope me-1"></i> Email Templates (<?= count($templates) ?>)</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#sms-tab"><i class="fas fa-sms me-1"></i> SMS Templates (<?= count($sms_templates) ?>)</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="email-tab">
            <?php if (empty($templates)): ?>
                <div class="text-center py-5 bg-white rounded shadow-sm">
                    <i class="fas fa-envelope-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No email templates yet</h5>
                    <p class="text-muted">Create templates to speed up your outreach</p>
                    <a href="<?= BASE_URL ?>/admin/crm/templates/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create Template</a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($templates as $t): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($t['name'] ?? '') ?></h6>
                                        <span class="badge bg-primary">Email</span>
                                    </div>
                                    <?php if (!empty($t['subject'])): ?>
                                        <p class="text-muted mb-1 style-87981"><strong>Subject:</strong> <?= htmlspecialchars(mb_strimwidth($t['subject'], 0, 60, '...')) ?></p>
                                    <?php endif; ?>
                                    <p class="text-muted mb-2 style-86354"><?= htmlspecialchars(mb_strimwidth($t['body'] ?? '', 0, 100, '...')) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><i class="fas fa-folder me-1"></i><?= ucfirst($t['category'] ?? 'general') ?></small>
                                        <div class="d-flex gap-1">
                                            <a href="<?= BASE_URL ?>/admin/crm/templates/<?= $t['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/crm/templates/<?= $t['id'] ?>/delete" data-aps-confirm="Delete this template?">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <button class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="sms-tab">
            <?php if (empty($sms_templates)): ?>
                <div class="text-center py-5 bg-white rounded shadow-sm">
                    <i class="fas fa-sms fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No SMS templates yet</h5>
                    <a href="<?= BASE_URL ?>/admin/crm/templates/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create Template</a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($sms_templates as $t): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($t['name'] ?? '') ?></h6>
                                        <span class="badge bg-success">SMS</span>
                                    </div>
                                    <p class="text-muted mb-2 style-86354"><?= htmlspecialchars(mb_strimwidth($t['body'] ?? '', 0, 120, '...')) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><i class="fas fa-folder me-1"></i><?= ucfirst($t['category'] ?? 'general') ?></small>
                                        <div class="d-flex gap-1">
                                            <a href="<?= BASE_URL ?>/admin/crm/templates/<?= $t['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/crm/templates/<?= $t['id'] ?>/delete" data-aps-confirm="Delete this template?">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <button class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
