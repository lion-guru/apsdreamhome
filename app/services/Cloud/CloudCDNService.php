<?php
namespace App\Services\Cloud;

use Aws\CloudFront\CloudFrontClient;
use Aws\Exception\AwsException;

class CloudCDNService
{
    private $cloudFrontClient;
    private $distributionId;
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
        
        $this->distributionId = config('cloud.aws.cloudfront.distribution_id');
        $this->initializeCloudFrontClient();
    }
    
    /**
     * Initialize CloudFront client
     */
    private function initializeCloudFrontClient()
    {
        try {
            $this->cloudFrontClient = new CloudFrontClient($this->config);
        } catch (AwsException $e) {
            throw new Exception('Failed to initialize CloudFront client: ' . $e->getMessage());
        }
    }
    
    /**
     * Create invalidation
     */
    public function createInvalidation($paths, $callerReference = null)
    {
        try {
            if (!$callerReference) {
                $callerReference = 'inv_' . time() . '_' . uniqid();
            }
            
            $result = $this->cloudFrontClient->createInvalidation([
                'DistributionId' => $this->distributionId,
                'InvalidationBatch' => [
                    'Paths' => [
                        'Quantity' => count($paths),
                        'Items' => $paths
                    ],
                    'CallerReference' => $callerReference
                ]
            ]);
            
            return [
                'success' => true,
                'invalidation_id' => $result['Invalidation']['Id'],
                'status' => $result['Invalidation']['Status'],
                'create_time' => $result['Invalidation']['CreateTime']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get invalidation status
     */
    public function getInvalidationStatus($invalidationId)
    {
        try {
            $result = $this->cloudFrontClient->getInvalidation([
                'DistributionId' => $this->distributionId,
                'Id' => $invalidationId
            ]);
            
            return [
                'success' => true,
                'status' => $result['Invalidation']['Status'],
                'create_time' => $result['Invalidation']['CreateTime'],
                'paths' => $result['Invalidation']['InvalidationBatch']['Paths']['Items']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * List invalidations
     */
    public function listInvalidations($maxItems = 100)
    {
        try {
            $result = $this->cloudFrontClient->listInvalidations([
                'DistributionId' => $this->distributionId,
                'MaxItems' => $maxItems
            ]);
            
            $invalidations = [];
            foreach ($result['InvalidationList']['Items'] as $invalidation) {
                $invalidations[] = [
                    'id' => $invalidation['Id'],
                    'status' => $invalidation['Status'],
                    'create_time' => $invalidation['CreateTime'],
                    'paths_count' => $invalidation['InvalidationBatch']['Paths']['Quantity']
                ];
            }
            
            return [
                'success' => true,
                'invalidations' => $invalidations,
                'is_truncated' => $result['InvalidationList']['IsTruncated'],
                'next_marker' => $result['InvalidationList']['NextMarker'] ?? null
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get distribution info
     */
    public function getDistributionInfo()
    {
        try {
            $result = $this->cloudFrontClient->getDistribution([
                'Id' => $this->distributionId
            ]);
            
            $distribution = $result['Distribution'];
            $config = $distribution['DistributionConfig'];
            
            return [
                'success' => true,
                'id' => $distribution['Id'],
                'status' => $distribution['Status'],
                'domain_name' => $config['DomainName'],
                'enabled' => $config['Enabled'],
                'default_root_object' => $config['DefaultRootObject'],
                'origins' => $config['Origins']['Items'],
                'cache_behaviors' => $config['CacheBehaviors']['Items'] ?? [],
                'default_cache_behavior' => $config['DefaultCacheBehavior'],
                'price_class' => $config['PriceClass']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update distribution
     */
    public function updateDistribution($config)
    {
        try {
            // Get current distribution config
            $currentResult = $this->cloudFrontClient->getDistribution([
                'Id' => $this->distributionId
            ]);
            
            $currentConfig = $currentResult['Distribution']['DistributionConfig'];
            
            // Update config with new values
            $updatedConfig = array_merge($currentConfig, $config);
            
            $result = $this->cloudFrontClient->updateDistribution([
                'Id' => $this->distributionId,
                'DistributionConfig' => $updatedConfig,
                'IfMatch' => $currentResult['ETag']
            ]);
            
            return [
                'success' => true,
                'distribution_id' => $result['Distribution']['Id'],
                'status' => $result['Distribution']['Status']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create signed URL for private content
     */
    public function createSignedUrl($resource, $expiration = 3600, $ipAddress = null)
    {
        try {
            $cloudFront = new \Aws\CloudFront\CloudFrontClient($this->config);
            
            $signedUrlOptions = [
                'url' => $resource,
                'expires' => time() + $expiration,
                'private_key' => config('cloud.aws.cloudfront.private_key'),
                'key_pair_id' => config('cloud.aws.cloudfront.key_pair_id')
            ];
            
            if ($ipAddress) {
                $signedUrlOptions['ip_address'] = $ipAddress;
            }
            
            return $cloudFront->getSignedUrl($signedUrlOptions);
        } catch (AwsException $e) {
            return false;
        }
    }
    
    /**
     * Create signed cookies for private content
     */
    public function createSignedCookies($resource, $expiration = 3600, $ipAddress = null)
    {
        try {
            $cloudFront = new \Aws\CloudFront\CloudFrontClient($this->config);
            
            $signedCookieOptions = [
                'url' => $resource,
                'expires' => time() + $expiration,
                'private_key' => config('cloud.aws.cloudfront.private_key'),
                'key_pair_id' => config('cloud.aws.cloudfront.key_pair_id')
            ];
            
            if ($ipAddress) {
                $signedCookieOptions['ip_address'] = $ipAddress;
            }
            
            return $cloudFront->getSignedCookie($signedCookieOptions);
        } catch (AwsException $e) {
            return false;
        }
    }
}
