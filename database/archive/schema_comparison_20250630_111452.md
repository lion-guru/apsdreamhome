# Database Schema Comparison
Generated: 2025-06-30 11:14:28

## Tables only in database (missing from schema):

- `activity_logs`
- `jwt_blacklist`
- `plots`
- `project_categories`
- `project_category_relations`
- `property`
- `property_feature_mappings`
- `property_features`
- `property_images`
- `saved_searches`
- `user_social_accounts`

## Tables only in schema (not in database):

None

## Table structure differences:

### Table: `about`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(10)`, File has `INT(10) NOT NULL AUTO_INCREMENT`
  - `title`: DB has `VARCHAR(100)`, File has `VARCHAR(100) NOT NULL`
  - `content`: DB has `LONGTEXT`, File has `LONGTEXT NOT NULL`
  - `image`: DB has `VARCHAR(300)`, File has `VARCHAR(300) NOT NULL`

### Table: `admin`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `auser`: DB has `VARCHAR(100)`, File has `(`AUSER`)`
  - `apass`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `role`: DB has `VARCHAR(50)`, File has `VARCHAR(50) NOT NULL`
  - `status`: DB has `VARCHAR(20)`, File has `VARCHAR(20) DEFAULT 'ACTIVE'`
  - `email`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `phone`: DB has `VARCHAR(20)`, File has `VARCHAR(20) DEFAULT NULL`

### Table: `admin_activity_log`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `admin_activity_log_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `admin_id`: DB has `INT(11)`, File has `(`ADMIN_ID`)`
  - `username`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `role`: DB has `VARCHAR(20)`, File has `VARCHAR(20) DEFAULT NULL`
  - `action`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `details`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `ip_address`: DB has `VARCHAR(45)`, File has `VARCHAR(45) DEFAULT NULL`
  - `user_agent`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `agents`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(100)`, File has `VARCHAR(100) NOT NULL`
  - `sales`: DB has `INT(11)`, File has `INT(11) DEFAULT 0`

### Table: `ai_chatbot_config`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `provider`: DB has `VARCHAR(50)`, File has `VARCHAR(50) NOT NULL`
  - `api_key`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `webhook_url`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `ai_chatbot_interactions`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `ai_chatbot_interactions_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `query`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `response`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `satisfaction_score`: DB has `DECIMAL(2,1)`, File has `DECIMAL(2`
  - `response_time`: DB has `DECIMAL(5,2)`, File has `DECIMAL(5`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `ai_config`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `ai_config_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `feature`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `enabled`: DB has `TINYINT(1)`, File has `TINYINT(1) DEFAULT 1`
  - `config_json`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `updated_by`: DB has `INT(11)`, File has `(`UPDATED_BY`)`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()`

### Table: `ai_lead_scores`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `lead_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `score`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `scored_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `ai_logs`
- **Structure differs** between database and schema file
  - **Columns only in database**: `input_text`, `ai_response`
  - **Columns only in schema file**: `target_id`, `target_type`, `details`, `idx_ai_logs_created_at`, `fk_ai_logs_user`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `action`: DB has `VARCHAR(100)`, File has `VARCHAR(100) NOT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `api_developers`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `dev_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `email`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `api_key`: DB has `VARCHAR(64)`, File has `VARCHAR(64) NOT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'ACTIVE'`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `api_integrations`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `service_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `api_url`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `api_key`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'ACTIVE'`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `api_keys`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `api_keys_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `api_key`: DB has `VARCHAR(64)`, File has `(`API_KEY`)`
  - `name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `permissions`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `rate_limit`: DB has `INT(11)`, File has `INT(11) DEFAULT 100`
  - `status`: DB has `ENUM('ACTIVE','REVOKED')`, File has `ENUM('ACTIVE'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP()`
  - `last_used_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NULL DEFAULT NULL`

### Table: `api_rate_limits`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `api_key_timestamp`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `api_key`: DB has `VARCHAR(255)`, File has `(`API_KEY`)`
  - `timestamp`: DB has `INT(11)`, File has `(`TIMESTAMP`)`

### Table: `api_request_logs`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `api_request_logs_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `api_key_id`: DB has `INT(11)`, File has `(`API_KEY_ID`)`
  - `endpoint`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `request_time`: DB has `TIMESTAMP`, File has `(`REQUEST_TIME`)`
  - `ip_address`: DB has `VARCHAR(45)`, File has `VARCHAR(45) DEFAULT NULL`
  - `user_agent`: DB has `TEXT`, File has `TEXT DEFAULT NULL`

### Table: `api_sandbox`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `dev_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `endpoint`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `payload`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'PENDING'`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `api_usage`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `dev_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `api_key`: DB has `VARCHAR(64)`, File has `VARCHAR(64) DEFAULT NULL`
  - `endpoint`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `usage_count`: DB has `INT(11)`, File has `INT(11) DEFAULT 1`
  - `timestamp`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `app_store`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `app_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `provider`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `app_url`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `price`: DB has `DECIMAL(10,2)`, File has `DECIMAL(10`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `ar_vr_tours`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `property_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `asset_url`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `asset_type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `uploaded_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `associate_levels`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `commission_percent`: DB has `DECIMAL(5,2)`, File has `DECIMAL(5`

### Table: `associates`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `associates_ibfk_1`, `associates_ibfk_2`, `associates_ibfk_3`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `email`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `phone`: DB has `VARCHAR(20)`, File has `VARCHAR(20) DEFAULT NULL`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `level`: DB has `INT(11)`, File has `(`LEVEL`)`
  - `parent_id`: DB has `INT(11)`, File has `(`PARENT_ID`)`
  - `commission_percent`: DB has `DECIMAL(5,2)`, File has `DECIMAL(5`
  - `join_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `status`: DB has `ENUM('ACTIVE','INACTIVE')`, File has `ENUM('ACTIVE'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `attendance`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `attendance_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `employee_id`: DB has `INT(11)`, File has `(`EMPLOYEE_ID`)`
  - `date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `in_time`: DB has `TIME`, File has `TIME DEFAULT NULL`
  - `out_time`: DB has `TIME`, File has `TIME DEFAULT NULL`
  - `status`: DB has `ENUM('PRESENT','ABSENT','LEAVE')`, File has `ENUM('PRESENT'`

### Table: `audit_access_log`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `accessed_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`
  - `action`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `user_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `details`: DB has `TEXT`, File has `TEXT DEFAULT NULL`

### Table: `audit_log`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `fk_audit_log_user`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `action`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `details`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `bookings`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `bookings_ibfk_1`, `bookings_ibfk_2`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `property_id`: DB has `INT(11)`, File has `(`PROPERTY_ID`)`
  - `booking_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `amount`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `customer_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `property_type`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `installment_plan`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()`

### Table: `chat_messages`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `sender_email`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `message`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `city`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `city_ibfk_1`
  - **Column type differences**:
  - `cid`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `cname`: DB has `VARCHAR(100)`, File has `VARCHAR(100) NOT NULL`
  - `sid`: DB has `INT(11)`, File has `(`SID`)`

### Table: `commission_payouts`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `commission_payouts_ibfk_1`, `commission_payouts_ibfk_2`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `associate_id`: DB has `INT(11)`, File has `(`ASSOCIATE_ID`)`
  - `transaction_id`: DB has `INT(11)`, File has `(`TRANSACTION_ID`)`
  - `amount`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `paid_on`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `status`: DB has `ENUM('PENDING','PAID','FAILED')`, File has `ENUM('PENDING'`

### Table: `commission_transactions`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `transaction_id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `associate_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `booking_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `business_amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `commission_amount`: DB has `DECIMAL(10,2)`, File has `DECIMAL(10`
  - `commission_percentage`: DB has `DECIMAL(4,2)`, File has `DECIMAL(4`
  - `level_difference_amount`: DB has `DECIMAL(10,2)`, File has `DECIMAL(10`
  - `upline_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `transaction_date`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `status`: DB has `ENUM('PENDING','PAID','CANCELLED')`, File has `ENUM('PENDING'`

### Table: `communications`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `lead_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `type`: DB has `ENUM('CALL','EMAIL','MEETING','WHATSAPP','SMS')`, File has `ENUM('CALL'`
  - `subject`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `notes`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `communication_date`: DB has `DATETIME`, File has `DATETIME DEFAULT NULL`
  - `user_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `companies`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(100)`, File has `VARCHAR(100) NOT NULL`
  - `address`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `gstin`: DB has `VARCHAR(20)`, File has `VARCHAR(20) DEFAULT NULL`
  - `pan`: DB has `VARCHAR(20)`, File has `VARCHAR(20) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `company_employees`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `company_employees_ibfk_1`, `company_employees_ibfk_2`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `company_id`: DB has `INT(11)`, File has `(`COMPANY_ID`)`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `position`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `salary`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `join_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `status`: DB has `ENUM('ACTIVE','INACTIVE')`, File has `ENUM('ACTIVE'`

### Table: `customer_documents`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `customer_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `doc_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'UPLOADED'`
  - `uploaded_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`
  - `blockchain_hash`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`

### Table: `customer_journeys`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `customer_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `journey`: DB has `LONGTEXT`, File has `LONGTEXT CHARACTER SET UTF8MB4 COLLATE UTF8MB4_BIN DEFAULT NULL CHECK (JSON_VALID(`JOURNEY`))`
  - `started_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`
  - `last_touch_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `customers`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `customers_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `customer_type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `kyc_status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `data_stream_events`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `event_type`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `payload`: DB has `LONGTEXT`, File has `LONGTEXT CHARACTER SET UTF8MB4 COLLATE UTF8MB4_BIN DEFAULT NULL CHECK (JSON_VALID(`PAYLOAD`))`
  - `streamed_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `documents`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `documents_ibfk_1`, `documents_ibfk_2`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `property_id`: DB has `INT(11)`, File has `(`PROPERTY_ID`)`
  - `type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `url`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `uploaded_on`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `drive_file_id`: DB has `VARCHAR(128)`, File has `VARCHAR(128) DEFAULT NULL`

### Table: `emi`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `emi_ibfk_1`, `emi_ibfk_2`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `property_id`: DB has `INT(11)`, File has `(`PROPERTY_ID`)`
  - `amount`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `due_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `paid_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `status`: DB has `ENUM('PENDING','PAID','OVERDUE')`, File has `ENUM('PENDING'`

### Table: `emi_installments`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `idx_emi_plan`, `idx_payment_status`, `idx_due_date`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `emi_plan_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `installment_number`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `principal_amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `interest_amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `due_date`: DB has `DATE`, File has `DATE NOT NULL`
  - `payment_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `payment_status`: DB has `ENUM('PENDING','PAID','OVERDUE')`, File has `ENUM('PENDING'`
  - `payment_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `reminder_sent`: DB has `TINYINT(1)`, File has `TINYINT(1) DEFAULT 0`
  - `last_reminder_date`: DB has `DATETIME`, File has `DATETIME DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP()`

### Table: `emi_plans`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `idx_property`, `idx_customer`, `idx_status`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `property_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `customer_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `total_amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `interest_rate`: DB has `DECIMAL(5,2)`, File has `DECIMAL(5`
  - `tenure_months`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `emi_amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `down_payment`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `start_date`: DB has `DATE`, File has `DATE NOT NULL`
  - `end_date`: DB has `DATE`, File has `DATE NOT NULL`
  - `status`: DB has `ENUM('ACTIVE','COMPLETED','DEFAULTED','CANCELLED')`, File has `ENUM('ACTIVE'`
  - `foreclosure_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `foreclosure_amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `foreclosure_payment_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP()`
  - `created_by`: DB has `INT(11)`, File has `INT(11) NOT NULL`

### Table: `employees`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `email`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `phone`: DB has `VARCHAR(20)`, File has `VARCHAR(20) DEFAULT NULL`
  - `role`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `salary`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `join_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `status`: DB has `ENUM('ACTIVE','INACTIVE')`, File has `ENUM('ACTIVE'`
  - `password`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `expenses`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `source`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `expense_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `description`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `farmers`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `farmers_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `land_area`: DB has `DECIMAL(10,2)`, File has `DECIMAL(10`
  - `location`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `kyc_doc`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`

### Table: `feedback`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `feedback_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `message`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `status`: DB has `VARCHAR(20)`, File has `VARCHAR(20) DEFAULT 'NEW'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `feedback_tickets`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `message`: DB has `TEXT`, File has `TEXT NOT NULL`
  - `status`: DB has `ENUM('OPEN','CLOSED')`, File has `ENUM('OPEN'`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `foreclosure_logs`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `idx_emi_plan`, `idx_status`, `idx_attempted_at`, `foreclosure_logs_ibfk_1`, `foreclosure_logs_ibfk_2`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `emi_plan_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `status`: DB has `ENUM('SUCCESS','FAILED')`, File has `ENUM('SUCCESS'`
  - `message`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `attempted_by`: DB has `INT(11)`, File has `(`ATTEMPTED_BY`)`
  - `attempted_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `gallery`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `image_path`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `caption`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `status`: DB has `ENUM('ACTIVE','INACTIVE')`, File has `ENUM('ACTIVE'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `global_payments`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `client`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `currency`: DB has `VARCHAR(10)`, File has `VARCHAR(10) DEFAULT 'INR'`
  - `purpose`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'PENDING'`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `inventory_log`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `plot_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `action`: DB has `ENUM('CREATED','BOOKED','SOLD','TRANSFERRED','RELEASED')`, File has `ENUM('CREATED'`
  - `user_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `note`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `action_date`: DB has `DATETIME`, File has `DATETIME DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `iot_device_events`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `device_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `event_type`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `event_value`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `event_time`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `iot_devices`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `property_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `device_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `device_type`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'ACTIVE'`
  - `last_seen`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `land_purchases`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `land_purchases_ibfk_1`, `land_purchases_ibfk_2`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `farmer_id`: DB has `INT(11)`, File has `(`FARMER_ID`)`
  - `property_id`: DB has `INT(11)`, File has `(`PROPERTY_ID`)`
  - `purchase_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `amount`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `registry_no`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `agreement_doc`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`

### Table: `leads`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `leads_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `contact`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `source`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `assigned_to`: DB has `INT(11)`, File has `(`ASSIGNED_TO`)`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `notes`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `converted_at`: DB has `DATETIME`, File has `DATETIME DEFAULT NULL`
  - `converted_amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`

### Table: `leaves`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `employee_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `leave_type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `from_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `to_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `status`: DB has `ENUM('PENDING','APPROVED','REJECTED')`, File has `ENUM('PENDING'`
  - `remarks`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `legal_documents`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `file_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `file_url`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `review_status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'PENDING'`
  - `ai_summary`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `ai_flags`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `uploaded_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `marketing_campaigns`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `type`: DB has `ENUM('EMAIL','SMS')`, File has `ENUM('EMAIL'`
  - `message`: DB has `TEXT`, File has `TEXT NOT NULL`
  - `scheduled_at`: DB has `DATETIME`, File has `DATETIME DEFAULT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'SCHEDULED'`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `marketing_strategies`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `title`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `description`: DB has `TEXT`, File has `TEXT NOT NULL`
  - `image_url`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `active`: DB has `TINYINT(1)`, File has `TINYINT(1) DEFAULT 1`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()`

### Table: `marketplace_apps`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `app_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `provider`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `app_url`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `migrations`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `unique_migration`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `version`: DB has `VARCHAR(20)`, File has `VARCHAR(20) NOT NULL`
  - `migration_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `applied_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `mlm_commission_ledger`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `mlm_commission_ledger_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `associate_id`: DB has `INT(11)`, File has `(`ASSOCIATE_ID`)`
  - `commission_amount`: DB has `DECIMAL(10,2)`, File has `DECIMAL(10`
  - `commission_date`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`
  - `description`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `status`: DB has `ENUM('PENDING','PAID','CANCELLED')`, File has `ENUM('PENDING'`

### Table: `mlm_commissions`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `user_name`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `transaction_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `property_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `commission_amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `commission_type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'PAID'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `mlm_tree`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `mlm_tree_ibfk_1`, `mlm_tree_ibfk_2`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `parent_id`: DB has `INT(11)`, File has `(`PARENT_ID`)`
  - `level`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `join_date`: DB has `DATE`, File has `DATE DEFAULT NULL`

### Table: `mobile_devices`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `device_user`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `push_token`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `platform`: DB has `VARCHAR(20)`, File has `VARCHAR(20) DEFAULT NULL`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `news`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `title`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `date`: DB has `DATE`, File has `DATE NOT NULL`
  - `summary`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `image`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `content`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `notification_logs`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `notification_logs_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `notification_id`: DB has `INT(11)`, File has `(`NOTIFICATION_ID`)`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) NOT NULL`
  - `error_message`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `notification_settings`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `unique_user_type`, `notification_settings_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) NOT NULL`
  - `email_enabled`: DB has `TINYINT(1)`, File has `TINYINT(1) DEFAULT 1`
  - `push_enabled`: DB has `TINYINT(1)`, File has `TINYINT(1) DEFAULT 1`
  - `sms_enabled`: DB has `TINYINT(1)`, File has `TINYINT(1) DEFAULT 0`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP()`

### Table: `notification_templates`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `unique_type`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) NOT NULL`
  - `title_template`: DB has `TEXT`, File has `TEXT NOT NULL`
  - `message_template`: DB has `TEXT`, File has `TEXT NOT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP()`

### Table: `notifications`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `notifications_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `message`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `read_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NULL DEFAULT NULL`

### Table: `opportunities`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `lead_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `stage`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `value`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `expected_close`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `status`: DB has `ENUM('OPEN','WON','LOST')`, File has `ENUM('OPEN'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `partner_certification`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `partner_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `app_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `cert_status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'PENDING'`
  - `revenue_share`: DB has `INT(11)`, File has `INT(11) DEFAULT 0`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `partner_rewards`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `partner_email`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `points`: DB has `INT(11)`, File has `INT(11) DEFAULT 0`
  - `description`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `password_resets`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `email`: DB has `VARCHAR(255)`, File has `(`EMAIL`)`
  - `token`: DB has `VARCHAR(255)`, File has `(`TOKEN`)`
  - `created_at`: DB has `DATETIME`, File has `DATETIME NOT NULL`
  - `expires_at`: DB has `DATETIME`, File has `DATETIME NOT NULL`
  - `used`: DB has `TINYINT(1)`, File has `TINYINT(1) NOT NULL DEFAULT 0`

### Table: `payment_gateway_config`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `provider`: DB has `VARCHAR(50)`, File has `VARCHAR(50) NOT NULL`
  - `api_key`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `api_secret`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `payment_logs`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `payment_logs_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `payment_method`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `amount`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `payments`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `booking_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `payment_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `method`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `status`: DB has `ENUM('PENDING','COMPLETED','FAILED')`, File has `ENUM('PENDING'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `permissions`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `action`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `description`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`

### Table: `project_amenities`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `project_amenities_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `project_id`: DB has `INT(11)`, File has `(`PROJECT_ID`)`
  - `icon_path`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `label`: DB has `VARCHAR(100)`, File has `VARCHAR(100) NOT NULL`

### Table: `project_gallery`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `project_gallery_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `project_id`: DB has `INT(11)`, File has `(`PROJECT_ID`)`
  - `image_path`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `caption`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `drive_file_id`: DB has `VARCHAR(128)`, File has `VARCHAR(128) DEFAULT NULL`

### Table: `projects`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `location`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `description`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `status`: DB has `ENUM('ACTIVE','INACTIVE')`, File has `ENUM('ACTIVE'`
  - `builder_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `project_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `start_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `end_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `budget`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()`
  - `brochure_path`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `youtube_url`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `brochure_drive_id`: DB has `VARCHAR(128)`, File has `VARCHAR(128) DEFAULT NULL`

### Table: `properties`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `properties_ibfk_1`, `properties_ibfk_2`, `properties_ibfk_3`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `project_id`: DB has `INT(11)`, File has `(`PROJECT_ID`)`
  - `type_id`: DB has `INT(11)`, File has `(`TYPE_ID`)`
  - `address`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `area`: DB has `DECIMAL(10,2)`, File has `DECIMAL(10`
  - `price`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `status`: DB has `VARCHAR(50)`, File has `ENUM('AVAILABLE'`
  - `owner_id`: DB has `INT(11)`, File has `(`OWNER_ID`)`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()`

### Table: `property_ownership`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `property_ownership_ibfk_1`, `property_ownership_ibfk_2`, `property_ownership_ibfk_3`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `property_id`: DB has `INT(11)`, File has `(`PROPERTY_ID`)`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `company_id`: DB has `INT(11)`, File has `(`COMPANY_ID`)`
  - `share`: DB has `DECIMAL(5,2)`, File has `DECIMAL(5`
  - `from_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `to_date`: DB has `DATE`, File has `DATE DEFAULT NULL`

### Table: `property_types`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(50)`, File has `(`NAME`)`
  - `description`: DB has `TEXT`, File has `TEXT DEFAULT NULL`

### Table: `property_visits`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `property_visits_ibfk_1`, `property_visits_ibfk_2`, `property_visits_ibfk_3`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `customer_id`: DB has `INT(11)`, File has `(`CUSTOMER_ID`)`
  - `property_id`: DB has `INT(11)`, File has `(`PROPERTY_ID`)`
  - `lead_id`: DB has `INT(11)`, File has `(`LEAD_ID`)`
  - `visit_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `visit_time`: DB has `TIME`, File has `TIME DEFAULT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `feedback`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `rating`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()`

### Table: `rent_payments`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `rent_payments_ibfk_1`, `rent_payments_ibfk_2`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `rental_property_id`: DB has `INT(11)`, File has `(`RENTAL_PROPERTY_ID`)`
  - `tenant_id`: DB has `INT(11)`, File has `(`TENANT_ID`)`
  - `amount`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `due_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `paid_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `status`: DB has `ENUM('PENDING','PAID','OVERDUE')`, File has `ENUM('PENDING'`

### Table: `rental_properties`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `rental_properties_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `owner_id`: DB has `INT(11)`, File has `(`OWNER_ID`)`
  - `address`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `rent_amount`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `status`: DB has `ENUM('AVAILABLE','RENTED','INACTIVE')`, File has `ENUM('AVAILABLE'`

### Table: `reports`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `title`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) NOT NULL`
  - `content`: DB has `TEXT`, File has `TEXT NOT NULL`
  - `file_path`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `generated_for_month`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `generated_for_year`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP()`

### Table: `resale_commissions`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `resale_commissions_ibfk_1`, `resale_commissions_ibfk_2`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `associate_id`: DB has `INT(11)`, File has `(`ASSOCIATE_ID`)`
  - `resale_property_id`: DB has `INT(11)`, File has `(`RESALE_PROPERTY_ID`)`
  - `amount`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `paid_on`: DB has `DATE`, File has `DATE DEFAULT NULL`

### Table: `resale_properties`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `resale_properties_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `owner_id`: DB has `INT(11)`, File has `(`OWNER_ID`)`
  - `details`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `price`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `status`: DB has `ENUM('AVAILABLE','SOLD','INACTIVE')`, File has `ENUM('AVAILABLE'`

### Table: `reward_history`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `associate_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `reward_type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `reward_value`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `reward_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `description`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `role_change_approvals`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `role_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `action`: DB has `ENUM('ASSIGN','REMOVE')`, File has `ENUM('ASSIGN'`
  - `requested_by`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `status`: DB has `ENUM('PENDING','APPROVED','REJECTED')`, File has `ENUM('PENDING'`
  - `requested_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`
  - `decided_by`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `decided_at`: DB has `DATETIME`, File has `DATETIME DEFAULT NULL`

### Table: `role_permissions`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `role_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `permission_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`

### Table: `roles`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(50)`, File has `(`NAME`)`

### Table: `saas_instances`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `client_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `domain`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'ACTIVE'`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `salaries`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `salaries_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `employee_id`: DB has `INT(11)`, File has `(`EMPLOYEE_ID`)`
  - `month`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `year`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `amount`: DB has `DECIMAL(15,2)`, File has `DECIMAL(15`
  - `status`: DB has `ENUM('PENDING','PAID','FAILED')`, File has `ENUM('PENDING'`
  - `paid_on`: DB has `DATE`, File has `DATE DEFAULT NULL`

### Table: `salary_plan`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `associate_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `level`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `salary_amount`: DB has `DECIMAL(12,2)`, File has `DECIMAL(12`
  - `payout_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `status`: DB has `ENUM('PENDING','PAID')`, File has `ENUM('PENDING'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `settings`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `key`: DB has `VARCHAR(100)`, File has `VARCHAR(100) NOT NULL`
  - `value`: DB has `TEXT`, File has `TEXT DEFAULT NULL`

### Table: `site_settings`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `setting_name`: DB has `VARCHAR(100)`, File has `(`SETTING_NAME`)`
  - `value`: DB has `TEXT`, File has `TEXT NOT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()`

### Table: `smart_contracts`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `agreement_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `parties`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `terms`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'PENDING'`
  - `blockchain_txn`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `state`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `sid`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `sname`: DB has `VARCHAR(100)`, File has `(`SNAME`)`

### Table: `support_tickets`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `support_tickets_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `subject`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `message`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `status`: DB has `VARCHAR(20)`, File has `VARCHAR(20) DEFAULT 'OPEN'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `tasks`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `title`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `description`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `assigned_to`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `assigned_by`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `due_date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `ENUM('PENDING'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `team`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(100)`, File has `VARCHAR(100) NOT NULL`
  - `designation`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `bio`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `photo`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `status`: DB has `ENUM('ACTIVE','INACTIVE')`, File has `ENUM('ACTIVE'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `team_hierarchy`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `associate_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `upline_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `level`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `testimonials`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `client_name`: DB has `VARCHAR(100)`, File has `VARCHAR(100) NOT NULL`
  - `testimonial`: DB has `TEXT`, File has `TEXT NOT NULL`
  - `client_photo`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `status`: DB has `ENUM('ACTIVE','INACTIVE')`, File has `ENUM('ACTIVE'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `third_party_integrations`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) NOT NULL`
  - `api_token`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `transactions`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `transactions_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `(`USER_ID`)`
  - `type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `amount`: DB has `DECIMAL(10,2)`, File has `DECIMAL(15`
  - `date`: DB has `DATE`, File has `DATE DEFAULT NULL`
  - `description`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `ref_id`: DB has `VARCHAR(100)`, File has `VARCHAR(100) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()`

### Table: `upload_audit_log`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `event_type`: DB has `VARCHAR(64)`, File has `VARCHAR(64) NOT NULL`
  - `entity_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `entity_table`: DB has `VARCHAR(64)`, File has `VARCHAR(64) NOT NULL`
  - `file_name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `drive_file_id`: DB has `VARCHAR(128)`, File has `VARCHAR(128) DEFAULT NULL`
  - `uploader`: DB has `VARCHAR(128)`, File has `VARCHAR(128) NOT NULL`
  - `slack_status`: DB has `VARCHAR(32)`, File has `VARCHAR(32) DEFAULT NULL`
  - `telegram_status`: DB has `VARCHAR(32)`, File has `VARCHAR(32) DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `user_preferences`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `unique_user_preference`, `idx_user_preferences_key`, `user_preferences_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `preference_key`: DB has `VARCHAR(100)`, File has `VARCHAR(100) NOT NULL`
  - `preference_value`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()`

### Table: `user_roles`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `user_roles_ibfk_1`, `user_roles_ibfk_2`
  - **Column type differences**:
  - `user_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `role_id`: DB has `INT(11)`, File has `(`ROLE_ID`)`

### Table: `user_sessions`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `user_id`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `login_time`: DB has `DATETIME`, File has `DATETIME DEFAULT NULL`
  - `logout_time`: DB has `DATETIME`, File has `DATETIME DEFAULT NULL`
  - `ip_address`: DB has `VARCHAR(45)`, File has `VARCHAR(45) DEFAULT NULL`
  - `status`: DB has `ENUM('ACTIVE','ENDED')`, File has `ENUM('ACTIVE'`

### Table: `users`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `idx_profile_picture`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(100)`, File has `VARCHAR(100) NOT NULL`
  - `email`: DB has `VARCHAR(255)`, File has `(`EMAIL`)`
  - `profile_picture`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `phone`: DB has `VARCHAR(20)`, File has `(`PHONE`)`
  - `type`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `password`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `status`: DB has `ENUM('ACTIVE','INACTIVE')`, File has `ENUM('ACTIVE'`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()`
  - `api_access`: DB has `TINYINT(1)`, File has `TINYINT(1) DEFAULT 0`
  - `api_rate_limit`: DB has `INT(11)`, File has `INT(11) DEFAULT 1000`

### Table: `visit_availability`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `unique_availability`, `visit_availability_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `property_id`: DB has `INT(11)`, File has `INT(11) NOT NULL`
  - `day_of_week`: DB has `TINYINT(4)`, File has `TINYINT(4) NOT NULL CHECK (`DAY_OF_WEEK` BETWEEN 0 AND 6)`
  - `start_time`: DB has `TIME`, File has `TIME NOT NULL`
  - `end_time`: DB has `TIME`, File has `TIME NOT NULL`
  - `max_visits_per_slot`: DB has `INT(11)`, File has `INT(11) DEFAULT 1`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`
  - `updated_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP()`

### Table: `visit_reminders`
- **Structure differs** between database and schema file
  - **Columns only in schema file**: `idx_reminder_status`, `visit_reminders_ibfk_1`
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `visit_id`: DB has `INT(11)`, File has `(`VISIT_ID`)`
  - `reminder_type`: DB has `ENUM('24H_BEFORE','1H_BEFORE','FEEDBACK_REQUEST')`, File has `ENUM('24H_BEFORE'`
  - `status`: DB has `ENUM('PENDING','SENT','FAILED')`, File has `ENUM('PENDING'`
  - `scheduled_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()`
  - `sent_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NULL DEFAULT NULL`
  - `error_message`: DB has `TEXT`, File has `TEXT DEFAULT NULL`
  - `created_at`: DB has `TIMESTAMP`, File has `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()`

### Table: `voice_assistant_config`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `provider`: DB has `VARCHAR(50)`, File has `VARCHAR(50) NOT NULL`
  - `api_key`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `whatsapp_automation_config`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `provider`: DB has `VARCHAR(50)`, File has `VARCHAR(50) NOT NULL`
  - `api_key`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `sender_number`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `workflow_automations`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `provider`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT NULL`
  - `webhook_url`: DB has `VARCHAR(255)`, File has `VARCHAR(255) DEFAULT NULL`
  - `status`: DB has `VARCHAR(50)`, File has `VARCHAR(50) DEFAULT 'ACTIVE'`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

### Table: `workflows`
- **Structure differs** between database and schema file
  - **Column type differences**:
  - `id`: DB has `INT(11)`, File has `INT(11) NOT NULL AUTO_INCREMENT`
  - `name`: DB has `VARCHAR(255)`, File has `VARCHAR(255) NOT NULL`
  - `definition`: DB has `LONGTEXT`, File has `LONGTEXT CHARACTER SET UTF8MB4 COLLATE UTF8MB4_BIN DEFAULT NULL CHECK (JSON_VALID(`DEFINITION`))`
  - `created_by`: DB has `INT(11)`, File has `INT(11) DEFAULT NULL`
  - `created_at`: DB has `DATETIME`, File has `DATETIME DEFAULT CURRENT_TIMESTAMP()`

## Foreign Key Differences:

### Table: `activity_logs`
  - Missing in schema: `user_id` → `users(id)` ON UPDATE RESTRICT ON DELETE CASCADE

### Table: `bookings`
  - Missing in schema: `user_id` → `users(id)` ON UPDATE RESTRICT ON DELETE CASCADE
  - Missing in schema: `property_id` → `properties(id)` ON UPDATE RESTRICT ON DELETE CASCADE
  - Missing in schema: `customer_id` → `users(id)` ON UPDATE RESTRICT ON DELETE CASCADE
  - Extra in schema: `bookings_ibfk_1` → `users` ON UPDATE RESTRICT ON DELETE RESTRICT
  - Extra in schema: `bookings_ibfk_2` → `properties` ON UPDATE RESTRICT ON DELETE RESTRICT

### Table: `documents`
  - Missing in schema: `user_id` → `users(id)` ON UPDATE RESTRICT ON DELETE CASCADE
  - Missing in schema: `property_id` → `properties(id)` ON UPDATE RESTRICT ON DELETE CASCADE
  - Extra in schema: `documents_ibfk_1` → `users` ON UPDATE RESTRICT ON DELETE RESTRICT
  - Extra in schema: `documents_ibfk_2` → `properties` ON UPDATE RESTRICT ON DELETE RESTRICT

### Table: `leads`
  - Missing in schema: `assigned_to` → `users(id)` ON UPDATE RESTRICT ON DELETE CASCADE
  - Extra in schema: `leads_ibfk_1` → `users` ON UPDATE RESTRICT ON DELETE RESTRICT

### Table: `plots`
  - Missing in schema: `project_id` → `projects(id)` ON UPDATE RESTRICT ON DELETE CASCADE
  - Missing in schema: `customer_id` → `users(id)` ON UPDATE RESTRICT ON DELETE SET NULL
  - Missing in schema: `associate_id` → `associates(id)` ON UPDATE RESTRICT ON DELETE SET NULL

### Table: `project_categories`
  - Missing in schema: `created_by` → `users(id)` ON UPDATE RESTRICT ON DELETE SET NULL

### Table: `project_category_relations`
  - Missing in schema: `project_id` → `projects(id)` ON UPDATE RESTRICT ON DELETE CASCADE
  - Missing in schema: `category_id` → `project_categories(id)` ON UPDATE RESTRICT ON DELETE CASCADE

### Table: `property_feature_mappings`
  - Missing in schema: `property_id` → `properties(id)` ON UPDATE RESTRICT ON DELETE CASCADE
  - Missing in schema: `feature_id` → `property_features(id)` ON UPDATE RESTRICT ON DELETE CASCADE

### Table: `property_images`
  - Missing in schema: `property_id` → `properties(id)` ON UPDATE RESTRICT ON DELETE CASCADE

### Table: `saved_searches`
  - Missing in schema: `user_id` → `users(id)` ON UPDATE RESTRICT ON DELETE CASCADE

### Table: `user_social_accounts`
  - Missing in schema: `user_id` → `users(id)` ON UPDATE RESTRICT ON DELETE CASCADE

