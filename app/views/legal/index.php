<?php
// app/views/legal/index.php — Public legal documentation portal
?>
<section class="bg-dark text-white text-center py-5 position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, #0a192f 0%, #1e3a5f 50%, #0d9488 100%); opacity: 0.95;"></div>
    <div class="container position-relative" style="z-index: 1;">
        <div class="d-inline-block bg-gold text-dark rounded-pill px-4 py-2 mb-3 fw-bold small text-uppercase tracking-wider">Legal & Compliance</div>
        <h1 class="display-4 fw-bold mb-3"><?= __('legal_docs_heading', [], 'Legal Documentation') ?></h1>
        <p class="lead mb-0" style="color: #e2e8f0;"><?= __('legal_index_subtitle', [], 'Policies, terms and agreements that govern your journey with us. Built on transparency and trust.') ?></p>
    </div>
</section>

<div class="bg-light py-2 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= __('legal_documentation', [], 'Legal Documentation') ?></li>
            </ol>
        </nav>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <?php if (empty($documents)): ?>
            <div class="text-center py-5">
                <div class="bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 100px; height: 100px;">
                    <i class="fas fa-file-contract fa-3x text-primary"></i>
                </div>
                <h3 class="fw-bold mb-2"><?= __('legal_no_documents', [], 'No Published Documents Yet') ?></h3>
                <p class="text-muted mb-3"><?= __('legal_check_back', [], 'Our legal documentation is regularly updated. Check back soon for new policies and agreements.') ?></p>
                <a href="<?= BASE_URL ?>/" class="btn btn-primary btn-lg mt-2"><?= __('back_home', [], 'Back to Home') ?></a>
            </div>
        <?php else: ?>
            <?php
            $totalDocs = 0;
            foreach ($documents as $catDocs) { $totalDocs += count($catDocs); }
            ?>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white">
                        <div class="card-body text-center p-4">
                            <div class="display-4 fw-bold mb-1"><?= $totalDocs ?></div>
                            <div class="small text-uppercase opacity-75">Total Documents</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-success text-white">
                        <div class="card-body text-center p-4">
                            <div class="display-4 fw-bold mb-1"><?= count($documents) ?></div>
                            <div class="small text-uppercase opacity-75">Categories</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-gold text-dark">
                        <div class="card-body text-center p-4">
                            <div class="display-4 fw-bold mb-1">Always Current</div>
                            <div class="small text-uppercase opacity-75">Regulatory Compliant</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($documents as $cat => $catDocs): ?>
                    <?php
                        $catLabel = $categories[$cat] ?? ucwords(str_replace(['_', '-'], ' ', $cat));
                        $catColors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                    ?>
                    <div class="col-12" data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 45px; height: 45px;">
                                <i class="fas fa-folder-open text-primary"></i>
                            </div>
                            <h2 class="h4 fw-bold mb-0"><?= htmlspecialchars($catLabel) ?></h2>
                            <span class="badge bg-secondary ms-2"><?= count($catDocs) ?></span>
                        </div>
                    </div>
                    <?php foreach ($catDocs as $index => $doc): ?>
                        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= ($index * 100) ?>">
                            <div class="card border-0 shadow-sm rounded-4 h-100 property-card hover-lift transition-all">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">
                                            <?= htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $doc['document_type'] ?? ''))) ?>
                                        </span>
                                        <?php if (!empty($doc['is_mandatory'])): ?>
                                            <span class="badge bg-danger rounded-pill">Required</span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($doc['title']) ?></h5>
                                    <p class="card-text text-muted small flex-grow-1">
                                        <?= htmlspecialchars(mb_substr(strip_tags((string)($doc['summary'] ?? '')), 0, 140)) ?>
                                        <?= mb_strlen(strip_tags((string)($doc['summary'] ?? ''))) > 140 ? '…' : '' ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                        <small class="text-muted">
                                            <?php if (!empty($doc['version'])): ?>
                                                v<?= htmlspecialchars($doc['version']) ?> ·
                                            <?php endif; ?>
                                            <?= !empty($doc['published_at']) ? date('M j, Y', strtotime($doc['published_at'])) : '' ?>
                                        </small>
                                        <a href="<?= BASE_URL ?>/legal/<?= urlencode($doc['slug']) ?>" class="btn btn-sm btn-gold btn-rounded px-4">
                                            Read <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    .hover-lift:hover {
        transform: translateY(-8px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important;
    }
    .btn-gold {
        background: linear-gradient(135deg, #d4af37, #b5952f);
        color: #0a192f;
        border: none;
        font-weight: 600;
    }
    .btn-gold:hover {
        background: linear-gradient(135deg, #b5952f, #d4af37);
        color: #0a192f;
    }
    .rounded-pill {
        border-radius: 50px !important;
    }
</style>