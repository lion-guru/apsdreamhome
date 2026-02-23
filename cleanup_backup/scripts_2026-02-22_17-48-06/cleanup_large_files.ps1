# Large File Cleanup and Management Script

# Configuration
$rootPath = "C:\xampp\htdocs\apsdreamhome"
$logFile = Join-Path $rootPath "large_files_cleanup_log.txt"
$backupRootDir = Join-Path $rootPath "backup_large_files_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Logging function
function Write-CleanupLog {
    param([string]$Message, [switch]$Warning, [switch]$IsError)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $prefix = if ($IsError) { "[ERROR]" } elseif ($Warning) { "[WARNING]" } else { "[INFO]" }
    $logEntry = "$prefix [$timestamp] $Message"
    
    try {
        Add-Content -Path $logFile -Value $logEntry -ErrorAction Stop
        Write-Host $logEntry
    }
    catch {
        Write-Host $logEntry
    }
}

# Create backup directory
if (-not (Test-Path $backupRootDir)) {
    New-Item -ItemType Directory -Path $backupRootDir | Out-Null
}

# Large file cleanup configuration
$cleanupConfig = @{
    LogFiles = @{
        Path = @("*_log.txt", "*_log_view.txt", "*.log")
        MaxAgeDays = 30
        MaxSizeBytes = 5 * 1024 * 1024  # 5MB
    }
    TempFiles = @{
        Path = @("*.tmp", "*.temp", "temp_*")
        MaxAgeDays = 7
    }
    BackupFiles = @{
        Path = @("backup_*")
        MaxAgeDays = 60
    }
    LargeMediaFiles = @{
        Path = @("*.pdf", "*.zip", "*.rar", "*.7z")
        MaxSizeBytes = 10 * 1024 * 1024  # 10MB
    }
}

# Function to clean up files based on configuration
function Remove-LargeFiles {
    param($Config)
    
    $totalFilesRemoved = 0
    $totalSpaceSaved = 0

    # Process log files
    $logFilesToRemove = Get-ChildItem -Path $rootPath -Recurse -Include $Config.LogFiles.Path |
        Where-Object { 
            $_.LastWriteTime -lt (Get-Date).AddDays(-$Config.LogFiles.MaxAgeDays) -or 
            $_.Length -gt $Config.LogFiles.MaxSizeBytes 
        }
    
    foreach ($file in $logFilesToRemove) {
        $backupPath = Join-Path $backupRootDir $file.Name
        try {
            Copy-Item -Path $file.FullName -Destination $backupPath -Force
            Remove-Item -Path $file.FullName -Force
            $totalFilesRemoved++
            $totalSpaceSaved += $file.Length
            Write-CleanupLog "Removed log file: $($file.FullName)"
        }
        catch {
            Write-CleanupLog "Failed to remove log file: $($file.FullName)" -Warning
        }
    }

    # Process temporary files
    $tempFilesToRemove = Get-ChildItem -Path $rootPath -Recurse -Include $Config.TempFiles.Path |
        Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$Config.TempFiles.MaxAgeDays) }
    
    foreach ($file in $tempFilesToRemove) {
        try {
            Remove-Item -Path $file.FullName -Force
            $totalFilesRemoved++
            $totalSpaceSaved += $file.Length
            Write-CleanupLog "Removed temporary file: $($file.FullName)"
        }
        catch {
            Write-CleanupLog "Failed to remove temporary file: $($file.FullName)" -Warning
        }
    }

    # Process old backup files
    $backupFilesToRemove = Get-ChildItem -Path $rootPath -Recurse -Include $Config.BackupFiles.Path |
        Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$Config.BackupFiles.MaxAgeDays) }
    
    foreach ($file in $backupFilesToRemove) {
        try {
            Remove-Item -Path $file.FullName -Force
            $totalFilesRemoved++
            $totalSpaceSaved += $file.Length
            Write-CleanupLog "Removed old backup file: $($file.FullName)"
        }
        catch {
            Write-CleanupLog "Failed to remove backup file: $($file.FullName)" -Warning
        }
    }

    # Process large media files
    $largeMediaFilesToRemove = Get-ChildItem -Path $rootPath -Recurse -Include $Config.LargeMediaFiles.Path |
        Where-Object { $_.Length -gt $Config.LargeMediaFiles.MaxSizeBytes }
    
    foreach ($file in $largeMediaFilesToRemove) {
        $backupPath = Join-Path $backupRootDir $file.Name
        try {
            Copy-Item -Path $file.FullName -Destination $backupPath -Force
            Remove-Item -Path $file.FullName -Force
            $totalFilesRemoved++
            $totalSpaceSaved += $file.Length
            Write-CleanupLog "Removed large media file: $($file.FullName)"
        }
        catch {
            Write-CleanupLog "Failed to remove large media file: $($file.FullName)" -Warning
        }
    }

    # Log summary
    Write-CleanupLog "Cleanup Summary:"
    Write-CleanupLog "Total Files Removed: $totalFilesRemoved"
    Write-CleanupLog "Total Space Saved: $($totalSpaceSaved / 1MB) MB"
}

# Execute cleanup
try {
    Remove-LargeFiles -Config $cleanupConfig
    Write-CleanupLog "Large file cleanup completed successfully"
}
catch {
    $errorDetails = $_.Exception.Message
    Write-CleanupLog "Critical error during large file cleanup: $errorDetails" -IsError
}

Write-CleanupLog "Cleanup log saved to $logFile"
