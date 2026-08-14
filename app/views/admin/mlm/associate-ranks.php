<?php
/** @var array $associates */
/** @var array $rankBenefits */
$associates = $associates ?? [];
$rankBenefits = $rankBenefits ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-trophy me-2"></i>Associate Ranks</h5>
        <div>
            <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/mlm/associate-ranks/promote-all" class="d-inline" onsubmit="return confirm('Run automatic rank promotions for all associates?')">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <button type="submit" class="btn btn-sm btn-success me-2"><i class="fas fa-play me-1"></i>Run Promotions</button>
            </form>
            <a href="<?= htmlspecialchars($base) ?>/admin/mlm/rank-benefits" class="btn btn-link btn-sm">View Rank Benefits</a>
        </div>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive"><table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Current Rank</th>
                    <th>Next Rank</th>
                    <th>Legs</th>
                    <th>Lifetime Sales</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($associates)): ?>
                    <tr><td colspan="10" class="text-center py-3 text-muted">No associates yet</td></tr>
                <?php else: foreach ($associates as $a):
                    $curRank = (string)($a['level'] ?? 'associate');
                    $color = '#94a3b8';
                    $icon = 'fa-user';
                    foreach ($rankBenefits as $rb) {
                        if ($rb['rank_name'] === $curRank) {
                            $color = (string)($rb['color_code'] ?? '#94a3b8');
                            $icon = (string)($rb['badge_icon'] ?? 'fa-user');
                            break;
                        }
                    }
                ?>
                    <tr>
                        <td><?= (int)($a['id'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string)($a['name'] ?? '#'.($a['user_id'] ?? ''))) ?></td>
                        <td><?= htmlspecialchars((string)($a['email'] ?? 'â€”')) ?></td>
                        <td>
                            <span class="badge" class="style-1810">
                                <i class="fas <?= htmlspecialchars($icon) ?> me-1"></i>
                                <?= htmlspecialchars(ucfirst($curRank)) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(ucfirst((string)($a['next_rank'] ?? 'â€”'))) ?></td>
                        <td><?= (int)($a['leg_count'] ?? 0) ?></td>
                        <td>&#8377;<?= number_format((float)($a['lifetime_sales'] ?? 0)) ?></td>
                        <td class="style-286">
                            <?php $pct = (float)($a['progress_pct'] ?? 0); ?>
                            <div class="progress" class="style-87912">
                                <div class="progress-bar bg-success" role="progressbar" class="style-21859"></div>
                            </div>
                            <small class="text-muted"><?= number_format($pct, 1) ?>%</small>
                        </td>
                        <td>
                            <span class="badge bg-<?= (string)($a['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars((string)($a['status'] ?? '')) ?>
                            </span>
                        </td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($base) ?>/admin/mlm/associate-ranks/<?= (int)($a['id'] ?? 0) ?>">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table></div>
    </div>
</div>
