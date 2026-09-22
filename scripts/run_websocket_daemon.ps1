<#
.SYNOPSIS
    APS Dream Home WebSocket Server Manager
.DESCRIPTION
    Manages WebSocket servers (port 8080) and Broadcast HTTP server (port 8081)
    with auto-restart, logging, and health monitoring.
.NOTES
    Requires: PHP 8.1+, Ratchet WebSocket library, NSSM (for service install)
#>

param(
    [Parameter(Mandatory=$true, Position=0)]
    [ValidateSet('start','stop','restart','status','monitor','install-service','uninstall-service')]
    [string]$Command,

    [string]$ProjectRoot = "C:\xampp\htdocs\apsdreamhome",
    [string]$PhpExe = "C:\xampp\php\php.exe",
    [int]$WsPort = 8080,
    [int]$BcastPort = 8081,
    [int]$MaxRestarts = 10,
    [int]$RestartDelaySec = 5
)

$ErrorActionPreference = "Stop"

# Paths
$WsServer = Join-Path $ProjectRoot "websocket_server.php"
$BcastServer = Join-Path $ProjectRoot "websocket_broadcast_server.php"
$LogDir = Join-Path $ProjectRoot "logs\websocket"
$PidDir = Join-Path $ProjectRoot "runtime\websocket"

# Ensure directories
$null = New-Item -ItemType Directory -Force -Path $LogDir, $PidDir

$WsLog = Join-Path $LogDir "websocket_server.log"
$BcastLog = Join-Path $LogDir "websocket_broadcast.log"
$WsPidFile = Join-Path $PidDir "websocket_server.pid"
$BcastPidFile = Join-Path $PidDir "websocket_broadcast.pid"
$WsStdoutLog = Join-Path $LogDir "websocket_server_stdout.log"
$WsStderrLog = Join-Path $LogDir "websocket_server_stderr.log"
$BcastStdoutLog = Join-Path $LogDir "websocket_broadcast_stdout.log"
$BcastStderrLog = Join-Path $LogDir "websocket_broadcast_stderr.log"

function Write-Log {
    param([string]$Message, [string]$Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Write-Host "[$timestamp] [$Level] $Message"
}

function Get-PhpProcess {
    param([string]$ScriptName)
    $processes = Get-Process php -ErrorAction SilentlyContinue
    foreach ($p in $processes) {
        try {
            $cmd = $p.GetProcessCommandLine()
            if ($cmd -match [regex]::Escape($ScriptName)) {
                return $p
            }
        } catch { }
    }
    return $null
}

function Start-Server {
    param(
        [string]$ScriptPath,
        [int]$Port,
        [string]$StdoutLog,
        [string]$StderrLog,
        [string]$PidFile,
        [string]$Name
    )

    if (Test-Path $PidFile) {
        $serverPid = Get-Content $PidFile -Raw -ErrorAction SilentlyContinue
        if ($serverPid -and (Get-Process -Id $serverPid -ErrorAction SilentlyContinue)) {
            Write-Log "$Name already running (PID: $serverPid)"
            return $true
        } else {
            Write-Log "Stale PID file for $Name, removing..."
            Remove-Item $PidFile -Force -ErrorAction SilentlyContinue
        }
    }

    Write-Log "Starting $Name on port $Port..."
    Set-Location $ProjectRoot

    # Start process with output redirection (separate files for stdout/stderr)
    $process = Start-Process -FilePath $PhpExe -ArgumentList "`"$ScriptPath`"" `
        -WorkingDirectory $ProjectRoot `
        -RedirectStandardOutput $StdoutLog `
        -RedirectStandardError $StderrLog `
        -WindowStyle Hidden `
        -PassThru

    # Wait a bit for startup
    Start-Sleep -Seconds 2

    # Verify process is alive
    if ($process -and !$process.HasExited) {
        $process.Id | Out-File -FilePath $PidFile -Encoding UTF8
        Write-Log "$Name started with PID $($process.Id)"
        return $true
    } else {
        Write-Log "FAILED to start $Name. Check log: $LogFile" "ERROR"
        return $false
    }
}

function Stop-Server {
    param([string]$PidFile, [string]$Name)

    if (Test-Path $PidFile) {
        $serverPid = Get-Content $PidFile -Raw -ErrorAction SilentlyContinue
        if ($serverPid -and (Get-Process -Id $serverPid -ErrorAction SilentlyContinue)) {
            Write-Log "Stopping $Name (PID: $serverPid)..."
            Stop-Process -Id $serverPid -Force -ErrorAction SilentlyContinue
        }
        Remove-Item $PidFile -Force -ErrorAction SilentlyContinue
        Write-Log "$Name stopped."
    } else {
        Write-Log "$Name not running (no PID file)."
    }
}

function Check-Health {
    param([int]$Port, [string]$Name)

    try {
        $tcp = New-Object System.Net.Sockets.TcpClient
        $result = $tcp.BeginConnect("127.0.0.1", $Port, $null, $null)
        $success = $result.AsyncWaitHandle.WaitOne(2000)
        $tcp.EndConnect($result)
        $tcp.Close()
        if ($success) {
            Write-Log "$Name (port $Port): HEALTHY" "OK"
            return $true
        }
    } catch { }
    Write-Log "$Name (port $Port): UNREACHABLE" "WARN"
    return $false
}

switch ($Command) {
    'start' {
        Write-Log "=== Starting APS Dream Home WebSocket Services ==="
        Start-Server $WsServer $WsPort $WsStdoutLog $WsStderrLog $WsPidFile "WebSocket Server"
        Start-Server $BcastServer $BcastPort $BcastStdoutLog $BcastStderrLog $BcastPidFile "Broadcast HTTP Server"
        Write-Log "All services started."
    }
    'stop' {
        Write-Log "=== Stopping APS Dream Home WebSocket Services ==="
        Stop-Server $WsPidFile "WebSocket Server"
        Stop-Server $BcastPidFile "Broadcast HTTP Server"
    }
    'restart' {
        Write-Log "=== Restarting APS Dream Home WebSocket Services ==="
        Stop-Server $WsPidFile "WebSocket Server"
        Stop-Server $BcastPidFile "Broadcast HTTP Server"
        Start-Sleep -Seconds 3
        Start-Server $WsServer $WsPort $WsStdoutLog $WsStderrLog $WsPidFile "WebSocket Server"
        Start-Server $BcastServer $BcastPort $BcastStdoutLog $BcastStderrLog $BcastPidFile "Broadcast HTTP Server"
    }
    'status' {
        Write-Log "=== APS Dream Home WebSocket Services Status ==="
        if (Test-Path $WsPidFile) {
            $serverPid = Get-Content $WsPidFile -Raw
            if (Get-Process -Id $serverPid -ErrorAction SilentlyContinue) {
                Check-Health $WsPort "WebSocket Server"
            } else { Write-Log "WebSocket Server: STOPPED (stale PID)" "WARN" }
        } else { Write-Log "WebSocket Server: STOPPED" }

        if (Test-Path $BcastPidFile) {
            $serverPid = Get-Content $BcastPidFile -Raw
            if (Get-Process -Id $serverPid -ErrorAction SilentlyContinue) {
                Check-Health $BcastPort "Broadcast HTTP Server"
            } else { Write-Log "Broadcast HTTP Server: STOPPED (stale PID)" "WARN" }
        } else { Write-Log "Broadcast HTTP Server: STOPPED" }
    }
    'monitor' {
        Write-Log "=== Starting Health Monitor (Ctrl+C to stop) ==="
        $restartCounts = @{ "WebSocket" = 0; "Broadcast" = 0 }
        while ($true) {
            # Check WebSocket
            if (-not (Check-Health $WsPort "WebSocket Server")) {
                $restartCounts["WebSocket"]++
                if ($restartCounts["WebSocket"] -le $MaxRestarts) {
                    Write-Log "Auto-restarting WebSocket Server (attempt $($restartCounts["WebSocket"])/$MaxRestarts)..." "WARN"
                    Stop-Server $WsPidFile "WebSocket Server"
                    Start-Sleep -Seconds $RestartDelaySec
                    Start-Server $WsServer $WsPort $WsStdoutLog $WsStderrLog $WsPidFile "WebSocket Server"
                } else {
                    Write-Log "Max restarts reached for WebSocket Server. Manual intervention required." "ERROR"
                }
            } else {
                $restartCounts["WebSocket"] = 0
            }

            # Check Broadcast
            if (-not (Check-Health $BcastPort "Broadcast HTTP Server")) {
                $restartCounts["Broadcast"]++
                if ($restartCounts["Broadcast"] -le $MaxRestarts) {
                    Write-Log "Auto-restarting Broadcast HTTP Server (attempt $($restartCounts["Broadcast"])/$MaxRestarts)..." "WARN"
                    Stop-Server $BcastPidFile "Broadcast HTTP Server"
                    Start-Sleep -Seconds $RestartDelaySec
                    Start-Server $BcastServer $BcastPort $BcastStdoutLog $BcastStderrLog $BcastPidFile "Broadcast HTTP Server"
                } else {
                    Write-Log "Max restarts reached for Broadcast HTTP Server. Manual intervention required." "ERROR"
                }
            } else {
                $restartCounts["Broadcast"] = 0
            }

            Start-Sleep -Seconds 30
        }
    }
    'install-service' {
        Write-Log "Installing Windows services via NSSM..."
        if (-not (Get-Command nssm -ErrorAction SilentlyContinue)) {
            Write-Log "ERROR: NSSM not found. Install from https://nssm.cc/download" "ERROR"
            exit 1
        }

        # WebSocket Server service
        nssm install "APSDreamHome-WebSocket" $PhpExe "`"$WsServer`""
        nssm set "APSDreamHome-WebSocket" AppDirectory $ProjectRoot
        nssm set "APSDreamHome-WebSocket" AppStdout (Join-Path $LogDir "websocket_server_service.log")
        nssm set "APSDreamHome-WebSocket" AppStderr (Join-Path $LogDir "websocket_server_service_error.log")
        nssm set "APSDreamHome-WebSocket" AppEnvironmentExtra "WS_PORT=$WsPort"
        nssm set "APSDreamHome-WebSocket" Start SERVICE_AUTO_START
        nssm set "APSDreamHome-WebSocket" Description "APS Dream Home WebSocket Server (port $WsPort)"
        nssm set "APSDreamHome-WebSocket" AppExit Default Restart
        nssm set "APSDreamHome-WebSocket" AppThrottle 5000

        # Broadcast service
        nssm install "APSDreamHome-WebSocket-Broadcast" $PhpExe "`"$BcastServer`""
        nssm set "APSDreamHome-WebSocket-Broadcast" AppDirectory $ProjectRoot
        nssm set "APSDreamHome-WebSocket-Broadcast" AppStdout (Join-Path $LogDir "websocket_broadcast_service.log")
        nssm set "APSDreamHome-WebSocket-Broadcast" AppStderr (Join-Path $LogDir "websocket_broadcast_service_error.log")
        nssm set "APSDreamHome-WebSocket-Broadcast" AppEnvironmentExtra "WS_HTTP_PORT=$BcastPort"
        nssm set "APSDreamHome-WebSocket-Broadcast" Start SERVICE_AUTO_START
        nssm set "APSDreamHome-WebSocket-Broadcast" Description "APS Dream Home WebSocket Broadcast HTTP Server (port $BcastPort)"
        nssm set "APSDreamHome-WebSocket-Broadcast" AppExit Default Restart
        nssm set "APSDreamHome-WebSocket-Broadcast" AppThrottle 5000

        Write-Log "Services installed. Start with: nssm start APSDreamHome-WebSocket && nssm start APSDreamHome-WebSocket-Broadcast"
    }
    'uninstall-service' {
        Write-Log "Uninstalling Windows services..."
        nssm stop "APSDreamHome-WebSocket" 2>$null
        nssm remove "APSDreamHome-WebSocket" confirm 2>$null
        nssm stop "APSDreamHome-WebSocket-Broadcast" 2>$null
        nssm remove "APSDreamHome-WebSocket-Broadcast" confirm 2>$null
        Write-Log "Services uninstalled."
    }
    default {
        Write-Log "Unknown command: $Command"
        exit 1
    }
}