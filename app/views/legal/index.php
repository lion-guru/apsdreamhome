<?php
// app/views/legal/index.php — Public legal documentation portal
?>
<section class="bg-dark text-white text-center py-5">
    <div class="container">
        <h1 class="display-4 fw-bold"><?= __('legal_documentation') ?: 'Legal Documentation' ?></h1>
        <p class="lead mb-0"><?= __('legal_index_subtitle') ?: 'Policies, terms and agreements that govern your journey with us.' ?></p>
    </div>
</section>

<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/"><?= __('breadcrumb_home') ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= __('legal_documentation') ?: 'Legal Documentation' ?></li>
            </ol>
        </nav>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <?php if (empty($documents)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                <h4><?= __('legal_no_documents') ?: 'No published documents yet' ?></h4>
                <p class="text-muted"><?= __('legal_check_back') ?: 'Check back soon for updates.' ?></p>
                <a href="<?= BASE_URL ?>/" class="btn btn-primary mt-2"><?= __('back_home') ?: 'Back to Home' ?></a>
            </div>
        <?php else: ?>
            <?php foreach ($documents as $cat => $catDocs): ?>
                <?php
                    $catLabel = $categories[$cat] ?? ucwords(str_replace(['_', '-'], ' ', $cat));
                ?>
                <div class="mb-5">
                    <h2 class="h4 fw-bold border-bottom pb-2 mb-4">
                        <i class="fas fa-folder-open text-warning me-2"></i><?= htmlspecialchars($catLabel) ?>
                        <span class="badge bg-secondary ms-2"><?= count($catDocs) ?></span>
                    </h2>
                    <div class="row g-4">
                        <?php foreach ($catDocs as $doc): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card border-0 shadow-sm rounded-4 h-100 property-card">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                <?= htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $doc['document_type'] ?? ''))) ?>
                                            </span>
                                            <?php if (!empty($doc['is_mandatory'])): ?>
                                                <span class="badge bg-danger">Required</span>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="card-title fw-bold"><?= htmlspecialchars($doc['title']) ?></h5>
                                        <p class="card-text text-muted small flex-grow-1">
                                            <?= htmlspecialchars(mb_substr(strip_tags((string)($doc['summary'] ?? '')), 0, 140)) ?>
                                            <?= mb_strlen(strip_tags((string)($doc['summary'] ?? ''))) > 140 ? '…' : '' ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted">
                                                <?php if (!empty($doc['version'])): ?>
                                                    v<?= htmlspecialchars($doc['version']) ?> ·
                                                <?php endif; ?>
                                                <?= !empty($doc['published_at']) ? date('M j, Y', strtotime($doc['published_at'])) : '' ?>
                                            </small>
                                            <a href="<?= BASE_URL ?>/legal/<?= urlencode($doc['slug']) ?>" class="btn btn-sm btn-outline-primary stretched-link">
                                                Read <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
