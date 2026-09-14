<?php
$template = $template ?? null;
$isEdit = !empty($template['id']);
$page_title = $isEdit ? 'Edit Template' : 'Create Template';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }

        .page-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 2rem; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .page-header h1 { font-size: 1.75rem; font-weight: 700; color: #f8fafc; }
        .page-header p { color: #94a3b8; font-size: 0.9rem; margin-top: 0.25rem; }

        .section-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; }

        .form-label { color: #94a3b8; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; }
        .form-control, .form-select {
            background: rgba(255,255,255,0.06) !important;
            border: 1px solid rgba(255,255,255,0.1);
            color: #e2e8f0 !important;
            border-radius: 10px;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59,130,246,0.15);
            outline: none;
        }
        .form-control::placeholder { color: #64748b; }
        .form-select option { background: #1e293b; color: #e2e8f0; }

        .form-check-input { background-color: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); }
        .form-check-input:checked { background-color: #3b82f6; border-color: #3b82f6; }
        .form-check-label { color: #94a3b8; font-size: 0.9rem; }

        .btn-submit { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; padding: 0.7rem 2rem; border-radius: 10px; font-size: 0.95rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; }
        .btn-submit:hover { background: linear-gradient(135deg, #2563eb, #1d4ed8); box-shadow: 0 4px 15px rgba(59,130,246,0.3); }

        .btn-back { color: #94a3b8; text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
        .btn-back:hover { color: #e2e8f0; }

        .variables-hint { background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); border-radius: 12px; padding: 1rem 1.25rem; margin-top: 0.5rem; }
        .variables-hint h6 { color: #60a5fa; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; }
        .variables-hint code { background: rgba(59,130,246,0.15); color: #93bbfc; padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.8rem; margin: 0.15rem 0.2rem; display: inline-block; }

        .flash-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #f87171; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.9rem; }

        textarea.form-control { min-height: 200px; font-family: 'Inter', monospace; line-height: 1.6; }

        @media (max-width: 768px) {
            .page-header { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-<?= $isEdit ? 'pen' : 'plus-circle' ?> me-2"></i><?= $page_title ?></h1>
                <p><?= $isEdit ? 'Update template details' : 'Create a new campaign template' ?></p>
            </div>
            <a href="<?= $base ?>/admin/campaign-templates" class="btn-back"><i class="fas fa-arrow-left me-1"></i> Back to Templates</a>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="flash-error"><i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <div class="section-card">
            <form method="POST" action="<?= $base ?>/admin/campaign-templates/<?= $isEdit ? 'update/' . (int)$template['id'] : 'store' ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="row g-4">
                    <!-- Name -->
                    <div class="col-md-6">
                        <label for="name" class="form-label"><i class="fas fa-tag me-1"></i> Template Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?= htmlspecialchars($template['name'] ?? $_POST['name'] ?? '') ?>"
                               placeholder="e.g. Welcome Email, Price Drop Alert">
                    </div>

                    <!-- Type -->
                    <div class="col-md-6">
                        <label for="type" class="form-label"><i class="fas fa-list me-1"></i> Template Type *</label>
                        <select class="form-select" id="type" name="type" required>
                            <?php
                            $types = ['email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'push' => 'Push Notification'];
                            $currentType = $template['type'] ?? $_POST['type'] ?? 'email';
                            foreach ($types as $val => $label):
                            ?>
                                <option value="<?= $val ?>" <?= $currentType === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Subject (email only) -->
                    <div class="col-md-12" id="subjectGroup">
                        <label for="subject" class="form-label"><i class="fas fa-heading me-1"></i> Subject Line</label>
                        <input type="text" class="form-control" id="subject" name="subject"
                               value="<?= htmlspecialchars($template['subject'] ?? $_POST['subject'] ?? '') ?>"
                               placeholder="Email subject line with merge fields like {{name}}">
                        <small style="color:#64748b;font-size:0.8rem;">Only used for email templates. SMS/WhatsApp/Push use the body directly.</small>
                    </div>

                    <!-- Body -->
                    <div class="col-md-12">
                        <label for="body" class="form-label"><i class="fas fa-align-left me-1"></i> Template Body *</label>
                        <textarea class="form-control" id="body" name="body" required
                                  placeholder="Write your template content here. Use {{name}}, {{phone}}, etc. for merge fields."><?= htmlspecialchars($template['body'] ?? $_POST['body'] ?? '') ?></textarea>

                        <!-- Variables Hint -->
                        <div class="variables-hint">
                            <h6><i class="fas fa-info-circle me-1"></i> Available Merge Fields</h6>
                            <div>
                                <code>{{name}}</code> <code>{{phone}}</code> <code>{{email}}</code> <code>{{city}}</code>
                                <code>{{budget}}</code> <code>{{property_type}}</code> <code>{{price}}</code> <code>{{location}}</code>
                                <code>{{area}}</code> <code>{{link}}</code> <code>{{otp}}</code>
                            </div>
                            <small style="color:#64748b;font-size:0.78rem;margin-top:0.5rem;display:block;">
                                These fields are automatically replaced with real values when the template is used in a campaign.
                            </small>
                        </div>
                    </div>

                    <!-- Is Active -->
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   <?= ($template['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Template is active and available for campaigns</label>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex justify-content-end gap-3 mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.06);">
                    <a href="<?= $base ?>/admin/campaign-templates" class="btn-back pt-2">Cancel</a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-<?= $isEdit ? 'save' : 'plus' ?> me-1"></i>
                        <?= $isEdit ? 'Update Template' : 'Create Template' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function() {
        const typeSelect = document.getElementById('type');
        const subjectGroup = document.getElementById('subjectGroup');
        function toggleSubject() {
            subjectGroup.style.display = typeSelect.value === 'email' ? 'block' : 'none';
        }
        typeSelect.addEventListener('change', toggleSubject);
        toggleSubject();
    })();
    </script>
</body>
</html>
