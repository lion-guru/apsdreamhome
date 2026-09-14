<?php
$templates = $templates ?? [];
$stats = $stats ?? ['total' => 0, 'email' => 0, 'sms' => 0, 'whatsapp' => 0, 'push' => 0];
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$page = $page ?? 1;
$search = $search ?? '';
$typeFilter = $typeFilter ?? '';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Templates - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }

        .page-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 2rem; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .page-header h1 { font-size: 1.75rem; font-weight: 700; color: #f8fafc; }
        .page-header p { color: #94a3b8; font-size: 0.9rem; margin-top: 0.25rem; }

        .stat-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; position: relative; overflow: hidden; }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
        .stat-card.blue::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        .stat-card.green::before { background: linear-gradient(90deg, #22c55e, #4ade80); }
        .stat-card.amber::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .stat-card.purple::before { background: linear-gradient(90deg, #a855f7, #c084fc); }
        .stat-card.teal::before { background: linear-gradient(90deg, #0d9488, #2dd4bf); }
        .stat-card .icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .stat-card .icon.blue { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .stat-card .icon.green { background: rgba(34,197,94,0.15); color: #4ade80; }
        .stat-card .icon.amber { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .stat-card .icon.purple { background: rgba(168,85,247,0.15); color: #c084fc; }
        .stat-card .icon.teal { background: rgba(13,148,136,0.15); color: #2dd4bf; }
        .stat-card .value { font-size: 2rem; font-weight: 700; color: #f8fafc; margin: 0.75rem 0 0.25rem; }
        .stat-card .label { color: #94a3b8; font-size: 0.85rem; }

        .section-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; }

        .table-dark { --bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.04); }
        .table-dark th { color: #94a3b8; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .table-dark td { color: #e2e8f0; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.04); }

        .badge-type { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-email { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .badge-sms { background: rgba(34,197,94,0.15); color: #4ade80; }
        .badge-whatsapp { background: rgba(37,211,102,0.15); color: #25d366; }
        .badge-push { background: rgba(168,85,247,0.15); color: #c084fc; }

        .badge-active { background: rgba(34,197,94,0.15); color: #4ade80; padding: 0.2rem 0.6rem; border-radius: 8px; font-size: 0.7rem; font-weight: 600; }
        .badge-inactive { background: rgba(239,68,68,0.15); color: #f87171; padding: 0.2rem 0.6rem; border-radius: 8px; font-size: 0.7rem; font-weight: 600; }

        .search-bar { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 1rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; }
        .search-bar input, .search-bar select { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); color: #e2e8f0; border-radius: 8px; padding: 0.5rem 1rem; font-size: 0.85rem; }
        .search-bar input::placeholder { color: #64748b; }
        .search-bar input:focus, .search-bar select:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59,130,246,0.15); }

        .btn-action { padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 500; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; }
        .btn-edit { background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); }
        .btn-edit:hover { background: rgba(59,130,246,0.25); color: #93bbfc; text-decoration: none; }
        .btn-delete { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
        .btn-delete:hover { background: rgba(239,68,68,0.25); color: #fca5a5; text-decoration: none; }

        .btn-create { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; padding: 0.6rem 1.25rem; border-radius: 10px; font-size: 0.9rem; font-weight: 600; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s; }
        .btn-create:hover { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; text-decoration: none; box-shadow: 0 4px 15px rgba(59,130,246,0.3); }

        .pagination .page-link { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); color: #94a3b8; font-size: 0.85rem; }
        .pagination .page-item.active .page-link { background: #3b82f6; border-color: #3b82f6; color: #fff; }
        .pagination .page-link:hover { background: rgba(255,255,255,0.08); color: #e2e8f0; }

        .flash-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #4ade80; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.9rem; }
        .flash-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #f87171; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.9rem; }

        .body-preview { color: #94a3b8; font-size: 0.8rem; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .empty-state { text-align: center; padding: 3rem; color: #64748b; }
        .empty-state i { font-size: 2.5rem; margin-bottom: 1rem; display: block; }

        @media (max-width: 768px) {
            .stat-card .value { font-size: 1.5rem; }
            .page-header { padding: 1.5rem; }
            .search-bar { flex-direction: column; }
            .search-bar input, .search-bar select { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-file-alt me-2"></i>Campaign Templates</h1>
                <p>Manage email, SMS, WhatsApp, and push notification templates</p>
            </div>
            <a href="<?= $base ?>/admin/campaign-templates/create" class="btn-create">
                <i class="fas fa-plus"></i> New Template
            </a>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="flash-success"><i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="flash-error"><i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card blue">
                    <div class="icon blue"><i class="fas fa-layer-group"></i></div>
                    <div class="value"><?= $stats['total'] ?></div>
                    <div class="label">Total Templates</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card green">
                    <div class="icon green"><i class="fas fa-envelope"></i></div>
                    <div class="value"><?= $stats['email'] ?></div>
                    <div class="label">Email</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card teal">
                    <div class="icon teal"><i class="fas fa-comment-sms"></i></div>
                    <div class="value"><?= $stats['sms'] ?></div>
                    <div class="label">SMS</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card" style="--c: #25d366;">
                    <div class="icon" style="background: rgba(37,211,102,0.15); color: #25d366;"><i class="fab fa-whatsapp"></i></div>
                    <div class="value"><?= $stats['whatsapp'] ?></div>
                    <div class="label">WhatsApp</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card purple">
                    <div class="icon purple"><i class="fas fa-bell"></i></div>
                    <div class="value"><?= $stats['push'] ?></div>
                    <div class="label">Push</div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="search-bar mb-4">
            <form method="GET" action="<?= $base ?>/admin/campaign-templates" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;width:100%;">
                <input type="text" name="search" placeholder="Search templates..." value="<?= htmlspecialchars($search) ?>" style="flex:1;min-width:200px;">
                <select name="type">
                    <option value="">All Types</option>
                    <option value="email" <?= $typeFilter === 'email' ? 'selected' : '' ?>>Email</option>
                    <option value="sms" <?= $typeFilter === 'sms' ? 'selected' : '' ?>>SMS</option>
                    <option value="whatsapp" <?= $typeFilter === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                    <option value="push" <?= $typeFilter === 'push' ? 'selected' : '' ?>>Push</option>
                </select>
                <button type="submit" class="btn-action btn-edit"><i class="fas fa-search"></i> Filter</button>
                <?php if ($search !== '' || $typeFilter !== ''): ?>
                    <a href="<?= $base ?>/admin/campaign-templates" class="btn-action btn-delete"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Templates Table -->
        <div class="section-card">
            <?php if (empty($templates)): ?>
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <p>No templates found.</p>
                    <a href="<?= $base ?>/admin/campaign-templates/create" class="btn-create mt-2"><i class="fas fa-plus"></i> Create First Template</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Body Preview</th>
                                <th>Status</th>
                                <th>Used</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($templates as $t): ?>
                                <tr>
                                    <td><?= (int)$t['id'] ?></td>
                                    <td class="fw-semibold" style="color:#f8fafc;"><?= htmlspecialchars($t['name']) ?></td>
                                    <td><span class="badge-type badge-<?= $t['type'] ?>"><?= ucfirst($t['type']) ?></span></td>
                                    <td><?= htmlspecialchars($t['subject'] ?? '—') ?></td>
                                    <td><span class="body-preview" title="<?= htmlspecialchars($t['body']) ?>"><?= htmlspecialchars(mb_strimwidth($t['body'], 0, 80, '...')) ?></span></td>
                                    <td>
                                        <?php if ($t['is_active']): ?>
                                            <span class="badge-active">Active</span>
                                        <?php else: ?>
                                            <span class="badge-inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int)$t['usage_count'] ?></td>
                                    <td style="color:#94a3b8;font-size:0.8rem;"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= $base ?>/admin/campaign-templates/edit/<?= (int)$t['id'] ?>" class="btn-action btn-edit"><i class="fas fa-pen"></i></a>
                                        <a href="<?= $base ?>/admin/campaign-templates/delete/<?= (int)$t['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Delete this template?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                        <span style="color:#64748b;font-size:0.85rem;">Showing <?= ($page - 1) * 20 + 1 ?>-<?= min($page * 20, $total) ?> of <?= $total ?></span>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($typeFilter) ?>"><i class="fas fa-chevron-left"></i></a></li>
                                <?php endif; ?>
                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($typeFilter) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($typeFilter) ?>"><i class="fas fa-chevron-right"></i></a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
