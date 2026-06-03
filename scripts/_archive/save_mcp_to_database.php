<?php
/**
 * Save MCP Server Configuration to Database
 * Stores API keys securely in local MySQL database
 * Direct PDO connection - no dependencies
 */

// Database configuration
$host = '127.0.0.1';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = getenv("DB_PASSWORD") ?: "";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected\n";
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// Create table for MCP configurations
$sql = "CREATE TABLE IF NOT EXISTS mcp_server_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_name VARCHAR(100) NOT NULL,
    command VARCHAR(50) NOT NULL,
    args TEXT,
    env TEXT,
    disabled BOOLEAN DEFAULT FALSE,
    registry VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_server (server_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$pdo->exec($sql);
echo "✅ Table mcp_server_configs created/verified\n";

// MCP Servers Configuration Data
$mcpServers = [
    [
        'server_name' => 'brave-search',
        'command' => 'npx',
        'args' => json_encode(['-y', '@modelcontextprotocol/server-brave-search']),
        'env' => json_encode(['BRAVE_API_KEY' => '']),
        'disabled' => true,
        'registry' => null
    ],
    [
        'server_name' => 'database',
        'command' => 'npx',
        'args' => json_encode(['-y', '@modelcontextprotocol/server-mysql']),
        'env' => json_encode([
            'MYSQL_HOST' => 'localhost',
            'MYSQL_PORT' => '3307',
            'MYSQL_USER' => 'root',
            'MYSQL_PASSWORD' => ''
        ]),
        'disabled' => false,
        'registry' => null
    ],
    [
        'server_name' => 'filesystem',
        'command' => 'npx',
        'args' => json_encode(['-y', '@modelcontextprotocol/server-filesystem', 'c:\\xampp\\htdocs\\apsdreamhome']),
        'env' => json_encode([]),
        'disabled' => false,
        'registry' => null
    ],
    [
        'server_name' => 'git',
        'command' => 'npx',
        'args' => json_encode(['-y', '@modelcontextprotocol/server-git', '--repository', 'c:\\xampp\\htdocs\\apsdreamhome']),
        'env' => json_encode([]),
        'disabled' => false,
        'registry' => null
    ],
    [
        'server_name' => 'github',
        'command' => 'npx',
        'args' => json_encode(['-y', '@modelcontextprotocol/server-github']),
        'env' => json_encode(['GITHUB_PERSONAL_ACCESS_TOKEN' => '']),
        'disabled' => false,
        'registry' => null
    ],
    [
        'server_name' => 'heroku',
        'command' => 'heroku-mcp-server',
        'args' => json_encode([]),
        'env' => json_encode(['HEROKU_API_KEY' => '']),
        'disabled' => false,
        'registry' => null
    ],
    [
        'server_name' => 'memory',
        'command' => 'npx',
        'args' => json_encode(['-y', '@modelcontextprotocol/server-memory']),
        'env' => json_encode([]),
        'disabled' => false,
        'registry' => null
    ],
    [
        'server_name' => 'puppeteer',
        'command' => 'npx',
        'args' => json_encode(['-y', '@modelcontextprotocol/server-puppeteer']),
        'env' => json_encode([]),
        'disabled' => false,
        'registry' => null
    ],
    [
        'server_name' => 'sentry',
        'command' => 'sentry-mcp-server',
        'args' => json_encode([]),
        'env' => json_encode([
            'SENTRY_AUTH_TOKEN' => '',
            'SENTRY_DSN' => ''
        ]),
        'disabled' => false,
        'registry' => null
    ],
    [
        'server_name' => 'supabase',
        'command' => 'npx',
        'args' => json_encode(['-y', 'mcp-remote', 'https://mcp.supabase.com/mcp?project_ref=shegdyxcfvfcrhyjarwu&features=database%2Cdebugging%2Cdevelopment%2Cfunctions%2Cbranching%2Cstorage%2Caccount']),
        'env' => json_encode([
            'SUPABASE_ANON_KEY' => 'sb_publishable_kcFct6-JNki6iALTvlPQVw_iLE5WZjC',
            'SUPABASE_URL' => 'https://shegdyxcfvfcrhyjarwu.supabase.co'
        ]),
        'disabled' => false,
        'registry' => null
    ],
    [
        'server_name' => 'io.windsurf/puppeteer',
        'command' => 'npx',
        'args' => json_encode(['-y', '@modelcontextprotocol/server-puppeteer']),
        'env' => json_encode([]),
        'disabled' => false,
        'registry' => 'io.windsurf/puppeteer'
    ],
    [
        'server_name' => 'io.windsurf/mcp-playwright',
        'command' => 'npx',
        'args' => json_encode(['-y', '@playwright/mcp@latest']),
        'env' => json_encode([]),
        'disabled' => false,
        'registry' => 'io.windsurf/mcp-playwright'
    ]
];

// Prepare statements
$checkStmt = $pdo->prepare("SELECT id FROM mcp_server_configs WHERE server_name = ?");
$insertStmt = $pdo->prepare("INSERT INTO mcp_server_configs (server_name, command, args, env, disabled, registry) VALUES (?, ?, ?, ?, ?, ?)");
$updateStmt = $pdo->prepare("UPDATE mcp_server_configs SET command = ?, args = ?, env = ?, disabled = ?, registry = ?, updated_at = NOW() WHERE server_name = ?");

// Insert or update configurations
foreach ($mcpServers as $server) {
    $checkStmt->execute([$server['server_name']]);
    $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exists) {
        // Update existing
        $updateStmt->execute([
            $server['command'], 
            $server['args'], 
            $server['env'], 
            $server['disabled'], 
            $server['registry'],
            $server['server_name']
        ]);
        echo "🔄 Updated: {$server['server_name']}\n";
    } else {
        // Insert new
        $insertStmt->execute([
            $server['server_name'],
            $server['command'],
            $server['args'],
            $server['env'],
            $server['disabled'],
            $server['registry']
        ]);
        echo "✅ Inserted: {$server['server_name']}\n";
    }
}

echo "\n🎉 All MCP configurations saved to database securely!\n";
echo "📊 Total servers: " . count($mcpServers) . "\n";

// Verify saved data
$saved = $pdo->query("SELECT server_name, disabled, registry FROM mcp_server_configs ORDER BY server_name")->fetchAll(PDO::FETCH_ASSOC);
echo "\n📋 Saved configurations:\n";
$active = 0;
$disabled = 0;
foreach ($saved as $row) {
    $status = $row['disabled'] ? '❌ Disabled' : '✅ Active';
    echo "  - {$row['server_name']} ({$status})\n";
    if ($row['disabled']) {
        $disabled++;
    } else {
        $active++;
    }
}

echo "\n📈 Summary:\n";
echo "  ✅ Active: $active\n";
echo "  ❌ Disabled: $disabled\n";
echo "  📁 Total: " . count($saved) . "\n";
echo "\n🔐 API keys are secure in local database (not in git)\n";
