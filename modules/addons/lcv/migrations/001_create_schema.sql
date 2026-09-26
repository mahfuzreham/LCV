-- LCV initial schema
-- Applied by the installer in the next migration runner stage.

CREATE TABLE IF NOT EXISTS lcv_roles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_key VARCHAR(100) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255) NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY lcv_roles_role_key_unique (role_key)
);

CREATE TABLE IF NOT EXISTS lcv_permissions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    permission_key VARCHAR(190) NOT NULL,
    label VARCHAR(190) NOT NULL,
    permission_group VARCHAR(100) NOT NULL,
    permission_type VARCHAR(30) NOT NULL DEFAULT 'action',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY lcv_permissions_key_unique (permission_key)
);

CREATE TABLE IF NOT EXISTS lcv_role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    KEY lcv_role_permissions_permission_idx (permission_id)
);

CREATE TABLE IF NOT EXISTS lcv_field_permissions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_id INT UNSIGNED NOT NULL,
    resource VARCHAR(100) NOT NULL,
    field_key VARCHAR(150) NOT NULL,
    can_view TINYINT(1) NOT NULL DEFAULT 0,
    can_edit TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY lcv_field_permissions_unique (role_id, resource, field_key)
);

CREATE TABLE IF NOT EXISTS lcv_admin_roles (
    admin_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (admin_id, role_id),
    KEY lcv_admin_roles_role_idx (role_id)
);

CREATE TABLE IF NOT EXISTS lcv_audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    admin_id INT UNSIGNED NULL,
    action VARCHAR(150) NOT NULL,
    resource VARCHAR(150) NULL,
    resource_id VARCHAR(100) NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY lcv_audit_admin_idx (admin_id),
    KEY lcv_audit_created_idx (created_at)
);
