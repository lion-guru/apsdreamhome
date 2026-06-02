<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">My Saved Searches</h4>
            <p class="text-muted mb-0">Stay updated with properties that match your criteria.</p>
        </div>
        <a href="<?= BASE_URL ?>/properties" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-search me-2"></i>New Search
        </a>
    </div>

    <?php if (!empty($searches)): ?>
        <div class="row g-3">
            <?php foreach ($searches as $search): ?>
                <div class="col-md-6">
                    <div class="card border rounded-4 p-3 transition-hover shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($search['name'] ?? 'Untitled Search') ?></h6>
                                <?php
                                $params = $search['search_params'] ?? '{}';
                                $decoded = json_decode($params, true);
                                if (is_array($decoded) && count($decoded) > 0):
                                ?>
                                    <p class="small text-muted mb-2">
                                        <?= implode(' • ', array_map(function($k, $v) {
                                            return ucfirst(str_replace('_', ' ', $k)) . ': ' . htmlspecialchars(is_array($v) ? implode(', ', $v) : $v);
                                        }, array_keys($decoded), $decoded)) ?>
                                    </p>
                                <?php endif; ?>
                                <small class="text-muted">Saved <?= date('d M Y', strtotime($search['created_at'])) ?></small>
                            </div>
                            <div class="ms-3 d-flex gap-2">
                                <a href="<?= BASE_URL ?>/properties" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fas fa-play me-1"></i>Run
                                </a>
                                <a href="<?= BASE_URL ?>/user/saved-searches/delete/<?= $search['id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete" onclick="return confirm('Delete this saved search?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px;">
                <i class="fas fa-search-location fa-3x text-muted opacity-25"></i>
            </div>
            <h5 class="fw-bold">No saved searches yet</h5>
            <p class="text-muted mx-auto" style="max-width: 400px;">When you perform a property search, you can save it to quickly access it later and receive email updates.</p>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary rounded-pill px-4 mt-2">Start Searching</a>
        </div>
    <?php endif; ?>
</div>

<style>
.transition-hover { transition: all 0.3s ease; }
.transition-hover:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important; }
</style>
