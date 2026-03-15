<?php

namespace App\Http\Controllers;

use App\Services\Localization\LocalizationService;
use App\Services\SystemLogger as Logger;

/**
 * Controller for Localization operations
 */
class LocalizationController extends BaseController
{
    private LocalizationService $localizationService;
    private $logger;

    public function __construct(LocalizationService $localizationService, Logger $logger)
    {
        parent::__construct();
        $this->localizationService = $localizationService;
        $this->logger = $logger;
    }

    /**
     * Set locale
     */
    public function setLocale()
    {
        try {
            $locale = isset($_REQUEST['locale']) ? $_REQUEST['locale'] : null;

            if (!$locale) {
                return $this->response([
                    'success' => false,
                    'message' => 'Locale is required'
                ], 400);
            }

            if (!$this->localizationService->isValidLocale($locale)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid locale'
                ], 400);
            }

            if ($this->localizationService->setLocale($locale)) {
                return $this->response([
                    'success' => true,
                    'message' => 'Locale set successfully'
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => 'Failed to set locale'
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to set locale", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to set locale'
            ], 500);
        }
    }

    /**
     * Get current locale
     */
    public function getCurrentLocale()
    {
        try {
            $locale = $this->localizationService->getCurrentLocale();

            return $this->response([
                'success' => true,
                'locale' => $locale
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get current locale", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to get current locale'
            ], 500);
        }
    }

    /**
     * Get supported locales
     */
    public function getSupportedLocales()
    {
        try {
            $locales = $this->localizationService->getSupportedLocales();

            return $this->response([
                'success' => true,
                'locales' => $locales
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get supported locales", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to get supported locales'
            ], 500);
        }
    }

    /**
     * Get translations
     */
    public function getTranslations()
    {
        try {
            $locale = isset($_REQUEST['locale']) ? $_REQUEST['locale'] : $this->localizationService->getCurrentLocale();
            $translations = $this->localizationService->getAllTranslations($locale);

            return $this->response([
                'success' => true,
                'translations' => $translations,
                'locale' => $locale
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get translations", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to get translations'
            ], 500);
        }
    }

    /**
     * Add translation
     */
    public function addTranslation()
    {
        try {
            $locale = isset($_REQUEST['locale']) ? $_REQUEST['locale'] : null;
            $key = isset($_REQUEST['key']) ? $_REQUEST['key'] : null;
            $value = isset($_REQUEST['value']) ? $_REQUEST['value'] : null;

            if (!$locale || !$key || !$value) {
                return $this->response([
                    'success' => false,
                    'message' => 'Locale, key, and value are required'
                ], 400);
            }

            if (!$this->localizationService->isValidLocale($locale)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid locale'
                ], 400);
            }

            if ($this->localizationService->addTranslation($locale, $key, $value)) {
                return $this->response([
                    'success' => true,
                    'message' => 'Translation added successfully'
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => 'Failed to add translation'
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to add translation'
            ], 500);
        }
    }

    /**
     * Update translation
     */
    public function updateTranslation()
    {
        try {
            $locale = isset($_REQUEST['locale']) ? $_REQUEST['locale'] : null;
            $key = isset($_REQUEST['key']) ? $_REQUEST['key'] : null;
            $value = isset($_REQUEST['value']) ? $_REQUEST['value'] : null;

            if (!$locale || !$key || !$value) {
                return $this->response([
                    'success' => false,
                    'message' => 'Locale, key, and value are required'
                ], 400);
            }

            if (!$this->localizationService->isValidLocale($locale)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid locale'
                ], 400);
            }

            if ($result = $this->localizationService->addTranslation($key, $value, $locale)) {
                return $this->response([
                    'success' => true,
                    'message' => 'Translation updated successfully'
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => 'Failed to update translation'
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to update translation", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to update translation'
            ], 500);
        }
    }

    /**
     * Delete translation
     */
    public function deleteTranslation()
    {
        try {
            $locale = isset($_REQUEST['locale']) ? $_REQUEST['locale'] : null;
            $key = isset($_REQUEST['key']) ? $_REQUEST['key'] : null;

            if (!$locale || !$key) {
                return $this->response([
                    'success' => false,
                    'message' => 'Locale and key are required'
                ], 400);
            }

            if (!$this->localizationService->isValidLocale($locale)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid locale'
                ], 400);
            }

            if ($this->localizationService->deleteTranslation($locale, $key)) {
                return $this->response([
                    'success' => true,
                    'message' => 'Translation deleted successfully'
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => 'Failed to delete translation'
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to delete translation'
            ], 500);
        }
    }

    /**
     * Export translations
     */
    public function exportTranslations()
    {
        try {
            $locale = isset($_REQUEST['locale']) ? $_REQUEST['locale'] : $this->localizationService->getCurrentLocale();

            if (!$this->localizationService->isValidLocale($locale)) {
                return $this->response([
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
            return $this->response([
                'success' => false,
                'message' => 'Failed to export translations'
            ], 500);
        }
    }

    /**
     * Import translations
     */
    public function importTranslations()
    {
        try {
            $locale = isset($_REQUEST['locale']) ? $_REQUEST['locale'] : null;
            $translations = isset($_REQUEST['translations']) ? $_REQUEST['translations'] : null;

            if (!$locale) {
                return $this->response([
                    'success' => false,
                    'message' => 'Locale parameter is required'
                ], 400);
            }

            if (!$this->localizationService->isValidLocale($locale)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid locale format'
                ], 400);
            }

            if (!is_array($translations)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Translations must be an array'
                ], 400);
            }

            if ($this->localizationService->importTranslations($translations, $locale)) {
                return $this->response([
                    'success' => true,
                    'message' => 'Translations imported successfully',
                    'count' => count($translations)
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => 'Some translations failed to import'
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to import translations", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to import translations'
            ], 500);
        }
    }

    /**
     * Add supported locale
     */
    public function addLocale()
    {
        try {
            $locale = isset($_REQUEST['locale']) ? $_REQUEST['locale'] : null;

            if (!$locale) {
                return $this->response([
                    'success' => false,
                    'message' => 'Locale parameter is required'
                ], 400);
            }

            if (!$this->localizationService->isValidLocale($locale)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid locale format'
                ], 400);
            }

            if ($this->localizationService->addSupportedLocale($locale)) {
                return $this->response([
                    'success' => true,
                    'message' => 'Locale added successfully'
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => 'Failed to add locale'
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to add locale", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to add locale'
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

            return $this->view('localization.management', [
                'stats' => $stats,
                'current_locale' => $this->localizationService->getCurrentLocale(),
                'supported_locales' => $this->localizationService->getSupportedLocales(),
                'page_title' => 'Localization Management - APS Dream Home'
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to load localization management", ['error' => $e->getMessage()]);
            return $this->view('errors.500');
        }
    }

    /**
     * Translation editor page
     */
    public function editor()
    {
        try {
            $locale = isset($_REQUEST['locale']) ? $_REQUEST['locale'] : $this->localizationService->getCurrentLocale();
            $translations = $this->localizationService->getAllTranslations($locale);

            return $this->view('localization.editor', [
                'translations' => $translations,
                'locale' => $locale,
                'supported_locales' => $this->localizationService->getSupportedLocales(),
                'page_title' => 'Translation Editor - APS Dream Home'
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to load translation editor", ['error' => $e->getMessage()]);
            return $this->view('errors.500');
        }
    }
}
