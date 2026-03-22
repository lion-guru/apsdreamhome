' APS Dream Home - Auto Startup Installer
' Installs the autonomous developer to Windows startup

Option Explicit

Dim objShell, objFSO, strScriptPath, strStartupPath, strShortcutPath

' Create shell object
Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

' Get current directory
strScriptPath = objFSO.GetParentFolderName(WScript.ScriptFullName)

' Display installation message
WScript.Echo "========================================"
WScript.Echo "APS DREAM HOME - AUTO STARTUP INSTALLER"
WScript.Echo "========================================"
WScript.Echo ""
WScript.Echo "Installing autonomous developer to Windows startup..."
WScript.Echo ""

' Get startup folder path
strStartupPath = objShell.SpecialFolders("Startup")

' Create startup shortcut
strShortcutPath = strStartupPath & "\APS Dream Home Developer.lnk"

' Create the shortcut
Set objShortcut = objShell.CreateShortcut(strShortcutPath)

' Set shortcut properties
objShortcut.TargetPath = strScriptPath & "\STARTUP_AUTO_DEVELOPER.bat"
objShortcut.WorkingDirectory = strScriptPath
objShortcut.Description = "APS Dream Home Autonomous Developer"
objShortcut.IconLocation = strScriptPath & "\favicon.ico"

' Check if favicon exists, if not use default
If Not objFSO.FileExists(strScriptPath & "\favicon.ico") Then
    objShortcut.IconLocation = "%SystemRoot%\System32\shell32.dll,27"
End If

' Save the shortcut
objShortcut.Save

' Create startup configuration file
Dim strConfigPath
strConfigPath = strScriptPath & "\config\auto_startup.json"

' Create config directory if not exists
If Not objFSO.FolderExists(strScriptPath & "\config") Then
    objFSO.CreateFolder strScriptPath & "\config"
End If

' Create configuration
Dim objConfig
Set objConfig = CreateObject("Scripting.Dictionary")

objConfig.Add "installed", True
objConfig.Add "install_date", Now()
objConfig.Add "version", "1.0"
objConfig.Add "auto_start", True
objConfig.Add "start_xampp", True
objConfig.Add "start_ai", True
objConfig.Add "open_vscode", True
objConfig.Add "open_browser", True
objConfig.Add "monitoring", True

' Save configuration
Dim objTextStream
Set objTextStream = objFSO.CreateTextFile(strConfigPath, True)
objTextStream.Write ConvertToJson(objConfig)
objTextStream.Close

' Create uninstall script
Dim strUninstallScript
strUninstallScript = strScriptPath & "\UNINSTALL_AUTO_STARTUP.vbs"

Dim objUninstallStream
Set objUninstallStream = objFSO.CreateTextFile(strUninstallScript, True)

objUninstallStream.WriteLine "' APS Dream Home - Auto Startup Uninstaller"
objUninstallStream.WriteLine ""
objUninstallStream.WriteLine "Option Explicit"
objUninstallStream.WriteLine ""
objUninstallStream.WriteLine "Dim objShell, objFSO, strStartupPath, strShortcutPath"
objUninstallStream.WriteLine ""
objUninstallStream.WriteLine "Set objShell = CreateObject(""WScript.Shell"")"
objUninstallStream.WriteLine "Set objFSO = CreateObject(""Scripting.FileSystemObject"")"
objUninstallStream.WriteLine ""
objUninstallStream.WriteLine "' Get startup folder path"
objUninstallStream.WriteLine "strStartupPath = objShell.SpecialFolders(""Startup"")"
objUninstallStream.WriteLine "strShortcutPath = strStartupPath & ""\APS Dream Home Developer.lnk"""
objUninstallStream.WriteLine ""
objUninstallStream.WriteLine "' Remove the shortcut"
objUninstallStream.WriteLine "If objFSO.FileExists(strShortcutPath) Then"
objUninstallStream.WriteLine "    objFSO.DeleteFile strShortcutPath"
objUninstallStream.WriteLine "    WScript.Echo ""Startup shortcut removed successfully"""
objUninstallStream.WriteLine "Else"
objUninstallStream.WriteLine "    WScript.Echo ""Startup shortcut not found"""
objUninstallStream.WriteLine "End If"
objUninstallStream.WriteLine ""
objUninstallStream.WriteLine "' Remove configuration"
objUninstallStream.WriteLine "strConfigPath = """ & strScriptPath & ""\config\auto_startup.json"""
objUninstallStream.WriteLine "If objFSO.FileExists(strConfigPath) Then"
objUninstallStream.WriteLine "    objFSO.DeleteFile strConfigPath"
objUninstallStream.WriteLine "    WScript.Echo ""Configuration removed successfully"""
objUninstallStream.WriteLine "End If"
objUninstallStream.WriteLine ""
objUninstallStream.WriteLine "WScript.Echo ""APS Dream Home Auto Startup uninstalled"""

objUninstallStream.Close

' Display success message
WScript.Echo "✅ Installation completed successfully!"
WScript.Echo ""
WScript.Echo "What has been installed:"
WScript.Echo "• Startup shortcut in Windows Startup folder"
WScript.Echo "• Configuration file: " & strConfigPath
WScript.Echo "• Uninstall script: " & strUninstallScript
WScript.Echo ""
WScript.Echo "Features:"
WScript.Echo "• Auto-start XAMPP services (Apache, MySQL)"
WScript.Echo "• Initialize APS Dream Home project"
WScript.Echo "• Start AI Assistant with 7 roles"
WScript.Echo "• Open VS Code with project"
WScript.Echo "• Open browser with project URLs"
WScript.Echo "• Continuous project monitoring"
WScript.Echo "• Automatic logging"
WScript.Echo ""
WScript.Echo "Next steps:"
WScript.Echo "1. Restart your computer"
WScript.Echo "2. Services will start automatically"
WScript.Echo "3. Check logs/auto_developer.log for status"
WScript.Echo "4. Access project at: http://localhost/apsdreamhome"
WScript.Echo ""
WScript.Echo "To uninstall:"
WScript.Echo "1. Run: " & strUninstallScript
WScript.Echo "2. Or manually delete shortcut from Startup folder"
WScript.Echo ""
WScript.Echo "========================================"

' Function to convert dictionary to JSON
Function ConvertToJson(objDict)
    Dim strJson, key
    strJson = "{"
    
    For Each key In objDict.Keys
        If strJson <> "{" Then strJson = strJson & ","
        strJson = strJson & """" & key & """:""" & objDict(key) & """"
    Next
    
    strJson = strJson & "}"
    ConvertToJson = strJson
End Function

' Ask if user wants to test the installation
Dim intResponse
intResponse = MsgBox("Would you like to test the autonomous developer now?" & vbCrLf & vbCrLf & _
"This will start all services and open the development environment.", _
vbQuestion + vbYesNo, "Test Installation")

If intResponse = vbYes Then
    WScript.Echo ""
    WScript.Echo "Starting test run..."
    objShell.Run """" & strScriptPath & "\STARTUP_AUTO_DEVELOPER.bat""", 1, True
End If

WScript.Echo ""
WScript.Echo "Installation completed. Ready for autonomous development!"
