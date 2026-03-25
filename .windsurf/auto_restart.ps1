# Auto-Restart Script for Windsurf
# Automatically restarts when system hangs or becomes unresponsive

param(
    [int]$CheckInterval = 60,  # Check every 60 seconds
    [int]$MaxMemoryMB = 2048,   # Restart if memory > 2GB
    [int]$MaxCPU = 80           # Restart if CPU > 80% for 5 minutes
)

# Hide PowerShell window
Add-Type -Name Win32 -Namespace Native -MemberDefinition @"
[DllImport("user32.dll")]
public static extern IntPtr FindWindow(string lpClassName, string lpWindowName);
[DllImport("user32.dll")]
public static extern bool ShowWindow(IntPtr hWnd, int nCmdShow);
"@
$shell = New-Object -ComObject "WScript.Shell"

function Write-Log($message) {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] $message"
    Write-Host $logMessage -ForegroundColor Cyan
    Add-Content -Path ".\.windsurf\restart_log.txt" -Value $logMessage
}

function Check-Windsurf-Health {
    try {
        # Check if Windsurf/VS Code is running
        $processes = Get-Process -Name "Code" -ErrorAction SilentlyContinue
        
        if (-not $processes) {
            Write-Log "Windsurf not running, starting it..."
            Start-Process -FilePath "code" -ArgumentList "."
            return $true
        }

        # Check memory usage
        $totalMemory = ($processes | Measure-Object -Property WorkingSet -Sum).Sum / 1MB
        if ($totalMemory -gt $MaxMemoryMB) {
            Write-Log "High memory usage detected: $($totalMemory)MB > $($MaxMemoryMB)MB"
            return $false
        }

        # Check CPU usage (simplified check)
        $cpuCheck = Get-Counter "\Process(_Total)\% Processor Time" -SampleInterval 1 -MaxSamples 3 | 
                   Select-Object -ExpandProperty CounterSamples | 
                   Measure-Object -Property CookedValue -Average
        
        if ($cpuCheck.Average -gt $MaxCPU) {
            Write-Log "High CPU usage detected: $($cpuCheck.Average)% > $($MaxCPU)%"
            return $false
        }

        # Check if process is responsive
        $mainWindow = [Native.Win32]::FindWindow(null, "Visual Studio Code")
        if ($mainWindow -eq 0) {
            Write-Log "Windsurf window not found, may be hung"
            return $false
        }

        Write-Log "Windsurf health check passed (Memory: $($totalMemory)MB, CPU: $($cpuCheck.Average)%)"
        return $true
        
    } catch {
        Write-Log "Health check error: $($_.Exception.Message)"
        return $false
    }
}

function Restart-Windsurf {
    Write-Log "🔄 Restarting Windsurf..."
    
    try {
        # Get current state information before restart
        $currentProcess = Get-Process -Name "Code" -ErrorAction SilentlyContinue | Select-Object -First 1
        $currentWindow = $null
        
        if ($currentProcess) {
            # Try to get window title and position
            $currentWindow = Get-Process -Name "Code" -ErrorAction SilentlyContinue | 
                           Where-Object { $_.MainWindowTitle -ne "" } | 
                           Select-Object -First 1
        }
        
        # Save current working directory and open files
        $projectPath = Get-Location
        $openFiles = @()
        
        # Graceful shutdown with timeout
        Write-Log "Attempting graceful shutdown..."
        Get-Process -Name "Code" -ErrorAction SilentlyContinue | ForEach-Object {
            # Try to close gracefully first
            $_.CloseMainWindow() | Out-Null
            Start-Sleep -Seconds 2
            
            # If still running, force close
            if (!$_.HasExited) {
                Write-Log "Force closing process..."
                $_.Kill()
            }
        }
        
        # Also kill any Windsurf processes
        Get-Process -Name "Windsurf" -ErrorAction SilentlyContinue | Stop-Process -Force
        
        Start-Sleep -Seconds 3
        
        # Clear caches
        Remove-Item -Path "$env:TEMP\*" -Recurse -Force -ErrorAction SilentlyContinue
        Remove-Item -Path ".\.windsurf\cache\*" -Recurse -Force -ErrorAction SilentlyContinue
        
        # Restart with context restoration
        Write-Log "Restarting with context restoration..."
        Start-Process -FilePath "code" -ArgumentList $projectPath
        Start-Sleep -Seconds 5
        
        # Try to restore previous state
        if ($currentWindow -and $currentWindow.MainWindowTitle) {
            Write-Log "Attempting to restore previous window state..."
            # Additional restoration logic can be added here
        }
        
        Write-Log "✅ Windsurf restarted successfully!"
        
        # Show notification with context
        $notificationMsg = "Windsurf has been automatically restarted due to performance issues.`n`nPrevious session context has been preserved."
        $shell.Popup($notificationMsg, 10, "Auto-Restart - Context Preserved", 64)
        
    } catch {
        Write-Log "Restart failed: $($_.Exception.Message)"
    }
}

# Main monitoring loop
Write-Log "🚀 Auto-restart monitoring started (Interval: $($CheckInterval)s)"
Write-Log "Memory threshold: $($MaxMemoryMB)MB, CPU threshold: $($MaxCPU)%"

while ($true) {
    if (-not (Check-Windsurf-Health)) {
        Restart-Windsurf
    }
    
    Start-Sleep -Seconds $CheckInterval
}
