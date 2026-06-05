<?php

namespace App\Services\Storage;

/**
 * StorageInterface - uniform contract for all storage backends.
 *
 * Implemented by:
 *   - LocalStorage (filesystem in public/uploads)
 *   - S3Storage    (AWS S3 / S3-compatible: MinIO, DigitalOcean Spaces, Cloudflare R2)
 *
 * Method contract:
 *   - put / get / exists / delete / size / mimeType / url are required.
 *   - temporaryUrl is best-effort: LocalStorage returns the plain URL,
 *     S3Storage returns a real SigV4 presigned URL with X-Amz-Expires.
 *   - copy / move / listFiles must NEVER throw; return ['success' => bool, ...]
 *     arrays so callers can branch on success without try/catch noise.
 *   - All paths use forward slashes ('uploads/foo/bar.jpg'), never backslashes.
 *   - All paths are RELATIVE to the storage root (public/uploads for local,
 *     bucket root for S3). No leading slash, no "..", no drive letters.
 */
interface StorageInterface
{
    /**
     * Write contents to a path. Auto-creates parent directories (local only;
     * S3 has no dirs but the key prefix works the same way).
     *
     * @param  string                $path      relative path, e.g. "properties/img.jpg"
     * @param  string|resource       $contents  raw bytes or open file handle
     * @param  array                 $options   optional: ['visibility'=>'public-read','Cache-Control'=>'...','ContentType'=>'...']
     * @return array{success:bool,path?:string,size?:int,error?:string}
     */
    public function put(string $path, $contents, array $options = []): array;

    /**
     * Read the object contents as a string. Returns null if not found.
     */
    public function get(string $path): ?string;

    /**
     * Cheap existence check (HEAD on S3, is_file() locally). Never throws.
     */
    public function exists(string $path): bool;

    /**
     * Delete the object. Returns true on success OR if the object was already
     * missing (idempotent). Never throws.
     */
    public function delete(string $path): bool;

    /**
     * Object size in bytes, or null if missing.
     */
    public function size(string $path): ?int;

    /**
     * Detected MIME type, or null if missing.
     */
    public function mimeType(string $path): ?string;

    /**
     * Public URL for the object. For S3 this is the virtual-hosted–style URL
     * (https://bucket.s3.region.amazonaws.com/key). For local it is the
     * BASE_URL-relative path.
     */
    public function url(string $path): ?string;

    /**
     * Time-limited URL suitable for sharing with end users. Local returns
     * the plain URL (no expiry). S3 returns a presigned SigV4 URL.
     *
     * @param  string $path
     * @param  int    $expiryMinutes  1..10080 (one week is the S3 max)
     * @return string|null
     */
    public function temporaryUrl(string $path, int $expiryMinutes = 60): ?string;

    /**
     * Server-side copy. Returns ['success' => bool, 'error' => ?string].
     */
    public function copy(string $from, string $to): array;

    /**
     * Server-side copy + delete of source. Returns ['success' => bool, 'error' => ?string].
     */
    public function move(string $from, string $to): array;

    /**
     * List object keys under a prefix. Returns at most ~1000 keys (S3 list
     * is paginated but we keep it simple: single page).
     *
     * @param  string $prefix  e.g. "backups/2026-06-05/"
     * @return array<int, array{key:string,size:int,modified:string,etag?:string}>
     */
    public function listFiles(string $prefix = ''): array;

    /**
     * Driver identifier, e.g. "local" or "s3". Used in logs and admin UI.
     */
    public function getDriver(): string;
}
