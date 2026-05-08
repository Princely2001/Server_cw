CREATE DATABASE IF NOT EXISTS alumni_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE alumni_db;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS analytics_filter_presets;
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

SET FOREIGN_KEY_CHECKS = 1;

-- USERS AND AUTHENTICATION

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university_email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('alumnus', 'developer', 'admin') NOT NULL DEFAULT 'alumnus',
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    has_attended_event TINYINT(1) NOT NULL DEFAULT 0,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE email_verification_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_email_verification_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_reset_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ALUMNI PROFILE MANAGEMENT

CREATE TABLE alumni_profiles (
    user_id INT PRIMARY KEY,

    bio TEXT NULL,
    linkedin_url VARCHAR(255) NULL,
    profile_image VARCHAR(255) NULL,

    programme VARCHAR(150) NULL,
    graduation_year INT NULL,
    industry_sector VARCHAR(100) NULL,
    current_job_title VARCHAR(150) NULL,
    current_employer VARCHAR(150) NULL,
    country VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    profile_completion INT NOT NULL DEFAULT 0,

    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    featured_for_date DATE NULL,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_alumni_profiles_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_profile_completion
        CHECK (profile_completion >= 0 AND profile_completion <= 100)
) ENGINE=InnoDB;

CREATE TABLE alumni_degrees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    degree_name VARCHAR(150) NOT NULL,
    university_url VARCHAR(255) NOT NULL,
    completion_date DATE NOT NULL,
    CONSTRAINT fk_degrees_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE alumni_certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    certification_name VARCHAR(150) NOT NULL,
    course_url VARCHAR(255) NOT NULL,
    completion_date DATE NOT NULL,
    CONSTRAINT fk_certifications_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE alumni_licences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    licence_name VARCHAR(150) NOT NULL,
    awarding_body_url VARCHAR(255) NOT NULL,
    completion_date DATE NOT NULL,
    CONSTRAINT fk_licences_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE alumni_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_name VARCHAR(150) NOT NULL,
    course_url VARCHAR(255) NOT NULL,
    completion_date DATE NOT NULL,
    CONSTRAINT fk_courses_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE alumni_employment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    role VARCHAR(100) NOT NULL,
    industry_sector VARCHAR(100) NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    CONSTRAINT fk_employment_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- BIDDING


CREATE TABLE alumni_bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bid_amount DECIMAL(10, 2) NOT NULL,
    target_date DATE NOT NULL,
    status ENUM('pending', 'won', 'lost') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_alumni_bids_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT uq_alumni_bids_user_target
        UNIQUE (user_id, target_date),

    CONSTRAINT chk_bid_amount_positive
        CHECK (bid_amount > 0)
) ENGINE=InnoDB;

CREATE TABLE alumni_monthly_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    win_month INT NOT NULL,
    win_year INT NOT NULL,
    appearance_count INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_alumni_monthly_limits_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT uq_monthly_limit_user_period
        UNIQUE (user_id, win_month, win_year),

    CONSTRAINT chk_win_month
        CHECK (win_month BETWEEN 1 AND 12),

    CONSTRAINT chk_appearance_count
        CHECK (appearance_count >= 0)
) ENGINE=InnoDB;

-- API KEY MANAGEMENT AND USAGE TRACKING


CREATE TABLE api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    client_name VARCHAR(100) NOT NULL,
    client_type ENUM('ar_app', 'analytics_dashboard', 'general') NOT NULL DEFAULT 'ar_app',

    key_hash CHAR(64) NOT NULL UNIQUE,
    key_prefix VARCHAR(12) NOT NULL,

    status ENUM('active', 'revoked') NOT NULL DEFAULT 'active',
    permissions JSON NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at DATETIME NULL,
    expires_at DATETIME NULL,
    revoked_at DATETIME NULL,

    CONSTRAINT fk_api_keys_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL DEFAULT 'GET',
    status_code INT NOT NULL DEFAULT 200,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_api_logs_key
        FOREIGN KEY (api_key_id) REFERENCES api_keys(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ANALYTICS DASHBOARD FILTER PRESETS


CREATE TABLE analytics_filter_presets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preset_name VARCHAR(100) NOT NULL,
    filters JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_filter_presets_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT uq_filter_preset_user_name
        UNIQUE (user_id, preset_name)
) ENGINE=InnoDB;


-- INDEXES


CREATE INDEX idx_users_email ON users(university_email);
CREATE INDEX idx_users_role_active ON users(role, is_active);

CREATE INDEX idx_email_verification_user ON email_verification_tokens(user_id);
CREATE INDEX idx_password_reset_user ON password_reset_tokens(user_id);
CREATE INDEX idx_email_verification_lookup ON email_verification_tokens(token_hash, expires_at, used_at);
CREATE INDEX idx_password_reset_lookup ON password_reset_tokens(token_hash, expires_at, used_at);

CREATE INDEX idx_profiles_programme ON alumni_profiles(programme);
CREATE INDEX idx_profiles_graduation_year ON alumni_profiles(graduation_year);
CREATE INDEX idx_profiles_industry ON alumni_profiles(industry_sector);
CREATE INDEX idx_profiles_employer ON alumni_profiles(current_employer);
CREATE INDEX idx_profiles_job_title ON alumni_profiles(current_job_title);
CREATE INDEX idx_profiles_location ON alumni_profiles(country, city);
CREATE INDEX idx_profiles_featured_for_date ON alumni_profiles(featured_for_date);

CREATE INDEX idx_degrees_user ON alumni_degrees(user_id);
CREATE INDEX idx_certifications_user ON alumni_certifications(user_id);
CREATE INDEX idx_certifications_name ON alumni_certifications(certification_name);
CREATE INDEX idx_courses_user ON alumni_courses(user_id);
CREATE INDEX idx_courses_name ON alumni_courses(course_name);
CREATE INDEX idx_licences_user ON alumni_licences(user_id);
CREATE INDEX idx_employment_user ON alumni_employment(user_id);
CREATE INDEX idx_employment_industry ON alumni_employment(industry_sector);

CREATE INDEX idx_bids_target_date ON alumni_bids(target_date);
CREATE INDEX idx_bids_user_date ON alumni_bids(user_id, target_date);
CREATE INDEX idx_bids_target_status_amount ON alumni_bids(target_date, status, bid_amount);
CREATE INDEX idx_bids_target_created ON alumni_bids(target_date, created_at);

CREATE INDEX idx_monthly_limits_user_period ON alumni_monthly_limits(user_id, win_month, win_year);

CREATE INDEX idx_api_keys_hash_status ON api_keys(key_hash, status);
CREATE INDEX idx_api_keys_user_status ON api_keys(user_id, status);
CREATE INDEX idx_api_logs_key ON api_logs(api_key_id);
CREATE INDEX idx_api_logs_accessed_at ON api_logs(accessed_at);

CREATE INDEX idx_filter_presets_user ON analytics_filter_presets(user_id);
CREATE INDEX idx_filter_presets_user_name ON analytics_filter_presets(user_id, preset_name);
