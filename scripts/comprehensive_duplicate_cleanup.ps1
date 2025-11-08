# Comprehensive Duplicate File Cleanup Script

# Configuration
$rootPath = "C:\xampp\htdocs\apsdreamhome"
$logFile = Join-Path $rootPath "duplicate_cleanup_log.txt"
$backupRootDir = Join-Path $rootPath "backup_duplicates_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Ensure backup directory exists
if (-not (Test-Path $backupRootDir)) {
    New-Item -ItemType Directory -Path $backupRootDir | Out-Null
}

# Logging function
function Write-Log {
    param([string]$Message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] $Message"
    Add-Content -Path $logFile -Value $logMessage
    Write-Host $logMessage
}

# Safe file backup function
function Backup-File {
    param(
        [string]$SourcePath,
        [string]$BackupRootDir
    )
    
    try {
        # Preserve original directory structure in backup
        $relativePath = $SourcePath.Substring($rootPath.Length).TrimStart("\")
        $backupPath = Join-Path $BackupRootDir $relativePath
        
        # Ensure backup directory exists
        $backupDir = Split-Path $backupPath
        if (-not (Test-Path $backupDir)) {
            New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
        }
        
        # Copy file
        Copy-Item -Path $SourcePath -Destination $backupPath -Force
        return $true
    }
    catch {
        Write-Log "Error backing up file $SourcePath : $_"
        return $false
    }
}

# Duplicate file cleanup function
function Remove-DuplicateFiles {
    param(
        [string[]]$FileExtensions = @("js", "css")
    )
    
    Write-Log "Starting duplicate file cleanup process"
    
    foreach ($ext in $FileExtensions) {
        Write-Log "Processing $ext files"
        
        # Find duplicate files
        $duplicateFiles = Get-ChildItem -Path $rootPath -Recurse -Include "*.$ext" | 
            Group-Object -Property Name | 
            Where-Object { $_.Count -gt 1 }
        
        foreach ($group in $duplicateFiles) {
            Write-Log "Duplicate found: $($group.Name)"
            
            # Sort files by path to determine which to keep
            $sortedFiles = $group.Group | Sort-Object FullName
            $keepFile = $sortedFiles[0]
            $duplicatesToRemove = $sortedFiles[1..($sortedFiles.Count-1)]
            
            Write-Log "Keeping: $($keepFile.FullName)"
            
            foreach ($fileToRemove in $duplicatesToRemove) {
                try {
                    # Backup file before removal
                    $backupResult = Backup-File -SourcePath $fileToRemove.FullName -BackupRootDir $backupRootDir
                    
                    if ($backupResult) {
                        Remove-Item -Path $fileToRemove.FullName -Force
                        Write-Log "Removed duplicate: $($fileToRemove.FullName)"
                    }
                }
                catch {
                    Write-Log "Error removing duplicate file $($fileToRemove.FullName): $_"
                }
            }
        }
    }
    
    Write-Log "Duplicate cleanup process completed"
}

# Main execution
try {
    Remove-DuplicateFiles
}
catch {
    Write-Log "Critical error during duplicate cleanup: $_"
}

Write-Log "Cleanup log saved to $logFile"
Write-Log "Backup of deleted files stored in $backupRootDir"
