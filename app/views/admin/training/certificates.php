<?php
$page_title = 'Training Certificates';
$page_description = 'Manage issued certificates';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Training Certificates</h1>
            <p class="text-muted">View all issued training certificates</p>
        </div>
    </div>

    <?php if (empty($certificates)): ?>
    <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No certificates issued yet.</div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Associate</th>
                        <th>Certificate Type</th>
                        <th>Issued Date</th>
                        <th>Certificate URL</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificates as $c): ?>
                    <tr>
                        <td class="fw-semibold"><?php echo htmlspecialchars($c['associate_name'] ?? '-'); ?></td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($c['certificate_type'] ?? '-'); ?></span></td>
                        <td><?php echo $c['issued_date'] ?? '-'; ?></td>
                        <td>
                            <?php if (!empty($c['certificate_url'])): ?>
                            <a href="<?php echo htmlspecialchars($c['certificate_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-1"></i>View
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="#" class="btn btn-sm btn-outline-info" title="Download"><i class="fas fa-download"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
