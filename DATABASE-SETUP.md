# 🗄️ Database Setup Instructions

## MySQL/XAMPP Setup Required

The database migration script requires MySQL to be running. Since XAMPP is installed, please follow these steps:

### 1. Start XAMPP Control Panel
```bash
# Open XAMPP Control Panel (GUI)
Start-Process "C:\xampp\xampp-control.exe"
```

### 2. Start MySQL Service
In XAMPP Control Panel:
- Click "Start" button next to "MySQL"
- Wait for the status to show "Running" (green)

### 3. Verify Database Connection
Once MySQL is running, execute the migration:

```bash
php run_indexes_migration.php
```

### 4. Alternative: Manual SQL Commands
If the script doesn't work, you can run these SQL commands directly in phpMyAdmin:

```sql
-- Indexes for leads table
CREATE INDEX IF NOT EXISTS leads_agent_status_index ON leads (agent_id, status);
CREATE INDEX IF NOT EXISTS leads_priority_index ON leads (priority);
CREATE INDEX IF NOT EXISTS leads_created_at_index ON leads (created_at);

-- Indexes for payouts table  
CREATE INDEX IF NOT EXISTS payouts_associate_status_index ON payouts (associate_id, status);
CREATE INDEX IF NOT EXISTS payouts_created_at_index ON payouts (created_at);

-- Indexes for users table
CREATE INDEX IF NOT EXISTS users_email_index ON users (email);
CREATE INDEX IF NOT EXISTS users_status_index ON users (status);
CREATE INDEX IF NOT EXISTS users_created_at_index ON users (created_at);
```

### 5. Access phpMyAdmin
- Open: http://localhost/phpmyadmin
- Select the `apsdreamhome` database
- Go to "SQL" tab
- Paste and execute the above commands

## 🔧 Troubleshooting

### If MySQL doesn't start:
1. Check if port 3306 is blocked
2. Restart XAMPP completely
3. Check Windows Firewall settings

### If database doesn't exist:
```sql
CREATE DATABASE IF NOT EXISTS apsdreamhome;
```

### If tables don't exist:
Run the main database setup script first before adding indexes.

---

**⚠️ Important**: Complete this database setup before proceeding with production deployment!
