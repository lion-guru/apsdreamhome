# Database Skills

## Connection
- Host: 127.0.0.1
- Port: 3307
- Database: apsdreamhome
- User: root
- Password: (empty)

## MCP Tool
Use mysql MCP for direct queries:
```
mysql query: SHOW TABLES
mysql query: DESCRIBE users
mysql query: SELECT * FROM users LIMIT 5
```

## Common Tables
- `users` - User accounts
- `properties` - Property listings
- `sites` - Project sites
- `bookings` - Property bookings
- `payments` - Payment records

## Query Patterns
```sql
-- All tables
SHOW TABLES

-- Table structure
DESCRIBE table_name

-- Search with LIKE
SELECT * FROM table WHERE name LIKE '%keyword%'

-- Join tables
SELECT u.name, p.title FROM users u JOIN properties p ON u.id = p.user_id
```

## Backup
```bash
mysqldump -u root apsdreamhome > backup.sql
```

## Admin Panel
- URL: http://localhost/apsdreamhome/admin/login
- Check: XAMPP MySQL running on port 3307
