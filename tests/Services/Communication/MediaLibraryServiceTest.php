<?php

namespace Tests\Services\Communication;

use PHPUnit\Framework\TestCase;
use App\Services\Communication\MediaLibraryService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

class MediaLibraryServiceTest extends TestCase
{
    private MediaLibraryService $mediaService;
    private Database $db;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mediaService = new MediaLibraryService($this->db, $this->logger);
    }

    public function testUploadMedia(): void
    {
        $fileData = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'size' => 1024000,
            'tmp_name' => '/tmp/test.jpg'
        ];

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->mediaService->uploadMedia($fileData, 'test');

        $this->assertTrue($result['success']);
        $this->assertEquals('Media uploaded successfully', $result['message']);
        $this->assertArrayHasKey('media_id', $result);
    }

    public function testUploadMediaInvalidFile(): void
    {
        $fileData = [
            'name' => 'test.exe',
            'type' => 'application/octet-stream',
            'size' => 1024000,
            'tmp_name' => '/tmp/test.exe'
        ];

        $result = $this->mediaService->uploadMedia($fileData, 'test');

        $this->assertFalse($result['success']);
        $this->assertStringContains('Invalid file type', $result['message']);
    }

    public function testUploadMediaFileSizeExceeded(): void
    {
        $fileData = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'size' => 20 * 1024 * 1024, // 20MB
            'tmp_name' => '/tmp/test.jpg'
        ];

        $result = $this->mediaService->uploadMedia($fileData, 'test');

        $this->assertFalse($result['success']);
        $this->assertStringContains('File size exceeds', $result['message']);
    }

    public function testGetMedia(): void
    {
        $mediaId = 1;
        $media = [
            'id' => $mediaId,
            'name' => 'test.jpg',
            'type' => 'image',
            'category' => 'test',
            'metadata' => json_encode(['width' => 800, 'height' => 600])
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($media);

        $result = $this->mediaService->getMedia($mediaId);

        $this->assertEquals($media, $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function testUpdateMedia(): void
    {
        $mediaId = 1;
        $updateData = [
            'name' => 'updated.jpg',
            'category' => 'updated'
        ];

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->mediaService->updateMedia($mediaId, $updateData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Media updated successfully', $result['message']);
    }

    public function testDeleteMedia(): void
    {
        $mediaId = 1;
        $media = [
            'id' => $mediaId,
            'file_path' => '/uploads/media/test.jpg'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($media);

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->mediaService->deleteMedia($mediaId);

        $this->assertTrue($result['success']);
        $this->assertEquals('Media deleted successfully', $result['message']);
    }

    public function testSearchMedia(): void
    {
        $query = 'test';
        $filters = ['category' => 'test', 'type' => 'image'];
        $results = [
            ['id' => 1, 'name' => 'test1.jpg', 'category' => 'test'],
            ['id' => 2, 'name' => 'test2.jpg', 'category' => 'test']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($results);

        $result = $this->mediaService->searchMedia($query, $filters);

        $this->assertCount(2, $result);
        $this->assertEquals('test1.jpg', $result[0]['name']);
    }

    public function testCreateGallery(): void
    {
        $galleryData = [
            'name' => 'Test Gallery',
            'description' => 'Test Description',
            'media_ids' => [1, 2, 3]
        ];

        $this->db->expects($this->exactly(4))
            ->method('execute')
            ->willReturn(1);

        $result = $this->mediaService->createGallery($galleryData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Gallery created successfully', $result['message']);
        $this->assertArrayHasKey('gallery_id', $result);
    }

    public function testGetGallery(): void
    {
        $galleryId = 1;
        $gallery = [
            'id' => $galleryId,
            'name' => 'Test Gallery',
            'description' => 'Test Description'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($gallery);

        $result = $this->mediaService->getGallery($galleryId);

        $this->assertEquals($gallery, $result);
    }

    public function testGetMediaStats(): void
    {
        $stats = [
            'total_media' => 100,
            'total_size' => 104857600,
            'by_type' => ['image' => 80, 'document' => 20],
            'by_category' => ['test' => 50, 'production' => 50]
        ];

        $this->db->expects($this->exactly(4))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                $stats['total_media'],
                $stats['total_size'],
                ['image' => 80],
                ['test' => 50]
            );

        $result = $this->mediaService->getMediaStats();

        $this->assertEquals($stats['total_media'], $result['total_media']);
        $this->assertEquals($stats['total_size'], $result['total_size']);
        $this->assertArrayHasKey('by_type', $result);
        $this->assertArrayHasKey('by_category', $result);
    }
}
