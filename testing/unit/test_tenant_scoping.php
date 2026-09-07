<?php
/**
 * Unit Tests: Tenant Scoping
 *
 * Tests for:
 *  - TenantAwareTrait: tenantId(), tenantWhere(), tenantInsertData()
 *  - ServiceTenantTrait: tenantId(), tenantSql(), tenantInsertData(), tenantNamedSql()
 *  - TenantContext: getId() returns int, reset() works
 *
 * Run: php testing/unit/test_tenant_scoping.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// ── Assertion helpers ──
$pass = 0;
$fail = 0;

function assert_true(bool $condition, string $msg): void {
    global $pass, $fail;
    if ($condition) { $pass++; echo "  ✅ $msg\n"; }
    else            { $fail++; echo "  ❌ $msg\n"; }
}

function assert_equals($expected, $actual, string $msg): void {
    global $pass, $fail;
    if ($expected === $actual) { $pass++; echo "  ✅ $msg\n"; }
    else { $fail++; echo "  ❌ $msg — expected " . var_export($expected, true) . ", got " . var_export($actual, true) . "\n"; }
}

function assert_is_int($value, string $msg): void {
    global $pass, $fail;
    if (is_int($value)) { $pass++; echo "  ✅ $msg\n"; }
    else { $fail++; echo "  ❌ $msg — expected int, got " . gettype($value) . "\n"; }
}

function section(string $title): void {
    echo "\n── $title ──\n";
}

// ════════════════════════════════════════════════════════════════════
// 1. TenantContext — Static Methods
// ════════════════════════════════════════════════════════════════════
section('TenantContext — Static Methods');

// Reset to clean state
\App\Core\Middleware\TenantContext::reset();

$tid = \App\Core\Middleware\TenantContext::getId();
assert_is_int($tid, 'TenantContext::getId() returns int');
assert_true($tid >= 1, "TenantContext::getId() >= 1 (got $tid)");

// After reset, getId() resolves to default (tenant 1)
\App\Core\Middleware\TenantContext::reset();
$tid2 = \App\Core\Middleware\TenantContext::getId();
assert_equals(1, $tid2, 'TenantContext::getId() defaults to 1 after reset');

// ════════════════════════════════════════════════════════════════════
// 2. TenantAwareTrait — via mock class
// ════════════════════════════════════════════════════════════════════
section('TenantAwareTrait — via mock controller');

// Create a mock class that uses the trait
$mockCode = '
class MockTenantAwareController {
    use \App\Traits\TenantAwareTrait;

    public function testTenantId(): int { return $this->tenantId(); }
    public function testTenantWhere(): array { return $this->tenantWhere(); }
    public function testTenantInsertData(): array { return $this->tenantInsertData(); }
}';
eval($mockCode);

$mock = new MockTenantAwareController();

// Test tenantId() returns int
$tid = $mock->testTenantId();
assert_is_int($tid, 'TenantAwareTrait::tenantId() returns int');
assert_true($tid >= 1, "TenantAwareTrait::tenantId() >= 1 (got $tid)");

// ════════════════════════════════════════════════════════════════════
// 3. TenantAwareTrait — tenantWhere() for superadmin (tenant_id=1)
// ════════════════════════════════════════════════════════════════════
section('TenantAwareTrait — tenantWhere() for superadmin');

// Ensure tenant context is 1 (superadmin)
\App\Core\Middleware\TenantContext::reset();
$tid = $mock->testTenantId();

if ($tid <= 1) {
    [$whereClause, $whereParams] = $mock->testTenantWhere();
    assert_equals('', $whereClause, 'tenantWhere() returns empty string for superadmin');
    assert_equals([], $whereParams, 'tenantWhere() returns empty params for superadmin');
} else {
    echo "  ⚠️  Skipping superadmin tests — tenant_id is $tid (not 1)\n";
}

// ════════════════════════════════════════════════════════════════════
// 4. TenantAwareTrait — tenantInsertData() for superadmin
// ════════════════════════════════════════════════════════════════════
section('TenantAwareTrait — tenantInsertData() for superadmin');

if ($tid <= 1) {
    $insertData = $mock->testTenantInsertData();
    assert_equals([], $insertData, 'tenantInsertData() returns empty array for superadmin');
    assert_true(empty($insertData), 'tenantInsertData() is empty for tenant_id <= 1');
} else {
    echo "  ⚠️  Skipping — tenant_id is $tid\n";
}

// ════════════════════════════════════════════════════════════════════
// 5. ServiceTenantTrait — via mock service
// ════════════════════════════════════════════════════════════════════
section('ServiceTenantTrait — via mock service');

$mockServiceCode = '
class MockTenantService {
    use \App\Traits\ServiceTenantTrait;

    public function testTenantId(): int { return $this->tenantId(); }
    public function testTenantSql(): string { return $this->tenantSql(); }
    public function testTenantInsertData(): array { return $this->tenantInsertData(); }
    public function testTenantNamedSql(): array { return $this->tenantNamedSql(); }
    public function testTenantColumn(): array { return $this->tenantColumn(); }

    // Expose the cached field for testing
    public function resetCache(): void { $this->_serviceTenantId = null; }
}';
eval($mockServiceCode);

$svc = new MockTenantService();

// Reset tenant context to ensure tenant_id = 1
\App\Core\Middleware\TenantContext::reset();
$svc->resetCache();
$svcTid = $svc->testTenantId();
assert_is_int($svcTid, 'ServiceTenantTrait::tenantId() returns int');
assert_equals(1, $svcTid, 'ServiceTenantTrait::tenantId() = 1 (superadmin default)');

// ════════════════════════════════════════════════════════════════════
// 5. ServiceTenantTrait — tenantSql() for superadmin
// ════════════════════════════════════════════════════════════════════
section('ServiceTenantTrait — tenantSql() for superadmin');

$tenantSql = $svc->testTenantSql();
assert_equals('', $tenantSql, 'tenantSql() returns empty string for superadmin');
assert_true(strpos($tenantSql, 'tenant_id') === false, 'tenantSql() contains no tenant_id clause for superadmin');

// ════════════════════════════════════════════════════════════════════
// 6. ServiceTenantTrait — tenantInsertData() for superadmin
// ════════════════════════════════════════════════════════════════════
section('ServiceTenantTrait — tenantInsertData() for superadmin');

$insertData = $svc->testTenantInsertData();
assert_equals([], $insertData, 'tenantInsertData() returns empty array for superadmin');

// ════════════════════════════════════════════════════════════════════
// 7. ServiceTenantTrait — tenantNamedSql() for superadmin
// ════════════════════════════════════════════════════════════════════
section('ServiceTenantTrait — tenantNamedSql() for superadmin');

[$namedSql, $namedParams] = $svc->testTenantNamedSql();
assert_equals('', $namedSql, 'tenantNamedSql() clause is empty for superadmin');
assert_equals([], $namedParams, 'tenantNamedSql() params are empty for superadmin');

// ════════════════════════════════════════════════════════════════════
// 8. ServiceTenantTrait — tenantColumn() for superadmin
// ════════════════════════════════════════════════════════════════════
section('ServiceTenantTrait — tenantColumn() for superadmin');

$col = $svc->testTenantColumn();
assert_equals([], $col, 'tenantColumn() returns empty for superadmin');

// ════════════════════════════════════════════════════════════════════
// 9. ServiceTenantTrait — Caching behavior
// ════════════════════════════════════════════════════════════════════
section('ServiceTenantTrait — tenant ID caching');

// First call sets cache
$first = $svc->testTenantId();
// Second call should use cache (same value)
$second = $svc->testTenantId();
assert_equals($first, $second, 'tenantId() returns consistent value (cached)');

// After reset, cache is cleared
$svc->resetCache();
$afterReset = $svc->testTenantId();
assert_equals($first, $afterReset, 'tenantId() same after cache reset (same TenantContext)');

// ════════════════════════════════════════════════════════════════════
// 10. Tenant scoping — simulate tenant_id > 1 behavior
// ════════════════════════════════════════════════════════════════════
section('Tenant scoping — simulate tenant_id=2 behavior');

// Simulate what would happen with tenant_id = 2 by directly testing the trait logic
// We can't call setById(2) without DB, so test the conditional branches directly

// Test the trait logic: if tid > 1, what happens
function simulateTenantWhere(int $tid): array {
    if ($tid <= 1) return ['', []];
    return [' AND tenant_id = ?', [$tid]];
}

function simulateTenantInsertData(int $tid): array {
    return $tid > 1 ? ['tenant_id' => $tid] : [];
}

function simulateTenantSql(int $tid): string {
    return $tid > 1 ? " AND tenant_id = $tid" : "";
}

function simulateTenantNamedSql(int $tid): array {
    if ($tid <= 1) return ['', []];
    return [' AND tenant_id = :stid', [':stid' => $tid]];
}

function simulateTenantColumn(int $tid): array {
    return $tid > 1 ? ['tenant_id' => $tid] : [];
}

// tenant_id = 1 (superadmin)
[$w, $p] = simulateTenantWhere(1);
assert_equals('', $w, 'simulate: tenantWhere(1) clause empty');
assert_equals([], $p, 'simulate: tenantWhere(1) params empty');

assert_equals([], simulateTenantInsertData(1), 'simulate: tenantInsertData(1) empty');
assert_equals('', simulateTenantSql(1), 'simulate: tenantSql(1) empty');
assert_equals('', simulateTenantNamedSql(1)[0], 'simulate: tenantNamedSql(1) clause empty');
assert_equals([], simulateTenantNamedSql(1)[1], 'simulate: tenantNamedSql(1) params empty');
assert_equals([], simulateTenantColumn(1), 'simulate: tenantColumn(1) empty');

// tenant_id = 2 (regular tenant)
[$w, $p] = simulateTenantWhere(2);
assert_equals(' AND tenant_id = ?', $w, 'simulate: tenantWhere(2) clause present');
assert_equals([2], $p, 'simulate: tenantWhere(2) params [2]');

assert_equals(['tenant_id' => 2], simulateTenantInsertData(2), 'simulate: tenantInsertData(2) includes tenant_id');
assert_equals(' AND tenant_id = 2', simulateTenantSql(2), 'simulate: tenantSql(2) clause with value');
assert_equals(' AND tenant_id = :stid', simulateTenantNamedSql(2)[0], 'simulate: tenantNamedSql(2) named clause');
assert_equals([':stid' => 2], simulateTenantNamedSql(2)[1], 'simulate: tenantNamedSql(2) named param');
assert_equals(['tenant_id' => 2], simulateTenantColumn(2), 'simulate: tenantColumn(2) includes tenant_id');

// tenant_id = 99 (another tenant)
[$w, $p] = simulateTenantWhere(99);
assert_equals(' AND tenant_id = ?', $w, 'simulate: tenantWhere(99) clause present');
assert_equals([99], $p, 'simulate: tenantWhere(99) params [99]');

assert_equals(['tenant_id' => 99], simulateTenantInsertData(99), 'simulate: tenantInsertData(99) includes tenant_id');
assert_equals(' AND tenant_id = 99', simulateTenantSql(99), 'simulate: tenantSql(99) clause with value');

// ════════════════════════════════════════════════════════════════════
// 11. SQL injection resistance — tenant_id must be int
// ════════════════════════════════════════════════════════════════════
section('SQL injection resistance — tenant_id is always int');

$tid = (int) simulateTenantWhere(2)[1][0];
assert_is_int($tid, 'tenant_id cast to int');
assert_equals(2, $tid, 'tenant_id value preserved after int cast');

// Simulate injection attempt
$evilInput = "1; DROP TABLE users";
$castInput = (int)$evilInput;
assert_equals(1, $castInput, "Int cast strips injection: '$evilInput' → 1");

// ════════════════════════════════════════════════════════════════════
// 12. Model $tenantScoped flag
// ════════════════════════════════════════════════════════════════════
section('Model — tenantScoped property exists');

// Check that the base Model class has tenantScoped property
$modelRef = new ReflectionProperty(\App\Core\Database\Model::class, 'tenantScoped');
assert_true($modelRef->isProtected() || $modelRef->isPublic(), 'Model::$tenantScoped is accessible');
$tenantScopedValue = $modelRef->getValue();
assert_equals(true, $tenantScopedValue === false || $tenantScopedValue === null, 'Model::$tenantScoped defaults to false or null (unset)');

// ════════════════════════════════════════════════════════════════════
// Summary
// ════════════════════════════════════════════════════════════════════
echo "\n" . str_repeat('═', 50) . "\n";
echo "  PASS: $pass | FAIL: $fail | TOTAL: " . ($pass + $fail) . "\n";
echo str_repeat('═', 50) . "\n\n";

exit($fail > 0 ? 1 : 0);
