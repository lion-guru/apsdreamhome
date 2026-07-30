<?php

namespace App\Core\Agentic;

class BackendAgent
{
    public string $id = 'backend';
    public string $name = 'Backend Engineer';
    public string $role = 'php_backend';
    public array $tools = ['filesystem', 'bash', 'grep', 'sequential_thinking'];
    public int $concurrentTasks = 3;
    public string $description = 'PHP/MVC backend dev, controller fixes, service layer improvements, SQL fixes';

    public function getPrompt(string $task): string
    {
        return "You are the Backend Engineer agent for APS Dream Home (custom PHP MVC framework). 

Task: {$task}

Rules:
1. Use raw PDO with prepared statements for all SQL
2. Use TenantContext::getId() or TenantScopeService for tenant scoping
3. Follow the patterns in TenantAwareTrait
4. Run php -l on all modified files before finishing
5. Check AGENTS.md for coding conventions
6. Always verify changes with E2E tests (153/153 passing target)
7. Use (int) cast for all user IDs in SQL queries
8. Never use string interpolation for SQL values
9. Check _archive before deleting any file (3-pass safety)
10. Commit every change with descriptive messages";
    }
}