# Comprehensive Dependency and Code Analyzer

$rootPath = "C:\xampp\htdocs\apsdreamhomefinal"
$logFile = Join-Path $rootPath "dependency_code_analysis_log.txt"
$reportFile = Join-Path $rootPath "dependency_code_analysis_report.json"
$backupRootDir = Join-Path $rootPath "backup_code_analysis_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Create backup and log directories
if (-not (Test-Path $backupRootDir)) {
    New-Item -ItemType Directory -Path $backupRootDir | Out-Null
}

# Logging function
function Write-AnalysisLog {
    param([string]$Message, [switch]$Warning, [switch]$IsError)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $prefix = if ($IsError) { "[ERROR]" } elseif ($Warning) { "[WARNING]" } else { "[INFO]" }
    $logEntry = "$prefix [$timestamp] $Message"
    
    try {
        Add-Content -Path $logFile -Value $logEntry -ErrorAction Stop
    }
    catch {
        Write-Host $logEntry
    }
    
    Write-Host $logEntry
}

# Function to analyze dependencies and code
function Get-DependencyAnalysis {
    # Initialize analysis report
    $analysisReport = @{
        UnusedComposerDependencies = @()
        UnusedNpmDependencies = @()
        UnusedCodeFiles = @()
        PotentialPerformanceIssues = @()
        SecurityVulnerabilities = @()
    }

    # Analyze Composer dependencies
    $composerJsonPath = Join-Path $rootPath "composer.json"
    # Composer lock path removed as it was unused
    
    if (Test-Path $composerJsonPath) {
        try {
            $composerJson = Get-Content $composerJsonPath | ConvertFrom-Json
            
            # Scan project files for dependency usage
            $phpFiles = Get-ChildItem -Path $rootPath -Recurse -Include *.php
            
            foreach ($dep in $composerJson.require.PSObject.Properties) {
                $depName = $dep.Name
                $isUsed = $false
                
                # Check if dependency is used in any PHP file
                foreach ($file in $phpFiles) {
                    $content = Get-Content $file.FullName -Raw
                    if ($content -match [regex]::Escape($depName)) {
                        $isUsed = $true
                        break
                    }
                }
                
                if (-not $isUsed) {
                    $analysisReport.UnusedComposerDependencies += $depName
                    Write-AnalysisLog "Potentially unused Composer dependency: $depName" -Warning
                }
            }
        }
        catch {
            Write-AnalysisLog "Error analyzing composer.json" -Error
        }
    }

    # Analyze NPM dependencies
    $packageJsonPath = Join-Path $rootPath "package.json"
    
    if (Test-Path $packageJsonPath) {
        try {
            $packageJson = Get-Content $packageJsonPath | ConvertFrom-Json
            
            # Scan JS files for dependency usage
            $jsFiles = Get-ChildItem -Path $rootPath -Recurse -Include *.js
            
            foreach ($dep in $packageJson.dependencies.PSObject.Properties) {
                $depName = $dep.Name
                $isUsed = $false
                
                # Check if dependency is used in any JS file
                foreach ($file in $jsFiles) {
                    $content = Get-Content $file.FullName -Raw
                    if ($content -match [regex]::Escape($depName)) {
                        $isUsed = $true
                        break
                    }
                }
                
                if (-not $isUsed) {
                    $analysisReport.UnusedNpmDependencies += $depName
                    Write-AnalysisLog "Potentially unused NPM dependency: $depName" -Warning
                }
            }
        }
        catch {
            Write-AnalysisLog "Error analyzing package.json" -Error
        }
    }

    # Analyze unused code files
    $codeExtensions = @("*.php", "*.js", "*.html", "*.css")
    $unusedThresholdDays = 180
    
    foreach ($ext in $codeExtensions) {
        $unusedFiles = Get-ChildItem -Path $rootPath -Recurse -Include $ext | 
            Where-Object { 
                $_.LastWriteTime -lt (Get-Date).AddDays(-$unusedThresholdDays) -and
                $_.Length -lt 1024  # Small files are likely to be unused
            }
        
        foreach ($file in $unusedFiles) {
            $analysisReport.UnusedCodeFiles += $file.FullName
            Write-AnalysisLog "Potentially unused code file: $($file.FullName)" -Warning
        }
    }

    # Performance and security analysis
    $performanceChecks = @{
        LargeFiles = 1024 * 1024 * 5  # 5MB
        ComplexFunctions = 50  # Lines of code
    }

    # Find large files that might impact performance
    $largeFiles = Get-ChildItem -Path $rootPath -Recurse -File | 
        Where-Object { $_.Length -gt $performanceChecks.LargeFiles }
    
    foreach ($file in $largeFiles) {
        $analysisReport.PotentialPerformanceIssues += @{
            File = $file.FullName
            Size = $file.Length
            Type = "Large File"
        }
        Write-AnalysisLog "Large file detected: $($file.FullName), Size: $($file.Length) bytes" -Warning
    }

    # Basic security vulnerability checks
    $securityPatterns = @(
        "eval\s*\(",
        "\bexec\b",
        "shell_exec",
        "passthru",
        "system\s*\(",
        "mysql_.*\(",
        "SELECT.*--",
        "\bpassword\b.*=",
        "md5\s*\("
    )

    $vulnerableFiles = Get-ChildItem -Path $rootPath -Recurse -Include *.php, *.js | 
        Where-Object {
            $content = Get-Content $_.FullName -Raw
            $securityPatterns | Where-Object { $content -match $_ }
        }
    
    foreach ($file in $vulnerableFiles) {
        $analysisReport.SecurityVulnerabilities += $file.FullName
        Write-AnalysisLog "Potential security vulnerability in: $($file.FullName)" -Warning
    }

    # Save analysis report
    $analysisReport | ConvertTo-Json -Depth 10 | Out-File $reportFile
    Write-AnalysisLog "Dependency and code analysis report saved to $reportFile"

    return $analysisReport
}

# Execute dependency and code analysis
try {
    $analysisReport = Get-DependencyAnalysis
    Write-AnalysisLog "Dependency and code analysis completed successfully"
}
catch {
    $errorDetails = $_.Exception.Message
    Write-AnalysisLog "Critical error during dependency and code analysis: $errorDetails" -IsError
}

Write-AnalysisLog "Analysis log saved to $logFile"
