<?php
namespace App\Services;

use PDO;

class ApiKeyConfigService
{
    private static $cache = null;

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'api_%' OR `key` LIKE 'ai_%'");
            self::$cache = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                self::$cache[$row['key']] = $row['value'];
            }
        } catch (\Throwable $e) {
            error_log('ApiKeyConfigService: ' . $e->getMessage());
            self::$cache = [];
        }
        return self::$cache;
    }

    public static function getProviderConfig(string $provider): array
    {
        $all = self::all();
        return [
            'name'     => $all["ai_provider_{$provider}_name"] ?? $provider,
            'url'      => $all["ai_provider_{$provider}_url"] ?? null,
            'key'      => $all["api_{$provider}_key"] ?? null,
            'models'   => isset($all["ai_provider_{$provider}_models"]) ? explode(',', $all["ai_provider_{$provider}_models"]) : [],
            'org_id'   => $all["api_{$provider}_org_id"] ?? null,
        ];
    }

    public static function getOllamaUrl(): string
    {
        return self::get('ai_provider_ollama_url', 'http://localhost:11434/v1');
    }

    public static function getGroqKey(): ?string
    {
        return self::get('api_groq_key');
    }

    public static function getOpenRouterKey(): ?string
    {
        return self::get('api_openrouter_key');
    }

    public static function getGeminiKey(): ?string
    {
        return self::get('api_gemini_key');
    }

    public static function getAnthropicKey(): ?string
    {
        return self::get('api_anthropic_key');
    }

    public static function getOpenAIKey(): ?string
    {
        return self::get('api_openai_key');
    }

    public static function getDefaultProvider(): string
    {
        return self::get('ai_default_provider', 'ollama');
    }

    public static function getFallbackProvider(): string
    {
        return self::get('ai_fallback_provider', 'groq');
    }

    public static function getMaxTokens(): int
    {
        return (int) self::get('ai_max_tokens', '4096');
    }

    public static function getTemperature(): float
    {
        return (float) self::get('ai_temperature', '0.7');
    }

    public static function set(string $key, string $value): bool
    {
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
            $ok = $stmt->execute([$key, $value]);
            self::$cache = null;
            return $ok;
        } catch (\Throwable $e) {
            error_log('ApiKeyConfigService::set: ' . $e->getMessage());
            return false;
        }
    }

    public static function flushCache(): void
    {
        self::$cache = null;
    }
}
