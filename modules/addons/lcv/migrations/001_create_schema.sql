-- Reference schema for Staff Permission & Support PIN.
-- The addon installer uses WHMCS Capsule and creates these tables automatically.

CREATE TABLE mod_lcv_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_key VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE mod_lcv_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(180) NOT NULL UNIQUE,
    label VARCHAR(180) NOT NULL,
    permission_group VARCHAR(80) NOT NULL,
    permission_type VARCHAR(40) NOT NULL DEFAULT 'action',
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE mod_lcv_role_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    UNIQUE KEY role_permission (role_id, permission_id)
);

CREATE TABLE mod_lcv_field_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    resource VARCHAR(80) NOT NULL,
    field_key VARCHAR(100) NOT NULL,
    can_view TINYINT(1) NOT NULL DEFAULT 0,
    can_edit TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY role_field (role_id, resource, field_key)
);

CREATE TABLE mod_lcv_admin_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL UNIQUE,
    role_id INT UNSIGNED NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE mod_lcv_role_departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    UNIQUE KEY role_department (role_id, department_id)
);

CREATE TABLE mod_lcv_support_pins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL UNIQUE,
    pin_hash VARCHAR(255) NOT NULL,
    failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    verified_at DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE mod_lcv_audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NULL,
    action VARCHAR(120) NOT NULL,
    resource VARCHAR(120) NULL,
    resource_id VARCHAR(80) NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL,
    KEY admin_created (admin_id, created_at)
);
