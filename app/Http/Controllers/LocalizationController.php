<?php

namespace App\Http\Controllers;

use App\Services\Localization\LocalizationService;
use Psr\Log\LoggerInterface;

class LocalizationController
{
    private LocalizationService $localizationService;
    private LoggerInterface $logger;

    public function __construct(LocalizationService $localizationService, LoggerInterface $logger)
    {
        $this->localizationService = $localizationService;
        $this->logger = $logger;
    }

    /**
     * Set locale for current session
     */
    public function setLocale()
    {
        try {
            $locale = request()->input('locale');
            
            if (!$locale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Locale parameter is required'
                ], 400);
            }

            if (!$this->localizationService->isValidLocale($locale)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid locale format'
                ], 400);
            }

            if ($this->localizationService->setLocale($locale)) {
                // Store in session
                session(['locale' => $locale]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Locale changed successfully',
                    'locale' => $locale,
                    'display_name' => $this->localizationService->getLocaleDisplayName($locale)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to change locale'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to set locale", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to set locale'
            ], 500);
        }
    }

    /**
     * Get current locale information
     */
    public function getCurrentLocale()
    {
        try {
            return response()->json([
                'success' => true,
                'current_locale' => $this->localizationService->getCurrentLocale(),
                'supported_locales' => $this->localizationService->getSupportedLocales(),
                'locale_display_names' => array_map(
                    fn($locale) => $this->localizationService->getLocaleDisplayName($locale),
                    $this->localizationService->getSupportedLocales()
                )
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get current locale", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get locale information'
            ], 500);
        }
    }

    /**
     * Translate a key
     */
    public function translate()
    {
        try {
            $key = request()->input('key');
            $params = request()->input('params', []);
            $locale = request()->input('locale');

            if (!$key) {
                return response()->json([
                    'success' => false,
                    'message' => 'Key parameter is required'
                ], 400);
            }

            $translation = $this->localizationService->translate($key, $params, $locale);

            return response()->json([
                'success' => true,
                'translation' => $translation,
                'key' => $key,
                'locale' => $locale ?: $this->localizationService->getCurrentLocale()
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to translate", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to translate'
            ], 500);
        }
    }

    /**
     * Add or update translation
     */
    public function addTranslation()
    {
        try {
            $key = request()->input('key');
            $translation = request()->input('translation');
            $locale = request()->input('locale');
            $context = request()->input('context');

            if (!$key || !$translation || !$locale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Key, translation, and locale are required'
                ], 400);
            }

            if (!$this->localizationService->isValidLocale($locale)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid locale format'
                ], 400);
            }

            if ($this->localizationService->addTranslation($key, $translation, $locale, $context)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Translation added successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add translation'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to add translation'
            ], 500);
        }
    }

    /**
     * Get all translations for a locale
     */
    public function getTranslations()
    {
        try {
            $locale = request()->input('locale', $this->localizationService->getCurrentLocale());

            if (!$this->localizationService->isValidLocale($locale)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid locale format'
                ], 400);
            }

            $translations = $this->localizationService->getAllTranslations($locale);

            return response()->json([
                'success' => true,
                'translations' => $translations,
                'locale' => $locale,
                'total' => count($translations)
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get translations", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get translations'
            ], 500);
        }
    }

    /**
     * Delete translation
     */
    public function deleteTranslation()
    {
        try {
            $key = request()->input('key');
            $locale = request()->input('locale');

            if (!$key || !$locale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Key and locale are required'
                ], 400);
            }

            if (!$this->localizationService->isValidLocale($locale)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid locale format'
                ], 400);
            }

            if ($this->localizationService->deleteTranslation($key, $locale)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Translation deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete translation'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete translation'
            ], 500);
        }
    }

    /**
     * Get localization statistics
     */
    public function getStatistics()
    {
        try {
            $stats = $this->localizationService->getStatistics();

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get localization statistics", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics'
            ], 500);
        }
    }

    /**
     * Import translations
     */
    public function importTranslations()
    {
        try {
            $locale = request()->input('locale');
            $translations = request()->input('translations', []);

            if (!$locale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Locale parameter is required'
                ], 400);
            }

            if (!$this->localizationService->isValidLocale($locale)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid locale format'
                ], 400);
            }

            if (!is_array($translations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Translations must be an array'
                ], 400);
            }

            if ($this->localizationService->importTranslations($translations, $locale)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Translations imported successfully',
                    'count' => count($translations)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Some translations failed to import'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to import translations", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to import translations'
            ], 500);
        }
    }

    /**
     * Export translations
     */
    public function exportTranslations()
    {
        try {
            $locale = request()->input('locale', $this->localizationService->getCurrentLocale());

            if (!$this->localizationService->isValidLocale($locale)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid locale format'
                ], 400);
            }

            $translations = $this->localizationService->exportTranslations($locale);

            // Set headers for file download
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="translations_' . $locale . '.json"');

            echo json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        } catch (\Exception $e) {
            $this->logger->error("Failed to export translations", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to export translations'
            ], 500);
        }
    }

    /**
     * Add supported locale
     */
    public function addLocale()
    {
        try {
            $locale = request()->input('locale');

            if (!$locale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Locale parameter is required'
                ], 400);
            }

            if (!$this->localizationService->isValidLocale($locale)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid locale format'
                ], 400);
            }

            if ($this->localizationService->addSupportedLocale($locale)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Locale added successfully',
                    'locale' => $locale,
                    'display_name' => $this->localizationService->getLocaleDisplayName($locale)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add locale'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to add locale", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to add locale'
            ], 500);
        }
    }

    /**
     * Clear translation cache
     */
    public function clearCache()
    {
        try {
            $this->localizationService->clearAllCache();

            return response()->json([
                'success' => true,
                'message' => 'Translation cache cleared successfully'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to clear cache", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache'
            ], 500);
        }
    }

    /**
     * Localization management page
     */
    public function management()
    {
        try {
            $stats = $this->localizationService->getStatistics();
            
            return view('localization.management', [
                'stats' => $stats,
                'current_locale' => $this->localizationService->getCurrentLocale(),
                'supported_locales' => $this->localizationService->getSupportedLocales(),
                'page_title' => 'Localization Management - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load localization management", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }

    /**
     * Translation editor page
     */
    public function editor()
    {
        try {
            $locale = request()->input('locale', $this->localizationService->getCurrentLocale());
            $translations = $this->localizationService->getAllTranslations($locale);
            
            return view('localization.editor', [
                'translations' => $translations,
                'locale' => $locale,
                'supported_locales' => $this->localizationService->getSupportedLocales(),
                'page_title' => 'Translation Editor - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load translation editor", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }
}
