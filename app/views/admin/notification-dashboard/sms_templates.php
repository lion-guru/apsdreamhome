<?php
$templates = $templates ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'SMS Templates' ?> - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        .page-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 2rem; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .page-header h1 { font-size: 1.75rem; font-weight: 700; color: #f8fafc; }
        .page-header p { color: #94a3b8; font-size: 0.9rem; margin-top: 0.25rem; }
        .section-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .section-card h3 { font-size: 1.1rem; font-weight: 600; color: #f8fafc; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .table-dark { --bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.04); }
        .table-dark th { color: #94a3b8; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .table-dark td { color: #e2e8f0; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .badge-active { background: rgba(34,197,94,0.15); color: #4ade80; padding: 0.2rem 0.6rem; border-radius: 8px; font-size: 0.7rem; font-weight: 600; }
        .badge-inactive { background: rgba(239,68,68,0.15); color: #f87171; padding: 0.2rem 0.6rem; border-radius: 8px; font-size: 0.7rem; font-weight: 600; }
        .template-body { color: #94a3b8; font-size: 0.8rem; max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .btn-back { background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.85rem; text-decoration: none; transition: all 0.2s; }
        .btn-back:hover { background: rgba(59,130,246,0.25); color: #93bbfc; }
        .empty-state { text-align: center; padding: 3rem; color: #64748b; }
        .empty-state i { font-size: 2.5rem; margin-bottom: 1rem; display: block; }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-sms me-2"></i>SMS Templates</h1>
                <p>Manage MSG91 SMS templates for notifications</p>
            </div>
            <a href="<?= $base ?>/admin/notification-dashboard" class="btn-back"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <div class="section-card">
            <?php if (!empty($templates)): ?>
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Body</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $t): ?>
                            <tr>
                                <td><code style="color: #60a5fa;"><?= htmlspecialchars($t['template_code']) ?></code></td>
                                <td><?= htmlspecialchars($t['template_name']) ?></td>
                                <td class="template-body"><?= htmlspecialchars($t['body']) ?></td>
                                <td><span class="badge-<?= ($t['is_active'] ?? 0) ? 'active' : 'inactive' ?>"><?= ($t['is_active'] ?? 0) ? 'Active' : 'Inactive' ?></span></td>
                                <td style="white-space: nowrap;"><?= date('d M Y', strtotime($t['created_at'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state"><i class="fas fa-sms"></i><p>No SMS templates found. Run the seed script to populate templates.</p></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
