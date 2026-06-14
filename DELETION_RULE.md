# File Deletion Rule — Mandatory Pre-Deletion Checklist

**Status:** ACTIVE — All agents must follow before deleting ANY file.

## Background
Commission plan manager (769 lines of real CRUD) was deleted prematurely because it was labeled "orphaned dead". It had real functionality (`mlm_commission_plans` CRUD, default level creation, plan activation/deactivation) that was never reimplemented. This cost significant time to rebuild.

---

## RULE: NEVER DELETE without completing ALL 7 checks

### Step 1: What does this file DO?
- Read the entire file. Understand its purpose, functions, classes, methods.
- Write a 1-line summary: "This file handles X for Y."

### Step 2: Is ANY part of it reimplemented?
- Search codebase for the SAME functionality (not just same filename).
- Check if a controller/service/view covers the same features.
- If even 1 feature is NOT reimplemented → DO NOT DELETE. Mark as "partially covered" instead.

### Step 3: Is it referenced ANYWHERE?
- Search routes (`routes/web.php`, `routes/api.php`)
- Search ALL controllers (`app/Http/Controllers/`)
- Search ALL views (`app/views/`)
- Search ALL services (`app/Services/`)
- Search `admin_menu_items` DB table
- Search sidebar includes, layout references
- A file with ANY reference = NOT orphaned

### Step 4: Can it be reached via URL?
- Check if any route maps to this file's path
- Check if any controller `render()` or `include`s it
- Check if any layout/sidebar/menu links to it
- Even legacy/old routes count as "reachable"

### Step 5: Does it have DATA in DB?
- If it reads/writes specific tables, check if those tables have data
- Example: commission_plan_manager reads `mlm_commission_plans` (5 rows) — NOT dead
- Empty tables don't mean dead code — they may need seeding

### Step 6: What breaks if we delete it?
- Trace all downstream effects
- Check if any other file depends on output/classes/functions from this file
- Check if deleting it leaves an orphaned route that 404s/500s

### Step 7: Make the call
- ALL 6 checks passed AND no real functionality → SAFE to delete
- ANY check fails → DO NOT DELETE. Instead:
  - If MVC needs it: create proper controller/view replacement FIRST
  - If legacy: move to `_archive/` directory (not delete)
  - If stub: keep but add `// TODO: implement` comment

---

## Safe Deletion Categories (skip full checklist)
| Category | Example | Safe? |
|----------|---------|-------|
| Cache files | `storage/cache/*.cache` | YES |
| Temp scripts | `_archive/` contents | YES |
| Test artifacts | `testing/*.png`, `*.tmp` | YES |
| Build artifacts | `node_modules/`, `vendor/` | YES |
| IDE config | `.vscode/`, `.idea/` | YES |
| Empty directories | `app/views/empty_folder/` | YES |

## NEVER Delete (always check first)
| Category | Example | Action |
|----------|---------|--------|
| View files with controller references | Views rendered by `->render()` | Check controller |
| Routes with controller methods | Any `Controller@method` | Check if method exists |
| Service classes | `app/Services/*.php` | Check if used by controller |
| Config files | `config/*.php` | Check if required elsewhere |
| Migration scripts | `scripts/*.php` | Keep for audit trail |
| Helper files | `app/Helpers/*.php` | Check if function called anywhere |

---

## Emergency Restore Command
If a file was deleted by mistake:
```bash
# Find the commit that deleted it
git log --diff-filter=D --name-only --oneline | grep "filename"

# Restore from last commit before deletion
git checkout <commit-hash>~1 -- path/to/file

# Or restore from git history (most recent version)
git log --all --diff-filter=A -- "path/to/file" --format="%H" -1 | xargs -I {} git checkout {} -- path/to/file
```

---

## Enforcement
- Pre-commit hook (if implemented) should flag deletions without checklist completion
- When in doubt, MOVE to `_archive/` instead of DELETE
- `_archive/` is recoverable; `git rm` is much harder to undo
