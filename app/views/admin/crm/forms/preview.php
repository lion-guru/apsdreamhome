<?php
$page_title = $page_title ?? 'Form Preview';
$form = $form ?? null;
$fields = json_decode($form['fields'] ?? '[]', true) ?? [];
$settings = json_decode($form['settings'] ?? '{}', true) ?? [];
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($form['name'] ?? 'Lead Form') ?> - Preview</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        body{background:#f5f7fa;font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif}
        .form-card{max-width:600px;margin:40px auto;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.1)}
        .form-header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:30px;text-align:center}
        .form-header h2{font-size:24px;font-weight:700;margin:0}
        .form-header p{opacity:.8;font-size:14px;margin:4px 0 0}
        .form-body{padding:30px}
        .form-control,.form-select{border-radius:10px;padding:12px 16px;border:1px solid #e0e0e0;transition:.2s}
        .form-control:focus,.form-select:focus{border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,.15)}
        .btn-submit{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;padding:14px 32px;border-radius:10px;font-weight:600;width:100%;font-size:16px;transition:.3s}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(102,126,234,.4)}
        .field-group{margin-bottom:20px}
        .required-star{color:#ef4444;margin-left:4px}
        .section-break{border-top:2px solid #e9ecef;margin:24px 0;padding-top:24px}
        .form-heading{font-size:18px;font-weight:700;color:#333;margin-bottom:16px}
    </style>
</head>
<body>
<div class="container">
    <div class="form-card card border-0">
        <div class="form-header">
            <h2><i class="fas fa-clipboard-list me-2"></i><?= htmlspecialchars($form['name'] ?? 'Lead Form') ?></h2>
            <?php if (!empty($form['description'])): ?><p><?= htmlspecialchars($form['description']) ?></p><?php endif; ?>
        </div>
        <div class="form-body">
            <form id="previewForm" method="POST" action="<?= $baseUrl ?>/api/leads">
                <input type="hidden" name="form_id" value="<?= $form['id'] ?? '' ?>">
                <?php foreach ($fields as $field): ?>
                    <?php if ($field['type'] === 'section'): ?>
                        <div class="section-break">
                            <h5 class="form-heading"><?= htmlspecialchars($field['label'] ?? '') ?></h5>
                        </div>
                    <?php elseif ($field['type'] === 'heading'): ?>
                        <h5 class="form-heading"><?= htmlspecialchars($field['label'] ?? '') ?></h5>
                    <?php elseif ($field['type'] === 'hidden'): ?>
                        <input type="hidden" name="<?= htmlspecialchars($field['name'] ?? $field['label']) ?>" value="<?= htmlspecialchars($field['default'] ?? '') ?>">
                    <?php else: ?>
                        <div class="field-group">
                            <label class="form-label fw-bold">
                                <?= htmlspecialchars($field['label'] ?? '') ?>
                                <?php if (!empty($field['required'])): ?><span class="required-star">*</span><?php endif; ?>
                            </label>
                            <?php if ($field['type'] === 'textarea'): ?>
                                <textarea class="form-control" name="<?= htmlspecialchars($field['name'] ?? $field['label']) ?>" 
                                    placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" 
                                    rows="4" <?= !empty($field['required']) ? 'required' : '' ?>></textarea>
                            <?php elseif ($field['type'] === 'select'): ?>
                                <select class="form-select" name="<?= htmlspecialchars($field['name'] ?? $field['label']) ?>" <?= !empty($field['required']) ? 'required' : '' ?>>
                                    <option value="">Select...</option>
                                    <?php foreach ($field['options'] ?? [] as $opt): ?>
                                        <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ($field['type'] === 'checkbox'): ?>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="<?= htmlspecialchars($field['name'] ?? $field['label']) ?>" id="field_<?= md5($field['label']) ?>">
                                    <label class="form-check-label" for="field_<?= md5($field['label']) ?>"><?= htmlspecialchars($field['label']) ?></label>
                                </div>
                            <?php elseif ($field['type'] === 'email'): ?>
                                <input type="email" class="form-control" name="<?= htmlspecialchars($field['name'] ?? $field['label']) ?>" 
                                    placeholder="<?= htmlspecialchars($field['placeholder'] ?? 'email@example.com') ?>" 
                                    <?= !empty($field['required']) ? 'required' : '' ?>>
                            <?php elseif ($field['type'] === 'phone'): ?>
                                <input type="tel" class="form-control" name="<?= htmlspecialchars($field['name'] ?? $field['label']) ?>" 
                                    placeholder="<?= htmlspecialchars($field['placeholder'] ?? '+91 9876543210') ?>" 
                                    pattern="[0-9+\s()-]{10,}" <?= !empty($field['required']) ? 'required' : '' ?>>
                            <?php else: ?>
                                <input type="text" class="form-control" name="<?= htmlspecialchars($field['name'] ?? $field['label']) ?>" 
                                    placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" 
                                    <?= !empty($field['required']) ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-submit mt-3">
                    <i class="fas fa-paper-plane me-1"></i> <?= htmlspecialchars($settings['submit_text'] ?? 'Submit') ?>
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>