$Host.UI.RawUI.WindowTitle = "APS Dream Home - Agentic Dev System"
Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  APS Dream Home - Autonomous Agentic Dev System" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  Started: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Yellow
Write-Host "  Mode: Continuous (press Ctrl+C to stop)" -ForegroundColor Yellow
Write-Host ""
Write-Host "  Agents Active:" -ForegroundColor Green
Write-Host "    Backend Engineer    - PHP/MVC backend & SQL" -ForegroundColor White
Write-Host "    Frontend Engineer   - Flutter UI/UX" -ForegroundColor White
Write-Host "    QA Engineer         - E2E tests & syntax checks" -ForegroundColor White
Write-Host "    Security Engineer   - SQL injection & auth audits" -ForegroundColor White
Write-Host "    DevOps Engineer     - Builds, APK, deployment" -ForegroundColor White
Write-Host "    Architecture Analyst - Codebase analysis" -ForegroundColor White
Write-Host "    Documentation Eng.  - AGENTS.md & lessons" -ForegroundColor White
Write-Host ""
Write-Host "  AI Backend: Ollama (Qwen 2.5 7B local)" -ForegroundColor Magenta
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

cd $PSScriptRoot\..
php agentic_dev_system\scheduler\run_scheduler.php