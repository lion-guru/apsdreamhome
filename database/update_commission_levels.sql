-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Backup existing data
DROP TABLE IF EXISTS mlm_commission_levels_backup;
CREATE TABLE mlm_commission_levels_backup SELECT * FROM mlm_commission_levels;

-- Clear existing levels for plan 1
TRUNCATE TABLE mlm_commission_levels;

-- Insert new commission levels for plan 1 (13 levels)
INSERT INTO mlm_commission_levels (plan_id, level, min_business, max_business, direct_percentage, created_at)
VALUES 
-- Level 1: 5% for first 500,000
(1, 1, 0, 500000, 5.00, NOW()),
-- Level 2: 6% for 500,001 - 1,000,000
(1, 2, 500001, 1000000, 6.00, NOW()),
-- Level 3: 7% for 1,000,001 - 2,000,000
(1, 3, 1000001, 2000000, 7.00, NOW()),
-- Level 4: 8% for 2,000,001 - 3,000,000
(1, 4, 2000001, 3000000, 8.00, NOW()),
-- Level 5: 9% for 3,000,001 - 4,000,000
(1, 5, 3000001, 4000000, 9.00, NOW()),
-- Level 6: 10% for 4,000,001 - 5,000,000
(1, 6, 4000001, 5000000, 10.00, NOW()),
-- Level 7: 11% for 5,000,001 - 7,500,000
(1, 7, 5000001, 7500000, 11.00, NOW()),
-- Level 8: 12% for 7,500,001 - 10,000,000
(1, 8, 7500001, 10000000, 12.00, NOW()),
-- Level 9: 13% for 10,000,001 - 15,000,000
(1, 9, 10000001, 15000000, 13.00, NOW()),
-- Level 10: 14% for 15,000,001 - 20,000,000
(1, 10, 15000001, 20000000, 14.00, NOW()),
-- Level 11: 15% for 20,000,001 - 30,000,000
(1, 11, 20000001, 30000000, 15.00, NOW()),
-- Level 12: 16% for 30,000,001 - 50,000,000
(1, 12, 30000001, 50000000, 16.00, NOW()),
-- Level 13: 17% for 50,000,001 and above
(1, 13, 50000001, NULL, 17.00, NOW());

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verify the update
SELECT 
    level,
    CONCAT('₹', FORMAT(min_business, 2)) as min_business,
    IFNULL(CONCAT('₹', FORMAT(max_business, 2)), '∞') as max_business,
    CONCAT(direct_percentage, '%') as commission_rate
FROM mlm_commission_levels
WHERE plan_id = 1
ORDER BY level;

-- Test commission calculation for different amounts
SELECT 
    t.amount,
    l.level,
    CONCAT(l.direct_percentage, '%') as commission_rate,
    CONCAT('₹', FORMAT((t.amount * l.direct_percentage / 100), 2)) as commission
FROM 
    (SELECT 250000 as amount UNION ALL
     SELECT 750000 UNION ALL
     SELECT 1500000 UNION ALL
     SELECT 2500000 UNION ALL
     SELECT 3500000 UNION ALL
     SELECT 4500000 UNION ALL
     SELECT 6000000 UNION ALL
     SELECT 9000000 UNION ALL
     SELECT 12000000 UNION ALL
     SELECT 18000000 UNION ALL
     SELECT 25000000 UNION ALL
     SELECT 40000000 UNION ALL
     SELECT 60000000) as t
JOIN mlm_commission_levels l ON t.amount BETWEEN l.min_business AND IFNULL(l.max_business, 9999999999)
WHERE l.plan_id = 1
ORDER BY t.amount;
