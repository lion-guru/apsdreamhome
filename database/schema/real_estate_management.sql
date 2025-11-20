-- Real Estate Project Management System Database Schema

-- Land Purchase Management
CREATE TABLE IF NOT EXISTS land_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    location TEXT NOT NULL,
    area DECIMAL(10,2) NOT NULL,
    purchase_date DATE NOT NULL,
    purchase_price DECIMAL(15,2) NOT NULL,
    owner_details TEXT,
    legal_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS land_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    land_id INT,
    document_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (land_id) REFERENCES land_records(id)
);

-- Project Development Management
CREATE TABLE IF NOT EXISTS projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    land_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE,
    expected_end_date DATE,
    status VARCHAR(50),
    FOREIGN KEY (land_id) REFERENCES land_records(id)
);

CREATE TABLE IF NOT EXISTS project_milestones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE,
    completion_percentage INT DEFAULT 0,
    status VARCHAR(50),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

CREATE TABLE IF NOT EXISTS project_tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    milestone_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to INT,
    due_date DATE,
    status VARCHAR(50),
    FOREIGN KEY (milestone_id) REFERENCES project_milestones(id)
);

-- CRM Management
CREATE TABLE IF NOT EXISTS properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(50),
    price DECIMAL(15,2),
    status VARCHAR(50),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

CREATE TABLE IF NOT EXISTS clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS leads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT,
    property_id INT,
    status VARCHAR(50),
    notes TEXT,
    follow_up_date DATE,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

-- Financial Management
CREATE TABLE IF NOT EXISTS expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    category VARCHAR(50),
    amount DECIMAL(15,2),
    description TEXT,
    date DATE,
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

CREATE TABLE IF NOT EXISTS invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT,
    property_id INT,
    amount DECIMAL(15,2),
    status VARCHAR(50),
    due_date DATE,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

-- Construction Workflow
CREATE TABLE IF NOT EXISTS resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    name VARCHAR(255),
    type VARCHAR(50),
    quantity INT,
    status VARCHAR(50),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

CREATE TABLE IF NOT EXISTS daily_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    date DATE,
    description TEXT,
    weather_conditions VARCHAR(255),
    work_completed TEXT,
    issues_faced TEXT,
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

CREATE TABLE IF NOT EXISTS quality_checklists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    title VARCHAR(255),
    description TEXT,
    status VARCHAR(50),
    completion_date DATE,
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

CREATE TABLE IF NOT EXISTS contractors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    specialization VARCHAR(255),
    contact_info TEXT,
    status VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS contractor_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contractor_id INT,
    project_id INT,
    start_date DATE,
    end_date DATE,
    payment_terms TEXT,
    status VARCHAR(50),
    FOREIGN KEY (contractor_id) REFERENCES contractors(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);