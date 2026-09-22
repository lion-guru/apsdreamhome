<?php
$page_title = 'News Management';
$active_page = 'news';
$newsList   = $news ?? [];
$filters    = $filters ?? [];
$total      = count($newsList);
$published  = 0; $draft = 0; $totalViews = 0;
foreach ($newsList as $a) {
    $totalViews += (int)($a['views'] ?? 0);
    if (($a['status'] ?? 'draft') === 'published') { $published++; } else { $draft++; }
}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-newspaper text-primary me-2"></i>News & Articles</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item active">News</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/blogs"       class="btn btn-outline-secondary btn-sm"><i class="fas fa-blog me-1"></i>Blog</a>
        <a href="<?= BASE_URL ?>/admin/gallery"      class="btn btn-outline-info btn-sm"><i class="fas fa-images me-1"></i>Gallery</a>
        <a href="<?= BASE_URL ?>/admin/news/create"  class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Article</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0d6efd;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-newspaper text-primary"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $total ?></div>
                <div class="text-muted small">Total Articles</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #198754;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $published ?></div>
                <div class="text-muted small">Published</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #ffc107;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-pencil-alt text-warning"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $draft ?></div>
                <div class="text-muted small">Drafts</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0dcaf0;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-eye text-info"></i>
                </div>
                <div class="fw-bold fs-4 text-dark"><?= number_format($totalViews) ?></div>
                <div class="text-muted small">Total Views</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1" for="newsSearch">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="newsSearch" name="search" class="form-control" placeholder="Search articles..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1" for="newsStatus">Status</label>
                <select id="newsStatus" name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft"     <?= ($filters['status'] ?? '') === 'draft'     ? 'selected' : '' ?>>Draft</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Search</button>
                <a href="<?= BASE_URL ?>/admin/news" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- News Table -->
<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h6 class="mb-0 fw-bold">All Articles</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="px-4" style="width:45%;">Article</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Views</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($newsList)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-newspaper fa-3x text-muted mb-3 d-block opacity-50"></i>
                                <h5 class="text-muted">No articles found</h5>
                                <p class="text-muted small mb-3">Create news articles to keep your customers informed about projects and offers.</p>
                                <a href="<?= BASE_URL ?>/admin/news/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Create Article</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($newsList as $article): ?>
                        <tr>
                            <td class="px-4">
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($article['title'] ?? '') ?></div>
                                <?php if (!empty($article['summary'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars(substr($article['summary'] ?? '', 0, 90)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (($article['status'] ?? 'draft') === 'published'): ?>
                                    <span class="badge bg-success">Published</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center fw-semibold"><?= number_format($article['views'] ?? 0) ?></td>
                            <td class="small text-muted">
                                <?php
                                $dateStr = $article['created_at'] ?? $article['date'] ?? '';
                                echo $dateStr ? date('d M Y', strtotime($dateStr)) : '—';
                                ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/news/<?= e($article['id']) ?>/edit" class="btn btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/news/<?= e($article['id']) ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>/news/<?= htmlspecialchars($article['slug'] ?? $article['id']) ?>" class="btn btn-outline-info" title="Preview" target="_blank"><i class="fas fa-external-link-alt"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
