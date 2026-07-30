# APS Dream Home — Load Testing Runner
# Runs all 5 load tests sequentially and aggregates results.

$ErrorActionPreference = "Stop"
$ProgressPreference    = "SilentlyContinue"

$ProjectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $ProjectRoot

$LoadDir   = Join-Path $ProjectRoot "testing/load"
$ResultsMd = Join-Path $LoadDir "load_test_results.json"
$StartTime = Get-Date

Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  APS Dream Home — Load Test Suite" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "Started: $($StartTime.ToString('yyyy-MM-dd HH:mm:ss'))"
Write-Host ""

# ------ Pre-flight ------
$phpExe = (Get-Command php.exe -ErrorAction SilentlyContinue).Source
if (-not $phpExe) {
    $phpExe = "C:\xampp\php\php.exe"
    if (-not (Test-Path $phpExe)) {
        Write-Error "PHP not found. Set PATH or ensure C:\xampp\php\php.exe exists."
        exit 1
    }
}
Write-Host "PHP: $phpExe"
Write-Host ""

# ------ Check Apache / MySQL are up ------
Write-Host "Pre-flight: checking services..." -ForegroundColor Yellow
$apacheUp = $false
try {
    $r = Invoke-WebRequest -Uri "http://localhost/apsdreamhome/" -UseBasicParsing -TimeoutSec 5 -ErrorAction SilentlyContinue
    $apacheUp = ($r.StatusCode -ge 200 -and $r.StatusCode -lt 500)
} catch { $apacheUp = $false }
if (-not $apacheUp) {
    Write-Warning "Apache may not be running. Start XAMPP Apache before running."
} else {
    Write-Host "  ✓ Apache responds on http://localhost/apsdreamhome/" -ForegroundColor Green
}

# ------ Run tests ------
$tests = @(
    @{ Name = "load_test";       Script = "load_test.php";       Args = @() },
    @{ Name = "benchmark";       Script = "benchmark.php";       Args = @("/", 50, "both") },
    @{ Name = "db_stress";       Script = "db_stress.php";       Args = @() },
    @{ Name = "asset_benchmark"; Script = "asset_benchmark.php"; Args = @() },
    @{ Name = "api_load";        Script = "api_load.php";        Args = @() }
)

$results = [ordered]@{}
$totalOk = 0
$totalFail = 0

foreach ($t in $tests) {
    $scriptPath = Join-Path $LoadDir $t.Script
    if (-not (Test-Path $scriptPath)) {
        Write-Host "  ✗ $($t.Name) : script missing" -ForegroundColor Red
        $totalFail++
        continue
    }

    Write-Host ""
    Write-Host "▶ Running $($t.Name)..." -ForegroundColor Yellow
    $logFile = Join-Path $LoadDir "results-$($t.Name).txt"

    $argString = ($t.Args | ForEach-Object { if ($_ -match '\s') { "'$_'" } else { "$_" } }) -join " "
    $cmd = "& '$phpExe' '$scriptPath' $argString 2>&1 | Tee-Object -FilePath '$logFile'"
    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    try {
        Invoke-Expression $cmd
        $sw.Stop()
        Write-Host "  ✓ $($t.Name) completed in $([math]::Round($sw.Elapsed.TotalSeconds, 1))s" -ForegroundColor Green
        $totalOk++

        # Capture JSON result if any
        $candidates = @(
            (Join-Path $LoadDir "$($t.Name)_results.json"),
            (Join-Path $LoadDir "load_test_results.json")  # load_test writes this
        )
        foreach ($j in $candidates) {
            if (Test-Path $j) {
                $key = Split-Path $j -Leaf
                try {
                    $results[$key] = Get-Content $j -Raw | ConvertFrom-Json
                } catch { }
            }
        }
    } catch {
        $sw.Stop()
        Write-Host "  ✗ $($t.Name) failed: $_" -ForegroundColor Red
        $totalFail++
    }
}

# ------ Aggregate ------
$EndTime = Get-Date
$Duration = $EndTime - $StartTime

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  AGGREGATED RESULTS" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "Ran $totalOk/$($tests.Count) tests in $([math]::Round($Duration.TotalSeconds, 1))s"
Write-Host ""

# ------ Per-test summary ------
if ($results.Contains('load_test_results.json')) {
    $lt = $results['load_test_results.json']
    Write-Host "── load_test.php (general load) ──" -ForegroundColor White
    Write-Host "  Throughput: $($lt.totals.throughput_rps) req/s"
    Write-Host "  Avg: $($lt.latency_summary.avg)s | p95: $($lt.latency_summary.p95)s | p99: $($lt.latency_summary.p99)s"
    Write-Host "  Errors: $($lt.totals.errors) ($($lt.totals.error_rate)%)"
    Write-Host ""
}
if ($results.Contains('benchmark_results.json')) {
    $b = $results['benchmark_results.json']
    Write-Host "── benchmark.php (single endpoint) ──" -ForegroundColor White
    Write-Host "  Path: $($b.path) | Iterations: $($b.iterations)"
    Write-Host "  Avg: $($b.avg_ms)ms | p95: $($b.p95_ms)ms | p99: $($b.p99_ms)ms"
    Write-Host "  Throughput: $($b.rps) req/s"
    Write-Host ""
}
if ($results.Contains('db_stress_results.json')) {
    $d = $results['db_stress_results.json']
    Write-Host "── db_stress.php (database) ──" -ForegroundColor White
    Write-Host "  Tables: $($d.select_stress.tables_tested) | Total SELECTs: $($d.select_stress.total_selects)"
    Write-Host "  Avg: $($d.select_stress.overall_avg_ms)ms | p95: $($d.select_stress.overall_p95_ms)ms | Slow: $($d.select_stress.slow_queries_overall)"
    Write-Host ""
}
if ($results.Contains('asset_benchmark_results.json')) {
    $a = $results['asset_benchmark_results.json']
    Write-Host "── asset_benchmark.php (static assets) ──" -ForegroundColor White
    $gt = $a.grand_total
    Write-Host "  Assets: $($gt.count) | Raw: $([math]::Round($gt.raw/1024, 1))KB | Gzip: $([math]::Round($gt.gzip/1024, 1))KB | Savings: $($gt.savings_pct)%"
    Write-Host ""
}
if ($results.Contains('api_load_results.json')) {
    $al = $results['api_load_results.json']
    Write-Host "── api_load.php (API rate limit) ──" -ForegroundColor White
    Write-Host "  Path: $($al.meta.api_path) | Authenticated: $($al.meta.authenticated)"
    Write-Host "  Throughput: $($al.summary.throughput_rps) req/s | p95: $($al.summary.p95_ms)ms"
    Write-Host "  Rate limited: $($al.summary.rate_limited) (first 429 at req #$($al.summary.first_429_at))"
    Write-Host ""
}

# ------ Write aggregated file ------
$aggregate = @{
    meta = @{
        suite = "APS Dream Home Load Test"
        started_at = $StartTime.ToString('o')
        finished_at = $EndTime.ToString('o')
        duration_s = [math]::Round($Duration.TotalSeconds, 1)
        php = & "$phpExe" -r 'echo PHP_VERSION;'
        tests_run = $totalOk
        tests_failed = $totalFail
    }
    results = $results
}
$aggregate | ConvertTo-Json -Depth 8 | Set-Content -Path $ResultsMd -Encoding UTF8

Write-Host "Aggregated results → $ResultsMd"
Write-Host "Per-test logs     → $LoadDir\results-*.txt"
Write-Host ""
if ($totalFail -eq 0) {
    Write-Host "✅ All tests passed." -ForegroundColor Green
} else {
    Write-Host "⚠️  $totalFail test(s) failed." -ForegroundColor Red
    exit 1
}
