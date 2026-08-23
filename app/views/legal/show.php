<?php
// app/views/legal/show.php — Single legal document detail
$doc = $document ?? [];
?>
<section class="bg-dark text-white py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/" class="text-white-50"><?= __('breadcrumb_home') ?></a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/legal" class="text-white-50"><?= __('legal_documentation') ?: 'Legal Documentation' ?></a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?= htmlspecialchars(mb_substr($doc['title'] ?? '', 0, 40)) ?></li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="badge bg-primary bg-opacity-25 text-white">
                <?= htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $doc['document_type'] ?? ''))) ?>
            </span>
            <?php if (!empty($doc['is_mandatory'])): ?>
                <span class="badge bg-danger">Required</span>
            <?php endif; ?>
            <?php if (!empty($doc['version'])): ?>
                <span class="badge bg-secondary">v<?= htmlspecialchars($doc['version']) ?></span>
            <?php endif; ?>
        </div>
        <h1 class="display-6 fw-bold mb-1"><?= htmlspecialchars($doc['title'] ?? '') ?></h1>
        <p class="text-white-50 mb-0">
            <?= !empty($doc['published_at']) ? __('published_on') ?: 'Published' . ': ' . date('F j, Y', strtotime($doc['published_at'])) : '' ?>
        </p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <?php if (!empty($doc['summary'])): ?>
                            <div class="alert alert-light border rounded-3">
                                <strong><?= __('summary') ?: 'Summary' ?>:</strong> <?= htmlspecialchars($doc['summary']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="legal-content">
                            <?= $doc['content'] ?? '' ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($doc['is_mandatory']) && isset($_SESSION['user_id'])): ?>
                    <div id="accept-box" class="card border-0 shadow-sm rounded-4 mt-4 <?= $accepted ? 'border-success' : '' ?>">
                        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <h6 class="fw-bold mb-1">
                                    <?= $accepted
                                        ? ('✅ ' . (('legal_already_accepted') ?: 'You have accepted this document'))
                                        : ((__('legal_accept_required') ?: 'Action required: please accept this document')) ?>
                                </h6>
                                <small class="text-muted">v<?= htmlspecialchars($doc['version'] ?? '1') ?></small>
                            </div>
                            <?php if (!$accepted): ?>
                                <button type="button" id="accept-btn" class="btn btn-success px-4"
                                        data-document-id="<?= (int)$doc['id'] ?>">
                                    <i class="fas fa-check me-1"></i><?= __('i_agree') ?: 'I Agree' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <?php if (!empty($related)): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h6 class="fw-bold mb-0"><i class="fas fa-file-alt text-warning me-2"></i><?= __('related_documents') ?: 'Related Documents' ?></h6>
                        </div>
                        <div class="list-group list-group-flush px-3 pb-3">
                            <?php foreach ($related as $rel): ?>
                                <a href="<?= BASE_URL ?>/legal/<?= urlencode($rel['slug']) ?>"
                                   class="list-group-item list-group-item-action border-0 rounded-3 mb-1 px-3 py-2">
                                    <div class="fw-semibold small"><?= htmlspecialchars($rel['title']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $rel['document_type'] ?? ''))) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-question-circle text-warning me-2"></i><?= __('need_help') ?: 'Need Help?' ?></h6>
                        <p class="text-muted small mb-3"><?= __('legal_help_text') ?: 'Questions about this document? Our team is happy to clarify.' ?></p>
                        <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-primary btn-sm w-100"><?= __('contact_us') ?: 'Contact Us' ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($doc['is_mandatory']) && isset($_SESSION['user_id']) && empty($accepted)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('accept-btn');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var body = new URLSearchParams();
        body.append('document_id', btn.getAttribute('data-document-id'));
        if (tokenMeta) body.append('csrf_token', tokenMeta.getAttribute('content'));
        fetch('<?= BASE_URL ?>/legal/accept', {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: body,
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d.success) { window.location.reload(); }
            else { alert(d.message || 'Failed'); }
        }).catch(function () { alert('Network error'); });
    });
});
</script>
<?php endif; ?>
