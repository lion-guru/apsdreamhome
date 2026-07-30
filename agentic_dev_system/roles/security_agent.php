<?php

namespace App\Core\Agentic;

class SecurityAgent
{
    public string $id = 'security';
    public string $name = 'Security Engineer';
    public string $role = 'security_audit';
    public string $description = 'SQL injection audits, CSRF checks, auth hardening, tenant isolation';
    public array $tools = ['filesystem', 'bash', 'grep', 'sequential_thinking'];
    public int $concurrentTasks = 2;
}