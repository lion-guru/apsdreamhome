<?php

namespace App\Core\Agentic;

class ArchitectureAgent
{
    public string $id = 'architecture';
    public string $name = 'Architecture Analyst';
    public string $role = 'codebase_analysis';
    public string $description = 'Deep codebase analysis, dead code detection, architecture improvements';
    public array $tools = ['filesystem', 'bash', 'grep', 'sequential_thinking', 'research'];
    public int $concurrentTasks = 2;
}