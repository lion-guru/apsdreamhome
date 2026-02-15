<?php
/**
 * CI/CD Configuration Validator
 * Validates generated CI configurations for syntax and completeness
 */

class CIConfigValidator
{
    private $resultsDir;
    private $validationResults = [];
    
    public function __construct()
    {
        $this->resultsDir = __DIR__ . '/../../results/ci';
    }
    
    public function validateAllConfigs()
    {
        echo "🔍 Validating CI Configuration Files...\n\n";
        
        $configs = [
            'jenkins' => 'jenkins-config.yml',
            'github' => 'github-config.yml', 
            'gitlab' => 'gitlab-config.yml',
            'azure' => 'azure-config.yml',
            'bitbucket' => 'bitbucket-config.yml'
        ];
        
        $allValid = true;
        
        foreach ($configs as $platform => $filename) {
            echo "🔧 Validating {$platform} configuration...\n";
            
            $result = $this->validateConfig($platform, $filename);
            $this->validationResults[$platform] = $result;
            
            if ($result['valid']) {
                echo "✅ {$platform}: PASSED\n";
            } else {
                echo "❌ {$platform}: FAILED\n";
                foreach ($result['errors'] as $error) {
                    echo "   - {$error}\n";
                }
                $allValid = false;
            }
            echo "\n";
        }
        
        // Validate test results
        echo "📊 Validating test results...\n";
        $testResults = $this->validateTestResults();
        if ($testResults['valid']) {
            echo "✅ Test Results: PASSED\n";
        } else {
            echo "❌ Test Results: FAILED\n";
            foreach ($testResults['errors'] as $error) {
                echo "   - {$error}\n";
            }
            $allValid = false;
        }
        
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📈 Validation Summary:\n";
        echo "Status: " . ($allValid ? "✅ ALL VALIDATIONS PASSED" : "❌ SOME VALIDATIONS FAILED") . "\n";
        
        if (!$allValid) {
            echo "\n⚠️  Fix the above issues before deploying to production.\n";
        } else {
            echo "\n🚀 All configurations are ready for production deployment!\n";
        }
        
        return $allValid;
    }
    
    private function validateConfig($platform, $filename)
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];
        
        $filepath = $this->resultsDir . '/' . $filename;
        
        // Check file exists
        if (!file_exists($filepath)) {
            $result['valid'] = false;
            $result['errors'][] = "Configuration file not found: {$filename}";
            return $result;
        }
        
        $content = file_get_contents($filepath);
        
        // Platform-specific validation
        switch ($platform) {
            case 'jenkins':
                $result = $this->validateJenkinsConfig($content, $result);
                break;
            case 'github':
                $result = $this->validateGitHubConfig($content, $result);
                break;
            case 'gitlab':
                $result = $this->validateGitLabConfig($content, $result);
                break;
            case 'azure':
                $result = $this->validateAzureConfig($content, $result);
                break;
            case 'bitbucket':
                $result = $this->validateBitbucketConfig($content, $result);
                break;
        }
        
        // Common validation checks
        $result = $this->validateCommonConfig($content, $result);
        
        return $result;
    }
    
    private function validateJenkinsConfig($content, $result)
    {
        // Check for required Jenkins pipeline elements
        $required = [
            'pipeline {',
            'agent any',
            'stages {',
            'stage(',
            'steps {',
            'post {'
        ];
        
        foreach ($required as $element) {
            if (strpos($content, $element) === false) {
                $result['valid'] = false;
                $result['errors'][] = "Missing required Jenkins element: {$element}";
            }
        }
        
        // Check for test execution
        if (strpos($content, 'TestAutomationSuite.php') === false) {
            $result['warnings'][] = "Test suite execution not found in pipeline";
        }
        
        // Check for quality gates
        if (strpos($content, 'quality-gates') === false && strpos($content, 'Quality Gates') === false) {
            $result['warnings'][] = "Quality gates stage not found";
        }
        
        return $result;
    }
    
    private function validateGitHubConfig($content, $result)
    {
        // Check for required GitHub Actions elements
        $required = [
            'name:',
            'on:',
            'jobs:',
            'runs-on:',
            'steps:',
            'uses:'
        ];
        
        foreach ($required as $element) {
            if (strpos($content, $element) === false) {
                $result['valid'] = false;
                $result['errors'][] = "Missing required GitHub Actions element: {$element}";
            }
        }
        
        // Check for test job
        if (strpos($content, 'test:') === false) {
            $result['warnings'][] = "Test job not found";
        }
        
        // Check for quality check
        if (strpos($content, 'quality-check') === false) {
            $result['warnings'][] = "Quality check job not found";
        }
        
        return $result;
    }
    
    private function validateGitLabConfig($content, $result)
    {
        // Check for required GitLab CI elements
        $required = [
            'stages:',
            'image:',
            'script:',
            'artifacts:'
        ];
        
        foreach ($required as $element) {
            if (strpos($content, $element) === false) {
                $result['valid'] = false;
                $result['errors'][] = "Missing required GitLab CI element: {$element}";
            }
        }
        
        // Check for test stage
        if (strpos($content, 'stage: test') === false) {
            $result['warnings'][] = "Test stage not found";
        }
        
        return $result;
    }
    
    private function validateAzureConfig($content, $result)
    {
        // Check for required Azure DevOps elements
        $required = [
            'trigger:',
            'pool:',
            'stages:',
            'jobs:',
            'steps:'
        ];
        
        foreach ($required as $element) {
            if (strpos($content, $element) === false) {
                $result['valid'] = false;
                $result['errors'][] = "Missing required Azure DevOps element: {$element}";
            }
        }
        
        // Check for test stage
        if (strpos($content, 'stage: Test') === false) {
            $result['warnings'][] = "Test stage not found";
        }
        
        return $result;
    }
    
    private function validateBitbucketConfig($content, $result)
    {
        // Check for required Bitbucket elements
        $required = [
            'image:',
            'pipelines:',
            'script:'
        ];
        
        foreach ($required as $element) {
            if (strpos($content, $element) === false) {
                $result['valid'] = false;
                $result['errors'][] = "Missing required Bitbucket element: {$element}";
            }
        }
        
        // Check for test step
        if (strpos($content, 'name: Run Tests') === false) {
            $result['warnings'][] = "Test step not clearly identified";
        }
        
        return $result;
    }
    
    private function validateCommonConfig($content, $result)
    {
        // Check for test automation references
        if (strpos($content, 'TestAutomationSuite.php') === false) {
            $result['warnings'][] = "Test automation suite not referenced";
        }
        
        // Check for CI results
        if (strpos($content, 'SimpleCITest.php') === false) {
            $result['warnings'][] = "CI results generation not referenced";
        }
        
        // Check for quality gates
        if (strpos($content, 'quality-gates') === false && strpos($content, 'check-quality-gates') === false) {
            $result['warnings'][] = "Quality gate validation not found";
        }
        
        // Check for deployment stages
        if (strpos($content, 'deploy') === false) {
            $result['warnings'][] = "No deployment stages found";
        }
        
        // Check file size (should not be empty)
        if (empty(trim($content))) {
            $result['valid'] = false;
            $result['errors'][] = "Configuration file is empty";
        }
        
        return $result;
    }
    
    private function validateTestResults()
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];
        
        // Check for test results file
        $testResultsFile = $this->resultsDir . '/test-results-full.json';
        if (!file_exists($testResultsFile)) {
            $result['valid'] = false;
            $result['errors'][] = "Test results file not found";
            return $result;
        }
        
        // Validate JSON structure
        $content = file_get_contents($testResultsFile);
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result['valid'] = false;
            $result['errors'][] = "Invalid JSON in test results: " . json_last_error_msg();
            return $result;
        }
        
        // Check required fields
        $requiredFields = ['timestamp', 'summary', 'suites'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $result['valid'] = false;
                $result['errors'][] = "Missing required field in test results: {$field}";
            }
        }
        
        // Check summary fields
        if (isset($data['summary'])) {
            $summaryFields = ['total_tests', 'passed', 'failed', 'overall_pass_rate'];
            foreach ($summaryFields as $field) {
                if (!isset($data['summary'][$field])) {
                    $result['warnings'][] = "Missing summary field: {$field}";
                }
            }
        }
        
        // Validate pass rate
        if (isset($data['summary']['overall_pass_rate'])) {
            $passRate = $data['summary']['overall_pass_rate'];
            if ($passRate < 80) {
                $result['warnings'][] = "Pass rate ({$passRate}%) is below recommended minimum (80%)";
            }
        }
        
        // Check for critical failures
        if (isset($data['summary']['critical_failures']) && $data['summary']['critical_failures'] > 0) {
            $result['warnings'][] = "Critical failures detected: {$data['summary']['critical_failures']}";
        }
        
        return $result;
    }
    
    public function generateValidationReport()
    {
        $report = "# CI/CD Configuration Validation Report\n\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($this->validationResults as $platform => $result) {
            $report .= "## {$platform} Configuration\n";
            $report .= "Status: " . ($result['valid'] ? "✅ PASSED" : "❌ FAILED") . "\n\n";
            
            if (!empty($result['errors'])) {
                $report .= "### Errors:\n";
                foreach ($result['errors'] as $error) {
                    $report .= "- {$error}\n";
                }
                $report .= "\n";
            }
            
            if (!empty($result['warnings'])) {
                $report .= "### Warnings:\n";
                foreach ($result['warnings'] as $warning) {
                    $report .= "- {$warning}\n";
                }
                $report .= "\n";
            }
        }
        
        $reportFile = $this->resultsDir . '/validation-report.md';
        file_put_contents($reportFile, $report);
        
        echo "📋 Validation report saved to: {$reportFile}\n";
        
        return $reportFile;
    }
}

// CLI interface
if (php_sapi_name() === 'cli') {
    $validator = new CIConfigValidator();
    
    echo "🔍 APS Dream Home - CI Configuration Validator\n";
    echo str_repeat("=", 50) . "\n\n";
    
    $allValid = $validator->validateAllConfigs();
    
    // Generate report
    $reportFile = $validator->generateValidationReport();
    
    echo "\n📁 Detailed report: {$reportFile}\n";
    
    exit($allValid ? 0 : 1);
}
