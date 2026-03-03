<?php
/**
 * APS Dream Home - Image Optimizer
 */

namespace App\Core;

class ImageOptimizer
{
    private $config;
    private $optimizedPath;
    private $thumbnailPath;

    public function __construct()
    {
        $this->config = [
            'quality' => 85,
            'max_width' => 1920,
            'max_height' => 1080,
            'thumbnail_width' => 300,
            'thumbnail_height' => 200,
            'webp_quality' => 80
        ];
        $this->optimizedPath = PUBLIC_PATH . '/assets/images/optimized';
        $this->thumbnailPath = PUBLIC_PATH . '/assets/images/thumbnails';
    }

    public function optimize($sourcePath, $filename)
    {
        if (!file_exists($sourcePath)) {
            return false;
        }

        $info = getimagesize($sourcePath);
        if (!$info) {
            return false;
        }

        $mimeType = $info['mime'];
        $width = $info[0];
        $height = $info[1];

        // Create image resource based on mime type
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        if (!$image) {
            return false;
        }

        // Resize if necessary
        $newWidth = $width;
        $newHeight = $height;

        if ($width > $this->config['max_width'] || $height > $this->config['max_height']) {
            $ratio = min($this->config['max_width'] / $width, $this->config['max_height'] / $height);
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);
        }

        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save optimized image
        $optimizedPath = $this->optimizedPath . '/' . $filename;
        imagejpeg($newImage, $optimizedPath, $this->config['quality']);

        // Create WebP version
        $webpPath = $this->optimizedPath . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        imagewebp($newImage, $webpPath, $this->config['webp_quality']);

        // Create thumbnail
        $this->createThumbnail($newImage, $filename);

        // Clean up
        imagedestroy($image);
        imagedestroy($newImage);

        return true;
    }

    private function createThumbnail($image, $filename)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $ratio = min($this->config['thumbnail_width'] / $width, $this->config['thumbnail_height'] / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);

        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $thumbnailPath = $this->thumbnailPath . '/' . $filename;
        imagejpeg($thumbnail, $thumbnailPath, $this->config['quality']);

        imagedestroy($thumbnail);
    }

    public function getOptimizedPath($filename)
    {
        return $this->optimizedPath . '/' . $filename;
    }

    public function getThumbnailPath($filename)
    {
        return $this->thumbnailPath . '/' . $filename;
    }
}
