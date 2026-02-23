$ErrorActionPreference = 'Stop'

function Invoke-ProjectMaintenance {
    param($ProjectPath)
    
    # Code quality checks
    Get-ChildItem -Path $ProjectPath -Filter *.php -Recurse | ForEach-Object {
        php -l $_.FullName
    }
    
    # Security audit
    & "$env:APPDATA\Composer\vendor\bin\phpcs" --standard=PSR12 --ignore=vendor/* $ProjectPath
    
    # Database backup
    function Invoke-SafeCommand {
        param([scriptblock]$Command)
        try {
            & $Command
        } catch {
            Write-Host "[ERROR] $($_.Exception.Message)"
            exit 1
        }
    }
    Invoke-SafeCommand { php "$ProjectPath\database\backup_demo_data.php" }
    
    # Database optimization
    Invoke-SafeCommand { php "$ProjectPath\database\optimize_database.php" }
    
    # Clear temp files
    if (Test-Path "$ProjectPath\tmp") {
        Remove-Item "$ProjectPath\tmp\*" -Force -Recurse
    } else {
        Write-Host "Info: tmp directory not found"
    }
}

Invoke-ProjectMaintenance -ProjectPath "c:\xampp\htdocs\apsdreamhome"
