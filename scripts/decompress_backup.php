<?php
$in = gzopen('C:/xampp/htdocs/apsdreamhome/storage/backups/full_backup_2026-09-05_22-30-02.sql.gz', 'rb');
$out = fopen('C:/xampp/htdocs/apsdreamhome/storage/backups/restore.sql', 'wb');
while (!gzeof($in)) {
    fwrite($out, gzread($in, 8192));
}
fclose($out);
gzclose($in);
echo 'Done: ' . filesize('C:/xampp/htdocs/apsdreamhome/storage/backups/restore.sql') . " bytes\n";
echo 'Import now with: mysql -u root apsdreamhome < restore.sql' . "\n";
