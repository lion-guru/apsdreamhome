<?php
$agreements = $agreements ?? [];
$stats = $stats ?? ['total' => 0, 'draft' => 0, 'pending' => 0, 'signed' => 0];
$agents = $agents ?? [];
$properties = $properties ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<style>
.aag-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
.aag-card h5 { color: #f8fafc; margin-bottom: 16px; font-size: 15px; }
.aag-stat { background: linear-gradient(135deg, #1e3a5f, #0f172a); border-radius: 10px; padding: 18px 16px; color: white; text-align: center; }
.aag-stat .num { font-size: 28px; font-weight: 700; }
.aag-stat .lbl { font-size: 12px; opacity: 0.8; margin-top: 4px; }
.aag-table { width: 100%; border-collapse: collapse; color: #f8fafc; }
.aag-table th { background: #0f172a; padding: 10px 12px; text-align: left; font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
.aag-table td { padding: 10px 12px; border-bottom: 1px solid #334155; font-size: 13px; }
.aag-table tr:hover td { background: rgba(255,255,255,0.03); }
.aag-badge { padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; display: inline-block; }
.aag-badge-draft { background: #64748b20; color: #94a3b8; border: 1px solid #64748b40; }
.aag-badge-pending { background: #f59e0b20; color: #f59e0b; border: 1px solid #f59e0b40; }
.aag-badge-signed { background: #10b98120; color: #10b981; border: 1px solid #10b98140; }
.aag-badge-expired { background: #ef444420; color: #ef4444; border: 1px solid #ef444440; }
.aag-badge-cancelled { background: #ef444420; color: #ef4444; border: 1px solid #ef444440; }
.aag-empty { color: #64748b; text-align: center; padding: 30px; font-size: 13px; }
.aag-btn { padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; border: 1px solid transparent; cursor: pointer; text-decoration: none; display: inline-block; }
.aag-btn-view { background: #3b82f620; color: #3b82f6; border-color: #3b82f640; }
.aag-btn-send { background: #f59e0b20; color: #f59e0b; border-color: #f59e0b40; }
.aag-btn-sign { background: #10b98120; color: #10b981; border-color: #10b98140; }
.aag-btn-cancel { background: #ef444420; color: #ef4444; border-color: #ef444440; }
.aag-btn:hover { opacity: 0.8; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 style="color: #f8fafc; margin:0;"><i class="fas fa-file-signature me-2"></i>Agent Agreements</h4>
        <a href="<?= $base ?>/admin/agent-agreements/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Create Agreement
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" style="background:#10b98120;color:#10b981;border-color:#10b98140;">
            <?= $_SESSION['flash_success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter:invert(1);"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="aag-stat">
                <div class="num"><?= (int)$stats['total'] ?></div>
                <div class="lbl">Total Agreements</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="aag-stat" style="background: linear-gradient(135deg, #64748b, #475569);">
                <div class="num"><?= (int)$stats['draft'] ?></div>
                <div class="lbl">Draft</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="aag-stat" style="background: linear-gradient(135deg, #854d0e, #78350f);">
                <div class="num"><?= (int)$stats['pending'] ?></div>
                <div class="lbl">Pending Signature</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="aag-stat" style="background: linear-gradient(135deg, #065f46, #064e3b);">
                <div class="num"><?= (int)$stats['signed'] ?></div>
                <div class="lbl">Signed</div>
            </div>
        </div>
    </div>

    <!-- Agreements Table -->
    <div class="aag-card">
        <h5><i class="fas fa-list me-2" style="color:#3b82f6;"></i>All Agreements</h5>
        <?php if (!empty($agreements)): ?>
        <div style="overflow-x:auto;">
            <table class="aag-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Agent</th>
                        <th>Property</th>
                        <th style="text-align:center;">Commission</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agreements as $a): ?>
                    <tr>
                        <td style="font-weight:600;"><?= htmlspecialchars($a['title']) ?></td>
                        <td>
                            <div style="font-weight:500;"><?= htmlspecialchars($a['agent_name'] ?? 'N/A') ?></div>
                            <div style="color:#64748b;font-size:11px;"><?= htmlspecialchars($a['agent_email'] ?? '') ?></div>
                        </td>
                        <td>
                            <?php if ($a['property_name']): ?>
                                <div style="font-weight:500;"><?= htmlspecialchars($a['property_name']) ?></div>
                                <div style="color:#64748b;font-size:11px;"><?= htmlspecialchars($a['property_location'] ?? '') ?></div>
                            <?php else: ?>
                                <span style="color:#64748b;font-size:12px;">General</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;font-weight:600;color:#10b981;"><?= (float)$a['commission_pct'] ?>%</td>
                        <td><span class="aag-badge aag-badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                        <td style="color:#94a3b8;font-size:12px;"><?= $a['start_date'] ? date('d M Y', strtotime($a['start_date'])) : '—' ?></td>
                        <td style="color:#94a3b8;font-size:12px;"><?= $a['end_date'] ? date('d M Y', strtotime($a['end_date'])) : '—' ?></td>
                        <td style="text-align:right;white-space:nowrap;">
                            <a href="<?= $base ?>/admin/agent-agreements/detail/<?= (int)$a['id'] ?>" class="aag-btn aag-btn-view"><i class="fas fa-eye"></i></a>
                            <?php if ($a['status'] === 'draft'): ?>
                                <form method="POST" action="<?= $base ?>/admin/agent-agreements/send/<?= (int)$a['id'] ?>" style="display:inline;">
    <?php echo CSRFProtection::csrfField(); ?>
                                    <button type="submit" class="aag-btn aag-btn-send"><i class="fas fa-paper-plane"></i></button>
                                </form>
                            <?php endif; ?>
                            <?php if ($a['status'] === 'pending'): ?>
                                <form method="POST" action="<?= $base ?>/admin/agent-agreements/sign/<?= (int)$a['id'] ?>" style="display:inline;">
    <?php echo CSRFProtection::csrfField(); ?>
                                    <button type="submit" class="aag-btn aag-btn-sign"><i class="fas fa-check"></i></button>
                                </form>
                            <?php endif; ?>
                            <?php if (in_array($a['status'], ['draft', 'pending'])): ?>
                                <form method="POST" action="<?= $base ?>/admin/agent-agreements/cancel/<?= (int)$a['id'] ?>" style="display:inline;">
    <?php echo CSRFProtection::csrfField(); ?>
                                    <button type="submit" class="aag-btn aag-btn-cancel" onclick="return confirm('Cancel this agreement?')"><i class="fas fa-times"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="aag-empty">No agreements yet. Click "Create Agreement" to get started.</div>
        <?php endif; ?>
    </div>
</div>
