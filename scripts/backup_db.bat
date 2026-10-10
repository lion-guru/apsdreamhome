@echo off
REM ==============================================================================
REM APS DREAM HOME — WINDOWS DATABASE BACKUP SCRIPT
REM ==============================================================================

setlocal enabledelayedexpansion

set "BACKUP_DIR=C:\xampp\htdocs\apsdreamhome\backups\db"
set "MYSQL_BIN=C:\xampp\mysql\bin"
set "DB_NAME=apsdreamhome"
set "DB_USER=root"

if not exist "%BACKUP_DIR%" (
    mkdir "%BACKUP_DIR%"
)

REM Timestamp via PowerShell (wmic is removed on newer Windows builds)
for /f %%I in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd_HH-mm-ss"') do set "TIMESTAMP=%%I"
if "%TIMESTAMP%"=="" set "TIMESTAMP=manual-%RANDOM%"

set "DUMP_FILE=%BACKUP_DIR%\%DB_NAME%_%TIMESTAMP%.sql"

echo [%DATE% %TIME%] Starting MySQL dump for %DB_NAME%...
"%MYSQL_BIN%\mysqldump.exe" -u %DB_USER% --single-transaction --quick --routines --triggers %DB_NAME% > "%DUMP_FILE%"

if exist "%DUMP_FILE%" (
    echo [%DATE% %TIME%] Backup successful: %DUMP_FILE%
) else (
    echo [%DATE% %TIME%] ERROR: Backup failed.
)

echo [%DATE% %TIME%] Done.
