<?php
$page_title = $page_title ?? 'Legal Pages';
$terms_content = $terms_content ?? ['content' => '', 'title' => 'Terms and Conditions', 'updated_at' => ''];
$privacy_content = $privacy_content ?? ['content' => '', 'title' => 'Privacy Policy', 'updated_at' => ''];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-gavel me-2"></i>Legal Pages</h1>
            <p class="text-muted mb-0">Manage Terms & Conditions and Privacy Policy</p>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Terms & Conditions</h5>
                    <?php if (!empty($terms_content['updated_at'])): ?>
                        <small class="text-muted">Updated: <?= $terms_content['updated_at'] ?></small>
                    <?php endif; ?>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="bg-light rounded p-3 mb-3 style-3144">
                        <?= strip_tags($terms_content['content'] ?? '<em>No content yet</em>') ?>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#termsModal"><i class="fas fa-edit me-1"></i>Edit Terms & Conditions</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Privacy Policy</h5>
                    <?php if (!empty($privacy_content['updated_at'])): ?>
                        <small class="text-muted">Updated: <?= $privacy_content['updated_at'] ?></small>
                    <?php endif; ?>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="bg-light rounded p-3 mb-3 style-3144">
                        <?= strip_tags($privacy_content['content'] ?? '<em>No content yet</em>') ?>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#privacyModal"><i class="fas fa-edit me-1"></i>Edit Privacy Policy</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-file-contract me-2"></i>Edit Terms & Conditions</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="<?= BASE_URL ?>/admin/legal-pages/update-terms">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($terms_content['title'] ?? 'Terms and Conditions') ?>"></div>
            <div class="mb-3"><label class="form-label">Content (HTML supported)</label><textarea name="content" class="form-control" rows="15" class="style-65267"><?= htmlspecialchars($terms_content['content'] ?? '') ?></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Terms</button></div>
    </form>
</div></div></div>

<!-- Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-shield-alt me-2"></i>Edit Privacy Policy</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="<?= BASE_URL ?>/admin/legal-pages/update-privacy">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($privacy_content['title'] ?? 'Privacy Policy') ?>"></div>
            <div class="mb-3"><label class="form-label">Content (HTML supported)</label><textarea name="content" class="form-control" rows="15" class="style-65267"><?= htmlspecialchars($privacy_content['content'] ?? '') ?></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Privacy Policy</button></div>
    </form>
</div></div></div>
