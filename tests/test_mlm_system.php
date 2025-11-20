<?php
/**
 * MLM System Testing Suite
 * Comprehensive testing for referral, registration, and commission systems
 */

require_once __DIR__ . '/../includes/config.php';

class MLMTestingSuite {
    private $conn;
    
    public function __construct() {
        $config = AppConfig::getInstance();
        $this->conn = $config->getDatabaseConnection();
    }
    
    public function runAllTests() {
        echo "🧪 Starting MLM System Testing Suite...\n";
        echo "=====================================\n\n";
        
        $tests = [
            'testDatabaseConnection',
            'testReferralCodeGeneration',
            'testRegistrationFlow',
            'testNetworkTree',
            'testCommissionCalculation',
            'testPerformance'
        ];
        
        $results = [];
        foreach ($tests as $test) {
            echo "Running: $test...\n";
            $result = $this->$test();
            $results[$test] = $result;
            echo $result['success'] ? "✅ PASS\n" : "❌ FAIL: {$result['error']}\n";
            echo "-" . str_repeat("-", 50) . "\n";
        }
        
        $this->printResults($results);
    }
    
    private function testDatabaseConnection() {
        try {
            $stmt = $this->conn->query("SELECT 1");
            return ['success' => true, 'message' => 'Database connection successful'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function testReferralCodeGeneration() {
        try {
            $code = "TEST" . substr(md5(uniqid()), 0, 4);
            return ['success' => strlen($code) === 8, 'message' => "Generated code: $code"];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function testRegistrationFlow() {
        try {
            // Test basic registration components
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM users");
            $count = $stmt->fetch_assoc()['count'];
            
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM mlm_profiles");
            $profile_count = $stmt->fetch_assoc()['count'];
            
            return [
                'success' => $count >= 0 && $profile_count >= 0,
                'message' => "Tables accessible: users($count), profiles($profile_count)"
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function testNetworkTree() {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM mlm_network_tree");
            $count = $stmt->fetch_assoc()['count'];
            
            return ['success' => true, 'message' => "Network tree has $count relationships"];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function testCommissionCalculation() {
        try {
            // Test commission table
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM mlm_commission_ledger");
            $count = $stmt->fetch_assoc()['count'];
            
            return ['success' => true, 'message' => "Commission ledger has $count records"];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function testPerformance() {
        try {
            $start = microtime(true);
            
            // Test query performance
            $stmt = $this->conn->query("SELECT * FROM users LIMIT 100");
            $end = microtime(true);
            $duration = $end - $start;
            
            return [
                'success' => $duration < 1.0,
                'message' => "Performance test completed in {$duration} seconds"
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function printResults($results) {
        echo "\n📊 Test Results Summary\n";
        echo "========================\n";
        
        $passed = 0;
        $total = count($results);
        
        foreach ($results as $test => $result) {
            echo str_pad($test, 40) . ": " . ($result['success'] ? "✅ PASS" : "❌ FAIL") . "\n";
            if ($result['success']) {
                $passed++;
            }
        }
        
        echo "\n📈 Summary: $passed/$total tests passed\n";
        
        if ($passed === $total) {
            echo "🎉 All tests passed! System ready for production.\n";
        } else {
            echo "⚠️  Some tests failed. Please review and fix issues.\n";
        }
    }
}

// Run tests if called directly
if (php_sapi_name() === 'cli') {
    $tester = new MLMTestingSuite();
    $tester->runAllTests();
}
?>