<?php
$output = shell_exec('git -C c:/xampp/htdocs/apsdreamhome show --name-status 4b078c20251613c5a32b30208bdf3dcb87fd25b8');
file_put_contents(__DIR__ . '/git_out.txt', $output);
echo "Done";?>