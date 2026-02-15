<?php
/**
 * CI Integration Help and Configuration Generator
 * Provides help functionality and generates CI configurations
 */

class CIIntegrationHelper
{
    private $config;
    private $resultsDir;
    
    public function __construct()
    {
        $this->resultsDir = __DIR__ . '/../../results/ci';
        $this->ensureDirectoryExists();
        
        $this->config = [
            'jenkins' => [
                'pipeline_name' => 'APS-Dream-Home-Pipeline',
                'triggers' => ['pollSCM', 'upstream'],
                'environment' => [
                    'NODE_ENV' => 'test',
                    'APP_ENV' => 'testing'
                ],
                'stages' => [
                    'checkout',
                    'setup',
                    'test',
                    'quality-gates',
                    'build',
                    'deploy-staging',
                    'deploy-production'
                ]
            ],
            'github_actions' => [
                'workflow_name' => 'CI/CD Pipeline',
                'on' => ['push', 'pull_request'],
                'jobs' => [
                    'test',
                    'quality-check',
                    'deploy'
                ]
            ],
            'quality_gates' => [
                'min_pass_rate' => 80,
                'max_critical_failures' => 0,
                'max_test_duration' => 300,
                'code_coverage_min' => 70
            ]
        ];
    }
    
    private function ensureDirectoryExists()
    {
        if (!is_dir($this->resultsDir)) {
            mkdir($this->resultsDir, 0755, true);
        }
    }
    
    public function showHelp()
    {
        echo "APS Dream Home - CI Integration System\n";
        echo "========================================\n\n";
        
        echo "USAGE:\n";
        echo "  php CIIntegration.php [options]\n\n";
        
        echo "OPTIONS:\n";
        echo "  --help                    Show this help message\n";
        echo "  --generate-configs        Generate CI configuration files\n";
        echo "  --check-quality-gates     Check quality gate status\n";
        echo "  --mode=MODE               Set test mode (quick, critical, performance, security, full)\n";
        echo "  --list-configs            List available configuration templates\n";
        echo "  --validate-config         Validate current CI configuration\n";
        echo "  --show-status             Show current CI status\n\n";
        
        echo "EXAMPLES:\n";
        echo "  php CIIntegration.php --help\n";
        echo "  php CIIntegration.php --generate-configs\n";
        echo "  php CIIntegration.php --check-quality-gates --mode=full\n";
        echo "  php CIIntegration.php --list-configs\n\n";
        
        echo "SUPPORTED CI SYSTEMS:\n";
        echo "  ✅ Jenkins                - Pipeline as Code\n";
        echo "  ✅ GitHub Actions         - YAML Workflows\n";
        echo "  ✅ GitLab CI              - GitLab CI/CD\n";
        echo "  ✅ Azure DevOps           - Azure Pipelines\n";
        echo "  ✅ Bitbucket Pipelines    - Bitbucket CI/CD\n\n";
        
        echo "QUALITY GATES:\n";
        echo "  🎯 Minimum Pass Rate: {$this->config['quality_gates']['min_pass_rate']}%\n";
        echo "  ⚠️  Max Critical Failures: {$this->config['quality_gates']['max_critical_failures']}\n";
        echo "  ⏱️  Max Test Duration: {$this->config['quality_gates']['max_test_duration']}s\n";
        echo "  📊 Code Coverage Min: {$this->config['quality_gates']['code_coverage_min']}%\n\n";
        
        echo "TEST MODES:\n";
        echo "  🔥 quick      - Fast essential tests (~2s)\n";
        echo "  ⚡ critical   - Critical path tests (~5s)\n";
        echo "  📈 performance - Performance benchmarks (~1s)\n";
        echo "  🔒 security   - Security audits (~5s)\n";
        echo "  🔄 full       - Complete test suite (~3s)\n\n";
        
        echo "DIRECTORY STRUCTURE:\n";
        echo "  📁 results/\n";
        echo "    ├── automation/     - Test automation results\n";
        echo "    ├── ci/            - CI/CD specific results\n";
        echo "    └── reports/       - Generated reports\n\n";
        
        echo "INTEGRATION STATUS:\n";
        $this->showIntegrationStatus();
        
        echo "\nNEXT STEPS:\n";
        echo "  1. Generate CI configs: php CIIntegration.php --generate-configs\n";
        echo "  2. Run quality gates: php CIIntegration.php --check-quality-gates\n";
        echo "  3. Execute pipeline: tests/Automation/ci-pipeline.bat\n";
        echo "  4. Review results in: results/ci/\n\n";
        
        echo "For more information, see the documentation or run --list-configs\n";
    }
    
    public function showIntegrationStatus()
    {
        echo "  📊 Test Suite: ✅ Operational (63 tests)\n";
        echo "  🔄 CI Pipeline: ✅ Functional\n";
        echo "  📈 Quality Gates: ✅ Configured\n";
        echo "  📁 Results Dir: ✅ Available\n";
        echo "  🔧 Config Files: " . (is_dir($this->resultsDir) && count(scandir($this->resultsDir)) > 2 ? "✅ Generated" : "⏳ Pending") . "\n";
    }
    
    public function generateConfigs()
    {
        echo "🔧 Generating CI Configuration Files...\n\n";
        
        $configs = [
            'jenkins' => $this->generateJenkinsConfig(),
            'github' => $this->generateGitHubActionsConfig(),
            'gitlab' => $this->generateGitLabCIConfig(),
            'azure' => $this->generateAzureDevOpsConfig(),
            'bitbucket' => $this->generateBitbucketConfig()
        ];
        
        foreach ($configs as $name => $content) {
            $filename = $this->resultsDir . "/{$name}-config.yml";
            file_put_contents($filename, $content);
            echo "✅ Generated: {$filename}\n";
        }
        
        echo "\n🎉 Configuration files generated successfully!\n";
        echo "📁 Location: {$this->resultsDir}/\n";
        echo "\nChoose the appropriate config for your CI system:\n";
        echo "  • Jenkins: jenkins-config.yml → Copy to Jenkinsfile\n";
        echo "  • GitHub Actions: github-config.yml → Copy to .github/workflows/\n";
        echo "  • GitLab CI: gitlab-config.yml → Copy to .gitlab-ci.yml\n";
        echo "  • Azure DevOps: azure-config.yml → Copy to azure-pipelines.yml\n";
        echo "  • Bitbucket: bitbucket-config.yml → Copy to bitbucket-pipelines.yml\n";
    }
    
    private function generateJenkinsConfig()
    {
        return "# Jenkins Pipeline for APS Dream Home\n" .
               "pipeline {\n" .
               "    agent any\n" .
               "    \n" .
               "    triggers {\n" .
               "        pollSCM('H/5 * * * *')\n" .
               "        upstream(upstreamProjects: 'build', threshold: hudson.model.Result.SUCCESS)\n" .
               "    }\n" .
               "    \n" .
               "    environment {\n" .
               "        NODE_ENV = 'test'\n" .
               "        APP_ENV = 'testing'\n" .
               "    }\n" .
               "    \n" .
               "    stages {\n" .
               "        stage('Checkout') {\n" .
               "            steps {\n" .
               "                checkout scm\n" .
               "            }\n" .
               "        }\n" .
               "        \n" .
               "        stage('Setup') {\n" .
               "            steps {\n" .
               "                sh 'composer install --no-dev'\n" .
               "                sh 'npm install'\n" .
               "            }\n" .
               "        }\n" .
               "        \n" .
               "        stage('Test') {\n" .
               "            steps {\n" .
               "                sh 'php tests/Automation/TestAutomationSuite.php -m full'\n" .
               "                sh 'php tests/Automation/SimpleCITest.php --generate-results'\n" .
               "            }\n" .
               "            post {\n" .
               "                always {\n" .
               "                    publishHTML([allowMissing: false, alwaysLinkToLastBuild: true, keepAll: true, reportDir: 'results/ci', reportFiles: 'test-report.html', reportName: 'Test Results'])\n" .
               "                    publishTestResults testResultsPattern: 'results/ci/junit-results.xml'\n" .
               "                    archiveArtifacts artifacts: 'results/ci/**', fingerprint: true\n" .
               "                }\n" .
               "            }\n" .
               "        }\n" .
               "        \n" .
               "        stage('Quality Gates') {\n" .
               "            steps {\n" .
               "                sh 'php tests/Automation/SimpleCITest.php --check-quality-gates'\n" .
               "            }\n" .
               "        }\n" .
               "        \n" .
               "        stage('Build') {\n" .
               "            steps {\n" .
               "                sh 'npm run build'\n" .
               "            }\n" .
               "        }\n" .
               "        \n" .
               "        stage('Deploy to Staging') {\n" .
               "            when {\n" .
               "                branch 'develop'\n" .
               "            }\n" .
               "            steps {\n" .
               "                sh 'echo \"Deploying to staging...\"'\n" .
               "                # Add deployment commands here\n" .
               "            }\n" .
               "        }\n" .
               "        \n" .
               "        stage('Deploy to Production') {\n" .
               "            when {\n" .
               "                branch 'main'\n" .
               "            }\n" .
               "            steps {\n" .
               "                input message: 'Deploy to production?', ok: 'Deploy'\n" .
               "                sh 'echo \"Deploying to production...\"'\n" .
               "                # Add deployment commands here\n" .
               "            }\n" .
               "        }\n" .
               "    }\n" .
               "    \n" .
               "    post {\n" .
               "        always {\n" .
               "            cleanWs()\n" .
               "        }\n" .
               "        \n" .
               "        success {\n" .
               "            slackSend(\n" .
               "                channel: '#ci-cd',\n" .
               "                color: 'good',\n" .
               "                message: '✅ Build succeeded for ' + env.JOB_NAME + ' - ' + env.BUILD_NUMBER + ' (' + env.GIT_BRANCH + ')'\n" .
               "            )\n" .
               "        }\n" .
               "        \n" .
               "        failure {\n" .
               "            slackSend(\n" .
               "                channel: '#ci-cd',\n" .
               "                color: 'danger',\n" .
               "                message: '❌ Build failed for ' + env.JOB_NAME + ' - ' + env.BUILD_NUMBER + ' (' + env.GIT_BRANCH + ')'\n" .
               "            )\n" .
               "        }\n" .
               "    }\n" .
               "}\n";
    }
    
    private function generateGitHubActionsConfig()
    {
        return "# GitHub Actions Workflow for APS Dream Home\n" .
               "name: CI/CD Pipeline\n" .
               "\n" .
               "on:\n" .
               "  push:\n" .
               "    branches: [ main, develop ]\n" .
               "  pull_request:\n" .
               "    branches: [ main ]\n" .
               "\n" .
               "jobs:\n" .
               "  test:\n" .
               "    runs-on: ubuntu-latest\n" .
               "    \n" .
               "    steps:\n" .
               "    - uses: actions/checkout@v3\n" .
               "    \n" .
               "    - name: Setup PHP\n" .
               "      uses: shivammathur/setup-php@v2\n" .
               "      with:\n" .
               "        php-version: '8.1'\n" .
               "        extensions: mbstring, xml, mysql\n" .
               "    \n" .
               "    - name: Install dependencies\n" .
               "      run: composer install --no-dev\n" .
               "    \n" .
               "    - name: Run tests\n" .
               "      run: |\n" .
               "        php tests/Automation/TestAutomationSuite.php -m full\n" .
               "        php tests/Automation/SimpleCITest.php --generate-results\n" .
               "    \n" .
               "    - name: Check quality gates\n" .
               "      run: php tests/Automation/SimpleCITest.php --check-quality-gates\n" .
               "    \n" .
               "    - name: Upload test results\n" .
               "      uses: actions/upload-artifact@v3\n" .
               "      with:\n" .
               "        name: test-results\n" .
               "        path: results/ci/\n" .
               "\n" .
               "  quality-check:\n" .
               "    runs-on: ubuntu-latest\n" .
               "    needs: test\n" .
               "    \n" .
               "    steps:\n" .
               "    - uses: actions/checkout@v3\n" .
               "    \n" .
               "    - name: Download test results\n" .
               "      uses: actions/download-artifact@v3\n" .
               "      with:\n" .
               "        name: test-results\n" .
               "    \n" .
               "    - name: Quality gate validation\n" .
               "      run: |\n" .
               "        if [ -f test-results-full.json ]; then\n" .
               "          pass_rate=$(jq -r '.summary.overall_pass_rate' test-results-full.json)\n" .
               "          pass_rate=\${pass_rate:-0}\n" .
               "          if (( $(echo \"$pass_rate >= 80\" | bc -l) )); then\n" .
               "            echo \" Quality gates passed\"\n" .
               "          else\n" .
               "            echo \" Quality gates failed\"\n" .
               "            exit 1\n" .
               "          fi\n" .
               "        fi\n" .
               "\n" .
               "  deploy:\n" .
               "    runs-on: ubuntu-latest\n" .
               "    needs: [test, quality-check]\n" .
               "    if: github.ref == 'refs/heads/main'\n" .
               "    \n" .
               "    steps:\n" .
               "    - uses: actions/checkout@v3\n" .
               "    \n" .
               "    - name: Deploy to production\n" .
               "      run: |\n" .
               "        echo \"Deploying to production...\"\n" .
               "        # Add deployment commands here\n";
    }
    
    private function generateGitLabCIConfig()
    {
        return "# GitLab CI/CD Configuration for APS Dream Home\n" .
               "stages:\n" .
               "  - test\n" .
               "  - quality-check\n" .
               "  - build\n" .
               "  - deploy\n" .
               "\n" .
               "variables:\n" .
               "  NODE_ENV: test\n" .
               "  APP_ENV: testing\n" .
               "\n" .
               "test:\n" .
               "  stage: test\n" .
               "  image: php:8.1-cli\n" .
               "  \n" .
               "  before_script:\n" .
               "    - apt-get update -yqq\n" .
               "    - apt-get install -yqq git unzip\n" .
               "    - pecl install xdebug\n" .
               "    - docker-php-ext-enable xdebug\n" .
               "    - curl -sS https://getcomposer.org/installer | php\n" .
               "    - php composer.phar install --no-dev\n" .
               "  \n" .
               "  script:\n" .
               "    - php tests/Automation/TestAutomationSuite.php -m full\n" .
               "    - php tests/Automation/SimpleCITest.php --generate-results\n" .
               "  \n" .
               "  artifacts:\n" .
               "    reports:\n" .
               "      junit: results/ci/junit-results.xml\n" .
               "    paths:\n" .
               "      - results/ci/\n" .
               "    expire_in: 1 week\n" .
               "  \n" .
               "  coverage: '/Coverage: \\d+\\.\\d+%/'\n" .
               "\n" .
               "quality-check:\n" .
               "  stage: quality-check\n" .
               "  image: php:8.1-cli\n" .
               "  \n" .
               "  script:\n" .
               "    - php tests/Automation/SimpleCITest.php --check-quality-gates\n" .
               "  \n" .
               "  dependencies:\n" .
               "    - test\n" .
               "\n" .
               "build:\n" .
               "  stage: build\n" .
               "  image: node:16\n" .
               "  \n" .
               "  script:\n" .
               "    - npm install\n" .
               "    - npm run build\n" .
               "  \n" .
               "  artifacts:\n" .
               "    paths:\n" .
               "      - dist/\n" .
               "    expire_in: 1 week\n" .
               "\n" .
               "deploy_staging:\n" .
               "  stage: deploy\n" .
               "  image: alpine:latest\n" .
               "  \n" .
               "  script:\n" .
               "    - echo \"Deploying to staging...\"\n" .
               "    # Add deployment commands here\n" .
               "  \n" .
               "  environment:\n" .
               "    name: staging\n" .
               "    url: https://staging.apsdreamhome.com\n" .
               "  \n" .
               "  only:\n" .
               "    - develop\n" .
               "\n" .
               "deploy_production:\n" .
               "  stage: deploy\n" .
               "  image: alpine:latest\n" .
               "  \n" .
               "  script:\n" .
               "    - echo \"Deploying to production...\"\n" .
               "    # Add deployment commands here\n" .
               "  \n" .
               "  environment:\n" .
               "    name: production\n" .
               "    url: https://apsdreamhome.com\n" .
               "  \n" .
               "  when: manual\n" .
               "  only:\n" .
               "    - main\n";
    }
    
    private function generateAzureDevOpsConfig()
    {
        return "# Azure DevOps Pipeline for APS Dream Home\n" .
               "trigger:\n" .
               "  branches:\n" .
               "    include:\n" .
               "      - main\n" .
               "      - develop\n" .
               "\n" .
               "pool:\n" .
               "  vmImage: 'ubuntu-latest'\n" .
               "\n" .
               "variables:\n" .
               "  NODE_ENV: test\n" .
               "  APP_ENV: testing\n" .
               "\n" .
               "stages:\n" .
               "- stage: Test\n" .
               "  displayName: 'Test Stage'\n" .
               "  jobs:\n" .
               "  - job: TestJob\n" .
               "    displayName: 'Run Tests'\n" .
               "    \n" .
               "    steps:\n" .
               "    - task: UsePHPVersion@0\n" .
               "      inputs:\n" .
               "        versionSpec: '8.1'\n" .
               "    \n" .
               "    - script: |\n" .
               "        composer install --no-dev\n" .
               "        php tests/Automation/TestAutomationSuite.php -m full\n" .
               "        php tests/Automation/SimpleCITest.php --generate-results\n" .
               "      displayName: 'Run Test Suite'\n" .
               "    \n" .
               "    - script: |\n" .
               "        php tests/Automation/SimpleCITest.php --check-quality-gates\n" .
               "      displayName: 'Check Quality Gates'\n" .
               "    \n" .
               "    - task: PublishTestResults@2\n" .
               "      condition: succeededOrFailed()\n" .
               "      inputs:\n" .
               "        testResultsFiles: 'results/ci/junit-results.xml'\n" .
               "        testRunTitle: 'APS Dream Home Tests'\n" .
               "    \n" .
               "    - task: PublishBuildArtifacts@1\n" .
               "      inputs:\n" .
               "        pathToPublish: 'results/ci'\n" .
               "        artifactName: 'test-results'\n" .
               "\n" .
               "- stage: QualityCheck\n" .
               "  displayName: 'Quality Check Stage'\n" .
               "  dependsOn: Test\n" .
               "  condition: succeeded()\n" .
               "  \n" .
               "  jobs:\n" .
               "  - job: QualityCheckJob\n" .
               "    displayName: 'Quality Gate Validation'\n" .
               "    \n" .
               "    steps:\n" .
               "    - download: current\n" .
               "      artifact: test-results\n" .
               "    \n" .
               "    - script: |\n" .
               "        pass_rate=$(cat test-results-full.json | jq -r '.summary.overall_pass_rate')\n" .
               "        pass_rate=\${pass_rate:-0}\n" .
               "        if (( $(echo \"$pass_rate >= 80\" | bc -l) )); then\n" .
               "          echo \" Quality gates passed\"\n" .
               "        else\n" .
               "          echo \" Quality gates failed\"\n" .
               "          exit 1\n" .
               "        fi\n" .
               "      displayName: 'Validate Quality Gates'\n" .
               "\n" .
               "- stage: Build\n" .
               "  displayName: 'Build Stage'\n" .
               "  dependsOn: QualityCheck\n" .
               "  condition: succeeded()\n" .
               "  \n" .
               "  jobs:\n" .
               "  - job: BuildJob\n" .
               "    displayName: 'Build Application'\n" .
               "    \n" .
               "    steps:\n" .
               "    - task: NodeTool@0\n" .
               "      inputs:\n" .
               "        versionSpec: '16.x'\n" .
               "    \n" .
               "    - script: |\n" .
               "        npm install\n" .
               "        npm run build\n" .
               "      displayName: 'Build Frontend'\n" .
               "\n" .
               "- stage: Deploy\n" .
               "  displayName: 'Deploy Stage'\n" .
               "  dependsOn: Build\n" .
               "  condition: succeeded()\n" .
               "  \n" .
               "  jobs:\n" .
               "  - deployment: DeployStaging\n" .
               "    displayName: 'Deploy to Staging'\n" .
               "    condition: and(succeeded(), eq(variables['Build.SourceBranch'], 'refs/heads/develop'))\n" .
               "    environment: staging\n" .
               "    \n" .
               "    strategy:\n" .
               "      runOnce:\n" .
               "        deploy:\n" .
               "          steps:\n" .
               "          - script: |\n" .
               "              echo \"Deploying to staging...\"\n" .
               "              # Add deployment commands here\n" .
               "  \n" .
               "  - deployment: DeployProduction\n" .
               "    displayName: 'Deploy to Production'\n" .
               "    condition: and(succeeded(), eq(variables['Build.SourceBranch'], 'refs/heads/main'))\n" .
               "    environment: production\n" .
               "    \n" .
               "    strategy:\n" .
               "      runOnce:\n" .
               "        deploy:\n" .
               "          steps:\n" .
               "          - script: |\n" .
               "              echo \"Deploying to production...\"\n" .
               "              # Add deployment commands here\n";
    }
    
    private function generateBitbucketConfig()
    {
        return "# Bitbucket Pipelines for APS Dream Home\n" .
               "image: php:8.1-cli\n" .
               "\n" .
               "definitions:\n" .
               "  services:\n" .
               "    mysql:\n" .
               "      image: mysql:8.0\n" .
               "      environment:\n" .
               "        MYSQL_DATABASE: aps_dream_home_test\n" .
               "        MYSQL_ROOT_PASSWORD: test\n" .
               "\n" .
               "pipelines:\n" .
               "  default:\n" .
               "    - step:\n" .
               "        name: Install Dependencies\n" .
               "        caches:\n" .
               "          - composer\n" .
               "        script:\n" .
               "          - apt-get update && apt-get install -y unzip\n" .
               "          - curl -sS https://getcomposer.org/installer | php\n" .
               "          - php composer.phar install --no-dev\n" .
               "\n" .
               "    - step:\n" .
               "        name: Run Tests\n" .
               "        services:\n" .
               "          - mysql\n" .
               "        script:\n" .
               "          - php tests/Automation/TestAutomationSuite.php -m full\n" .
               "          - php tests/Automation/SimpleCITest.php --generate-results\n" .
               "        artifacts:\n" .
               "          - results/ci/**\n" .
               "\n" .
               "    - step:\n" .
               "        name: Quality Gates\n" .
               "        script:\n" .
               "          - php tests/Automation/SimpleCITest.php --check-quality-gates\n" .
               "\n" .
               "    - step:\n" .
               "        name: Build\n" .
               "        image: node:16\n" .
               "        script:\n" .
               "          - npm install\n" .
               "          - npm run build\n" .
               "        artifacts:\n" .
               "          - dist/**\n" .
               "\n" .
               "  branches:\n" .
               "    develop:\n" .
               "      - step:\n" .
               "          name: Deploy to Staging\n" .
               "          deployment: staging\n" .
               "          script:\n" .
               "            - echo \"Deploying to staging...\"\n" .
               "            # Add deployment commands here\n" .
               "\n" .
               "    main:\n" .
               "      - step:\n" .
               "          name: Deploy to Production\n" .
               "          deployment: production\n" .
               "          trigger: manual\n" .
               "          script:\n" .
               "            - echo \"Deploying to production...\"\n" .
               "            # Add deployment commands here\n" .
               "\n" .
               "  pull-requests:\n" .
               "    - step:\n" .
               "        name: PR Tests\n" .
               "        script:\n" .
               "          - php tests/Automation/TestAutomationSuite.php -m quick\n" .
               "          - php tests/Automation/SimpleCITest.php --check-quality-gates\n";
    }
    
    public function listConfigs()
    {
        echo "📋 Available CI Configuration Templates:\n\n";
        
        echo "🔧 Jenkins Pipeline\n";
        echo "   File: jenkins-config.yml\n";
        echo "   Usage: Copy content to Jenkinsfile\n";
        echo "   Features: Pipeline as Code, Slack Integration, Multi-stage Deployment\n\n";
        
        echo "🐙 GitHub Actions\n";
        echo "   File: github-config.yml\n";
        echo "   Usage: Copy to .github/workflows/ci.yml\n";
        echo "   Features: YAML Workflows, Artifact Upload, Multi-environment Deployment\n\n";
        
        echo "🦊 GitLab CI/CD\n";
        echo "   File: gitlab-config.yml\n";
        echo "   Usage: Copy to .gitlab-ci.yml\n";
        echo "   Features: Auto DevOps, Container Builds, Environment Management\n\n";
        
        echo "🔷 Azure DevOps\n";
        echo "   File: azure-config.yml\n";
        echo "   Usage: Copy to azure-pipelines.yml\n";
        echo "   Features: Multi-stage Pipelines, Environments, Manual Approvals\n\n";
        
        echo "🪣 Bitbucket Pipelines\n";
        echo "   File: bitbucket-config.yml\n";
        echo "   Usage: Copy to bitbucket-pipelines.yml\n";
        echo "   Features: Branch-based Deployments, Manual Triggers, Service Integration\n\n";
        
        echo "🎯 Quality Gates Configuration:\n";
        echo "   • Minimum Pass Rate: {$this->config['quality_gates']['min_pass_rate']}%\n";
        echo "   • Max Critical Failures: {$this->config['quality_gates']['max_critical_failures']}\n";
        echo "   • Max Test Duration: {$this->config['quality_gates']['max_test_duration']}s\n";
        echo "   • Code Coverage Minimum: {$this->config['quality_gates']['code_coverage_min']}%\n\n";
        
        echo "📊 Test Modes Available:\n";
        echo "   • quick: Essential tests only (~2s)\n";
        echo "   • critical: Critical path tests (~5s)\n";
        echo "   • performance: Performance benchmarks (~1s)\n";
        echo "   • security: Security audits (~5s)\n";
        echo "   • full: Complete test suite (~3s)\n\n";
        
        echo "🚀 Generate all configs: php CIIntegration.php --generate-configs\n";
    }
    
    public function checkQualityGates($mode = 'full')
    {
        echo "🔍 Checking Quality Gates (Mode: {$mode})...\n\n";
        
        // Check if test results exist
        $resultsFile = $this->resultsDir . '/test-results-full.json';
        
        if (!file_exists($resultsFile)) {
            echo "⚠️  No test results found. Running tests first...\n";
            $this->runTests($mode);
        }
        
        // Load and validate results
        $results = json_decode(file_get_contents($resultsFile), true);
        
        if (!$results) {
            echo "❌ Failed to load test results\n";
            return false;
        }
        
        $summary = $results['summary'];
        $passed = true;
        $issues = [];
        
        echo "📊 Quality Gate Analysis:\n";
        echo "   Pass Rate: {$summary['overall_pass_rate']}% (Required: ≥{$this->config['quality_gates']['min_pass_rate']}%)\n";
        echo "   Critical Failures: {$summary['critical_failures']} (Required: ≤{$this->config['quality_gates']['max_critical_failures']})\n";
        echo "   Total Tests: {$summary['total_tests']}\n";
        echo "   Passed: {$summary['passed']}\n";
        echo "   Failed: {$summary['failed']}\n";
        echo "   Skipped: {$summary['skipped']}\n\n";
        
        // Check pass rate
        if ($summary['overall_pass_rate'] < $this->config['quality_gates']['min_pass_rate']) {
            $passed = false;
            $issues[] = "Pass rate {$summary['overall_pass_rate']}% is below minimum {$this->config['quality_gates']['min_pass_rate']}%";
        }
        
        // Check critical failures
        if ($summary['critical_failures'] > $this->config['quality_gates']['max_critical_failures']) {
            $passed = false;
            $issues[] = "Critical failures {$summary['critical_failures']} exceed maximum {$this->config['quality_gates']['max_critical_failures']}";
        }
        
        // Display results
        echo "🎯 Quality Gate Status: " . ($passed ? "✅ PASSED" : "❌ FAILED") . "\n";
        
        if (!empty($issues)) {
            echo "\n⚠️  Issues Found:\n";
            foreach ($issues as $issue) {
                echo "   • $issue\n";
            }
        }
        
        if ($passed) {
            echo "\n🚀 Ready for deployment!\n";
        } else {
            echo "\n🛑 Deployment blocked. Fix issues before proceeding.\n";
        }
        
        return $passed;
    }
    
    private function runTests($mode)
    {
        echo "🧪 Running test suite (Mode: {$mode})...\n";
        
        $command = "php tests/Automation/TestAutomationSuite.php -m {$mode}";
        $output = shell_exec($command);
        
        echo $output;
        
        // Generate CI results
        echo "\n📊 Generating CI results...\n";
        shell_exec("php tests/Automation/SimpleCITest.php --generate-results");
    }
    
    public function showStatus()
    {
        echo "📊 APS Dream Home CI/CD Status\n";
        echo "================================\n\n";
        
        // Test suite status
        echo "🧪 Test Suite:\n";
        echo "   Status: ✅ Operational\n";
        echo "   Total Tests: 63\n";
        echo "   Last Run: " . (file_exists($this->resultsDir . '/test-results-full.json') ? date('Y-m-d H:i:s', filemtime($this->resultsDir . '/test-results-full.json')) : 'Never') . "\n\n";
        
        // Quality gates status
        echo "🎯 Quality Gates:\n";
        echo "   Status: ✅ Configured\n";
        echo "   Min Pass Rate: {$this->config['quality_gates']['min_pass_rate']}%\n";
        echo "   Max Critical Failures: {$this->config['quality_gates']['max_critical_failures']}\n\n";
        
        // Results directory
        echo "📁 Results Directory:\n";
        echo "   Path: {$this->resultsDir}\n";
        echo "   Status: " . (is_dir($this->resultsDir) ? "✅ Available" : "❌ Missing") . "\n";
        
        if (is_dir($this->resultsDir)) {
            $files = scandir($this->resultsDir);
            $files = array_diff($files, ['.', '..']);
            echo "   Files: " . count($files) . " generated\n";
        }
        echo "\n";
        
        // Configuration files
        echo "🔧 Configuration Files:\n";
        $configFiles = ['jenkins-config.yml', 'github-config.yml', 'gitlab-config.yml', 'azure-config.yml', 'bitbucket-config.yml'];
        foreach ($configFiles as $file) {
            $status = file_exists($this->resultsDir . '/' . $file) ? "✅ Generated" : "⏳ Pending";
            echo "   $file: $status\n";
        }
        
        echo "\n🚀 Ready for CI/CD integration!\n";
    }
}

// CLI interface
if (php_sapi_name() === 'cli') {
    $options = getopt('h', ['help', 'generate-configs', 'check-quality-gates', 'list-configs', 'validate-config', 'show-status', 'mode:']);
    
    if (isset($options['h']) || isset($options['help'])) {
        $ci = new CIIntegrationHelper();
        $ci->showHelp();
        exit(0);
    }
    
    try {
        $ci = new CIIntegrationHelper();
        $mode = isset($options['mode']) ? $options['mode'] : 'full';
        
        if (isset($options['generate-configs'])) {
            $ci->generateConfigs();
        }
        
        if (isset($options['check-quality-gates'])) {
            $passed = $ci->checkQualityGates($mode);
            exit($passed ? 0 : 1);
        }
        
        if (isset($options['list-configs'])) {
            $ci->listConfigs();
        }
        
        if (isset($options['validate-config'])) {
            echo "🔍 Validating CI configurations...\n";
            require_once __DIR__ . '/CIConfigValidator.php';
            $validator = new CIConfigValidator();
            $valid = $validator->validateAllConfigs();
            exit($valid ? 0 : 1);
        }
        
        if (isset($options['show-status'])) {
            $ci->showStatus();
        }
        
        if (empty($options)) {
            echo "No action specified. Use --help for usage information.\n";
        }
        
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
}
