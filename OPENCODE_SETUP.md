# OpenCode Setup Guide for APS Dream Home

## Step 1: Install OpenCode on Laptop

### Option A: Scoop (Recommended for Windows)
```powershell
# PowerShell me run karo (Admin mode me)
scoop install opencode
```

### Option B: Chocolatey
```powershell
# PowerShell me run karo
choco install opencode
```

### Option C: Direct Download
1. Jaao: https://opencode.ai/download
2. Windows version download karo
3. Extract karo aur PATH me add karo

## Step 2: Verify Installation
```powershell
opencode --version
```

## Step 3: AI Model Setup

### For Your PC (i3, 16GB RAM):
Your available models - recommended order:

1. **qwen2.5-coder:1.5b** (986 MB) ← Best for coding
2. **llama3.2:1b** (1.3 GB) - Good general
3. **gemma3:1b** (815 MB) - Fastest, lightest

### Quick Start (No Extra Setup!)
Ollama already installed hai. Just start karo:
```powershell
ollama serve
```

### OpenCode Config:
Project me `opencode.json` already configured hai:
```json
"model": "ollama/qwen2.5-coder:1.5b"
```

## Step 4: Configure OpenCode

### Global Config (~/.config/opencode/opencode.json)
```json
{
  "$schema": "https://opencode.ai/config.json",
  "model": "ollama/qwen2.5-coder:1.5b",
  "providers": {
    "ollama": {
      "baseURL": "http://localhost:11434"
    }
  }
}
```

### Project Config (project root me)
Project me `opencode.json` already hai. Wo automatically load hoga.

## Step 5: Setup MCP Servers

### Install npm packages globally:
```powershell
npm install -g @modelcontextprotocol/server-filesystem
npm install -g @modelcontextprotocol/server-git
npm install -g @modelcontextprotocol/server-memory
npm install -g @modelcontextprotocol/server-fetch
npm install -g @modelcontextprotocol/server-sqlite
npm install -g @modelcontextprotocol/server-mysql
```

## Step 6: Use OpenCode

### Terminal me:
```powershell
# Project directory me jaao
cd C:\xampp\htdocs\apsdreamhome

# OpenCode start karo
opencode

# Ya specific agent ke saath:
opencode --agent php-expert
```

### Commands:
- `/models` - Models dekho
- `/help` - Help dekho
- `/theme` - Theme change karo
- `Ctrl+D` - Exit

## VS Code Extension (Optional)

VS Code me bhi use kar sakte ho:
```powershell
code --install-extension opencode.opencode-vscode
```

## Troubleshooting

### opencode command not found:
```powershell
# PATH check karo
echo $env:PATH

# Ya direct path se run karo
~/.opencode/bin/opencode
```

### Ollama not connecting:
```powershell
# Ollama running hai check karo
ollama ps

# Ya restart karo
ollama serve
```

### MCP servers not working:
```powershell
# Check if npm packages installed
npm list -g @modelcontextprotocol/server-filesystem
```

## Project-Specific Setup

Project me ye files already configured hain:
- `opencode.json` - AI model aur MCP settings
- `AGENTS.md` - Project guidelines
- `mcp_config/mcp_servers.json` - MCP server configs

## Quick Start Commands

```powershell
# 1. Ollama start
ollama serve

# 2. New terminal me jaao aur project directory me
cd C:\xampp\htdocs\apsdreamhome

# 3. OpenCode launch karo
opencode

# 4. Ask karo:
# "Explain the property listing controller"
# "Add a new feature to the CRM"
# "Find all PHP files related to user management"
```

## Free Models (No API Key Needed)

OpenCode me free models available hain:
- `opencode/big-pickle`
- `opencode/gpt-5-nano`
- `opencode/mimo-v2-pro-free`

Model change karne ke liye:
```
/models
```
Ya `opencode.json` me model change karo.

## Support

- Docs: https://opencode.ai/docs
- GitHub: https://github.com/anomalyco/opencode
