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

## 4. 🌐 AI AGENT INTEGRATION & RESEARCH PROTOCOL (FREE CODER MODE)
This protocol outlines how TRAE will leverage various AI agents and research tools to optimize development, minimize token usage, and integrate diverse capabilities.

### 4.1. 🧠 AVAILABLE AI AGENTS
-   **Ollama (Local):** Prioritize for code generation, refactoring, and general coding assistance to save API tokens. Models configured in `c:\Users\abhay\.continue\config.yaml`.
-   **Gemini (Google API):** Use for complex problem-solving, advanced code generation, and when Ollama's capabilities are insufficient. Refer to `config/gemini_config.php` for settings.
-   **OpenAI (API):** Utilize for specific tasks where OpenAI models excel, if required.
-   **OpenRouter (API):** Use as a flexible gateway to various models, especially for cost-effective alternatives or specialized models.
-   **Hugging Face (API):** For tasks involving specific open-source models or when leveraging Hugging Face's ecosystem.
-   **Google Search (WebSearch Tool):** Primary tool for general knowledge, documentation, library research, and troubleshooting to avoid unnecessary AI model calls.
-   **"Open Coder Zen" (API):** Explore for additional coding assistance or specialized functions as needed.

### 4.2. 💡 TOKEN OPTIMIZATION STRATEGIES
-   **Prioritize Local:** Always attempt to use local Ollama models first for coding tasks.
-   **Strategic WebSearch:** Before querying an AI model, perform a `WebSearch` if the information is likely available online (e.g., syntax, common patterns, library usage).
-   **Concise Prompts:** Formulate clear and concise prompts for AI models to reduce input token usage.
-   **Iterative Refinement:** Break down complex AI tasks into smaller steps to manage output tokens and refine results iteratively.

### 4.3. 🛠️ RESEARCH & BROWSING TOOLS
-   **Web Search (Built-in Tool):** Use to find documentation, libraries, solutions, and real-time information.
-   **Fetch Content (MCP Tool):** Use to read the full content of documentation URLs found via search, saving context.
-   **Browsing (Puppeteer MCP Tool):** Use to verify UI changes, interact with complex web applications, or gather information from dynamic web pages if needed.

### 4.4. 💭 REASONING & PLANNING
-   **Internal Reasoning:** Utilize internal reasoning to plan complex refactors, architectural changes, and task breakdowns.
-   **Sequential Thinking:** (If available) Employ for multi-step problem-solving and logical deduction.

## 5. 🤝 WINDSURF COLLABORATION
-   If Windsurf is active, check `MASTER_PLAN.md` to see what it is working on.
-   Do not duplicate work. If `MASTER_PLAN.md` says "Windsurf is doing X", verify X is done before proceeding.
