# Comprehensive PHP Duplicate File Finder and Remover

$rootPath = "C:\xampp\htdocs\apsdreamhomefinal"
# Removed unused log file path
$backupRootDir = Join-Path $rootPath "backup_php_duplicates_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Ensure backup directory exists
if (-not (Test-Path $backupRootDir)) {
    New-Item -ItemType Directory -Path $backupRootDir | Out-Null
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

# Find and remove PHP duplicates
function Remove-PhpDuplicates {
    # Find all PHP files
    $phpFiles = Get-ChildItem -Path $rootPath -Recurse -Include *.php

    # Group files by name
    $duplicateGroups = $phpFiles | Group-Object Name | Where-Object { $_.Count -gt 1 }

    foreach ($group in $duplicateGroups) {
        Write-Host "Checking duplicates for: $($group.Name)"
        
        # Compare file contents
        $uniqueFiles = $group.Group | Group-Object { Get-FileHash $_.FullName }

        foreach ($contentGroup in $uniqueFiles) {
            if ($contentGroup.Count -gt 1) {
                Write-Host "Duplicate content found for: $($group.Name)"
                
                # Keep the first file, remove others
                $filesToRemove = $contentGroup.Group[1..($contentGroup.Group.Count-1)]

                foreach ($fileToRemove in $filesToRemove) {
                    # Backup file
                    $backupPath = Join-Path $backupRootDir ($fileToRemove.Name)
                    Copy-Item -Path $fileToRemove.FullName -Destination $backupPath -Force

                    # Remove duplicate
                    Remove-Item -Path $fileToRemove.FullName -Force
                    Write-Host "Removed duplicate: $($fileToRemove.FullName)"
                }
            }
        }
    }
}

# Execute duplicate removal
Remove-PhpDuplicates

Write-Host "PHP duplicate cleanup completed. Backup stored in $backupRootDir"
