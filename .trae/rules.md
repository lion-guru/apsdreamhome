# 🤖 TRAE AUTOMATION PROTOCOLS

## 1. 🕵️‍♂️ DEEP SCAN PROTOCOL
When asked to "scan" or "analyze":
1.  **Map Routes:** Read `routes/web.php` to understand *intended* behavior.
2.  **Verify Files:** Use `LS` on `app/Controllers` and `app/views` to verify *actual* existence.
3.  **Check Config:** Verify `config/database.php` and `config/constants.php`.
4.  **Update Context:** Write findings to `.trae/context.md`.

## 2. 🛠️ BUILD PROTOCOL
When asked to "build" or "fix":
1.  **Check Dependencies:** Do not use libraries (like Illuminate/Support) unless verified in `vendor`.
2.  **Follow MVC:**
    -   Controllers -> `app/Controllers/{Namespace}/NameController.php`
    -   Views -> `app/views/{folder}/name.php`
    -   Models -> `app/Models/{Name}.php`
3.  **No Blade:** All views must be `.php` and use `<?php echo $var; ?>` syntax.

## 3. 🧠 MEMORY PROTOCOL
-   **Read:** Always read `.trae/context.md` at the start of a complex task.
-   **Write:** Update `.trae/context.md` when a new major component (Controller/Model) is created.

## 4. 🌐 RESEARCH & SEARCH PROTOCOL (FREE CODER MODE)
-   **Web Search:** Use the built-in `WebSearch` tool to find documentation, libraries, or solutions.
-   **Fetch Content:** Use the `fetch` MCP tool to read the full content of documentation URLs found via search.
-   **Browsing:** Use `puppeteer` to verify UI changes or interact with complex web apps if needed.
-   **Reasoning:** Use `sequential-thinking` (if available) or internal reasoning to plan complex refactors.

## 5. 🤝 WINDSURF COLLABORATION
-   If Windsurf is active, check `MASTER_PLAN.md` to see what it is working on.
-   Do not duplicate work. If `MASTER_PLAN.md` says "Windsurf is doing X", verify X is done before proceeding.
