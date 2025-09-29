# MLM Schema Analysis and Recommendations

## Current MLM Table Structure

### 1. Associates Table
- **Purpose**: Stores MLM associate/user information
- **Key Fields**: 
  - `user_id` (links to users table)
  - `sponsor_id` (upline reference)
  - `status` (active/inactive)
  - `rank`/`level`
  - `join_date`
  - `wallet_balance`

### 2. MLM Tree
- **Purpose**: Maintains the hierarchical structure of the MLM network
- **Key Fields**:
  - `user_id`
  - `parent_id` (immediate upline)
  - `level` (depth in the tree)
  - `path` (materialized path for easy querying)

### 3. Commissions
- **Purpose**: Tracks all commission transactions
- **Key Tables:
  - `mlm_commissions`
  - `mlm_commission_ledger`
  - `commission_payouts`
  - `commission_transactions`

## Recommended Changes

### 1. Schema Enhancements

#### a) Add Missing Indexes
```sql
-- For faster lookups in the MLM tree
CREATE INDEX idx_mlm_tree_parent ON mlm_tree(parent_id);
CREATE INDEX idx_mlm_tree_path ON mlm_tree(path(255));

-- For commission calculations
CREATE INDEX idx_commissions_user ON mlm_commissions(user_id, status);
CREATE INDEX idx_commission_ledger_user ON mlm_commission_ledger(user_id, transaction_date);
```

#### b) Add Foreign Key Constraints
```sql
-- Ensure data integrity
ALTER TABLE mlm_commissions 
ADD CONSTRAINT fk_commission_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE mlm_tree
ADD CONSTRAINT fk_tree_parent 
FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE SET NULL;
```

### 2. Data Migration Steps

#### a) Back Up Existing Data
```sql
-- Create backup tables
CREATE TABLE mlm_commissions_backup AS SELECT * FROM mlm_commissions;
CREATE TABLE mlm_tree_backup AS SELECT * FROM mlm_tree;
-- Repeat for other MLM tables
```

#### b) Migrate to New Schema
```sql
-- Example migration for adding new fields
ALTER TABLE associates 
ADD COLUMN wallet_balance DECIMAL(15,2) DEFAULT 0.00,
ADD COLUMN kyc_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending';

-- Migrate existing data if needed
UPDATE associates a
JOIN users u ON a.user_id = u.id
SET a.join_date = COALESCE(a.join_date, u.created_at);
```

### 3. New Features to Implement

#### a) Wallet System
```sql
CREATE TABLE associate_wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    associate_id INT NOT NULL,
    available_balance DECIMAL(15,2) DEFAULT 0.00,
    pending_balance DECIMAL(15,2) DEFAULT 0.00,
    total_earned DECIMAL(15,2) DEFAULT 0.00,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES associates(user_id) ON DELETE CASCADE
);

CREATE TABLE wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    description TEXT,
    reference_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wallet_id) REFERENCES associate_wallets(id) ON DELETE CASCADE
);
```

#### b) Enhanced Reporting Views
```sql
-- Monthly commission summary
CREATE VIEW vw_monthly_commissions AS
SELECT 
    a.user_id,
    u.name,
    DATE_FORMAT(c.created_at, '%Y-%m') AS month,
    COUNT(*) AS transaction_count,
    SUM(c.commission_amount) AS total_commission,
    c.commission_type
FROM mlm_commissions c
JOIN associates a ON c.user_id = a.user_id
JOIN users u ON a.user_id = u.id
GROUP BY a.user_id, u.name, DATE_FORMAT(c.created_at, '%Y-%m'), c.commission_type;
```

## Implementation Plan

### Phase 1: Schema Updates (1-2 days)
1. Add new tables for wallet system
2. Add missing indexes and constraints
3. Create backup of existing data

### Phase 2: Data Migration (1 day)
1. Migrate existing data to new schema
2. Validate data integrity
3. Test all existing functionality

### Phase 3: New Features (3-5 days)
1. Implement wallet system
2. Add KYC verification
3. Create enhanced reporting
4. Implement notification system

## Testing Strategy

1. **Unit Tests**
   - Test commission calculations
   - Verify tree structure integrity
   - Validate wallet transactions

2. **Integration Tests**
   - Test commission distribution
   - Verify wallet operations
   - Test reporting views

3. **Performance Testing**
   - Test with large datasets
   - Optimize slow queries
   - Verify index usage

## Rollback Plan

1. Keep backup of all data before migration
2. Document all schema changes
3. Have rollback scripts ready
4. Test rollback procedure

## Monitoring and Maintenance

1. Set up monitoring for:
   - Commission calculations
   - Wallet transactions
   - Tree structure integrity
   - Performance metrics

2. Regular maintenance tasks:
   - Database optimization
   - Clean up old data
   - Update statistics

## Next Steps

1. Review and approve the proposed changes
2. Schedule a maintenance window for the updates
3. Perform the updates in a staging environment first
4. Test thoroughly before production deployment
