-- Add transaction data for dashboard visualization

-- Check if transactions table exists and create it if needed
CREATE TABLE IF NOT EXISTS transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    property_id INT,
    transaction_type ENUM('booking', 'installment', 'commission', 'refund', 'other') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50),
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'completed',
    reference_number VARCHAR(50),
    notes TEXT,
    INDEX idx_user (user_id),
    INDEX idx_property (property_id),
    INDEX idx_date (date)
);

-- Add transaction data
INSERT IGNORE INTO transactions (user_id, property_id, transaction_type, amount, payment_method, date, status, reference_number, notes) VALUES
(1, 1, 'booking', 1500000.00, 'bank_transfer', '2025-05-01 10:15:30', 'completed', 'TXN-20250501-001', 'Initial booking amount for Luxury Villa'),
(2, 1, 'installment', 3500000.00, 'bank_transfer', '2025-05-10 14:30:00', 'completed', 'TXN-20250510-001', 'First installment payment'),
(3, 2, 'booking', 700000.00, 'credit_card', '2025-05-02 11:45:20', 'completed', 'TXN-20250502-001', 'Initial booking for City Apartment'),
(4, 2, 'installment', 1500000.00, 'bank_transfer', '2025-05-12 09:20:15', 'pending', 'TXN-20250512-001', 'First installment payment - pending clearance'),
(5, 3, 'booking', 900000.00, 'bank_transfer', '2025-05-05 16:10:45', 'completed', 'TXN-20250505-001', 'Initial booking for Suburban House'),
(6, 1, 'commission', 150000.00, 'bank_transfer', '2025-05-10 17:30:00', 'completed', 'TXN-20250510-002', 'Agent commission for Luxury Villa sale'),
(7, 4, 'booking', 2000000.00, 'bank_transfer', '2025-05-08 10:05:30', 'completed', 'TXN-20250508-001', 'Initial booking for Beach Property'),
(8, 4, 'installment', 4000000.00, 'bank_transfer', '2025-05-15 11:25:40', 'completed', 'TXN-20250515-001', 'First installment payment'),
(9, 5, 'booking', 2500000.00, 'credit_card', '2025-05-09 14:50:10', 'completed', 'TXN-20250509-001', 'Initial booking for Penthouse'),
(10, 3, 'refund', 100000.00, 'bank_transfer', '2025-05-16 15:40:20', 'completed', 'TXN-20250516-001', 'Partial refund due to amenity changes');
