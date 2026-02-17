-- Create table for associate levels and rewards
CREATE TABLE IF NOT EXISTS associate_levels (
    level_id INT PRIMARY KEY AUTO_INCREMENT,
    level_name VARCHAR(50) NOT NULL,
    min_business DECIMAL(12,2) NOT NULL,
    max_business DECIMAL(12,2) NOT NULL,
    commission_percentage DECIMAL(4,2) NOT NULL,
    reward_description VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default levels based on the payout system
INSERT INTO associate_levels (level_name, min_business, max_business, commission_percentage, reward_description) VALUES
('Associate', 0, 1000000, 5.00, 'Mobile'),
('Sr. Associate', 1000001, 3500000, 7.00, 'Tablet'),
('Bdm', 3500001, 7000000, 10.00, 'Laptop'),
('Sr. Bdm', 7000001, 15000000, 12.00, 'Domestic/Foreign Tour'),
('Vice President', 15000001, 30000000, 15.00, 'Pulsar Bike'),
('President', 30000001, 50000000, 18.00, 'Bullet'),
('Site Manager', 50000001, 999999999, 20.00, 'Car');

-- Add level tracking to associates table
ALTER TABLE associates
ADD COLUMN IF NOT EXISTS current_level_id INT DEFAULT 1,
ADD COLUMN IF NOT EXISTS total_business DECIMAL(12,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS current_month_business DECIMAL(12,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS team_business DECIMAL(12,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS reward_earned BOOLEAN DEFAULT FALSE,
ADD FOREIGN KEY (current_level_id) REFERENCES associate_levels(level_id);

-- Create table for team hierarchy
CREATE TABLE IF NOT EXISTS team_hierarchy (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    upline_id INT NOT NULL,
    level INT NOT NULL COMMENT 'Level in the hierarchy (1 for direct sponsor, 2 for sponsor\'s sponsor, etc.)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES associates(associate_id),
    FOREIGN KEY (upline_id) REFERENCES associates(associate_id)
);

-- Create table for commission transactions with level difference tracking
CREATE TABLE IF NOT EXISTS commission_transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    booking_id INT NOT NULL,
    business_amount DECIMAL(12,2) NOT NULL,
    commission_amount DECIMAL(10,2) NOT NULL,
    commission_percentage DECIMAL(4,2) NOT NULL,
    level_difference_amount DECIMAL(10,2) DEFAULT 0.00,
    upline_id INT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (associate_id) REFERENCES associates(associate_id),
    FOREIGN KEY (upline_id) REFERENCES associates(associate_id),
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);

-- Create trigger to update team hierarchy when new associate is added
DELIMITER //
CREATE TRIGGER IF NOT EXISTS after_associate_insert
AFTER INSERT ON associates
FOR EACH ROW
BEGIN
    -- Insert direct relationship
    IF NEW.sponser_id IS NOT NULL THEN
        INSERT INTO team_hierarchy (associate_id, upline_id, level)
        SELECT NEW.associate_id, associate_id, 1
        FROM associates
        WHERE uid = NEW.sponser_id;
        
        -- Insert indirect relationships (up to 7 levels)
        INSERT INTO team_hierarchy (associate_id, upline_id, level)
        SELECT NEW.associate_id, th.upline_id, th.level + 1
        FROM team_hierarchy th
        WHERE th.associate_id = (SELECT associate_id FROM associates WHERE uid = NEW.sponser_id)
        AND th.level < 7;
    END IF;
END //
DELIMITER ;

-- Create trigger to handle commission distribution and level updates
DELIMITER //
CREATE TRIGGER IF NOT EXISTS after_commission_transaction
AFTER INSERT ON commission_transactions
FOR EACH ROW
BEGIN
    DECLARE total_bus DECIMAL(12,2);
    DECLARE current_associate_level DECIMAL(4,2);
    DECLARE upline_commission_pct DECIMAL(4,2);
    DECLARE level_diff DECIMAL(4,2);
    DECLARE v_upline_id INT;
    
    -- Get current associate's commission percentage
    SELECT al.commission_percentage INTO current_associate_level
    FROM associates a
    JOIN associate_levels al ON a.current_level_id = al.level_id
    WHERE a.associate_id = NEW.associate_id;
    
    -- Calculate and distribute level difference commission to upline
    -- Find immediate upline with higher level
    SELECT th.upline_id INTO v_upline_id
    FROM team_hierarchy th
    JOIN associates a ON th.upline_id = a.associate_id
    JOIN associate_levels al ON a.current_level_id = al.level_id
    WHERE th.associate_id = NEW.associate_id
    AND th.level = 1
    AND al.commission_percentage > current_associate_level
    LIMIT 1;
    
    IF v_upline_id IS NOT NULL THEN
        -- Get upline's commission percentage
        SELECT al.commission_percentage INTO upline_commission_pct
        FROM associates a
        JOIN associate_levels al ON a.current_level_id = al.level_id
        WHERE a.associate_id = v_upline_id;
        
        -- Calculate level difference
        SET level_diff = upline_commission_pct - current_associate_level;
        
        -- Calculate and store level difference amount (20% of business)
        IF level_diff > 0 THEN
            UPDATE commission_transactions
            SET level_difference_amount = (NEW.business_amount * 0.20 * level_diff / 100),
                upline_id = v_upline_id
            WHERE transaction_id = NEW.transaction_id;
        END IF;
    END IF;
    
    -- Update total business for the associate
    UPDATE associates
    SET total_business = total_business + NEW.business_amount,
        current_month_business = current_month_business + NEW.business_amount
    WHERE associate_id = NEW.associate_id;
    
    -- Get updated total business
    SELECT total_business INTO total_bus
    FROM associates
    WHERE associate_id = NEW.associate_id;
    
    -- Update associate level based on total business
    UPDATE associates a
    SET current_level_id = (
        SELECT level_id
        FROM associate_levels
        WHERE min_business <= total_bus
        AND max_business >= total_bus
    )
    WHERE associate_id = NEW.associate_id;
    
    -- Update team business for all uplines
    UPDATE associates a
    JOIN team_hierarchy th ON a.associate_id = th.upline_id
    SET a.team_business = a.team_business + NEW.business_amount
    WHERE th.associate_id = NEW.associate_id;
END //
DELIMITER ;