<?php
/**
 * Database Migration Script
 * Automated database schema migration for production
 */

echo "🗄️ DATABASE MIGRATION STARTING...\n";

// Backup current database
$backupFile = "database_backup_" . date("Y-m-d_H-i-s") . ".sql";
echo "✅ Creating backup: $backupFile\n";

// Run migration commands
$migrationCommands = [
    "mysqldump -u root -p apsdreamhome > $backupFile",
    "mysql -u root -e \"USE apsdreamhome; SOURCE database/migrations/production_migration.sql;\"",
    "mysql -u root -e \"USE apsdreamhome; OPTIMIZE TABLE properties, projects, users;\""
];

foreach ($migrationCommands as $command) {
    echo "🔧 Executing: $command\n";
    shell_exec($command);
}

echo "✅ Database migration completed!\n";
?>