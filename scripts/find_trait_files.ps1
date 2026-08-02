<# Find files with ServiceTenantTrait class #>
$serviceDir = "C:\xampp\htdocs\apsdreamhome\app\Services"
$files = Get-ChildItem -Path "$serviceDir\*.php" -Recurse

$traitFiles = @()
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    if ($content -match "class ServiceTenantTrait") {
        $traitFiles += $file.FullName
    }
}

if ($traitFiles.Count -gt 0) {
    Write-Host "Files containing ServiceTenantTrait class:" -ForegroundColor Green
    foreach ($file in $traitFiles) {
        Write-Host "  $file"
    }
} else {
    Write-Host "No files contain ServiceTenantTrait class" -ForegroundColor Yellow
}
