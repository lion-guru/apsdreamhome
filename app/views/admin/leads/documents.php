<?php $page_title = 'Lead Notes & Activities'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-sticky-note me-2"></i>Lead Notes & Activities</h2>
    <?php if (!$lead): ?>
        <div class="alert alert-danger">Lead not found.</div>
    <?php else: ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-user me-2"></i><?= htmlspecialchars($lead['name']) ?> (Lead #<?= $lead['id'] ?>)</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><small class="text-muted">Phone:</small><br><?= htmlspecialchars($lead['phone'] ?? 'N/A') ?></div>
                    <div class="col-md-3"><small class="text-muted">Email:</small><br><?= htmlspecialchars($lead['email'] ?? 'N/A') ?></div>
                    <div class="col-md-3"><small class="text-muted">Status:</small><br><span class="badge bg-primary"><?= ucfirst($lead['status']) ?></span></div>
                    <div class="col-md-3"><small class="text-muted">Source:</small><br><?= htmlspecialchars($lead['source'] ?? 'N/A') ?></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes (<?= count($notes) ?>)</h6></div>
                    <div class="card-body">
                        <?php if (empty($notes)): ?>
                            <p class="text-muted">No notes yet</p>
                        <?php else: ?>
                            <?php foreach ($notes as $n): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <small class="text-muted"><?= date('d M Y H:i', strtotime($n['created_at'])) ?> by <?= htmlspecialchars($n['author'] ?? 'System') ?></small>
                                    <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($n['content'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-history me-2"></i>Activities (<?= count($activities) ?>)</h6></div>
                    <div class="card-body">
                        <?php if (empty($activities)): ?>
                            <p class="text-muted">No activities yet</p>
                        <?php else: ?>
                            <?php foreach ($activities as $a): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <span class="badge bg-<?= ($a['activity_type'] ?? '')==='call'?'success':(($a['activity_type'] ?? '')==='email'?'info':'primary') ?>"><?= ucfirst($a['activity_type'] ?? '') ?></span>
                                    <small class="text-muted ms-2"><?= date('d M Y H:i', strtotime($a['activity_date'] ?? $a['created_at'])) ?></small>
                                    <p class="mb-0 mt-1"><?= htmlspecialchars($a['description'] ?? '') ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/admin/leads" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Leads</a>
</div>
