<?php
// Idempotent: seed the AI Command Center sidebar items into admin_menu_items (S538).
// Ordered so the ai_tech section stays consistent; safe to re-run.

$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
]);

function seedMenuItem(PDO $pdo, array $item): string {
    $check = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ? LIMIT 1");
    $check->execute([$item['url']]);
    if ($check->fetchColumn()) {
        return "SKIP {$item['url']} (exists)";
    }
    $stmt = $pdo->prepare(
        "INSERT INTO admin_menu_items
            (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active)
         VALUES (?, ?, ?, ?, NULL, ?, ?, ?, 1)"
    );
    $stmt->execute([
        $item['tenant_id']    ?? 1,
        $item['name'],
        $item['icon'],
        $item['url'],
        $item['section']      ?? 'ai_tech',
        $item['order_index'],
        $item['permission_key'] ?? NULL,
    ]);
    return "SEEDED {$item['url']}";
}

$items = [
    ['url' => '/admin/ai/hub', 'name' => 'AI Command Center', 'icon' => 'fas fa-robot', 'order_index' => 205],
    ['url' => '/admin/ai-analytics/insights', 'name' => 'AI Insights', 'icon' => 'fas fa-lightbulb', 'order_index' => 206],
    ['url' => '/admin/ai-management', 'name' => 'AI Management', 'icon' => 'fas fa-cogs', 'order_index' => 207],
    ['url' => '/admin/ai-management/integrations', 'name' => 'AI Integrations', 'icon' => 'fas fa-plug', 'order_index' => 208],
    ['url' => '/admin/ai-management/lead-scores', 'name' => 'AI Lead Scores', 'icon' => 'fas fa-chart-bar', 'order_index' => 209],
    ['url' => '/admin/ai-management/chatbot-logs', 'name' => 'AI Chatbot Logs', 'icon' => 'fas fa-comments', 'order_index' => 210],
    ['url' => '/admin/ai-management/context-memory', 'name' => 'AI Context Memory', 'icon' => 'fas fa-brain', 'order_index' => 211],
    ['url' => '/admin/ai-management/usage-analytics', 'name' => 'AI Usage & Cost', 'icon' => 'fas fa-chart-pie', 'order_index' => 212],
];

foreach ($items as $item) {
    echo seedMenuItem($pdo, $item) . "\n";
}
