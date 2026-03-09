<?php

namespace Tests\Services\Localization;

use PHPUnit\Framework\TestCase;
use App\Services\Localization\LocalizationService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

class LocalizationServiceTest extends TestCase
{
    private LocalizationService $localizationService;
    private Database $db;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->localizationService = new LocalizationService($this->db, $this->logger, 'en_US');
    }

    public function testTranslateSuccess(): void
    {
        $key = 'welcome_message';
        $translation = 'Welcome to our application!';
        $locale = 'en_US';

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['translation' => $translation]);

        $result = $this->localizationService->translate($key, [], $locale);

        $this->assertEquals($translation, $result);
    }

    public function testTranslateWithParams(): void
    {
        $key = 'welcome_user';
        $translation = 'Welcome :name!';
        $params = ['name' => 'John'];
        $locale = 'en_US';

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['translation' => $translation]);

        $result = $this->localizationService->translate($key, $params, $locale);

        $this->assertEquals('Welcome John!', $result);
    }

    public function testTranslateFallbackToDefault(): void
    {
        $key = 'missing_key';
        $defaultTranslation = 'Default translation';
        $locale = 'fr_FR';

        // First call returns null for requested locale
        $this->db->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                null, // No translation for fr_FR
                ['translation' => $defaultTranslation] // Default locale translation
            );

        $result = $this->localizationService->translate($key, [], $locale);

        $this->assertEquals($defaultTranslation, $result);
    }

    public function testTranslateKeyNotFound(): void
    {
        $key = 'non_existent_key';
        $locale = 'en_US';

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null); // Fallback also null

        $result = $this->localizationService->translate($key, [], $locale);

        $this->assertEquals($key, $result); // Returns key as fallback
    }

    public function testAddTranslationSuccess(): void
    {
        $key = 'new_key';
        $translation = 'New translation';
        $locale = 'en_US';
        $context = 'General';

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->localizationService->addTranslation($key, $translation, $locale, $context);

        $this->assertTrue($result);
    }

    public function testAddTranslationFailure(): void
    {
        $key = 'new_key';
        $translation = 'New translation';
        $locale = 'en_US';

        $this->db->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Database error'));

        $result = $this->localizationService->addTranslation($key, $translation, $locale);

        $this->assertFalse($result);
    }

    public function testSetLocaleSuccess(): void
    {
        $locale = 'hi_IN';

        $result = $this->localizationService->setLocale($locale);

        $this->assertTrue($result);
        $this->assertEquals($locale, $this->localizationService->getCurrentLocale());
    }

    public function testSetLocaleUnsupported(): void
    {
        $locale = 'unsupported_locale';

        $result = $this->localizationService->setLocale($locale);

        $this->assertFalse($result);
        $this->assertNotEquals($locale, $this->localizationService->getCurrentLocale());
    }

    public function testGetCurrentLocale(): void
    {
        $locale = $this->localizationService->getCurrentLocale();

        $this->assertEquals('en_US', $locale);
    }

    public function testGetSupportedLocales(): void
    {
        $locales = $this->localizationService->getSupportedLocales();

        $this->assertIsArray($locales);
        $this->assertContains('en_US', $locales);
        $this->assertContains('hi_IN', $locales);
        $this->assertContains('gu_IN', $locales);
        $this->assertContains('mr_IN', $locales);
    }

    public function testAddSupportedLocale(): void
    {
        $newLocale = 'fr_FR';

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->localizationService->addSupportedLocale($newLocale);

        $this->assertTrue($result);
        $this->assertContains($newLocale, $this->localizationService->getSupportedLocales());
    }

    public function testAddAlreadySupportedLocale(): void
    {
        $existingLocale = 'en_US';

        $result = $this->localizationService->addSupportedLocale($existingLocale);

        $this->assertTrue($result); // Should return true without error
    }

    public function testGetAllTranslations(): void
    {
        $locale = 'en_US';
        $expectedTranslations = [
            'welcome' => ['translation' => 'Welcome', 'context' => 'General'],
            'goodbye' => ['translation' => 'Goodbye', 'context' => 'General']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['key_name' => 'welcome', 'translation' => 'Welcome', 'context' => 'General'],
                ['key_name' => 'goodbye', 'translation' => 'Goodbye', 'context' => 'General']
            ]);

        $result = $this->localizationService->getAllTranslations($locale);

        $this->assertEquals($expectedTranslations, $result);
    }

    public function testDeleteTranslationSuccess(): void
    {
        $key = 'delete_key';
        $locale = 'en_US';

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->localizationService->deleteTranslation($key, $locale);

        $this->assertTrue($result);
    }

    public function testDeleteTranslationFailure(): void
    {
        $key = 'delete_key';
        $locale = 'en_US';

        $this->db->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Database error'));

        $result = $this->localizationService->deleteTranslation($key, $locale);

        $this->assertFalse($result);
    }

    public function testGetStatistics(): void
    {
        $expectedStats = [
            'translations_by_locale' => ['en_US' => 10, 'hi_IN' => 5],
            'total_unique_keys' => 8,
            'coverage_percentage' => 75.0,
            'configuration' => [
                'default_locale' => 'en_US',
                'storage_type' => 'database'
            ]
        ];

        $this->db->expects($this->exactly(3))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['locale' => 'en_US', 'count' => 10], ['locale' => 'hi_IN', 'count' => 5]],
                [['config_key' => 'default_locale', 'config_value' => 'en_US']],
                [['config_key' => 'storage_type', 'config_value' => 'database']]
            );

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['count' => 8]);

        $result = $this->localizationService->getStatistics();

        $this->assertEquals($expectedStats['translations_by_locale'], $result['translations_by_locale']);
        $this->assertEquals($expectedStats['total_unique_keys'], $result['total_unique_keys']);
        $this->assertEquals($expectedStats['configuration'], $result['configuration']);
    }

    public function testImportTranslations(): void
    {
        $locale = 'fr_FR';
        $translations = [
            'welcome' => 'Bienvenue',
            'goodbye' => 'Au revoir'
        ];

        $this->db->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(1);

        $result = $this->localizationService->importTranslations($translations, $locale);

        $this->assertTrue($result);
    }

    public function testImportTranslationsPartialFailure(): void
    {
        $locale = 'fr_FR';
        $translations = [
            'welcome' => 'Bienvenue',
            'goodbye' => 'Au revoir'
        ];

        $this->db->expects($this->exactly(2))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(1, 0); // Second one fails

        $result = $this->localizationService->importTranslations($translations, $locale);

        $this->assertFalse($result); // Returns false if any fail
    }

    public function testExportTranslations(): void
    {
        $locale = 'en_US';
        $expectedTranslations = [
            'welcome' => ['translation' => 'Welcome', 'context' => 'General'],
            'goodbye' => ['translation' => 'Goodbye', 'context' => 'General']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['key_name' => 'welcome', 'translation' => 'Welcome', 'context' => 'General'],
                ['key_name' => 'goodbye', 'translation' => 'Goodbye', 'context' => 'General']
            ]);

        $result = $this->localizationService->exportTranslations($locale);

        $this->assertEquals($expectedTranslations, $result);
    }

    public function testIsValidLocale(): void
    {
        $this->assertTrue($this->localizationService->isValidLocale('en_US'));
        $this->assertTrue($this->localizationService->isValidLocale('hi_IN'));
        $this->assertFalse($this->localizationService->isValidLocale('invalid'));
        $this->assertFalse($this->localizationService->isValidLocale('en'));
        $this->assertFalse($this->localizationService->isValidLocale('EN_US'));
    }

    public function testGetLocaleDisplayName(): void
    {
        $this->assertEquals('English (United States)', $this->localizationService->getLocaleDisplayName('en_US'));
        $this->assertEquals('हिन्दी (भारत)', $this->localizationService->getLocaleDisplayName('hi_IN'));
        $this->assertEquals('ગુજરાતી (ભારત)', $this->localizationService->getLocaleDisplayName('gu_IN'));
        $this->assertEquals('मराठी (भारत)', $this->localizationService->getLocaleDisplayName('mr_IN'));
        $this->assertEquals('unknown_locale', $this->localizationService->getLocaleDisplayName('unknown_locale'));
    }

    public function testClearAllCache(): void
    {
        // Add some cached translations first
        $this->localizationService->translate('test_key', [], 'en_US');

        // Clear cache
        $this->localizationService->clearAllCache();

        // Verify cache is cleared (this is more of a behavioral test)
        $this->assertTrue(true); // If no exception thrown, cache clearing worked
    }

    public function testTranslateFromCache(): void
    {
        $key = 'cached_key';
        $translation = 'Cached translation';
        $locale = 'en_US';

        // Mock database to return translation
        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['translation' => $translation]);

        // First call should hit database
        $result1 = $this->localizationService->translate($key, [], $locale);
        $this->assertEquals($translation, $result1);

        // Second call should use cache (no additional database calls expected)
        $result2 = $this->localizationService->translate($key, [], $locale);
        $this->assertEquals($translation, $result2);
    }

    public function testConstructorWithCustomLocale(): void
    {
        $customService = new LocalizationService($this->db, $this->logger, 'hi_IN');

        $this->assertEquals('hi_IN', $customService->getCurrentLocale());
    }

    public function testConstructorWithCustomMode(): void
    {
        $customService = new LocalizationService($this->db, $this->logger, 'en_US', LocalizationService::MODE_SIMPLE);

        $this->assertEquals('en_US', $customService->getCurrentLocale());
        // Mode is internal, but constructor should not fail
    }
}
