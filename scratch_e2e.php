<?php
chdir('C:\\xampp\\htdocs\\apsdreamhome');
echo shell_exec('node testing/visual_tests/E2E_MASTER_TEST.mjs 2>&1');
