<?php

namespace App\Services;

use App\Core\Middleware\TenantContext;
use PDO;

/**
 * CustomerService
 * Handles customer registration, profile management, inquiries, wishlist, documents, and KYC.
 */
class CustomerService
{
    protected $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
    }

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    private function tenantWhere(): array
    {
        $tid = $this->getTenantId();
        if ($tid > 1) {
            return [" AND tenant_id = ?", [$tid]];
        }
        return ["", []];
    }

    /**
     * Register a new customer user.
     */
    public function registerCustomer(array $data): array
    {
        $this->db->beginTransaction();

        try {
            $userId = (int)($data["user_id"] ?? 0);
            $customerCode = 'CUST-' . strtoupper(bin2hex(random_bytes(4)));
            $tid = $this->getTenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $tenantParam = $tid > 1 ? [$tid] : [];

            $userSql = "INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at{$tenantCol})
                        VALUES (:name, :email, :phone, :password, 'customer', 'active', NOW(), NOW())";
            $userStmt = $this->db->prepare($userSql);
            $params = [
                ":name" => ($data["first_name"] ?? '') . ' ' . ($data["last_name"] ?? ''),
                ":email" => $data["email"],
                ":phone" => $data["phone"],
                ":password" => password_hash($data["password"] ?? 'default123', PASSWORD_DEFAULT),
            ];
            if ($tid > 1) $params[":tenant_id"] = $tid;
            $userStmt->execute($params);
            $userId = (int)$this->db->lastInsertId();

            $customerSql = "INSERT INTO customers (user_id, customer_code, first_name, last_name, email, phone)
                            VALUES (:user_id, :customer_code, :first_name, :last_name, :email, :phone)";
            $customerStmt = $this->db->prepare($customerSql);
            $customerStmt->execute([
                ":user_id" => $userId,
                ":customer_code" => $customerCode,
                ":first_name" => $data["first_name"],
                ":last_name" => $data["last_name"],
                ":email" => $data["email"],
                ":phone" => $data["phone"],
                ":date_of_birth" => $data["date_of_birth"] ?? null,
                ":gender" => $data["gender"] ?? null,
                ":marital_status" => $data["marital_status"] ?? null,
                ":occupation" => $data["occupation"] ?? null,
                ":annual_income" => $data["annual_income"] ?? null,
                ":permanent_address" => $data["permanent_address"] ?? null,
                ":current_address" => $data["current_address"] ?? null,
                ":city" => $data["city"] ?? null,
                ":state" => $data["state"] ?? null,
                ":pincode" => $data["pincode"] ?? null,
                ":country" => $data["country"] ?? "India",
                ":preferred_property_type" => $data["preferred_property_type"] ?? null,
                ":preferred_location" => $data["preferred_location"] ?? null,
                ":budget_range_min" => $data["budget_range_min"] ?? null,
                ":budget_range_max" => $data["budget_range_max"] ?? null,
                ":preferred_area_min" => $data["preferred_area_min"] ?? null,
                ":preferred_area_max" => $data["preferred_area_max"] ?? null,
                ":account_type" => $data["account_type"] ?? "individual",
                ":company_name" => $data["company_name"] ?? null,
                ":gst_number" => $data["gst_number"] ?? null,
                ":status" => "pending",
                ":email_verified" => 0,
                ":phone_verified" => 0,
                ":aadhar_verified" => 0,
                ":kyc_completed" => 0,
                ":verification_documents" => null,
                ":profile_image" => null,
                ":bio" => null,
                ":created_by" => $userId,
                ":updated_by" => $userId
            ]);

            $this->db->commit();

            return ["success" => true, "customer_code" => $customerCode, "user_id" => $userId];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Customer registration failed: " . $e->getMessage());
            return ["success" => false, "message" => "Registration failed"];
        }
    }

    public function getCustomer($id)
    {
        [$tSql, $tParams] = $this->tenantWhere();
        $stmt = $this->db->prepare("SELECT u.* FROM customers c JOIN users u ON c.user_id = u.id WHERE c.id = :id" . $tSql);
        $params = [":id" => $id];
        if (!empty($tParams)) $params = array_merge($params, $tParams);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getCustomerByEmail($email)
    {
        [$tSql, $tParams] = $this->tenantWhere();
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND role = 'customer'" . $tSql);
        $params = [":email" => $email];
        if (!empty($tParams)) $params = array_merge($params, $tParams);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getCustomerByUserId($userId)
    {
        [$tSql, $tParams] = $this->tenantWhere();
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :userId" . $tSql);
        $params = [":userId" => $userId];
        if (!empty($tParams)) $params = array_merge($params, $tParams);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $data)
    {
        [$tSql, $tParams] = $this->tenantWhere();
        $sql = "UPDATE users SET ";
        $params = [];
        $updates = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $sql .= implode(", ", $updates) . " WHERE id = :customer_id" . $tSql;
        $params[":customer_id"] = $id;
        if (!empty($tParams)) {
            $params = array_merge($params, $tParams);
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function addToWishlist($customerId, $propertyType, $propertyId, $notes = "")
    {
        try {
            $stmt = $this->db->prepare("INSERT IGNORE INTO customer_wishlist (customer_id, property_type, property_id, notes) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$customerId, $propertyType, $propertyId, $notes]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function removeFromWishlist($customerId, $propertyType, $propertyId)
    {
        $stmt = $this->db->prepare("DELETE FROM customer_wishlist WHERE customer_id = ? AND property_type = ? AND property_id = ?");
        return $stmt->execute([$customerId, $propertyType, $propertyId]);
    }

    public function getWishlist($customerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer_wishlist WHERE customer_id = ? ORDER BY added_at DESC");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function createInquiry($data)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO customer_inquiries (
                customer_id, inquiry_type, property_type, property_id, subject, message,
                contact_name, contact_email, contact_phone, status, priority, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            return $stmt->execute([
                $data["customer_id"] ?? null,
                $data["inquiry_type"] ?? "property",
                $data["property_type"] ?? null,
                $data["property_id"] ?? null,
                $data["subject"],
                $data["message"],
                $data["contact_name"] ?? null,
                $data["contact_email"] ?? null,
                $data["contact_phone"] ?? null,
                $data["status"] ?? "pending",
                $data["priority"] ?? "medium"
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getInquiries($customerId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM customer_inquiries WHERE customer_id = ? ORDER BY created_at DESC");
            $stmt->execute([$customerId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function updatePreference($customerId, $key, $value, $type = "string")
    {
        $stmt = $this->db->prepare("INSERT INTO user_preferences (customer_id, preference_key, preference_value, preference_type) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE preference_value = VALUES(preference_value), updated_at = NOW()");
        return $stmt->execute([$customerId, $key, $value, $type]);
    }

    public function getPreferences($customerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM user_preferences WHERE customer_id = ?");
        $stmt->execute([$customerId]);

        $preferences = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $preferences[$row["preference_key"]] = $row["preference_value"];
        }

        return $preferences;
    }

    public function uploadDocument($customerId, $documentType, $documentName, $filePath, $fileSize, $fileType)
    {
        $stmt = $this->db->prepare("INSERT INTO documents (entity_type, entity_id, document_type, url, uploaded_on) VALUES ('customer', ?, ?, ?, NOW())");
        return $stmt->execute([$customerId, $documentType, $filePath]);
    }

    public function getDocuments($customerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM documents WHERE entity_type = 'customer' AND entity_id = ? ORDER BY uploaded_on DESC");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function verifyEmail($email)
    {
        [$tSql, $tParams] = $this->tenantWhere();
        $stmt = $this->db->prepare("UPDATE users SET email_verified = 1 WHERE email = ? AND role = 'customer'" . $tSql);
        $params = [$email];
        if (!empty($tParams)) $params = array_merge($params, $tParams);
        return $stmt->execute($params);
    }

    public function verifyPhone($phone)
    {
        [$tSql, $tParams] = $this->tenantWhere();
        $stmt = $this->db->prepare("UPDATE users SET phone_verified = 1 WHERE phone = ? AND role = 'customer'" . $tSql);
        $params = [$phone];
        if (!empty($tParams)) $params = array_merge($params, $tParams);
        return $stmt->execute($params);
    }

    public function completeKYC($customerId, $documents)
    {
        $this->db->beginTransaction();

        try {
            [$tSql, $tParams] = $this->tenantWhere();
            
            // Update customer KYC status
            $stmt = $this->db->prepare("UPDATE users SET kyc_completed = 1, verification_documents = ? WHERE id = (SELECT user_id FROM customers WHERE id = ?)" . $tSql);
            $params = [json_encode($documents), $customerId];
            if (!empty($tParams)) $params = array_merge($params, $tParams);
            $stmt->execute($params);

            // Mark documents as verified
            foreach ($documents as $docType) {
                $stmt = $this->db->prepare("UPDATE documents SET verification_status = 'verified', verified_at = NOW() WHERE entity_type = 'customer' AND entity_id = ? AND document_type = ?");
                $stmt->execute([$customerId, $docType]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Get all bookings for a customer by user_id.
     * Called by MobileApiController::getCustomerBookings().
     */
    public function getCustomerBookings(int $customerId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT b.id, b.status, b.booking_amount, b.booking_date,
                       p.title AS property_title, p.price AS property_price,
                       p.type AS property_type, p.location, p.city
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                WHERE b.customer_id = :cid
                ORDER BY b.created_at DESC
            ");
            $stmt->execute([':cid' => $customerId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('CustomerService::getCustomerBookings error: ' . $e->getMessage());
            return [];
        }
    }
}