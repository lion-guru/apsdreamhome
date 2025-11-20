<?php
/**
 * CDN Asset Downloader
 * Downloads external CDN assets locally to avoid CSP issues and improve performance
 */

class CDNAssetDownloader {
    private $cdnUrls = [
        'css' => [
            'bootstrap' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            'font-awesome' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            'aos' => 'https://unpkg.com/aos@2.3.1/dist/aos.css',
            'swiper' => 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css'
        ],
        'js' => [
            'bootstrap' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            'aos' => 'https://unpkg.com/aos@2.3.1/dist/aos.js',
            'swiper' => 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js',
            'lazyload' => 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js'
        ]
    ];
    
    private $localPaths = [
        'css' => 'assets/vendor/css/',
        'js' => 'assets/vendor/js/'
    ];
    
    public function __construct() {
        // Create directories if they don't exist
        foreach ($this->localPaths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
    
    public function downloadAllAssets() {
        $results = ['success' => [], 'failed' => []];
        
        echo "🚀 Starting CDN asset download...\n\n";
        
        // Download CSS files
        foreach ($this->cdnUrls['css'] as $name => $url) {
            echo "📥 Downloading CSS: $name... ";
            $localPath = $this->localPaths['css'] . $name . '.min.css';
            
            if ($this->downloadFile($url, $localPath)) {
                echo "✅ Success\n";
                $results['success'][] = "$name CSS";
            } else {
                echo "❌ Failed\n";
                $results['failed'][] = "$name CSS";
            }
        }
        
        // Download JS files
        foreach ($this->cdnUrls['js'] as $name => $url) {
            echo "📥 Downloading JS: $name... ";
            $localPath = $this->localPaths['js'] . $name . '.min.js';
            
            if ($this->downloadFile($url, $localPath)) {
                echo "✅ Success\n";
                $results['success'][] = "$name JS";
            } else {
                echo "❌ Failed\n";
                $results['failed'][] = "$name JS";
            }
        }
        
        $this->printResults($results);
        return $results;
    }
    
    private function downloadFile($url, $localPath) {
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);
            
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($content !== false && $httpCode === 200) {
                return file_put_contents($localPath, $content) !== false;
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function printResults($results) {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 DOWNLOAD SUMMARY\n";
        echo str_repeat("=", 50) . "\n";
        
        echo "✅ Successfully downloaded: " . count($results['success']) . " files\n";
        foreach ($results['success'] as $file) {
            echo "   - $file\n";
        }
        
        if (!empty($results['failed'])) {
            echo "\n❌ Failed to download: " . count($results['failed']) . " files\n";
            foreach ($results['failed'] as $file) {
                echo "   - $file\n";
            }
        }
        
        echo str_repeat("=", 50) . "\n";
    }
    
    public function generateLocalPaths() {
        return [
            'css' => [
                'bootstrap' => '/apsdreamhomefinal/' . $this->localPaths['css'] . 'bootstrap.min.css',
                'font-awesome' => '/apsdreamhomefinal/' . $this->localPaths['css'] . 'font-awesome.min.css',
                'aos' => '/apsdreamhomefinal/' . $this->localPaths['css'] . 'aos.min.css',
                'swiper' => '/apsdreamhomefinal/' . $this->localPaths['css'] . 'swiper.min.css'
            ],
            'js' => [
                'bootstrap' => '/apsdreamhomefinal/' . $this->localPaths['js'] . 'bootstrap.min.js',
                'aos' => '/apsdreamhomefinal/' . $this->localPaths['js'] . 'aos.min.js',
                'swiper' => '/apsdreamhomefinal/' . $this->localPaths['js'] . 'swiper.min.js',
                'lazyload' => '/apsdreamhomefinal/' . $this->localPaths['js'] . 'lazyload.min.js'
            ]
        ];
    }
}

// Run the downloader if this script is called directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $downloader = new CDNAssetDownloader();
    $downloader->downloadAllAssets();
}
?>