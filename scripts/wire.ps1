# wire.ps1 - One-command local dev pipeline for APS Dream Home.
#
# Stages (chained, stops at first failure unless -KeepGoing):
#   1. git status sanity   (prints branch, ahead/behind, dirty count)
#   2. syntax lint         (changed PHP files only; -AllLint lints the whole app/)
#   3. APP master suite    (php testing/master_test_runner.php - 11 suites incl. HTTP smoke)
#   4. HTTP health probe   (GET / and /api/health on the local server)
#   5. deploy              (only with -Stage staging|live AND env APS_DEPLOY_* set;
#                           otherwise prints the exact commands the owner must run)
#
# Usage:
#   powershell -ExecutionPolicy Bypass -File scripts\wire.ps1                 # lint+master+health (safe default)
#   powershell -ExecutionPolicy Bypass -File scripts\wire.ps1 -AllLint        # full app/ lint (~1-2 min)
#   powershell -ExecutionPolicy Bypass -File scripts\wire.ps1 -Stage staging  # + staging deploy (needs creds)
#   powershell -ExecutionPolicy Bypass -File scripts\wire.ps1 -Stage live     # + live deploy (needs creds)
#   powershell -ExecutionPolicy Bypass -File scripts\wire.ps1 -SkipHealth     # skip local HTTP probe (CI)
#   powershell -ExecutionPolicy Bypass -File scripts\wire.ps1 -KeepGoing      # don't stop on first red suite
#
# Exit codes: 0 everything green, 1 a stage failed, 2 deploy requested but creds missing.

param(
    [ValidateSet('staging','live')]
    [string]$Stage,
    [switch]$AllLint,
    [switch]$SkipHealth,
    [switch]$KeepGoing
)

$root = Split-Path -Parent $PSScriptRoot
Set-Location $root
$fail = $false

function Step($name) { Write-Output "`n=== $name ===" }

# 1. git sanity
Step "1. GIT SANITY"
$branch = git rev-parse --abbrev-ref HEAD
$ahead  = (git rev-list --count '@{u}..HEAD' 2>$null)
$behind = (git rev-list --count 'HEAD..@{u}' 2>$null)
$dirty  = (git status --short | Measure-Object).Count
Write-Output " branch: $branch   dirty-files: $dirty   ahead: $ahead   behind: $behind"
if ($dirty -gt 0) { Write-Output " note: uncommitted work present - deploys ship committed state only, not this tree" }
if ($behind -gt 0) { Write-Output " WARNING: local branch is behind origin - run 'git pull --rebase' first" }

# 2. lint
Step "2. SYNTAX LINT"
if ($AllLint) {
    $files = Get-ChildItem -Recurse -Include *.php app
} else {
    $files = @(git -c core.fileMode=false diff HEAD --name-only) + @(git ls-files -m --exclude-standard) | Where-Object { $_ -match '\.php$' -and (Test-Path $_) } | Sort-Object -Unique
}
$lintFail = 0
foreach ($f in $files) {
    $out = php -l $f 2>&1
    if ($LASTEXITCODE -ne 0) { $lintFail++; Write-Output " FAIL: $f`n$out" }
}
Write-Output " linted $($files.Count) file(s); failures: $lintFail"
if ($lintFail -gt 0) { $fail = $true; if (-not $KeepGoing) { Write-Output " STOP: fix syntax errors first"; exit 1 } }

# 3. master suite
Step "3. MASTER SUITE"
$out = & php testing/master_test_runner.php 2>&1 | Out-String
$out.TrimEnd() -split "`n" | Select-Object -Last 6 | ForEach-Object { Write-Output $_ }
$m = [regex]::Match($out, 'Failed\s*:\s*(\d+)')
$failed = if ($m.Success) { [int]$m.Groups[1].Value } else { -1 }
$p = [regex]::Match($out, 'Passed\s*:\s*(\d+)')
$t = [regex]::Match($out, 'Total Suites Run\s*:\s*(\d+)')
if ($failed -ne 0) { $fail = $true; if (-not $KeepGoing) { Write-Output " STOP: master suite red (failed=$failed)"; exit 1 } } else { $pass = if ($p.Success) { $p.Groups[1].Value } else { '?' }; $total = if ($t.Success) { $t.Groups[1].Value } else { '?' }; Write-Output " master: $pass/$total PASSED" }

# 4. HTTP health probe
if (-not $SkipHealth) {
    Step "4. LOCAL HTTP PROBE"
    foreach ($path in @('/', '/api/health')) {
        try {
            $r = Invoke-WebRequest -Uri "http://localhost/apsdreamhome$path" -UseBasicParsing -TimeoutSec 15
            Write-Output (" {0,-14} HTTP {1}  ({2:N0} bytes)" -f $path, [int]$r.StatusCode, $r.Content.Length)
        } catch {
            $fail = $true
            Write-Output (" {0,-14} FAILED: {1}" -f $path, $_.Exception.Message)
            if (-not $KeepGoing) { exit 1 }
        }
    }
}

# 5. deploy (explicit creds required)
if ($Stage) {
    Step "5. DEPLOY ($Stage)"
    $host = $env:APS_DEPLOY_HOST; $user = $env:APS_DEPLOY_USER; $path = $env:APS_DEPLOY_PATH
    if (-not $host -or -not $user -or -not $path) {
        Write-Output " DEPLOY SKIPPED - required env vars not set:"
        Write-Output "   setx APS_DEPLOY_HOST <ssh host or 'staging.apsdreamhome.com'>"
        Write-Output "   setx APS_DEPLOY_USER <deploy user>"
        Write-Output "   setx APS_DEPLOY_PATH <remote web root, e.g. /var/www/apsdreamhome>"
        Write-Output " Then re-run with the same -Stage flag."
        Write-Output " Manual deploy (owner): rsync -az --delete --exclude .git --exclude storage/logs/ ./ ${user}@${host}:$path/"
        exit 2
    }
    Write-Output " syncing tree to ${user}@${host}:$path ..."
    rsync -az --delete --exclude .git --exclude 'storage/logs' --exclude 'backups' ./ "${user}@${host}:$path/"
    if ($LASTEXITCODE -ne 0) { Write-Output " rsync failed"; exit 1 }
    Write-Output " remote master suite check:"
    ssh "${user}@${host}" "cd $path && php testing/master_test_runner.php"
    if ($LASTEXITCODE -ne 0) { Write-Output " REMOTE MASTER RED - rollback from backups/app_<latest>.tar before proceeding"; exit 1 }
    Write-Output " remote green. Verify $Stage URL manually, then mark the deploy note in AGENTS.md."
}

if ($fail) { exit 1 }
Write-Output "`n wire: ALL STAGES GREEN"
exit 0
