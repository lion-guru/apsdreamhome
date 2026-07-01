<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-search-plus text-primary me-2"></i>Saved Searches</h1>
                    <p class="text-muted small mb-0">Save, organize, and share filter combinations for any admin list view</p>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-outline-primary me-2">
                        <i class="fas fa-filter me-1"></i>Use on Leads
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/user-properties" class="btn btn-outline-info">
                        <i class="fas fa-filter me-1"></i>Use on Properties
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['flash_success'] ?? ''); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_SESSION['flash_error'] ?? ''); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-uppercase small opacity-75">My Searches</div>
                                    <div class="h2 mb-0 fw-bold"><?php echo number_format($stats['my_searches'] ?? 0); ?></div>
                                </div>
                                <i class="fas fa-user fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm bg-gradient-success text-white">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-uppercase small opacity-75">Public Shared</div>
                                    <div class="h2 mb-0 fw-bold"><?php echo number_format($stats['public_searches'] ?? 0); ?></div>
                                </div>
                                <i class="fas fa-share-alt fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm bg-gradient-warning text-white">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-uppercase small opacity-75">Favorites</div>
                                    <div class="h2 mb-0 fw-bold"><?php echo number_format($stats['favorites'] ?? 0); ?></div>
                                </div>
                                <i class="fas fa-star fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm bg-gradient-info text-white">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-uppercase small opacity-75">Entity Types</div>
                                    <div class="h2 mb-0 fw-bold"><?php echo count($stats['by_entity'] ?? []); ?></div>
                                </div>
                                <i class="fas fa-database fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-bookmark me-2 text-primary"></i>My Saved Searches</h5>
                                <div>
                                    <select class="form-select form-select-sm d-inline-block w-auto" onchange="window.location='<?php echo BASE_URL; ?>/admin/saved-searches?entity_type=' + this.value + '&favorites=<?php echo $favorites_only ? '1' : '0'; ?>'">
                                        <option value="">All Entity Types</option>
                                        <?php foreach ($entity_types as $et): ?>
                                            <option value="<?php echo $et; ?>" <?php echo $entity_type === $et ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $et)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <a href="<?php echo BASE_URL; ?>/admin/saved-searches?favorites=<?php echo $favorites_only ? '0' : '1'; ?>&entity_type=<?php echo htmlspecialchars($entity_type); ?>" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-star me-1"></i><?php echo $favorites_only ? 'Show All' : 'Favorites Only'; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($searches)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-1">No saved searches yet.</p>
                                    <p class="small">Use the form on the right to save your first filter combination.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th></th>
                                                <th>Name</th>
                                                <th>Entity</th>
                                                <th>Filters</th>
                                                <th>Uses</th>
                                                <th>Last Used</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($searches as $s): ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($s['is_favorite']): ?>
                                                            <i class="fas fa-star text-warning" title="Favorite"></i>
                                                        <?php endif; ?>
                                                        <?php if ($s['is_public']): ?>
                                                            <i class="fas fa-share-alt text-info" title="Public/Shared"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($s['name']); ?></strong>
                                                        <?php if (!empty($s['description'])): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($s['description']); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($s['entity_type']); ?></span></td>
                                                    <td>
                                                        <?php
                                                        $fkeys = array_keys($s['filters'] ?? []);
                                                        $shown = array_slice($fkeys, 0, 3);
                                                        foreach ($shown as $k): ?>
                                                            <span class="badge bg-light text-dark border me-1"><?php echo htmlspecialchars($k); ?></span>
                                                        <?php endforeach;
                                                        if (count($fkeys) > 3): ?>
                                                            <span class="badge bg-light text-muted">+<?php echo count($fkeys) - 3; ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><span class="badge bg-info"><?php echo (int)$s['use_count']; ?></span></td>
                                                    <td>
                                                        <?php if ($s['last_used_at']): ?>
                                                            <small><?php echo date('M j, g:i a', strtotime($s['last_used_at'])); ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">Never</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?php echo BASE_URL; ?>/admin/saved-searches/apply/<?php echo $s['id']; ?>" class="btn btn-outline-primary" title="Apply">
                                                                <i class="fas fa-play"></i>
                                                            </a>
                                                            <button class="btn btn-outline-warning favorite-btn" data-id="<?php echo $s['id']; ?>" title="Toggle favorite">
                                                                <i class="fas fa-star"></i>
                                                            </button>
                                                            <button class="btn btn-outline-danger delete-btn" data-id="<?php echo $s['id']; ?>" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Save New Search</h5>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <form method="POST" action="<?php echo BASE_URL; ?>/admin/saved-searches/store">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="mb-3">
                                    <label class="form-label small">Name *</label>
                                    <input type="text" name="name" class="form-control" required placeholder="e.g. Hot leads from Facebook">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Entity Type *</label>
                                    <select name="entity_type" class="form-select" required>
                                        <option value="">Select...</option>
                                        <?php foreach ($entity_types as $et): ?>
                                            <option value="<?php echo $et; ?>"><?php echo ucfirst(str_replace('_', ' ', $et)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Description</label>
                                    <textarea name="description" class="form-control" rows="2" placeholder="Optional notes about this search"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Filters (JSON)</label>
                                    <textarea name="filters" class="form-control font-monospace" rows="5" placeholder='{"status":"new","source":"facebook","date_from":"2026-01-01"}' required></textarea>
                                    <small class="text-muted">JSON object of filter key=value pairs</small>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_favorite" id="isFav">
                                    <label class="form-check-label" for="isFav">Mark as favorite</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_public" id="isPub">
                                    <label class="form-check-label" for="isPub">Share with team (public)</label>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i>Save Search
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-history me-2 text-info"></i>Recent Activity</h5>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <?php if (empty($history)): ?>
                                <p class="text-muted text-center small mb-0">No recent search activity.</p>
                            <?php else: ?>
                                <?php foreach ($history as $h): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($h['entity_type']); ?></span>
                                            <small class="text-muted"><?php echo date('M j, H:i', strtotime($h['created_at'])); ?></small>
                                        </div>
                                        <small class="text-muted">
                                            <?php
                                            $keys = array_keys($h['filters'] ?? []);
                                            echo count($keys) . ' filter' . (count($keys) !== 1 ? 's' : '') . ' applied';
                                            if (!is_null($h['results_count'])): ?>
                                                · <?php echo (int)$h['results_count']; ?> results
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.bg-gradient-primary { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); }
.bg-gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.bg-gradient-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.bg-gradient-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.favorite-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            fetch('<?php echo BASE_URL; ?>/admin/saved-searches/favorite/' + id, { method: 'POST' })
                .then(r => r.json())
                .then(d => { if (d.success) location.reload(); });
        });
    });
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Delete this saved search?')) return;
            const id = this.getAttribute('data-id');
            window.location = '<?php echo BASE_URL; ?>/admin/saved-searches/delete/' + id;
        });
    });
});
</script>
