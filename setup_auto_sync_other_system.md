# Auto Sync Setup for Other System
## Dusre System Mein Auto Sync Kaise Start Karein

### Step 1: Script Copy Karo
```powershell
# Dusre system mein ye file banaye:
# C:\xampp\htdocs\apsdreamhome\scripts\auto_sync.ps1

# Is script ko copy karo current system se
```

### Step 2: Script Run Karo
```powershell
# PowerShell admin mode mein kholo
cd C:\xampp\htdocs\apsdreamhome\scripts

# Continuous mode mein start karo (30 second interval)
.\auto_sync.ps1 -Continuous -Interval 30

# Ya single sync ke liye
.\auto_sync.ps1
```

### Step 3: Background Mein Run Karne Ke Liye
```powershell
# Hidden window mein run karne ke liye
powershell.exe -WindowStyle Hidden -File "C:\xampp\htdocs\apsdreamhome\scripts\auto_sync.ps1" -Continuous -Interval 30

# Ya Windows Task Scheduler mein add karo
# Trigger: System startup
# Action: powershell.exe -WindowStyle Hidden -File "C:\xampp\htdocs\apsdreamhome\scripts\auto_sync.ps1" -Continuous -Interval 30
```

### Step 4: Verification
```powershell
# Check if script is running
Get-Process | Where-Object {$_.ProcessName -eq "powershell"}

# Check log file
Get-Content "C:\xampp\htdocs\apsdreamhome\auto_pull.log" -Tail 10
```

### Troubleshooting
```powershell
# Agar permission error aaye:
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Agar git path issue ho:
$env:PATH += ";C:\Program Files\Git\bin"

# Manual check karne ke liye:
cd C:\xampp\htdocs\apsdreamhome
git status
git pull origin main
```

### Expected Output
```
🔄 APS Dream Home - Auto Pull Sync
=================================
[2026-03-01 17:45:00] 🚀 Starting auto-pull sync (Interval: 30s)
[2026-03-01 17:45:00] 📁 Project: C:\xampp\htdocs\apsdreamhome\scripts
[2026-03-01 17:45:00] 🔁 Running in continuous mode
[2026-03-01 17:45:00] 📥 Checking for remote changes...
[2026-03-01 17:45:01] ✅ Already up to date
[2026-03-01 17:45:01] 🎉 Synchronization complete
[2026-03-01 17:45:01] ⏰ Waiting 30 seconds...
```

### Auto-Start Setup (Optional)
```powershell
# Startup mein add karne ke liye
$shortcutPath = "$env:APPDATA\Microsoft\Windows\Start Menu\Programs\Startup\APS Auto Sync.lnk"
$scriptPath = "C:\xampp\htdocs\apsdreamhome\scripts\auto_sync.ps1"
$arguments = "-WindowStyle Hidden -File `"$scriptPath`" -Continuous -Interval 30"

$shell = New-Object -ComObject WScript.Shell
$shortcut = $shell.CreateShortcut($shortcutPath)
$shortcut.TargetPath = "powershell.exe"
$shortcut.Arguments = $arguments
$shortcut.Save()
```

### Important Notes
- Script sirf clean working directory mein pull karega
- Agar local changes hain to skip karega
- Log file automatically banegi: auto_pull.log
- 30 second interval adjustable hai
