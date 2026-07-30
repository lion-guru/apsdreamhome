# APS Dream Home - Autonomous Agentic Dev System

## Overview

This is a self-running multi-agent development system that works continuously on the APS Dream Home project. Like JARVIS from Iron Man - it watches the project, finds issues, fixes them, runs tests, and reports progress autonomously.

## How It Works

### The Agent Pipeline (from `.devin/rules/agent_orchestration.mdc`)

```
Analysis → Implementation → Testing → Review → Documentation → Repeat
```

### Agent Roles

| Agent                      | Role                | What It Does                                               |
| -------------------------- | ------------------- | ---------------------------------------------------------- |
| **Backend Engineer**       | `php_backend`       | PHP/MVC backend, controllers, services, SQL fixes          |
| **Frontend Engineer**      | `flutter_frontend`  | Flutter UI/UX, page wiring, responsive fixes               |
| **QA Engineer**            | `testing`           | E2E tests (153 checks), regression, syntax checks          |
| **Security Engineer**      | `security_audit`    | SQL injection, CSRF, auth hardening, tenant isolation      |
| **DevOps Engineer**        | `infrastructure`    | Builds, APK, deployment, cron, DB migrations               |
| **Architecture Analyst**   | `codebase_analysis` | Deep scans, dead code detection, architecture improvements |
| **Documentation Engineer** | `documentation`     | AGENTS.md updates, changelog, lesson capture               |

### AI Backend (Ollama)

- **Model**: Qwen 2.5 7B (configurable)
- **Fallback**: Qwen 2.5 3B
- **Features**: Code reasoning, bug analysis, fix suggestions, auto-review
- **Runs locally** - no API costs, no internet needed

### Continuous Operation

The scheduler runs in a loop:

1. **Discover tasks** from: git diff, PHP syntax errors, AGENTS.md pending items, E2E failures, archive audit
2. **Assign to agents** based on task type
3. **Execute** with proper tool access
4. **Verify** with E2E tests after each change
5. **Report** progress to heartbeat log
6. **Repeat** until max cycles or stopped

## Files Created

```
agentic_dev_system/
├── config.json              # System configuration
├── orchestrator.php          # Main orchestrator
├── task_discovery.php        # Auto-task discovery
├── tools/
│   └── ollama.php            # Ollama AI client
├── roles/
│   ├── backend_agent.php     # Backend Engineer agent
│   ├── frontend_agent.php    # Frontend Engineer agent
│   ├── qa_agent.php          # QA Engineer agent
│   ├── security_agent.php    # Security Engineer agent
│   ├── devops_agent.php      # DevOps Engineer agent
│   ├── architecture_agent.php # Architecture Analyst agent
│   └── documentation_agent.php # Documentation Engineer agent
├── scheduler/
│   └── run_scheduler.php     # Continuous scheduler
├── reports/                   # Agent work reports
├── logs/                      # Heartbeat & activity logs
├── state/                     # Agent state persistence
├── start.bat                  # Windows startup script
└── start.ps1                  # PowerShell startup script
```

## Starting the System

### Option 1: Windows Batch File

```
double-click start.bat
```

### Option 2: PowerShell

```powershell
cd C:\xampp\htdocs\apsdreamhome\agentic_dev_system
.\start.ps1
```

### Option 3: Command Line

```bash
cd /d C:\xampp\htdocs\apsdreamhome
php agentic_dev_system\scheduler\run_scheduler.php
```

### Option 4: OpenCode IDE Instructions

Just type in OpenCode IDE: _"Run the agentic dev system"_
The system will read this README and start working autonomously.

## What It Can Do Autonomously

- ✅ Find PHP syntax errors and fix them
- ✅ Detect SQL injection vulnerabilities and patch them
- ✅ Identify dead code and archive it safely
- ✅ Run E2E tests after every change
- ✅ Verify tenant isolation on all queries
- ✅ Auto-commit fixes with descriptive messages
- ✅ Check Ollama AI model availability for reasoning
- ✅ Scan for missing views, broken routes, orphaned files
- ✅ Audit archived files for safe removal
- ✅ Update AGENTS.md with progress and lessons learned

## Configuration

Edit `agentic_dev_system/config.json` to adjust:

- `ollama.model` - Which local AI model to use
- `scheduler.cycle_interval_ms` - How often to check for new tasks (default: 30s)
- `scheduler.max_cycles_per_run` - Max cycles before stopping (default: 999 = continuous)
- `scheduler.work_hours_start/end` - Only work during business hours (optional)

## State Persistence

The system saves its state in `agentic_dev_system/state/agent_state.json` so it can resume if interrupted.

## Logs

Activity log: `agentic_dev_system/logs/agent_heartbeat.log`
Scheduler log: `agentic_dev_system/logs/scheduler.log`

## Safety Features

- All file deletions follow the 7-step pre-deletion checklist
- AGENTS.md rules are always respected
- E2E tests run after every change (rollback if failed)
- No destructive operations without confirmation (unless auto-approve enabled)
- Git commits every change with descriptive messages
