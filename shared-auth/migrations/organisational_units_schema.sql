-- Organisational Units Schema
-- Flexible hierarchy system for organisations
-- UK English spelling used throughout

-- Organisational unit types - defines the structure levels for an organisation
CREATE TABLE IF NOT EXISTS organisational_unit_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    level_order INT NOT NULL,
    parent_type_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_type_id) REFERENCES organisational_unit_types(id) ON DELETE SET NULL,
    UNIQUE KEY unique_org_type_name (organisation_id, name),
    INDEX idx_organisation (organisation_id),
    INDEX idx_parent_type (parent_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Organisational units - actual instances of the hierarchy
CREATE TABLE IF NOT EXISTS organisational_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    unit_type_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) NULL,
    description TEXT NULL,
    parent_unit_id INT NULL,
    manager_user_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_type_id) REFERENCES organisational_unit_types(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_unit_id) REFERENCES organisational_units(id) ON DELETE SET NULL,
    FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_organisation (organisation_id),
    INDEX idx_unit_type (unit_type_id),
    INDEX idx_parent_unit (parent_unit_id),
    INDEX idx_manager (manager_user_id),
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User organisational unit assignments - links users to units
CREATE TABLE IF NOT EXISTS user_organisational_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organisational_unit_id INT NOT NULL,
    role_in_unit VARCHAR(100) DEFAULT 'member',
    is_primary BOOLEAN DEFAULT FALSE,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organisational_unit_id) REFERENCES organisational_units(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_unit (user_id, organisational_unit_id),
    INDEX idx_user (user_id),
    INDEX idx_unit (organisational_unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Unit admin roles - defines admin roles at different unit levels
CREATE TABLE IF NOT EXISTS unit_admin_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    unit_type_id INT NULL,
    role_name VARCHAR(100) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    can_manage_children BOOLEAN DEFAULT TRUE,
    can_manage_users BOOLEAN DEFAULT TRUE,
    can_manage_units BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_type_id) REFERENCES organisational_unit_types(id) ON DELETE SET NULL,
    INDEX idx_organisation (organisation_id),
    INDEX idx_unit_type (unit_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User unit admin assignments - assigns admin roles at unit level
CREATE TABLE IF NOT EXISTS user_unit_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organisational_unit_id INT NOT NULL,
    unit_admin_role_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organisational_unit_id) REFERENCES organisational_units(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_admin_role_id) REFERENCES unit_admin_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_unit_admin (user_id, organisational_unit_id, unit_admin_role_id),
    INDEX idx_user (user_id),
    INDEX idx_unit (organisational_unit_id),
    INDEX idx_admin_role (unit_admin_role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






