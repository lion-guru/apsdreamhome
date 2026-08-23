<?php
$page_title = $page_title ?? 'Templates';
$page_heading = $page_heading ?? 'Campaign Templates';
$content = $content ?? '';
$templates = $templates ?? [];
ob_start();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Campaign Templates</h2>
            <p class="text-muted mb-0">Reusable message templates for email, SMS, and WhatsApp</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/marketing-campaigns" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-3">
        <?php if (empty($templates)): ?>
            <div class="col-12">
                <div class="alert alert-info">No templates yet</div>
            </div>
        <?php else: ?>
            <?php foreach ($templates as $t):
                $typeColors = ['email' => 'primary', 'sms' => 'info', 'whatsapp' => 'success', 'push' => 'warning'];
                $color = $typeColors[$t['type']] ?? 'secondary';
                $vars = json_decode($t['variables'] ?? '[]', true) ?: [];
            ?>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?= htmlspecialchars($t['name'] ?? '') ?></h5>
                                <span class="badge bg-<?= $color ?>"><?= ucfirst($t['type']) ?></span>
                            </div>
                            <span class="text-muted small">Used <?= $t['usage_count'] ?? 0 ?> times</span>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <?php if (!empty($t['subject'])): ?>
                                <p class="fw-bold mb-2"><?= htmlspecialchars($t['subject'] ?? '') ?></p>
                            <?php endif; ?>
                            <pre class="bg-light p-3 rounded small style-65441"><?= htmlspecialchars($t['body'] ?? '') ?></pre>
                            <?php if (!empty($vars)): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Variables:</small>
                                    <?php foreach ($vars as $v): ?>
                                        <code class="me-1">{{<?= $v ?>}}</code>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="<?= BASE_URL ?>/admin/marketing-campaigns/create?template=<?= $t['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> Use Template
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
