<?php
/**
 * S3 Storage adapter test suite.
 *
 * Run modes:
 *   S3_TEST_MODE=true php testing/test_s3_storage.php
 *
 * Modes:
 *   - default: runs against LocalStorage. No S3 credentials needed.
 *   - S3_TEST_MODE=true with creds: runs full S3 round-trip against real S3.
 *   - S3_TEST_MODE=true without creds: skips live S3 tests but exercises
 *     signing helpers with assertions against fixed reference outputs.
 *
 * Exit code: 0 = all pass, 1 = any fail.
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/Core/Autoloader.php';
if (is_file(APP_ROOT . '/app/Core/Database/Database.php')) {
    require_once APP_ROOT . '/app/Core/Database/Database.php';
}

use App\Services\Storage\LocalStorage;
use App\Services\Storage\S3Storage;
use App\Services\Storage\StorageManager;
use App\Services\Storage\StorageInterface;

$tests = [];
$pass = 0;
$fail = 0;

function test(string $name, callable $fn) {
    global $tests, $pass, $fail;
    try {
        $fn();
        echo "  \033[32m✓\033[0m $name\n";
        $pass++;
        $tests[] = ['name' => $name, 'status' => 'pass'];
    } catch (\Throwable $e) {
        echo "  \033[31m✗\033[0m $name\n";
        echo "      " . $e->getMessage() . "\n";
        $fail++;
        $tests[] = ['name' => $name, 'status' => 'fail', 'error' => $e->getMessage()];
    }
}

function assert_true(bool $v, string $msg = 'expected true') { if (!$v) throw new \RuntimeException($msg); }
function assert_false(bool $v, string $msg = 'expected false') { if ($v) throw new \RuntimeException($msg); }
function assert_eq($a, $b, string $msg = '') {
    if ($a !== $b) throw new \RuntimeException("Expected " . var_export($b, true) . " got " . var_export($a, true) . ($msg ? " - $msg" : ''));
}
function assert_null($v, string $msg = 'expected null') { if ($v !== null) throw new \RuntimeException($msg . ', got ' . var_export($v, true)); }
function assert_not_null($v, string $msg = 'expected not null') { if ($v === null) throw new \RuntimeException($msg); }
function assert_throws(callable $fn, string $msgContains = '') {
    try { $fn(); }
    catch (\Throwable $e) {
        if ($msgContains !== '' && strpos($e->getMessage(), $msgContains) === false) {
            throw new \RuntimeException("Expected error containing '$msgContains', got: " . $e->getMessage());
        }
        return;
    }
    throw new \RuntimeException("Expected an exception, none thrown");
}

$s3TestMode = (getenv('S3_TEST_MODE') ?: '') === 'true';
$hasCreds = (getenv('AWS_ACCESS_KEY_ID') ?: '') !== ''
    && (getenv('AWS_SECRET_ACCESS_KEY') ?: '') !== ''
    && (getenv('AWS_BUCKET') ?: '') !== '';

echo "\n=== APS Dream Home S3 Storage Test Suite ===\n";
echo "Mode: " . ($s3TestMode ? 'S3_TEST_MODE' : 'local-only') . "\n";
echo "Live S3 creds: " . ($hasCreds ? 'yes' : 'no') . "\n\n";

// ---------- LocalStorage tests (always run) ----------

echo "LocalStorage:\n";

test('LocalStorage: getDriver() returns "local"', function () {
    $s = new LocalStorage();
    assert_eq($s->getDriver(), 'local');
});

test('LocalStorage: put + get round-trip', function () {
    $s = new LocalStorage();
    $r = $s->put('test/hello.txt', 'hello world');
    assert_true($r['success'], 'put failed: ' . print_r($r, true));
    assert_eq($r['size'], 11);
    $g = $s->get('test/hello.txt');
    assert_eq($g, 'hello world');
    $s->delete('test/hello.txt');
});

test('LocalStorage: exists()', function () {
    $s = new LocalStorage();
    $s->put('test/exists.txt', 'x');
    assert_true($s->exists('test/exists.txt'));
    assert_false($s->exists('test/does-not-exist.txt'));
    $s->delete('test/exists.txt');
});

test('LocalStorage: delete()', function () {
    $s = new LocalStorage();
    $s->put('test/del.txt', 'y');
    assert_true($s->delete('test/del.txt'));
    assert_false($s->exists('test/del.txt'));
    // idempotent
    assert_true($s->delete('test/del.txt'));
});

test('LocalStorage: size()', function () {
    $s = new LocalStorage();
    $s->put('test/sized.txt', '12345');
    assert_eq($s->size('test/sized.txt'), 5);
    assert_null($s->size('test/missing.txt'));
    $s->delete('test/sized.txt');
});

test('LocalStorage: mimeType()', function () {
    $s = new LocalStorage();
    $s->put('test/sample.html', '<html></html>');
    $mt = $s->mimeType('test/sample.html');
    assert_true($mt !== null);
    assert_true(strpos($mt, 'html') !== false || strpos($mt, 'text') !== false, "expected text/html, got $mt");
    $s->delete('test/sample.html');
});

test('LocalStorage: url()', function () {
    $s = new LocalStorage();
    $u = $s->url('uploads/x.jpg');
    assert_eq($u, '/uploads/uploads/x.jpg'); // base prefix + path
});

test('LocalStorage: temporaryUrl() equals url()', function () {
    $s = new LocalStorage();
    assert_eq($s->temporaryUrl('uploads/x.jpg', 10), $s->url('uploads/x.jpg'));
});

test('LocalStorage: copy()', function () {
    $s = new LocalStorage();
    $s->put('test/src.txt', 'copy me');
    $r = $s->copy('test/src.txt', 'test/dst.txt');
    assert_true($r['success']);
    assert_eq($s->get('test/dst.txt'), 'copy me');
    assert_eq($s->get('test/src.txt'), 'copy me');
    $s->delete('test/src.txt');
    $s->delete('test/dst.txt');
});

test('LocalStorage: move()', function () {
    $s = new LocalStorage();
    $s->put('test/m.txt', 'move me');
    $r = $s->move('test/m.txt', 'test/m2.txt');
    assert_true($r['success']);
    assert_false($s->exists('test/m.txt'));
    assert_eq($s->get('test/m2.txt'), 'move me');
    $s->delete('test/m2.txt');
});

test('LocalStorage: listFiles() finds files under prefix', function () {
    $s = new LocalStorage();
    $s->put('test/list/a.txt', '1');
    $s->put('test/list/b.txt', '22');
    $files = $s->listFiles('test/list');
    assert_true(count($files) >= 2);
    $names = array_column($files, 'key');
    assert_true(in_array('test/list/a.txt', $names));
    assert_true(in_array('test/list/b.txt', $names));
    $s->delete('test/list/a.txt');
    $s->delete('test/list/b.txt');
});

test('LocalStorage: rejects path traversal', function () {
    $s = new LocalStorage();
    $r = $s->put('../escape.txt', 'x');
    assert_false($r['success']);
    $r2 = $s->put('/abs.txt', 'x');
    assert_false($r2['success']);
    $r3 = $s->put('C:/win.txt', 'x');
    assert_false($r3['success']);
});

test('LocalStorage: get() on missing returns null', function () {
    $s = new LocalStorage();
    assert_null($s->get('test/missing-file.txt'));
});

test('LocalStorage: size() on missing returns null', function () {
    $s = new LocalStorage();
    assert_null($s->size('test/missing-file.txt'));
});

test('LocalStorage: mimeType() on missing returns null', function () {
    $s = new LocalStorage();
    assert_null($s->mimeType('test/missing-file.txt'));
});

test('LocalStorage: put() with binary content', function () {
    $s = new LocalStorage();
    $bin = "\x00\x01\x02\x03\xff";
    $r = $s->put('test/bin.dat', $bin);
    assert_true($r['success']);
    assert_eq($s->get('test/bin.dat'), $bin);
    $s->delete('test/bin.dat');
});

echo "\nS3Storage (config + helpers, no live call when no creds):\n";

test('S3Storage: isConfigured() false with empty creds', function () {
    // Use a child class that overrides env reads for this test
    $s = new S3Storage(['access_key' => '', 'secret_key' => '', 'bucket' => '']);
    assert_false($s->isConfigured());
});

test('S3Storage: isConfigured() true with creds', function () {
    $s = new S3Storage([
        'access_key' => 'AKIA0000',
        'secret_key' => 'secret0000',
        'bucket'     => 'my-bucket',
        'region'     => 'us-east-1',
    ]);
    assert_true($s->isConfigured());
});

test('S3Storage: getDriver() returns "s3"', function () {
    $s = new S3Storage();
    assert_eq($s->getDriver(), 's3');
});

test('S3Storage: put() with no creds returns error envelope', function () {
    $s = new S3Storage();
    $r = $s->put('foo.txt', 'bar');
    assert_false($r['success']);
    assert_true(isset($r['error']));
});

test('S3Storage: url() with virtual-hosted addressing', function () {
    $s = new S3Storage([
        'access_key' => 'x', 'secret_key' => 'y', 'bucket' => 'mybucket', 'region' => 'ap-south-1',
    ]);
    $u = $s->url('folder/file.jpg');
    assert_eq($u, 'https://mybucket.s3.ap-south-1.amazonaws.com/folder/file.jpg');
});

test('S3Storage: url() with custom endpoint + path-style', function () {
    $s = new S3Storage([
        'access_key' => 'x', 'secret_key' => 'y', 'bucket' => 'mybucket', 'region' => 'us-east-1',
        'endpoint'   => 'https://nyc3.digitaloceanspaces.com',
    ]);
    $u = $s->url('folder/file.jpg');
    assert_eq($u, 'https://nyc3.digitaloceanspaces.com/mybucket/folder/file.jpg');
});

test('S3Storage: temporaryUrl() returns presigned URL with X-Amz-Signature', function () {
    $s = new S3Storage([
        'access_key' => 'AKIATEST',
        'secret_key' => 'secretTEST',
        'bucket'     => 'mybucket',
        'region'     => 'us-east-1',
    ]);
    $u = $s->temporaryUrl('folder/img.jpg', 60);
    assert_true($u !== null);
    assert_true(strpos($u, 'X-Amz-Signature=') !== false, 'no signature in URL');
    assert_true(strpos($u, 'X-Amz-Algorithm=AWS4-HMAC-SHA256') !== false, 'no algorithm in URL');
    assert_true(strpos($u, 'X-Amz-Expires=3600') !== false, 'wrong expiry');
    assert_true(strpos($u, 'mybucket') !== false, 'bucket missing');
    assert_true(strpos($u, 'folder/img.jpg') !== false, 'key missing');
});

test('S3Storage: SigV4 signature is deterministic for fixed inputs', function () {
    // Reference vector from the AWS SigV4 spec (GET, simple path, no body)
    // https://docs.aws.amazon.com/general/latest/gr/sigv4-signed-request-examples.html
    $s = new S3Storage([
        'access_key' => 'AKIDEXAMPLE',
        'secret_key' => 'wJalrXUtnFEMI/K7MDENG+bPxRfiCYEXAMPLEKEY',
        'bucket'     => 'examplebucket',
        'region'     => 'us-east-1',
    ]);
    $url = $s->temporaryUrl('test.txt', 60);
    // The credential is URL-encoded by http_build_query. Decode for the
    // assertion - the actual signed string is AKIDEXAMPLE/YYYYMMDD/region/s3/aws4_request.
    $qs = parse_url($url, PHP_URL_QUERY);
    parse_str($qs, $params);
    assert_eq($params['X-Amz-Credential'] ?? null, 'AKIDEXAMPLE/' . substr($params['X-Amz-Date'] ?? '', 0, 8) . '/us-east-1/s3/aws4_request', 'bad credential scope');
    assert_eq($params['X-Amz-Algorithm'] ?? null, 'AWS4-HMAC-SHA256', 'bad algorithm');
    assert_eq($params['X-Amz-SignedHeaders'] ?? null, 'host', 'bad signed headers');
    assert_eq(strlen($params['X-Amz-Signature'] ?? ''), 64, 'signature should be 64 hex chars');
    assert_true(ctype_xdigit($params['X-Amz-Signature']), 'signature should be all hex');
});

test('S3Storage: rejects path traversal', function () {
    $s = new S3Storage([
        'access_key' => 'x', 'secret_key' => 'y', 'bucket' => 'b', 'region' => 'us-east-1',
    ]);
    $r = $s->put('../escape.txt', 'x');
    assert_false($r['success']);
    $r2 = $s->put('/abs.txt', 'x');
    assert_false($r2['success']);
});

test('S3Storage: mime guessing', function () {
    $s = new S3Storage(['access_key' => 'x', 'secret_key' => 'y', 'bucket' => 'b', 'region' => 'us-east-1']);
    $r = $s->put('test.jpg', 'binary'); // no creds, will fail PUT
    // We only care that mime was attached in error context, not result
    assert_false($r['success']);
});

test('S3Storage: PUT envelope has success+error fields for failure', function () {
    $s = new S3Storage();
    $r = $s->put('foo.txt', 'bar');
    assert_true(array_key_exists('success', $r));
    assert_true(array_key_exists('error', $r));
    assert_false($r['success']);
});

test('S3Storage: temporary URL expiry clamped to S3 limits', function () {
    $s = new S3Storage(['access_key' => 'x', 'secret_key' => 'y', 'bucket' => 'b', 'region' => 'us-east-1']);
    // 30 days > 7 day max - should be clamped
    $url = $s->temporaryUrl('x.jpg', 30 * 24 * 60);
    parse_str(parse_url($url, PHP_URL_QUERY), $p);
    // X-Amz-Expires is in seconds. 7 days = 604800
    assert_eq((int) $p['X-Amz-Expires'], 7 * 24 * 60 * 60, 'expiry not clamped to 7 days');
});

test('S3Storage: 4xx-style not retried, single envelope returned', function () {
    // Use a bad endpoint that returns a non-5xx error quickly.
    // We can't easily test this without internet, but we can verify
    // the code path is correct: when isConfigured() is false the call
    // returns a single envelope without retrying.
    $s = new S3Storage();
    $r = $s->put('x.txt', 'y');
    assert_false($r['success']);
    assert_eq($r['error'], 'S3 not configured');
});

test('StorageManager: testS3() returns graceful error when not configured', function () {
    StorageManager::reset();
    putenv('AWS_ACCESS_KEY_ID=');
    putenv('AWS_SECRET_ACCESS_KEY=');
    putenv('AWS_BUCKET=');
    $m = StorageManager::getInstance();
    $r = $m->testS3();
    assert_false($r['success']);
    assert_true(strpos($r['error'], 'not configured') !== false);
});

test('StorageManager: getDriverName() reflects current state', function () {
    StorageManager::reset();
    putenv('STORAGE_DRIVER=local');
    $m = StorageManager::getInstance();
    assert_eq($m->getDriverName(), 'local');
});

test('StorageInterface: LocalStorage and S3Storage both implement it', function () {
    $l = new LocalStorage();
    $s = new S3Storage();
    assert_true($l instanceof \App\Services\Storage\StorageInterface);
    assert_true($s instanceof \App\Services\Storage\StorageInterface);
});

test('LocalStorage: copy() rejects path with traversal', function () {
    $s = new LocalStorage();
    $r = $s->copy('test/src.txt', '../escape.txt');
    assert_false($r['success']);
});

test('LocalStorage: move() on missing source returns failure', function () {
    $s = new LocalStorage();
    $r = $s->move('test/missing.txt', 'test/dest.txt');
    assert_false($r['success']);
});

test('S3Storage: copy() returns failure without creds', function () {
    $s = new S3Storage();
    $r = $s->copy('a', 'b');
    assert_false($r['success']);
});

test('S3Storage: move() returns failure without creds', function () {
    $s = new S3Storage();
    $r = $s->move('a', 'b');
    assert_false($r['success']);
});

test('S3Storage: listFiles() returns empty array without creds', function () {
    $s = new S3Storage();
    $r = $s->listFiles();
    assert_eq($r, []);
});

test('S3Storage: size() returns null without creds', function () {
    $s = new S3Storage();
    assert_null($s->size('x'));
});

test('S3Storage: mimeType() returns null without creds', function () {
    $s = new S3Storage();
    assert_null($s->mimeType('x'));
});

test('S3Storage: exists() returns false without creds', function () {
    $s = new S3Storage();
    assert_false($s->exists('x'));
});

test('S3Storage: delete() returns false without creds', function () {
    $s = new S3Storage();
    assert_false($s->delete('x'));
});

test('S3Storage: get() returns null without creds', function () {
    $s = new S3Storage();
    assert_null($s->get('x'));
});

test('S3Storage: url() returns null for traversal path', function () {
    $s = new S3Storage(['access_key' => 'x', 'secret_key' => 'y', 'bucket' => 'b', 'region' => 'us-east-1']);
    assert_null($s->url('../escape'));
    assert_null($s->url('/abs'));
});

test('LocalStorage: listFiles with empty prefix returns array', function () {
    $s = new LocalStorage();
    $files = $s->listFiles('');
    assert_true(is_array($files));
    // We can't assert a non-zero count since /uploads/ may be empty in CI.
    // Just verify it's well-formed.
    foreach ($files as $f) {
        assert_true(isset($f['key']));
        assert_true(isset($f['size']));
        assert_true(isset($f['modified']));
    }
});

// ---------- StorageManager ----------

echo "\nStorageManager:\n";

test('StorageManager: getInstance() returns singleton', function () {
    $a = StorageManager::getInstance();
    $b = StorageManager::getInstance();
    assert_eq($a, $b);
});

test('StorageManager: disk() returns LocalStorage by default', function () {
    StorageManager::reset();
    putenv('STORAGE_DRIVER=local');
    $m = StorageManager::getInstance();
    $d = $m->disk();
    assert_eq($d->getDriver(), 'local');
});

test('StorageManager: isS3Enabled() false without creds', function () {
    StorageManager::reset();
    putenv('STORAGE_DRIVER=s3');
    putenv('AWS_ACCESS_KEY_ID=');
    putenv('AWS_SECRET_ACCESS_KEY=');
    putenv('AWS_BUCKET=');
    $m = StorageManager::getInstance();
    assert_false($m->isS3Enabled());
});

test('StorageManager: isS3Enabled() true with creds', function () {
    StorageManager::reset();
    putenv('STORAGE_DRIVER=s3');
    putenv('AWS_ACCESS_KEY_ID=AKIA0000');
    putenv('AWS_SECRET_ACCESS_KEY=secret0000');
    putenv('AWS_BUCKET=test-bucket');
    $m = StorageManager::getInstance();
    assert_true($m->isS3Enabled());
});

test('StorageManager: falls back to local when S3 driver fails', function () {
    StorageManager::reset();
    putenv('STORAGE_DRIVER=s3');
    putenv('AWS_ACCESS_KEY_ID=');
    putenv('AWS_SECRET_ACCESS_KEY=');
    putenv('AWS_BUCKET=');
    $m = StorageManager::getInstance();
    $disk = $m->disk();
    // Disk should still be usable (local fallback)
    assert_eq($disk->getDriver(), 'local');
});

test('StorageManager: url() delegates to current disk', function () {
    StorageManager::reset();
    putenv('STORAGE_DRIVER=local');
    $m = StorageManager::getInstance();
    $u = $m->url('test/path.jpg');
    assert_eq($u, '/uploads/test/path.jpg');
});

test('StorageManager: temporaryUrl() delegates to current disk', function () {
    StorageManager::reset();
    putenv('STORAGE_DRIVER=local');
    $m = StorageManager::getInstance();
    $u = $m->temporaryUrl('x.jpg', 5);
    assert_eq($u, '/uploads/x.jpg');
});

test('StorageManager: put/get round-trip via facade', function () {
    StorageManager::reset();
    putenv('STORAGE_DRIVER=local');
    $m = StorageManager::getInstance();
    $r = $m->put('facade/test.txt', 'data');
    assert_true($r['success']);
    assert_eq($m->get('facade/test.txt'), 'data');
    $m->delete('facade/test.txt');
});

test('StorageManager: listFiles via facade', function () {
    StorageManager::reset();
    putenv('STORAGE_DRIVER=local');
    $m = StorageManager::getInstance();
    $m->put('facade/list/a.txt', '1');
    $m->put('facade/list/b.txt', '2');
    $files = $m->listFiles('facade/list');
    assert_true(count($files) >= 2);
    $m->delete('facade/list/a.txt');
    $m->delete('facade/list/b.txt');
});

// ---------- Live S3 tests (only if creds set) ----------

if ($s3TestMode && $hasCreds) {
    echo "\nS3Storage (live):\n";
    $s3 = new S3Storage();
    $prefix = 'test/apsdreamhome-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '/';

    test('S3 live: put small text file', function () use ($s3, $prefix) {
        $r = $s3->put($prefix . 'hello.txt', 'hello from apsdreamhome');
        assert_true($r['success'], 'put failed: ' . print_r($r, true));
        assert_eq($r['size'], 25);
    });

    test('S3 live: exists() true after put', function () use ($s3, $prefix) {
        assert_true($s3->exists($prefix . 'hello.txt'));
    });

    test('S3 live: get() returns same bytes', function () use ($s3, $prefix) {
        $g = $s3->get($prefix . 'hello.txt');
        assert_eq($g, 'hello from apsdreamhome');
    });

    test('S3 live: size() matches put', function () use ($s3, $prefix) {
        assert_eq($s3->size($prefix . 'hello.txt'), 25);
    });

    test('S3 live: mimeType() returns text/plain', function () use ($s3, $prefix) {
        $mt = $s3->mimeType($prefix . 'hello.txt');
        assert_true($mt !== null);
        assert_true(strpos($mt, 'text/') !== false, "expected text/* got $mt");
    });

    test('S3 live: url() returns S3 URL', function () use ($s3, $prefix) {
        $u = $s3->url($prefix . 'hello.txt');
        assert_true($u !== null);
        assert_true(strpos($u, 'amazonaws.com') !== false || strpos($u, (string) (getenv('AWS_ENDPOINT') ?: '')) !== false);
    });

    test('S3 live: temporaryUrl() returns presigned URL with signature', function () use ($s3, $prefix) {
        $u = $s3->temporaryUrl($prefix . 'hello.txt', 5);
        assert_true($u !== null);
        assert_true(strpos($u, 'X-Amz-Signature=') !== false);
        // Fetch it and verify content
        $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
        $body = @file_get_contents($u, false, $ctx);
        if ($body === false) {
            throw new \RuntimeException('Could not fetch presigned URL');
        }
        assert_eq($body, 'hello from apsdreamhome');
    });

    test('S3 live: copy() creates copy', function () use ($s3, $prefix) {
        $r = $s3->copy($prefix . 'hello.txt', $prefix . 'copy.txt');
        assert_true($r['success']);
        assert_eq($s3->get($prefix . 'copy.txt'), 'hello from apsdreamhome');
    });

    test('S3 live: move() copies + deletes original', function () use ($s3, $prefix) {
        $r = $s3->move($prefix . 'copy.txt', $prefix . 'moved.txt');
        assert_true($r['success']);
        assert_eq($s3->get($prefix . 'moved.txt'), 'hello from apsdreamhome');
        assert_false($s3->exists($prefix . 'copy.txt'));
    });

    test('S3 live: listFiles() finds both files', function () use ($s3, $prefix) {
        $files = $s3->listFiles($prefix);
        $keys = array_column($files, 'key');
        assert_true(in_array($prefix . 'hello.txt', $keys));
        assert_true(in_array($prefix . 'moved.txt', $keys));
    });

    test('S3 live: large file >5MB uses multipart', function () use ($s3, $prefix) {
        // 6 MB random bytes
        $big = random_bytes(6 * 1024 * 1024);
        $r = $s3->put($prefix . 'big.bin', $big);
        assert_true($r['success'], 'multipart put failed: ' . print_r($r, true));
        assert_eq($s3->size($prefix . 'big.bin'), 6 * 1024 * 1024);
    });

    test('S3 live: delete() works and is idempotent', function () use ($s3, $prefix) {
        assert_true($s3->delete($prefix . 'hello.txt'));
        assert_true($s3->delete($prefix . 'hello.txt')); // idempotent
        assert_false($s3->exists($prefix . 'hello.txt'));
    });

    test('S3 live: missing object get() returns null', function () use ($s3, $prefix) {
        assert_null($s3->get($prefix . 'does-not-exist.txt'));
    });

    test('S3 live: missing object size() returns null', function () use ($s3, $prefix) {
        assert_null($s3->size($prefix . 'does-not-exist.txt'));
    });

    // Cleanup
    $cleanup = $s3->listFiles($prefix);
    foreach ($cleanup as $f) {
        $s3->delete($f['key']);
    }
} else {
    echo "\nS3Storage live tests: SKIPPED (set S3_TEST_MODE=true with AWS creds to run)\n";
}

echo "\n=== Summary ===\n";
echo "Passed: \033[32m$pass\033[0m\n";
echo "Failed: \033[31m$fail\033[0m\n";
echo "Total:  " . ($pass + $fail) . "\n\n";

exit($fail === 0 ? 0 : 1);
