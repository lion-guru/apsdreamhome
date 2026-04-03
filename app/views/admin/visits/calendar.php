<?php
/**
 * Site Visit Calendar View
 */

$page_title = 'Visit Calendar - APS Dream Home';
include __DIR__ . '/../../layouts/admin_header.php';

$monthName = date('F Y', strtotime($year . '-' . $month . '-01'));
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDay = date('N', strtotime($year . '-' . $month . '-01'));
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-calendar-alt me-2"></i>Visit Calendar</h1>
            <p class="text-muted">Schedule and manage property site visits</p>
        </div>
        <div class="btn-group">
            <a href="/admin/visits/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Schedule Visit
            </a>
            <a href="/admin/visits" class="btn btn-outline-secondary">
                <i class="fas fa-list me-2"></i>List View
            </a>
        </div>
    </div>

    <!-- Calendar Navigation -->
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <a href="?month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?>" class="btn btn-outline-primary">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
            <h3 class="mb-0"><?= $monthName ?></h3>
            <a href="?month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?>" class="btn btn-outline-primary">
                Next <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Mon</th>
                            <th class="text-center">Tue</th>
                            <th class="text-center">Wed</th>
                            <th class="text-center">Thu</th>
                            <th class="text-center">Fri</th>
                            <th class="text-center">Sat</th>
                            <th class="text-center text-danger">Sun</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $day = 1;
                        $weeks = ceil(($daysInMonth + $firstDay - 1) / 7);
                        
                        for ($week = 0; $week < $weeks; $week++): ?>
                        <tr style="height: 120px;">
                            <?php for ($d = 1; $d <= 7; $d++): ?>
                            <td class="<?= ($d == 7) ? 'bg-light' : '' ?>" style="vertical-align: top; width: 14.28%;">
                                <?php
                                if (($week == 0 && $d < $firstDay) || $day > $daysInMonth) {
                                    echo '&nbsp;';
                                } else {
                                    $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                    $dayVisits = array_filter($visits, function($v) use ($currentDate) {
                                        return $v['visit_date'] == $currentDate;
                                    });
                                    
                                    echo '<div class="d-flex justify-content-between align-items-start">';
                                    echo '<span class="badge bg-light text-dark">' . $day . '</span>';
                                    if (!empty($dayVisits)) {
                                        echo '<span class="badge bg-primary">' . count($dayVisits) . '</span>';
                                    }
                                    echo '</div>';
                                    
                                    foreach ($dayVisits as $visit) {
                                        $statusColor = [
                                            'scheduled' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            'no_show' => 'warning'
                                        ][$visit['status']] ?? 'secondary';
                                        
                                        echo '<div class="mt-1 p-1 bg-' . $statusColor . ' bg-opacity-10 rounded small">';
                                        echo '<div class="text-truncate fw-bold">' . htmlspecialchars($visit['lead_name']) . '</div>';
                                        echo '<div class="text-truncate text-muted">' . date('h:i A', strtotime($visit['visit_time'])) . '</div>';
                                        echo '<div class="text-truncate text-muted">' . htmlspecialchars($visit['property_title']) . '</div>';
                                        echo '</div>';
                                    }
                                    
                                    $day++;
                                }
                                ?>
                            </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3">
                <span class="badge bg-primary">Scheduled</span>
                <span class="badge bg-success">Completed</span>
                <span class="badge bg-danger">Cancelled</span>
                <span class="badge bg-warning text-dark">No Show</span>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
