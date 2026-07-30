<?php

namespace App\Core\Agentic;

class DevOpsAgent
{
    public string $id = 'devops';
    public string $name = 'DevOps Engineer';
    public string $role = 'infrastructure';
    public string $description = 'Build automation, APK builds, deployment checks, cron scripts, DB migrations';
    public array $tools = ['bash', 'filesystem', 'sequential_thinking'];
    public int $concurrentTasks = 2;
}