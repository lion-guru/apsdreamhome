# Fix App::database() -> Database::getInstance()
Get-ChildItem -Path "C:\xampp\htdocs\apsdreamhome\app\Services" -Filter "*.php" -Recurse | ForEach-Object {
    $content = Get-Content $_.FullName -Raw
    if ($content -match 'App::database\(\)') {
        $newContent = $content -replace 'App::database\(\)', '\App\Core\Database\Database::getInstance()'
        $newContent = $newContent -replace '\\App\\Core\\App::database\(\)', '\App\Core\Database\Database::getInstance()'
        Set-Content -Path $_.FullName -Value $newContent -NoNewline
        Write-Host "Fixed: $($_.Name)" -ForegroundColor Green
    }
}

Write-Host "`nDone! All App::database() fixed." -ForegroundColor Cyan
