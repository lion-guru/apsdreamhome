-- Add transaction data for dashboard visualization with minimal column requirements

-- First, let's create a very basic INSERT that should work with almost any transactions table
-- We'll only use the most common columns that are likely to exist

INSERT INTO transactions (user_id, amount, date) VALUES
(1, 1500000.00, '2025-05-01 10:15:30'),
(2, 3500000.00, '2025-05-10 14:30:00'),
(3, 700000.00, '2025-05-02 11:45:20'),
(4, 1500000.00, '2025-05-12 09:20:15'),
(5, 900000.00, '2025-05-05 16:10:45'),
(6, 150000.00, '2025-05-10 17:30:00'),
(7, 2000000.00, '2025-05-08 10:05:30'),
(8, 4000000.00, '2025-05-15 11:25:40'),
(9, 2500000.00, '2025-05-09 14:50:10'),
(10, 100000.00, '2025-05-16 15:40:20');
