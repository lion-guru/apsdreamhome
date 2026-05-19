<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-ad me-2"></i><?= $ad ? 'Edit' : 'New' ?> Ad Slot</h1>
        <a href="<?= BASE_URL ?>/admin/ads" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Slot Key <span class="text-danger">*</span></label>
                    <input type="text" name="slot_key" class="form-control" value="<?= htmlspecialchars($ad['slot_key'] ?? '') ?>" required placeholder="e.g. header_banner, sidebar_ad">
                    <div class="form-text">Unique identifier used in code to render this ad</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($ad['title'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Slot Type</label>
                    <select name="slot_type" class="form-select">
                        <option value="banner" <?= ($ad['slot_type'] ?? '') === 'banner' ? 'selected' : '' ?>>Banner</option>
                        <option value="sidebar" <?= ($ad['slot_type'] ?? '') === 'sidebar' ? 'selected' : '' ?>>Sidebar</option>
                        <option value="inline" <?= ($ad['slot_type'] ?? '') === 'inline' ? 'selected' : '' ?>>Inline</option>
                        <option value="footer" <?= ($ad['slot_type'] ?? '') === 'footer' ? 'selected' : '' ?>>Footer</option>
                        <option value="popup" <?= ($ad['slot_type'] ?? '') === 'popup' ? 'selected' : '' ?>>Popup</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= ($ad['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($ad['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= (int)($ad['sort_order'] ?? 0) ?>" min="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image URL</label>
                    <input type="url" name="image_url" class="form-control" value="<?= htmlspecialchars($ad['image_url'] ?? '') ?>" placeholder="https://example.com/ad.jpg">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link URL</label>
                    <input type="url" name="link_url" class="form-control" value="<?= htmlspecialchars($ad['link_url'] ?? '') ?>" placeholder="https://example.com">
                </div>
                <div class="col-12">
                    <label class="form-label">Content / Text Ad</label>
                    <textarea name="content" rows="3" class="form-control" placeholder="Text content for non-image ads"><?= htmlspecialchars($ad['content'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">HTML Code (for Google AdSense or custom HTML)</label>
                    <textarea name="html_code" rows="5" class="form-control font-monospace" placeholder="&lt;script async src=...&gt;&lt;/script&gt;"><?= htmlspecialchars($ad['html_code'] ?? '') ?></textarea>
                    <div class="form-text">Paste Google AdSense code or custom HTML here. If set, this overrides image/content.</div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i>Save Ad Slot</button>
                </div>
            </form>
        </div>
    </div>
</div>
