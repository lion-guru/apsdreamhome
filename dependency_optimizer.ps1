# Comprehensive Dependency Optimization Script

$rootPath = "C:\xampp\htdocs\apsdreamhomefinal"
$logFile = Join-Path $rootPath "dependency_optimization_log.txt"
$backupRootDir = Join-Path $rootPath "backup_dependencies_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Logging function
function Write-DependencyLog {
    param([string]$Message, [switch]$Warning, [switch]$ErrorLog)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $prefix = if ($ErrorLog) { "[ERROR]" } elseif ($Warning) { "[WARNING]" } else { "[INFO]" }
    $logEntry = "$prefix [$timestamp] $Message"
    
    try {
        Add-Content -Path $logFile -Value $logEntry -ErrorAction Stop
    }
    catch {
        Write-Host $logEntry
    }
    
    Write-Host $logEntry
}

# Dependency Analysis Configuration
$dependencyConfig = @{
    # Unused or deprecated PHP extensions
    UnusedPhpExtensions = @(
        "ext-mysqli",  # If PDO is used instead
        "ext-json",    # If json_encode/decode is used natively
        "ext-curl",    # If file_get_contents or alternative methods are used
        "ext-mbstring" # If not explicitly used for multibyte string handling
    )

    # Large or unnecessary Composer packages
    LargeComposerPackages = @(
        "phpunit/phpunit",           # Testing framework, not needed in production
        "mockery/mockery",           # Mocking library for testing
        "symfony/var-dumper",        # Debug tool
        "phpstan/phpstan",           # Static analysis tool
        "squizlabs/php_codesniffer"  # Code style checker
    )

    # Potentially redundant NPM packages
    RedundantNpmPackages = @(
        "jquery",          # If using a more modern framework
        "bootstrap",       # If using a different CSS framework
        "moment",          # If using native Date or alternative libraries
        "lodash",          # If using native JS methods or alternative libraries
        "popper.js"        # If using alternative positioning libraries
    )

    # Minimum package size threshold (in MB)
    MinPackageSizeThreshold = 5
}

# Function to analyze Composer dependencies
function Get-ComposerDependencyAnalysis {
    param([string]$ProjectPath)

    $composerJsonPath = Join-Path $ProjectPath "composer.json"
    $composerLockPath = Join-Path $ProjectPath "composer.lock"

    if (-not (Test-Path $composerJsonPath) -or -not (Test-Path $composerLockPath)) {
        Write-DependencyLog "Composer files not found in $ProjectPath" -Warning
        return $null
    }

    try {
        $composerJson = Get-Content $composerJsonPath | ConvertFrom-Json
        $composerLock = Get-Content $composerLockPath | ConvertFrom-Json

        $unusedDependencies = @()
        $largeDependencies = @()

        # Check for unused PHP extensions
        $dependencyConfig.UnusedPhpExtensions | ForEach-Object {
            $extension = $_
            if ($composerJson.require -and $composerJson.require.$extension) {
                $unusedDependencies += $extension
            }
        }

        # Check for large or unnecessary packages
        $composerLock.packages | ForEach-Object {
            $package = $_
            $packageSize = (Get-ChildItem -Path (Join-Path $ProjectPath "vendor\$($package.name)") -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB

            # Check for large packages
            if ($packageSize -gt $dependencyConfig.MinPackageSizeThreshold) {
                $largeDependencies += @{
                    Name = $package.name
                    Version = $package.version
                    Size = [math]::Round($packageSize, 2)
                }
            }

            # Check for potentially unnecessary packages
            $dependencyConfig.LargeComposerPackages | ForEach-Object {
                if ($package.name -like "*$_*") {
                    $unusedDependencies += $package.name
                }
            }
        }

        return @{
            UnusedDependencies = $unusedDependencies
            LargeDependencies = $largeDependencies
        }
    }
    catch {
        Write-DependencyLog "Error analyzing Composer dependencies: $_" -ErrorLog
        return $null
    }
}

# Function to analyze NPM dependencies
function Get-NpmDependencyAnalysis {
    param([string]$ProjectPath)

    $packageJsonPath = Join-Path $ProjectPath "package.json"
    $packageLockPath = Join-Path $ProjectPath "package-lock.json"

    if (-not (Test-Path $packageJsonPath) -or -not (Test-Path $packageLockPath)) {
        Write-DependencyLog "NPM files not found in $ProjectPath" -Warning
        return $null
    }

    try {
        $packageJson = Get-Content $packageJsonPath | ConvertFrom-Json
        $packageLock = Get-Content $packageLockPath | ConvertFrom-Json

        $unusedNpmPackages = @()
        $largeNpmPackages = @()

        # Check for redundant NPM packages
        $dependencyConfig.RedundantNpmPackages | ForEach-Object {
            $package = $_
            if ($packageJson.dependencies -and $packageJson.dependencies.$package) {
                $unusedNpmPackages += $package
            }
        }

        # Check for large NPM packages
        if ($packageLock.packages) {
            $packageLock.packages.PSObject.Properties | ForEach-Object {
                $packageName = $_.Name
                $package = $_.Value

                try {
                    $packagePath = Join-Path $ProjectPath "node_modules\$packageName"
                    if (Test-Path $packagePath) {
                        $packageSize = (Get-ChildItem -Path $packagePath -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB

                        if ($packageSize -gt $dependencyConfig.MinPackageSizeThreshold) {
                            $largeNpmPackages += @{
                                Name = $packageName
                                Version = $package.version
                                Size = [math]::Round($packageSize, 2)
                            }
                        }
                    }
                }
                catch {
                    Write-DependencyLog "Error processing NPM package ${packageName}: $_" -Warning
                }
            }
        }

        return @{
            UnusedPackages = $unusedNpmPackages
            LargePackages = $largeNpmPackages
        }
    }
    catch {
        Write-DependencyLog "Error analyzing NPM dependencies: $_" -ErrorLog
        return $null
    }
}

# Function to remove unnecessary dependencies
function Remove-UnnecessaryDependencies {
    param(
        [string]$ProjectPath,
        [string[]]$ComposerDependenciesToRemove,
        [string[]]$NpmDependenciesToRemove
    )

    # Backup current dependencies
    $backupPath = $ProjectPath.Replace($rootPath, $backupRootDir)
    if (-not (Test-Path $backupPath)) {
        New-Item -ItemType Directory -Path $backupPath -Force | Out-Null
    }

    # Backup Composer files
    $composerJsonPath = Join-Path $ProjectPath "composer.json"
    $composerLockPath = Join-Path $ProjectPath "composer.lock"
    if (Test-Path $composerJsonPath) {
        Copy-Item $composerJsonPath (Join-Path $backupPath "composer.json")
    }
    if (Test-Path $composerLockPath) {
        Copy-Item $composerLockPath (Join-Path $backupPath "composer.lock")
    }

    # Remove Composer dependencies
    if ($ComposerDependenciesToRemove) {
        try {
            $ComposerDependenciesToRemove | ForEach-Object {
                $package = $_
                Start-Process "composer" -ArgumentList "remove", $package -WorkingDirectory $ProjectPath -Wait
                Write-DependencyLog "Removed Composer package: $package"
            }
        }
        catch {
            Write-DependencyLog "Error removing Composer dependencies: $_" -ErrorLog
        }
    }

    # Backup NPM files
    $packageJsonPath = Join-Path $ProjectPath "package.json"
    $packageLockPath = Join-Path $ProjectPath "package-lock.json"
    if (Test-Path $packageJsonPath) {
        Copy-Item $packageJsonPath (Join-Path $backupPath "package.json")
    }
    if (Test-Path $packageLockPath) {
        Copy-Item $packageLockPath (Join-Path $backupPath "package-lock.json")
    }

    # Remove NPM dependencies
    if ($NpmDependenciesToRemove) {
        try {
            $NpmDependenciesToRemove | ForEach-Object {
                $package = $_
                Start-Process "npm" -ArgumentList "uninstall", $package -WorkingDirectory $ProjectPath -Wait
                Write-DependencyLog "Removed NPM package: $package"
            }
        }
        catch {
            Write-DependencyLog "Error removing NPM dependencies: $_" -ErrorLog
        }
    }
}

# Main optimization process
try {
    Write-DependencyLog "Starting comprehensive dependency optimization"

    # Analyze Composer dependencies
    $composerResults = Get-ComposerDependencyAnalysis -ProjectPath $rootPath
    if ($composerResults) {
        Write-DependencyLog "Composer Dependency Analysis:"
        Write-DependencyLog "Unused Dependencies: $($composerResults.UnusedDependencies -join ', ')"
        Write-DependencyLog "Large Dependencies:"
        $composerResults.LargeDependencies | ForEach-Object {
            Write-DependencyLog "- $($_.Name) (Version: $($_.Version), Size: $($_.Size) MB)"
        }
    }

    # Analyze NPM dependencies
    $npmResults = Get-NpmDependencyAnalysis -ProjectPath $rootPath
    if ($npmResults) {
        Write-DependencyLog "NPM Dependency Analysis:"
        Write-DependencyLog "Unused Packages: $($npmResults.UnusedPackages -join ', ')"
        Write-DependencyLog "Large Packages:"
        $npmResults.LargePackages | ForEach-Object {
            Write-DependencyLog "- $($_.Name) (Version: $($_.Version), Size: $($_.Size) MB)"
        }
    }

    # Remove unnecessary dependencies
    Remove-UnnecessaryDependencies `
        -ProjectPath $rootPath `
        -ComposerDependenciesToRemove $composerResults.UnusedDependencies `
        -NpmDependenciesToRemove $npmResults.UnusedPackages

    Write-DependencyLog "Dependency optimization completed successfully"
    Write-DependencyLog "Dependency log saved to $logFile"
    Write-DependencyLog "Backup of modified files stored in $backupRootDir"
}
catch {
    Write-DependencyLog "Critical error during dependency optimization: $_" -ErrorLog
}
