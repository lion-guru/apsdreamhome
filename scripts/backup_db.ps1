<#>
.SYNOPSIS
    Creates a MySQL database backup with timestamp.

.DESCRIPTION
    Creates a compressed SQL dump of the apsdreamhome database.
    Stores in backups/db/ directory with timestamp.

.NOTES
    Run as scheduled task daily at 00:30.
#>

param(
    [string]$BackupDir = "C:\xampp\htdocs\apsdreamhome\backups\db",
    [string]$DbName = "apsdreamhome",
    [string]$DbUser = "root",
    [string]$DbPass = "",
    [string]$MySQLPath = "C:\xampp\mysql\bin"
)

$ErrorActionPreference = 'Stop'

# Create backup directory if not exists
if (-not (Test-Path $BackupDir)) {
    New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null
}

$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$backupFile = "$BackupDir\apsdreamhome_$timestamp.sql"
$compressedFile = "$backupFile.gz"

Write-Host "=== Database Backup Started ===" -ForegroundColor Cyan
Write-Host "Database: $DbName"
Write-Host "Output: $compressedFile"

try {
    # Build mysqldump command
    $cmd = "$MySQLPath\mysqldump.exe"
    $args = @(
        "--user=$DbUser",
        "--databases", $DbName,
        "--single-transaction",
        "--routines",
        "--triggers",
        "--events",
        "--hex-blob",
        "--skip-lock-tables",
        "--quick",
        "--max_allowed_packet=1G"
    )
    
    if ($DbPass) {
        $args += "--password=$DbPass"
    }
    
    Write-Host "Running mysqldump..."
    $process = Start-Process -FilePath $cmd -ArgumentList $args -NoNewWindow -Wait -PassThru -RedirectStandardOutput $backupFile -RedirectStandardError "$backupFile.err"
    
    if ($process.ExitCode -ne 0) {
        $err = Get-Content "$backupFile.err" -Raw
        throw "mysqldump failed with exit code $($process.ExitCode): $err"
    }
    
    # Compress
    Write-Host "Compressing backup..."
    & "C:\Program Files\7-Zip\7z.exe" a -tgzip $compressedFile $backupFile | Out-Null
    
    # Remove uncompressed file
    Remove-Item $backupFile -Force
    Remove-Item "$backupFile.err" -Force -ErrorAction SilentlyContinue
    
    $size = (Get-Item $compressedFile).Length
    $sizeMB = [math]::Round($size / 1MB, 2)
    
    Write-Host "✅ Backup completed: $compressedFile ($sizeMB MB)" -ForegroundColor Green
    
    # Cleanup old backups (keep last 30 days)
    $cutoff = (Get-Date).AddDays(-30)
    Get-ChildItem $BackupDir -Filter "apsdreamhome_*.sql.gz" | Where-Object { $_.LastWriteTime -lt $cutoff } | Remove-Item -Force
    Write-Host "Old backups cleaned up (older than 30 days)"
    
    Write-Host "=== Backup Completed Successfully ===" -ForegroundColor Green
    
} catch {
    Write-Error "Backup failed: $_"
    exit 1
}