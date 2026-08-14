<?php
$page_title = $page_title ?? 'Preview Import';
$base = defined('BASE_URL') ? BASE_URL : '';
$rows = $rows ?? [];
$total_rows = $total_rows ?? count($rows);
$error_rows = $error_rows ?? 0;
$success_rows = $total_rows - $error_rows;
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-eye me-2 text-primary"></i>Preview Import (<?= $total_rows ?> rows)</h4>
        <div>
            <a href="<?= $base ?>/admin/leads/import" class="btn btn-outline-secondary btn-sm me-2"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <?php if ($success_rows > 0): ?>
                <form action="<?= $base ?>/admin/leads/import/commit" method="POST" style="display:inline;">
    <?php echo CSRFProtection::csrfField(); ?>
                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Import <?= $success_rows ?> leads?')">
                        <i class="fas fa-check me-1"></i>Import <?= $success_rows ?> Leads
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($error_rows > 0): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong><?= $error_rows ?> rows</strong> have validation errors and will be skipped.
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Source</th>
                            <th>Budget</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Errors</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-file-import fa-3x text-muted mb-3" style="opacity:0.2"></i>
                                <h5 class="text-muted">No rows to preview</h5>
                                <p class="text-muted mb-3">The CSV file appears to be empty or has no valid data rows. Check your file format and try again.</p>
                                <a href="<?= BASE_URL ?>/admin/leads/import" class="btn btn-primary">
                                    <i class="fas fa-upload me-1"></i> Re-import
                                </a>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="<?= !empty($row['_errors']) ? 'table-danger' : '' ?>">
                                <td class="small"><?= (int)$row['_row'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['name'] ?? '') ?></td>
                                <td class="small"><?= htmlspecialchars($row['email'] ?? '') ?></td>
                                <td class="small"><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($row['source'] ?? 'csv_import') ?></span></td>
                                <td class="small">₹<?= number_format((float)($row['budget'] ?? 0)) ?></td>
                                <td>
                                    <?php
                                    $pri = $row['priority'] ?? 'medium';
                                    $priClass = $pri === 'high' ? 'danger' : ($pri === 'low' ? 'secondary' : 'warning');
                                    ?>
                                    <span class="badge bg-<?= $priClass ?>"><?= ucfirst($pri) ?></span>
                                </td>
                                <td><span class="badge bg-primary">New</span></td>
                                <td>
                                    <?php if (!empty($row['_errors'])): ?>
                                        <?php foreach ($row['_errors'] as $err): ?>
                                            <div class="text-danger small"><i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($err) ?></div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle text-success"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
