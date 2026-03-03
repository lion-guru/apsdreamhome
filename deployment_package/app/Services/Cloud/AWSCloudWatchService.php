<?php
namespace App\Services\Cloud;

use Aws\CloudWatch\CloudWatchClient;
use Aws\Exception\AwsException;

class AWSCloudWatchService
{
    private $cloudWatchClient;
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
        
        $this->initializeCloudWatchClient();
    }
    
    /**
     * Initialize CloudWatch client
     */
    private function initializeCloudWatchClient()
    {
        try {
            $this->cloudWatchClient = new CloudWatchClient($this->config);
        } catch (AwsException $e) {
            throw new Exception('Failed to initialize CloudWatch client: ' . $e->getMessage());
        }
    }
    
    /**
     * Put custom metric
     */
    public function putMetric($namespace, $metricName, $value, $dimensions = [], $unit = 'None')
    {
        try {
            $result = $this->cloudWatchClient->putMetricData([
                'Namespace' => $namespace,
                'MetricData' => [
                    [
                        'MetricName' => $metricName,
                        'Value' => $value,
                        'Unit' => $unit,
                        'Dimensions' => $this->formatDimensions($dimensions),
                        'Timestamp' => time()
                    ]
                ]
            ]);
            
            return [
                'success' => true,
                'message' => 'Metric published successfully'
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get metric statistics
     */
    public function getMetricStatistics($namespace, $metricName, $dimensions, $startTime, $endTime, $period = 300, $statistics = ['Average'])
    {
        try {
            $result = $this->cloudWatchClient->getMetricStatistics([
                'Namespace' => $namespace,
                'MetricName' => $metricName,
                'Dimensions' => $this->formatDimensions($dimensions),
                'StartTime' => $startTime,
                'EndTime' => $endTime,
                'Period' => $period,
                'Statistics' => $statistics
            ]);
            
            $datapoints = [];
            foreach ($result['Datapoints'] as $datapoint) {
                $datapoints[] = [
                    'timestamp' => $datapoint['Timestamp'],
                    'value' => $datapoint['Average'] ?? $datapoint['Sum'] ?? $datapoint['Maximum'] ?? $datapoint['Minimum'],
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
     * Create alarm
     */
    public function createAlarm($alarmName, $namespace, $metricName, $dimensions, $threshold, $comparisonOperator, $evaluationPeriods, $period, $statistic, $alarmActions = [])
    {
        try {
            $params = [
                'AlarmName' => $alarmName,
                'AlarmDescription' => "Alarm for {$metricName} when {$comparisonOperator} {$threshold}",
                'Namespace' => $namespace,
                'MetricName' => $metricName,
                'Dimensions' => $this->formatDimensions($dimensions),
                'Threshold' => $threshold,
                'ComparisonOperator' => $comparisonOperator,
                'EvaluationPeriods' => $evaluationPeriods,
                'Period' => $period,
                'Statistic' => $statistic,
                'ActionsEnabled' => true
            ];
            
            if (!empty($alarmActions)) {
                $params['AlarmActions'] = $alarmActions;
            }
            
            $result = $this->cloudWatchClient->putMetricAlarm($params);
            
            return [
                'success' => true,
                'alarm_arn' => $result['AlarmArn']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get alarm status
     */
    public function getAlarmStatus($alarmName)
    {
        try {
            $result = $this->cloudWatchClient->describeAlarms([
                'AlarmNames' => [$alarmName]
            ]);
            
            $alarms = $result['MetricAlarms'];
            
            if (empty($alarms)) {
                return null;
            }
            
            $alarm = $alarms[0];
            
            return [
                'alarm_name' => $alarm['AlarmName'],
                'alarm_arn' => $alarm['AlarmArn'],
                'state_value' => $alarm['StateValue'],
                'state_reason' => $alarm['StateReason'],
                'metric_name' => $alarm['MetricName'],
                'namespace' => $alarm['Namespace'],
                'threshold' => $alarm['Threshold'],
                'comparison_operator' => $alarm['ComparisonOperator'],
                'evaluation_periods' => $alarm['EvaluationPeriods'],
                'period' => $alarm['Period'],
                'statistic' => $alarm['Statistic']
            ];
        } catch (AwsException $e) {
            return null;
        }
    }
    
    /**
     * List alarms
     */
    public function listAlarms($alarmNamePrefix = null, $stateValue = null, $maxRecords = 100)
    {
        try {
            $params = [
                'MaxRecords' => $maxRecords
            ];
            
            if ($alarmNamePrefix) {
                $params['AlarmNamePrefix'] = $alarmNamePrefix;
            }
            
            if ($stateValue) {
                $params['StateValue'] = $stateValue;
            }
            
            $result = $this->cloudWatchClient->describeAlarms($params);
            
            $alarms = [];
            foreach ($result['MetricAlarms'] as $alarm) {
                $alarms[] = [
                    'alarm_name' => $alarm['AlarmName'],
                    'state_value' => $alarm['StateValue'],
                    'state_reason' => $alarm['StateReason'],
                    'metric_name' => $alarm['MetricName'],
                    'namespace' => $alarm['Namespace'],
                    'threshold' => $alarm['Threshold']
                ];
            }
            
            return [
                'success' => true,
                'alarms' => $alarms,
                'next_token' => $result['NextToken'] ?? null
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete alarm
     */
    public function deleteAlarm($alarmName)
    {
        try {
            $this->cloudWatchClient->deleteAlarms([
                'AlarmNames' => [$alarmName]
            ]);
            
            return [
                'success' => true,
                'message' => 'Alarm deleted successfully'
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create log group
     */
    public function createLogGroup($logGroupName, $retentionInDays = 7)
    {
        try {
            $logsClient = new \Aws\CloudWatchLogs\CloudWatchLogsClient($this->config);
            
            $params = [
                'logGroupName' => $logGroupName
            ];
            
            if ($retentionInDays) {
                $params['retentionInDays'] = $retentionInDays;
            }
            
            $logsClient->createLogGroup($params);
            
            return [
                'success' => true,
                'message' => 'Log group created successfully'
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Put log event
     */
    public function putLogEvent($logGroupName, $message, $level = 'INFO')
    {
        try {
            $logsClient = new \Aws\CloudWatchLogs\CloudWatchLogsClient($this->config);
            
            $logEvent = [
                'timestamp' => round(microtime(true) * 1000),
                'message' => "[{$level}] " . $message
            ];
            
            $result = $logsClient->putLogEvents([
                'logGroupName' => $logGroupName,
                'logStreamName' => date('Y/m/d') . '/' . uniqid(),
                'logEvents' => [$logEvent]
            ]);
            
            return [
                'success' => true,
                'next_sequence_token' => $result['nextSequenceToken']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get dashboard
     */
    public function getDashboard($dashboardName)
    {
        try {
            $result = $this->cloudWatchClient->getDashboard([
                'DashboardName' => $dashboardName
            ]);
            
            return [
                'success' => true,
                'dashboard_name' => $result['DashboardName'],
                'dashboard_body' => json_decode($result['DashboardBody'], true),
                'last_modified' => $result['LastModified']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create dashboard
     */
    public function createDashboard($dashboardName, $widgets)
    {
        try {
            $dashboardBody = json_encode([
                'widgets' => $widgets
            ]);
            
            $result = $this->cloudWatchClient->putDashboard([
                'DashboardName' => $dashboardName,
                'DashboardBody' => $dashboardBody
            ]);
            
            return [
                'success' => true,
                'dashboard_arn' => $result['DashboardArn']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Format dimensions for CloudWatch API
     */
    private function formatDimensions($dimensions)
    {
        $formatted = [];
        
        foreach ($dimensions as $name => $value) {
            $formatted[] = [
                'Name' => $name,
                'Value' => $value
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get application metrics
     */
    public function getApplicationMetrics($startTime, $endTime)
    {
        $metrics = [];
        
        // Get response time metrics
        $responseTime = $this->getMetricStatistics(
            'APS-Dream-Home',
            'ResponseTime',
            ['Environment' => 'production'],
            $startTime,
            $endTime,
            300,
            ['Average', 'Maximum']
        );
        
        if ($responseTime['success']) {
            $metrics['response_time'] = $responseTime['datapoints'];
        }
        
        // Get request count metrics
        $requestCount = $this->getMetricStatistics(
            'APS-Dream-Home',
            'RequestCount',
            ['Environment' => 'production'],
            $startTime,
            $endTime,
            300,
            ['Sum']
        );
        
        if ($requestCount['success']) {
            $metrics['request_count'] = $requestCount['datapoints'];
        }
        
        // Get error rate metrics
        $errorRate = $this->getMetricStatistics(
            'APS-Dream-Home',
            'ErrorRate',
            ['Environment' => 'production'],
            $startTime,
            $endTime,
            300,
            ['Average']
        );
        
        if ($errorRate['success']) {
            $metrics['error_rate'] = $errorRate['datapoints'];
        }
        
        return [
            'success' => true,
            'metrics' => $metrics
        ];
    }
}
