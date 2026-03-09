<?php

namespace Tests\Feature\Media;

use App\Services\Media\MediaLibraryService;
use PHPUnit\Framework\TestCase;

/**
 * Media Library Service Test - APS Dream Home
 * Custom MVC testing without Laravel dependencies
 */
class MediaLibraryServiceTest extends TestCase
{
    private $mediaService;
    private $testMediaId;
    
    protected function setUp(): void
    {
        $this->mediaService = new MediaLibraryService();
    }
    
    /** @test */
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(MediaLibraryService::class, $this->mediaService);
    }
    
    /** @test */
    public function it_can_handle_file_upload()
    {
        $data = [
            'title' => 'Test Image',
            'description' => 'A test image for unit testing',
            'category' => 'test',
            'tags' => 'test,unit,upload'
        ];
        
        // Mock file upload
        $files = [
            'media_file' => [
                'name' => 'test_image.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => sys_get_temp_dir() . '/test_media.jpg',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ];
        
        // Create a temporary image file
        $image = imagecreate(100, 100);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        imagestring($image, 1, 10, 10, 'Test', $textColor);
        imagejpeg($image, $files['media_file']['tmp_name']);
        imagedestroy($image);
        
        $result = $this->mediaService->handleUpload($data, $files);
        
        // Clean up temporary file
        unlink($files['media_file']['tmp_name']);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('media_id', $result);
        
        // Store ID for cleanup
        $this->testMediaId = $result['media_id'];
    }
    
    /** @test */
    public function it_validates_required_upload_fields()
    {
        $data = [
            'title' => '',
            'description' => '',
            'category' => '',
            'tags' => ''
        ];
        
        $result = $this->mediaService->handleUpload($data);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('No file uploaded', $result['message']);
    }
    
    /** @test */
    public function it_validates_file_size()
    {
        $data = [
            'title' => 'Test Image',
            'description' => 'Test description',
            'category' => 'test',
            'tags' => 'test'
        ];
        
        // Mock oversized file
        $files = [
            'media_file' => [
                'name' => 'large_image.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => sys_get_temp_dir() . '/large_test.jpg',
                'error' => UPLOAD_ERR_OK,
                'size' => 15 * 1024 * 1024 // 15MB (exceeds 10MB limit)
            ]
        ];
        
        // Create a temporary file
        file_put_contents($files['media_file']['tmp_name'], 'test content');
        
        $result = $this->mediaService->handleUpload($data, $files);
        
        // Clean up temporary file
        unlink($files['media_file']['tmp_name']);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('exceeds maximum limit', $result['message']);
    }
    
    /** @test */
    public function it_validates_file_type()
    {
        $data = [
            'title' => 'Test File',
            'description' => 'Test description',
            'category' => 'test',
            'tags' => 'test'
        ];
        
        // Mock invalid file type
        $files = [
            'media_file' => [
                'name' => 'test_file.exe',
                'type' => 'application/octet-stream',
                'tmp_name' => sys_get_temp_dir() . '/test_file.exe',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ];
        
        // Create a temporary file
        file_put_contents($files['media_file']['tmp_name'], 'test content');
        
        $result = $this->mediaService->handleUpload($data, $files);
        
        // Clean up temporary file
        unlink($files['media_file']['tmp_name']);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('File type not allowed', $result['message']);
    }
    
    /** @test */
    public function it_can_get_media_files()
    {
        $result = $this->mediaService->getMediaFiles();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
    }
    
    /** @test */
    public function it_can_filter_media_files_by_category()
    {
        $result = $this->mediaService->getMediaFiles('test');
        
        $this->assertTrue($result['success']);
        
        // Verify all results have the specified category (if any results exist)
        if (!empty($result['data'])) {
            foreach ($result['data'] as $file) {
                $this->assertEquals('test', $file['category']);
            }
        }
    }
    
    /** @test */
    public function it_can_search_media_files()
    {
        $result = $this->mediaService->getMediaFiles(null, 'test');
        
        $this->assertTrue($result['success']);
        $this->assertIsArray($result['data']);
    }
    
    /** @test */
    public function it_can_get_media_file_by_id()
    {
        // First create a test media file
        $this->createTestMediaFile();
        
        if ($this->testMediaId) {
            $result = $this->mediaService->getMediaFile($this->testMediaId);
            
            $this->assertTrue($result['success']);
            $this->assertArrayHasKey('data', $result);
            $this->assertEquals($this->testMediaId, $result['data']['id']);
            $this->assertArrayHasKey('url', $result['data']);
        }
    }
    
    /** @test */
    public function it_returns_error_for_non_existent_media_file()
    {
        $result = $this->mediaService->getMediaFile(99999);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not found', $result['message']);
    }
    
    /** @test */
    public function it_can_update_media_file()
    {
        // First create a test media file
        $this->createTestMediaFile();
        
        if ($this->testMediaId) {
            $result = $this->mediaService->updateMediaFile(
                $this->testMediaId,
                'Updated Title',
                'Updated description',
                'updated_category',
                'updated,tag'
            );
            
            $this->assertTrue($result['success']);
            
            // Verify the update
            $fileResult = $this->mediaService->getMediaFile($this->testMediaId);
            if ($fileResult['success']) {
                $this->assertEquals('Updated Title', $fileResult['data']['title']);
                $this->assertEquals('Updated description', $fileResult['data']['description']);
                $this->assertEquals('updated_category', $fileResult['data']['category']);
                $this->assertEquals('updated,tag', $fileResult['data']['tags']);
            }
        }
    }
    
    /** @test */
    public function it_can_delete_media_file()
    {
        // First create a test media file
        $this->createTestMediaFile();
        
        if ($this->testMediaId) {
            $result = $this->mediaService->deleteMediaFile($this->testMediaId);
            
            $this->assertTrue($result['success']);
            
            // Verify file is deleted
            $fileResult = $this->mediaService->getMediaFile($this->testMediaId);
            $this->assertFalse($fileResult['success']);
        }
    }
    
    /** @test */
    public function it_can_get_media_stats()
    {
        $result = $this->mediaService->getMediaStats();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $stats = $result['data'];
        
        // Check required stats keys
        $requiredKeys = [
            'total_files',
            'total_size',
            'by_category',
            'by_type',
            'recent_uploads',
            'storage_usage'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $stats);
        }
    }
    
    /** @test */
    public function it_can_get_categories()
    {
        $result = $this->mediaService->getCategories();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
    }
    
    /** @test */
    public function it_can_identify_image_files()
    {
        $this->assertTrue($this->mediaService->isImage('image/jpeg'));
        $this->assertTrue($this->mediaService->isImage('image/png'));
        $this->assertTrue($this->mediaService->isImage('image/gif'));
        $this->assertFalse($this->mediaService->isImage('application/pdf'));
        $this->assertFalse($this->mediaService->isImage('text/plain'));
    }
    
    /** @test */
    public function it_can_get_file_url()
    {
        $url = $this->mediaService->getFileUrl('test_image.jpg');
        
        $this->assertIsString($url);
        $this->assertStringContainsString('test_image.jpg', $url);
        $this->assertStringContainsString('/uploads/media/', $url);
    }
    
    /** @test */
    public function it_can_get_file_path()
    {
        $path = $this->mediaService->getFilePath('test_image.jpg');
        
        $this->assertIsString($path);
        $this->assertStringContainsString('test_image.jpg', $path);
        $this->assertStringContainsString('/uploads/media/', $path);
    }
    
    /** @test */
    public function it_can_create_thumbnail_for_image()
    {
        // First create a test media file
        $this->createTestMediaFile();
        
        if ($this->testMediaId) {
            $result = $this->mediaService->getMediaFile($this->testMediaId);
            
            if ($result['success'] && $result['data']['is_image']) {
                $thumbnailFilename = $this->mediaService->createThumbnail($result['data']['filename'], 150, 150);
                
                $this->assertIsString($thumbnailFilename);
                $this->assertStringContainsString('_thumb', $thumbnailFilename);
                
                // Clean up thumbnail
                $thumbnailPath = STORAGE_PATH . '/uploads/media/thumbnails/' . $thumbnailFilename;
                if (file_exists($thumbnailPath)) {
                    unlink($thumbnailPath);
                }
            }
        }
    }
    
    /** @test */
    public function it_handles_pagination_correctly()
    {
        // Get first page
        $page1 = $this->mediaService->getMediaFiles(null, null, 5, 0);
        
        // Get second page
        $page2 = $this->mediaService->getMediaFiles(null, null, 5, 5);
        
        $this->assertTrue($page1['success']);
        $this->assertTrue($page2['success']);
        
        $this->assertLessThanOrEqual(5, count($page1['data']));
        $this->assertLessThanOrEqual(5, count($page2['data']));
    }
    
    /** @test */
    public function it_handles_upload_errors_correctly()
    {
        $data = [
            'title' => 'Test Image',
            'description' => 'Test description',
            'category' => 'test',
            'tags' => 'test'
        ];
        
        // Test UPLOAD_ERR_NO_FILE
        $files = [
            'media_file' => [
                'name' => '',
                'type' => '',
                'tmp_name' => '',
                'error' => UPLOAD_ERR_NO_FILE,
                'size' => 0
            ]
        ];
        
        $result = $this->mediaService->handleUpload($data, $files);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('No file was uploaded', $result['message']);
    }
    
    /** @test */
    public function it_generates_unique_filenames()
    {
        // This is tested indirectly through the upload process
        // Multiple uploads with the same name should get different filenames
        
        $data = [
            'title' => 'Test Image',
            'description' => 'Test description',
            'category' => 'test',
            'tags' => 'test'
        ];
        
        $files = [
            'media_file' => [
                'name' => 'duplicate_name.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => sys_get_temp_dir() . '/test_duplicate.jpg',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ];
        
        // Create temporary image
        $image = imagecreate(50, 50);
        imagejpeg($image, $files['media_file']['tmp_name']);
        imagedestroy($image);
        
        $result1 = $this->mediaService->handleUpload($data, $files);
        
        // Clean up and recreate for second upload
        unlink($files['media_file']['tmp_name']);
        $image = imagecreate(50, 50);
        imagejpeg($image, $files['media_file']['tmp_name']);
        imagedestroy($image);
        
        $result2 = $this->mediaService->handleUpload($data, $files);
        
        // Clean up
        unlink($files['media_file']['tmp_name']);
        
        if ($result1['success'] && $result2['success']) {
            // Clean up test files
            $this->mediaService->deleteMediaFile($result1['media_id']);
            $this->mediaService->deleteMediaFile($result2['media_id']);
            
            // Filenames should be different
            $this->assertNotEquals($result1['filename'], $result2['filename']);
        }
    }
    
    /**
     * Helper method to create a test media file
     */
    private function createTestMediaFile()
    {
        if ($this->testMediaId) {
            return; // Already created
        }
        
        $data = [
            'title' => 'Test Media File',
            'description' => 'Test description',
            'category' => 'test',
            'tags' => 'test,unit'
        ];
        
        $files = [
            'media_file' => [
                'name' => 'test_media.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => sys_get_temp_dir() . '/test_media_unit.jpg',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ];
        
        // Create temporary image
        $image = imagecreate(100, 100);
        $bgColor = imagecolorallocate($image, 200, 200, 200);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        imagestring($image, 2, 20, 40, 'TEST', $textColor);
        imagejpeg($image, $files['media_file']['tmp_name']);
        imagedestroy($image);
        
        $result = $this->mediaService->handleUpload($data, $files);
        
        // Clean up temporary file
        unlink($files['media_file']['tmp_name']);
        
        if ($result['success']) {
            $this->testMediaId = $result['media_id'];
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up test media file
        if ($this->testMediaId) {
            $this->mediaService->deleteMediaFile($this->testMediaId);
        }
        
        parent::tearDown();
    }
}