# PowerShell script to remove duplicate JS and CSS files

$duplicateLog = "C:\xampp\htdocs\apsdreamhome\duplicate_removal_log.txt"
$backupDir = "C:\xampp\htdocs\apsdreamhome\backup_duplicates_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Create backup directory
New-Item -ItemType Directory -Path $backupDir | Out-Null

function Remove-Duplicates {
    param([string]$FileExtension)
    
    Write-Output "Removing duplicate $FileExtension files..." | Tee-Object -FilePath $duplicateLog -Append
    
    $duplicateFiles = Get-ChildItem -Path "C:\xampp\htdocs\apsdreamhome" -Recurse -Include "*.$FileExtension" | 
        Group-Object -Property Name | 
        Where-Object { $_.Count -gt 1 }
    
    foreach ($group in $duplicateFiles) {
        $keepFirst = $true
        foreach ($file in $group.Group) {
            if ($keepFirst) {
                # Keep the first file in a preferred location
                $preferredPath = Join-Path "C:\xampp\htdocs\apsdreamhome\vendor" $file.Name
                New-Item -ItemType Directory -Path (Split-Path $preferredPath) -ErrorAction SilentlyContinue | Out-Null
                Copy-Item $file.FullName $preferredPath -Force
                
                Write-Output "Preserved: $($file.FullName) -> $preferredPath" | Tee-Object -FilePath $duplicateLog -Append
                $keepFirst = $false
            } else {
                # Backup duplicate files before deletion
                $backupPath = Join-Path $backupDir $file.Name
                Copy-Item $file.FullName $backupPath
                
                # Remove duplicate file
                Remove-Item $file.FullName -Force
                
                Write-Output "Deleted: $($file.FullName)" | Tee-Object -FilePath $duplicateLog -Append
            }
        }
    }
}

# Remove duplicates for JS and CSS
Remove-Duplicates -FileExtension "js"
Remove-Duplicates -FileExtension "css"

Write-Output "Duplicate removal complete. Check $duplicateLog for details."
Write-Output "Backup of deleted files stored in $backupDir"
