# CSS Consolidation Script
$cssDir = 'C:\xampp\htdocs\apsdreamhome\assets\css'
$outputDir = 'C:\xampp\htdocs\apsdreamhome\assets\css\consolidated'

if (-not (Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
}

$bundles = @{
    'aps-core.css' = @('style.css', 'frontend.css', 'frontend-enhancements.css')
    'aps-components.css' = @('customer-pages.css', 'notification-system.css', 'image-gallery.css', 'image-uploader.css', 'live-chat-widget.css')
    'aps-layout.css' = @('header.css', 'mobile-responsive.css', 'modern-style.css', 'advanced-features.css')
    'aps-pages.css' = @('chatbot.css', 'ai-chat.css', 'ai-chat-enhanced.css', 'ai-features.css', 'live-chat-widget.css', 'notification-system.css', 'image-gallery.css', 'image-uploader.css', 'employee.css', 'ai-features.css')
}

foreach ($bundleName in $bundles.Keys) {
    $outputPath = Join-Path $outputDir $bundleName
    $content = @()
    
    $content += "/* ========================================================================"
    $content += " * $bundleName - Consolidated Bundle"
    $content += " * Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    $content += " * ======================================================================== */"
    $content += ""
    
    foreach ($file in $bundles[$bundleName]) {
        $filePath = Join-Path $cssDir $file
        if (Test-Path $filePath) {
            $fileContent = Get-Content $filePath -Raw
            $content += "/* ===== $file ===== */"
            $content += $fileContent
            $content += ""
            Write-Host "  Added: $file" -ForegroundColor Green
        } else {
            Write-Host "  Missing: $file" -ForegroundColor Yellow
        }
    }
    
    Set-Content -Path $outputPath -Value ($content -join "`n") -Encoding UTF8
    $sizeKB = [Math]::Round((New-Object System.IO.FileInfo($outputPath)).Length / 1024, 1)
    Write-Host "Created: $bundleName ($sizeKB KB)" -ForegroundColor Cyan
}

Write-Host "`nConsolidation complete!" -ForegroundColor Green
