<?php

namespace App\Http\Controllers\Traits;

/**
 * Trait HasViewRendering
 *
 * Provides view rendering with layout support, SEO injection, and CSRF token auto-injection.
 * Include this trait in controllers that render HTML views.
 */
trait HasViewRendering
{
    /**
     * Default layout for views.
     */
    protected string $layout = 'layouts/base';

    /**
     * Render a view with data.
     *
     * Accepts both slash notation (`admin/users/index`) and dot notation
     * (`admin.users.index`) — dots are converted to slashes automatically.
     *
     * Includes a self-rendering safeguard: if the view file already produced
     * a full HTML document (starts with <!DOCTYPE>), we skip the layout to
     * avoid double-render.
     *
     * Auto-injects SEO meta tags via generateSEO() if not already provided
     * in $data['seo'] — gives every page proper OG / Twitter / JSON-LD.
     */
    protected function render(string $view, array $data = [], ?string $layout = null): void
    {
        if ($layout !== null) {
            $this->layout = $layout;
        }

        // Normalize dot-notation (admin.users.index) to slash-notation (admin/users/index)
        $view = str_replace('.', '/', $view);

        // Ensure translation helper is loaded before any view content
        if (!function_exists('__')) {
            require_once dirname(__DIR__, 3) . '/Helpers/TranslationHelper.php';
        }

        // Ensure ViewHelper is available in all views
        if (!class_exists('App\\Helpers\\ViewHelper')) {
            require_once dirname(__DIR__, 3) . '/Helpers/ViewHelper.php';
        }

        // Start output buffering to prevent header issues
        ob_start();

        // Merge with class data
        $data = array_merge($this->data ?? [], $data);

        // Auto-inject SEO meta tags (only if not explicitly provided)
        if (!isset($data['seo']) || !is_array($data['seo'])) {
            $data['seo'] = $this->generateSEO($data);
        }

        // Auto-inject CSRF token into every rendered view (so forms always have a valid token)
        if (!isset($data['csrf_token'])) {
            $data['csrf_token'] = $this->getCsrfToken();
        }

        // Ensure BASE_URL is available in views (views are included before layout loads bootstrap.php)
        if (!isset($data['BASE_URL'])) {
            $data['BASE_URL'] = defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhome';
        }

        // Extract data to variables
        extract($data);

        // Include view content
        $viewPath = dirname(__DIR__, 3) . '/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "View not found: " . $view;
        }

        // Get content and clean buffer
        $content = ob_get_clean();

        // Self-rendering safeguard: if the view already produced a full HTML
        // document (starts with <!DOCTYPE>), it owns the layout — emit as-is
        // to prevent double-render.
        if (preg_match('/^\s*<!DOCTYPE/i', $content)) {
            echo $content;
            return;
        }

        // If layout exists, render layout with content
        if ($this->layout) {
            $layoutPath = dirname(__DIR__, 3) . '/views/' . $this->layout . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    /**
     * Alias for render()
     */
    public function view(string $view, array $data = []): void
    {
        $this->render($view, $data);
    }

    /**
     * Generate SEO meta tag payload for auto-injection.
     *
     * Reads from common $data keys (page_title, meta_description, etc.) and
     * falls back to sensible defaults. The returned array is consumed by
     * header.php and base.php to render <title>, <meta>, Open Graph,
     * Twitter Card, and optional JSON-LD structured data.
     */
    protected function generateSEO(array $data = []): array
    {
        $title = $data['page_title'] ?? $data['title'] ?? 'APS Dream Home - Premium Real Estate';
        $description = $data['meta_description'] ?? $data['description'] ?? $data['page_description']
            ?? 'Find your dream home with APS Dream Home. Premium plots, flats, villas, and farmhouses across India.';
        $keywords = $data['meta_keywords'] ?? $data['keywords']
            ?? 'real estate, plots, flats, villas, property, APS Dream Home, India';

        $image = $data['og_image'] ?? (defined('BASE_URL') ? BASE_URL : '') . '/assets/images/og-default.jpg';

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $url = $data['canonical_url']
            ?? ($protocol . '://' . $host . $requestUri);

        return [
            'title'              => $title,
            'description'        => $description,
            'keywords'           => $keywords,
            'og_title'           => $title,
            'og_description'     => $description,
            'og_image'           => $image,
            'og_url'             => $url,
            'og_type'            => $data['og_type'] ?? 'website',
            'twitter_card'       => 'summary_large_image',
            'twitter_title'      => $title,
            'twitter_description'=> $description,
            'twitter_image'      => $image,
            'canonical'          => $url,
            'json_ld'            => $data['json_ld'] ?? null
        ];
    }

    /**
     * Render error page with custom message.
     */
    protected function renderError(string $title, string $message, int $code = 500): void
    {
        http_response_code($code);
        $this->render('errors/generic', [
            'page_title' => $title,
            'error_title' => $title,
            'error_message' => $message,
            'error_code' => $code
        ]);
    }

    /**
     * Get views base path.
     */
    protected function getViewsBasePath(): string
    {
        return realpath(dirname(__DIR__, 3) . '/views');
    }
}