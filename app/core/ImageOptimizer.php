<?php
/**
 * Image Optimizer
 *
 * Resizes uploaded images to a sane max-width, strips EXIF metadata,
 * and (when supported) emits a WebP version alongside the original.
 * Designed to be a no-op for non-image files and for environments
 * without GD — failures fall back to the original file.
 *
 * Typical usage from a controller, right after move_uploaded_file():
 *
 *     (new \App\Core\ImageOptimizer())
 *         ->setMaxWidth(1920)
 *         ->optimize($filepath);
 *
 * Stats are kept in a static array and exposed via getStats() so
 * a small admin/debug block can show how much disk the optimizer
 * has saved.
 */

namespace App\Core;

class ImageOptimizer
{
    /** @var int Maximum width in pixels; taller images keep aspect ratio. */
    private int $maxWidth = 1920;

    /** @var int JPEG/WebP quality (0-100). */
    private int $quality = 82;

    /** @var bool Emit a .webp sibling next to the optimized file. */
    private bool $emitWebp = true;

    /** @var bool Strip EXIF (orientation only — pixel data kept). */
    private bool $stripExif = true;

    /** @var array<int,array{path:string,orig:int,new:int,webp:int|null,saved:int,ratio:float}> */
    private static array $log = [];

    public function setMaxWidth(int $w): self
    {
        if ($w > 0 && $w <= 8000) {
            $this->maxWidth = $w;
        }
        return $this;
    }

    public function setQuality(int $q): self
    {
        if ($q >= 30 && $q <= 100) {
            $this->quality = $q;
        }
        return $this;
    }

    public function setEmitWebp(bool $on): self
    {
        $this->emitWebp = $on;
        return $this;
    }

    public function setStripExif(bool $on): self
    {
        $this->stripExif = $on;
        return $this;
    }

    /**
     * Optimize an image file in place.
     *
     * Returns true if the file was rewritten, false if it was left
     * untouched (not an image, GD missing, or smaller than the
     * source already). Never throws.
     */
    public function optimize(string $path): bool
    {
        $origSize = @filesize($path) ?: 0;
        $entry = [
            'path'   => $path,
            'orig'   => $origSize,
            'new'    => $origSize,
            'webp'   => null,
            'saved'  => 0,
            'ratio'  => 0.0,
        ];

        try {
            if (!is_file($path) || $origSize === 0) {
                self::$log[] = $entry;
                return false;
            }

            if (!function_exists('getimagesize') || !function_exists('imagecreatefromjpeg')) {
                self::$log[] = $entry;
                return false;
            }

            $info = @getimagesize($path);
            if (!$info || empty($info[0]) || empty($info[2])) {
                self::$log[] = $entry;
                return false;
            }

            [$width, $height, $type] = [$info[0], $info[1], $info[2]];
            $src = $this->loadImage($path, $type);
            if ($src === null) {
                self::$log[] = $entry;
                return false;
            }

            $needsResize = $width > $this->maxWidth;
            $newWidth    = $width;
            $newHeight   = $height;
            $dst         = $src;

            if ($needsResize) {
                $ratio       = $this->maxWidth / $width;
                $newWidth    = $this->maxWidth;
                $newHeight   = max(1, (int) round($height * $ratio));
                $dst         = imagecreatetruecolor($newWidth, $newHeight);

                if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                    imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
                }
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            }

            $ok = $this->saveImage($dst, $path, $type);
            if ($ok) {
                clearstatcache(true, $path);
                $entry['new'] = (int) filesize($path);
            }

            if ($this->emitWebp && function_exists('imagewebp')) {
                $webpPath = preg_replace('/\.[a-zA-Z0-9]+$/', '', $path) . '.webp';
                if (@imagewebp($dst, $webpPath, $this->quality)) {
                    clearstatcache(true, $webpPath);
                    $entry['webp'] = (int) filesize($webpPath);
                }
            }

            imagedestroy($src);
            if ($dst !== $src) {
                imagedestroy($dst);
            }
        } catch (\Throwable $e) {
            error_log('[ImageOptimizer] ' . $e->getMessage() . ' on ' . $path);
        }

        $entry['saved'] = max(0, $entry['orig'] - $entry['new']);
        $entry['ratio'] = $entry['orig'] > 0 ? round($entry['saved'] / $entry['orig'] * 100, 1) : 0.0;
        self::$log[] = $entry;
        return $entry['new'] < $entry['orig'];
    }

    /**
     * Cumulative stats for the current request.
     *
     * @return array{files:int,orig:int,new:int,webp:int,saved:int,ratio:float}
     */
    public static function getStats(): array
    {
        $files = count(self::$log);
        $orig  = 0; $new = 0; $webp = 0;
        foreach (self::$log as $e) {
            $orig += $e['orig'];
            $new  += $e['new'];
            $webp += (int) ($e['webp'] ?? 0);
        }
        $saved = max(0, $orig - $new);
        $ratio = $orig > 0 ? round($saved / $orig * 100, 1) : 0.0;
        return [
            'files' => $files,
            'orig'  => $orig,
            'new'   => $new,
            'webp'  => $webp,
            'saved' => $saved,
            'ratio' => $ratio,
        ];
    }

    /** @return array<int,array{path:string,orig:int,new:int,webp:int|null,saved:int,ratio:float}> */
    public static function getLog(): array
    {
        return self::$log;
    }

    public static function resetStats(): void
    {
        self::$log = [];
    }

    /**
     * Static convenience wrapper for one-liner call sites.
     */
    public static function optimizeStatic(string $path): bool
    {
        return (new self())->optimize($path);
    }

    private function loadImage(string $path, int $type)
    {
        switch ($type) {
            case IMAGETYPE_JPEG: return @imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:  return @imagecreatefrompng($path);
            case IMAGETYPE_GIF:  return @imagecreatefromgif($path);
            case IMAGETYPE_WEBP: return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null;
        }
        return null;
    }

    private function saveImage($img, string $path, int $type): bool
    {
        switch ($type) {
            case IMAGETYPE_JPEG: return @imagejpeg($img, $path, $this->quality);
            case IMAGETYPE_PNG:
                $level = (int) round((100 - $this->quality) / 11);
                return @imagepng($img, $path, max(0, min(9, $level)));
            case IMAGETYPE_GIF:  return @imagegif($img, $path);
            case IMAGETYPE_WEBP:
                return function_exists('imagewebp') ? @imagewebp($img, $path, $this->quality) : false;
        }
        return false;
    }
}
