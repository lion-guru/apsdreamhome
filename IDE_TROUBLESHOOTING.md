# IDE TROUBLESHOOTING GUIDE
# =======================
# Windsurf & GitLab Language Server Issues

## PROBLEMS IDENTIFIED:
- Windsurf client couldn't create connection to server
- GitLab Language Server failed to start
- IDE not opening properly

## ROOT CAUSES:
1. **MCP Tools Conflict** - MCP servers interfering with IDE startup
2. **GitLab Language Server** - Conflicting with PHP language server
3. **Too many language servers** - Memory/CPU overload
4. **Large project size** - 2000+ files causing performance issues

## QUICK FIXES APPLIED:

### 1. Disable MCP Tools
```json
"mcp.enabled": false,
"mcp.autoStart": false,
```

### 2. Disable GitLab Language Server
```json
"gitlab.lsp.enabled": false,
```

### 3. Disable Windsurf
```json
"windsurf.enabled": false,
```

### 4. Optimize File Watching
```json
"files.watcherExclude": {
    "**/testing/*/**": true,
    "**/backup/*/**": true,
    "**/logs/*/**": true
}
```

## STEPS TO FIX IDE:

### Step 1: Replace Settings File
1. Close VS Code completely
2. Copy `.vscode/settings_fixed.json` to `.vscode/settings.json`
3. Restart VS Code

### Step 2: Clear Cache
1. Delete `.vscode/.cache` folder if exists
2. Delete `%APPDATA%\Code\User\workspaceStorage` for this workspace
3. Restart VS Code

### Step 3: Disable Extensions
1. Disable: MCP Tools extension
2. Disable: GitLab extension
3. Disable: Windsurf extension
4. Keep only essential extensions:
   - PHP Intelephense
   - MySQL Client
   - GitLens
   - Prettier

### Step 4: Test PHP Only
1. Open a PHP file
2. Check if Intelephense is working
3. Verify syntax highlighting
4. Check code completion

## ALTERNATIVE APPROACH:

### Create Minimal Settings
If issues persist, use minimal settings:
```json
{
    "php.version": "8.2",
    "php.validate.executablePath": "C:\\xampp\\php\\php.exe",
    "intelephense.maxMemory": 1024,
    "files.autoSave": "afterDelay",
    "telemetry.enableTelemetry": false
}
```

## VERIFICATION:

### Check IDE Status:
- [ ] VS Code opens without errors
- [ ] PHP files load quickly
- [ ] Intelephense working
- [ ] No connection errors
- [ ] Git operations working

### Test Project Access:
- [ ] Can open PHP files
- [ ] Can navigate folders
- [ ] Can edit files
- [ ] Git commits working

## FILES CREATED/FIXED:
- `.vscode/settings_fixed.json` - Optimized settings
- `IDE_TROUBLESHOOTING.md` - This guide

## NEXT STEPS:
1. Apply the fixed settings
2. Restart VS Code
3. Test functionality
4. Re-enable extensions one by one if needed

## CONTACT:
If issues persist, check:
- VS Code Help > Toggle Developer Tools for errors
- Windows Event Viewer for system errors
- XAMPP control panel for services status
