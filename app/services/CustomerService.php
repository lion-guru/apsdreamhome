<?php

namespace App\Services;

class CustomerService
{
    private $db;

    public function __construct()
    {
        $this->db = new \PDO("mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "");
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function authenticate($email, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE email = :email AND status = 'active'");
        $stmt->execute([":email" => $email]);
        $customer = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($customer && password_verify($password, $customer["password"])) {
            // Update last login
            $updateStmt = $this->db->prepare("UPDATE customers SET last_login = NOW(), login_count = login_count + 1 WHERE id = :id");
            $updateStmt->execute([":id" => $customer["id"]]);

            return $customer;
        }

        return false;
    }

    public function register($data)
    {
        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM customers WHERE email = :email");
        $stmt->execute([":email" => $data["email"]]);

        if ($stmt->fetch()) {
            return ["success" => false, "message" => "Email already exists"];
        }

        // Generate customer code
        $customerCode = "CUS" . str_pad(mt_rand(1, 999999), 6, "0", STR_PAD_LEFT);

        // Insert customer
        $stmt = $this->db->prepare("INSERT INTO customers (
            customer_code, first_name, last_name, email, phone, password,
            date_of_birth, gender, marital_status, occupation, annual_income,
            permanent_address, current_address, city, state, pincode, country,
            preferred_property_type, preferred_location, budget_range_min, budget_range_max,
            preferred_area_min, preferred_area_max, account_type, company_name, gst_number,
            status, created_at
        ) VALUES (
            :customer_code, :first_name, :last_name, :email, :phone, :password,
            :date_of_birth, :gender, :marital_status, :occupation, :annual_income,
            :permanent_address, :current_address, :city, :state, :pincode, :country,
            :preferred_property_type, :preferred_location, :budget_range_min, :budget_range_max,
            :preferred_area_min, :preferred_area_max, :account_type, :company_name, :gst_number,
            :status, NOW()
        )");

        $stmt->execute([
            ":customer_code" => $customerCode,
            ":first_name" => $data["first_name"],
            ":last_name" => $data["last_name"],
            ":email" => $data["email"],
            ":phone" => $data["phone"],
            ":password" => password_hash($data["password"], PASSWORD_DEFAULT),
            ":date_of_birth" => $data["date_of_birth"] ?? null,
            ":gender" => $data["gender"] ?? null,
            ":marital_status" => $data["marital_status"] ?? null,
            ":occupation" => $data["occupation"] ?? null,
            ":annual_income" => $data["annual_income"] ?? null,
            ":status" => "pending",
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
            ":gst_number" => $data["gst_number"] ?? null
        ]);

        return ["success" => true, "customer_code" => $customerCode];
    }

    public function getCustomer($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = :id");
        $stmt->execute([":id" => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getCustomerByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE email = :email");
        $stmt->execute([":email" => $email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $data)
    {
        $sql = "UPDATE customers SET ";
        $params = [":id" => $id];
        $updates = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $sql .= implode(", ", $updates) . " WHERE id = :id";

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
        $stmt = $this->db->prepare("INSERT INTO customer_preferences (customer_id, preference_key, preference_value, preference_type) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE preference_value = VALUES(preference_value), updated_at = NOW()");
        return $stmt->execute([$customerId, $key, $value, $type]);
    }

    public function getPreferences($customerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer_preferences WHERE customer_id = ?");
        $stmt->execute([$customerId]);

        $preferences = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $preferences[$row["preference_key"]] = $row["preference_value"];
        }

        return $preferences;
    }

    public function uploadDocument($customerId, $documentType, $documentName, $filePath, $fileSize, $fileType)
    {
        $stmt = $this->db->prepare("INSERT INTO customer_documents (customer_id, document_type, document_name, file_path, file_size, file_type, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$customerId, $documentType, $documentName, $filePath, $fileSize, $fileType]);
    }

    public function getDocuments($customerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer_documents WHERE customer_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function verifyEmail($email)
    {
        $stmt = $this->db->prepare("UPDATE customers SET email_verified = 1 WHERE email = ?");
        return $stmt->execute([$email]);
    }

    public function verifyPhone($phone)
    {
        $stmt = $this->db->prepare("UPDATE customers SET phone_verified = 1 WHERE phone = ?");
        return $stmt->execute([$phone]);
    }

    public function completeKYC($customerId, $documents)
    {
        $this->db->beginTransaction();

        try {
            // Update customer KYC status
            $stmt = $this->db->prepare("UPDATE customers SET kyc_completed = 1, verification_documents = ? WHERE id = ?");
            $stmt->execute([json_encode($documents), $customerId]);

            // Mark documents as verified
            foreach ($documents as $docType) {
                $stmt = $this->db->prepare("UPDATE customer_documents SET is_verified = 1, verified_at = NOW() WHERE customer_id = ? AND document_type = ?");
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
