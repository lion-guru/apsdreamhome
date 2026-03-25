# 🔧 APS Dream Home - Startup Troubleshooting Guide

## 🚨 **Problem: Auto-Startup Not Working**

### **📊 Issue Analysis:**
- **Installer Runs**: ✅ VBScript executes successfully
- **PC Restarts**: ✅ Computer restarts
- **Services Don't Start**: ❌ XAMPP/VS Code not launching
- **Root Cause**: Service startup failures or path issues

---

## 🔍 **Debugging Steps:**

### **Step 1: Check Installer Created Files**
```batch
' Verify these files were created:
C:\xampp\htdocs\apsdreamhome\config\auto_startup.json
C:\xampp\htdocs\apsdreamhome\UNINSTALL_AUTO_STARTUP.vbs

' Check Windows Startup folder:
%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\
' Should contain: "APS Dream Home Developer.lnk"
```

### **Step 2: Test Manual Startup**
```batch
' Run this to test step by step:
C:\xampp\htdocs\apsdreamhome\TEST_STARTUP_MANUAL.bat
```

### **Step 3: Check Debug Logs**
```batch
' Run debug version for detailed logging:
C:\xampp\htdocs\apsdreamhome\STARTUP_AUTO_DEVELOPER_DEBUG.bat

' Check logs:
C:\xampp\htdocs\apsdreamhome\logs\startup_debug.log
C:\xampp\htdocs\apsdreamhome\logs\startup_errors.log
```

---

## ⚠️ **Common Issues & Solutions:**

### **Issue 1: XAMPP Not Starting**
**Symptoms:**
- MySQL/Apache services not running
- "Service not found" errors
- Browser shows "Unable to connect"

**Solutions:**
```batch
# A. Check XAMPP Installation
dir C:\xampp
dir D:\xampp

# B. Manual XAMPP Start
C:\xampp\xampp-control.exe

# C. Check Port Conflicts
netstat -an | find ":80"
netstat -an | find ":3306"

# D. Reinstall XAMPP if needed
```

### **Issue 2: VS Code Not Opening**
**Symptoms:**
- No VS Code window opens
- "File not found" errors
- VS Code opens but no project

**Solutions:**
```batch
# A. Check VS Code Path
where code
dir "C:\Program Files\Microsoft VS Code\"
dir "C:\Program Files (x86)\Microsoft VS Code\"

# B. Manual VS Code Start
code C:\xampp\htdocs\apsdreamhome

# C. Reinstall VS Code
```

### **Issue 3: VBScript Permission Issues**
**Symptoms:**
- "Permission denied" errors
- Shortcut not created
- Auto-startup not working

**Solutions:**
```batch
# A. Run as Administrator
Right-click INSTALL_AUTO_STARTUP_FINAL.vbs → "Run as administrator"

# B. Check Startup Folder Permissions
echo %APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup

# C. Manually Create Shortcut
# Create desktop shortcut to:
Target: C:\xampp\htdocs\apsdreamhome\STARTUP_AUTO_DEVELOPER.bat
```

---

## 🛠️ **Advanced Troubleshooting:**

### **Check Windows Task Scheduler:**
```batch
' Open Task Scheduler:
taskschd.msc

' Look for "APS Dream Home Developer" task
' Check if it's enabled and configured for "At startup"
```

### **Check Registry Entries:**
```batch
' Open Registry Editor:
regedit

' Check:
HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\Run
' Should contain entry pointing to startup script
```

### **Check Windows Event Viewer:**
```batch
' Open Event Viewer:
eventvwr.msc

' Look for errors around startup time
' Check Application and System logs
```

---

## 🚀 **Working Solutions:**

### **Solution 1: Use Debug Version**
```batch
' Replace auto-startup with debug version:
1. Run: UNINSTALL_AUTO_STARTUP.vbs
2. Run: INSTALL_AUTO_STARTUP_FINAL.vbs
3. Test: STARTUP_AUTO_DEVELOPER_DEBUG.bat
```

### **Solution 2: Manual Desktop Shortcut**
```batch
' Create desktop shortcut:
Target: C:\xampp\htdocs\apsdreamhome\STARTUP_AUTO_DEVELOPER_DEBUG.bat
Working Directory: C:\xampp\htdocs\apsdreamhome
Icon: C:\xampp\htdocs\apsdreamhome\favicon.ico
```

### **Solution 3: Task Scheduler Method**
```batch
' Create scheduled task:
1. Open Task Scheduler (taskschd.msc)
2. Create Basic Task
3. Name: "APS Dream Home Developer"
4. Trigger: "At startup"
5. Action: Start program
6. Program: C:\xampp\htdocs\apsdreamhome\STARTUP_AUTO_DEVELOPER_DEBUG.bat
```

---

## 📋 **Quick Test Commands:**

### **Test XAMPP:**
```batch
cd C:\xampp
xampp-control.exe
```

### **Test Project:**
```batch
cd C:\xampp\htdocs\apsdreamhome
php AUTO_START_DEVELOPER.php
```

### **Test VS Code:**
```batch
code C:\xampp\htdocs\apsdreamhome
```

### **Test Browser:**
```batch
start http://localhost/apsdreamhome
```

---

## 🎯 **Recommendations:**

### **Immediate Actions:**
1. **Run Manual Test**: `TEST_STARTUP_MANUAL.bat`
2. **Check Debug Logs**: `startup_debug.log`
3. **Verify XAMPP**: Ensure services start manually
4. **Test VS Code**: Verify path and launch

### **If Still Not Working:**
1. **Use Debug Version**: More detailed logging
2. **Check Permissions**: Administrator access
3. **Manual Shortcut**: Desktop shortcut method
4. **Reinstall XAMPP**: Fresh installation

---

## 📞 **Support Commands:**

### **Get System Info:**
```batch
systeminfo | findstr /B /C:"OS Name" "System Type" "Total Physical Memory"
```

### **Check Services:**
```batch
sc query mysql
sc query apache2.4
```

### **Network Check:**
```batch
ping localhost
netstat -an | find ":80"
```

---

**🔧 Complete troubleshooting guide created! Use these steps to identify and fix startup issues.**
