<?php
namespace App\Services\Cloud;

use Aws\Rds\RdsClient;
use Aws\Exception\AwsException;
use PDO;

class AWSRDSService
{
    private $rdsClient;
    private $dbConnection;
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
        
        $this->initializeRDSClient();
    }
    
    /**
     * Initialize RDS client
     */
    private function initializeRDSClient()
    {
        try {
            $this->rdsClient = new RdsClient($this->config);
        } catch (AwsException $e) {
            throw new Exception('Failed to initialize RDS client: ' . $e->getMessage());
        }
    }
    
    /**
     * Connect to RDS database
     */
    public function connect($dbIdentifier, $username, $password, $database)
    {
        try {
            // Get database endpoint
            $endpoint = $this->getDatabaseEndpoint($dbIdentifier);
            
            if (!$endpoint) {
                throw new Exception('Database endpoint not found');
            }
            
            // Create PDO connection
            $dsn = "mysql:host={$endpoint['address']};port={$endpoint['port']};dbname={$database}";
            
            $this->dbConnection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to connect to RDS: ' . $e->getMessage());
        }
    }
    
    /**
     * Get database endpoint
     */
    public function getDatabaseEndpoint($dbIdentifier)
    {
        try {
            $result = $this->rdsClient->describeDBInstances([
                'DBInstanceIdentifier' => $dbIdentifier
            ]);
            
            $instances = $result['DBInstances'];
            
            if (empty($instances)) {
                return null;
            }
            
            $instance = $instances[0];
            
            return [
                'address' => $instance['Endpoint']['Address'],
                'port' => $instance['Endpoint']['Port'],
                'hosted_zone_id' => $instance['Endpoint']['HostedZoneId']
            ];
        } catch (AwsException $e) {
            return null;
        }
    }
    
    /**
     * Create database snapshot
     */
    public function createSnapshot($dbIdentifier, $snapshotIdentifier)
    {
        try {
            $result = $this->rdsClient->createDBSnapshot([
                'DBInstanceIdentifier' => $dbIdentifier,
                'DBSnapshotIdentifier' => $snapshotIdentifier
            ]);
            
            return [
                'success' => true,
                'snapshot_id' => $result['DBSnapshot']['DBSnapshotIdentifier'],
                'status' => $result['DBSnapshot']['Status'],
                'create_time' => $result['DBSnapshot']['SnapshotCreateTime']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get snapshot status
     */
    public function getSnapshotStatus($snapshotIdentifier)
    {
        try {
            $result = $this->rdsClient->describeDBSnapshots([
                'DBSnapshotIdentifier' => $snapshotIdentifier
            ]);
            
            $snapshots = $result['DBSnapshots'];
            
            if (empty($snapshots)) {
                return null;
            }
            
            $snapshot = $snapshots[0];
            
            return [
                'snapshot_id' => $snapshot['DBSnapshotIdentifier'],
                'status' => $snapshot['Status'],
                'create_time' => $snapshot['SnapshotCreateTime'],
                'engine' => $snapshot['Engine'],
                'engine_version' => $snapshot['EngineVersion'],
                'snapshot_type' => $snapshot['SnapshotType'],
                'allocated_storage' => $snapshot['AllocatedStorage'],
                'storage_type' => $snapshot['StorageType']
            ];
        } catch (AwsException $e) {
            return null;
        }
    }
    
    /**
     * List snapshots
     */
    public function listSnapshots($dbIdentifier = null, $maxRecords = 100)
    {
        try {
            $params = [
                'MaxRecords' => $maxRecords
            ];
            
            if ($dbIdentifier) {
                $params['DBInstanceIdentifier'] = $dbIdentifier;
            }
            
            $result = $this->rdsClient->describeDBSnapshots($params);
            
            $snapshots = [];
            foreach ($result['DBSnapshots'] as $snapshot) {
                $snapshots[] = [
                    'snapshot_id' => $snapshot['DBSnapshotIdentifier'],
                    'db_instance' => $snapshot['DBInstanceIdentifier'],
                    'status' => $snapshot['Status'],
                    'create_time' => $snapshot['SnapshotCreateTime'],
                    'engine' => $snapshot['Engine'],
                    'allocated_storage' => $snapshot['AllocatedStorage'],
                    'snapshot_type' => $snapshot['SnapshotType']
                ];
            }
            
            return [
                'success' => true,
                'snapshots' => $snapshots,
                'marker' => $result['Marker'] ?? null
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete snapshot
     */
    public function deleteSnapshot($snapshotIdentifier)
    {
        try {
            $result = $this->rdsClient->deleteDBSnapshot([
                'DBSnapshotIdentifier' => $snapshotIdentifier
            ]);
            
            return [
                'success' => true,
                'snapshot_id' => $result['DBSnapshot']['DBSnapshotIdentifier'],
                'status' => $result['DBSnapshot']['Status']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Restore database from snapshot
     */
    public function restoreFromSnapshot($snapshotIdentifier, $newDbIdentifier, $options = [])
    {
        try {
            $params = [
                'DBSnapshotIdentifier' => $snapshotIdentifier,
                'DBInstanceIdentifier' => $newDbIdentifier,
                'DBInstanceClass' => $options['instance_class'] ?? 'db.t3.micro',
                'Engine' => $options['engine'] ?? 'mysql',
                'MultiAZ' => $options['multi_az'] ?? false,
                'PubliclyAccessible' => $options['publicly_accessible'] ?? false,
                'StorageType' => $options['storage_type'] ?? 'gp2',
                'AllocatedStorage' => $options['allocated_storage'] ?? 20
            ];
            
            if (isset($options['subnet_group_name'])) {
                $params['DBSubnetGroupName'] = $options['subnet_group_name'];
            }
            
            if (isset($options['security_group_ids'])) {
                $params['VpcSecurityGroupIds'] = $options['security_group_ids'];
            }
            
            $result = $this->rdsClient->restoreDBInstanceFromDBSnapshot($params);
            
            return [
                'success' => true,
                'db_instance_id' => $result['DBInstance']['DBInstanceIdentifier'],
                'status' => $result['DBInstance']['DBInstanceStatus']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get database metrics
     */
    public function getDatabaseMetrics($dbIdentifier, $metricName, $startTime, $endTime)
    {
        try {
            $cloudWatchClient = new \Aws\CloudWatch\CloudWatchClient($this->config);
            
            $result = $cloudWatchClient->getMetricStatistics([
                'Namespace' => 'AWS/RDS',
                'MetricName' => $metricName,
                'Dimensions' => [
                    [
                        'Name' => 'DBInstanceIdentifier',
                        'Value' => $dbIdentifier
                    ]
                ],
                'StartTime' => $startTime,
                'EndTime' => $endTime,
                'Period' => 300, // 5 minutes
                'Statistics' => ['Average', 'Maximum', 'Minimum']
            ]);
            
            $datapoints = [];
            foreach ($result['Datapoints'] as $datapoint) {
                $datapoints[] = [
                    'timestamp' => $datapoint['Timestamp'],
                    'average' => $datapoint['Average'],
                    'maximum' => $datapoint['Maximum'],
                    'minimum' => $datapoint['Minimum'],
                    'unit' => $datapoint['Unit']
                ];
            }
            
            return [
                'success' => true,
                'datapoints' => $datapoints,
                'label' => $result['Label']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute query
     */
    public function query($sql, $params = [])
    {
        if (!$this->dbConnection) {
            throw new Exception('Database connection not established');
        }
        
        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Query failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Execute non-query statement
     */
    public function execute($sql, $params = [])
    {
        if (!$this->dbConnection) {
            throw new Exception('Database connection not established');
        }
        
        try {
            $stmt = $this->dbConnection->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            throw new Exception('Execution failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        if (!$this->dbConnection) {
            throw new Exception('Database connection not established');
        }
        
        return $this->dbConnection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit()
    {
        if (!$this->dbConnection) {
            throw new Exception('Database connection not established');
        }
        
        return $this->dbConnection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback()
    {
        if (!$this->dbConnection) {
            throw new Exception('Database connection not established');
        }
        
        return $this->dbConnection->rollback();
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId()
    {
        if (!$this->dbConnection) {
            throw new Exception('Database connection not established');
        }
        
        return $this->dbConnection->lastInsertId();
    }
}
