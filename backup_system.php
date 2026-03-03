<?php
/**
 * APS Dream Home - Backup System
 * Automated database and file backup
 */

class BackupSystem {
    private $backupDir;
    
    public function __construct() {
        $this->backupDir = __DIR__ . "/backups";
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    public function createDatabaseBackup() {
        $filename = "database_backup_" . date("Y-m-d_H-i-s") . ".sql";
        $filepath = $this->backupDir . "/" . $filename;
        
        $command = "mysqldump -u root -p apsdreamhome > " . $filepath;
        exec($command);
        
        return file_exists($filepath) ? $filepath : false;
    }
    
    public function createFilesBackup() {
        $filename = "files_backup_" . date("Y-m-d_H-i-s") . ".zip";
        $filepath = $this->backupDir . "/" . $filename;
        
        $dirsToBackup = ["app", "public", "config"];
        $this->createZipFromDirectories($dirsToBackup, $filepath);
        
        return file_exists($filepath) ? $filepath : false;
    }
    
    public function cleanupOldBackups($keepDays = 7) {
        $files = glob($this->backupDir . "/*");
        $cutoff = time() - ($keepDays * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
    
    private function createZipFromDirectories($dirs, $zipFile) {
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            foreach ($dirs as $dir) {
                if (is_dir(__DIR__ . "/../$dir")) {
                    $this->addDirectoryToZip($zip, __DIR__ . "/../$dir", $dir);
                }
            }
            $zip->close();
        }
    }
    
    private function addDirectoryToZip($zip, $dir, $baseDir) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== "." && $file !== "..") {
                $path = $dir . "/" . $file;
                if (is_dir($path)) {
                    $this->addDirectoryToZip($zip, $path, $baseDir . "/" . $file);
                } else {
                    $zip->addFile($path, $baseDir . "/" . $file);
                }
            }
        }
    }
}

// Usage example
$backup = new BackupSystem();
$dbBackup = $backup->createDatabaseBackup();
$filesBackup = $backup->createFilesBackup();
$backup->cleanupOldBackups();

echo "💾 Backup completed:\n";
echo "   Database: " . basename($dbBackup) . "\n";
echo "   Files: " . basename($filesBackup) . "\n";
?>