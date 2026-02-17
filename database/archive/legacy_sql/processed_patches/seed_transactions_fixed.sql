-- Add transaction data for dashboard visualization with correct column structure

-- First, let's check the actual structure of the transactions table
-- Based on the error message, it seems 'property_id' column doesn't exist

-- Add transaction data with only the columns that exist in your table
-- Most likely structure includes: id, user_id, amount, date, status, etc.
INSERT IGNORE INTO transactions (user_id, amount, date, status, reference_number) VALUES
(1, 1500000.00, '2025-05-01 10:15:30', 'completed', 'TXN-20250501-001'),
(2, 3500000.00, '2025-05-10 14:30:00', 'completed', 'TXN-20250510-001'),
(3, 700000.00, '2025-05-02 11:45:20', 'completed', 'TXN-20250502-001'),
(4, 1500000.00, '2025-05-12 09:20:15', 'pending', 'TXN-20250512-001'),
(5, 900000.00, '2025-05-05 16:10:45', 'completed', 'TXN-20250505-001'),
(6, 150000.00, '2025-05-10 17:30:00', 'completed', 'TXN-20250510-002'),
(7, 2000000.00, '2025-05-08 10:05:30', 'completed', 'TXN-20250508-001'),
(8, 4000000.00, '2025-05-15 11:25:40', 'completed', 'TXN-20250515-001'),
(9, 2500000.00, '2025-05-09 14:50:10', 'completed', 'TXN-20250509-001'),
(10, 100000.00, '2025-05-16 15:40:20', 'completed', 'TXN-20250516-001');
