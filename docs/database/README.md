# Database Documentation

## Overview
This document outlines the database schema, relationships, and important queries for the APS Dream Home system.

## Database Schema

### Core Tables

#### 1. Users
- `users` - System users (admin, agents, customers)
- `roles` - User roles and permissions
- `user_meta` - Additional user information

#### 2. Properties
- `properties` - Property listings
- `property_types` - Types of properties
- `property_features` - Features and amenities
- `property_images` - Property photos and documents

#### 3. Customers & Leads
- `customers` - Customer information
- `leads` - Sales leads
- `kyc_documents` - Customer KYC documents
- `customer_ledger` - Financial transactions

#### 4. Financial
- `transactions` - All financial transactions
- `invoices` - Customer invoices
- `payments` - Payment records
- `commissions` - Agent commissions

#### 5. Land Management
- `land_parcels` - Land inventory
- `land_owners` - Landowner information
- `land_transactions` - Land purchase/sale records
- `registry` - Land registry information

#### 6. HR
- `employees` - Employee records
- `attendance` - Attendance tracking
- `leave_requests` - Leave management
- `payroll` - Salary processing

## Entity Relationship Diagram

See the [ER Diagram documentation](?doc=database&file=er-diagram.md) for a detailed breakdown of the database schema.

## Important Queries

### Get Active Properties
```sql
SELECT p.*, pt.name as property_type 
FROM properties p
JOIN property_types pt ON p.type_id = pt.id
WHERE p.status = 'active';
```

### Customer Ledger
```sql
SELECT c.name, cl.* 
FROM customer_ledger cl
JOIN customers c ON cl.customer_id = c.id
WHERE c.id = :customer_id
ORDER BY cl.transaction_date DESC;
```

## Database Maintenance
- Regular backups are scheduled
- Index optimization runs weekly
- Data archiving for old records

## Database Migrations

For detailed migration instructions, see the [Migration Guide](?doc=database&file=migration-guide.md) for version updates and schema changes.
