<?php
/**
 * Theme Helper — LIGHT MODE ONLY (dark mode removed 2026-09-03)
 * Single source of truth: style.css + premium-theme.css light tokens
 */

namespace App\Helpers;

class Theme
{
    /**
     * Get current theme — always light (dark mode removed)
     */
    public static function current(): string
    {
        return 'light';
    }

    /**
     * Set theme — no-op (dark mode removed)
     */
    public static function set(string $theme): void
    {
        // no-op — light mode only
    }

    /**
     * Toggle theme — no-op (dark mode removed)
     */
    public static function toggle(): string
    {
        return 'light';
    }

    /**
     * Get CSS class for body — always empty (light only)
     */
    public static function bodyClass(): string
    {
        return '';
    }

    /**
     * Get theme icon — light mode only (dark mode removed)
     */
    public static function icon(): string
    {
        return 'fa-moon';
    }

    /**
     * Render theme toggle button — deprecated (dark mode removed, light only)
     */
    public static function toggleButton(): string
    {
        return '';
    }

    /**
     * Get dark mode CSS — deprecated (dark mode removed, light mode is single source of truth)
     */
    public static function darkModeCSS(): string
    {
        return '';
    }
}
