<?php $this->layout = 'layouts/admin'; ?>
<h1>Dead Letter Queue</h1>
<div class="stats">
  <div>Total Failed: <?= $stats['total_failed'] ?> (threshold <?= $threshold ?>, retention <?= $retentionDays ?> days)</div>
  <div>By Transport: <?php foreach ($stats['by_transport'] as $k=>$v) echo $k . '=' . $v . '; '; ?></div>
  <div>By Class: <?php foreach ($stats['by_class'] as $k=>$v) echo ($k ?: 'unknown') . '=' . $v . '; '; ?></div>
  <div>Oldest: <?= $stats['oldest_failure'] ?? '-' ?> Newest: <?= $stats['newest_failure'] ?? '-' ?></div>
</div>

<h2>Grouped by Transport/Class</h2>
<table border="1" cellpadding="6">
<tr><th>Transport</th><th>Class</th><th>Count</th><th>First</th><th>Last</th></tr>
<?php foreach ($byTransport as $r): ?>
<tr><td><?= htmlspecialchars($r['queue_name']) ?></td><td><?= htmlspecialchars($r['class_name'] ?? '-') ?></td><td><?= $r['count'] ?></td><td><?= $r['first_failed'] ?></td><td><?= $r['last_failed'] ?></td></tr>
<?php endforeach; ?>
</table>

<h2>Failed Messages</h2>
<form method="post" action="/admin/messenger/dead-letters/cleanup" onsubmit="return confirm('Cleanup old messages?')">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? $_SESSION['csrf_token'] ?? '') ?>">
  <button type="submit">Cleanup Old (> <?= $retentionDays ?> days)</button>
</form>
<table border="1" cellpadding="6">
<tr><th>ID</th><th>Queue</th><th>Class</th><th>Available</th><th>Created</th><th>Body preview</th><th>Actions</th></tr>
<?php foreach ($failed as $m): 
  $body = json_decode($m['body'], true);
  $class = $body['class'] ?? '-';
?>
<tr>
  <td><?= $m['id'] ?></td>
  <td><?= htmlspecialchars($m['queue_name']) ?></td>
  <td><?= htmlspecialchars(is_string($class) ? $class : '-') ?></td>
  <td><?= $m['available_at'] ?></td>
  <td><?= $m['created_at'] ?></td>
  <td><code><?= htmlspecialchars(substr((string)$m['body'], 0, 200)) ?>…</code></td>
  <td>
    <form method="post" action="/admin/messenger/dead-letters/<?= $m['id'] ?>/retry" style="display:inline"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? $_SESSION['csrf_token'] ?? '') ?>"><button type="submit">Retry</button></form>
    <form method="post" action="/admin/messenger/dead-letters/<?= $m['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete message #<?= $m['id'] ?>?')"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? $_SESSION['csrf_token'] ?? '') ?>"><button type="submit">Delete</button></form>
  </td>
</tr>
<?php endforeach; ?>
<?php if (empty($failed)): ?><tr><td colspan="7">No failed messages — queue healthy.</td></tr><?php endif; ?>
</table>
