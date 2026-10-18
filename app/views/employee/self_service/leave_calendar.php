<?php
/**
 * Leave Calendar View
 * Visual calendar for leave management
*/

$page_title = $page_title ?? 'Leave Calendar';
$financial_year = $financial_year ?? date('Y');
$month = $_GET['month'] ?? date('Y-m');

$employeeId = $_SESSION['employee_id'] ?? 0;
$leaveBalances = $leave_balances ?? [];
$leaveHistory = $leave_history ?? [];
$leaveTypes = $leave_types ?? [];

// Build leave events for calendar
$leaveEvents = [];
foreach ($leaveHistory as $leave) {
    $start = new DateTime($leave['start_date']);
    $end = new DateTime($leave['end_date']);
    $color = $leave['color'] ?? '#007bff';
    $status = $leave['status'] ?? 'pending';
    
    $statusColors = [
        'approved' => '#28a745',
        'pending' => '#ffc107',
        'rejected' => '#dc3545',
        'cancelled' => '#6c757d'
    ];
    $bgColor = $statusColors[$status] ?? $color;
    
    for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
        $leaveEvents[] = [
            'date' => $d->format('Y-m-d'),
            'title' => $leave['leave_type_name'] ?? 'Leave',
            'status' => $status,
            'bgColor' => $bgColor,
            'leaveId' => $leave['id']
        ];
    }
}
$leaveEventsJson = json_encode($leaveEvents);
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.leave-calendar-card { border: none; border-radius: 14px; }
.fc { font-family: inherit; }
.fc .fc-toolbar-title { font-size: 1.25rem; font-weight: 600; }
.fc .fc-button { background: #7c2d12; border-color: #7c2d12; }
.fc .fc-button:hover { background: #5a1f0e; border-color: #5a1f0e; }
.fc .fc-button-active { background: #5a1f0e; border-color: #5a1f0e; }
.fc-daygrid-day { height: 100px; min-height: 100px; }
.fc-daygrid-event { border: none; border-radius: 4px; padding: 2px 4px; font-size: 0.75rem; font-weight: 500; margin: 1px; cursor: pointer; }
.fc-daygrid-more-link { font-size: 0.7rem; font-weight: 600; }
.fc-day-today { background: #fff3e0 !important; }
.leave-balance-mini { border: 1px solid #e2e8f0; border-radius: 10px; transition: transform 0.2s; }
.leave-balance-mini:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.leave-type-badge { font-size: 0.65rem; padding: 2px 6px; border-radius: 20px; }
.legend-item { display: inline-flex; align-items: center; gap: 6px; margin-right: 16px; font-size: 0.8rem; }
.legend-color { width: 16px; height: 16px; border-radius: 4px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-calendar-alt me-2 text-danger"></i>Leave Calendar</h4>
            <p class="text-muted mb-0">Visual view of your leave schedule — <?= date('F Y', strtotime($month . '-01')) ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/employee/self-service/leave" class="btn btn-outline-primary">
                <i class="fas fa-list me-1"></i> List View
            </a>
            <a href="<?= BASE_URL ?>/employee/self-service/leave?apply=1" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Apply Leave
            </a>
        </div>
    </div>

    <!-- Month Navigation -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="<?= BASE_URL ?>/employee/self-service/leave-calendar?month=<?= date('Y-m', strtotime($month . ' -1 month')) ?>" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-chevron-left me-1"></i> Previous
                    </a>
                </div>
                <div class="col-md-6 text-center">
                    <h5 class="mb-0 fw-bold text-primary"><?= date('F Y', strtotime($month . '-01')) ?></h5>
                </div>
                <div class="col-md-3 text-end">
                    <a href="<?= BASE_URL ?>/employee/self-service/leave-calendar?month=<?= date('Y-m', strtotime($month . ' +1 month')) ?>" class="btn btn-outline-secondary w-100">
                        Next <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Balances Summary -->
    <div class="row g-3 mb-4">
        <?php if (empty($leaveBalances)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No leave balances found for this year. Contact HR to set up leave types.
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($leaveBalances as $lb): 
            $usedPct = ($lb['allocated_days'] ?? 0) > 0 
                ? (($lb['used_days'] ?? 0) / ($lb['allocated_days'] ?? 1)) * 100 
                : 0;
            $remaining = ($lb['remaining_days'] ?? 0);
            $badgeClass = $remaining <= 0 ? 'bg-danger' : ($remaining <= 2 ? 'bg-warning text-dark' : 'bg-success');
        ?>
        <div class="col-md-3 col-sm-6">
            <div class="card leave-balance-mini shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <span class="badge leave-type-badge mb-2" style="background: <?= $lb['color'] ?? '#007bff' ?>; color: white;">
                        <?= htmlspecialchars($lb['leave_type_code'] ?? '') ?>
                    </span>
                    <h6 class="card-title mb-3"><?= htmlspecialchars($lb['leave_type_name'] ?? '') ?></h6>
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="fw-bold text-primary"><?= number_format($lb['allocated_days'] ?? 0, 1) ?></div>
                            <small class="text-muted">Allocated</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning"><?= number_format($lb['used_days'] ?? 0, 1) ?></div>
                            <small class="text-muted">Used</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold fs-5 <?= $badgeClass ?>"><?= number_format($remaining, 1) ?></div>
                            <small class="text-muted">Remaining</small>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-<?= $usedPct > 80 ? 'danger' : ($usedPct > 50 ? 'warning' : 'success') ?>" 
                             style="width: <?= min($usedPct, 100) ?>%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block"><?= round($usedPct) ?>% Utilized</small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Calendar Legend -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <h6 class="mb-2 fw-semibold">Legend</h6>
            <div class="d-flex flex-wrap">
                <span class="legend-item"><span class="legend-color" style="background: #28a745;"></span> Approved</span>
                <span class="legend-item"><span class="legend-color" style="background: #ffc107;"></span> Pending</span>
                <span class="legend-item"><span class="legend-color" style="background: #dc3545;"></span> Rejected</span>
                <span class="legend-item"><span class="legend-color" style="background: #6c757d;"></span> Cancelled</span>
                <span class="legend-item ms-auto"><i class="fas fa-circle text-warning me-1"></i> Today</span>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div id="leaveCalendar"></div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function() {
    const leaveEvents = <?= $leaveEventsJson ?>;
    
    const calendarEl = document.getElementById('leaveCalendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: '<?= $month . '-01' ?>',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek,listMonth'
        },
        events: leaveEvents,
        eventDisplay: 'block',
        eventBackgroundColor: function(event) {
            return event.extendedProps.bgColor;
        },
        eventBorderColor: function(event) {
            return event.extendedProps.bgColor;
        },
        eventTextColor: '#fff',
        eventClick: function(info) {
            const event = info.event;
            const status = event.extendedProps.status;
            const statusLabels = {
                'approved': 'Approved',
                'pending': 'Pending',
                'rejected': 'Rejected',
                'cancelled': 'Cancelled'
            };
            const statusBadges = {
                'approved': 'bg-success',
                'pending': 'bg-warning text-dark',
                'rejected': 'bg-danger',
                'cancelled': 'bg-secondary'
            };
            
            if (confirm(`${event.title} (${statusLabels[status] || status})\n\nDo you want to view details?`)) {
                window.location.href = '<?= BASE_URL ?>/employee/self-service/leave/' + event.extendedProps.leaveId;
            }
        },
        dayCellDidMount: function(info) {
            if (info.date.toDateString() === new Date().toDateString()) {
                info.el.style.backgroundColor = '#fff3e0';
                info.el.style.border = '2px solid #f59e0b';
            }
        },
        height: 'auto',
        expandRows: true,
        dayMaxEvents: 3,
        moreLinkContent: function(args) {
            return '+ ' + args.num + ' more';
        }
    });
    
    calendar.render();
});
</script>

<!-- FullCalendar CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/main.min.css" rel="stylesheet" nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/main.min.css" rel="stylesheet" nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.10/main.min.css" rel="stylesheet" nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/main.min.js" nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/main.min.js" nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.10/main.min.js" nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></script>