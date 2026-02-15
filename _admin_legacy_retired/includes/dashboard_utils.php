<?php
/**
 * Utility functions for the admin dashboard
 * Provides functions to count users and properties by type
 */

/**
 * Count users by their type (agent, builder, etc.)
 * @param mysqli $con Database connection
 * @param string $type User type to count
 * @return int Number of users of the specified type
 */
function countUsersByType($con, $type) {
    try {
        $stmt = $con->prepare("SELECT COUNT(*) as count FROM user WHERE utype = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $con->error);
            return 0;
        }

        $stmt->bind_param("s", $type);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return 0;
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;

    } catch (Exception $e) {
        error_log("Error counting users by type: " . $e->getMessage());
        return 0;
    }
}

/**
 * Count properties by their type (apartment, house, etc.)
 * @param mysqli $con Database connection
 * @param string $type Property type to count (empty string for all properties)
 * @return int Number of properties of the specified type
 */
function countPropertiesByType($con, $type) {
    try {
        if (empty($type)) {
            // Count all properties
            $stmt = $con->prepare("SELECT COUNT(*) as count FROM property");
            if (!$stmt) {
                error_log("Prepare failed: " . $con->error);
                return 0;
            }
        } else {
            // Count properties of specific type
            $stmt = $con->prepare("SELECT COUNT(*) as count FROM property WHERE type = ?");
            if (!$stmt) {
                error_log("Prepare failed: " . $con->error);
                return 0;
            }
            $stmt->bind_param("s", $type);
        }

        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return 0;
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;

    } catch (Exception $e) {
        error_log("Error counting properties by type: " . $e->getMessage());
        return 0;
    }
}