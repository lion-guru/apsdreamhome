<?php $this->layout = 'layouts/admin'; ?>
<h1>Messenger Routing Dashboard</h1>
<p>Queue depth by transport (messenger_messages). Failed DLQ threshold: <?= $threshold ?>. Cached 300s.</p>

<h2>Queue Depths</h2>
<table border="1" cellpadding="6">
<tr><th>Queue</th><th>Depth</th><th>Oldest Available</th><th>Newest Created</th><th>Oldest Created</th></tr>
<?php foreach ($queueDepths as $q): ?>
<tr><td><?= htmlspecialchars($q['queue_name']) ?></td><td><?= $q['depth'] ?></td><td><?= $q['oldest_available'] ?></td><td><?= $q['newest'] ?></td><td><?= $q['oldest'] ?></td></tr>
<?php endforeach; ?>
<?php if (empty($queueDepths)): ?><tr><td colspan="5">No queued messages — all transports idle (sync transport in dev, see Dead Letter for failed).</td></tr><?php endif; ?>
</table>

<h2>Dead Letter Summary</h2>
<p>Total Failed: <?= $stats['total_failed'] ?> | Oldest: <?= $stats['oldest_failure'] ?? '-' ?> | Newest: <?= $stats['newest_failure'] ?? '-' ?></p>
<p><a href="/admin/messenger/dead-letters">Open Dead Letter Queue →</a></p>

<h2>Failed by Transport/Class</h2>
<table border="1" cellpadding="6">
<tr><th>Transport</th><th>Class</th><th>Count</th><th>First</th><th>Last</th></tr>
<?php foreach ($byTransport as $r): ?>
<tr><td><?= htmlspecialchars($r['queue_name']) ?></td><td><?= htmlspecialchars($r['class_name'] ?? '-') ?></td><td><?= $r['count'] ?></td><td><?= $r['first_failed'] ?></td><td><?= $r['last_failed'] ?></td></tr>
<?php endforeach; ?>
<?php if (empty($byTransport)): ?><tr><td colspan="5">No failures.</td></tr><?php endif; ?>
</table>
