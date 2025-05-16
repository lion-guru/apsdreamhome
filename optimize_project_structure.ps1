# Project Structure Optimization Script

$rootPath = "C:\xampp\htdocs\apsdreamhomefinal"
$logFile = Join-Path $rootPath "project_structure_optimization_log.txt"
$backupRootDir = Join-Path $rootPath "backup_project_structure_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Create backup and log directories
if (-not (Test-Path $backupRootDir)) {
    New-Item -ItemType Directory -Path $backupRootDir | Out-Null
}

# Logging function
function Write-OptimizationLog {
    param([string]$Message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "[$timestamp] $Message"
    
    try {
        Add-Content -Path $logFile -Value $logEntry -ErrorAction Stop
    }
    catch {
        # Fallback logging if file is locked
        Write-Host $logEntry
    }
    
    Write-Host $logEntry
}

# Function to analyze and optimize directories
function Optimize-ProjectStructure {
    # Directories to analyze and potentially clean
    $directoriesToClean = @(
        "$rootPath\backups",
        "$rootPath\backup_duplicates",
        "$rootPath\backup_php_duplicates",
        "$rootPath\backup_all_duplicates"
    )

    # Cleanup old backup directories
    foreach ($dir in $directoriesToClean) {
        if (Test-Path $dir) {
            $oldBackups = Get-ChildItem $dir | 
                Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) }
            
            foreach ($oldBackup in $oldBackups) {
                Write-OptimizationLog "Removing old backup: $($oldBackup.FullName)"
                try {
                    Remove-Item $oldBackup.FullName -Recurse -Force
                }
                catch {
                    Write-OptimizationLog "Error removing old backup: $($oldBackup.FullName) - $_"
                }
            }
        }
    }

    # Analyze and consolidate vendor and library directories
    $vendorDirs = @(
        "$rootPath\vendor",
        "$rootPath\assets\vendor",
        "$rootPath\includes\vendor"
    )

    foreach ($vendorDir in $vendorDirs) {
        if (Test-Path $vendorDir) {
            Write-OptimizationLog "Analyzing vendor directory: $vendorDir"
            
            # Find duplicate or unnecessary library files
            $duplicateLibs = Get-ChildItem $vendorDir -Recurse | 
                Group-Object Name | 
                Where-Object { $_.Count -gt 1 }
            
            foreach ($libGroup in $duplicateLibs) {
                $keepLib = $libGroup.Group | 
                    Sort-Object { $_.LastWriteTime } -Descending | 
                    Select-Object -First 1
                
                $libsToRemove = $libGroup.Group | 
                    Where-Object { $_.FullName -ne $keepLib.FullName }
                
                foreach ($libToRemove in $libsToRemove) {
                    Write-OptimizationLog "Removing duplicate library: $($libToRemove.FullName)"
                    try {
                        $backupPath = $libToRemove.FullName.Replace($rootPath, $backupRootDir)
                        $backupDir = Split-Path $backupPath
                        
                        if (-not (Test-Path $backupDir)) {
                            New-Item -ItemType Directory -Path $backupDir | Out-Null
                        }
                        
                        Copy-Item $libToRemove.FullName $backupPath
                        Remove-Item $libToRemove.FullName -Force
                    }
                    catch {
                        Write-OptimizationLog "Error removing library: $($libToRemove.FullName) - $_"
                    }
                }
            }
        }
    }

    # Analyze and clean temporary and cache directories
    $tempDirs = @(
        "$rootPath\temp",
        "$rootPath\cache",
        "$rootPath\tmp"
    )

    foreach ($tempDir in $tempDirs) {
        if (Test-Path $tempDir) {
            Write-OptimizationLog "Cleaning temporary directory: $tempDir"
            
            # Remove files older than 30 days
            $oldTempFiles = Get-ChildItem $tempDir -Recurse | 
                Where-Object { 
                    $_.LastWriteTime -lt (Get-Date).AddDays(-30) -and 
                    $_.PSIsContainer -eq $false 
                }
            
            foreach ($oldFile in $oldTempFiles) {
                Write-OptimizationLog "Removing old temporary file: $($oldFile.FullName)"
                try {
                    Remove-Item $oldFile.FullName -Force
                }
                catch {
                    Write-OptimizationLog "Error removing temporary file: $($oldFile.FullName) - $_"
                }
            }
        }
    }

    # Generate project structure report
    $structureReport = Get-ChildItem $rootPath -Recurse | 
        Group-Object { $_.Directory.Name } | 
        Select-Object Name, Count | 
        Sort-Object Count -Descending

    Write-OptimizationLog "Project Structure Report:"
    foreach ($dirGroup in $structureReport) {
        Write-OptimizationLog "Directory: $($dirGroup.Name), Files: $($dirGroup.Count)"
    }
}

# Execute project structure optimization
try {
    Optimize-ProjectStructure
    Write-OptimizationLog "Project structure optimization completed successfully"
}
catch {
    Write-OptimizationLog "Critical error during project structure optimization: $_"
}

Write-OptimizationLog "Optimization log saved to $logFile"
Write-OptimizationLog "Backup of removed files stored in $backupRootDir"
