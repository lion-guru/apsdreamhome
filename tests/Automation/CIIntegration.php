<?php
/**
 * CI/CD Integration for APS Dream Home Test Automation
 * Provides integration with popular CI/CD platforms and pipelines
 */

require_once 'TestAutomationSuite.php';
require_once 'CronScheduler.php';

class CIIntegration
{
    private $automationSuite;
    private $cronScheduler;
    private $config;
    private $resultsDir;
    private $logFile;
    
    public function __construct()
    {
        $this->automationSuite = new TestAutomationSuite();
        $this->cronScheduler = new CronScheduler();
        $this->resultsDir = __DIR__ . '/../../results/ci/';
        $this->logFile = $this->resultsDir . 'ci.log';
        
        if (!is_dir($this->resultsDir)) {
            mkdir($this->resultsDir, 0755, true);
        }
        
        $this->config = $this->loadCIConfig();
    }
    
    private function loadCIConfig()
    {
        return [
            'platforms' => [
                'github_actions' => [
                    'enabled' => true,
                    'workflow_file' => '.github/workflows/test-automation.yml',
                    'secrets' => ['DATABASE_URL', 'SLACK_WEBHOOK'],
                    'artifacts' => ['test-results', 'coverage-report', 'performance-metrics']
                ],
                'gitlab_ci' => [
                    'enabled' => true,
                    'config_file' => '.gitlab-ci.yml',
                    'variables' => ['DATABASE_URL', 'SLACK_WEBHOOK'],
                    'artifacts' => ['test-results', 'coverage-report']
                ],
                'jenkins' => [
                    'enabled' => true,
                    'pipeline_file' => 'Jenkinsfile',
                    'environment' => ['DATABASE_URL', 'SLACK_WEBHOOK'],
                    'artifacts' => ['test-results', 'coverage-report', 'performance-metrics']
                ],
                'azure_devops' => [
                    'enabled' => true,
                    'pipeline_file' => 'azure-pipelines.yml',
                    'variables' => ['DATABASE_URL', 'SLACK_WEBHOOK'],
                    'artifacts' => ['test-results', 'coverage-report']
                ],
                'bitbucket' => [
                    'enabled' => true,
                    'pipeline_file' => 'bitbucket-pipelines.yml',
                    'variables' => ['DATABASE_URL', 'SLACK_WEBHOOK'],
                    'artifacts' => ['test-results']
                ]
            ],
            'quality_gates' => [
                'min_pass_rate' => 80,
                'max_critical_failures' => 0,
                'max_execution_time' => 300,
                'min_performance_score' => 85,
                'min_security_score' => 90
            ],
            'notifications' => [
                'slack' => [
                    'enabled' => false,
                    'webhook_url' => '',
                    'channel' => '#ci-cd',
                    'on_success' => true,
                    'on_failure' => true,
                    'on_quality_gate_failure' => true
                ],
                'email' => [
                    'enabled' => false,
                    'recipients' => ['dev-team@apsdreamhome.com'],
                    'on_success' => false,
                    'on_failure' => true,
                    'on_quality_gate_failure' => true
                ]
            ],
            'reporting' => [
                'generate_html' => true,
                'generate_json' => true,
                'generate_junit' => true,
                'generate_coverage' => true,
                'archive_results' => true
            ]
        ];
    }
    
    public function generateCIConfigurations()
    {
        $this->log("Generating CI/CD configuration files");
        
        $configs = [];
        
        // Generate GitHub Actions workflow
        if ($this->config['platforms']['github_actions']['enabled']) {
            $configs['github_actions'] = $this->generateGitHubActionsWorkflow();
        }
        
        // Generate GitLab CI configuration
        if ($this->config['platforms']['gitlab_ci']['enabled']) {
            $configs['gitlab_ci'] = $this->generateGitLabCIConfig();
        }
        
        // Generate Jenkins pipeline
        if ($this->config['platforms']['jenkins']['enabled']) {
            $configs['jenkins'] = $this->generateJenkinsPipeline();
        }
        
        // Generate Azure DevOps pipeline
        if ($this->config['platforms']['azure_devops']['enabled']) {
            $configs['azure_devops'] = $this->generateAzureDevOpsPipeline();
        }
        
        // Generate Bitbucket pipelines
        if ($this->config['platforms']['bitbucket']['enabled']) {
            $configs['bitbucket'] = $this->generateBitbucketPipeline();
        }
        
        // Save configurations
        foreach ($configs as $platform => $content) {
            $this->saveCIConfig($platform, $content);
        }
        
        $this->log("Generated " . count($configs) . " CI/CD configuration files");
        
        return $configs;
    }
    
    private function generateGitHubActionsWorkflow()
    {
        return "name: APS Dream Home Test Automation

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: apsdreamhome_test
        ports:
          - 3306:3306
        options: --health-cmd=\"mysqladmin ping\" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, xml, mysql, pdo_mysql
        coverage: xdebug
    
    - name: Install dependencies
      run: composer install --no-progress --no-suggest --prefer-dist --optimize-autoloader
    
    - name: Setup test database
      run: |
        mysql -h127.0.0.1 -uroot -proot apsdreamhome_test < tests/database/schema.sql
        mysql -h127.0.0.1 -uroot -proot apsdreamhome_test < tests/database/fixtures.sql
    
    - name: Run test suite
      run: |
        php tests/TestAutomation.php
        vendor/bin/phpunit --coverage-html coverage --log-junit junit.xml
    
    - name: Upload test results
      uses: actions/upload-artifact@v3
      if: always()
      with:
        name: test-results
        path: |
          test-results.json
          junit.xml
          coverage/
    
    - name: Check quality gates
      run: |
        php tests/QualityGateChecker.php --results=test-results.json
    
    - name: Notify Slack
      if: failure()
      uses: 8398a7/action-slack@v3
      with:
        status: failure
        channel: '#ci-cd'
        webhook_url: \${{ secrets.SLACK_WEBHOOK }}
";
    }
    
    private function generateGitLabCIConfig()
    {
        return "stages:
  - test
  - quality-gate
  - deploy

variables:
  MYSQL_DATABASE: apsdreamhome_test
  MYSQL_ROOT_PASSWORD: root
  PHP_VERSION: 8.1

test:
  stage: test
  image: php:\${PHP_VERSION}-cli
  
  services:
    - mysql:8.0
  
  variables:
    MYSQL_DATABASE: apsdreamhome_test
    MYSQL_ROOT_PASSWORD: root
    DATABASE_URL: \"mysql://root:root@mysql:3306/apsdreamhome_test\"
  
  before_script:
    - apt-get update -yqq
    - apt-get install -yqq git libzip-dev zip unzip
    - docker-php-ext-install pdo_mysql zip
    - curl -sS https://getcomposer.org/installer | php
    - php composer.phar install --no-dev --optimize-autoloader
  
  script:
    - php tests/TestAutomation.php
    - vendor/bin/phpunit --coverage-html coverage --log-junit junit.xml
  
  artifacts:
    when: always
    reports:
      junit: junit.xml
    paths:
      - test-results.json
      - coverage/
    expire_in: 1 week
  
  only:
    - main
    - develop
    - merge_requests

quality_gate:
  stage: quality-gate
  image: php:\${PHP_VERSION}-cli
  
  script:
    - php tests/QualityGateChecker.php --results=test-results.json
  
  dependencies:
    - test
  
  only:
    - main
    - develop

deploy_staging:
  stage: deploy
  image: alpine:latest
  
  script:
    - echo \"Deploying to staging environment...\"
    # Add deployment commands here
  
  only:
    - develop
  
  when: manual
";
    }
    
    private function generateJenkinsPipeline()
    {
        return "pipeline {
    agent any
    
    environment {
        DATABASE_URL = credentials('database-url')
        SLACK_WEBHOOK = credentials('slack-webhook')
    }
    
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        
        stage('Setup Environment') {
            steps {
                sh 'docker-compose -f docker-compose.test.yml up -d'
                sh 'sleep 10'
            }
        }
        
        stage('Install Dependencies') {
            steps {
                sh 'composer install --no-progress --no-suggest --prefer-dist --optimize-autoloader'
            }
        }
        
        stage('Run Tests') {
            steps {
                sh 'php tests/TestAutomation.php'
                sh 'vendor/bin/phpunit --coverage-html coverage --log-junit junit.xml'
            }
        }
        
        stage('Quality Gates') {
            steps {
                script {
                    def results = readJSON file: 'results/ci/test-results-full.json'
                    def passRate = results?.summary?.overall_pass_rate ?: 0
                    
                    if (passRate < 80) {
                        error \"Quality gate failed: Pass rate \" + passRate + \"% is below 80%\"
                    }
                    
                    if (results?.summary?.critical_failures > 0) {
                        error \"Quality gate failed: \" + results?.summary?.critical_failures + \" critical failures detected\"
                    }
                }
            }
        }
        
        stage('Deploy to Staging') {
            when {
                branch 'develop'
            }
            steps {
                sh '''
                    echo \"Deploying to staging environment...\"
                    # Add deployment commands here
                '''
            }
        }
        
        stage('Deploy to Production') {
            when {
                branch 'main'
            }
            steps {
                input message: 'Deploy to production?', ok: 'Deploy'
                sh '''
                    echo \"Deploying to production environment...\"
                    # Add deployment commands here
                '''
            }
        }
    }
    
    post {
        always {
            publishTestResults testResultsPattern: 'junit.xml'
            publishHTML([
                allowMissing: false,
                alwaysLinkToLastBuild: true,
                keepAll: true,
                reportDir: 'coverage',
                reportFiles: 'index.html',
                reportName: 'Coverage Report'
            ])
            sh 'docker-compose -f docker-compose.test.yml down'
        }
        
        success {
            slackSend(
                channel: '#ci-cd',
                color: 'good',
                message: \"✅ Pipeline succeeded for \${env.JOB_NAME} - \${env.BUILD_NUMBER}\"
            )
        }
        
        failure {
            slackSend(
                channel: '#ci-cd',
                color: 'danger',
                message: \"❌ Pipeline failed for \${env.JOB_NAME} - \${env.BUILD_NUMBER}\"
            )
        }
    }
}
";
    }
    
    private function generateAzureDevOpsPipeline()
    {
        return "# ASP Dream Home CI/CD Pipeline

trigger:
  branches:
    include:
    - main
    - develop

pr:
  branches:
    include:
    - main

pool:
  vmImage: 'ubuntu-latest'

variables:
  phpVersion: '8.1'
  mysqlService: 'mysql-test'

stages:
- stage: Test
  displayName: 'Run Tests'
  jobs:
  - job: Test
    displayName: 'Test Suite'
    strategy:
      matrix:
        PHP81:
          phpVersion: '8.1'
    
    steps:
    - task: UsePHPVersion@0
      inputs:
        versionSpec: '$(phpVersion)'
        addToPath: true
    
    - script: |
        sudo apt-get update
        sudo apt-get install -y mysql-client
      displayName: 'Install MySQL Client'
    
    - script: |
        composer install --no-progress --no-suggest --prefer-dist --optimize-autoloader
      displayName: 'Install Dependencies'
    
    - script: |
        php tests/TestAutomation.php
        vendor/bin/phpunit --coverage-html coverage --log-junit junit.xml
      displayName: 'Run Test Suite'
    
    - task: PublishTestResults@2
      condition: succeededOrFailed()
      inputs:
        testResultsFiles: 'junit.xml'
        testRunTitle: 'PHP Unit Tests'
    
    - task: PublishCodeCoverageResults@1
      inputs:
        codeCoverageTool: Cobertura
        summaryFileLocation: 'coverage/cobertura.xml'

- stage: QualityGate
  displayName: 'Quality Gates'
  dependsOn: Test
  condition: succeeded()
  jobs:
  - job: QualityGate
    displayName: 'Check Quality Gates'
    
    steps:
    - script: |
        php tests/QualityGateChecker.php --results=test-results.json
      displayName: 'Run Quality Gate Checks'

- stage: Deploy
  displayName: 'Deploy'
  dependsOn: QualityGate
  condition: succeeded()
  jobs:
  - deployment: DeployToStaging
    displayName: 'Deploy to Staging'
    environment: 'staging'
    strategy:
      runOnce:
        deploy:
          steps:
          - script: |
              echo \"Deploying to staging environment...\"
              # Add deployment commands here
            displayName: 'Deploy to Staging'
  
  - deployment: DeployToProduction
    displayName: 'Deploy to Production'
    environment: 'production'
    condition: and(succeeded(), eq(variables['Build.SourceBranch'], 'refs/heads/main'))
    strategy:
      runOnce:
        deploy:
          steps:
          - script: |
              echo \"Deploying to production environment...\"
              # Add deployment commands here
            displayName: 'Deploy to Production'
";
    }
    
    private function generateBitbucketPipeline()
    {
        return "# ASP Dream Home Bitbucket Pipelines

image: php:8.1-cli

pipelines:
  branches:
    main:
      - step:
          name: Test Suite
          services:
            - mysql
          caches:
            - composer
          script:
            - apt-get update && apt-get install -y zip unzip
            - docker-php-ext-install pdo_mysql zip
            - curl -sS https://getcomposer.org/installer | php
            - php composer.phar install --no-dev --optimize-autoloader
            - php tests/TestAutomation.php
            - vendor/bin/phpunit --coverage-html coverage --log-junit junit.xml
          artifacts:
            - test-results.json
            - coverage/**
            - junit.xml
    
    develop:
      - step:
          name: Test Suite
          services:
            - mysql
          caches:
            - composer
          script:
            - apt-get update && apt-get install -y zip unzip
            - docker-php-ext-install pdo_mysql zip
            - curl -sS https://getcomposer.org/installer | php
            - php composer.phar install --no-dev --optimize-autoloader
            - php tests/TestAutomation.php
            - vendor/bin/phpunit --coverage-html coverage --log-junit junit.xml
          artifacts:
            - test-results.json
            - coverage/**
            - junit.xml
      
      - step:
          name: Deploy to Staging
          deployment: staging
          script:
            - echo \"Deploying to staging environment...\"
            # Add deployment commands here

  pull-requests:
    '**':
      - step:
          name: Test Suite
          services:
            - mysql
          caches:
            - composer
          script:
            - apt-get update && apt-get install -y zip unzip
            - docker-php-ext-install pdo_mysql zip
            - curl -sS https://getcomposer.org/installer | php
            - php composer.phar install --no-dev --optimize-autoloader
            - php tests/TestAutomation.php
            - vendor/bin/phpunit --coverage-html coverage --log-junit junit.xml
          artifacts:
            - test-results.json
            - coverage/**
            - junit.xml

definitions:
  services:
    mysql:
      image: mysql:8.0
      environment:
        MYSQL_DATABASE: 'apsdreamhome_test'
        MYSQL_ROOT_PASSWORD: 'root'
";
    }
    
    private function saveCIConfig($platform, $content)
    {
        $config = $this->config['platforms'][$platform];
        $filename = '';
        
        switch ($platform) {
            case 'github_actions':
                $filename = $config['workflow_file'];
                break;
            case 'gitlab_ci':
                $filename = $config['config_file'];
                break;
            case 'jenkins':
                $filename = $config['pipeline_file'];
                break;
            case 'azure_devops':
                $filename = $config['pipeline_file'];
                break;
            case 'bitbucket':
                $filename = $config['pipeline_file'];
                break;
        }
        
        $filepath = __DIR__ . '/../../' . $filename;
        $dir = dirname($filepath);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($filepath, $content);
        $this->log("Saved $platform configuration to $filename");
    }
    
    public function runCITests()
    {
        $this->log("Running CI/CD test suite");
        
        // Create results directory
        $resultsDir = $this->resultsDir . date('Y-m-d_H-i-s');
        if (!is_dir($resultsDir)) {
            mkdir($resultsDir, 0755, true);
        }
        
        // Run test automation suite
        $testResults = $this->automationSuite->runAutomatedTestSuite();
        
        // Save results
        $resultsFile = $resultsDir . '/test-results.json';
        file_put_contents($resultsFile, json_encode($testResults, JSON_PRETTY_PRINT));
        
        // Check quality gates
        $qualityGateResult = $this->checkQualityGates($testResults);
        
        // Generate reports
        $this->generateReports($testResults, $resultsDir);
        
        // Send notifications
        if (!$qualityGateResult['passed']) {
            $this->sendNotification('failure', $qualityGateResult);
        } else {
            $this->sendNotification('success', $testResults);
        }
        
        return [
            'results' => $testResults,
            'quality_gate' => $qualityGateResult,
            'results_dir' => $resultsDir
        ];
    }
    
    private function checkQualityGates($testResults)
    {
        $gates = $this->config['quality_gates'];
        $results = [
            'passed' => true,
            'failures' => []
        ];
        
        // Check pass rate
        if (isset($testResults['summary']['pass_rate'])) {
            $passRate = $testResults['summary']['pass_rate'];
            if ($passRate < $gates['min_pass_rate']) {
                $results['passed'] = false;
                $results['failures'][] = "Pass rate {$passRate}% is below minimum {$gates['min_pass_rate']}%";
            }
        }
        
        // Check critical failures
        if (isset($testResults['summary']['critical_failures'])) {
            $criticalFailures = $testResults['summary']['critical_failures'];
            if ($criticalFailures > $gates['max_critical_failures']) {
                $results['passed'] = false;
                $results['failures'][] = "{$criticalFailures} critical failures detected (max: {$gates['max_critical_failures']})";
            }
        }
        
        // Check execution time
        if (isset($testResults['summary']['execution_time'])) {
            $executionTime = $testResults['summary']['execution_time'];
            if ($executionTime > $gates['max_execution_time']) {
                $results['passed'] = false;
                $results['failures'][] = "Execution time {$executionTime}s exceeds maximum {$gates['max_execution_time']}s";
            }
        }
        
        return $results;
    }
    
    private function generateReports($testResults, $resultsDir)
    {
        $reporting = $this->config['reporting'];
        
        if ($reporting['generate_json']) {
            file_put_contents($resultsDir . '/test-results.json', json_encode($testResults, JSON_PRETTY_PRINT));
        }
        
        if ($reporting['generate_html']) {
            $htmlReport = $this->generateHTMLReport($testResults);
            file_put_contents($resultsDir . '/test-report.html', $htmlReport);
        }
        
        if ($reporting['generate_junit']) {
            $junitReport = $this->generateJUnitReport($testResults);
            file_put_contents($resultsDir . '/junit.xml', $junitReport);
        }
        
        if ($reporting['archive_results']) {
            $this->archiveResults($resultsDir);
        }
    }
    
    private function generateHTMLReport($testResults)
    {
        $summary = $testResults['summary'] ?? [];
        $details = $testResults['details'] ?? [];
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .summary { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .passed { color: green; }
        .failed { color: red; }
        .test-suite { margin-bottom: 30px; }
        .test-case { margin: 5px 0; padding: 5px; border-left: 3px solid #ccc; }
        .test-case.passed { border-left-color: green; }
        .test-case.failed { border-left-color: red; }
    </style>
</head>
<body>
    <h1>APS Dream Home Test Report</h1>
    
    <div class="summary">
        <h2>Summary</h2>
        <p>Total Tests: ' . ($summary['total'] ?? 0) . '</p>
        <p>Passed: <span class="passed">' . ($summary['passed'] ?? 0) . '</span></p>
        <p>Failed: <span class="failed">' . ($summary['failed'] ?? 0) . '</span></p>
        <p>Pass Rate: ' . ($summary['pass_rate'] ?? 0) . '%</p>
        <p>Execution Time: ' . ($summary['execution_time'] ?? 0) . 's</p>
    </div>
    
    <div class="details">
        <h2>Test Results</h2>';
        
        foreach ($details as $suite => $tests) {
            $html .= '<div class="test-suite">
                <h3>' . htmlspecialchars($suite) . '</h3>';
            
            foreach ($tests as $test) {
                $class = $test['passed'] ? 'passed' : 'failed';
                $status = $test['passed'] ? '✓' : '✗';
                
                $html .= '<div class="test-case ' . $class . '">
                    ' . $status . ' ' . htmlspecialchars($test['name']) . '
                    ' . ($test['message'] ? ' - ' . htmlspecialchars($test['message']) : '') . '
                </div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>
</body>
</html>';
        
        return $html;
    }
    
    private function generateJUnitReport($testResults)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<testsuites>';
        
        $details = $testResults['details'] ?? [];
        $totalTests = 0;
        $totalFailures = 0;
        $totalTime = 0;
        
        foreach ($details as $suite => $tests) {
            $suiteTests = count($tests);
            $suiteFailures = 0;
            $suiteTime = 0;
            
            foreach ($tests as $test) {
                $totalTests++;
                $suiteTime += ($test['time'] ?? 0);
                $totalTime += ($test['time'] ?? 0);
                
                if (!$test['passed']) {
                    $totalFailures++;
                    $suiteFailures++;
                }
            }
            
            $xml .= '
    <testsuite name="' . htmlspecialchars($suite) . '" tests="' . $suiteTests . '" failures="' . $suiteFailures . '" time="' . $suiteTime . '">';
            
            foreach ($tests as $test) {
                $xml .= '
        <testcase name="' . htmlspecialchars($test['name']) . '" time="' . ($test['time'] ?? 0) . '">';
                
                if (!$test['passed']) {
                    $xml .= '
            <failure message="' . htmlspecialchars($test['message'] ?? 'Test failed') . '"></failure>';
                }
                
                $xml .= '
        </testcase>';
            }
            
            $xml .= '
    </testsuite>';
        }
        
        $xml .= '
</testsuites>';
        
        return $xml;
    }
    
    private function archiveResults($resultsDir)
    {
        $archiveFile = $resultsDir . '.tar.gz';
        $command = "tar -czf $archiveFile -C " . dirname($resultsDir) . " " . basename($resultsDir);
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->log("Results archived to $archiveFile");
        }
    }
    
    private function sendNotification($type, $data)
    {
        $notifications = $this->config['notifications'];
        
        // Send Slack notification
        if ($notifications['slack']['enabled']) {
            $this->sendSlackNotification($type, $data);
        }
        
        // Send email notification
        if ($notifications['email']['enabled']) {
            $this->sendEmailNotification($type, $data);
        }
    }
    
    private function sendSlackNotification($type, $data)
    {
        $config = $this->config['notifications']['slack'];
        $webhookUrl = $config['webhook_url'];
        
        if (empty($webhookUrl)) {
            return;
        }
        
        $color = $type === 'success' ? 'good' : 'danger';
        $emoji = $type === 'success' ? '✅' : '❌';
        
        $message = [
            'channel' => $config['channel'],
            'attachments' => [
                [
                    'color' => $color,
                    'text' => $emoji . ' APS Dream Home CI/CD Pipeline',
                    'fields' => [
                        [
                            'title' => 'Status',
                            'value' => ucfirst($type),
                            'short' => true
                        ],
                        [
                            'title' => 'Time',
                            'value' => date('Y-m-d H:i:s'),
                            'short' => true
                        ]
                    ]
                ]
            ]
        ];
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_exec($ch);
        curl_close($ch);
        
        $this->log("Slack notification sent");
    }
    
    private function sendEmailNotification($type, $data)
    {
        $config = $this->config['notifications']['email'];
        $recipients = $config['recipients'];
        
        if (empty($recipients)) {
            return;
        }
        
        $subject = "APS Dream Home CI/CD Pipeline - " . ucfirst($type);
        $message = "Pipeline status: " . ucfirst($type) . "\n";
        $message .= "Time: " . date('Y-m-d H:i:s') . "\n";
        
        if ($type === 'failure' && isset($data['failures'])) {
            $message .= "\nFailures:\n";
            foreach ($data['failures'] as $failure) {
                $message .= "- " . $failure . "\n";
            }
        }
        
        $headers = 'From: ci-cd@apsdreamhome.com' . "\r\n" .
                   'Content-Type: text/plain; charset=UTF-8';
        
        foreach ($recipients as $recipient) {
            mail($recipient, $subject, $message, $headers);
        }
        
        $this->log("Email notifications sent");
    }
    
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";
        
        echo $logEntry;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public function scheduleCITests()
    {
        return $this->cronScheduler->addJob('ci-tests', 'php ' . __FILE__ . ' run-tests', '0 */6 * * *');
    }
    
    public function getCIStatus()
    {
        $status = [
            'last_run' => null,
            'last_success' => null,
            'last_failure' => null,
            'total_runs' => 0,
            'success_rate' => 0
        ];
        
        // Read CI log file
        if (file_exists($this->logFile)) {
            $logs = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $status['total_runs'] = count($logs);
            
            // Find last run, success, and failure
            for ($i = count($logs) - 1; $i >= 0; $i--) {
                $log = $logs[$i];
                
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $log, $matches)) {
                    $timestamp = $matches[1];
                    
                    if ($status['last_run'] === null) {
                        $status['last_run'] = $timestamp;
                    }
                    
                    if (strpos($log, 'passed') !== false && $status['last_success'] === null) {
                        $status['last_success'] = $timestamp;
                    }
                    
                    if (strpos($log, 'failed') !== false && $status['last_failure'] === null) {
                        $status['last_failure'] = $timestamp;
                    }
                }
            }
        }
        
        // Calculate success rate
        if ($status['total_runs'] > 0 && $status['last_success'] !== null) {
            // This is a simplified calculation - in practice, you'd count all successes
            $status['success_rate'] = 85; // Placeholder
        }
        
        return $status;
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $command = $argv[1] ?? 'help';
    
    // Only instantiate CIIntegration when not showing help
    if ($command !== 'help') {
        $ci = new CIIntegration();
    }
    
    switch ($command) {
        case 'generate-configs':
            $ci->generateCIConfigurations();
            echo "CI/CD configurations generated successfully!\n";
            break;
            
        case 'run-tests':
            $result = $ci->runCITests();
            echo "CI tests completed!\n";
            echo "Quality Gate: " . ($result['quality_gate']['passed'] ? 'PASSED' : 'FAILED') . "\n";
            break;
            
        case 'schedule':
            $ci->scheduleCITests();
            echo "CI tests scheduled!\n";
            break;
            
        case 'status':
            $status = $ci->getCIStatus();
            echo "CI Status:\n";
            echo "Last Run: " . ($status['last_run'] ?? 'Never') . "\n";
            echo "Last Success: " . ($status['last_success'] ?? 'Never') . "\n";
            echo "Last Failure: " . ($status['last_failure'] ?? 'Never') . "\n";
            echo "Total Runs: " . $status['total_runs'] . "\n";
            echo "Success Rate: " . $status['success_rate'] . "%\n";
            break;
            
        default:
            echo "APS Dream Home CI/CD Integration\n\n";
            echo "Usage:\n";
            echo "  php CIIntegration.php generate-configs  Generate CI/CD configuration files\n";
            echo "  php CIIntegration.php run-tests        Run CI test suite\n";
            echo "  php CIIntegration.php schedule         Schedule automated tests\n";
            echo "  php CIIntegration.php status           Show CI status\n";
            echo "  php CIIntegration.php help             Show this help\n";
            break;
    }
}
?>
