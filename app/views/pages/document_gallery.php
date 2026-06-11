<?php
$selectedCategory = $selected_category ?? '';
$searchQuery = $search_query ?? '';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
            <li class="breadcrumb-item active">Document Gallery</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 fw-bold text-primary">
                <i class="fas fa-folder-open me-2"></i>Document Gallery
            </h1>
            <p class="text-muted">Download brochures, legal documents, guides, and project layouts</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?= BASE_URL ?>/documents" class="row g-3">
                <div class="col-md-6">
                    <label for="q" class="form-label">Search</label>
                    <input type="text" class="form-control" id="q" name="q" placeholder="Search documents..." value="<?= htmlspecialchars($searchQuery) ?>">
                </div>
                <div class="col-md-4">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $selectedCategory === $cat['category'] ? 'selected' : '' ?>>
                                <?= ucfirst(htmlspecialchars($cat['category'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Grid -->
    <div class="row">
        <?php if (!empty($documents)): ?>
            <?php foreach ($documents as $doc): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 document-card">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="document-icon me-3">
                                    <?php
                                    $icon = match ($doc['file_type'] ?? 'pdf') {
                                        'pdf' => 'fa-file-pdf text-danger',
                                        'doc', 'docx' => 'fa-file-word text-primary',
                                        'xls', 'xlsx' => 'fa-file-excel text-success',
                                        'jpg', 'jpeg', 'png' => 'fa-file-image text-info',
                                        default => 'fa-file text-muted'
                                    };
                                    ?>
                                    <i class="fas <?= $icon ?> fa-3x"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1"><?= htmlspecialchars($doc['title']) ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-tag me-1"></i><?= ucfirst(htmlspecialchars($doc['category'] ?? 'general')) ?>
                                        <?php if ($doc['file_size'] > 0): ?>
                                            <span class="ms-2"><i class="fas fa-database me-1"></i><?= round($doc['file_size'] / 1024, 1) ?> KB</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <?php if (!empty($doc['description'])): ?>
                                <p class="card-text small text-muted mb-3"><?= htmlspecialchars($doc['description']) ?></p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-download me-1"></i><?= (int)($doc['downloads_count'] ?? 0) ?> downloads
                                </small>
                                <a href="<?= BASE_URL ?>/documents/download/<?= $doc['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card aps-cp-card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No documents found</h5>
                        <p class="text-muted">Try adjusting your search or filter criteria.</p>
                        <a href="<?= BASE_URL ?>/documents" class="btn btn-primary">View All Documents</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.document-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}
.document-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.12);
}
.document-icon {
    width: 50px;
    text-align: center;
}
</style>
