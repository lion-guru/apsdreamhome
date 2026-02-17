-- Drop existing procedure if it exists
DROP PROCEDURE IF EXISTS CalculateMLMCommission;

-- Update the stored procedure with proper commission calculation
DELIMITER //
CREATE PROCEDURE CalculateMLMCommission(
    IN p_transaction_id INT,
    IN p_amount DECIMAL(12,2)
)
BEGIN
    DECLARE v_associate_id INT;
    DECLARE v_user_id INT;
    DECLARE v_plan_id INT;
    DECLARE v_direct_commission DECIMAL(12,2);
    DECLARE v_level_commission DECIMAL(12,2);
    DECLARE v_current_level INT;
    DECLARE v_max_level INT;
    DECLARE v_parent_id INT;
    DECLARE v_upline_id INT;
    DECLARE v_direct_percent DECIMAL(5,2);
    DECLARE v_upline_percent DECIMAL(5,2);
    DECLARE v_commission_amount DECIMAL(12,2);
    
    -- Get transaction and associate details
    SELECT ta.associate_id, ta.associate_id, u.id, a.commission_plan_id, a.parent_id
    INTO v_associate_id, v_associate_id, v_user_id, v_plan_id, v_parent_id
    FROM transaction_associates ta
    JOIN associates a ON ta.associate_id = a.id
    JOIN users u ON a.user_id = u.id
    WHERE ta.transaction_id = p_transaction_id
    LIMIT 1;
    
    -- Get the maximum commission level from the plan
    SELECT MAX(level) INTO v_max_level 
    FROM mlm_commission_levels 
    WHERE plan_id = v_plan_id;
    
    -- Calculate direct commission (Level 1)
    SELECT direct_percentage INTO v_direct_percent
    FROM mlm_commission_levels
    WHERE plan_id = v_plan_id AND level = 1
    LIMIT 1;
    
    SET v_direct_commission = (p_amount * v_direct_percent / 100);
    
    -- Insert direct commission
    INSERT INTO mlm_commissions (
        commission_plan_id,
        user_id,
        transaction_id,
        commission_amount,
        commission_type,
        status,
        level,
        direct_percentage,
        is_direct,
        created_at
    ) VALUES (
        v_plan_id,
        v_user_id,
        p_transaction_id,
        v_direct_commission,
        'direct_commission',
        'pending',
        1,
        v_direct_percent,
        1,
        NOW()
    );
    
    -- Calculate and insert upline commissions (Levels 2 and above)
    SET v_current_level = 2;
    SET v_upline_id = v_parent_id;
    
    upline_loop: LOOP
        -- Exit if we've reached max level or no more upline
        IF v_current_level > v_max_level OR v_upline_id IS NULL THEN
            LEAVE upline_loop;
        END IF;
        
        -- Get upline's commission percentage for this level
        SELECT direct_percentage INTO v_upline_percent
        FROM mlm_commission_levels
        WHERE plan_id = v_plan_id AND level = v_current_level
        LIMIT 1;
        
        -- If no percentage defined for this level, use previous level's percentage
        IF v_upline_percent IS NULL THEN
            SELECT direct_percentage INTO v_upline_percent
            FROM mlm_commission_levels
            WHERE plan_id = v_plan_id AND level < v_current_level
            ORDER BY level DESC
            LIMIT 1;
        END IF;
        
        -- Calculate level commission (difference from previous level)
        SELECT direct_percentage INTO v_direct_percent
        FROM mlm_commission_levels
        WHERE plan_id = v_plan_id AND level = (v_current_level - 1)
        LIMIT 1;
        
        SET v_level_commission = (p_amount * (v_upline_percent - v_direct_percent) / 100);
        
        -- Get upline user ID
        SELECT user_id INTO v_user_id
        FROM associates
        WHERE id = v_upline_id;
        
        -- Insert level commission
        INSERT INTO mlm_commissions (
            commission_plan_id,
            user_id,
            transaction_id,
            commission_amount,
            commission_type,
            status,
            level,
            direct_percentage,
            difference_percentage,
            upline_id,
            is_direct,
            created_at
        ) VALUES (
            v_plan_id,
            v_user_id,
            p_transaction_id,
            v_level_commission,
            'level_commission',
            'pending',
            v_current_level,
            v_upline_percent,
            (v_upline_percent - v_direct_percent),
            v_upline_id,
            0,
            NOW()
        );
        
        -- Move up to next level
        SELECT parent_id INTO v_upline_id
        FROM associates
        WHERE id = v_upline_id;
        
        SET v_current_level = v_current_level + 1;
    END LOOP;
    
    -- Update transaction with commission status
    UPDATE transactions 
    SET status = 'commission_processed'
    WHERE id = p_transaction_id;
    
    -- Update associate's business volume
    UPDATE associates
    SET 
        direct_business = direct_business + p_amount,
        total_business = total_business + p_amount,
        updated_at = NOW()
    WHERE id = v_associate_id;
    
    -- Update upline's team business
    CALL UpdateTeamBusiness(v_associate_id, p_amount);
    
END //
DELIMITER ;

-- Create procedure to update team business (if not exists)
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS UpdateTeamBusiness(
    IN p_associate_id INT,
    IN p_amount DECIMAL(12,2)
)
BEGIN
    DECLARE v_parent_id INT;
    
    -- Get parent ID
    SELECT parent_id INTO v_parent_id
    FROM associates
    WHERE id = p_associate_id;
    
    -- Update team business for all upline
    WHILE v_parent_id IS NOT NULL DO
        UPDATE associates
        SET 
            team_business = team_business + p_amount,
            total_business = total_business + p_amount,
            updated_at = NOW()
        WHERE id = v_parent_id;
        
        -- Move up to next level
        SELECT parent_id INTO v_parent_id
        FROM associates
        WHERE id = v_parent_id;
    END WHILE;
END //
DELIMITER ;
