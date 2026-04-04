# Fix all old WhatsApp and phone numbers
Get-ChildItem -Path "C:\xampp\htdocs\apsdreamhome\app\views" -Filter "*.php" -Recurse | ForEach-Object {
    $content = Get-Content $_.FullName -Raw
    $changes = 0
    
    # Fix WhatsApp numbers
    if ($content -match 'wa\.me/919876543210') {
        $content = $content -replace 'wa\.me/919876543210', 'wa.me/919277121112'
        $changes++
    }
    
    # Fix phone display (with space)
    if ($content -match '\+91 98765 43210') {
        $content = $content -replace '\+91 98765 43210', '+91 92771 21112'
        $changes++
    }
    
    # Fix phone (without space)
    if ($content -match '\+91-9876543210') {
        $content = $content -replace '\+91-9876543210', '+91-9277121112'
        $changes++
    }
    
    if ($changes -gt 0) {
        Set-Content -Path $_.FullName -Value $content -NoNewline
        Write-Host "Fixed: $($_.Name) ($changes changes)" -ForegroundColor Green
    }
}

Write-Host "`nAll phone numbers updated!" -ForegroundColor Cyan
