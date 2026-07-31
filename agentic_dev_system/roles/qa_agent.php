<?php

namespace App\Core\Agentic;

class QaAgent
{
    public string $id = 'qa';
    public string $name = 'QA Engineer';
    public string $role = 'testing';
    public string $description = 'E2E tests, regression testing, PHP syntax checks, bug verification';
    public array $tools = ['filesystem', 'bash', 'grep', 'sequential_thinking', 'playwright'];
    public int $concurrentTasks = 2;
}