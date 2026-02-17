-- Drop existing foreign key constraints to allow table modifications
ALTER TABLE associates DROP FOREIGN KEY IF EXISTS associates_ibfk_1;
ALTER TABLE associates DROP FOREIGN KEY IF EXISTS associates_ibfk_2;
ALTER TABLE associates DROP FOREIGN KEY IF EXISTS associates_ibfk_3;
ALTER TABLE associates DROP FOREIGN KEY IF EXISTS associates_ibfk_4;
ALTER TABLE associates DROP FOREIGN KEY IF EXISTS associates_ibfk_5;

-- Create temporary table to store valid sponsor relationships
CREATE TEMPORARY TABLE temp_valid_sponsors AS
SELECT a.associate_id, a.sponser_id
FROM associates a
INNER JOIN associates s ON a.sponser_id = s.uid;

-- Update invalid sponsor references to NULL
UPDATE associates a
LEFT JOIN associates s ON a.sponser_id = s.uid
SET a.sponser_id = NULL
WHERE a.sponser_id IS NOT NULL AND s.uid IS NULL;

-- Modify associates table structure
ALTER TABLE associates
  MODIFY associate_id INT AUTO_INCREMENT,
  ADD COLUMN sponser_id_int INT NULL AFTER sponser_id;

-- Update the new integer sponsor_id column
UPDATE associates a
INNER JOIN associates s ON a.sponser_id = s.uid
SET a.sponser_id_int = s.associate_id;

-- Drop the old sponsor_id column and rename the new one
ALTER TABLE associates
  DROP COLUMN sponser_id,
  CHANGE sponser_id_int sponser_id INT NULL;

-- Add foreign key constraints
ALTER TABLE associates
  ADD CONSTRAINT fk_associate_sponsor FOREIGN KEY (sponser_id) REFERENCES associates(associate_id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_associate_level FOREIGN KEY (current_level_id) REFERENCES associate_levels(level_id) ON DELETE SET NULL;

-- Drop temporary table
DROP TEMPORARY TABLE IF EXISTS temp_valid_sponsors;

-- Update commission_transactions table structure
ALTER TABLE commission_transactions
  MODIFY transaction_id INT AUTO_INCREMENT,
  ADD level_difference_amount DECIMAL(10,2) DEFAULT 0.00,
  ADD upline_id INT,
  ADD CONSTRAINT fk_commission_upline FOREIGN KEY (upline_id) REFERENCES associates(associate_id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_commission_associate FOREIGN KEY (associate_id) REFERENCES associates(associate_id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_commission_booking FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE;

-- Create team_hierarchy table if not exists
CREATE TABLE IF NOT EXISTS team_hierarchy (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    upline_id INT NOT NULL,
    level INT NOT NULL COMMENT 'Level in hierarchy (1 for direct sponsor, 2 for sponsor\'s sponsor, etc.)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_team_associate FOREIGN KEY (associate_id) REFERENCES associates(associate_id) ON DELETE CASCADE,
    CONSTRAINT fk_team_upline FOREIGN KEY (upline_id) REFERENCES associates(associate_id) ON DELETE CASCADE
);

-- Add indexes for performance optimization
CREATE INDEX idx_commission_date ON commission_transactions(transaction_date);
CREATE INDEX idx_team_hierarchy_level ON team_hierarchy(level);
CREATE INDEX idx_associate_sponsor ON associates(sponser_id);

-- Update triggers for maintaining data integrity
DELIMITER //

DROP TRIGGER IF EXISTS after_associate_insert//
CREATE TRIGGER after_associate_insert AFTER INSERT ON associates
FOR EACH ROW
BEGIN
    -- Insert direct relationship
    IF NEW.sponser_id IS NOT NULL THEN
        INSERT INTO team_hierarchy (associate_id, upline_id, level)
        VALUES (NEW.associate_id, NEW.sponser_id, 1);
        
        -- Insert indirect relationships
        INSERT INTO team_hierarchy (associate_id, upline_id, level)
        SELECT NEW.associate_id, th.upline_id, th.level + 1
        FROM team_hierarchy th
        WHERE th.associate_id = NEW.sponser_id;
    END IF;
END//

DELIMITER ;