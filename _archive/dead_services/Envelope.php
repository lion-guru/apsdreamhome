<?php

namespace App\Services;

/**
 * Envelope â€” standardized return-format helper for services + API endpoints.
 *
 * Convention used across the codebase:
 *   {
 *     "success": bool,
 *     "data":    <payload> | null,
 *     "error":   string | null,
 *     "meta":    array        // pagination, counts, timing, etc.
 *   }
 *
 * Using this everywhere lets API consumers (mobile app, integrations,
 * internal AJAX) parse responses uniformly. Services that need to throw
 * a typed error can use the static factories (ok, fail, notFound, ...).
 *
 * The class is final + readonly so it cannot be mutated after creation
 * (envelope contents are immutable). Helper factories return new
 * instances on every call.
 */
final class Envelope
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public ?string $error = null,
        public array $meta = [],
    ) {
    }

    public static function ok(mixed $data = null, array $meta = []): self
    {
        return new self(true, $data, null, $meta);
    }

    public static function fail(string $error, mixed $data = null, array $meta = []): self
    {
        return new self(false, $data, $error, $meta);
    }

    public static function notFound(string $what = 'Resource', array $meta = []): self
    {
        return new self(false, null, $what . ' not found', $meta);
    }

    public static function forbidden(string $reason = 'Access denied', array $meta = []): self
    {
        return new self(false, null, $reason, $meta);
    }

    public static function unauthorized(string $reason = 'Not authenticated', array $meta = []): self
    {
        return new self(false, null, $reason, $meta);
    }

    public static function validation(array $errors, array $meta = []): self
    {
        return new self(false, null, 'Validation failed', array_merge($meta, ['errors' => $errors]));
    }

    public function toArray(): array
    {
        $out = [
            'success' => $this->success,
            'data'    => $this->data,
            'error'   => $this->error,
        ];
        if (!empty($this->meta)) {
            $out['meta'] = $this->meta;
        }
        return $out;
    }

    public function toJson(int $status = 200): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function send(int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo $this->toJson($status);
    }

    public function withMeta(array $extra): self
    {
        return new self($this->success, $this->data, $this->error, array_merge($this->meta, $extra));
    }
}?>