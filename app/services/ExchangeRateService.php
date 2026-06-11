<?php

namespace App\Services;

use App\Core\Cache;
use Exception;

/**
 * Exchange Rate Service
 *
 * Fetches live currency exchange rates from free public APIs.
 * Primary: exchangerate-api.com (no key needed)
 * Fallback: open.er-api.com (no key needed)
 * Cache: Redis/file via Cache, 1-hour TTL
 */
class ExchangeRateService
{
    private const CACHE_TTL = 3600;
    private const CACHE_PREFIX = 'fx_rate_';
    private const TIMEOUT = 5;
    private const MAX_RETRIES = 1;

    private const SUPPORTED = ['INR', 'USD', 'EUR', 'GBP', 'AED', 'SGD', 'JPY', 'CAD', 'AUD'];

    private const TEST_RATES = [
        'USD' => 83.50,
        'EUR' => 90.20,
        'GBP' => 98.70,
        'AED' => 22.70,
        'SGD' => 62.10,
        'JPY' => 0.56,
        'CAD' => 61.80,
        'AUD' => 54.30,
    ];

    private bool $testMode;

    public function __construct()
    {
        $this->testMode = strtoupper(trim($_ENV['EXCHANGE_RATE_TEST_MODE'] ?? '')) === 'TRUE';
    }

    /**
     * Get exchange rate from one currency to another.
     */
    public function getRate(string $fromCurrency, string $toCurrency = 'INR'): array
    {
        try {
            $from = strtoupper(trim($fromCurrency));
            $to   = strtoupper(trim($toCurrency));

            if ($from === $to) {
                return ['success' => true, 'rate' => 1.0, 'from' => $from, 'to' => $to, 'cached' => false, 'fetched_at' => date('Y-m-d H:i:s')];
            }

            if ($this->testMode) {
                $rate = self::TEST_RATES[$from] ?? null;
                if ($rate === null) {
                    return ['success' => false, 'error' => "No test rate for {$from}", 'from' => $from, 'to' => $to];
                }
                return ['success' => true, 'rate' => $rate, 'from' => $from, 'to' => $to, 'cached' => false, 'fetched_at' => date('Y-m-d H:i:s')];
            }

            // Check cache
            $cacheKey = self::CACHE_PREFIX . $from . '_' . $to;
            $cached = $this->getCachedRate($cacheKey);
            if ($cached !== null) {
                return ['success' => true, 'rate' => $cached['rate'], 'from' => $from, 'to' => $to, 'cached' => true, 'fetched_at' => $cached['fetched_at']];
            }

            // Fetch all rates for $from, then pick $to
            $allRates = $this->fetchRatesFromApi($from);
            if ($allRates === null) {
                return ['success' => false, 'error' => 'Exchange rate API unavailable', 'from' => $from, 'to' => $to];
            }

            if (!isset($allRates[$to])) {
                return ['success' => false, 'error' => "Currency {$to} not found in API response", 'from' => $from, 'to' => $to];
            }

            $rate = (float)$allRates[$to];
            $fetchedAt = date('Y-m-d H:i:s');
            $this->cacheRate($cacheKey, ['rate' => $rate, 'fetched_at' => $fetchedAt], self::CACHE_TTL);

            return ['success' => true, 'rate' => $rate, 'from' => $from, 'to' => $to, 'cached' => false, 'fetched_at' => $fetchedAt];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'from' => $fromCurrency ?? $from ?? '', 'to' => $toCurrency ?? $to ?? ''];
        }
    }

    /**
     * Get all rates for a base currency.
     */
    public function getAllRates(string $baseCurrency = 'INR'): array
    {
        try {
            $base = strtoupper(trim($baseCurrency));

            if ($this->testMode) {
                $rates = self::TEST_RATES;
                if ($base !== 'INR') {
                    $inrToBase = isset(self::TEST_RATES[$base]) ? 1.0 / self::TEST_RATES[$base] : null;
                    if ($inrToBase === null) {
                        return ['success' => false, 'error' => "No test rate for {$base}"];
                    }
                    $rates = [];
                    foreach (self::TEST_RATES as $code => $inrRate) {
                        if ($code !== $base) {
                            $rates[$code] = round($inrRate * $inrToBase, 4);
                        }
                    }
                    $rates['INR'] = round($inrToBase, 4);
                }
                return ['success' => true, 'rates' => $rates, 'base' => $base, 'fetched_at' => date('Y-m-d H:i:s')];
            }

            $cacheKey = self::CACHE_PREFIX . $base . '_all';
            $cached = $this->getCachedRate($cacheKey);
            if ($cached !== null) {
                return ['success' => true, 'rates' => $cached['rates'], 'base' => $base, 'fetched_at' => $cached['fetched_at']];
            }

            $rates = $this->fetchRatesFromApi($base);
            if ($rates === null) {
                return ['success' => false, 'error' => 'Exchange rate API unavailable', 'base' => $base];
            }

            $fetchedAt = date('Y-m-d H:i:s');
            $this->cacheRate($cacheKey, ['rates' => $rates, 'fetched_at' => $fetchedAt], self::CACHE_TTL);

            return ['success' => true, 'rates' => $rates, 'base' => $base, 'fetched_at' => $fetchedAt];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'base' => $baseCurrency ?? $base ?? 'INR'];
        }
    }

    /**
     * Convert an amount from one currency to another.
     */
    public function convertAmount(float $amount, string $fromCurrency, string $toCurrency = 'INR'): array
    {
        try {
            if ($amount <= 0) {
                return ['success' => false, 'error' => 'Amount must be > 0'];
            }

            $rateResult = $this->getRate($fromCurrency, $toCurrency);
            if (!$rateResult['success']) {
                return ['success' => false, 'error' => $rateResult['error'] ?? 'Rate lookup failed'];
            }

            $rate = (float)$rateResult['rate'];
            $converted = round($amount * $rate, 2);

            return [
                'success'          => true,
                'original_amount'  => $amount,
                'converted_amount' => $converted,
                'rate'             => $rate,
                'from'             => strtoupper($fromCurrency),
                'to'               => strtoupper($toCurrency),
                'cached'           => $rateResult['cached'] ?? false,
                'fetched_at'       => $rateResult['fetched_at'] ?? date('Y-m-d H:i:s'),
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * List supported currencies.
     */
    public function getSupportedCurrencies(): array
    {
        return self::SUPPORTED;
    }

    // ============================================================
    //  PRIVATE HELPERS
    // ============================================================

    private function fetchRatesFromApi(string $baseCurrency): ?array
    {
        $urls = [
            "https://api.exchangerate-api.com/v4/latest/{$baseCurrency}",
            "https://open.er-api.com/v6/latest/{$baseCurrency}",
        ];

        foreach ($urls as $url) {
            $rates = $this->fetchFromApi($url);
            if ($rates !== null) {
                return $rates;
            }
        }
        return null;
    }

    private function fetchFromApi(string $url): ?array
    {
        $retries = 0;
        while ($retries <= self::MAX_RETRIES) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => self::TIMEOUT,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER     => ['Accept: application/json'],
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $body = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error === '' && $httpCode === 200 && $body !== false) {
                $data = json_decode($body, true);
                if (is_array($data) && isset($data['rates']) && is_array($data['rates'])) {
                    return $data['rates'];
                }
            }

            $retries++;
            if ($retries <= self::MAX_RETRIES) {
                usleep(200000);
            }
        }
        return null;
    }

    private function getCachedRate(string $key): ?array
    {
        try {
            $cached = Cache::get($key);
            if (is_array($cached) && isset($cached['rate'])) {
                return $cached;
            }
            if (is_array($cached) && isset($cached['rates'])) {
                return $cached;
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function cacheRate(string $key, array $data, int $ttl): void
    {
        try {
            Cache::set($key, $data, $ttl);
        } catch (Exception $e) {
            // Silently fail — next request will re-fetch
        }
    }
}
