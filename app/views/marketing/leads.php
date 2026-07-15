<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-bullhorn me-2 text-primary"></i>Marketing Leads</h4>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Name</th><th>Phone</th><th>Source</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if (!empty($leads)): ?>
                            <?php foreach ($leads as $l): ?>
                                <tr>
                                    <td><?= htmlspecialchars($l['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($l['source'] ?? '') ?></td>
                                    <td><span class="badge bg-<?= ($l['status'] ?? '') === 'new' ? 'info' : 'warning' ?>"><?= htmlspecialchars($l['status'] ?? '') ?></span></td>
                                    <td><a href="<?= BASE_URL ?>/admin/marketing-automation/leads/<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-muted text-center">No leads found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>