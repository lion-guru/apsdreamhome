-- Create associate permissions table
CREATE TABLE IF NOT EXISTS associate_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    module_name VARCHAR(50) NOT NULL,
    permission_type ENUM('read', 'write', 'delete', 'admin') NOT NULL,
    is_allowed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    UNIQUE KEY unique_associate_module (associate_id, module_name)
);

-- Insert default permissions based on associate levels
INSERT INTO associate_permissions (associate_id, module_name, permission_type, is_allowed)
SELECT
    id as associate_id,
    'dashboard' as module_name,
    'read' as permission_type,
    TRUE as is_allowed
FROM mlm_agents
ON DUPLICATE KEY UPDATE is_allowed = TRUE;

-- Associate level permissions
INSERT INTO associate_permissions (associate_id, module_name, permission_type, is_allowed)
SELECT
    id as associate_id,
    'customers' as module_name,
    'read' as permission_type,
    TRUE as is_allowed
FROM mlm_agents
ON DUPLICATE KEY UPDATE is_allowed = TRUE;

-- BDM and above can manage team
INSERT INTO associate_permissions (associate_id, module_name, permission_type, is_allowed)
SELECT
    id as associate_id,
    'team_management' as module_name,
    'read' as permission_type,
    CASE
        WHEN current_level IN ('BDM', 'Sr. BDM', 'Vice President', 'President', 'Site Manager') THEN TRUE
        ELSE FALSE
    END as is_allowed
FROM mlm_agents
ON DUPLICATE KEY UPDATE is_allowed = CASE
    WHEN current_level IN ('BDM', 'Sr. BDM', 'Vice President', 'President', 'Site Manager') THEN TRUE
    ELSE FALSE
END;

-- Commission management for higher levels
INSERT INTO associate_permissions (associate_id, module_name, permission_type, is_allowed)
SELECT
    id as associate_id,
    'commission_management' as module_name,
    'read' as permission_type,
    CASE
        WHEN current_level IN ('Sr. BDM', 'Vice President', 'President', 'Site Manager') THEN TRUE
        ELSE FALSE
    END as is_allowed
FROM mlm_agents
ON DUPLICATE KEY UPDATE is_allowed = CASE
    WHEN current_level IN ('Sr. BDM', 'Vice President', 'President', 'Site Manager') THEN TRUE
    ELSE FALSE
END;

-- CRM access for all levels
INSERT INTO associate_permissions (associate_id, module_name, permission_type, is_allowed)
SELECT
    id as associate_id,
    'crm' as module_name,
    'read' as permission_type,
    TRUE as is_allowed
FROM mlm_agents
ON DUPLICATE KEY UPDATE is_allowed = TRUE;

-- Reports access based on level
INSERT INTO associate_permissions (associate_id, module_name, permission_type, is_allowed)
SELECT
    id as associate_id,
    'reports' as module_name,
    'read' as permission_type,
    CASE
        WHEN current_level IN ('Associate', 'Sr. Associate') THEN FALSE
        ELSE TRUE
    END as is_allowed
FROM mlm_agents
ON DUPLICATE KEY UPDATE is_allowed = CASE
    WHEN current_level IN ('Associate', 'Sr. Associate') THEN FALSE
    ELSE TRUE
END;
