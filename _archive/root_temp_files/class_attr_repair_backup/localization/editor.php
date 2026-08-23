<?php
// app/views/localization/editor.php
/**
 * Localization Editor - Admin Translation Management
 * Data passed: $translations, $locale, $supported_locales, $page_title
 */
$locale = $locale ?? 'en';
$supported_locales = $supported_locales ?? ['en' => ['name' => 'English', 'native' => 'English']];
$translations = $translations ?? [];
$page_title = $page_title ?? 'Translation Editor - APS Dream Home';
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="page-title"><?= $page_title ?></h1>
            <p class="text-muted">Manage translations for locale: <strong><?= htmlspecialchars($locale ?? '') ?></strong></p>
        </div>
    </div>

    <!-- Locale Selector -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Switch Locale</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/localization/editor" class="d-flex gap-2 flex-wrap align-items-center">
                <select name="locale" id="locale-select" class="form-select form-select-sm" class="style-19078">
                    <?php foreach ($supported_locales as $code => $info): ?>
                        <option value="<?= htmlspecialchars($code ?? '') ?>" <?= $code === $locale ? 'selected' : '' ?>>
                            <?= htmlspecialchars($info['name'] ?? $code) ?> (<?= htmlspecialchars($code ?? '') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Load</button>
            </form>
        </div>
    </div>

    <!-- Translation Editor -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Translation Keys (<?= count($translations) ?> total)</h5>
            <div class="d-flex gap-2">
                <input type="text" id="search-translations" class="form-control form-control-sm" class="style-73161" placeholder="Search keys...">
                <button type="button" class="btn btn-success btn-sm" id="save-all-btn"><i class="fas fa-save me-1"></i> Save All Changes</button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($translations)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-language fa-3x mb-3"></i>
                    <p>No translations found for this locale.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="translations-table">
                        <thead class="table-light">
                            <tr>
                                <th class="style-14650">Key</th>
                                <th class="style-5994">English (Reference)</th>
                                <th class="style-5994">Translation (<?= htmlspecialchars($locale ?? '') ?>)</th>
                                <th class="style-16982"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($translations as $key => $value): ?>
                                <tr data-key="<?= htmlspecialchars($key ?? '') ?>">
                                    <td>
                                        <code class="small"><?= htmlspecialchars($key ?? '') ?></code>
                                    </td>
                                    <td>
                                        <span class="text-muted small" id="en-<?= md5($key) ?>">
                                            <?= htmlspecialchars($value['en'] ?? $value ?? '') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               class="form-control form-control-sm translation-input" 
                                               name="translations[<?= htmlspecialchars($key ?? '') ?>]" 
                                               value="<?= htmlspecialchars($value[$locale] ?? $value ?? '') ?>"
                                               data-key="<?= htmlspecialchars($key ?? '') ?>"
                                               data-locale="<?= htmlspecialchars($locale ?? '') ?>">
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary save-single-btn" 
                                                data-key="<?= htmlspecialchars($key ?? '') ?>">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Localization Editor JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-translations');
    const table = document.getElementById('translations-table');
    const saveAllBtn = document.getElementById('save-all-btn');
    const rows = table ? table.querySelectorAll('tbody tr') : [];

    // Search filter
    if (searchInput && rows.length) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            rows.forEach(row => {
                const key = row.dataset.key.toLowerCase();
                const enText = row.querySelector('[id^="en-"]').textContent.toLowerCase();
                row.style.display = key.includes(query) || enText.includes(query) ? '' : 'none';
            });
        });
    }

    // Save single translation
    document.querySelectorAll('.save-single-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const key = this.dataset.key;
            const input = document.querySelector(`input[data-key="${key}"]`);
            const locale = input.dataset.locale;
            const value = input.value;

            saveTranslation(key, value, locale, this);
        });
    });

    // Save all changes
    if (saveAllBtn) {
        saveAllBtn.addEventListener('click', function() {
            const changes = [];
            document.querySelectorAll('.translation-input').forEach(input => {
                const key = input.dataset.key;
                const locale = input.dataset.locale;
                const value = input.value;
                changes.push({ key, value, locale });
            });

            if (changes.length === 0) {
                alert('No changes to save.');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            fetch('<?= BASE_URL ?>/admin/localization/editor', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ translations: changes })
            })
            .then(response => response.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-save me-1"></i> Save All Changes';
                if (data.success) {
                    alert('All translations saved successfully!');
                } else {
                    alert('Error: ' + (data.message || 'Failed to save'));
                }
            })
            .catch(err => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-save me-1"></i> Save All Changes';
                console.error(err);
                alert('Error saving translations.');
            });
        });
    }

    function saveTranslation(key, value, locale, btn) {
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch('<?= BASE_URL ?>/admin/localization/editor', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ language: locale, key, translation: value })
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (!data.success) {
                alert('Error: ' + (data.message || 'Failed to save'));
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            console.error(err);
            alert('Error saving translation.');
        });
    }
});
</script>

<style>
.translation-input {
    font-family: monospace;
    font-size: 0.85rem;
}
#translations-table tbody tr:hover {
    background-color: #f8f9fa;
}
.translation-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}
</style>