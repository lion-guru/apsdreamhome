<?php
// This script will safely append missing tables to db_schema.sql

// Configuration
$schemaFile = __DIR__ . '/db_schema.sql';
$updateFile = __DIR__ . '/schema_update_20250630_111549.sql';
$backupFile = __DIR__ . '/db_schema.backup.' . date('Ymd_His') . '.sql';

// Create a backup of the current schema file
if (!copy($schemaFile, $backupFile)) {
    die("Error: Could not create backup of schema file.\n");
}
echo "Backup created: " . basename($backupFile) . "\n";

// Read the current schema
$schemaContent = file_get_contents($schemaFile);
if ($schemaContent === false) {
    die("Error: Could not read schema file.\n");
}

// Read the update content
$updateContent = file_get_contents($updateFile);
if ($updateContent === false) {
    die("Error: Could not read update file.\n");
}

// Find the end of the schema file (before the final SET statements)
$endMarker = '/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;';
$endPos = strrpos($schemaContent, $endMarker);

if ($endPos === false) {
    die("Error: Could not find end marker in schema file.\n");
}

// Split the schema into two parts
$schemaStart = substr($schemaContent, 0, $endPos + strlen($endMarker));
$schemaEnd = substr($schemaContent, $endPos + strlen($endMarker));

// Process the update content to extract only the CREATE TABLE statements
$tablesToAdd = [];
$lines = explode("\n", $updateContent);
$currentTable = null;

foreach ($lines as $line) {
    if (preg_match('/^-- Table: (\w+)/', $line, $matches)) {
        $currentTable = $matches[1];
        $tablesToAdd[$currentTable] = [];
    } elseif ($currentTable !== null) {
        $tablesToAdd[$currentTable][] = $line;
    }
}

// Filter out tables that already exist in the schema
$existingTables = [];
preg_match_all('/CREATE\s+TABLE\s+[`"]([^`"]+)[`"]/i', $schemaContent, $matches);
if (!empty($matches[1])) {
    $existingTables = array_map('strtolower', $matches[1]);
}

// Generate the new content
$newContent = $schemaStart . "\n\n";
$tablesAdded = 0;

foreach ($tablesToAdd as $tableName => $tableLines) {
    if (!in_array(strtolower($tableName), $existingTables)) {
        $newContent .= "--\n-- Table structure for table `$tableName`\n--\n\n";
        $newContent .= implode("\n", $tableLines) . "\n\n";
        $tablesAdded++;
        echo "Added table: $tableName\n";
    } else {
        echo "Skipped (already exists): $tableName\n";
    }
}

// Add the end of the schema file
$newContent .= $schemaEnd;

// Write the updated content back to the schema file
if (file_put_contents($schemaFile, $newContent) !== false) {
    echo "\nSchema file updated successfully. $tablesAdded tables were added.\n";
    echo "Original schema was backed up to: " . basename($backupFile) . "\n";
} else {
    echo "Error: Could not write to schema file.\n";
    echo "Your original schema is still intact at: " . basename($backupFile) . "\n";
}
?>
