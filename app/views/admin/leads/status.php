<?php $page_title = 'Lead Status'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-chart-pie me-2"></i><?= __('admin_lead_status_overview') ?></h2>
    <div class="row mb-4">
        <?php foreach ($statuses as $s): ?>
            <div class="col-md-2 col-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-<?= $s['status']==='new'?'primary':($s['status']==='converted'?'success':($s['status']==='closed'?'secondary':($s['status']==='dead'?'danger':'warning'))) ?>"><?= $s['cnt'] ?></h3>
                        <small class="text-muted"><?= ucfirst($s['status']) ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="col-md-2 col-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= number_format($total) ?></h3>
                    <small class="text-muted"><?= __('admin_total') ?></small>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($by_source)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-filter me-2"></i><?= __('admin_status_by_source') ?></h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th><?= __('admin_source') ?></th><th><?= __('admin_status') ?></th><th><?= __('admin_count') ?></th></tr></thead>
                        <tbody>
                        <?php foreach ($by_source as $bs): ?>
                            <tr>
                                <td><?= htmlspecialchars($bs['source'] ?? 'Unknown') ?></td>
                                <td><span class="badge bg-<?= $bs['status']==='new'?'primary':($bs['status']==='converted'?'success':'secondary') ?>"><?= ucfirst($bs['status']) ?></span></td>
                                <td><strong><?= $bs['cnt'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
