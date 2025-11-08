<?php

namespace App\Core\Http;

use App\Core\App;
use Countable;
use App\Core\Support\ParameterBag;
use JsonException;

class Request implements Countable {
    /**
     * The request query parameters ($_GET)
     */
    public ParameterBag $query;
    
    /**
     * The request request parameters ($_POST)
     */
    public ParameterBag $request;
    
    /**
     * The request attributes (parameters from the URL)
     */
    public ParameterBag $attributes;
    
    /**
     * The request cookies ($_COOKIE)
     */
    public ParameterBag $cookies;
    
    /**
     * The request files ($_FILES)
     */
    public ParameterBag $files;
    
    /**
     * The request server and headers ($_SERVER)
     */
    public ParameterBag $server;
    
    /**
     * The request headers
     */
    public ParameterBag $headers;
    
    /**
     * The request content (raw body)
     */
    protected mixed $content = null;
    
    /**
     * The request method
     */
    protected ?string $method = null;
    
    /**
     * The request URI
     */
    protected ?string $uri = null;
    
    /**
     * The request path
     */
    protected ?string $path = null;
    
    /**
     * The request format
     */
    protected ?string $format = null;
    
    /**
     * The session
     */
    protected mixed $session = null;
    
    /**
     * The route parameters
     */
    protected array $routeParams = [];
    
    /**
     * The trusted proxies
     */
    protected static array $trustedProxies = [];
    
    /**
     * The trusted host patterns
     */
    protected static array $trustedHostPatterns = [];
    
    /**
     * The trusted hosts
     */
    protected static array $trustedHosts = [];
    
    /**
     * The list of trusted headers
     */
    protected static array $trustedHeaders = [
        'forwarded' => 'FORWARDED',
        'client-ip' => 'X_FORWARDED_FOR',
        'client-host' => 'X_FORWARDED_HOST',
        'client-port' => 'X_FORWARDED_PORT',
        'client-proto' => 'X_FORWARDED_PROTO',
    ];
    
    /**
     * Create a new Request instance
     */
    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        $this->initialize($query, $request, $attributes, $cookies, $files, $server, $content);
    }
    
    /**
     * Initialize the request with the given data
     */
    public function initialize(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        $this->query = new ParameterBag($query);
        $this->request = new ParameterBag($request);
        $this->attributes = new ParameterBag($attributes);
        $this->cookies = new ParameterBag($cookies);
        $this->files = new ParameterBag($files);
        $this->server = new ParameterBag($server);
        $this->headers = new ParameterBag($this->getHeaders());
        $this->content = $content;
        
        $this->method = $this->getMethod();
        $this->uri = $this->getUri();
        $this->path = $this->getPathInfo();
        $this->format = $this->getFormat();
    }
    
    /**
     * Create a request from PHP globals
     */
    public static function createFromGlobals(): static {
        $server = $_SERVER;
        
        // Normalize server parameters
        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CLIENT_IP', $server)) {
                unset($server['HTTP_CLIENT_IP']);
            }
            if (array_key_exists('HTTP_X_FORWARDED_FOR', $server)) {
                unset($server['HTTP_X_FORWARDED_FOR']);
            }
        }
        
        $request = new static(
            $_GET,
            $_POST,
            [],
            $_COOKIE,
            self::normalizeFiles($_FILES),
            $server
        );
        
        // Handle request body based on content type
        $contentType = $request->headers->get('CONTENT_TYPE', '');
        $method = strtoupper($request->server->get('REQUEST_METHOD', 'GET'));
        
        // Handle JSON requests
        if (0 === strpos($contentType, 'application/json')) {
            $data = json_decode($request->getContent(), true);
            if (JSON_ERROR_NONE === json_last_error()) {
                $request->request = new ParameterBag($data);
            }
        } 
        // Handle form data for PUT, DELETE, PATCH
        elseif (in_array($method, ['PUT', 'DELETE', 'PATCH'])) {
            if (0 === strpos($contentType, 'application/x-www-form-urlencoded')) {
                parse_str($request->getContent(), $data);
                $request->request = new ParameterBag($data);
            } elseif (0 === strpos($contentType, 'multipart/form-data')) {
                // Handle multipart form data for PUT/DELETE/PATCH
                $request->request = new ParameterBag($_POST);
            }
        }
        
        return $request;
    }
    
    /**
     * Normalize uploaded files array
     * 
     * @param array $files The files array to normalize
     * @return array The normalized files array
     */
    protected static function normalizeFiles(array $files): array {
        $normalized = [];
        
        foreach ($files as $key => $file) {
            if ($file instanceof \App\Core\Http\UploadedFile) {
                $normalized[$key] = $file;
            } elseif (is_array($file)) {
                if (isset($file['tmp_name'])) {
                    $normalized[$key] = new \App\Core\Http\UploadedFile(
                        $file['tmp_name'],
                        $file['name'] ?? null,
                        $file['type'] ?? null,
                        $file['error'] ?? null
                    );
                } else {
                    $normalized[$key] = self::normalizeFiles($file);
                }
            }
        }
        
        return $normalized;
    }
    
    /**
     * Create a new request with a different URI
     */
    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null): static {
        $dup = clone $this;
        
        if (null !== $query) {
            $dup->query = new ParameterBag($query);
        }
        
        if (null !== $request) {
            $dup->request = new ParameterBag($request);
        }
        
        if (null !== $attributes) {
            $dup->attributes = new ParameterBag($attributes);
        }
        
        if (null !== $cookies) {
            $dup->cookies = new ParameterBag($cookies);
        }
        
        if (null !== $files) {
            $dup->files = new ParameterBag($files);
        }
        
        if (null !== $server) {
            $dup->server = new ParameterBag($server);
            $dup->headers = new ParameterBag($dup->getHeaders());
        }
        
        return $dup;
    }
    
    /**
     * Get the request method
     */
    public function getMethod(): string {
        if (null === $this->method) {
            $this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET')) ?: 'GET';
            
            if ('POST' === $this->method) {
                if ($method = $this->headers->get('X-HTTP-METHOD-OVERRIDE')) {
                    $this->method = strtoupper($method);
                } elseif ($method = $this->request->get('_method')) {
                    $this->method = strtoupper($method);
                }
            }
        }
        
        return $this->method;
    }
    
    /**
     * Set the request method
     */
    public function setMethod($method) {
        $this->method = strtoupper($method);
        return $this;
    }
    
    /**
     * Get the request URI
     */
    public function getUri() {
        if (null === $this->uri) {
            $this->uri = $this->prepareRequestUri();
        }
        
        return $this->uri;
    }
    
    /**
     * Get the path info (the part of the URL after the script name)
     */
    public function getPathInfo() {
        if (null === $this->path) {
            $this->path = $this->preparePathInfo();
        }
        
        return $this->path;
    }
    
    /**
     * Get the request format
     */
    public function getFormat() {
        if (null === $this->format) {
            $this->format = $this->get('_format', 'html');
        }
        
        return $this->format;
    }
    
    /**
     * Set the request format
     */
    public function setFormat($format) {
        $this->format = $format;
        return $this;
    }
    
    /**
     * Get the request content
     *
     * @param bool $asResource If true, returns the content as a resource
     * @return string|resource The request content or a resource
     * @throws \LogicException When the content cannot be read twice
     */
    public function getContent(bool $asResource = false) {
        if (false === $this->content || (true === $asResource && null !== $this->content)) {
            throw new \LogicException('getContent() can only be called once when using the resource return type.');
        }
        
        if (true === $asResource) {
            $this->content = false;
            return fopen('php://input', 'r');
        }
        
        if (null === $this->content) {
            $this->content = file_get_contents('php://input') ?: '';
        }
        
        return $this->content;
    }
    
    /**
     * Get the request content as a string
     */
    public function getContentAsString(): string {
        return (string) $this->getContent();
    }
    
    /**
     * Get the request content as an array (for JSON requests)
     *
     * @param bool $assoc When true, returns an associative array instead of an object
     * @return array|\stdClass The parsed JSON data
     * @throws JsonException If the content is not valid JSON
     */
    public function getContentAsJson(bool $assoc = true) {
        $content = $this->getContentAsString();
        
        if ('' === $content) {
            return $assoc ? [] : new \stdClass();
        }
        
        $data = json_decode($content, $assoc, 512, JSON_THROW_ON_ERROR);
        
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonException(json_last_error_msg(), json_last_error());
        }
        
        return $data;
    }
    
    /**
     * Get the request content as XML
     *
     * @return \SimpleXMLElement The parsed XML data
     * @throws \RuntimeException If the content is not valid XML
     */
    public function getContentAsXml() {
        $content = $this->getContentAsString();
        
        if ('' === $content) {
            return new \SimpleXMLElement('<?xml version="1.0"?><root></root>');
        }
        
        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);
        
        try {
            $xml = new \SimpleXMLElement($content, LIBXML_NONET);
            
            if ($error = libxml_get_last_error()) {
                throw new \RuntimeException($error->message, $error->code);
            }
            
            return $xml;
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to parse XML: ' . $e->getMessage(), 0, $e);
        } finally {
            libxml_use_internal_errors($internalErrors);
            libxml_disable_entity_loader($disableEntities);
        }
    }
    
    /**
     * Get the request headers
     */
    public function getHeaders() {
        $headers = [];
        
        foreach ($this->server->all() as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (\in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headers[$key] = $value;
            }
        }
        
        return $headers;
    }
    
    /**
     * Get a header by name
     */
    public function getHeader($key, $default = null) {
        return $this->headers->get($key, $default);
    }
    
    /**
     * Get the session
     */
    public function getSession() {
        if (null === $this->session) {
            $this->session = App::getInstance()->session();
        }
        
        return $this->session;
    }
    
    /**
     * Set the session
     */
    public function setSession($session) {
        $this->session = $session;
        return $this;
    }
    
    /**
     * Get the client IP address
     */
    public function getClientIp(): string {
        $ip = $this->server->get('REMOTE_ADDR', '0.0.0.0');
        
        // Check for IPs from proxy
        $trustedProxies = $this->getTrustedProxies();
        
        if ($trustedProxies && $this->isFromTrustedProxy()) {
            // Check for IPs from the X-Forwarded-For header
            if ($forwardedFor = $this->headers->get('X-Forwarded-For')) {
                $clientIps = array_map('trim', explode(',', $forwardedFor));
                $clientIps = array_diff($clientIps, $trustedProxies);
                
                if (!empty($clientIps)) {
                    $ip = array_shift($clientIps);
                }
            }
            // Check for IPs from the X-Real-IP header
            elseif ($realIp = $this->headers->get('X-Real-IP')) {
                $ip = $realIp;
            }
        }
        
        // Validate the IP address
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return $ip;
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Get the client IPs
     * 
     * @return array An array of IP addresses, the first being the client IP
     */
    public function getClientIps(): array {
        $ip = $this->getClientIp();
        
        if (!$this->isFromTrustedProxy()) {
            return [$ip];
        }
        
        $ips = [];
        
        // Check for IPs from the X-Forwarded-For header
        if ($forwardedFor = $this->headers->get('X-Forwarded-For')) {
            $ips = array_map('trim', explode(',', $forwardedFor));
            $ips = array_diff($ips, $this->getTrustedProxies());
            $ips = array_filter($ips, function($ip) {
                return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
            });
        }
        
        // Add the client IP to the end of the array
        $ips[] = $ip;
        
        return array_unique($ips);
    }
    
    /**
     * Check if the request comes from a trusted proxy
     */
    public function isFromTrustedProxy(): bool {
        $ip = $this->server->get('REMOTE_ADDR');
        
        if (!$ip) {
            return false;
        }
        
        $trustedProxies = $this->getTrustedProxies();
        
        if (empty($trustedProxies)) {
            return false;
        }
        
        return in_array($ip, $trustedProxies, true);
    }
    
    /**
     * Get the trusted proxies
     */
    public static function getTrustedProxies(): array {
        return self::$trustedProxies;
    }
    
    /**
     * Set the trusted proxies
     */
    public static function setTrustedProxies(array $proxies): void {
        self::$trustedProxies = $proxies;
    }
    
    /**
     * Get the client user agent
     */
    public function getUserAgent() {
        return $this->headers->get('User-Agent');
    }
    
    /**
     * Check if the request is secure
     */
    public function isSecure() {
        $https = $this->server->get('HTTPS');
        return !empty($https) && 'off' !== strtolower($https);
    }
    
    /**
     * Check if the request is an AJAX request
     */
    public function isAjax() {
        return 'XMLHttpRequest' == $this->headers->get('X-Requested-With');
    }
    
    /**
     * Check if the request is a JSON request
     */
    public function isJson(): bool {
        $contentType = $this->getContentType();
        return false !== strpos($contentType, '/json') || false !== strpos($contentType, '+json');
    }
    
    /**
     * Check if the request is a JSON API request
     */
    public function isJsonApi(): bool {
        return $this->isJson() || 'application/vnd.api+json' === $this->getContentType();
    }
    
    /**
     * Check if the request is an XML request
     */
    public function isXml(): bool {
        $contentType = $this->getContentType();
        return false !== strpos($contentType, '/xml') || false !== strpos($contentType, '+xml');
    }
    
    /**
     * Check if the request is a form submission
     */
    public function isForm(): bool {
        $contentType = $this->getContentType();
        return in_array($contentType, ['application/x-www-form-urlencoded', 'multipart/form-data']);
    }
    
    /**
     * Get the preferred content type from the Accept header
     */
    public function getPreferredContentType(array $contentTypes = null): ?string {
        $accepts = $this->getAcceptableContentTypes();
        
        if (empty($accepts)) {
            return $contentTypes[0] ?? null;
        }
        
        if (null === $contentTypes) {
            return $accepts[0];
        }
        
        foreach ($accepts as $accept) {
            if (in_array($accept, ['*/*', 'text/*', 'application/*'])) {
                return $contentTypes[0];
            }
            
            if (in_array($accept, $contentTypes)) {
                return $accept;
            }
        }
        
        return null;
    }
    
    /**
     * Get the acceptable content types
     */
    public function getAcceptableContentTypes(): array {
        $accept = $this->headers->get('Accept', '*/*');
        $accepts = [];
        
        foreach (array_filter(explode(',', $accept)) as $type) {
            $accepts[] = strtolower(trim(explode(';', $type)[0]));
        }
        
        return array_unique($accepts);
    }
    
    /**
     * Check if the request accepts any of the given content types
     */
    public function accepts($contentTypes): bool {
        $accepts = $this->getAcceptableContentTypes();
        
        if (empty($accepts)) {
            return true;
        }
        
        $contentTypes = is_array($contentTypes) ? $contentTypes : func_get_args();
        
        foreach ($contentTypes as $contentType) {
            if (in_array(strtolower($contentType), $accepts, true)) {
                return true;
            }
            
            // Check for wildcards
            foreach ($accepts as $accept) {
                $pattern = str_replace('*', '.*', $accept);
                
                if (preg_match('{' . $pattern . '}i', $contentType)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get the preferred language from the Accept-Language header
     */
    public function getPreferredLanguage(array $languages = null): ?string {
        $accepts = $this->getLanguages();
        
        if (empty($accepts)) {
            return $languages[0] ?? null;
        }
        
        if (null === $languages) {
            return $accepts[0];
        }
        
        foreach ($accepts as $accept) {
            if (in_array($accept, $languages)) {
                return $accept;
            }
            
            // Check for language codes (e.g., 'en' matches 'en-US')
            foreach ($languages as $language) {
                if (str_starts_with($language, $accept . '-') || str_starts_with($accept, $language . '-')) {
                    return $language;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get the accepted languages from the Accept-Language header
     */
    public function getLanguages(): array {
        $acceptLanguage = $this->headers->get('Accept-Language', '*');
        $languages = [];
        
        foreach (array_filter(explode(',', $acceptLanguage)) as $part) {
            $parts = explode(';', $part);
            $language = strtolower(trim($parts[0]));
            
            if (str_contains($language, '-')) {
                $language = explode('-', $language, 2)[0];
            }
            
            $languages[] = $language;
        }
        
        return array_unique($languages);
    }
    
    /**
     * Check if the request accepts any of the given languages
     */
    public function acceptsLanguage($languages): bool {
        $accepts = $this->getLanguages();
        
        if (empty($accepts)) {
            return true;
        }
        
        $languages = is_array($languages) ? $languages : func_get_args();
        
        foreach ($languages as $language) {
            $language = strtolower($language);
            
            if (in_array($language, $accepts, true)) {
                return true;
            }
            
            // Check for language codes (e.g., 'en' matches 'en-US')
            foreach ($accepts as $accept) {
                if (str_starts_with($language, $accept . '-') || str_starts_with($accept, $language . '-')) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get the content type
     */
    public function getContentType(): ?string {
        $contentType = $this->headers->get('Content-Type');
        
        if (empty($contentType)) {
            return null;
        }
        
        // Remove the charset if present
        if (false !== $pos = strpos($contentType, ';')) {
            $contentType = substr($contentType, 0, $pos);
        }
        
        return trim($contentType);
    }
    
    /**
     * Get the content type with parameters
     */
    public function getContentTypeWithParameters(): ?string {
        return $this->headers->get('Content-Type');
    }
    
    /**
     * Get the content type parameters
     */
    public function getContentTypeParameters(): array {
        $contentType = $this->headers->get('Content-Type');
        
        if (empty($contentType)) {
            return [];
        }
        
        $parameters = [];
        $parts = explode(';', $contentType);
        
        // Remove the content type
        array_shift($parts);
        
        foreach ($parts as $part) {
            if (str_contains($part, '=')) {
                list($key, $value) = explode('=', $part, 2);
                $parameters[trim($key)] = trim($value, ' \t"\'');
            }
        }
        
        return $parameters;
    }
    
    /**
     * Get the content type charset
     */
    public function getCharset(): ?string {
        $parameters = $this->getContentTypeParameters();
        return $parameters['charset'] ?? null;
    }
    
    /**
     * Get the request scheme (http or https)
     */
    public function getScheme(): string {
        return $this->isSecure() ? 'https' : 'http';
    }
    
    /**
     * Get the request host
     */
    public function getHost(): string {
        $host = $this->headers->get('Host');
        
        if (!$host) {
            $host = $this->server->get('SERVER_NAME', $this->server->get('SERVER_ADDR', 'localhost'));
        }
        
        // Remove port number from host
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));
        
        // Check for trusted hosts
        if (!empty(self::$trustedHostPatterns)) {
            foreach (self::$trustedHostPatterns as $pattern) {
                if (preg_match($pattern, $host)) {
                    return $host;
                }
            }
            
            throw new \UnexpectedValueException(sprintf('Untrusted Host: %s', $host));
        }
        
        return $host;
    }
    
    /**
     * Get the request port
     */
    public function getPort(): int {
        if ($host = $this->headers->get('Host')) {
            if (str_contains($host, ':')) {
                return (int) substr($host, strrpos($host, ':') + 1);
            }
            
            return $this->isSecure() ? 443 : 80;
        }
        
        return (int) $this->server->get('SERVER_PORT', $this->isSecure() ? 443 : 80);
    }
    
    /**
     * Get the request URL
     */
    public function getUrl(): string {
        return sprintf(
            '%s://%s%s',
            $this->getScheme(),
            $this->getHttpHost(),
            $this->getRequestUri()
        );
    }
    
    /**
     * Get the HTTP host (host:port)
     */
    public function getHttpHost(): string {
        $host = $this->getHost();
        $port = $this->getPort();
        
        if (('http' === $this->getScheme() && 80 !== $port) || ('https' === $this->getScheme() && 443 !== $port)) {
            return $host . ':' . $port;
        }
        
        return $host;
    }
    
    /**
     * Get the request URI (path + query string)
     */
    public function getRequestUri(): string {
        if (null === $this->uri) {
            $this->uri = $this->prepareRequestUri();
        }
        
        return $this->uri;
    }
    
    /**
     * Get the base URL
     */
    public function getBaseUrl(): string {
        $baseUrl = str_replace('\\', '/', dirname($this->server->get('SCRIPT_NAME')));
        return rtrim($baseUrl, '/');
    }
    
    /**
     * Get the root URL
     */
    public function getRootUrl(): string {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }
    
    /**
     * Get the base path
     */
    public function getBasePath(): string {
        return rtrim(parse_url($this->getBaseUrl(), PHP_URL_PATH), '/');
    }
    
    /**
     * Get the full URL for the request
     */
    public function fullUrl(): string {
        return $this->getUrl();
    }
    
    /**
     * Get the full URL with the given query string parameters
     */
    public function fullUrlWithQuery(array $query): string {
        $query = array_merge($this->query->all(), $query);
        return $this->url() . (empty($query) ? '' : '?' . http_build_query($query, '', '&'));
    }
    
    /**
     * Get the current URL without the query string
     */
    public function url(): string {
        return $this->getScheme() . '://' . $this->getHttpHost() . $this->getPathInfo();
    }
    
    /**
     * Get the current path info
     */
    public function path(): string {
        return $this->getPathInfo();
    }
    
    /**
     * Get the current decoded path info
     */
    public function decodedPath(): string {
        return rawurldecode($this->path());
    }
    
    /**
     * Get a segment from the path
     */
    public function segment(int $index, $default = null) {
        $segments = explode('/', trim($this->path(), '/'));
        return $segments[$index - 1] ?? $default;
    }
    
    /**
     * Get all of the segments for the request path
     */
    public function segments(): array {
        return array_values(array_filter(explode('/', $this->path()), function ($segment) {
            return $segment !== '';
        }));
    }
    
    /**
     * Get the request body as JSON
     * 
     * @deprecated Use getContentAsJson() instead
     */
    public function json($key = null, $default = null) {
        try {
            $data = $this->getContentAsJson(true);
            
            if (null === $key) {
                return $data;
            }
            
            // Support dot notation for nested keys
            if (str_contains($key, '.')) {
                $value = $data;
                
                foreach (explode('.', $key) as $segment) {
                    if (is_array($value) && array_key_exists($segment, $value)) {
                        $value = $value[$segment];
                    } else {
                        return $default;
                    }
                }
                
                return $value;
            }
            
            return $data[$key] ?? $default;
        } catch (JsonException $e) {
            if (null === $key) {
                return [];
            }
            
            return $default;
        }
    }
    
    /**
     * Get the request body as XML
     * 
     * @return \SimpleXMLElement The parsed XML data
     * @throws \RuntimeException If the content is not valid XML
     */
    public function xml() {
        return $this->getContentAsXml();
    }
    
    /**
     * Get the request body as an array (for form data)
     * 
     * @return array The form data
     */
    public function form(): array {
        return $this->request->all();
    }
    
    /**
     * Get all input data (query + request)
     * 
     * @return array The input data
     */
    public function all(): array {
        return array_merge($this->query->all(), $this->request->all());
    }
    
    /**
     * Get only the specified input items
     * 
     * @param array|string $keys The keys to get
     * @return array The filtered input data
     */
    public function only($keys): array {
        $keys = is_array($keys) ? $keys : func_get_args();
        $results = [];
        
        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }
        
        return $results;
    }
    
    /**
     * Get all input except for the specified items
     * 
     * @param array|string $keys The keys to exclude
     * @return array The filtered input data
     */
    public function except($keys): array {
        $keys = is_array($keys) ? $keys : func_get_args();
        $results = $this->all();
        
        foreach ($keys as $key) {
            unset($results[$key]);
        }
        
        return $results;
    }
    
    /**
     * Check if the request has a given input key
     * 
     * @param string|array $key The key(s) to check
     * @return bool True if the key exists, false otherwise
     */
    public function has($key): bool {
        $keys = is_array($key) ? $key : func_get_args();
        
        foreach ($keys as $value) {
            if ($this->query->has($value) || $this->request->has($value)) {
                continue;
            }
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if the request has any of the given input keys
     * 
     * @param string|array $keys The key(s) to check
     * @return bool True if any of the keys exist, false otherwise
     */
    public function hasAny($keys): bool {
        $keys = is_array($keys) ? $keys : func_get_args();
        
        foreach ($keys as $key) {
            if ($this->query->has($key) || $this->request->has($key)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if the request has all of the given input keys
     * 
     * @param string|array $keys The key(s) to check
     * @return bool True if all of the keys exist, false otherwise
     */
    public function hasAll($keys): bool {
        return $this->has($keys);
    }
    
    /**
     * Check if the request has a non-empty value for a given input key
     * 
     * @param string|array $key The key(s) to check
     * @return bool True if the key exists and is not empty, false otherwise
     */
    public function filled($key): bool {
        $keys = is_array($key) ? $key : func_get_args();
        
        foreach ($keys as $value) {
            if ($this->isEmptyString($this->get($value))) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if the request has any non-empty values for the given input keys
     * 
     * @param string|array $keys The key(s) to check
     * @return bool True if any of the keys exist and are not empty, false otherwise
     */
    public function anyFilled($keys): bool {
        $keys = is_array($keys) ? $keys : func_get_args();
        
        foreach ($keys as $key) {
            if (!$this->isEmptyString($this->get($key))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if the given input key is missing from the request
     * 
     * @param string|array $key The key(s) to check
     * @return bool True if the key is missing, false otherwise
     */
    public function missing($key): bool {
        return !$this->has($key);
    }
    
    /**
     * Check if the given value is an empty string
     */
    protected function isEmptyString($value): bool {
        if (is_null($value)) {
            return true;
        }
        
        if (is_string($value) && trim($value) === '') {
            return true;
        }
        
        if ((is_array($value) || $value instanceof Countable) && count($value) === 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get a parameter from the request
     */
    public function get($key, $default = null) {
        if ($this->attributes->has($key)) {
            return $this->attributes->get($key);
        }
        
        if ($this->query->has($key)) {
            return $this->query->get($key);
        }
        
        if ($this->request->has($key)) {
            return $this->request->get($key);
        }
        
        // Check for nested parameters (e.g., 'user[name]')
        if (str_contains($key, '.')) {
            $value = $this->all();
            
            foreach (explode('.', $key) as $segment) {
                if (is_array($value) && array_key_exists($segment, $value)) {
                    $value = $value[$segment];
                } elseif (is_object($value) && isset($value->$segment)) {
                    $value = $value->$segment;
                } else {
                    return $default;
                }
            }
            
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Get a boolean value from the request
     */
    public function boolean($key, $default = false): bool {
        $value = $this->get($key, $default);
        
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $value = strtolower($value);
            
            if (in_array($value, ['1', 'true', 'on', 'yes', 'y'])) {
                return true;
            }
            
            if (in_array($value, ['0', 'false', 'off', 'no', 'n', ''])) {
                return false;
            }
        }
        
        return (bool) $value;
    }
    
    /**
     * Get an integer value from the request
     */
    public function integer($key, $default = 0): int {
        return (int) $this->get($key, $default);
    }
    
    /**
     * Get a float value from the request
     */
    public function float($key, $default = 0.0): float {
        return (float) $this->get($key, $default);
    }
    
    /**
     * Get a string value from the request
     */
    public function string($key, $default = ''): string {
        $value = $this->get($key, $default);
        return is_scalar($value) ? (string) $value : $default;
    }
    
    /**
     * Get an array value from the request
     */
    public function array($key, $default = []): array {
        $value = $this->get($key, $default);
        return is_array($value) ? $value : $default;
    }
    
    /**
     * Get a date value from the request
     */
    public function date($key, $format = null, $default = null) {
        $value = $this->get($key);
        
        if (is_null($value)) {
            return $default;
        }
        
        try {
            $date = new \DateTime($value);
            return $format ? $date->format($format) : $date;
        } catch (\Exception $e) {
            return $default;
        }
    }
    
    /**
     * Get an enum value from the request
     */
    public function enum(string $key, string $enumClass, $default = null) {
        $value = $this->get($key);
        
        if (is_null($value)) {
            return $default;
        }
        
        if (!enum_exists($enumClass)) {
            throw new \InvalidArgumentException(sprintf('Enum class %s does not exist', $enumClass));
        }
        
        return $enumClass::tryFrom($value) ?? $default;
    }
    
    /**
     * Get the uploaded file from the request
     * 
     * @param string $key The file input name
     * @param mixed $default Default value if the file doesn't exist
     * @return \App\Core\Http\UploadedFile|null The uploaded file or null if not found
     */
    public function file($key, $default = null) {
        $file = $this->files->get($key, $default);
        
        if ($file instanceof \App\Core\Http\UploadedFile) {
            return $file;
        }
        
        if (is_array($file) && isset($file['tmp_name'])) {
            return new \App\Core\Http\UploadedFile(
                $file['tmp_name'],
                $file['name'] ?? null,
                $file['type'] ?? null,
                $file['error'] ?? null
            );
        }
        
        return $default;
    }
    
    /**
     * Count the number of items in the request
     * 
     * @return int The number of items
     */
    public function count(): int
    {
        return count($this->all());
    }
    
    /**
     * Get all uploaded files from the request
     */
    public function allFiles(): array {
        return $this->files->all();
    }
    
    /**
     * Check if the request has a file
     */
    public function hasFile($key): bool {
        $file = $this->file($key);
        return $file instanceof \App\Core\Http\UploadedFile && $file->isValid();
    }
    
    /**
     * Get the bearer token from the request
     */
    public function bearerToken(): ?string {
        $header = $this->headers->get('Authorization', '');
        
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        
        return null;
    }
    
    /**
     * Get the CSRF token from the request
     */
    public function csrfToken(): ?string {
        return $this->get('_token') ?? $this->headers->get('X-CSRF-TOKEN');
    }
    
    /**
     * Check if the request has a valid CSRF token
     */
    public function hasValidCsrfToken(): bool {
        $token = $this->csrfToken();
        
        if (empty($token)) {
            return false;
        }
        
        $session = $this->getSession();
        
        if (!$session->has('_token')) {
            return false;
        }
        
        return hash_equals($session->get('_token'), $token);
    }
    
    /**
     * Set route parameters
     */
    public function setRouteParams(array $params) {
        $this->routeParams = $params;
        return $this;
    }
    
    /**
     * Get route parameters
     */
    public function route($key = null, $default = null) {
        if (null === $key) {
            return $this->routeParams;
        }
        
        return $this->routeParams[$key] ?? $default;
    }
    
    /**
     * Prepare the request URI
     */
    protected function prepareRequestUri() {
        $requestUri = '';
        
        if ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');
            
            // Remove query string from request URI
            if (false !== $pos = strpos($requestUri, '?')) {
                $requestUri = substr($requestUri, 0, $pos);
            }
        }
        
        return $requestUri;
    }
    
    /**
     * Prepare the path info
     */
    protected function preparePathInfo() {
        if (null === ($requestUri = $this->getUri())) {
            return '/';
        }
        
        // Remove the query string from REQUEST_URI
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        
        $requestUri = rawurldecode($requestUri);
        
        // Remove script filename from the path
        $scriptName = $this->getScriptName();
        $pathInfo = '/';
        
        // Remove the query string from script name if needed
        if (false !== $pos = strpos($scriptName, '?')) {
            $scriptName = substr($scriptName, 0, $pos);
        }
        
        if (!empty($scriptName) && 0 === strpos($requestUri, $scriptName)) {
            $pathInfo = substr($requestUri, strlen($scriptName));
        } elseif (!empty($scriptName) && 0 === strpos($requestUri, dirname($scriptName))) {
            $pathInfo = substr($requestUri, strlen(dirname($scriptName)));
        }
        
        if (false !== $pos = strpos($pathInfo, '?')) {
            $pathInfo = substr($pathInfo, 0, $pos);
        }
        
        return (string) $pathInfo;
    }
    
    /**
     * Get the script name
     */
    protected function getScriptName() {
        $scriptName = $this->server->get('SCRIPT_NAME');
        
        if (empty($scriptName)) {
            $scriptName = $this->server->get('ORIG_SCRIPT_NAME');
        }
        
        return $scriptName;
    }
    
    /**
     * Magic method to get parameters from the request
     */
    public function __get($key) {
        return $this->get($key);
    }
    
    /**
     * Magic method to check if a parameter exists
     */
    public function __isset($key) {
        return $this->attributes->has($key) || 
               $this->query->has($key) || 
               $this->request->has($key);
    }
}
