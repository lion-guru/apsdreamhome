<?php

namespace App\Core\Agentic;

class FrontendAgent
{
    public string $id = 'frontend';
    public string $name = 'Frontend Engineer';
    public string $role = 'flutter_frontend';
    public string $description = 'Flutter UI/UX, page wiring, responsive fixes, widget dev';
    public array $tools = ['filesystem', 'bash', 'grep', 'sequential_thinking', 'playwright'];
    public int $concurrentTasks = 3;
}