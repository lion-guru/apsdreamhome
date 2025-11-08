<?php

namespace App\Core\Http;

use DateTimeInterface;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;
use JsonSerializable;

/**
 * HTTP Response class
 * 
 * This class represents an HTTP response. It provides methods to set the response status code,
 * headers, and content. It also includes support for HTTP caching headers (ETag, Last-Modified, Cache-Control).
 * 
 * Example usage:
 * ```php
 * // Create a new response with content and status code
 * $response = new Response('Hello, World!', 200);
 * 
 * // Set response headers
 * $response->setHeader('Content-Type', 'text/plain');
 * 
 * // Set caching headers
 * $response->setEtag('unique-content-hash');
 * $response->setLastModified(new DateTime('2023-01-01'));
 * $response->setPublic();
 * $response->setMaxAge(3600);
 * 
 * // Send the response
 * $response->send();
 * ```
 */
use SplFileInfo;
use SplFileObject;
use App\Core\Http\Request;

/**
 * Class Response
 * 
 * @package App\Core\Http
 */
class Response implements \JsonSerializable {
    /**
     * The response content
     * 
     * @var mixed The response content (string, array, or any other type that can be cast to string)
     */
    protected mixed $content = '';
    
    /**
     * A callback for streaming the response
     * 
     * @var callable|null A callback that will be called when the response is sent.
     *                    The callback should output content directly.
     */
    protected $streamedCallback = null;
    
    /**
     * The HTTP status code
     * 
     * @var int The HTTP status code (e.g., 200, 404, 500)
     */
    protected int $statusCode = 200;
    
    /**
     * The HTTP status text
     * 
     * @var string The HTTP status text (e.g., 'OK', 'Not Found')
     */
    protected string $statusText = 'OK';
    
    /**
     * The response headers
     * 
     * @var array<string, array<string>> The response headers as an associative array
     * where keys are header names and values are arrays of header values
     */
    protected array $headers = [];
    
    /**
     * The response cookies
     * 
     * @var array<string, array> The response cookies as an associative array
     * where keys are cookie names and values are arrays of cookie options
     */
    protected array $cookies = [];
    
    /**
     * The response charset
     * 
     * @var string The response charset (default: 'UTF-8')
     */
    protected string $charset = 'UTF-8';
    
    /**
     * The response content type
     * 
     * @var string The response content type (default: 'text/html')
     */
    protected string $contentType = 'text/html';
    
    /**
     * The response protocol version
     * 
     * @var string The HTTP protocol version (default: '1.1')
     */
    protected string $protocolVersion = '1.1';
    
    /**
     * The ETag for the response
     * 
     * @var string|null The ETag value (including quotes)
     */
    protected ?string $etag = null;
    
    /**
     * The Last-Modified date for the response
     * 
     * @var DateTimeInterface|null The Last-Modified date
     */
    protected ?DateTimeInterface $lastModified = null;
    
    /**
     * The Cache-Control directives
     * 
     * @var array<string, mixed> The Cache-Control directives as key-value pairs
     */
    protected array $cacheControlDirectives = [];
    
    /**
     * Default MIME types
     * 
     * @var array<string, string> Map of file extensions to MIME types
     */
    protected static array $mimeTypes = [
        'txt'  => 'text/plain',
        'html' => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'rss'  => 'application/rss+xml',
        'atom' => 'application/atom+xml',
        'pdf'  => 'application/pdf',
        'zip'  => 'application/zip',
        'gif'  => 'image/gif',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'svg'  => 'image/svg+xml',
        'webp' => 'image/webp',
        'ico'  => 'image/x-icon',
    ];
    
    /**
     * @var array Default CORS settings
     */
    protected static array $defaultCorsSettings = [
        'allowedOrigins' => ['*'],
        'allowedMethods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
        'allowedHeaders' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposedHeaders' => [],
        'maxAge' => 0,
        'supportsCredentials' => false,
        'allowedOriginsPatterns' => [],
    ];
    
    /**
     * @var array Default file download headers
     */
    protected static array $defaultFileHeaders = [
        'Content-Transfer-Encoding' => 'binary',
        'Content-Type' => 'application/octet-stream',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Pragma' => 'public',
        'X-Content-Type-Options' => 'nosniff',
    ];
    
    /**
     * @var array Supported compression encodings
     */
    protected static array $supportedEncodings = ['gzip', 'deflate'];
    
    /**
     * Download a file
     * @var bool Whether response compression is enabled
     */
    protected bool $compressionEnabled = true;
    
    /**
     * @var int Compression level (0-9)
     */
    protected int $compressionLevel = 6;
    
    /**
     * @var int Minimum content length to compress (in bytes)
     */
    protected int $minCompressionSize = 1024;
    
    /**
     * Whether the response has been sent
     * 
     * @var bool True if the response has been sent, false otherwise
     */
    protected bool $sent = false;
    
    /**
     * HTTP status codes and their corresponding status texts
     * 
     * @var array<int, string> Map of status codes to status texts
     */
    protected static array $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',        // RFC2324
        421 => 'Misdirected Request',   // RFC7540
        422 => 'Unprocessable Entity',  // RFC4918
        423 => 'Locked',                // RFC4918
        424 => 'Failed Dependency',     // RFC4918
        425 => 'Too Early',             // RFC-ietf-httpbis-replay-04
        426 => 'Upgrade Required',      // RFC2817
        428 => 'Precondition Required', // RFC6585
        429 => 'Too Many Requests',     // RFC6585
        431 => 'Request Header Fields Too Large', // RFC6585
        451 => 'Unavailable For Legal Reasons',   // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',  // RFC2295
        507 => 'Insufficient Storage',     // RFC4918
        508 => 'Loop Detected',           // RFC5842
        510 => 'Not Extended',            // RFC2774
        511 => 'Network Authentication Required', // RFC6585
    ];
    
    /**
     * The cache control header value
     * 
     * @var string The Cache-Control header value (default: 'no-cache, private')
     */
    protected string $cacheControl = 'no-cache, private';
    
    /**
     * Whether the response is cacheable by HTTP caches
     * 
     * @var bool True if the response is cacheable, false otherwise
     */
    protected bool $cacheable = true;
    
    /**
     * The date and time after which the response is considered stale
     * 
     * @var \DateTimeInterface|null The expiration date of the response
     */
    protected ?DateTimeInterface $expires = null;
    
    /**
     * Set a cookie
     *
     * @param string $name The name of the cookie
     * @param string $value The value of the cookie
     * @param array $options Cookie options (expires, path, domain, secure, httponly, samesite)
     * @return $this
     */
    public function setCookie(string $name, string $value = '', array $options = []): static
    {
        $this->cookies[$name] = [
            'value' => $value,
            'options' => $options
        ];
        
        return $this;
    }
    
    /**
     * Remove a cookie
     *
     * @param string $name The name of the cookie to remove
     * @param string $path The path where the cookie was set
     * @param string|null $domain The domain where the cookie was set
     * @return $this
     */
    public function removeCookie(string $name, string $path = '/', ?string $domain = null): static
    {
        $this->setCookie($name, '', [
            'expires' => time() - 3600,
            'path' => $path,
            'domain' => $domain,
            'secure' => false,
            'httponly' => true
        ]);
        
        return $this;
    }
    
    /**
     * Get all cookies
     *
     * @return array
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }
    
    /**
     * Check if a cookie exists
     *
     * @param string $name The name of the cookie
     * @return bool
     */
    public function hasCookie(string $name): bool
    {
        return isset($this->cookies[$name]);
    }
    
    /**
     * Send all cookies with the response
     * 
     * @return void
     */
    protected function sendCookies(): void
    {
        if (isset($this->headers['Set-Cookie'])) {
            foreach ((array) $this->headers['Set-Cookie'] as $cookie) {
                if (!@header('Set-Cookie: ' . $cookie, false, $this->statusCode)) {
                    throw new \RuntimeException('Failed to send cookie header');
                }
            }
        }
        
        foreach ($this->cookies as $name => $cookie) {
            $options = $cookie['options'];
            
            // Set cookie with all options
            setcookie(
                $name,
                $cookie['value'],
                [
                    'expires' => $options['expires'] ?? 0,
                    'path' => $options['path'] ?? '/',
                    'domain' => $options['domain'] ?? '',
                    'secure' => $options['secure'] ?? false,
                    'httponly' => $options['httponly'] ?? true,
                    'samesite' => $options['samesite'] ?? 'Lax'
                ]
            );
        }
    }
    
    /**
     * Check if the response content should be compressed
     *
     * @param string $content The content to check
     * @return bool
     */
    protected function shouldCompress(string $content): bool {
        // Don't compress if compression is disabled
        if (!$this->compressionEnabled) {
            return false;
        }
        
        // Don't compress if content is too small
        if (strlen($content) < $this->minCompressionSize) {
            return false;
        }
        
        // Check if client accepts compressed content
        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            return false;
        }
        
        // Check if any of the supported encodings are accepted
        foreach (self::$supportedEncodings as $encoding) {
            if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], $encoding) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Compress the response content
     *
     * @param string &$content The content to compress (passed by reference)
     * @return bool Whether compression was successful
     */
    protected function compressContent(string &$content): bool {
        // Determine which encoding to use
        $encoding = '';
        foreach (self::$supportedEncodings as $enc) {
            if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], $enc) !== false) {
                $encoding = $enc;
                break;
            }
        }
        
        if (empty($encoding)) {
            return false;
        }
        
        // Set the appropriate Content-Encoding header
        $this->setHeader('Content-Encoding', $encoding);
        
        // Compress the content
        $compressed = false;
        switch (strtolower($encoding)) {
            case 'gzip':
                $compressed = gzencode($content, $this->compressionLevel);
                break;
                
            case 'deflate':
                $compressed = gzdeflate($content, $this->compressionLevel);
                break;
        }
        
        if ($compressed !== false) {
            // Update the content with compressed data
            $content = $compressed;
            
            // Update the Content-Length header
            $this->setHeader('Content-Length', (string) strlen($content));
            
            // Add Vary header to ensure caches handle the content correctly
            if ($this->hasHeader('Vary')) {
                $vary = $this->getHeader('Vary');
                if (stripos($vary, 'Accept-Encoding') === false) {
                    $this->setHeader('Vary', $vary . ', Accept-Encoding');
                }
            } else {
                $this->setHeader('Vary', 'Accept-Encoding');
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Configure response compression
     *
     * @param bool $enabled Whether compression is enabled
     * @param int $level Compression level (0-9)
     * @param int $minSize Minimum content length to compress (in bytes)
     * @return $this
     */
    public function setCompression(bool $enabled, int $level = 6, int $minSize = 1024): self {
        $this->compressionEnabled = $enabled;
        $this->compressionLevel = max(0, min(9, $level));
        $this->minCompressionSize = max(0, $minSize);
        return $this;
    }
    
    /**
     * Enable response compression
     *
     * @param int $level Compression level (0-9)
     * @param int $minSize Minimum content length to compress (in bytes)
     * @return $this
     */
    public function enableCompression(int $level = 6, int $minSize = 1024): self {
        return $this->setCompression(true, $level, $minSize);
    }
    
    /**
     * Disable response compression
     *
     * @return $this
     */
    public function disableCompression(): self {
        $this->compressionEnabled = false;
        return $this;
    }
    
    
    /**
     * Default Content Security Policy directives
     * 
     * @var array<string, array<string>> Map of CSP directives to their values
     */
    protected static array $defaultCspDirectives = [
        'default-src' => ["'self'"],
        'script-src' => ["'self'"],
        'style-src' => ["'self'"],
        'img-src' => ["'self'"],
        'font-src' => ["'self'"],
        'connect-src' => ["'self'"],
        'media-src' => ["'self'"],
        'object-src' => ["'none'"],
        'child-src' => ["'self'"],
        'frame-ancestors' => ["'self'"],
        'form-action' => ["'self'"],
        'base-uri' => ["'self'"],
        'frame-src' => ["'self'"],
        'worker-src' => ["'self'"],
        'manifest-src' => ["'self'"],
        'upgrade-insecure-requests' => [],
        'block-all-mixed-content' => [],
    ];
    
    
    
    /**
     * Set the response content type to HTML
     *
     * @param string $charset The charset (default: 'UTF-8')
     * @return $this
     */
    public function html(string $charset = 'UTF-8'): static
    {
        return $this->setContentType('text/html', $charset);
    }
    
    /**
     * Set the response content type to plain text
     *
     * @param string $charset The charset (default: 'UTF-8')
     * @return $this
     */
    public function text(string $charset = 'UTF-8'): static
    {
        return $this->setContentType('text/plain', $charset);
    }
    
    /**
     * Set the response content type to XML
     *
     * @param string $charset The charset (default: 'UTF-8')
     * @return $this
     */
    public function xml(string $charset = 'UTF-8'): static
    {
        return $this->setContentType('application/xml', $charset);
    }
    
    /**
     * Convert the response to a JSON string
     *
     * @return string JSON representation of the response
     */
    public function toJson(): string
    {
        return json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    
    
    /**
     * Create a new Response instance
     *
     * @param mixed $content The response content
     * @param int $status The HTTP status code
     * @param array $headers An array of response headers
     */
    public function __construct(mixed $content = '', int $status = 200, array $headers = []) {
        $this->setContent($content);
        $this->setStatusCode($status);
        
        // Set default headers if not already set
        if (!$this->hasHeader('Content-Type')) {
            $this->setContentType($this->contentType, $this->charset);
        }
        
        // Set any custom headers
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }
    
    /**
     * Create a new JSON response
     *
     * @param mixed $data The data to encode as JSON
     * @param int $status The HTTP status code (default: 200)
     * @param array $headers Additional headers to include
     * @param int $options JSON encoding options
     * @param int $depth Maximum depth for JSON encoding
     * @return static
     * @throws \RuntimeException If JSON encoding fails
     */
    public static function json(
        mixed $data, 
        int $status = 200, 
        array $headers = [], 
        int $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        int $depth = 512
    ): static {
        // Encode the data to JSON
        $json = json_encode($data, $options, $depth);
        
        // Check for JSON encoding errors
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException(
                'Failed to encode JSON data: ' . json_last_error_msg(),
                json_last_error()
            );
        }
        
        // Create response with JSON content first
        $response = new static($json, $status, $headers);
        
        // Set the proper Content-Type header if not already set
        $contentTypeSet = false;
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'content-type') {
                $contentTypeSet = true;
                break;
            }
        }
        
        if (!$contentTypeSet) {
            $response->setContentType('application/json', 'utf-8');
        }
        
        return $response;
    }
    
    /**
     * Create a new redirect response
     *
     * @param string $url The URL to redirect to
     * @param int $status The HTTP status code (default: 302)
     * @param array $headers Additional headers to set
     * @return static
     */
    public static function redirect($url, $status = 302, array $headers = []) {
        $headers['Location'] = $url;
        return new static('', $status, $headers);
    }
    
    /**
     * Create a new error response
     *
     * @param string $message The error message
     * @param int $status The HTTP status code (default: 500)
     * @param array $headers Additional headers to set
     * @return static
     */
    public static function error($message, $status = 500, array $headers = []) {
        return new static($message, $status, $headers);
    }
    
    /**
     * Create a new not found response
     *
     * @param string $message The error message
     * @param array $headers Additional headers to set
     * @return static
     */
    public static function notFound($message = 'Not Found', array $headers = []) {
        return new static($message, 404, $headers);
    }
    
    /**
     * Create a new forbidden response
     *
     * @param string $message The error message
     * @param array $headers Additional headers to set
     * @return static
     */
    public static function forbidden($message = 'Forbidden', array $headers = []) {
        return new static($message, 403, $headers);
    }
    
    /**
     * Create a new unauthorized response
     *
     * @param string $message The error message
     * @param array $headers Additional headers to set
     * @return static
     */
    public static function unauthorized($message = 'Unauthorized', array $headers = []) {
        return new static($message, 401, $headers);
    }
    
    /**
     * Set the response content
     *
     * @param mixed $content The response content (string, array, object, or callable for streaming)
     * @return $this
     */
    public function setContent(mixed $content): static {
        if (is_callable($content)) {
            $this->streamedCallback = $content;
            $this->content = '';
        } else {
            $this->content = $content;
            $this->streamedCallback = null;
        }
        return $this;
    }
    
    /**
     * Set the content type for this response
     *
     * @param string $contentType The content type (e.g., 'text/html', 'application/json')
     * @param string|null $charset The charset (null to keep current)
     * @return $this
     * @throws \InvalidArgumentException If the content type is invalid
     */
    public function setContentType(string $contentType, ?string $charset = null): static {
        // Basic MIME type validation
        if (!preg_match('#^[\w\-]+/[\w\-\.\+]+$#', $contentType)) {
            throw new \InvalidArgumentException(sprintf('Invalid content type "%s"', $contentType));
        }
        
        $this->contentType = $contentType;
        
        // Update charset if provided
        if ($charset !== null) {
            $this->charset = $charset;
        }
        
        // Build the Content-Type header
        $contentTypeHeader = $contentType;
        
        // Only append charset for text/* and application/* types that don't already specify charset
        $shouldIncludeCharset = 
            $this->charset && 
            (str_starts_with($contentType, 'text/') || 
             str_starts_with($contentType, 'application/')) &&
            !preg_match('/;\s*charset\s*=/i', $contentType);
        
        if ($shouldIncludeCharset) {
            $contentTypeHeader .= '; charset=' . $this->charset;
        }
        
        $this->setHeader('Content-Type', $contentTypeHeader);
        return $this;
    }
    
    /**
     * Get the response content type
     *
     * @return string
     */
    public function getContentType(): string {
        return $this->contentType;
    }
    
    /**
     * Specify data which should be serialized to JSON
     *
     * @return mixed Data which can be serialized by json_encode()
     */
    public function jsonSerialize(): mixed {
        // If content is set and is not empty, return it directly
        if (!empty($this->content)) {
            return $this->content;
        }
        
        // Otherwise, return status information
        return [
            'status' => $this->statusCode,
            'statusText' => $this->statusText,
            'content' => $this->content,
            'headers' => $this->headers,
            'version' => $this->protocolVersion,
            'charset' => $this->charset
        ];
    }
    
    /**
     * Create a file download response
     *
     * @param string|SplFileInfo $file The path to the file or SplFileInfo instance
     * @param string|null $name The file name shown to the user (null to use the original name)
     * @param array $headers Additional headers to set
     * @param string $disposition The disposition type (attachment/inline)
     * @param bool $autoEtag Whether to automatically generate an ETag
     * @param bool $autoLastModified Whether to automatically set the Last-Modified header
     * @return static
     * @throws \RuntimeException If the file does not exist or is not readable
     */
    /**
     * Create a file download response with support for resumable downloads
     *
     * @param string|SplFileInfo $file The file path or SplFileInfo object
     * @param string|null $name The file name to use for the download
     * @param array $headers Additional headers to set
     * @param string $disposition The content disposition (attachment or inline)
     * @param bool $autoEtag Whether to automatically generate an ETag
     * @param bool $autoLastModified Whether to automatically set the Last-Modified header
     * @param bool $resumable Whether to enable resumable downloads (supports Range header)
     * @return static
     * @throws \RuntimeException If the file does not exist or is not readable
     */
    public static function file(
        $file,
        ?string $name = null,
        array $headers = [],
        string $disposition = 'attachment',
        bool $autoEtag = true,
        bool $autoLastModified = true,
        bool $resumable = true
    ): static {
        if (!$file instanceof SplFileInfo) {
            $file = new SplFileInfo($file);
        }
        
        if (!$file->isReadable()) {
            throw new RuntimeException(sprintf('The file "%s" is not readable.', $file->getPathname()));
        }
        
        $name = $name ?? $file->getBasename();
        $contentType = self::getMimeType($file->getExtension()) ?? 'application/octet-stream';
        $fileSize = $file->getSize();
        $lastModified = $file->getMTime();
        
        $response = new static(null, 200, $headers);
        
        // Set basic headers
        $response->setHeader('Content-Type', $contentType);
        $response->setHeader('Content-Length', (string)$fileSize);
        $response->setHeader('Accept-Ranges', 'bytes');
        
        // Create content disposition header
        $filename = $response->isAscii($name) ? $name : $name . '.bin';
        $response->setHeader('Content-Disposition', sprintf(
            '%s; filename="%s"',
            $disposition,
            str_replace('"', '\\"', $filename)
        ));
        
        // Set cache headers if enabled
        if ($autoEtag || $autoLastModified) {
            $cacheOptions = [
                'max_age' => 31536000, // 1 year
                'public' => true,
            ];
            
            if ($autoEtag) {
                $etag = sprintf('"%s-%s"', $lastModified, $fileSize);
                $cacheOptions['etag'] = $etag;
                $response->setHeader('ETag', $etag);
            }
            
            if ($autoLastModified) {
                $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
                $cacheOptions['last_modified'] = $lastModified;
            }
            
            $response->setCache($cacheOptions);
        }
        
        // Handle range requests for resumable downloads
        $range = null;
        if ($resumable && isset($_SERVER['HTTP_RANGE'])) {
            $range = $response->parseRangeHeader($_SERVER['HTTP_RANGE'], $fileSize);
            
            if ($range) {
                $response->setStatusCode(206); // Partial Content
                $response->setHeader('Content-Range', sprintf(
                    'bytes %d-%d/%d',
                    $range['start'],
                    $range['end'],
                    $fileSize
                ));
                $response->setHeader('Content-Length', (string)($range['end'] - $range['start'] + 1));
            } else {
                $response->setStatusCode(416); // Requested Range Not Satisfiable
                $response->setHeader('Content-Range', sprintf('bytes */%d', $fileSize));
                return $response;
            }
        }
        
        // Set the content to stream the file in chunks
        $response->setContent(function () use ($file, $range, $fileSize) {
            $handle = $file->openFile('rb');
            $chunkSize = 8 * 1024; // 8KB chunks
            
            if ($range) {
                // Seek to the start of the range
                $handle->fseek($range['start']);
                $bytesToSend = $range['end'] - $range['start'] + 1;
            } else {
                $bytesToSend = $fileSize;
            }
            
            $bytesSent = 0;
            
            while (!$handle->eof() && $bytesSent < $bytesToSend) {
                $chunk = min($chunkSize, $bytesToSend - $bytesSent);
                
                if ($chunk <= 0) {
                    break;
                }
                
                echo $handle->fread($chunk);
                $bytesSent += $chunk;
                
                // Flush the output buffer to send data immediately
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                
                // Prevent timeouts for large files
                if (connection_aborted()) {
                    break;
                }
            }
            
            $handle = null; // Close the file handle
        });
        
        return $response;
    }
    
    /**
     * Create a JSONP response
     *
     * @param string $callback The JSONP callback function name
     * @param mixed $data The data to encode as JSONP
     * @param int $status The HTTP status code
     * @param array $headers Additional headers to set
     * @param int $options JSON encoding options
     * @return static
     */
    public static function jsonp(
        string $callback,
        mixed $data,
        int $status = 200,
        array $headers = [],
        int $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ): static {
        $json = json_encode($data, $options);
        
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException(
                'Failed to encode JSON data: ' . json_last_error_msg(),
                json_last_error()
            );
        }
        
        $content = sprintf('/**/%s(%s);', $callback, $json);
        $headers['Content-Type'] = 'application/javascript; charset=utf-8';
        
        return new static($content, $status, $headers);
    }
    
    /**
     * Set the response charset
     *
     * @param string $charset The charset to set
     * @return $this
     */
    public function setCharset(string $charset): static {
        $this->charset = $charset;
        return $this;
    }
    
    /**
     * Get the response charset
     *
     * @return string
     */
    public function getCharset(): string {
        return $this->charset;
    }
    
    /**
     * Set the Content Security Policy for the response
     * 
     * @param array $directives An associative array of CSP directives
     * @param bool $reportOnly Whether to use Content-Security-Policy-Report-Only header
     * @return $this
     */
    public function setContentSecurityPolicy(array $directives, bool $reportOnly = false)
    {
        $headerValue = [];
        
        foreach ($directives as $directive => $sources) {
            if (empty($sources) && $sources !== []) {
                // Handle directives without values (like 'block-all-mixed-content')
                $headerValue[] = $directive;
            } else {
                $headerValue[] = $directive . ' ' . (is_array($sources) ? implode(' ', $sources) : $sources);
            }
        }
        
        $headerName = $reportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
        $this->headers[$headerName] = [implode('; ', $headerValue)];
        
        return $this;
    }
    
    /**
     * Add a Content Security Policy directive
     * 
     * @param string $directive The CSP directive name
     * @param string|array $sources The allowed sources
     * @param bool $replace Whether to replace existing sources for this directive
     * @param bool $reportOnly Whether to use Content-Security-Policy-Report-Only header
     * @return $this
     */
    public function addContentSecurityPolicyDirective(
        string $directive, 
        $sources, 
        bool $replace = false,
        bool $reportOnly = false
    ) {
        $headerName = $reportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
        $currentPolicy = $this->headers[$headerName][0] ?? '';
        
        // Parse existing policy
        $directives = [];
        foreach (explode(';', $currentPolicy) as $part) {
            $part = trim($part);
            if (empty($part)) continue;
            
            $parts = explode(' ', $part, 2);
            if (count($parts) === 2) {
                $directives[trim($parts[0])] = $parts[1];
            } else {
                $directives[$parts[0]] = '';
            }
        }
        
        // Add or update the directive
        $sources = is_array($sources) ? implode(' ', $sources) : $sources;
        
        if ($replace || !isset($directives[$directive])) {
            $directives[$directive] = $sources;
        } else {
            $directives[$directive] .= ' ' . $sources;
        }
        
        // Rebuild the header
        $headerValue = [];
        foreach ($directives as $dir => $src) {
            $headerValue[] = trim($dir . ' ' . $src);
        }
        
        $this->headers[$headerName] = [implode('; ', $headerValue)];
        
        return $this;
    }
    
    /**
     * Remove a Content Security Policy directive
     * 
     * @param string $directive The CSP directive to remove
     * @param bool $reportOnly Whether to modify the Report-Only header
     * @return $this
     */
    public function removeContentSecurityPolicyDirective(string $directive, bool $reportOnly = false)
    {
        $headerName = $reportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
        
        if (isset($this->headers[$headerName])) {
            $directives = [];
            foreach (explode(';', $this->headers[$headerName][0]) as $part) {
                $part = trim($part);
                if (empty($part)) continue;
                
                $parts = explode(' ', $part, 2);
                if (count($parts) === 2 && $parts[0] !== $directive) {
                    $directives[$parts[0]] = $parts[1];
                } elseif (count($parts) === 1 && $parts[0] !== $directive) {
                    $directives[$parts[0]] = '';
                }
            }
            
            $headerValue = [];
            foreach ($directives as $dir => $src) {
                $headerValue[] = trim($dir . ' ' . $src);
            }
            
            if (empty($headerValue)) {
                unset($this->headers[$headerName]);
            } else {
                $this->headers[$headerName] = [implode('; ', $headerValue)];
            }
        }
        
        return $this;
    }
    
    /**
     * Enable CORS (Cross-Origin Resource Sharing) for the response
     *
     * @param array $options CORS options
     *   - allowedOrigins: array List of allowed origins (e.g., ['https://example.com', 'http://localhost:3000'])
     *   - allowedMethods: array List of allowed HTTP methods (e.g., ['GET', 'POST', 'OPTIONS'])
     *   - allowedHeaders: array List of allowed headers (e.g., ['Content-Type', 'Authorization'])
     *   - exposedHeaders: array List of headers to expose to the browser
     *   - maxAge: int Maximum age (in seconds) of the CORS preflight request
     *   - supportsCredentials: bool Whether to allow credentials (cookies, HTTP authentication)
     *   - allowedOriginsPatterns: array List of regex patterns for allowed origins
     * @return $this
     */
    public function enableCors(array $options = []): self
    {
        $options = array_merge(self::$defaultCorsSettings, $options);
        
        // Handle allowed origins
        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
        
        if ($origin) {
            $allowed = false;
            
            // Check against allowed origins
            if (in_array('*', $options['allowedOrigins'], true)) {
                $allowed = true;
            } elseif (in_array($origin, $options['allowedOrigins'], true)) {
                $allowed = true;
            } else {
                // Check against allowed origin patterns
                foreach ($options['allowedOriginsPatterns'] as $pattern) {
                    if (preg_match($pattern, $origin)) {
                        $allowed = true;
                        break;
                    }
                }
            }
            
            if ($allowed) {
                $this->headers['Access-Control-Allow-Origin'] = [$origin];
                
                if ($options['supportsCredentials']) {
                    $this->headers['Access-Control-Allow-Credentials'] = ['true'];
                }
                
                // Handle preflight requests
                if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                    $this->setStatusCode(204); // No Content
                    
                    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                        $this->headers['Access-Control-Allow-Methods'] = [
                            implode(', ', $options['allowedMethods'])
                        ];
                    }
                    
                    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                        $this->headers['Access-Control-Allow-Headers'] = [
                            implode(', ', $options['allowedHeaders'])
                        ];
                    }
                    
                    if ($options['maxAge'] > 0) {
                        $this->headers['Access-Control-Max-Age'] = [$options['maxAge']];
                    }
                    
                    if (!empty($options['exposedHeaders'])) {
                        $this->headers['Access-Control-Expose-Headers'] = [
                            implode(', ', $options['exposedHeaders'])
                        ];
                    }
                    
                    // Prevent any output for OPTIONS requests
                    $this->setContent('');
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Set default CORS settings that will be used for all responses
     * 
     * @param array $settings CORS settings
     * @return void
     */
    public static function setDefaultCorsSettings(array $settings): void
    {
        self::$defaultCorsSettings = array_merge(self::$defaultCorsSettings, $settings);
    }
    
    /**
     * Set default file download headers that will be used for all file downloads
     * 
     * @param array $headers File download headers
     * @return void
     */
    public static function setDefaultFileHeaders(array $headers): void
    {
        self::$defaultFileHeaders = array_merge(self::$defaultFileHeaders, $headers);
    }
    
    /**
     * Send a file for download
     *
     * @param string|SplFileInfo $file Path to the file or SplFileInfo instance
     * @param string|null $filename The filename that should be sent to the client
     * @param array $headers Additional headers to send with the response
     * @param string $disposition Disposition type (attachment or inline)
     * @return static
     * @throws \RuntimeException If the file cannot be read
     */
    public static function download($file, ?string $filename = null, array $headers = [], string $disposition = 'attachment'): self
    {
        if (!($file instanceof SplFileInfo)) {
            $file = new SplFileInfo($file);
        }
        
        if (!$file->isReadable()) {
            throw new \RuntimeException(sprintf('File "%s" does not exist or is not readable', $file->getPathname()));
        }
        
        $filename = $filename ?? $file->getFilename();
        $filePath = $file->getPathname();
        $fileSize = $file->getSize();
        
        // Get MIME type
        $mimeType = self::getMimeType($file->getExtension()) ?: 
                   (function_exists('mime_content_type') ? mime_content_type($filePath) : 'application/octet-stream');
        
        // Set default headers
        $responseHeaders = array_merge(
            self::$defaultFileHeaders,
            [
                'Content-Length' => $fileSize,
                'Content-Type' => $mimeType,
                'Content-Disposition' => sprintf('%s; filename="%s"', $disposition, self::quoteFilename($filename)),
                'Last-Modified' => gmdate('D, d M Y H:i:s', $file->getMTime()) . ' GMT',
                'ETag' => sprintf('"%s"', hash_file('sha256', $filePath)),
            ],
            $headers
        );
        
        // Handle range requests for resumable downloads
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = self::parseRangeHeader($_SERVER['HTTP_RANGE'], $fileSize);
            if ($range) {
                $responseHeaders['Content-Range'] = sprintf('bytes %d-%d/%d', $range['start'], $range['end'], $fileSize);
                $responseHeaders['Content-Length'] = $range['end'] - $range['start'] + 1;
                
                $response = new static('', 206, $responseHeaders);
                $response->setContent(function() use ($filePath, $range) {
                    $handle = fopen($filePath, 'rb');
                    fseek($handle, $range['start']);
                    $length = $range['end'] - $range['start'] + 1;
                    $bytesRead = 0;
                    
                    while (!feof($handle) && $bytesRead < $length) {
                        $chunkSize = min(8192, $length - $bytesRead);
                        echo fread($handle, $chunkSize);
                        $bytesRead += $chunkSize;
                        flush();
                        if (connection_status() !== CONNECTION_NORMAL) {
                            break;
                        }
                    }
                    
                    fclose($handle);
                });
                
                return $response;
            }
        }
        
        // If we get here, it's a regular file download
        $response = new static('', 200, $responseHeaders);
        $response->setContent(function() use ($filePath) {
            $handle = fopen($filePath, 'rb');
            while (!feof($handle) && !connection_aborted()) {
                echo fread($handle, 8192);
                flush();
            }
            fclose($handle);
        });
        
        return $response;
    }
    
    /**
     * Quote a filename for use in Content-Disposition header
     *
     * @param string $filename The filename to quote
     * @return string The quoted filename
     */
    protected static function quoteFilename(string $filename): string
    {
        // Only quote if it contains at least one of: space, tab, ", ', <, >, |, \, :, ;, ,, =, ?, *
        if (preg_match('/[\x00-\x20\x22\x27\x3c\x3e\x5c\x7c\x3a\x3b\x2c\x3d\x3f\x2a]/', $filename)) {
            return sprintf('"%s"', str_replace('"', '\\"', $filename));
        }
        
        return $filename;
    }
    
    
    /**
     * Parse the Range header for resumable downloads
     *
     * @param string $rangeHeader The Range header value
     * @param int $fileSize The size of the file in bytes
     * @return array|null Array with 'start' and 'end' keys, or null if range is invalid
     */
    protected static function parseRangeHeader(string $rangeHeader, int $fileSize): ?array
    {
        if (preg_match('/bytes=\s*(\d+)-(\d*)[\D.*]?/i', $rangeHeader, $matches)) {
            $start = (int)$matches[1];
            $end = isset($matches[2]) && $matches[2] !== '' ? (int)$matches[2] : $fileSize - 1;
            
            // Validate range
            if ($start < 0 || $end >= $fileSize || $start > $end) {
                return null;
            }
            
            return [
                'start' => $start,
                'end' => $end,
            ];
        }
        
        return null;
    }
    
    /**
     * Get the MIME type for a file extension
     *
     * @param string $extension The file extension (without dot)
     * @return string|null The MIME type or null if not found
     */
    public static function getMimeType(string $extension): ?string
    {
        $extension = strtolower(ltrim($extension, '.'));
        $mimeTypes = [
            // Text
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/plain',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'csv' => 'text/csv',
            
            // Images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'webp' => 'image/webp',
            
            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            '7z' => 'application/x-7z-compressed',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
            'bz2' => 'application/x-bzip2',
            
            // Audio/video
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'flv' => 'video/x-flv',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'wmv' => 'video/x-ms-wmv',
            
            // Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'rtf' => 'application/rtf',
            'txt' => 'text/plain',
            
            // Source code
            'php' => 'application/x-httpd-php',
            'c' => 'text/x-csrc',
            'cpp' => 'text/x-c++src',
            'h' => 'text/x-chdr',
            'java' => 'text/x-java',
            'py' => 'text/x-python',
            'rb' => 'application/x-ruby',
            'sh' => 'application/x-sh',
            'pl' => 'application/x-perl',
            'sql' => 'application/sql',
            'go' => 'text/x-go',
            'cs' => 'text/x-csharp',
            'swift' => 'text/x-swift',
            'kt' => 'text/x-kotlin',
            'rs' => 'text/rust',
        ];
        
        return $mimeTypes[$extension] ?? null;
    }
    
    
    /**
     * Set cache headers for the response
     *
     * @param array $options Cache options
     *   - etag: string|null The ETag value
     *   - last_modified: int|\DateTimeInterface|null The last modified timestamp or DateTime
     *   - max_age: int Maximum cache age in seconds (default: 3600)
     *   - s_maxage: int Shared cache max age in seconds
     *   - public: bool Whether the response is public (default: true)
     *   - must_revalidate: bool Whether to require revalidation (default: false)
     *   - proxy_revalidate: bool Whether to require proxy revalidation (default: false)
     *   - no_cache: bool Whether to disable caching (default: false)
     *   - no_store: bool Whether to prevent storage (default: false)
     *   - no_transform: bool Whether to prevent transformations (default: false)
     * @return $this
     */
    public function setCache(array $options = []): static {
        $options = array_merge([
            'etag' => null,
            'last_modified' => null,
            'max_age' => 3600,
            's_maxage' => null,
            'public' => true,
            'must_revalidate' => false,
            'proxy_revalidate' => false,
            'no_cache' => false,
            'no_store' => false,
            'no_transform' => false,
        ], $options);
        
        $directives = [];
        
        if ($options['no_cache']) {
            $directives[] = 'no-cache';
        }
        
        if ($options['no_store']) {
            $directives[] = 'no-store';
        }
        
        if ($options['no_transform']) {
            $directives[] = 'no-transform';
        }
        
        if ($options['must_revalidate']) {
            $directives[] = 'must-revalidate';
        }
        
        if ($options['proxy_revalidate']) {
            $directives[] = 'proxy-revalidate';
        }
        
        if ($options['public']) {
            $directives[] = 'public';
        } else {
            $directives[] = 'private';
        }
        
        if (null !== $options['max_age']) {
            $directives[] = 'max-age=' . $options['max_age'];
        }
        
        if (null !== $options['s_maxage']) {
            $directives[] = 's-maxage=' . $options['s_maxage'];
        }
        
        $this->setHeader('Cache-Control', implode(', ', $directives));
        
        if (null !== $options['etag']) {
            $this->setEtag($options['etag']);
        }
        
        if (null !== $options['last_modified']) {
            $this->setLastModified($options['last_modified']);
        }
        
        return $this;
    }
    
    /**
     * Make the response expire immediately
     *
     * @return $this
     */
    public function expire(): static {
        $this->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $this->setHeader('Pragma', 'no-cache');
        
        return $this;
    }
    
    /**
     * Set no-cache headers for the response
     *
     * @return $this
     */
    public function setNoCache(): static {
        return $this->setCache([
            'no_cache' => true,
            'no_store' => true,
            'must_revalidate' => true,
            'max_age' => 0,
        ]);
    }
    
    /**
     * Get the response content
     *
     * @return string
     */
    public function getContent(): string {
        if (is_callable($this->content)) {
            ob_start();
            call_user_func($this->content);
            return ob_get_clean();
        }
        
        // Handle streamed content
        if ($this->streamedCallback !== null) {
            ob_start();
            call_user_func($this->streamedCallback);
            return ob_get_clean();
        }
        
        return (string) $this->content;
    }
    
    /**
     * Convert the response to a string
     *
     * @return string
     */
    public function __toString(): string {
        try {
            return $this->getContent();
        } catch (\Throwable $e) {
            // Prevent exceptions from being thrown when casting to string
            error_log(sprintf('Error in Response::__toString(): %s', $e->getMessage()));
            return '';
        }
    }
    
    /**
     * Set the HTTP status code
     *
     * @param int $code The HTTP status code
     * @param string|null $text The HTTP status text
     * @return $this
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     */
    public function setStatusCode($code, $text = null) {
        $this->statusCode = (int) $code;
        
        if ($this->isInvalid()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }
        
        if (null === $text) {
            $this->statusText = isset(self::$statusTexts[$code]) ? self::$statusTexts[$code] : 'unknown status';
        } else {
            $this->statusText = $text;
        }
        
        return $this;
    }
    
    /**
     * Get the HTTP status code
     *
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }
    
    /**
     * Get the HTTP status text
     *
     * @return string
     */
    public function getStatusText() {
        return $this->statusText;
    }
    
    /**
     * Sets a header by name.
     *
     * @param string $key The header name
     * @param string|string[] $values The value or an array of values
     * @param bool $replace Whether to replace the header or add it
     * @return $this
     */
    public function setHeader(string $key, $values, bool $replace = true): static {
        // Normalize the header key to lowercase with hyphens
        $key = str_replace('_', '-', strtolower($key));
        
        if ($replace || !isset($this->headers[$key])) {
            $this->headers[$key] = (array) $values;
        } else {
            $this->headers[$key] = array_merge($this->headers[$key], (array) $values);
        }
        
        return $this;
    }
    
    
    /**
     * Get a header by name
     *
     * @param string $key The header name
     * @param mixed $default The default value if the header does not exist
     * @return string|array|null
     */
    public function getHeader($key, $default = null) {
        $key = str_replace('_', '-', strtolower($key));
        
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }
        
        return $default;
    }
    
    /**
     * Check if a header exists
     *
     * @param string $key The header name
     * @return bool
     */
    public function hasHeader($key) {
        $key = str_replace('_', '-', strtolower($key));
        return isset($this->headers[$key]);
    }
    
    /**
     * Remove a header
     *
     * @param string $key The header name
     * @return $this
     */
    public function removeHeader($key) {
        $key = str_replace('_', '-', strtolower($key));
        unset($this->headers[$key]);
        return $this;
    }
    
    /**
     * Get all headers
     *
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }
    
    
    /**
     * Check if the response is invalid
     *
     * @return bool
     */
    public function isInvalid() {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }
    
    /**
     * Check if the response is successful
     *
     * @return bool
     */
    public function isSuccessful() {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }
    
    /**
     * Check if the response is a redirection
     *
     * @return bool
     */
    public function isRedirection() {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }
    
    /**
     * Check if the response is a client error
     *
     * @return bool
     */
    public function isClientError() {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }
    
    /**
     * Check if the response is a server error
     *
     * @return bool
     */
    public function isServerError() {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }
    
    /**
     * Check if the response is OK (status code 200)
     *
     * @return bool
     */
    public function isOk() {
        return 200 === $this->statusCode;
    }
    
    /**
     * Check if the response is a 404 Not Found
     *
     * @return bool
     */
    public function isNotFound() {
        return 404 === $this->statusCode;
    }
    
    /**
     * Check if the response is a 403 Forbidden
     *
     * @return bool
     */
    public function isForbidden() {
        return 403 === $this->statusCode;
    }
    
    /**
     * Check if the response is a 401 Unauthorized
     *
     * @return bool
     */
    public function isUnauthorized() {
        return 401 === $this->statusCode;
    }
    
    /**
     * Sends HTTP headers and content.
     * 
     * This method will send the response headers and content to the client.
     * It also handles output buffering and fastcgi process management.
     *
     * @return $this
     * @throws \RuntimeException
     */
    /**
     * Sets the response's cache headers for a file download.
     *
     * @param string $filePath Path to the file
     * @param string|null $etag The ETag for the file (auto-generated if null)
     * @param bool $public Whether the response is public
     * @param int $maxAge Maximum time in seconds the response is considered fresh
     * @return $this
     */
    public function setFileCacheHeaders(string $filePath, ?string $etag = null, bool $public = false, int $maxAge = 3600): static {
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('The file "%s" does not exist.', $filePath));
        }
        
        $this->setEtag($etag ?: hash_file('sha256', $filePath));
        $this->setLastModified(\DateTime::createFromFormat('U', (string) filemtime($filePath)));
        $this->setMaxAge($maxAge);
        
        if ($public) {
            $this->setPublic();
        } else {
            $this->setPrivate();
        }
        
        return $this;
    }
    
    /**
     * Sets the ETag value.
     *
     * @param string|null $etag The ETag unique identifier or null to generate one
     * @param bool $weak Whether the ETag should be weak
     * @return $this
     */
    public function setEtag(?string $etag = null, bool $weak = false): static {
        if (null === $etag) {
            $etag = hash('sha256', $this->getContent());
        }
        
        $this->etag = $weak ? 'W/"' . $etag . '"' : '"' . $etag . '"';
        $this->setHeader('ETag', $this->etag);
        
        return $this;
    }
    
    /**
     * Gets the ETag value.
     *
     * @return string|null The ETag value or null if not set
     */
    public function getEtag(): ?string {
        return $this->etag;
    }
    
    /**
     * Removes a Cache-Control directive.
     *
     * @param string $key The directive to remove
     * @return $this
     */
    public function removeCacheControlDirective(string $key): static {
        $key = str_replace('_', '-', strtolower($key));
        unset($this->cacheControlDirectives[$key]);
        $this->updateCacheControlHeader();
        
        return $this;
    }
    
    /**
     * Updates the Cache-Control header based on the current directives.
     */
    protected function updateCacheControlHeader(): void {
        $parts = [];
        
        foreach ($this->cacheControlDirectives as $key => $value) {
            if (true === $value) {
                $parts[] = $key;
            } else {
                if (preg_match('#[^a-zA-Z0-9._-]#', $value)) {
                    $value = '"' . $value . '"';
                }
                
                $parts[] = "$key=$value";
            }
        }
        
        $this->setHeader('Cache-Control', implode(', ', $parts) ?: 'no-cache, private');
    }
    
    /**
     * Adds a Cache-Control directive.
     *
     * @param string $key The directive name
     * @param mixed $value The directive value (true for flag, string/int for value)
     * @return $this
     */
    public function addCacheControlDirective(string $key, $value = true): static {
        $key = str_replace('_', '-', strtolower($key));
        $this->cacheControlDirectives[$key] = $value;
        $this->updateCacheControlHeader();
        
        return $this;
    }
    
    /**
     * Sets the Last-Modified HTTP header with a DateTime instance or timestamp.
     *
     * @param DateTimeInterface|int|null $date A DateTimeInterface instance, timestamp, or null to remove the header
     * @return $this
     */
    public function setLastModified($date): static {
        if (null === $date) {
            $this->removeHeader('Last-Modified');
            $this->lastModified = null;
            return $this;
        }

        if (is_int($date)) {
            $date = new \DateTime('@' . $date);
        } elseif ($date === null) {
            $this->lastModified = null;
            $this->removeHeader('Last-Modified');
            return $this;
        }

        if (!$date instanceof \DateTimeInterface) {
            throw new InvalidArgumentException('The date must be an instance of DateTimeInterface, an integer timestamp, or null');
        }

        $date = clone $date;
        if ($date instanceof \DateTime) {
            $date->setTimezone(new \DateTimeZone('UTC'));
        }
        $this->lastModified = $date;
        $this->setHeader('Last-Modified', $date->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }
    
    /**
     * Gets the Last-Modified HTTP header as a DateTime instance.
     *
     * @return \DateTimeInterface|null The Last-Modified header value or null if not set
     */
    public function getLastModified(): ?\DateTimeInterface {
        return $this->lastModified;
    }
    
    /**
     * Marks the response as "public".
     *
     * @return $this
     */
    public function setPublic(): static {
        $this->setHeader('Cache-Control', 'public');
        $this->removeCacheControlDirective('private');
        
        return $this;
    }
    
    /**
     * Marks the response as private.
     *
     * @return $this
     */
    public function setPrivate(): static {
        $this->setHeader('Cache-Control', 'private');
        $this->removeCacheControlDirective('public');
        
        return $this;
    }
    
    /**
     * Sets the number of seconds after which the response should no longer be considered fresh.
     *
     * @param int $value Number of seconds
     * @return $this
     */
    public function setMaxAge(int $value): static {
        $this->addCacheControlDirective('max-age', $value);
        
        return $this;
    }
    
    /**
     * Sets the number of seconds after which the response should no longer be considered fresh by shared caches.
     *
     * @param int $value Number of seconds
     * @return $this
     */
    public function setSharedMaxAge(int $value): static {
        $this->setPublic();
        $this->addCacheControlDirective('s-maxage', $value);
        
        return $this;
    }
    
    /**
     * Marks the response as "not modified".
     *
     * @return $this
     */
    public function setNotModified(): static {
        $this->setStatusCode(304);
        $this->setContent(null);
        
        // Remove headers that MUST NOT be included with 304 Not Modified responses
        $this->removeHeader('Content-Type');
        $this->removeHeader('Content-Length');
        $this->removeHeader('Transfer-Encoding');
        
        return $this;
    }
    
    /**
     * Checks if the response is still fresh based on the request's cache validation headers.
     *
     * @param Request $request The request to validate against
     * @return bool True if the response is still fresh, false otherwise
     */
    public function isNotModified(Request $request): bool {
        if (!in_array($request->getMethod(), ['GET', 'HEAD'])) {
            return false;
        }
        
        $notModified = false;
        $lastModified = $this->getLastModified();
        $ifModifiedSince = $request->getHeader('If-Modified-Since');
        
        // Check ETag if available
        $etag = $this->getEtag();
        if ($etag) {
            $ifNoneMatch = $request->getHeader('If-None-Match');
            if ($ifNoneMatch) {
                // Handle both string and array return types
                $ifNoneMatch = is_array($ifNoneMatch) ? $ifNoneMatch[0] : $ifNoneMatch;
                $etags = preg_split('/\s*,\s*/', $ifNoneMatch);
                $notModified = in_array($etag, $etags) || in_array('*', $etags);
            }
        }
        
        // Check Last-Modified if available
        if ($lastModified && $ifModifiedSince) {
            $time = strtotime($ifModifiedSince);
            if (false !== $time) {
                $notModified = $notModified || $lastModified->getTimestamp() <= $time;
            }
        }
        
        if ($notModified) {
            $this->setNotModified();
        }
        
        return $notModified;
    }
    
    public function send() {
        try {
            // Send cookies before any output
            $this->sendCookies();
            
            // Handle conditional GET requests
            if ($this->isNotModified($this->request ?? new Request())) {
                $this->setStatusCode(304);
                $this->setContent('');
            }
            
            // Send the content (which will also send headers)
            $this->sendContent();
            
            // Mark the response as sent
            $this->sent = true;
            
            // Flush output buffer if needed
            if (ob_get_level() > 0) {
                ob_flush();
                flush();
            }
            
            return $this;
            
        } catch (\Exception $e) {
            // Clean any output that was already sent
            if (ob_get_level() > 0) {
                ob_clean();
            }
            
            // Re-throw the exception with additional context
            throw new \RuntimeException(
                sprintf('Failed to send the response: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
    
    /**
     * Send the response content
     *
     * @return $this
     * @throws \RuntimeException If output buffering is not active when required
     */
    public function sendContent() {
        // Send headers first
        $this->sendHeaders();
        
        // Handle streamed content
        if (is_callable($this->content)) {
            try {
                call_user_func($this->content);
            } catch (\Exception $e) {
                // Rethrow as RuntimeException to maintain the method's contract
                throw new \RuntimeException('Error executing response callback: ' . $e->getMessage(), 0, $e);
            }
            return $this;
        }
        
        // Get the content as string
        $content = (string) $this->content;
        
        // Only attempt compression for non-empty content
        if ($content !== '' && $this->shouldCompress($content)) {
            $this->compressContent($content);
        }
        
        // Output the content with error suppression to avoid issues with output handlers
        echo @$content;
        
        // Flush output buffers if any
        if (ob_get_level() > 0) {
            ob_flush();
            flush();
        }
        
        return $this;
    }
    
    /**
     * Sends HTTP headers.
     *
     * This method sends the HTTP status line and all headers. It also handles
     * the Content-Type header specially to ensure it's only sent once.
     *
     * @return $this
     * @throws \RuntimeException When headers cannot be sent
     */
    public function sendHeaders() {
        // Headers have already been sent by the developer
        if (headers_sent($file, $line)) {
            throw new \RuntimeException(sprintf(
                'Headers already sent in %s on line %d',
                $file,
                $line
            ));
        }
        
        try {
            // Status line
            if (!@header(sprintf('HTTP/1.1 %s %s', $this->statusCode, $this->statusText), true, $this->statusCode)) {
                throw new \RuntimeException('Failed to send HTTP status line');
            }
            
            // Headers
            foreach ($this->headers as $name => $values) {
                $replace = 0 === strcasecmp($name, 'Content-Type');
                
                foreach ((array) $values as $value) {
                    if (!@header($name . ': ' . $value, $replace, $this->statusCode)) {
                        throw new \RuntimeException(sprintf('Failed to send header: %s', $name));
                    }
                }
            }
            
            // Cookies (handled separately as they have different header syntax)
            if (isset($this->headers['Set-Cookie'])) {
                foreach ((array) $this->headers['Set-Cookie'] as $cookie) {
                    if (!@header('Set-Cookie: ' . $cookie, false, $this->statusCode)) {
                        throw new \RuntimeException('Failed to send cookie header');
                    }
                }
            }
            
            return $this;
            
        } catch (\Exception $e) {
            // Clean any headers that might have been sent
            if (headers_sent()) {
                header_remove();
            }
            
            throw new \RuntimeException(
                sprintf('Failed to send headers: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
    
    /**
     * Stream a file or other streamable resource
     *
     * @param string|resource $stream The file path or stream resource
     * @param int $status The HTTP status code
     * @param array $headers Additional headers
     * @param string|null $contentType The content type (null to guess)
     * @return static
     */
    public static function stream($stream, int $status = 200, array $headers = [], ?string $contentType = null): static {
        if (is_string($stream)) {
            if (!is_readable($stream)) {
                throw new RuntimeException(sprintf('The file "%s" is not readable.', $stream));
            }
            
            $stream = fopen($stream, 'rb');
            if (false === $stream) {
                throw new RuntimeException(sprintf('Could not open file "%s" for reading.', $stream));
            }
            
            if (null === $contentType) {
                $filePath = is_string($stream) ? $stream : stream_get_meta_data($stream)['uri'];
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $contentType = self::getMimeType($extension) ?? 'application/octet-stream';
            }
        }
        
        if (!is_resource($stream) || 'stream' !== get_resource_type($stream)) {
            throw new InvalidArgumentException('Invalid stream provided.');
        }
        
        $response = new static(null, $status, $headers);
        $response->setContent(function () use ($stream) {
            while (!feof($stream)) {
                echo fread($stream, 8192);
                flush();
                if (connection_status() != CONNECTION_NORMAL) {
                    break;
                }
            }
            fclose($stream);
        });
        
        if ($contentType) {
            $response->setContentType($contentType);
        }
        
        return $response;
    }
    
    /**
     * Create a response that displays a file inline
     *
     * @param string|SplFileInfo $file The file path or SplFileInfo instance
     * @param string|null $name The filename to use for the download
     * @param array $headers Additional headers
     * @return static
     */
    public static function inline($file, ?string $name = null, array $headers = [])
    {
        return static::file($file, $name, $headers);
    }
    
    /**
     * Create a response with no content (status 204)
     *
     * @return static
     */
    public static function noContent(): self
    {
        return new static('', 204);
    }
    
    
    /**
     * Add a MIME type to the known types
     *
     * @param string $mimeType The MIME type
     * @return void
     */
    public static function addMimeType(string $extension, string $mimeType): void {
        self::$mimeTypes[strtolower($extension)] = $mimeType;
    }
    
    /**
     * Check if a string is ASCII
     *
     * @param string $string The string to check
     * @return bool
     */
    protected static function isAscii(string $string): bool {
        return !preg_match('/[^\x00-\x7F]/', $string);
    }
    
    
    /**
     * Check if the response has been sent
     *
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->sent;
    }
    
    /**
     * Cleans or flushes output buffers up to the target level.
     *
     * This method ensures that all output buffers are properly closed, flushed, or cleaned
     * depending on the parameters. It's particularly useful for long-running processes
     * or when you need to ensure all output is sent to the client.
     *
     * @param int $targetLevel The target output buffering level (0 = all buffers)
     * @param bool $flush Whether to flush (true) or clean (false) the buffers
     * @return void
     * @throws \RuntimeException If an output buffer cannot be closed
     */
    public static function closeOutputBuffers(int $targetLevel, bool $flush): void {
        // Get the current output buffer status
        $status = @ob_get_status(true);
        
        // If there are no active output buffers, nothing to do
        if ($status === false) {
            return;
        }
        
        $level = count($status);
        $flags = PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE);
        
        // Process each output buffer from the top down
        while ($level-- > $targetLevel) {
            // Skip if no buffer exists at this level
            if (!isset($status[$level])) {
                continue;
            }
            
            $buffer = $status[$level];
            
            // Check if we can operate on this buffer
            $canOperate = !isset($buffer['del']) 
                ? (!isset($buffer['flags']) || ($buffer['flags'] & $flags) === $flags)
                : $buffer['del'];
                
            if ($canOperate) {
                $success = $flush ? @ob_end_flush() : @ob_end_clean();
                
                if ($success === false) {
                    throw new \RuntimeException(sprintf(
                        'Failed to %s output buffer at level %d',
                        $flush ? 'flush' : 'clean',
                        $level
                    ));
                }
            }
        }
    }
}
