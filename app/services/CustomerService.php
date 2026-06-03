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
        $stmt = $this->db->prepare("SELECT u.* FROM users c JOIN users u ON c.user_id = u.id WHERE c.id = :id");
        $stmt->execute([":id" => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getCustomerByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND role = 'customer'");
        $stmt->execute([":email" => $email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getCustomerByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :userId");
        $stmt->execute([":userId" => $userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $data)
    {
        $sql = "UPDATE users SET ";
        $params = [];
        $updates = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $sql .= implode(", ", $updates) . " WHERE id = (SELECT user_id FROM users WHERE id = :customer_id)";
        $params[":customer_id"] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function addToWishlist($customerId, $propertyType, $propertyId, $notes = "")
    {
        $stmt = $this->db->prepare("INSERT IGNORE INTO customer_wishlist (customer_id, property_type, property_id, notes) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$customerId, $propertyType, $propertyId, $notes]);
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
    }

    public function getInquiries($customerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer_inquiries WHERE customer_id = ? ORDER BY created_at DESC");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updatePreference($customerId, $key, $value, $type = "string")
    {
        $stmt = $this->db->prepare("INSERT INTO users (customer_id, preference_key, preference_value, preference_type) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE preference_value = VALUES(preference_value), updated_at = NOW()");
        return $stmt->execute([$customerId, $key, $value, $type]);
    }

    public function getPreferences($customerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE customer_id = ?");
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
        $stmt = $this->db->prepare("UPDATE users SET email_verified = 1 WHERE email = ? AND role = 'customer'");
        return $stmt->execute([$email]);
    }

    public function verifyPhone($phone)
    {
        $stmt = $this->db->prepare("UPDATE users SET phone_verified = 1 WHERE phone = ? AND role = 'customer'");
        return $stmt->execute([$phone]);
    }

    public function completeKYC($customerId, $documents)
    {
        $this->db->beginTransaction();

        try {
            // Update customer KYC status
            $stmt = $this->db->prepare("UPDATE users SET kyc_completed = 1, verification_documents = ? WHERE id = (SELECT user_id FROM users WHERE id = ?)");
            $stmt->execute([json_encode($documents), $customerId]);

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
}