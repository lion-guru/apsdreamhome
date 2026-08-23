ï»¿<?php
/** @var array $data ChatAnalytics dashboard data */
/** @var array $conversations Conversation stats */
/** @var array $actionLabels Action label map */
/** @var int $days Period */
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$totals = $data['totals'] ?? [];
$usage = $data['usage'] ?? [];
$trend = $data['trend'] ?? [];
$dropoffs = $data['dropoffs'] ?? [];

$totalStarts = $totals['total_starts'] ?? 0;
$totalCompleted = $totals['total_completions'] ?? 0;
$totalCancels = $totals['total_cancels'] ?? 0;
$totalDropoffs = $totals['total_dropoffs'] ?? 0;
$overallRate = $totalStarts > 0 ? round(($totalCompleted / $totalStarts) * 100, 1) : 0;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">ðŸ’¬ Chat Action Analytics</h2>
            <p class="text-muted mb-0">Track chatbot action usage, completion rates, and drop-offs</p>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm style-30246" id="periodSelect" onchange="window.location='<?= BASE_URL ?>/admin/chat-analytics?days='+this.value">
                <option value="7" <?=$days==7?'selected':''?>>Last 7 days</option>
                <option value="30" <?=$days==30?'selected':''?>>Last 30 days</option>
                <option value="90" <?=$days==90?'selected':''?>>Last 90 days</option>
            </select>
            <a href="<?=$base?>/admin/chat-history" class="btn btn-outline-secondary btn-sm">ðŸ“‹ History</a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-primary"><?=$totalStarts?></div>
                    <small class="text-muted">Total Actions Started</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-success"><?=$totalCompleted?></div>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-warning"><?=$totalCancels?></div>
                    <small class="text-muted">Cancelled</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-info"><?=$overallRate?>%</div>
                    <small class="text-muted">Completion Rate</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Conversation Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">💬 Conversation States</h5></div>
                <div class="card-body">
                    <div class="d-flex gap-4 flex-wrap">
                        <div class="text-center">
                            <div class="fs-4 fw-bold"><?=$conversations['total'] ?? 0?></div>
                            <small class="text-muted">Total Conversations</small>
                        </div>
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-success"><?=$conversations['completed'] ?? 0?></div>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-primary"><?=$conversations['active'] ?? 0?></div>
                            <small class="text-muted">Active</small>
                        </div>
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-warning"><?=$conversations['awaiting_confirm'] ?? 0?></div>
                            <small class="text-muted">Awaiting Confirm</small>
                        </div>
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-secondary"><?=$conversations['cancelled'] ?? 0?></div>
                            <small class="text-muted">Cancelled</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Per-Action Breakdown -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">⚙️ Action Breakdown</h5></div>
                <div class="card-body">
                    <?php if (empty($usage)): ?>
                    <p class="text-muted text-center py-4">No data yet. Start using the chatbot to see analytics.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th class="text-center">Started</th>
                                    <th class="text-center">Completed</th>
                                    <th class="text-center">Cancelled</th>
                                    <th class="text-center">Dropped</th>
                                    <th class="text-center">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usage as $row): ?>
                                <?php $rate = $row['starts'] > 0 ? round(($row['completions'] / $row['starts']) * 100, 1) : 0; ?>
                                <tr>
                                    <td><?=$actionLabels[$row['action']] ?? $row['action']?></td>
                                    <td class="text-center"><?=$row['starts']?></td>
                                    <td class="text-center"><span class="text-success"><?=$row['completions']?></span></td>
                                    <td class="text-center"><span class="text-warning"><?=$row['cancels']?></span></td>
                                    <td class="text-center"><span class="text-danger"><?=$row['dropoffs']?></span></td>
                                    <td class="text-center">
                                        <div class="progress style-64842">
                                            <div class="progress-bar bg-<?=$rate>=70?'success':($rate>=40?'warning':'danger')?> style-5847"<?=$rate?>%"><?=$rate?>%</div>
                                        </div>
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

        <!-- Drop-off Analysis -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">📉 Drop-off Points</h5></div>
                <div class="card-body">
                    <?php if (empty($dropoffs)): ?>
                    <p class="text-muted text-center py-4">No drop-offs recorded yet.</p>
                    <?php else: ?>
                    <?php foreach ($dropoffs as $d): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge bg-secondary">Step <?=intval($d['dropoff_step'])+1?></span>
                            <small class="ms-2"><?=$actionLabels[$d['action']] ?? $d['action']?></small>
                        </div>
                        <span class="badge bg-danger"><?=$d['count']?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Trend -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">📈 Daily Trend (Last <?=$days?> Days)</h5></div>
        <div class="card-body">
            <?php if (empty($trend)): ?>
            <p class="text-muted text-center py-4">No daily data yet.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-center">Started</th>
                            <th class="text-center">Completed</th>
                            <th class="text-center">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($trend, 0, 14) as $day): ?>
                        <?php $rate = $day['starts'] > 0 ? round(($day['completions'] / $day['starts']) * 100, 0) : 0; ?>
                        <tr>
                            <td><?=date('d M (D)', strtotime($day['day']))?></td>
                            <td class="text-center"><?=$day['starts']?></td>
                            <td class="text-center"><span class="text-success"><?=$day['completions']?></span></td>
                            <td class="text-center"><?=$rate?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

