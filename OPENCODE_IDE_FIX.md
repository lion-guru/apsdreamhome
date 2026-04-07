# OPENCODE IDE TROUBLESHOOTING GUIDE
# ==================================

## PROBLEM: OpenCode IDE not working
## SOLUTION: Minimal configuration for stability

## STEPS TO FIX OPENCODE IDE:

### Step 1: Use OpenCode-Specific Settings
1. Close VS Code completely
2. Copy `.vscode/settings_opencode.json` to `.vscode/settings.json`
3. Restart VS Code

### Step 2: Install Only Essential Extensions
1. Install only these extensions:
   - PHP Intelephense (bmewburn.vscode-intelephense-client)
   - Prettier (esbenp.prettier-vscode)
   - JSON (ms-vscode.vscode-json)
   - GitLens (eamodio.gitlens)

2. Disable/Uninstall all other extensions:
   - MCP Tools
   - GitLab Language Server
   - Windsurf
   - Database tools
   - AI tools
   - Testing tools

### Step 3: Clear VS Code Cache
1. Close VS Code
2. Delete `%APPDATA%\Code\User\workspaceStorage`
3. Delete `.vscode/.cache` if exists
4. Restart VS Code

### Step 4: Test Basic Functionality
1. Open a PHP file
2. Check syntax highlighting
3. Check code completion
4. Check file navigation

## OPENCODE-SPECIFIC CONFIGURATION:

### Minimal Settings:
- PHP 8.2 support only
- No AI/MCP features
- No database tools
- No language servers except PHP
- File exclusions for performance

### Disabled Features:
- TypeScript/JavaScript servers
- CSS/HTML validation
- AI code completion
- MCP tools
- GitLab integration
- Database connections
- Testing frameworks

## VERIFICATION CHECKLIST:
- [ ] VS Code opens without errors
- [ ] PHP files load quickly
- [ ] Intelephense working
- [ ] No connection errors
- [ ] Basic editing works
- [ ] File explorer works

## FILES CREATED:
- `.vscode/settings_opencode.json` - Minimal settings
- `.vscode/extensions_opencode_clean.json` - Essential extensions only

## ALTERNATIVE: Use Different Editor
If OpenCode still doesn't work, try:
1. Notepad++ (simple, lightweight)
2. Sublime Text (fast, minimal)
3. Atom (if available)

## NEXT STEPS:
1. Apply OpenCode settings
2. Install only essential extensions
3. Test basic PHP editing
4. Disable all conflicting features

OPENCODE STATUS: **Minimal configuration ready**
