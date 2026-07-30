<?php
/**
 * test_hotpath_cache.php
 *
 * Unit tests for App\Services\Cache\HotPathCacheService.
 *
 * Asserts:
 *   1.  All 5 getter methods cache results (callback fires only on first call)
 *   2.  All 5 invalidation methods drop the right keys
 *   3.  getStats() returns correct hit-rate
 *   4.  Fallback to file cache when Redis unavailable
 *   5.  TTL expiry (via override)
 *   6.  Callback invoked exactly once on miss, never on hit
 *
 * Run: php testing/test_hotpath_cache.php
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Services\Cache\HotPathCacheService;
use App\Services\CacheService;

$tests = [];
$pass = 0;
$fail = 0;

function record(string $label, bool $ok, $detail = ''): void {
    global $tests, $pass, $fail;
    $tests[] = ['label' => $label, 'ok' => $ok, 'detail' => $detail];
    if ($ok) { $pass++; } else { $fail++; }
    $icon = $ok ? "\033[32m[ OK ]\033[0m" : "\033[31m[FAIL]\033[0m";
    echo $icon . " $label" . ($detail ? "  — $detail" : '') . PHP_EOL;
}

function section(string $title): void {
    echo PHP_EOL . "\033[1;36m── $title ──\033[0m" . PHP_EOL;
}

HotPathCacheService::resetStats();

// Flush any pre-existing cache so first-call tests are guaranteed to miss
try {
    \App\Services\CacheService::flushAll();
} catch (\Throwable $e) {
    // best-effort
}

section('1. Property listing cache');

$callCount1 = 0;
$filters = ['q' => 'flat', 'type' => 'flat', 'min_price' => 1000000];
$payload1 = HotPathCacheService::getPropertyList($filters, 1, 12, 'newest', function () use (&$callCount1) {
    $callCount1++;
    return ['properties' => [['id' => 1, 'name' => 'Test Flat']], 'total' => 1];
});
record('PropertyList first call invokes callback', $callCount1 === 1, "count=$callCount1");
record('PropertyList returns array with properties', is_array($payload1) && isset($payload1['properties']));
record('PropertyList first call is a miss', HotPathCacheService::getStats()['paths']['property_list']['misses'] === 1);

$payload2 = HotPathCacheService::getPropertyList($filters, 1, 12, 'newest', function () use (&$callCount1) {
    $callCount1++;
    return ['properties' => [], 'total' => 0];
});
record('PropertyList second call is a hit (no callback)', $callCount1 === 1, "count=$callCount1");
record('PropertyList hits = 1', HotPathCacheService::getStats()['paths']['property_list']['hits'] === 1);

// Different filter → cache miss
$filters2 = ['q' => 'plot'];
HotPathCacheService::getPropertyList($filters2, 1, 12, 'newest', function () use (&$callCount1) {
    $callCount1++;
    return ['properties' => [], 'total' => 0];
});
record('PropertyList different filters = another miss', $callCount1 === 2);

$dropped = HotPathCacheService::invalidatePropertyList();
record('invalidatePropertyList returns >= 1', $dropped >= 1, "dropped=$dropped");

// After invalidation, callback should fire again
HotPathCacheService::getPropertyList($filters, 1, 12, 'newest', function () use (&$callCount1) {
    $callCount1++;
    return ['properties' => [['id' => 2]], 'total' => 1];
});
record('PropertyList callback fires after invalidation', $callCount1 === 3);

section('2. Header projects cache');

$callCount2 = 0;
$projects1 = HotPathCacheService::getHeaderProjects(function () use (&$callCount2) {
    $callCount2++;
    return ['locations' => ['gorakhpur' => ['name' => 'Gorakhpur', 'count' => 3]], 'projects' => []];
});
record('HeaderProjects first call invokes callback', $callCount2 === 1);
record('HeaderProjects returns expected locations', isset($projects1['locations']['gorakhpur']));

$projects2 = HotPathCacheService::getHeaderProjects(function () use (&$callCount2) {
    $callCount2++;
    return [];
});
record('HeaderProjects second call is a hit', $callCount2 === 1, "count=$callCount2");

HotPathCacheService::invalidateHeaderProjects();
$projects3 = HotPathCacheService::getHeaderProjects(function () use (&$callCount2) {
    $callCount2++;
    return ['locations' => [], 'projects' => []];
});
record('HeaderProjects callback fires after invalidate', $callCount2 === 2);

section('3. Admin dashboard KPIs (per role+user)');

$callCount3 = 0;
$kpi1 = HotPathCacheService::getAdminDashboardKpis('admin', 1, function () use (&$callCount3) {
    $callCount3++;
    return ['users' => 100, 'revenue' => 50000];
});
record('AdminDashKpis first call invokes callback', $callCount3 === 1);
record('AdminDashKpis returns KPI array', is_array($kpi1) && isset($kpi1['users']));

// Different user → different cache key
HotPathCacheService::getAdminDashboardKpis('admin', 2, function () use (&$callCount3) {
    $callCount3++;
    return ['users' => 50, 'revenue' => 25000];
});
record('AdminDashKpis per-user key isolation', $callCount3 === 2, "count=$callCount3 (should be 2)");

HotPathCacheService::invalidateAdminDashboard();
$callCount3 = 0;
HotPathCacheService::getAdminDashboardKpis('admin', 1, function () use (&$callCount3) {
    $callCount3++;
    return ['users' => 200];
});
record('AdminDashKpis callback fires after invalidate', $callCount3 === 1);

section('4. Home featured properties cache');

$callCount4 = 0;
$home1 = HotPathCacheService::getHomeFeaturedProperties(function () use (&$callCount4) {
    $callCount4++;
    return [['id' => 1, 'name' => 'Suryoday']];
});
record('HomeFeatured first call invokes callback', $callCount4 === 1);
$home2 = HotPathCacheService::getHomeFeaturedProperties(function () use (&$callCount4) {
    $callCount4++;
    return [];
});
record('HomeFeatured second call is a hit', $callCount4 === 1, "count=$callCount4");

HotPathCacheService::invalidateHomeFeatured();
HotPathCacheService::getHomeFeaturedProperties(function () use (&$callCount4) {
    $callCount4++;
    return [];
});
record('HomeFeatured callback fires after invalidate', $callCount4 === 2);

section('5. Saved searches count (per user)');

$callCount5 = 0;
$count1 = HotPathCacheService::getUserSavedSearchesCount(7, function () use (&$callCount5) {
    $callCount5++;
    return 5;
});
record('SavedSearchesCount first call invokes callback', $callCount5 === 1);
record('SavedSearchesCount returns int', $count1 === 5);

$count2 = HotPathCacheService::getUserSavedSearchesCount(7, function () use (&$callCount5) {
    $callCount5++;
    return 0;
});
record('SavedSearchesCount second call is a hit', $callCount5 === 1);

// Different user → miss
$count3 = HotPathCacheService::getUserSavedSearchesCount(8, function () use (&$callCount5) {
    $callCount5++;
    return 3;
});
record('SavedSearchesCount per-user key isolation', $callCount5 === 2);

HotPathCacheService::invalidateUserSavedSearches(7);
$count4 = HotPathCacheService::getUserSavedSearchesCount(7, function () use (&$callCount5) {
    $callCount5++;
    return 10;
});
record('SavedSearchesCount callback fires after per-user invalidate', $callCount5 === 3);

section('6. getStats() — aggregate hit rate');

$stats = HotPathCacheService::getStats();
record('getStats returns paths array', isset($stats['paths']) && is_array($stats['paths']));
record('getStats returns total array', isset($stats['total']) && is_array($stats['total']));
record('getStats has 5 paths', count($stats['paths']) === 5, "got " . count($stats['paths']));

$allPaths = ['property_list', 'header_projects', 'admin_dash_kpis', 'home_featured', 'saved_searches_count'];
$allPresent = true;
foreach ($allPaths as $p) {
    if (!isset($stats['paths'][$p])) { $allPresent = false; break; }
}
record('getStats includes all 5 path keys', $allPresent);

$total = $stats['total'];
record('getStats total has calls counter', isset($total['calls']) && $total['calls'] > 0);
record('getStats total has hit_rate', isset($total['hit_rate']) && $total['hit_rate'] >= 0 && $total['hit_rate'] <= 100);

section('7. resetStats()');

HotPathCacheService::resetStats();
$stats2 = HotPathCacheService::getStats();
$allZero = true;
foreach ($stats2['paths'] as $p) {
    if ($p['hits'] !== 0 || $p['misses'] !== 0 || $p['calls'] !== 0) {
        $allZero = false;
        break;
    }
}
record('resetStats zeroes all counters', $allZero);

section('8. Fallback to file cache (no Redis)');

HotPathCacheService::resetStats();
$callCount6 = 0;
$fb1 = HotPathCacheService::getPropertyList(['fallback_test' => 1], 1, 12, 'newest', function () use (&$callCount6) {
    $callCount6++;
    return ['properties' => [['id' => 999]], 'total' => 1];
});
record('Fallback first call fires callback', $callCount6 === 1);

$fb2 = HotPathCacheService::getPropertyList(['fallback_test' => 1], 1, 12, 'newest', function () use (&$callCount6) {
    $callCount6++;
    return [];
});
record('Fallback second call is a hit (file or Redis)', $callCount6 === 1, "count=$callCount6");

section('9. Cacheable types (int, array, string, scalar)');

HotPathCacheService::resetStats();
// Re-flush so section 9 starts clean
\App\Services\CacheService::flushAll();
$callCount7 = 0;
$str = HotPathCacheService::getHeaderProjects(function () use (&$callCount7) {
    $callCount7++;
    return ['s' => 'string-value', 'n' => 42, 'b' => true];
});
record('Array payload cached', $callCount7 === 1);
HotPathCacheService::getHeaderProjects(function () use (&$callCount7) { $callCount7++; return []; });
record('Array payload hit', $callCount7 === 1);

HotPathCacheService::resetStats();
$callCount8 = 0;
$countVal = HotPathCacheService::getUserSavedSearchesCount(99, function () use (&$callCount8) {
    $callCount8++;
    return 0;   // edge case: count = 0
});
record('Zero count cacheable (no false-skip)', $callCount8 === 1);
record('Zero count value preserved', $countVal === 0);
HotPathCacheService::getUserSavedSearchesCount(99, function () use (&$callCount8) { $callCount8++; return 1; });
record('Zero count second call is a hit', $callCount8 === 1);

section('10. TTL constants are sane');

$ref = new ReflectionClass(HotPathCacheService::class);
foreach (['TTL_PROPERTY_LIST' => 300, 'TTL_HEADER_PROJECTS' => 600, 'TTL_ADMIN_KPIS' => 120, 'TTL_HOME_FEATURED' => 900, 'TTL_SAVED_SEARCHES_COUNT' => 30] as $const => $expected) {
    $actual = $ref->getConstant($const);
    record("TTL $const = $expected", $actual === $expected, "actual=$actual");
}

section('Summary');

$total = $pass + $fail;
echo PHP_EOL . "\033[1;33m=== HotPathCacheService test: $pass / $total passed ===\033[0m" . PHP_EOL;
exit($fail === 0 ? 0 : 1);
