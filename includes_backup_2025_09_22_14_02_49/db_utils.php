<?php
/**
 * Database utility functions for counting users and properties
 */

/**
 * Count users by their type (agent, builder, etc.)
 * 
 * @param mysqli $con Database connection
 * @param string $type User type to count
 * @return int Number of users of the specified type
 */
function countUsersByType($con, $type) {
    if (!$con) {
        return 0;
    }
    
    try {
        $stmt = $con->prepare("SELECT COUNT(*) as count FROM users WHERE utype = ?");
        if ($stmt) {
            $stmt->bind_param("s", $type);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['count'];
        }
    } catch (Exception $e) {
        error_log("Error counting users by type: " . $e->getMessage());
    }
    
    return 0;
}

/**
 * Count properties by their type (apartment, house, etc.)
 * 
 * @param mysqli $con Database connection
 * @param string $type Property type to count (empty string for all properties)
 * @return int Number of properties of the specified type
 */
function countPropertiesByType($con, $type) {
    if (!$con) {
        return 0;
    }
    
    try {
        if (empty($type)) {
            // Count all properties
            $stmt = $con->prepare("SELECT COUNT(*) as count FROM property");
            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();
                return $row['count'];
            }
        } else {
            // Count properties of specific type
            $stmt = $con->prepare("SELECT COUNT(*) as count FROM property WHERE type = ?");
            if ($stmt) {
                $stmt->bind_param("s", $type);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();
                return $row['count'];
            }
        }
    } catch (Exception $e) {
        error_log("Error counting properties by type: " . $e->getMessage());
    }
    
    return 0;
}