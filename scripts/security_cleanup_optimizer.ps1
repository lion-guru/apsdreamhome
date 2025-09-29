# Comprehensive Security and Cleanup Optimizer

$rootPath = "C:\xampp\htdocs\apsdreamhomefinal"
$logFile = Join-Path $rootPath "security_cleanup_log.txt"
$backupRootDir = Join-Path $rootPath "backup_security_cleanup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Create backup and log directories
if (-not (Test-Path $backupRootDir)) {
    New-Item -ItemType Directory -Path $backupRootDir | Out-Null
}

# Logging function
function Write-SecurityLog {
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

# Security Vulnerability Patterns
$securityVulnerabilityPatterns = @{
    Dangerous = @(
        "eval\s*\(",
        "\\bexec\\b",
        "shell_exec",
        "passthru",
        "system\s*\(",
        "mysql_.*\(",
        "SELECT.*--",
        "\\bpassword\\b.*=",
        "md5\s*\(",
        "unserialize\s*\(",
        "\\$_GET\[",
        "\\$_POST\[",
        "\\$_REQUEST\["
    )
    Deprecated = @(
        "mysql_.*\(",
        "ereg_",
        "split\(",
        "create_function\s*\("
    )
    SensitiveInfo = @(
        "DB_PASSWORD",
        "API_KEY",
        "SECRET_KEY",
        "access_token"
    )
}

# Function to analyze and remediate security vulnerabilities
function Optimize-Security {
    param(
        [string[]]$FileExtensions = @("*.php", "*.js", "*.inc"),
        [int]$MaxFileSize = 1MB  # Limit file size to process
    )

    $vulnerableFiles = @()
    $remediatedFiles = @()
    $skippedFiles = @()

    # Find files with potential security issues
    Get-ChildItem -Path $rootPath -Recurse -Include $FileExtensions | 
    Where-Object { $_.Length -le $MaxFileSize } | 
    ForEach-Object {
        $file = $_
        try {
            # Use try-catch to handle permission and access errors
            $content = Get-Content $file.FullName -Raw -ErrorAction Stop

            # Check for dangerous patterns
            $dangerousMatches = $securityVulnerabilityPatterns.Dangerous | 
                Where-Object { $content -match $_ }
            
            $deprecatedMatches = $securityVulnerabilityPatterns.Deprecated | 
                Where-Object { $content -match $_ }
            
            $sensitiveMatches = $securityVulnerabilityPatterns.SensitiveInfo | 
                Where-Object { $content -match $_ }

            if ($dangerousMatches -or $deprecatedMatches -or $sensitiveMatches) {
                $vulnerableFiles += @{
                    Path = $file.FullName
                    DangerousPatterns = $dangerousMatches
                    DeprecatedPatterns = $deprecatedMatches
                    SensitivePatterns = $sensitiveMatches
                }

                # Basic remediation
                $remediatedContent = $content
                
                # Remove or sanitize dangerous patterns
                $dangerousMatches | ForEach-Object {
                    $remediatedContent = $remediatedContent -replace $_, "// SECURITY: Removed potentially dangerous code"
                }

                # Replace deprecated functions
                $deprecatedMatches | ForEach-Object {
                    $remediatedContent = $remediatedContent -replace $_, "// SECURITY: Replaced deprecated function"
                }

                # Mask sensitive information
                $sensitiveMatches | ForEach-Object {
                    $remediatedContent = $remediatedContent -replace $_, "// SECURITY: Sensitive information removed"
                }

                # Backup original file
                $backupPath = $file.FullName.Replace($rootPath, $backupRootDir)
                $backupDir = Split-Path $backupPath
                if (-not (Test-Path $backupDir)) {
                    New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
                }
                Copy-Item $file.FullName $backupPath -ErrorAction Continue

                # Write remediated content
                Set-Content -Path $file.FullName -Value $remediatedContent -ErrorAction Continue

                $remediatedFiles += $file.FullName
                Write-SecurityLog "Remediated security vulnerabilities in: $($file.FullName)"
            }
        }
        catch {
            # Log skipped files
            $skippedFiles += $file.FullName
            Write-SecurityLog "Skipped file due to access error: $($file.FullName)" -Warning
        }
    }

    return @{
        VulnerableFiles = $vulnerableFiles
        RemediatedFiles = $remediatedFiles
        SkippedFiles = $skippedFiles
    }
}

# Function to clean up large files and logs
function Clean-LargeFiles {
    param(
        [long]$MaxFileSize = 10MB,  # 10MB threshold
        [int]$MaxLogAge = 30  # Days
    )

    $largeLogs = Get-ChildItem -Path $rootPath -Recurse | 
        Where-Object { 
            $_.Length -gt $MaxFileSize -and 
            $_.LastWriteTime -lt (Get-Date).AddDays(-$MaxLogAge)
        }

    foreach ($logFile in $largeLogs) {
        try {
            # Backup large log before truncating
            $backupPath = $logFile.FullName.Replace($rootPath, $backupRootDir)
            $backupDir = Split-Path $backupPath
            
            if (-not (Test-Path $backupDir)) {
                New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
            }
            
            Copy-Item $logFile.FullName $backupPath
            
            # Truncate log file
            $truncatedContent = Get-Content $logFile.FullName -Tail 1000
            Set-Content -Path $logFile.FullName -Value $truncatedContent
            
            Write-SecurityLog "Truncated large log file: $($logFile.FullName)"
        }
        catch {
            Write-SecurityLog "Error processing large log file: $($logFile.FullName)" -Error
        }
    }
}

# Execute security optimization and cleanup
try {
    Write-SecurityLog "Starting comprehensive security and cleanup process"

    # Optimize security
    $securityResults = Optimize-Security
    Write-SecurityLog "Security Optimization Summary:"
    Write-SecurityLog "Vulnerable Files: $($securityResults.VulnerableFiles.Count)"
    Write-SecurityLog "Remediated Files: $($securityResults.RemediatedFiles.Count)"

    # Clean large files
    Clean-LargeFiles
    
    Write-SecurityLog "Security and cleanup process completed successfully"
}
catch {
    Write-SecurityLog "Critical error during security optimization: $_" -Error
}

Write-SecurityLog "Security log saved to $logFile"
Write-SecurityLog "Backup of modified files stored in $backupRootDir"
