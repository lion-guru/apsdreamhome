<?php
/**
 * Theme Helper
 * 
 * Light/Dark mode theme system
 * Stores preference in localStorage (JS) and session (PHP)
 */

namespace App\Helpers;

class Theme
{
    /**
     * Get current theme
     */
    public static function current(): string
    {
        @session_start();
        return $_SESSION['theme'] ?? 'light';
    }

    /**
     * Set theme
     */
    public static function set(string $theme): void
    {
        @session_start();
        $_SESSION['theme'] = in_array($theme, ['light', 'dark']) ? $theme : 'light';
    }

    /**
     * Toggle theme
     */
    public static function toggle(): string
    {
        $current = self::current();
        $new = $current === 'light' ? 'dark' : 'light';
        self::set($new);
        return $new;
    }

    /**
     * Get CSS class for body
     */
    public static function bodyClass(): string
    {
        return self::current() === 'dark' ? 'dark-mode' : '';
    }

    /**
     * Get theme icon
     */
    public static function icon(): string
    {
        return self::current() === 'dark' ? 'fa-sun' : 'fa-moon';
    }

    /**
     * Render theme toggle button
     */
    public static function toggleButton(): string
    {
        $icon = self::icon();
        return '<button id="theme-toggle" class="btn btn-outline-secondary btn-sm" onclick="toggleTheme()">
            <i class="fas ' . $icon . '"></i>
        </button>';
    }

    /**
     * Get dark mode CSS
     */
    public static function darkModeCSS(): string
    {
        return '
        <style>
            body.dark-mode { background-color: #1a1a2e !important; color: #e0e0e0 !important; }
            body.dark-mode .card { background-color: #16213e !important; border-color: #0f3460 !important; color: #e0e0e0 !important; }
            body.dark-mode .table { color: #e0e0e0 !important; }
            body.dark-mode .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(255,255,255,0.05) !important; }
            body.dark-mode .form-control { background-color: #16213e !important; border-color: #0f3460 !important; color: #e0e0e0 !important; }
            body.dark-mode .modal-content { background-color: #16213e !important; color: #e0e0e0 !important; }
            body.dark-mode .navbar { background-color: #16213e !important; }
            body.dark-mode .sidebar { background-color: #16213e !important; }
            body.dark-mode a { color: #4fc3f7 !important; }
            body.dark-mode .text-muted { color: #9e9e9e !important; }
            body-dark-mode .btn-outline-secondary { border-color: #0f3460 !important; color: #e0e0e0 !important; }
        </style>
        <script>
            function toggleTheme() {
                const body = document.body;
                const isDark = body.classList.toggle("dark-mode");
                localStorage.setItem("theme", isDark ? "dark" : "light");
                const icon = document.querySelector("#theme-toggle i");
                if (icon) icon.className = isDark ? "fas fa-sun" : "fas fa-moon";
            }
            // Apply saved theme on load
            (function() {
                const saved = localStorage.getItem("theme");
                if (saved === "dark") {
                    document.body.classList.add("dark-mode");
                }
            })();
        </script>';
    }
}
