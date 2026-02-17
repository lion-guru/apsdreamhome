<?php
/**
 * APS Dream Home - Database Directory Cleanup & Organization
 * Properly organizes the database directory structure
 */

// Define the new organized structure
$databaseDir = __DIR__ . '/database';
$newStructure = [
    '01_main_databases' => [
        'apsdreamhome.sql',
        'apsdreamhomes.sql',
        'aps_dream_homes_main_config.sql'
    ],
    '02_migrations' => [
        'migrate.php',
        'migrate_databases.php',
        'migrate_without_fk.php'
    ],
    '03_backups' => [
        'apsdreamhome (4).sql',
        'apsdreamhome_ultimate.sql',
        'apsdreamhome_backup_20250925.sql'
    ],
    '04_seeders' => [
        'insert_sample_data.sql',
        'sample_data.sql',
        'create_sample_data.php',
        'seed_demo_data_final.sql'
    ],
    '05_tools' => [
        'database_analyzer.php',
        'database_merger.php',
        'organize_database.php',
        'backup_database.php'
    ],
    '06_documentation' => [
        'README.md',
        'DATABASE_TOOLS_GUIDE.md',
        'README_DEMO_DATA.md',
        'README_MIGRATIONS.md'
    ]
];

echo "=== APS Dream Home - Database Directory Reorganization ===\n\n";

// Create organized subdirectories
foreach (array_keys($newStructure) as $dir) {
    $dirPath = $databaseDir . '/' . $dir;
    if (!is_dir($dirPath)) {
        mkdir($dirPath, 0755, true);
        echo "✅ Created: $dir/\n";
    }
}

// Move files to appropriate directories
$movedCount = 0;
foreach ($newStructure as $category => $files) {
    foreach ($files as $file) {
        $sourcePath = $databaseDir . '/' . $file;
        if (file_exists($sourcePath)) {
            $targetPath = $databaseDir . '/' . $category . '/' . $file;
            if (rename($sourcePath, $targetPath)) {
                echo "📦 Moved: $file -> $category/\n";
                $movedCount++;
            }
        }
    }
}

// Handle special cases - large SQL files
$largeSqlFiles = [
    'aps_complete_schema_part1.sql',
    'aps_complete_schema_part2.sql',
    'aps_complete_schema_part3.sql',
    'enhanced_database_structure.sql'
];

foreach ($largeSqlFiles as $file) {
    $sourcePath = $databaseDir . '/' . $file;
    if (file_exists($sourcePath)) {
        $targetPath = $databaseDir . '/01_main_databases/' . $file;
        if (rename($sourcePath, $targetPath)) {
            echo "📦 Moved large file: $file -> 01_main_databases/\n";
            $movedCount++;
        }
    }
}

// Create a summary of what was organized
echo "\n=== Organization Summary ===\n";
echo "📊 Files moved: $movedCount\n";
echo "📁 New structure:\n";

foreach ($newStructure as $category => $files) {
    $count = count($files);
    echo "   $category/ ($count files)\n";
}

// Create organization guide
$guideContent = "# APS Dream Home - Database Directory Structure

## 📁 Organized Structure

```
database/
├── 01_main_databases/     # Main database files
│   ├── apsdreamhome.sql (MAIN FILE - 11KB)
│   ├── apsdreamhomes.sql
│   └── aps_dream_homes_main_config.sql
├── 02_migrations/         # Database migration scripts
├── 03_backups/           # Backup database files
├── 04_seeders/           # Sample data files
├── 05_tools/             # Database utility scripts
└── 06_documentation/     # Database documentation
```

## 🎯 Main Database Files

### **📄 Primary Files:**
- **`apsdreamhome.sql`** - Main database file (11KB complete)
- **`aps_dream_homes_main_config.sql`** - Header/footer settings
- **`apsdreamhomes.sql`** - Full database schema

### **📦 Backup Files:**
- Old versions and large schema files
- Previous backups organized by date

### **🔧 Migration Files:**
- Database update scripts
- Schema modification files

### **📊 Seeder Files:**
- Sample data insertion scripts
- Demo data for testing

### **🛠️ Tool Files:**
- Database analyzers and utilities
- Backup and restore scripts

## 🚀 Usage Instructions

1. **Import Main Database:** Use `01_main_databases/apsdreamhome.sql`
2. **Add Settings:** Import `01_main_databases/aps_dream_homes_main_config.sql`
3. **Run Migrations:** Execute files in `02_migrations/` if needed
4. **Add Sample Data:** Use files in `04_seeders/` for testing

## 📋 File Categories Explained

| Category | Purpose | When to Use |
|----------|---------|-------------|
| **01_main_databases** | Core database files | Always - for fresh setup |
| **02_migrations** | Update existing databases | When upgrading |
| **03_backups** | Previous versions | Reference only |
| **04_seeders** | Sample data | For testing/demo |
| **05_tools** | Utility scripts | For maintenance |
| **06_documentation** | Help files | For reference |

## ✅ Benefits of This Organization

- **📁 Clear Structure** - Easy to find files
- **🔒 No Duplicates** - Single source of truth
- **📦 Organized Backups** - Easy recovery
- **🛠️ Tool Separation** - Utilities separate from data
- **📚 Documentation** - All guides in one place

## 🎯 Quick Start

```bash
# 1. Import main database
mysql -u root -p apsdreamhome < database/01_main_databases/apsdreamhome.sql

# 2. Import settings
mysql -u root -p apsdreamhome < database/01_main_databases/aps_dream_homes_main_config.sql

# 3. Ready to use!
```

---

*Generated: " . date('Y-m-d H:i:s') . "*
";

file_put_contents($databaseDir . '/DATABASE_ORGANIZATION_GUIDE.md', $guideContent);

echo "\n✅ Database directory reorganization completed!\n";
echo "📋 Created organization guide: database/DATABASE_ORGANIZATION_GUIDE.md\n";
echo "🎯 Main file: database/01_main_databases/apsdreamhome.sql\n";
echo "📦 All files properly organized and accessible!\n";

?>
