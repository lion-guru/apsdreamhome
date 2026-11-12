<?php
/**
 * Verify the AGENTS.md session log against git HEAD.
 *
 * WHY (S461): AGENTS.md was found TRUNCATED in the worktree - cut at exactly
 * byte offset 512000 (500 KiB) with the literal string
 * "... [truncated for diff preview]" appended, i.e. a tool's TRUNCATED READ
 * OUTPUT had been written back over the file.  That silently dropped 45
 * session entries (Sessions 159-199 and more) - 1257 lines of history.
 *
 * The damage was invisible: `git status` showed the file as MODIFIED (a
 * normal-looking edit), the file still opened, and it still had 162 "## Session"
 * headings, so nothing complained.  Only a byte-level comparison against
 * HEAD showed that the committed history was intact and the worktree was
 * the damaged copy.
 *
 * This guard makes that class of damage impossible to commit:
 *   - the worktree file must not be SHORTER than HEAD's (a truncation is
 *     always a shrink; a legitimate entry is always an append)
 *   - the worktree must not contain a truncation marker
 *   - it must not have lost session headings
 *
 * Usage:
 *   php scripts/verify_agents_log.php          # check only, exit 1 on damage
 *   php scripts/verify_agents_log.php --restore  # restore from HEAD (byte-exact)
 *
 * Exit codes: 0 = healthy, 1 = DAMAGED (worktree is truncated/corrupt),
 *              2 = could not check.
 */

$root = dirname(__DIR__);
$file = $root . '/AGENTS.md';

$restore = in_array('--restore', $argv, true);

if (!is_file($file)) {
    fwrite(STDERR, "FAIL: AGENTS.md not found at {$file}\n");
    exit(2);
}

$worktree = file_get_contents($file);

// ---- HEAD's copy ---------------------------------------------------------
// NOTE: `git -C '<path>'` does NOT work on Windows (cmd.exe does not treat
// single quotes as quoting), and exec() splits the output into LINES, which
// would corrupt a byte-exact comparison.  So: chdir into the repo and let
// git write the blob to a temp file through cmd.exe's byte-preserving
// redirection.
$head = null;
$headSessions = null;
$tmp = @tempnam(sys_get_temp_dir(), 'agents');
if ($tmp !== false) {
    $cwd = getcwd();
    @chdir($root);
    $cmd = 'git cat-file -p HEAD:AGENTS.md > ' . escapeshellarg($tmp) . ' 2>NUL';
    @exec($cmd, $ignored, $rc);
    if ($cwd !== false) {
        @chdir($cwd);
    }
    if ($rc === 0) {
        $bytes = @file_get_contents($tmp);
        if ($bytes !== false && $bytes !== '') {
            $head = $bytes;
        }
    }
    @unlink($tmp);
}
if ($head === null) {
    fwrite(STDERR, "WARN: could not read HEAD:AGENTS.md (git unavailable or no HEAD) - "
        . "running the standalone checks only\n");
}

$marker = '[truncated for diff preview]';
// The corruption signature is the marker at the VERY END of the file (the
// file was cut and the marker appended).  Checking the whole document
// instead would false-positive on a session entry that QUOTES the marker -
// which is exactly what S463's own entry does.
$tail = substr($worktree, -512);
$hasMarker = strpos($tail, $marker) !== false;

$sessions = function (string $text): int {
    return preg_match_all('/^## Session \d+/m', $text);
};
$wtSessions = $sessions($worktree);
$hdSessions = $head !== null ? $sessions($head) : null;

$problems = [];

if ($hasMarker) {
    $problems[] = "the worktree contains the truncation marker '{$marker}'";
}

if ($head !== null) {
    $wtLen = strlen($worktree);
    $hdLen = strlen($head);

    if ($wtLen < $hdLen) {
        $problems[] = sprintf(
            'the worktree is SHORTER than HEAD (%d bytes vs %d) - %d bytes of log were lost',
            $wtLen, $hdLen, $hdLen - $wtLen
        );
    }

    if ($hdSessions !== null && $wtSessions < $hdSessions) {
        $problems[] = sprintf(
            'session headings lost: worktree has %d, HEAD has %d',
            $wtSessions, $hdSessions
        );
    }
}

if ($problems) {
    fwrite(STDERR, "=== AGENTS.md INTEGRITY: DAMAGED ===\n");
    foreach ($problems as $p) {
        fwrite(STDERR, "  - {$p}\n");
    }
    fwrite(STDERR, "  worktree: " . strlen($worktree) . " bytes, {$wtSessions} session headings\n");
    if ($head !== null) {
        fwrite(STDERR, "  HEAD    : " . strlen($head) . " bytes, {$hdSessions} session headings\n");
    }

    if ($restore && $head !== null) {
        // byte-exact restore from HEAD
        file_put_contents($file, $head);
        fwrite(STDERR, "  RESTORED from HEAD (byte-exact, " . strlen($head) . " bytes).\n");
        exit(0);
    }

    fwrite(STDERR, "  Run 'php scripts/verify_agents_log.php --restore' to restore it byte-exactly "
        . "from HEAD (only safe when the worktree holds nothing newer than HEAD).\n");
    exit(1);
}

echo "AGENTS.md integrity OK: " . strlen($worktree) . " bytes, {$wtSessions} session headings"
    . ($head !== null ? " (HEAD: " . strlen($head) . " bytes, {$hdSessions} headings)" : "")
    . "\n";
exit(0);
