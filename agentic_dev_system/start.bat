@echo off
echo ============================================
echo  APS Dream Home - Autonomous Agent Dev System
echo ============================================
echo Started at %date% %time%
echo.
echo This system runs continuously in the background
echo while you sleep. It auto-discovers tasks, fixes
code, runs tests, and reports progress.
echo.
echo Press Ctrl+C to stop.
echo ============================================
echo.
cd /d "%~dp0.."
php agentic_dev_system\scheduler\run_scheduler.php