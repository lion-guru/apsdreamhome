<?php
chdir('C:\\xampp\\htdocs\\apsdreamhome');
pclose(popen('start /B node testing/visual_tests/E2E_MASTER_TEST.mjs > e2e_output.txt 2>&1', 'r'));
echo "Started in background";
