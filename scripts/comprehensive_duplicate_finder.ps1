# Comprehensive Duplicate File Finder and Remover

$rootPath = "C:\xampp\htdocs\apsdreamhome"
$logFile = Join-Path $rootPath "comprehensive_duplicates_log.txt"
$backupRootDir = Join-Path $rootPath "backup_all_duplicates_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Ensure backup directory exists
if (-not (Test-Path $backupRootDir)) {
    New-Item -ItemType Directory -Path $backupRootDir | Out-Null
}

# Logging function
function Write-Log {
    param([string]$Message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "[$timestamp] $Message"
    Add-Content -Path $logFile -Value $logEntry
    Write-Host $logEntry
}

# Function to compute file hash for comparison
function Get-FileContentHash {
    param([string]$Path)
    try {
        $crypto = [System.Security.Cryptography.HashAlgorithm]::Create("SHA256")
        $stream = [System.IO.File]::OpenRead($Path)
        $hash = [System.BitConverter]::ToString($crypto.ComputeHash($stream)).Replace("-", "")
        $stream.Close()
        return $hash
    }
    catch {
        Write-Log "Error hashing file $Path : $_"
        return $null
    }
}

# Exclude directories from duplicate search
$excludedDirs = @(
    "\.git",
    "node_modules",
    "vendor",
    "backup_duplicates",
    "backup_php_duplicates",
    "backup_all_duplicates"
)

# Function to find and remove duplicates
function Remove-DuplicateFiles {
    param(
        [string[]]$ExcludedExtensions = @("exe", "dll", "so", "bin"),
        [int]$MinFileSizeBytes = 1024  # Ignore files smaller than 1KB
    )

    Write-Log "Starting comprehensive duplicate file search"

    # Find all files, excluding specified directories and extensions
    $allFiles = Get-ChildItem -Path $rootPath -Recurse -File | 
        Where-Object { 
            $excluded = $false
            foreach ($dir in $excludedDirs) {
                if ($_.FullName -match $dir) {
                    $excluded = $true
                    break
                }
            }
            
            $excluded -eq $false -and 
            $ExcludedExtensions -notcontains $_.Extension.TrimStart('.') -and 
            $_.Length -ge $MinFileSizeBytes
        }

    # Group files by name
    $filesByName = $allFiles | Group-Object Name | Where-Object { $_.Count -gt 1 }

    foreach ($nameGroup in $filesByName) {
        Write-Log "Checking duplicates for: $($nameGroup.Name)"
        
        # Group files with same name by content hash
        $filesByContent = $nameGroup.Group | Group-Object { Get-FileContentHash $_.FullName }

        foreach ($contentGroup in $filesByContent) {
            if ($contentGroup.Count -gt 1) {
                Write-Log "Duplicate content found for: $($nameGroup.Name)"
                
                # Keep the first file, remove others
                $keepFile = $contentGroup.Group[0]
                $filesToRemove = $contentGroup.Group[1..($contentGroup.Group.Count-1)]

                foreach ($fileToRemove in $filesToRemove) {
                    try {
                        # Backup file
                        $relativePath = $fileToRemove.FullName.Substring($rootPath.Length).TrimStart("\")
                        $backupPath = Join-Path $backupRootDir $relativePath
                        
                        # Ensure backup directory exists
                        $backupDir = Split-Path $backupPath
                        if (-not (Test-Path $backupDir)) {
                            New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
                        }
                        
                        # Copy and remove
                        Copy-Item -Path $fileToRemove.FullName -Destination $backupPath -Force
                        Remove-Item -Path $fileToRemove.FullName -Force
                        
                        Write-Log "Removed duplicate: $($fileToRemove.FullName)"
                    }
                    catch {
                        Write-Log "Error processing duplicate file $($fileToRemove.FullName): $_"
                    }
                }
            }
        }
    }

    Write-Log "Comprehensive duplicate cleanup completed"
}

# Execute duplicate removal
try {
    Remove-DuplicateFiles
}
catch {
    Write-Log "Critical error during comprehensive duplicate cleanup: $_"
}

Write-Log "Cleanup log saved to $logFile"
Write-Log "Backup of deleted files stored in $backupRootDir"
