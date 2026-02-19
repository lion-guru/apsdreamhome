
ALTER TABLE leads ADD COLUMN account_name VARCHAR(100) NULL AFTER pincode;
ALTER TABLE leads ADD COLUMN account_number VARCHAR(50) NULL AFTER account_name;
ALTER TABLE leads ADD COLUMN ifsc_code VARCHAR(20) NULL AFTER account_number;
ALTER TABLE leads ADD COLUMN bank_name VARCHAR(100) NULL AFTER ifsc_code;
ALTER TABLE leads ADD COLUMN branch_name VARCHAR(100) NULL AFTER bank_name;
