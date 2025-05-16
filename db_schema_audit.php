<?php
/**
 * Comprehensive Database Schema Audit Tool
 * Provides in-depth analysis of database structure, integrity, and potential issues
 */
require_once __DIR__ . '/includes/db_security_upgrade.php';

class DatabaseSchemaAuditor {
    private $db;
    private $auditReport = [];
    private $logFile;

    public function __construct(DatabaseSecurityUpgrade $db) {
        $this->db = $db;
        $this->logFile = __DIR__ . '/logs/db_schema_audit_' . date('Y-m-d') . '.log';
        $this->ensureLogDirectoryExists();
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectoryExists() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Perform comprehensive database schema audit
     * @return array Detailed audit report
     */
    public function performAudit() {
        $this->log('Starting comprehensive database schema audit');
        
        $this->auditReport = [
            'timestamp' => date('Y-m-d H:i:s'),
            'database_name' => $this->getDatabaseName(),
            'tables' => [],
            'potential_issues' => [],
            'recommendations' => []
        ];

        $tables = $this->getAllTables();
        
        foreach ($tables as $table) {
            $tableDetails = $this->analyzeTable($table);
            $this->auditReport['tables'][$table] = $tableDetails;
            
            $this->checkTableIntegrity($table, $tableDetails);
        }

        $this->generateRecommendations();
        $this->log('Database schema audit completed');
        
        return $this->auditReport;
    }

    /**
     * Get current database name
     * @return string Database name
     */
    private function getDatabaseName() {
        $query = 'SELECT DATABASE()';
        $result = $this->db->preparedQuery($query);
        return $result->fetchColumn();
    }

    /**
     * Retrieve all tables in the database
     * @return array List of table names
     */
    private function getAllTables() {
        $query = 'SHOW TABLES';
        $result = $this->db->preparedQuery($query);
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Analyze individual table structure
     * @param string $tableName Table to analyze
     * @return array Table details
     */
    private function analyzeTable($tableName) {
        $tableDetails = [
            'columns' => [],
            'primary_key' => null,
            'indexes' => [],
            'foreign_keys' => []
        ];

        // Analyze columns
        $columnQuery = "SHOW COLUMNS FROM `{$tableName}`";
        $columns = $this->db->preparedQuery($columnQuery);
        
        while ($column = $columns->fetch(PDO::FETCH_ASSOC)) {
            $columnDetails = [
                'name' => $column['Field'],
                'type' => $column['Type'],
                'null' => $column['Null'] === 'YES',
                'default' => $column['Default'],
                'key' => $column['Key']
            ];

            if ($column['Key'] === 'PRI') {
                $tableDetails['primary_key'] = $column['Field'];
            }

            $tableDetails['columns'][] = $columnDetails;
        }

        // Analyze indexes
        $indexQuery = "SHOW INDEXES FROM `{$tableName}`";
        $indexes = $this->db->preparedQuery($indexQuery);
        
        while ($index = $indexes->fetch(PDO::FETCH_ASSOC)) {
            $tableDetails['indexes'][] = [
                'name' => $index['Key_name'],
                'column' => $index['Column_name'],
                'unique' => $index['Non_unique'] == 0
            ];
        }

        return $tableDetails;
    }

    /**
     * Check table integrity and identify potential issues
     * @param string $tableName Table to check
     * @param array $tableDetails Table details
     */
    private function checkTableIntegrity($tableName, $tableDetails) {
        $issues = [];

        // Check for missing primary key
        if (!$tableDetails['primary_key']) {
            $issues[] = 'Missing primary key';
        }

        // Check for nullable critical columns
        foreach ($tableDetails['columns'] as $column) {
            if ($column['null'] && in_array($column['name'], ['id', 'user_id', 'email'])) {
                $issues[] = "Critical column {$column['name']} is nullable";
            }
        }

        // Check for potential indexing improvements
        if (count($tableDetails['indexes']) < 2) {
            $issues[] = 'Potential need for additional indexes';
        }

        if (!empty($issues)) {
            $this->auditReport['potential_issues'][$tableName] = $issues;
            $this->log("Integrity issues found in table {$tableName}: " . implode(', ', $issues));
        }
    }

    /**
     * Generate recommendations based on audit findings
     */
    private function generateRecommendations() {
        $recommendations = [];

        if (isset($this->auditReport['potential_issues'])) {
            $recommendations[] = 'Review and address tables with potential integrity issues';
        }

        // Add more generic recommendations
        $recommendations[] = 'Ensure all critical tables have primary keys';
        $recommendations[] = 'Add appropriate indexes for frequently queried columns';
        $recommendations[] = 'Avoid nullable columns for critical fields';

        $this->auditReport['recommendations'] = $recommendations;
    }

    /**
     * Log audit messages
     * @param string $message Log message
     */
    private function log($message) {
        $logEntry = date('Y-m-d H:i:s') . " - {$message}
";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Generate HTML report
     * @return string HTML report
     */
    public function generateHTMLReport() {
        $report = $this->auditReport;
        
        $html = "<html><body>";
        $html .= "<h1>Database Schema Audit Report</h1>";
        $html .= "<p>Timestamp: {$report['timestamp']}</p>";
        $html .= "<h2>Database: {$report['database_name']}</h2>";

        // Tables Summary
        $html .= "<h3>Tables Overview</h3>";
        $html .= "<ul>";
        foreach ($report['tables'] as $tableName => $tableDetails) {
            $html .= "<li><strong>{$tableName}</strong>";
            $html .= "<ul>";
            $html .= "<li>Primary Key: " . ($tableDetails['primary_key'] ?? 'None') . "</li>";
            $html .= "<li>Columns: " . count($tableDetails['columns']) . "</li>";
            $html .= "<li>Indexes: " . count($tableDetails['indexes']) . "</li>";
            $html .= "</ul></li>";
        }
        $html .= "</ul>";

        // Potential Issues
        if (!empty($report['potential_issues'])) {
            $html .= "<h3>Potential Issues</h3>";
            $html .= "<ul>";
            foreach ($report['potential_issues'] as $tableName => $issues) {
                $html .= "<li><strong>{$tableName}</strong>: " . implode(', ', $issues) . "</li>";
            }
            $html .= "</ul>";
        }

        // Recommendations
        $html .= "<h3>Recommendations</h3>";
        $html .= "<ul>";
        foreach ($report['recommendations'] as $recommendation) {
            $html .= "<li>{$recommendation}</li>";
        }
        $html .= "</ul>";

        $html .= "</body></html>";
        
        return $html;
    }
}

// Execute audit if run directly
if (php_sapi_name() === 'cli') {
    try {
        $dbSecurity = new DatabaseSecurityUpgrade();
        $auditor = new DatabaseSchemaAuditor($dbSecurity);
        
        $auditReport = $auditor->performAudit();
        
        // Generate and save HTML report
        $htmlReport = $auditor->generateHTMLReport();
        file_put_contents(__DIR__ . '/logs/db_schema_audit_report.html', $htmlReport);
        
        echo "Database Schema Audit Completed.
";
        echo "Report saved to: " . __DIR__ . "/logs/db_schema_audit_report.html
";
    } catch (Exception $e) {
        echo "Audit failed: " . $e->getMessage() . "\n";
    }
} else {
    // Web interface for report
    try {
        $dbSecurity = new DatabaseSecurityUpgrade();
        $auditor = new DatabaseSchemaAuditor($dbSecurity);
        
        $auditReport = $auditor->performAudit();
        echo $auditor->generateHTMLReport();
    } catch (Exception $e) {
        echo "Audit failed: " . $e->getMessage();
    }
}
?>
