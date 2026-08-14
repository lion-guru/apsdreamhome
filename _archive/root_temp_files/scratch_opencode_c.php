<?php
chdir('C:\\xampp\\htdocs\\apsdreamhome');
pclose(popen('start /B opencode run "DO NEXT" --auto -c > C:\\xampp\\htdocs\\apsdreamhome\\c_drive_opencode.log 2>&1', 'r'));
echo "Started in background";?>