' APS Dream Home - Auto Startup Uninstaller

Option Explicit

Dim objShell, objFSO, strStartupPath, strShortcutPath

Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

' Get startup folder path
strStartupPath = objShell.SpecialFolders("Startup")
strShortcutPath = strStartupPath & "\APS Dream Home Developer.lnk"

' Remove the shortcut
If objFSO.FileExists(strShortcutPath) Then
    objFSO.DeleteFile strShortcutPath
    WScript.Echo "Startup shortcut removed successfully"
Else
    WScript.Echo "Startup shortcut not found"
End If

' Remove the configuration
strConfigPath = "C:\xampp\htdocs\apsdreamhome\config\auto_startup.json"
If objFSO.FileExists(strConfigPath) Then
    objFSO.DeleteFile strConfigPath
    WScript.Echo "Configuration removed successfully"
End If

WScript.Echo "APS Dream Home Auto Startup uninstalled"
