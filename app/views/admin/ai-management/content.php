<?php
$page_title = $page_title ?? 'AI Generated Content';
$contents = $contents ?? [];
$stats = $stats ?? ['total' => 0, 'published' => 0, 'draft' => 0];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">AI Generated Content</h1>
        <p class="text-muted mb-0">Content created by AI models</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-4 col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Content</h6>
                        <h3 class="mb-0"><?= $stats['total'] ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-success bg-opacity-10 text-success rounded p-3">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Published</h6>
                        <h3 class="mb-0"><?= $stats['published'] ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                            <i class="fas fa-pen fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Draft</h6>
                        <h3 class="mb-0"><?= $stats['draft'] ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Content List</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Model</th>
                        <th>Tokens</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contents)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No content generated yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($contents as $row): ?>
                            <tr>
                                <td><span class="badge bg-info"><?= htmlspecialchars($row['content_type'] ?? 'general') ?></span></td>
                                <td><strong><?= htmlspecialchars(mb_substr($row['title'] ?? 'Untitled', 0, 50)) ?></strong></td>
                                <td><small class="text-muted"><?= htmlspecialchars($row['model_used'] ?? 'N/A') ?></small></td>
                                <td><small><?= number_format($row['tokens_used'] ?? 0) ?></small></td>
                                <td>
                                    <span class="badge bg-<?= ($row['is_published'] ?? 0) ? 'success' : 'secondary' ?> content-status-<?= $row['id'] ?>">
                                        <?= ($row['is_published'] ?? 0) ? 'Published' : 'Draft' ?>
                                    </span>
                                </td>
                                <td><small><?= date('M j, Y', strtotime($row['created_at'] ?? 'now')) ?></small></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-<?= ($row['is_published'] ?? 0) ? 'warning' : 'success' ?>" onclick="toggleContent(<?= $row['id'] ?>)">
                                        <i class="fas fa-<?= ($row['is_published'] ?? 0) ? 'times' : 'check' ?>"></i>
                                        <?= ($row['is_published'] ?? 0) ? 'Unpublish' : 'Publish' ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleContent(id) {
    if (!confirm('Toggle publish status for this content?')) return;
    fetch('<?= BASE_URL ?>/admin/ai-management/content/toggle/' + id, { method: 'POST' })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                location.reload();
            } else {
                alert('Error: ' + (d.message || 'Unknown error'));
            }
        })
        .catch(() => alert('Request failed'));
}
</script>
