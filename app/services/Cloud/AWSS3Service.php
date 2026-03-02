<?php
namespace App\Services\Cloud;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class AWSS3Service
{
    private $s3Client;
    private $bucket;
    private $config;
    
    public function __construct()
    {
        $this->config = [
            'region' => config('cloud.aws.region', 'us-east-1'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('cloud.aws.key'),
                'secret' => config('cloud.aws.secret')
            ]
        ];
        
        $this->bucket = config('cloud.aws.s3.bucket');
        $this->initializeS3Client();
    }
    
    /**
     * Initialize S3 client
     */
    private function initializeS3Client()
    {
        try {
            $this->s3Client = new S3Client($this->config);
        } catch (AwsException $e) {
            throw new Exception('Failed to initialize S3 client: ' . $e->getMessage());
        }
    }
    
    /**
     * Upload file to S3
     */
    public function uploadFile($localPath, $s3Key, $options = [])
    {
        try {
            $uploadOptions = [
                'Bucket' => $this->bucket,
                'Key' => $s3Key,
                'SourceFile' => $localPath
            ];
            
            // Add optional parameters
            if (isset($options['acl'])) {
                $uploadOptions['ACL'] = $options['acl'];
            }
            
            if (isset($options['metadata'])) {
                $uploadOptions['Metadata'] = $options['metadata'];
            }
            
            if (isset($options['content_type'])) {
                $uploadOptions['ContentType'] = $options['content_type'];
            }
            
            $result = $this->s3Client->putObject($uploadOptions);
            
            return [
                'success' => true,
                'url' => $result['ObjectURL'],
                'etag' => $result['ETag'],
                'version_id' => $result['VersionId'] ?? null
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Upload file from string
     */
    public function uploadFromString($content, $s3Key, $options = [])
    {
        try {
            $uploadOptions = [
                'Bucket' => $this->bucket,
                'Key' => $s3Key,
                'Body' => $content
            ];
            
            // Add optional parameters
            if (isset($options['acl'])) {
                $uploadOptions['ACL'] = $options['acl'];
            }
            
            if (isset($options['metadata'])) {
                $uploadOptions['Metadata'] = $options['metadata'];
            }
            
            if (isset($options['content_type'])) {
                $uploadOptions['ContentType'] = $options['content_type'];
            }
            
            $result = $this->s3Client->putObject($uploadOptions);
            
            return [
                'success' => true,
                'url' => $result['ObjectURL'],
                'etag' => $result['ETag'],
                'version_id' => $result['VersionId'] ?? null
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Download file from S3
     */
    public function downloadFile($s3Key, $localPath = null)
    {
        try {
            $getObjectOptions = [
                'Bucket' => $this->bucket,
                'Key' => $s3Key
            ];
            
            $result = $this->s3Client->getObject($getObjectOptions);
            
            if ($localPath) {
                file_put_contents($localPath, $result['Body']);
                return [
                    'success' => true,
                    'local_path' => $localPath,
                    'size' => $result['ContentLength'],
                    'last_modified' => $result['LastModified']
                ];
            } else {
                return [
                    'success' => true,
                    'content' => $result['Body'],
                    'size' => $result['ContentLength'],
                    'last_modified' => $result['LastModified']
                ];
            }
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete file from S3
     */
    public function deleteFile($s3Key)
    {
        try {
            $result = $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $s3Key
            ]);
            
            return [
                'success' => true,
                'delete_marker' => $result['DeleteMarker'] ?? false
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * List files in S3
     */
    public function listFiles($prefix = '', $maxKeys = 1000)
    {
        try {
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => $prefix,
                'MaxKeys' => $maxKeys
            ]);
            
            $files = [];
            foreach ($result['Contents'] as $object) {
                $files[] = [
                    'key' => $object['Key'],
                    'size' => $object['Size'],
                    'last_modified' => $object['LastModified'],
                    'etag' => $object['ETag'],
                    'storage_class' => $object['StorageClass']
                ];
            }
            
            return [
                'success' => true,
                'files' => $files,
                'count' => count($files),
                'is_truncated' => $result['IsTruncated'],
                'next_token' => $result['NextContinuationToken'] ?? null
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get file URL
     */
    public function getFileUrl($s3Key, $expiration = 3600)
    {
        try {
            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key' => $s3Key
            ]);
            
            $request = $this->s3Client->createPresignedRequest($cmd, '+' . $expiration . ' seconds');
            
            return (string) $request->getUri();
        } catch (AwsException $e) {
            return false;
        }
    }
    
    /**
     * Get file info
     */
    public function getFileInfo($s3Key)
    {
        try {
            $result = $this->s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $s3Key
            ]);
            
            return [
                'success' => true,
                'size' => $result['ContentLength'],
                'last_modified' => $result['LastModified'],
                'etag' => $result['ETag'],
                'content_type' => $result['ContentType'],
                'metadata' => $result['Metadata']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Copy file within S3
     */
    public function copyFile($sourceKey, $destinationKey)
    {
        try {
            $result = $this->s3Client->copyObject([
                'Bucket' => $this->bucket,
                'Key' => $destinationKey,
                'CopySource' => $this->bucket . '/' . $sourceKey
            ]);
            
            return [
                'success' => true,
                'etag' => $result['ETag'],
                'version_id' => $result['VersionId'] ?? null
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create folder
     */
    public function createFolder($folderPath)
    {
        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $folderPath . '/',
                'Body' => '',
                'ACL' => 'public-read'
            ]);
            
            return [
                'success' => true,
                'url' => $result['ObjectURL']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get bucket usage statistics
     */
    public function getBucketStats()
    {
        try {
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'MaxKeys' => 1
            ]);
            
            return [
                'success' => true,
                'object_count' => $result['KeyCount'],
                'total_size' => $result['MaxKeys'] // This would need actual calculation
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
