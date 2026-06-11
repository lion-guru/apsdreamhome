<?php $page_title = 'Lead Groups'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-layer-group me-2"></i>Lead Groups</h2>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>By Status</h6></div>
                <div class="card-body p-0">
                    <?php if (empty($statuses)): ?>
                        <p class="text-muted text-center py-3">No data</p>
                    <?php else: ?>
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Status</th><th>Count</th></tr></thead>
                            <tbody>
                            <?php foreach ($statuses as $s): ?>
                                <tr>
                                    <td><span class="badge bg-<?= $s['status']==='new'?'primary':($s['status']==='converted'?'success':'secondary') ?>"><?= ucfirst($s['status']) ?></span></td>
                                    <td><strong><?= $s['cnt'] ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-filter me-2"></i>By Source</h6></div>
                <div class="card-body p-0">
                    <?php if (empty($sources)): ?>
                        <p class="text-muted text-center py-3">No data</p>
                    <?php else: ?>
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Source</th><th>Count</th></tr></thead>
                            <tbody>
                            <?php foreach ($sources as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['source'] ?? 'Unknown') ?></td>
                                    <td><strong><?= $s['cnt'] ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-list me-2"></i>All Leads</h6></div>
        <div class="card-body p-0">
            <?php if (empty($leads)): ?>
                <p class="text-muted text-center py-4">No leads</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Source</th><th>Status</th><th>Assigned</th><th>Created</th></tr></thead>
                        <tbody>
                        <?php foreach ($leads as $l): ?>
                            <tr>
                                <td><?= $l['id'] ?></td>
                                <td><a href="<?= BASE_URL ?>/admin/leads/show/<?= $l['id'] ?>"><?= htmlspecialchars($l['name']) ?></a></td>
                                <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($l['source'] ?? 'N/A') ?></span></td>
                                <td><span class="badge bg-<?= $l['status']==='new'?'primary':($l['status']==='converted'?'success':'warning') ?>"><?= ucfirst($l['status']) ?></span></td>
                                <td><?= htmlspecialchars($l['assignee_name'] ?? 'Unassigned') ?></td>
                                <td><?= date('d M Y', strtotime($l['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
