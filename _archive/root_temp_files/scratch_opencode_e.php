<?php
chdir('E:\\coding-assistant');
pclose(popen('start /B opencode run "DO NEXT" --auto -c > C:\\xampp\\htdocs\\apsdreamhome\\e_drive_opencode.log 2>&1', 'r'));
echo "Started in background";?>