<?php
$page_title = $page_title ?? __('assoc_sched_title', [], 'My Schedule');
$current_page = 'schedule';
$events = $events ?? [];
$month = (int)($_GET['month'] ?? date('m'));
$year = (int)($_GET['year'] ?? date('Y'));
$today = date('Y-m-d');
?>

<style>
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
    .cal-header { background: #f8fafc; padding: 8px; text-align: center; font-weight: 700; font-size: 0.75rem; color: #64748b; text-transform: uppercase; }
    .cal-day { min-height: 80px; padding: 6px; border: 1px solid #f1f5f9; border-radius: 6px; background: #fff; cursor: pointer; transition: all 0.15s; position: relative; }
    .cal-day:hover { border-color: #c7d2fe; background: #fafafe; }
    .cal-day.today { border: 2px solid #0d9488; background: #faf5ff; }
    .cal-day.other-month { opacity: 0.35; }
    .cal-day .day-num { font-weight: 700; font-size: 0.82rem; color: #1e293b; margin-bottom: 4px; }
    .cal-day.today .day-num { color: #0d9488; }
    .cal-event { padding: 2px 6px; border-radius: 4px; font-size: 0.68rem; font-weight: 600; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; }
    .cal-event.task { background: #dbeafe; color: #1d4ed8; }
    .cal-event.site_visit { background: #fef3c7; color: #b45309; }
    .cal-event.overdue { background: #fee2e2; color: #dc2626; }
    .event-list-item { padding: 10px 14px; border-radius: 10px; margin-bottom: 8px; background: #f8fafc; border-left: 4px solid #6366f1; display: flex; justify-content: space-between; align-items: center; }
    .event-list-item.site_visit { border-left-color: #f59e0b; }
    .event-list-item.overdue { border-left-color: #ef4444; background: #fef2f2; }
</style>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i><?= __('assoc_sched_title', [], 'My Schedule') ?></h5>
            <div class="d-flex gap-2">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" id="listViewBtn" onclick="showView('list')"><i class="fas fa-list me-1"></i> <?= __('assoc_sched_list', [], 'List') ?></button>
                    <button type="button" class="btn btn-outline-primary" id="calendarViewBtn" onclick="showView('calendar')"><i class="fas fa-calendar me-1"></i> <?= __('assoc_sched_calendar', [], 'Calendar') ?></button>
                </div>
                <a href="<?= BASE_URL ?>/associate/crm" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> <?= __('assoc_sched_add_task', [], 'Add Task') ?></a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="listView">
            <?php if (empty($events)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-check fa-3x text-muted mb-3" class="style-82835"></i>
                    <h5 class="text-muted"><?= __('assoc_sched_empty', [], 'No scheduled events') ?></h5>
                    <p class="text-muted"><?= __('assoc_sched_empty_desc', [], 'Your upcoming tasks and site visits will appear here.') ?></p>
                    <a href="<?= BASE_URL ?>/associate/crm" class="btn btn-primary"><i class="fas fa-plus me-1"></i> <?= __('assoc_sched_add_task', [], 'Add Task') ?></a>
                </div>
            <?php else: ?>
                <?php
                $currentDate = '';
                foreach ($events as $event):
                    $eventDate = date('Y-m-d', strtotime($event['event_date']));
                    $isToday = ($eventDate === $today);
                    $isPast = strtotime($event['event_date'] . ' ' . ($event['event_time'] ?? '00:00')) < time();
                    $isVisit = (($event['event_type'] ?? '') === 'site_visit');
                    $priorityClass = match($event['priority'] ?? 'medium') { 'high'=>'danger', 'medium'=>'warning', 'low'=>'info', default=>'secondary' };

                    if ($eventDate !== $currentDate):
                        $currentDate = $eventDate;
                ?>
                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <?php if ($isToday): ?>
                                <span class="badge bg-primary"><?= __('assoc_sched_today', [], 'Today') ?></span>
                            <?php elseif ($isPast): ?>
                                <span class="badge bg-secondary"><?= __('assoc_sched_past', [], 'Past') ?></span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark"><?= date('d M Y', strtotime($eventDate)) ?></span>
                            <?php endif; ?>
                            <div class="flex-grow-1" class="style-83167"></div>
                            <small class="text-muted"><?= date('l', strtotime($eventDate)) ?></small>
                        </div>
                <?php endif; ?>
                        <div class="event-list-item <?= $isVisit ? 'site_visit' : '' ?> <?= $isPast && ($event['status'] ?? '') !== 'completed' ? 'overdue' : '' ?>">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" class="style-5206">
                                    <i class="fas fa-<?= $isVisit ? 'map-marker-alt' : 'tasks' ?> <?= $isToday ? 'text-primary' : ($isVisit ? 'text-warning' : 'text-muted') ?>"></i>
                                </div>
                                <div>
                                    <strong class="<?= $isPast ? 'text-muted' : '' ?>"><?= htmlspecialchars($event['title'] ?? '') ?></strong>
                                    <?php if (!empty($event['lead_name'])): ?>
                                        <br><small class="text-muted"><?= __('assoc_sched_lead', [], 'Lead') ?>: <?= htmlspecialchars($event['lead_name'] ?? '') ?></small>
                                    <?php endif; ?>
                                    <?php if ($isVisit && !empty($event['event_time'])): ?>
                                        <br><small class="text-warning"><i class="fas fa-clock me-1"></i><?= date('h:i A', strtotime($event['event_time'])) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <?php if (!empty($event['event_time']) && !$isVisit): ?>
                                    <small class="text-muted"><i class="fas fa-clock me-1"></i><?= date('h:i A', strtotime($event['event_time'])) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($event['priority'])): ?>
                                    <br><span class="badge bg-<?= $priorityClass ?> mt-1"><?= ucfirst($event['priority']) ?></span>
                                <?php endif; ?>
                                <?php if ($isVisit): ?>
                                    <br><span class="badge bg-warning text-dark mt-1"><i class="fas fa-map-marker-alt me-1"></i><?= __('assoc_sched_visit', [], 'Visit') ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                <?php endforeach; ?>
                    </div>
            <?php endif; ?>
        </div>

        <div id="calendarView" class="style-54390">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="?month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chevron-left"></i></a>
                <h5 class="mb-0"><?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h5>
                <div class="d-flex gap-2">
                    <a href="?month=<?= date('m') ?>&year=<?= date('Y') ?>" class="btn btn-outline-primary btn-sm"><?= __('assoc_sched_today', [], 'Today') ?></a>
                    <a href="?month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>

            <div class="cal-grid">
                <?php foreach ([__('assoc_sched_sun', [], 'Sun'),__('assoc_sched_mon', [], 'Mon'),__('assoc_sched_tue', [], 'Tue'),__('assoc_sched_wed', [], 'Wed'),__('assoc_sched_thu', [], 'Thu'),__('assoc_sched_fri', [], 'Fri'),__('assoc_sched_sat', [], 'Sat')] as $d): ?>
                    <div class="cal-header"><?= $d ?></div>
                <?php endforeach; ?>

                <?php
                $firstDay = date('w', mktime(0, 0, 0, $month, 1, $year));
                $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
                $prevMonth = $month == 1 ? 12 : $month - 1;
                $prevYear = $month == 1 ? $year - 1 : $year;
                $daysInPrev = date('t', mktime(0, 0, 0, $prevMonth, 1, $prevYear));

                $eventsByDate = [];
                foreach ($events as $ev) {
                    $d = date('Y-m-d', strtotime($ev['event_date']));
                    $eventsByDate[$d][] = $ev;
                }

                for ($i = 0; $i < $firstDay; $i++) {
                    $day = $daysInPrev - $firstDay + 1 + $i;
                    $dateStr = sprintf('%04d-%02d-%02d', $prevYear, $prevMonth, $day);
                    echo "<div class='cal-day other-month'><div class='day-num'>$day</div></div>";
                }

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $isToday = ($dateStr === $today);
                    $dayEvents = $eventsByDate[$dateStr] ?? [];
                    echo "<div class='cal-day " . ($isToday ? 'today' : '') . "'>";
                    echo "<div class='day-num'>$day</div>";
                    foreach (array_slice($dayEvents, 0, 3) as $ev) {
                        $isVisit = (($ev['event_type'] ?? '') === 'site_visit');
                        $cls = $isVisit ? 'site_visit' : 'task';
                        $isOverdue = strtotime($dateStr) < strtotime($today) && ($ev['status'] ?? '') !== 'completed';
                        if ($isOverdue) $cls = 'overdue';
                        echo "<div class='cal-event $cls' title='" . htmlspecialchars($ev['title'] ?? '') . "'>" . htmlspecialchars(mb_substr($ev['title'] ?? '', 0, 20)) . "</div>";
                    }
                    if (count($dayEvents) > 3) {
                        echo "<div class='text-muted' style='font-size:0.65rem;'>+" . (count($dayEvents) - 3) . " <?= __('assoc_sched_more', [], 'more') ?></div>";
                    }
                    echo "</div>";
                }

                $totalCells = $firstDay + $daysInMonth;
                $remaining = (7 - ($totalCells % 7)) % 7;
                for ($i = 1; $i <= $remaining; $i++) {
                    echo "<div class='cal-day other-month'><div class='day-num'>$i</div></div>";
                }
                ?>
            </div>

            <div class="d-flex gap-3 mt-3 justify-content-center">
                <small><span class="cal-event task" class="style-35851"><?= __('assoc_sched_task', [], 'Task') ?></span></small>
                <small><span class="cal-event site_visit" class="style-35851"><?= __('assoc_sched_visit', [], 'Site Visit') ?></span></small>
                <small><span class="cal-event overdue" class="style-35851"><?= __('assoc_sched_overdue', [], 'Overdue') ?></span></small>
            </div>
        </div>
    </div>
</div>

<script>
function showView(view) {
    document.getElementById('listView').style.display = view === 'list' ? 'block' : 'none';
    document.getElementById('calendarView').style.display = view === 'calendar' ? 'block' : 'none';
    document.getElementById('listViewBtn').classList.toggle('active', view === 'list');
    document.getElementById('calendarViewBtn').classList.toggle('active', view === 'calendar');
}
</script>
