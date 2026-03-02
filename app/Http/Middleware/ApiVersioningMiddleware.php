<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiVersioningMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Extract API version from request
        $version = $this->extractVersion($request);
        
        // Set version in request for later use
        $request->attributes->set('api_version', $version);
        
        // Validate version support
        if (!$this->isVersionSupported($version)) {
            return response()->json([
                'error' => 'Unsupported API version',
                'message' => "API version {$version} is not supported",
                'supported_versions' => $this->getSupportedVersions()
            ], 400);
        }
        
        // Add version headers
        $response = $next($request);
        $response->headers->set('API-Version', $version);
        $response->headers->set('API-Supported-Versions', implode(',', $this->getSupportedVersions()));
        
        return $response;
    }
    
    /**
     * Extract API version from request
     */
    private function extractVersion(Request $request): string
    {
        // Priority order: Header > URL > Query > Default
        
        // 1. Check Accept header
        $acceptHeader = $request->header('Accept');
        if ($acceptHeader && preg_match('/application\/vnd\.apsdreamhome\.v(\d+\.\d+)\+json/', $acceptHeader, $matches)) {
            return $matches[1];
        }
        
        // 2. Check custom API version header
        $versionHeader = $request->header('API-Version');
        if ($versionHeader) {
            return $versionHeader;
        }
        
        // 3. Check URL path
        $path = $request->path();
        if (preg_match('/^api\/v(\d+\.\d+)\//', $path, $matches)) {
            return $matches[1];
        }
        
        // 4. Check query parameter
        $versionQuery = $request->query('version');
        if ($versionQuery) {
            return $versionQuery;
        }
        
        // 5. Return default version
        return $this->getDefaultVersion();
    }
    
    /**
     * Check if version is supported
     */
    private function isVersionSupported(string $version): bool
    {
        return in_array($version, $this->getSupportedVersions());
    }
    
    /**
     * Get supported versions
     */
    private function getSupportedVersions(): array
    {
        return [
            '1.0', // Legacy version
            '1.1', // Current stable version
            '2.0', // Latest version
        ];
    }
    
    /**
     * Get default version
     */
    private function getDefaultVersion(): string
    {
        return '2.0';
    }
}
