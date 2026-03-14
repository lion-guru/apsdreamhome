# 🤖 MCP AUTO-EXECUTION RULES

## 🎯 OBJECTIVE
Maximize context efficiency and autonomous operation using installed MCP servers.

## 🛠️ AVAILABLE MCP SERVERS

### 1. 🧠 Memory Server (Priority: HIGH)
- **Use for:** Storing project decisions, user preferences, and architectural patterns.
- **Trigger:** When user says "remember this", "save context", or after completing a major task.
- **Action:** Use `memory_store` to save key-value pairs.

### 2. 🗄️ MySQL Server (Priority: HIGH)
- **Use for:** Inspecting database schema, checking data integrity.
- **Trigger:** When dealing with Models, Migrations, or SQL errors.
- **Action:** Use `mysql_query` to inspect tables instead of asking user.

### 3. 📂 Filesystem Server (Priority: MEDIUM)
- **Use for:** Deep file operations and structure analysis.
- **Trigger:** When analyzing project structure.
- **Action:** Use `filesystem_list` or `filesystem_read` for deep scans.

### 4. 🌐 Puppeteer Server (Priority: MEDIUM)
- **Use for:** Testing UI, taking screenshots of errors.
- **Trigger:** When user asks to "test UI", "check layout", or "verify fix".
- **Action:** Use `puppeteer_navigate` and `puppeteer_screenshot`.

## 🔄 AUTOMATION WORKFLOW

1.  **Start of Session:**
    -   Read `MASTER_PLAN.md`.
    -   Check `verify-progress.php` status.
    -   Load critical context from Memory Server.

2.  **During Development:**
    -   If a file is missing -> Check Memory if it was deleted intentionally.
    -   If a bug occurs -> Use MySQL/Filesystem to debug before asking user.

3.  **End of Session:**
    -   Update `MASTER_PLAN.md`.
    -   Store new patterns in Memory Server.

## 🚫 RESTRICTIONS
-   Do NOT store sensitive passwords in Memory.
-   Do NOT run destructive SQL queries (`DROP`, `DELETE`) without approval.
