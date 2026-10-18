<?php
$events = $events ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Google Calendar Events</h1>
        <a href="<?= BASE_URL . '/admin/calendar-sync' ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="card"><div class="card-body p-0">
        <?php if (empty($events)): ?>
        <div class="text-center text-muted py-5"><i class="fas fa-calendar-times fa-3x mb-3"></i><p>No upcoming events</p></div>
        <?php else: ?>
        <table class="table mb-0">
            <thead><tr><th>Event</th><th>Start</th><th>End</th><th>Location</th></tr></thead>
            <tbody>
            <?php foreach ($events as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['summary'] ?? '(no title)') ?><?php if (!empty($e['description'])): ?><br><small class="text-muted"><?= htmlspecialchars(mb_substr($e['description'], 0, 80)) ?></small><?php endif; ?></td>
                    <td><?= !empty($e['start']['dateTime']) ? date('M d, Y h:i A', strtotime($e['start']['dateTime'])) : (!empty($e['start']['date']) ? date('M d, Y', strtotime($e['start']['date'])) : '—') ?></td>
                    <td><?= !empty($e['end']['dateTime']) ? date('M d, Y h:i A', strtotime($e['end']['dateTime'])) : (!empty($e['end']['date']) ? date('M d, Y', strtotime($e['end']['date'])) : '—') ?></td>
                    <td><?= htmlspecialchars($e['location'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div></div>
</div>
