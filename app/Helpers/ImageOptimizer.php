<?php
/**
 * Image Optimizer - Compress images to WebP format
 */
class ImageOptimizer {
    public static function optimizeDirectory($dir, $convert_webp = true) {
        $results = ['processed' => 0, 'saved_kb' => 0, 'errors' => []];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

        foreach ($files as $file) {
            if (!in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png'])) continue;
            if ($file->getSize() < 500 * 1024) continue; // Skip small files

            $results['processed']++;
            $original_size = $file->getSize();

            // Use GD to optimize
            $image = imagecreatefromstring(file_get_contents($file->getPathname()));
            if (!$image) {
                $results['errors'][] = $file->getPathname();
                continue;
            }

            if ($convert_webp) {
                $webp_path = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $file->getPathname());
                if (function_exists('imagewebp')) {
                    imagewebp($image, $webp_path, 80);
                    imagedestroy($image);
                    $new_size = filesize($webp_path);
                    $results['saved_kb'] += ($original_size - $new_size) / 1024;
                }
            }

            imagedestroy($image);
        }

        return $results;
    }
}
?>