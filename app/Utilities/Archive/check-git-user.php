<?php
echo "Current Git User Configuration:\n";
echo "Name: " . shell_exec('git config user.name') . "\n";
echo "Email: " . shell_exec('git config user.email') . "\n";
echo "Remote: " . shell_exec('git config remote.origin.url') . "\n";
echo "Branch: " . shell_exec('git branch --show-current') . "\n";
?>
