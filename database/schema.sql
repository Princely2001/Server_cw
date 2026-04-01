CREATE DATABASE IF NOT EXISTS alumni_db;
USE alumni_db;

-- Disable foreign key checks to allow dropping tables cleanly
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables if they exist (Order matters due to foreign keys)
DROP TABLE IF EXISTS api_logs;
DROP TABLE IF EXISTS api_keys;
DROP TABLE IF EXISTS alumni_monthly_limits;
DROP TABLE IF EXISTS alumni_bids;
DROP TABLE IF EXISTS alumni_employment;
DROP TABLE IF EXISTS alumni_courses;
DROP TABLE IF EXISTS alumni_licences;
DROP TABLE IF EXISTS alumni_certifications;
DROP TABLE IF EXISTS alumni_degrees;
DROP TABLE IF EXISTS alumni_profiles;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS email_verification_tokens;
DROP TABLE IF EXISTS users;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================
-- 1. CORE AUTHENTICATION TABLES
-- ==========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university_email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('alumnus', 'developer', 'admin') NOT NULL DEFAULT 'alumnus',
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE email_verification_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_email_verification_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_reset_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);

-- ==========================================
-- 2. ALUMNI PROFILE MANAGEMENT TABLES
-- ==========================================
CREATE TABLE alumni_profiles (
    user_id INT PRIMARY KEY,
    bio TEXT,
    linkedin_url VARCHAR(255),
    profile_image VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE alumni_degrees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    degree_name VARCHAR(150) NOT NULL,
    university_url VARCHAR(255) NOT NULL,
    completion_date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE alumni_certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    certification_name VARCHAR(150) NOT NULL,
    course_url VARCHAR(255) NOT NULL,
    completion_date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE alumni_licences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    licence_name VARCHAR(150) NOT NULL,
    awarding_body_url VARCHAR(255) NOT NULL,
    completion_date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE alumni_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_name VARCHAR(150) NOT NULL,
    course_url VARCHAR(255) NOT NULL,
    completion_date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE alumni_employment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    role VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL, 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE alumni_bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bid_amount DECIMAL(10, 2) NOT NULL,
    target_date DATE NOT NULL, 
    status ENUM('pending', 'winning', 'losing', 'won', 'lost', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE alumni_monthly_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    win_month INT NOT NULL, 
    win_year INT NOT NULL,  
    appearance_count INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================
-- 4. API KEY MANAGEMENT & SECURITY TABLES
-- ==========================================
CREATE TABLE api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_key VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('active', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE CASCADE
);

-- ==========================================
-- 5. OPTIMIZATION INDEXES
-- ==========================================
-- Auth Indexes
CREATE INDEX idx_users_email ON users(university_email);
CREATE INDEX idx_email_verification_user ON email_verification_tokens(user_id);
CREATE INDEX idx_password_reset_user ON password_reset_tokens(user_id);
CREATE INDEX idx_email_verification_lookup ON email_verification_tokens(token_hash, expires_at, used_at);
CREATE INDEX idx_password_reset_lookup ON password_reset_tokens(token_hash, expires_at, used_at);

-- Bidding Indexes
CREATE INDEX idx_bids_target_date ON alumni_bids(target_date);
CREATE INDEX idx_bids_user_date ON alumni_bids(user_id, target_date);

-- API Indexes
CREATE INDEX idx_api_keys_lookup ON api_keys(api_key, status);
CREATE INDEX idx_api_logs_key ON api_logs(api_key_id);