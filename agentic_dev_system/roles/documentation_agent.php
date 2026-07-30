<?php

namespace App\Core\Agentic;

class DocumentationAgent
{
    public string $id = 'docs';
    public string $name = 'Documentation Engineer';
    public string $role = 'documentation';
    public string $description = 'AGENTS.md updates, changelog, lesson capture, docs generation';
    public array $tools = ['filesystem', 'bash', 'sequential_thinking'];
    public int $concurrentTasks = 1;
}