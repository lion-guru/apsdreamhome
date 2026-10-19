<?php
/** Employee Self-Service - Leave Calendar */

$page_title = $page_title ?? 'Leave & Holiday Calendar';
$holidays = $holidays ?? [];
$leaves = $leaves ?? [];
$stats = $stats ?? [];
$holidayTypes = $holidayTypes ?? [];
$filters = $filters ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.holiday-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 0.75rem; background: #fafafa; transition: all 0.2s; }
.holiday-card:hover { border-color: #7c2d12; background: #fefcf6; }
.holiday-type-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 15px; }
.calendar-day { min-height: 100px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.5rem; background: white; }
.calendar-day.today { border-color: #7c2d12; background: #fefce8; }
.calendar-day.has-holiday { background: #fff7ed; }
.calendar-day.has-leave { background: #f0fdf4; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Leave & Holiday Calendar</h4>
            <p class="text-muted mb-0 small">View company holidays and your leave schedule</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['total'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Holidays</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['upcoming'] ?? 0 ?></div>
                    <div class="text-white-50 small">Upcoming</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['this_month'] ?? 0 ?></div>
                    <div class="text-white-50 small">This Month</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= count($leaves) ?></div>
                    <div class="text-white-50 small">Your Leaves</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <?php foreach ($holidayTypes as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($filters['type'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Region</label>
                    <select name="region" class="form-select form-select-sm">
                        <option value="">All Regions</option>
                        <option value="IN" <?= ($filters['region'] ?? '') === 'IN' ? 'selected' : '' ?>>India</option>
                        <option value="US" <?= ($filters['region'] ?? '') === 'US' ? 'selected' : '' ?>>US</option>
                        <option value="UK" <?= ($filters['region'] ?? '') === 'UK' ? 'selected' : '' ?>>UK</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Year</label>
                    <select name="year" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                            <option value="<?= $y ?>" <?= ($filters['year'] ?? '') == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Month</label>
                    <select name="month" class="form-select form-select-sm">
                        <option value="">All Months</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($filters['month'] ?? '') == $m ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/employee/self-service/holiday-calendar" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Calendar View -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-calendar me-2"></i>Calendar View</h5>
            <div class="d-flex gap-2">
                <select id="calYear" class="form-select form-select-sm" style="width: auto;" onchange="updateCalendar()">
                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?= $y ?>" <?= $y == (date('Y')) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <select id="calMonth" class="form-select form-select-sm" style="width: auto;" onchange="updateCalendar()">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == (int)date('n') ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" style="min-width: 100%;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 14.28%">Sun</th>
                            <th class="text-center" style="width: 14.28%">Mon</th>
                            <th class="text-center" style="width: 14.28%">Tue</th>
                            <th class="text-center" style="width: 14.28%">Wed</th>
                            <th class="text-center" style="width: 14.28%">Thu</th>
                            <th class="text-center" style="width: 14.28%">Fri</th>
                            <th class="text-center" style="width: 14.28%">Sat</th>
                        </tr>
                    </thead>
                    <tbody id="calendarBody">
                        <!-- Calendar generated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Holidays List -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-list me-2"></i>Upcoming Holidays</h5>
            <span class="badge bg-primary"><?= count(array_filter($holidays, fn($h) => strtotime($h['date']) >= strtotime(date('Y-m-d')))) ?> upcoming</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($holidays)): ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Holidays Found</h5>
                <p class="text-muted small mb-3">No holidays match your current filters</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50%">Holiday</th>
                            <th style="width: 15%">Date</th>
                            <th style="width: 15%">Type</th>
                            <th style="width: 10%">Region</th>
                            <th style="width: 10%">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($holidays as $h): 
                            $isUpcoming = strtotime($h['date']) >= strtotime(date('Y-m-d'));
                            $isToday = $h['date'] === date('Y-m-d');
                            $typeColors = [
                                'national' => 'danger',
                                'state' => 'warning',
                                'company' => 'primary',
                                'regional' => 'info',
                                'religious' => 'purple',
                                'optional' => 'secondary'
                            ];
                            $typeColor = $typeColors[$h['type']] ?? 'secondary';
                        ?>
                        <tr class="<?= $isToday ? 'table-warning' : '' ?>">
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($h['name']) ?></div>
                                <?php if (!empty($h['description'])): ?>
                                    <div class="small text-muted"><?= htmlspecialchars($h['description']) ?></div>
                                <?php endif; ?>
                                <?php if ($isToday): ?>
                                    <span class="badge bg-warning text-dark mt-1">Today!</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="<?= $isToday ? 'fw-bold text-warning' : '' ?>">
                                    <?= date('d M Y', strtotime($h['date'])) ?>
                                    <?php if ($h['is_half_day']): ?>
                                        <span class="badge bg-info ms-1">Half Day</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $typeColor ?>"><?= htmlspecialchars(ucfirst($h['type'])) ?></span>
                            </td>
                            <td><?= htmlspecialchars($h['region'] ?? '—') ?></td>
                            <td>
                                <?php if ($h['is_recurring'] && !empty($h['recurrence_rule'])): ?>
                                    <span class="badge bg-light text-dark" style="background: #f1f5f9; color: #475569;">
                                        <i class="fas fa-sync me-1"></i> Recurring
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Your Leaves -->
    <?php if (!empty($leaves)): ?>
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-calendar-check me-2"></i>Your Leave Schedule</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Applied</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaves as $leave): 
                            $statusClass = [
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'cancelled' => 'secondary',
                            ][$leave['status']] ?? 'secondary';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($leave['leave_type_name'] ?? 'Leave') ?></td>
                            <td><?= date('d M Y', strtotime($leave['start_date'])) ?></td>
                            <td><?= date('d M Y', strtotime($leave['end_date'])) ?></td>
                            <td><?= $leave['total_days'] ?></td>
                            <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst($leave['status']) ?></span></td>
                            <td><?= date('d M Y', strtotime($leave['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
// Holiday data for calendar
const holidays = <?= json_encode($holidays) ?>;
const leaves = <?= json_encode($leaves) ?>;

function updateCalendar() {
    const year = parseInt(document.getElementById('calYear').value);
    const month = parseInt(document.getElementById('calMonth').value);
    renderCalendar(year, month);
}

function renderCalendar(year, month) {
    const tbody = document.getElementById('calendarBody');
    const firstDay = new Date(year, month - 1, 1);
    const lastDay = new Date(year, month, 0);
    const startDay = firstDay.getDay(); // 0 = Sunday
    const daysInMonth = lastDay.getDate();
    
    let html = '<tr>';
    
    // Empty cells before first day
    for (let i = 0; i < startDay; i++) {
        html += '<td class="calendar-day empty"></td>';
    }
    
    // Days of month
    for (let day = 1; day <= daysInMonth; day++) {
        if ((startDay + day - 1) % 7 === 0 && day !== 1) {
            html += '</tr><tr>';
        }
        
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = dateStr === '<?= date('Y-m-d') ?>';
        const dayHolidays = holidays.filter(h => h.date === dateStr);
        const dayLeaves = leaves.filter(l => l['start_date'] <= dateStr && l['end_date'] >= dateStr);
        const hasHoliday = dayHolidays.length > 0;
        const hasLeave = dayLeaves.length > 0;
        
        let cellClass = 'calendar-day';
        if (isToday) cellClass += ' today';
        if (hasHoliday) cellClass += ' has-holiday';
        if (hasLeave) cellClass += ' has-leave';
        
        html += `<td class="${cellClass}">`;
        html += `<div class="fw-semibold">${day}</div>`;
        
        if (hasHoliday) {
            html += '<div class="small">';
            dayHolidays.forEach(h => {
                const typeColors = {
                    'national': 'danger',
                    'state': 'warning',
                    'company': 'primary',
                    'regional': 'info',
                    'religious': 'purple',
                    'optional': 'secondary'
                };
                const typeColor = typeColors[h.type] || 'secondary';
                html += `<span class="badge bg-${typeColor} badge-sm me-1" style="font-size: 0.6rem;">${h.name}</span>`;
            });
            html += '</div>';
        }
        
        if (hasLeave) {
            html += '<div class="small">';
            dayLeaves.forEach(l => {
                html += `<span class="badge bg-success badge-sm me-1" style="font-size: 0.6rem;">Leave: ${l['leave_type_name']}</span>`;
            });
            html += '</div>';
        }
        
        html += '</td>';
    }
    
    // Fill remaining cells
    const totalCells = startDay + daysInMonth;
    const remainingCells = (Math.ceil(totalCells / 7) * 7) - totalCells;
    for (let i = 0; i < remainingCells; i++) {
        html += '<td class="calendar-day empty"></td>';
    }
    
    html += '</tr>';
    tbody.innerHTML = html;
}

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    renderCalendar(parseInt(document.getElementById('calYear').value), parseInt(document.getElementById('calMonth').value));
});
</script>