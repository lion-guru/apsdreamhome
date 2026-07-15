<?php
/**
 * Create Page View - CMS Page Creation Form
 * Data: $page_title
 */
$page_title = $page_title ?? "Create Page";
?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5><i class="fas fa-plus-circle me-2"></i><?= htmlspecialchars($page_title) ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/pages/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Page Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" required placeholder="Enter page title">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="slug" required placeholder="page-slug" pattern="[a-z0-9-]+">
                                <div class="form-text">URL-friendly identifier (lowercase, numbers, hyphens)</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Meta Description</label>
                            <textarea class="form-control" name="meta_description" rows="2" placeholder="Brief description for SEO"></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Meta Keywords</label>
                            <input type="text" class="form-control" name="meta_keywords" placeholder="keyword1, keyword2, keyword3">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" rows="15" required placeholder="Enter page content (HTML allowed)"></textarea>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status</label>
                                <select class="form-select" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Page</button>
                            <a href="<?= BASE_URL ?>/admin/pages" class="btn btn-secondary"><i class="fas fa-times me-1"></i> Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>