<?php
/**
 * MCP Configuration Loader
 * Loads MCP server configurations from .env file
 * Secure - uses environment variables, not committed to git
 */

class MCPConfigLoader
{
    private static $config = null;

    /**
     * Load MCP server configurations from environment
     * @return array
     */
    public static function load(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        // Load .env file if not already loaded
        self::loadEnv();

        self::$config = [
            'mcpServers' => [
                'brave-search' => [
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-brave-search'],
                    'env' => [
                        'BRAVE_API_KEY' => getenv('MCP_BRAVE_API_KEY') ?: ''
                    ],
                    'disabled' => empty(getenv('MCP_BRAVE_API_KEY'))
                ],
                'database' => [
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-mysql'],
                    'env' => [
                        'MYSQL_HOST' => getenv('DB_HOST') ?: 'localhost',
                        'MYSQL_PORT' => getenv('DB_PORT') ?: '3307',
                        'MYSQL_USER' => getenv('DB_USERNAME') ?: 'root',
                        'MYSQL_PASSWORD' => getenv('DB_PASSWORD') ?: ''
                    ],
                    'disabled' => false
                ],
                'filesystem' => [
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-filesystem', 'c:\\xampp\\htdocs\\apsdreamhome'],
                    'disabled' => false
                ],
                'git' => [
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-git', '--repository', 'c:\\xampp\\htdocs\\apsdreamhome'],
                    'disabled' => false
                ],
                'github' => [
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-github'],
                    'env' => [
                        'GITHUB_PERSONAL_ACCESS_TOKEN' => getenv('MCP_GITHUB_TOKEN') ?: ''
                    ],
                    'disabled' => empty(getenv('MCP_GITHUB_TOKEN'))
                ],
                'heroku' => [
                    'command' => 'heroku-mcp-server',
                    'env' => [
                        'HEROKU_API_KEY' => getenv('MCP_HEROKU_API_KEY') ?: ''
                    ],
                    'disabled' => empty(getenv('MCP_HEROKU_API_KEY'))
                ],
                'memory' => [
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-memory'],
                    'disabled' => false
                ],
                'puppeteer' => [
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-puppeteer'],
                    'disabled' => false
                ],
                'sentry' => [
                    'command' => 'sentry-mcp-server',
                    'env' => [
                        'SENTRY_AUTH_TOKEN' => getenv('MCP_SENTRY_AUTH_TOKEN') ?: '',
                        'SENTRY_DSN' => getenv('MCP_SENTRY_DSN') ?: ''
                    ],
                    'disabled' => empty(getenv('MCP_SENTRY_AUTH_TOKEN'))
                ],
                'supabase' => [
                    'command' => 'npx',
                    'args' => ['-y', 'mcp-remote', 'https://mcp.supabase.com/mcp?project_ref=shegdyxcfvfcrhyjarwu&features=database%2Cdebugging%2Cdevelopment%2Cfunctions%2Cbranching%2Cstorage%2Caccount'],
                    'env' => [
                        'SUPABASE_URL' => getenv('MCP_SUPABASE_URL') ?: '',
                        'SUPABASE_ANON_KEY' => getenv('MCP_SUPABASE_ANON_KEY') ?: ''
                    ],
                    'disabled' => empty(getenv('MCP_SUPABASE_ANON_KEY'))
                ],
                'io.windsurf/puppeteer' => [
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-puppeteer'],
                    'registry' => 'io.windsurf/puppeteer',
                    'disabled' => false
                ],
                'io.windsurf/mcp-playwright' => [
                    'command' => 'npx',
                    'args' => ['-y', '@playwright/mcp@latest'],
                    'registry' => 'io.windsurf/mcp-playwright',
                    'disabled' => false
                ]
            ]
        ];

        return self::$config;
    }

    /**
     * Get a specific MCP server configuration
     * @param string $serverName
     * @return array|null
     */
    public static function getServer(string $serverName): ?array
    {
        $config = self::load();
        return $config['mcpServers'][$serverName] ?? null;
    }

    /**
     * Check if a server is enabled
     * @param string $serverName
     * @return bool
     */
    public static function isEnabled(string $serverName): bool
    {
        $server = self::getServer($serverName);
        return $server !== null && empty($server['disabled']);
    }

    /**
     * Get list of active servers
     * @return array
     */
    public static function getActiveServers(): array
    {
        $config = self::load();
        $active = [];

        foreach ($config['mcpServers'] as $name => $server) {
            if (empty($server['disabled'])) {
                $active[] = $name;
            }
        }

        return $active;
    }

    /**
     * Load .env file
     */
    private static function loadEnv(): void
    {
        $envFile = __DIR__ . '/../.env';

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos($line, '#') === 0) {
                continue;
            }

            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                    (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                    $value = substr($value, 1, -1);
                }

                // Set environment variable if not already set
                if (getenv($key) === false) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }
    }
}

// Example usage when called directly
if (php_sapi_name() === 'cli' && basename($argv[0]) === 'MCPConfigLoader.php') {
    echo "ðŸ”§ MCP Configuration Loader\n";
    echo "============================\n\n";

    $config = MCPConfigLoader::load();
    $active = MCPConfigLoader::getActiveServers();

    echo "ðŸ“Š Configuration Summary:\n";
    echo "Total servers: " . count($config['mcpServers']) . "\n";
    echo "Active servers: " . count($active) . "\n\n";

    echo "âœ… Active Servers:\n";
    foreach ($active as $server) {
        echo "  - $server\n";
    }

    echo "\nâ�Œ Disabled Servers (missing API keys):\n";
    foreach ($config['mcpServers'] as $name => $server) {
        if (!empty($server['disabled'])) {
            echo "  - $name\n";
        }
    }

    echo "\nðŸ”� All API keys are loaded from .env (secure)\n";
}?>