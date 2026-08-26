@echo off
REM APS Dream Home — WebSocket keep-alive (prod)
REM Keeps Ratchet ws://localhost:8080 alive; restarts on crash.
REM Use with Task Scheduler or nssm. For dev, just run: php websocket_server.php

:loop
echo [%date% %time%] Starting WebSocket server on :8080 ...
php "%~dp0..\websocket_server.php"
echo [%date% %time%] WebSocket exited with code %errorlevel% — restarting in 5s ...
timeout /t 5 /nobreak >nul
goto loop
