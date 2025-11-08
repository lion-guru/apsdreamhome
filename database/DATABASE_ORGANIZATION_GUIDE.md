# APS Dream Home - Database Directory Structure

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

*Generated: 2025-09-30 17:41:04*
