param([string]$OutDir="C:\xampp\htdocs\apsdreamhome\logs")
$ts=Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$log="$OutDir/nightly_$ts.log"
New-Item -ItemType Directory -Force -Path $OutDir | Out-Null
"=== Nightly $ts ===" | Tee-Object $log
"php -l (8 layouts) ..." | Tee-Object $log -Append
php -l app/views/layouts/base.php 2>&1 | Tee-Object $log -Append
php -l app/views/layouts/admin.php 2>&1 | Tee-Object $log -Append
"--- E2E 360 ---" | Tee-Object $log -Append
node testing/visual_tests/E2E_MASTER_TEST.mjs 2>&1 | Tee-Object $log -Append
"--- Visual 14 ---" | Tee-Object $log -Append
node testing/visual_tests/VISUAL_SMOKE.mjs 2>&1 | Tee-Object $log -Append
"--- Smoke 7 + Workflow 15 + Health ---" | Tee-Object $log -Append
php testing/smoke_all_ai.php 2>&1 | Tee-Object $log -Append
php testing/workflow_probe.php 2>&1 | Tee-Object $log -Append
php scripts/health_check.php 2>&1 | Tee-Object $log -Append
"=== Done $log ===" | Tee-Object $log -Append
Write-Host "Log: $log"
