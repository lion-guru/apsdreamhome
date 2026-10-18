<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\ExecutiveAIService;

$service = new ExecutiveAIService();

// Test executeAction
$result = $service->executeAction('view_report', ['type' => 'sales', 'period' => 'month'], 'ceo', 1);
echo "view_report: ";
print_r($result);

$result = $service->executeAction('assign_lead', ['lead_id' => 999, 'assignee_id' => 1], 'sales_manager', 1);
echo "assign_lead (invalid lead): ";
print_r($result);

$result = $service->executeAction('create_task', ['title' => 'Test Task', 'assignee_id' => 1, 'due_date' => '2026-10-20'], 'coo', 1);
echo "create_task: ";
print_r($result);