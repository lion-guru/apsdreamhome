# Comprehensive Duplicate File Removal Script

$rootPath = "C:\xampp\htdocs\apsdreamhome"
$logFile = Join-Path $rootPath "duplicate_removal_log.txt"
$backupRootDir = Join-Path $rootPath "backup_duplicates_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Ensure backup directory exists
if (-not (Test-Path $backupRootDir)) {
    New-Item -ItemType Directory -Path $backupRootDir | Out-Null
}

# Logging function
function Write-DuplicateLog {
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

# Function to compute file hash for comparison
function Get-FileHash {
    param([string]$Path)
    $crypto = [System.Security.Cryptography.HashAlgorithm]::Create("SHA256")
    $stream = [System.IO.File]::OpenRead($Path)
    $hash = [System.BitConverter]::ToString($crypto.ComputeHash($stream)).Replace("-", "")
    $stream.Close()
    return $hash
}

# Function to remove duplicate files
function Remove-DuplicateFiles {
    param(
        [string]$SearchPath,
        [string[]]$FileExtensions = @("*.php", "*.js", "*.css", "*.html", "*.txt")
    )

    $totalDuplicatesRemoved = 0
    $totalSpaceSaved = 0

    foreach ($extension in $FileExtensions) {
        # Find all files with the current extension
        $files = Get-ChildItem -Path $SearchPath -Recurse -Include $extension

        # Group files by their hash
        $fileGroups = $files | Group-Object { Get-FileHash $_.FullName }

        foreach ($group in $fileGroups) {
            if ($group.Count -gt 1) {
                # Sort files by last write time (keep the most recently modified)
                $sortedFiles = $group.Group | Sort-Object LastWriteTime -Descending
                $keepFile = $sortedFiles[0]
                $filesToRemove = $sortedFiles[1..($sortedFiles.Count-1)]

                foreach ($fileToRemove in $filesToRemove) {
                    try {
                        # Create backup
                        $backupPath = Join-Path $backupRootDir $fileToRemove.Name
                        Copy-Item -Path $fileToRemove.FullName -Destination $backupPath -Force

                        # Remove duplicate
                        Remove-Item -Path $fileToRemove.FullName -Force
                        
                        Write-DuplicateLog "Removed duplicate file: $($fileToRemove.FullName)"
                        Write-DuplicateLog "Kept file: $($keepFile.FullName)"
                        
                        $totalDuplicatesRemoved++
                        $totalSpaceSaved += $fileToRemove.Length
                    }
                    catch {
                        Write-DuplicateLog "Failed to remove duplicate file: $($fileToRemove.FullName)" -Warning
                    }
                }
            }
        }
    }

    # Log summary
    Write-DuplicateLog "Duplicate Removal Summary:"
    Write-DuplicateLog "Total Duplicate Files Removed: $totalDuplicatesRemoved"
    Write-DuplicateLog "Total Space Saved: $($totalSpaceSaved / 1MB) MB"
}

# Execute duplicate file removal
try {
    Write-DuplicateLog "Starting comprehensive duplicate file removal"
    Remove-DuplicateFiles -SearchPath $rootPath
    Write-DuplicateLog "Duplicate file removal completed successfully"
}
catch {
    $errorDetails = $_.Exception.Message
    Write-DuplicateLog "Critical error during duplicate file removal: $errorDetails" -IsError
}

Write-DuplicateLog "Duplicate removal log saved to $logFile"
