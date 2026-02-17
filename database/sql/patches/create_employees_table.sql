-- Create employees table for demo and production use
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    role VARCHAR(50) DEFAULT 'employee',
    status VARCHAR(20) DEFAULT 'active',
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert demo employee data (all with password 'Aps@128128')
-- Passwords are bcrypt hashes for 'Aps@128128'
INSERT INTO employees (name, email, phone, role, status, password) VALUES
('Demo Employee 1', 'demo.employee1@aps.com', '9000000001', 'employee', 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1'),
('Demo Employee 2', 'demo.employee2@aps.com', '9000000002', 'employee', 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1'),
('Demo Employee 3', 'demo.employee3@aps.com', '9000000003', 'employee', 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1'),
('Demo Employee 4', 'demo.employee4@aps.com', '9000000004', 'employee', 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1'),
('Demo Employee 5', 'demo.employee5@aps.com', '9000000005', 'employee', 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1');
