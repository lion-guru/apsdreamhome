# MCP Configuration Guide

## Unified MCP Setup for All IDEs

This directory contains the unified MCP (Model Context Protocol) configuration that works across multiple IDEs.

## Directory Structure

```
mcp_config/
├── mcp_servers.json    # Unified server definitions
└── mcp.env             # Environment variables (API keys, etc.)

.windsurf/
└── mcp_servers.json    # Copied from mcp_config/ (Windsurf)

.vscode/
└── (settings.json points to mcp_config/mcp_servers.json)

opencode_mcp.json       # Standalone config for CLI usage
```

## IDE Configuration

### VS Code
1. Install "MCP" extension: `modelcontextprotocol.vscode-mcp`
2. Add to `.vscode/settings.json`:
```json
{
  "mcp.serverConfigPath": "mcp_config/mcp_servers.json"
}
```

### Windsurf
- Already configured to use `.windsurf/mcp_servers.json`
- Copy updated config: `Copy-Item "mcp_config/mcp_servers.json" ".windsurf/mcp_servers.json"`

### opencode (CLI)
- Use `opencode_mcp.json` for CLI-based MCP usage

## Available MCP Servers

| Server | Purpose | API Key Needed |
|--------|---------|----------------|
| `mysql` | MySQL database operations | No |
| `filesystem` | File read/write/search | No |
| `memory` | Context/memory storage | No |
| `sqlite` | SQLite database | No |
| `fetch` | Web HTTP requests | No |
| `git` | Git operations | No |
| `github` | GitHub API | Yes (GITHUB_TOKEN) |
| `sequential-thinking` | AI reasoning | No |
| `brave-search` | Web search | Yes (BRAVE_API_KEY) |

## Environment Variables

Edit `mcp_config/mcp.env` to add your API keys:

```env
GITHUB_TOKEN=ghp_xxxxxxxxxxxxx
BRAVE_API_KEY=BSAxxxxxxxxxxxxxxxxxxxx
```

## Quick Start

1. Install required MCP packages:
```bash
npm install -g @modelcontextprotocol/server-filesystem
npm install -g @modelcontextprotocol/server-git
npm install -g @modelcontextprotocol/server-github
npm install -g @modelcontextprotocol/server-memory
npm install -g @modelcontextprotocol/server-sqlite
npm install -g @modelcontextprotocol/server-fetch
```

2. Set environment variables (optional)

3. Reload your IDE
