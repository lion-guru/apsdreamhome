import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { z } from "zod";

const OLLAMA = process.env.OLLAMA_URL || "http://localhost:11434";
const FAST_MODEL = "qwen2.5-coder:1.5b";
const SMART_MODEL = process.env.OLLAMA_SMART_MODEL || "qwen2.5-coder:1.5b";

// Project context - APS Dream Home specific
const PROJECT_CONTEXT = `You are an expert PHP developer working on APS Dream Home - a Real Estate + MLM platform.
Tech stack: Custom PHP MVC (NOT Laravel), MySQL, TailwindCSS, Chart.js, Vanilla JS.
Key patterns:
- Controllers extend app/Core/Controller.php
- Models use app/Core/Database.php (PDO wrapper)
- Views are pure PHP in app/views/
- Routes in routes/web.php and routes/api.php
- Services in app/Services/ for business logic
- Always use prepared statements, never raw SQL
- Follow PSR-12 coding standards
- Security: CSRF tokens, input sanitization, Argon2ID passwords`;

async function ollamaCall(model, prompt, system = "", options = {}) {
  const body = {
    model,
    prompt,
    stream: false,
    options: {
      temperature: options.temperature ?? 0.2,
      num_predict: options.num_predict ?? 2048,
      top_p: 0.9,
      repeat_penalty: 1.1,
      ...options
    }
  };
  if (system) body.system = system;

  const res = await fetch(`${OLLAMA}/api/generate`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body)
  });

  if (!res.ok) throw new Error(`Ollama error: ${res.status}`);
  const data = await res.json();
  return data.response || "";
}

const server = new McpServer({ name: "ollama-beast", version: "2.0.0" });

// ============================================================
// TOOL 1: Smart Code Generator (Project-aware)
// ============================================================
server.tool(
  "ollama_code",
  "Generate PHP/JS/SQL code for APS Dream Home project. Project-aware, follows existing patterns.",
  {
    task: z.string().describe("What to build: e.g. 'create a LeadController with CRUD methods'"),
    file_type: z.enum(["php", "js", "sql", "css", "json", "blade"]).default("php"),
    context: z.string().optional().describe("Existing code or file content for context"),
    smart: z.boolean().default(false).describe("Use smarter generation (slower but better)")
  },
  async ({ task, file_type, context, smart }) => {
    const model = smart ? SMART_MODEL : FAST_MODEL;
    const system = `${PROJECT_CONTEXT}\nGenerate ONLY ${file_type.toUpperCase()} code. No explanations. Just clean, working code.`;
    const prompt = context
      ? `Existing code:\n\`\`\`${file_type}\n${context}\n\`\`\`\n\nTask: ${task}`
      : `Task: ${task}`;

    const code = await ollamaCall(model, prompt, system, { temperature: 0.1 });
    // Strip markdown fences if present
    const clean = code.replace(/^```[\w]*\n?/gm, "").replace(/```$/gm, "").trim();
    return { content: [{ type: "text", text: clean }] };
  }
);

// ============================================================
// TOOL 2: Code Reviewer
// ============================================================
server.tool(
  "ollama_review",
  "Review PHP/JS code for bugs, security issues, and improvements. Returns detailed analysis.",
  {
    code: z.string().describe("Code to review"),
    focus: z.enum(["security", "performance", "bugs", "all"]).default("all")
  },
  async ({ code, focus }) => {
    const system = `${PROJECT_CONTEXT}\nYou are a senior code reviewer. Be concise and specific. List issues with line references.`;
    const prompt = `Review this code for ${focus} issues:\n\`\`\`\n${code}\n\`\`\`\n\nFormat: Issue | Severity | Fix`;
    const result = await ollamaCall(FAST_MODEL, prompt, system, { temperature: 0.3 });
    return { content: [{ type: "text", text: result }] };
  }
);

// ============================================================
// TOOL 3: SQL Generator
// ============================================================
server.tool(
  "ollama_sql",
  "Generate optimized MySQL queries for APS Dream Home database. Knows the schema patterns.",
  {
    request: z.string().describe("What data you need: e.g. 'get all active leads with agent name'"),
    table_hints: z.string().optional().describe("Relevant table names or schema snippet")
  },
  async ({ request, table_hints }) => {
    const system = `You are a MySQL expert. Database: apsdreamhome (Real Estate + MLM platform).
Common tables: users, leads, properties, plots, colonies, commissions, mlm_network, bookings, employees.
Generate ONLY the SQL query. Use proper JOINs, indexes, LIMIT. Always use prepared statement placeholders (?).`;
    const prompt = table_hints
      ? `Schema hint:\n${table_hints}\n\nGenerate SQL for: ${request}`
      : `Generate SQL for: ${request}`;

    const sql = await ollamaCall(FAST_MODEL, prompt, system, { temperature: 0.1, num_predict: 512 });
    const clean = sql.replace(/^```[\w]*\n?/gm, "").replace(/```$/gm, "").trim();
    return { content: [{ type: "text", text: clean }] };
  }
);

// ============================================================
// TOOL 4: Fix/Debug Code
// ============================================================
server.tool(
  "ollama_fix",
  "Fix bugs, errors or broken code. Pass the error message + code and get a fixed version.",
  {
    code: z.string().describe("Broken code"),
    error: z.string().optional().describe("Error message or description of the bug"),
    language: z.string().default("php")
  },
  async ({ code, error, language }) => {
    const system = `${PROJECT_CONTEXT}\nFix the code. Return ONLY the corrected code, no explanations.`;
    const prompt = error
      ? `Error: ${error}\n\nCode:\n\`\`\`${language}\n${code}\n\`\`\`\n\nFixed code:`
      : `Fix this ${language} code:\n\`\`\`${language}\n${code}\n\`\`\`\n\nFixed code:`;

    const fixed = await ollamaCall(FAST_MODEL, prompt, system, { temperature: 0.1 });
    const clean = fixed.replace(/^```[\w]*\n?/gm, "").replace(/```$/gm, "").trim();
    return { content: [{ type: "text", text: clean }] };
  }
);

// ============================================================
// TOOL 5: Explain Code
// ============================================================
server.tool(
  "ollama_explain",
  "Explain what a piece of code does in simple terms. Useful for understanding complex logic.",
  {
    code: z.string().describe("Code to explain"),
    detail: z.enum(["brief", "full"]).default("brief")
  },
  async ({ code, detail }) => {
    const system = "You are a senior developer. Explain code clearly and concisely.";
    const prompt = detail === "brief"
      ? `In 2-3 sentences, what does this code do?\n\`\`\`\n${code}\n\`\`\``
      : `Explain this code in detail - purpose, logic flow, and any gotchas:\n\`\`\`\n${code}\n\`\`\``;

    const result = await ollamaCall(FAST_MODEL, prompt, system, { temperature: 0.4 });
    return { content: [{ type: "text", text: result }] };
  }
);

// ============================================================
// TOOL 6: Auto-complete / Fill-in-the-middle
// ============================================================
server.tool(
  "ollama_complete",
  "Complete partial code. Give code before cursor and optionally after cursor.",
  {
    prefix: z.string().describe("Code before the cursor / completion point"),
    suffix: z.string().optional().describe("Code after cursor (for fill-in-middle)"),
    language: z.string().default("php")
  },
  async ({ prefix, suffix, language }) => {
    const system = `Complete the ${language} code. Return ONLY the completion, not the whole file.`;
    const prompt = suffix
      ? `Complete the ${language} code between <FILL> markers:\n${prefix}<FILL>${suffix}`
      : `Continue this ${language} code:\n${prefix}`;

    const completion = await ollamaCall(FAST_MODEL, prompt, system, {
      temperature: 0.1,
      num_predict: 512
    });
    const clean = completion.replace(/^```[\w]*\n?/gm, "").replace(/```$/gm, "").trim();
    return { content: [{ type: "text", text: clean }] };
  }
);

// ============================================================
// TOOL 7: Test Generator
// ============================================================
server.tool(
  "ollama_tests",
  "Generate PHPUnit tests for a class or function.",
  {
    code: z.string().describe("PHP class or function to test"),
    class_name: z.string().optional().describe("Class name for the test file")
  },
  async ({ code, class_name }) => {
    const system = `${PROJECT_CONTEXT}\nGenerate PHPUnit 9 tests. Use PHPUnit assertions. Mock dependencies with Mockery or PHPUnit mocks.`;
    const prompt = `Generate comprehensive PHPUnit tests for:\n\`\`\`php\n${code}\n\`\`\``;
    const tests = await ollamaCall(SMART_MODEL, prompt, system, { temperature: 0.2, num_predict: 2048 });
    const clean = tests.replace(/^```[\w]*\n?/gm, "").replace(/```$/gm, "").trim();
    return { content: [{ type: "text", text: clean }] };
  }
);

// ============================================================
// TOOL 8: Refactor
// ============================================================
server.tool(
  "ollama_refactor",
  "Refactor code for better readability, performance or design patterns.",
  {
    code: z.string().describe("Code to refactor"),
    goal: z.string().default("clean code, better naming, extract methods, remove duplication")
  },
  async ({ code, goal }) => {
    const system = `${PROJECT_CONTEXT}\nRefactor the code. Return ONLY the refactored code.`;
    const prompt = `Refactor for: ${goal}\n\nCode:\n\`\`\`php\n${code}\n\`\`\`\n\nRefactored:`;
    const result = await ollamaCall(SMART_MODEL, prompt, system, { temperature: 0.2, num_predict: 2048 });
    const clean = result.replace(/^```[\w]*\n?/gm, "").replace(/```$/gm, "").trim();
    return { content: [{ type: "text", text: clean }] };
  }
);

// ============================================================
// TOOL 9: Chat (general coding Q&A)
// ============================================================
server.tool(
  "ollama_chat",
  "Ask any coding question. General purpose Q&A for the project.",
  {
    question: z.string().describe("Your question"),
    context: z.string().optional().describe("Additional context")
  },
  async ({ question, context }) => {
    const system = `${PROJECT_CONTEXT}\nAnswer concisely and practically.`;
    const prompt = context ? `Context: ${context}\n\nQuestion: ${question}` : question;
    const result = await ollamaCall(FAST_MODEL, prompt, system, { temperature: 0.5 });
    return { content: [{ type: "text", text: result }] };
  }
);

// ============================================================
// TOOL 10: Model info
// ============================================================
server.tool(
  "ollama_models",
  "List all available local Ollama models with details",
  {},
  async () => {
    const res = await fetch(`${OLLAMA}/api/tags`);
    const data = await res.json();
    const list = data.models.map(m =>
      `• ${m.name} | ${m.details.parameter_size} | ${m.details.quantization_level}`
    ).join("\n");
    return { content: [{ type: "text", text: `Available models:\n${list}` }] };
  }
);

const transport = new StdioServerTransport();
await server.connect(transport);
