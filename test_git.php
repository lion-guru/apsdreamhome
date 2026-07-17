<?php
$old = shell_exec('git show ad684118ce3be8a9bff46954d6d037b928653919^:app/Http/Controllers/Front/PageController.php');
$new = shell_exec('git show ad684118ce3be8a9bff46954d6d037b928653919:app/Http/Controllers/Front/PageController.php');
$current = file_get_contents(__DIR__ . '/app/Http/Controllers/Front/PageController.php');

echo "Old (Commit before ad68...): " . substr_count($old, "\n") . " lines\n";
echo "New (Commit ad68...): " . substr_count($new, "\n") . " lines\n";
echo "Current: " . substr_count($current, "\n") . " lines\n";

// Get diff
$diff = shell_exec('git diff ad684118ce3be8a9bff46954d6d037b928653919^ ad684118ce3be8a9bff46954d6d037b928653919 --stat');
echo "Stat diff:\n" . $diff;
?>
