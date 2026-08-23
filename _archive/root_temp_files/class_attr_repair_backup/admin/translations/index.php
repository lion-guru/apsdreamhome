<?php
/**
 * Translation Management View (Admin)
 * Data: $languages, $translation_stats, $supported_languages, $page_title
 */
$page_title = $page_title ?? 'Translation Management';
$languages = $languages ?? [];
$translation_stats = $translation_stats ?? [];
$supported_languages = $supported_languages ?? ['en' => ['name' => 'English', 'native_name' => 'English', 'flag' => 'ðŸ‡ºðŸ‡¸']];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-language me-2"></i><?= htmlspecialchars($page_title ?? '') ?></h2>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTranslationModal">
                <i class="fas fa-plus me-1"></i> Add Translation
            </button>
        </div>
    </div>

    <!-- Language Stats Cards -->
    <div class="row g-3 mb-4">
        <?php foreach ($supported_languages as $code => $info): 
            $stats = $translation_stats[$code] ?? ['total' => 0, 'completed' => 0, 'percentage' => 0];
        ?>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <span class="badge bg-primary rounded-pill p-2 fs-5"><?= $info['flag'] ?? 'ðŸŒ�' ?></span>
                        </div>
                        <div>
                            <div class="aps-cp-stat-label"><?= htmlspecialchars($info['name'] ?? '') ?> (<?= $code ?>)</div>
                            <div class="aps-cp-stat-value">
                                <?= $stats['completed'] ?? 0 ?> / <?= $stats['total'] ?? 0 ?>
                                <small class="text-muted ms-2">(<?= $stats['percentage'] ?? 0 ?>%)</small>
                            </div>
                        </div>
                    </div>
                    <div class="progress mt-2" class="style-29939">
                        <div class="progress-bar bg-primary" class="style-58879"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Translations Table -->
    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
            <i class="fas fa-list me-2"></i>Translations
            <div class="input-group" class="style-61646">
                <input type="text" class="form-control form-control-sm" id="translationSearch" placeholder="Search translations...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Key</th>
                            <?php foreach ($supported_languages as $code => $info): ?>
                                <th><?= htmlspecialchars($info['name'] ?? '') ?> (<?= $code ?>)</th>
                            <?php endforeach; ?>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="translationsTableBody">
                        <?php if (empty($languages)): ?>
                        <tr>
                            <td colspan="<?= count($supported_languages) + 2 ?>" class="text-center py-5">
                                <i class="fas fa-language fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No Translations Found</h4>
                                <p class="text-muted">Add your first translation using the button above.</p>
                            </td>
                        </tr>
                        <?php else: foreach ($languages as $key => $translations): ?>
                        <tr>
                            <td><code class="small"><?= htmlspecialchars($key ?? '') ?></code></td>
                            <?php foreach ($supported_languages as $code => $info): ?>
                                <td>
                                    <span class="text-<?= isset($translations[$code]) && !empty($translations[$code]) ? 'success' : 'muted' ?>">
                                        <?= htmlspecialchars($translations[$code] ?? '-') ?>
                                    </span>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary edit-translation" data-key="<?= htmlspecialchars($key ?? '') ?>" data-translations="<?= htmlspecialchars(json_encode($translations)) ?>"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Translation Modal -->
<div class="modal fade" id="addTranslationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/translations" id="translationForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Translation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Key <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="key" required placeholder="e.g., welcome_message">
                    </div>
                    
                    <hr>
                    <h6>Translations</h6>
                    <?php foreach ($supported_languages as $code => $info): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><?= htmlspecialchars($info['name'] ?? '') ?> (<?= $code ?>) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="translations[<?= $code ?>]" placeholder="Translation in <?= $info['name'] ?>" required>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Translation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Search filter
document.getElementById('translationSearch')?.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#translationsTableBody tr').forEach(row => {
        const key = row.querySelector('td:first-child')?.textContent?.toLowerCase() || '';
        row.style.display = key.includes(query) ? '' : 'none';
    });
});

// Edit translation
document.querySelectorAll('.edit-translation').forEach(btn => {
    btn.addEventListener('click', function() {
        const key = this.dataset.key;
        const translations = JSON.parse(this.dataset.translations);
        
        document.querySelector('#addTranslationModal input[name="key"]').value = key;
        document.querySelector('#addTranslationModal input[name="action"]').value = 'edit';
        
        Object.keys(translations).forEach(lang => {
            const input = document.querySelector(`#addTranslationModal input[name="translations[${lang}]"]`);
            if (input) input.value = translations[lang] || '';
        });
        
        document.querySelector('#addTranslationModal .modal-title').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Translation';
        new bootstrap.Modal(document.getElementById('addTranslationModal')).show();
    });
});

// Reset on close
document.getElementById('addTranslationModal')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('translationForm').reset();
    document.querySelector('#addTranslationModal input[name="action"]').value = 'add';
    document.querySelector('#addTranslationModal .modal-title').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Add Translation';
});
</script>