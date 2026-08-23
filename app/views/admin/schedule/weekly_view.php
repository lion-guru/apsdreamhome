<?php
$page_title = $page_title ?? 'Weekly Schedule - APS Dream Home';
$page_heading = $page_heading ?? 'Weekly Schedule View';
$week_start = $week_start ?? '';
$week_end = $week_end ?? '';
$week_offset = $week_offset ?? 0;
$week_dates = $week_dates ?? [];
$users = $users ?? [];
$schedule_grid = $schedule_grid ?? [];
$work_schedules = $work_schedules ?? [];
$departments = $departments ?? [];
$department = $department ?? '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-calendar-week me-2"></i><?= htmlspecialchars($page_heading ?? '') ?></h4>
        <div class="btn-group">
            <a href="?week=<?= $week_offset - 1 ?>&department=<?= urlencode($department) ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-chevron-left"></i></a>
            <span class="btn btn-outline-secondary btn-sm disabled"><?= htmlspecialchars($week_start ?? '') ?> - <?= htmlspecialchars($week_end ?? '') ?></span>
            <a href="?week=<?= $week_offset + 1 ?>&department=<?= urlencode($department) ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-chevron-right"></i></a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th class="style-730">Employee</th>
                    <?php foreach ($week_dates as $wd): ?>
                        <th class="text-center <?= !empty($wd['is_today']) ? 'table-primary' : '' ?>">
                            <?= htmlspecialchars($wd['day'] ?? '') ?><br><small><?= htmlspecialchars($wd['date'] ?? '') ?></small>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-users fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $emp): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($emp['name'] ?? '') ?></strong><br><small class="text-muted"><?= htmlspecialchars($emp['department'] ?? '') ?></small></td>
                            <?php foreach ($week_dates as $wd): ?>
                                <?php
                                $shift = $schedule_grid[$emp['id']][$wd['date']] ?? null;
                                $ws = $work_schedules[$emp['id']] ?? null;
                                $dayNum = date('w', strtotime($wd['date']));
                                $isWorkDay = $ws && in_array($dayNum, explode(',', $ws['work_days'] ?? ''));
                                ?>
                                <td class="text-center align-middle <?= !empty($wd['is_today']) ? 'table-primary' : '' ?>">
                                    <?php if ($shift): ?>
                                        <span class="badge style-22927"><?= htmlspecialchars($shift['shift_type_name'] ?? '') ?></span>
                                    <?php elseif ($isWorkDay): ?>
                                        <span class="text-info"><small>Scheduled</small></span>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
