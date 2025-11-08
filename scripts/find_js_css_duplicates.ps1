# PowerShell script to find duplicate JS and CSS files

$duplicateLog = "C:\xampp\htdocs\apsdreamhome\duplicate_js_css_files.log"

# Clear previous log
Clear-Content $duplicateLog -ErrorAction SilentlyContinue

function Find-Duplicates {
    param([string]$FileExtension)
    
    Write-Output "Finding duplicate $FileExtension files..." | Tee-Object -FilePath $duplicateLog -Append
    
    $duplicateFiles = Get-ChildItem -Path "C:\xampp\htdocs\apsdreamhome" -Recurse -Include "*.$FileExtension" | 
        Group-Object -Property Name | 
        Where-Object { $_.Count -gt 1 }
    
    foreach ($group in $duplicateFiles) {
        Write-Output "Duplicate found: $($group.Name) (Occurrences: $($group.Count))" | Tee-Object -FilePath $duplicateLog -Append
        $group.Group | ForEach-Object { 
            Write-Output $_.FullName | Tee-Object -FilePath $duplicateLog -Append 
        }
        Write-Output "---" | Tee-Object -FilePath $duplicateLog -Append
    }
}

# Find duplicates for JS and CSS
Find-Duplicates -FileExtension "js"
Find-Duplicates -FileExtension "css"

Write-Output "Duplicate search complete. Check $duplicateLog for details."
