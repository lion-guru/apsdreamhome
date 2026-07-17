<?php
$output = [];
exec('git log -n 3 -p -- routes/web.php', $output);
echo implode("\n", $output);
?>
