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

for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set "dt=%%I"
set "YYYY=%dt:~0,4%"
set "MM=%dt:~4,2%"
set "DD=%dt:~6,2%"
set "HH=%dt:~8,2%"
set "MIN=%dt:~10,2%"
set "SEC=%dt:~12,2%"

set "TIMESTAMP=%YYYY%-%MM%-%DD%_%HH%-%MIN%-%SEC%"
set "DUMP_FILE=%BACKUP_DIR%\%DB_NAME%_%TIMESTAMP%.sql"

echo [%DATE% %TIME%] Starting MySQL dump for %DB_NAME%...
"%MYSQL_BIN%\mysqldump.exe" -u %DB_USER% --single-transaction --quick --routines --triggers %DB_NAME% > "%DUMP_FILE%"

if exist "%DUMP_FILE%" (
    echo [%DATE% %TIME%] Backup successful: %DUMP_FILE%
) else (
    echo [%DATE% %TIME%] ERROR: Backup failed.
)

echo [%DATE% %TIME%] Done.
