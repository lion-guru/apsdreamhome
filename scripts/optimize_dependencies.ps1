# Comprehensive Dependency Optimization Script

$rootPath = "C:\xampp\htdocs\apsdreamhomefinal"
$logFile = Join-Path $rootPath "dependency_optimization_log.txt"
$backupRootDir = Join-Path $rootPath "backup_dependencies_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Create backup and log directories
if (-not (Test-Path $backupRootDir)) {
    New-Item -ItemType Directory -Path $backupRootDir | Out-Null
}

# Logging function
function Write-DependencyLog {
    param([string]$Message, [switch]$Warning, [switch]$Error)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $prefix = if ($Error) { "[ERROR]" } elseif ($Warning) { "[WARNING]" } else { "[INFO]" }
    $logEntry = "$prefix [$timestamp] $Message"
    
    try {
        Add-Content -Path $logFile -Value $logEntry -ErrorAction Stop
    }
    catch {
        Write-Host $logEntry
    }
    
    Write-Host $logEntry
}

# Function to analyze and optimize dependencies
function Optimize-Dependencies {
    # Dependency analysis configuration
    $dependencyConfig = @{
        # Directories to scan for dependencies
        ScanDirectories = @(
            "$rootPath\vendor",
            "$rootPath\node_modules",
            "$rootPath\assets\vendor"
        )
        
        # Known unnecessary or outdated libraries to remove
        UnnecessaryLibraries = @(
            "*test*",
            "*example*",
            "*demo*",
            "*.md",
            "*.txt",
            "*.log",
            "*.yml",
            "*.yaml",
            "*.json"
        )
        
        # Minimum library age for potential removal (in days)
        MinLibraryAgeForRemoval = 180
        
        # Minimum file size to consider for removal (in bytes)
        MinFileSizeForRemoval = 1024
    }

    # Analyze and optimize each dependency directory
    foreach ($depDir in $dependencyConfig.ScanDirectories) {
        if (Test-Path $depDir) {
            Write-DependencyLog "Analyzing dependency directory: $depDir"
            
            # Find unnecessary files
            $unnecessaryFiles = Get-ChildItem -Path $depDir -Recurse -File | 
                Where-Object { 
                    # Match unnecessary library patterns
                    ($dependencyConfig.UnnecessaryLibraries | 
                        Where-Object { $_.Name -like $_ }).Count -gt 0 -or
                    
                    # Check file age
                    $_.LastWriteTime -lt (Get-Date).AddDays(-$dependencyConfig.MinLibraryAgeForRemoval) -or
                    
                    # Check file size
                    $_.Length -lt $dependencyConfig.MinFileSizeForRemoval
                }
            
            # Remove unnecessary files
            foreach ($file in $unnecessaryFiles) {
                try {
                    # Create backup path
                    $backupPath = $file.FullName.Replace($rootPath, $backupRootDir)
                    $backupDir = Split-Path $backupPath
                    
                    if (-not (Test-Path $backupDir)) {
                        New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
                    }
                    
                    # Backup and remove file
                    Copy-Item $file.FullName $backupPath -Force
                    Remove-Item $file.FullName -Force
                    
                    Write-DependencyLog "Removed unnecessary file: $($file.FullName)"
                }
                catch {
                    Write-DependencyLog "Error removing file: $($file.FullName)" -Error
                }
            }
            
            # Find and remove empty directories
            $emptyDirs = Get-ChildItem -Path $depDir -Recurse -Directory | 
                Where-Object { $_.GetFiles().Count -eq 0 -and $_.GetDirectories().Count -eq 0 }
            
            foreach ($emptyDir in $emptyDirs) {
                try {
                    Remove-Item $emptyDir.FullName -Force
                    Write-DependencyLog "Removed empty directory: $($emptyDir.FullName)"
                }
                catch {
                    Write-DependencyLog "Error removing empty directory: $($emptyDir.FullName)" -Error
                }
            }
        }
    }

    # Analyze composer dependencies
    $composerJsonPath = Join-Path $rootPath "composer.json"
    $composerLockPath = Join-Path $rootPath "composer.lock"
    
    if (Test-Path $composerJsonPath) {
        try {
            $composerJson = Get-Content $composerJsonPath | ConvertFrom-Json
            
            # Check for outdated or unused dependencies
            $unusedDeps = $composerJson.require.PSObject.Properties | 
                Where-Object { 
                    # Add logic to detect potentially unused dependencies
                    # This is a simplified example and might need more sophisticated detection
                    $_.Name -notmatch "php|ext-|symfony|laravel|doctrine"
                }
            
            foreach ($dep in $unusedDeps) {
                Write-DependencyLog "Potentially unused Composer dependency: $($dep.Name)" -Warning
            }
        }
        catch {
            Write-DependencyLog "Error analyzing composer.json" -Error
        }
    }

    # Analyze npm dependencies
    $packageJsonPath = Join-Path $rootPath "package.json"
    
    if (Test-Path $packageJsonPath) {
        try {
            $packageJson = Get-Content $packageJsonPath | ConvertFrom-Json
            
            # Check for outdated or unused npm dependencies
            $unusedNpmDeps = $packageJson.dependencies.PSObject.Properties | 
                Where-Object { 
                    # Add logic to detect potentially unused dependencies
                    # This is a simplified example and might need more sophisticated detection
                    $_.Name -notmatch "jquery|bootstrap|vue|react|angular"
                }
            
            foreach ($dep in $unusedNpmDeps) {
                Write-DependencyLog "Potentially unused NPM dependency: $($dep.Name)" -Warning
            }
        }
        catch {
            Write-DependencyLog "Error analyzing package.json" -Error
        }
    }

    # Generate dependency report
    $dependencyReport = @{
        VendorDirectories = $dependencyConfig.ScanDirectories
        RemovedFiles = $unnecessaryFiles.Count
        EmptyDirectoriesRemoved = $emptyDirs.Count
        PotentiallyUnusedComposerDeps = $unusedDeps.Count
        PotentiallyUnusedNpmDeps = $unusedNpmDeps.Count
    }

    Write-DependencyLog "Dependency Optimization Report:"
    $dependencyReport.GetEnumerator() | ForEach-Object {
        Write-DependencyLog "$($_.Key): $($_.Value)"
    }
}

# Execute dependency optimization
try {
    Optimize-Dependencies
    Write-DependencyLog "Dependency optimization completed successfully"
}
catch {
    Write-DependencyLog "Critical error during dependency optimization: $_" -Error
}

Write-DependencyLog "Optimization log saved to $logFile"
Write-DependencyLog "Backup of removed files stored in $backupRootDir"
