-- Migration: Add Organisational Units Tables
-- Run this to add organisational structure support
-- UK English spelling used throughout

-- Organisational units (teams, departments, areas, regions, etc.)
CREATE TABLE IF NOT EXISTS organisational_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    
    -- Basic info
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    unit_type VARCHAR(100) NULL,  -- e.g., "team", "area", "region", "department" (user-defined, free text)
    
    -- Hierarchy (self-referential - a unit can have a parent unit)
    parent_unit_id INT NULL,
    
    -- Flexible metadata for custom attributes
    metadata JSON NULL,  -- e.g., {"location": "Building A", "cost_center": "CC123", "manager_email": "x@y.com"}
    
    -- Optional manager
    manager_user_id INT NULL,
    
    -- Ordering (for display purposes)
    display_order INT DEFAULT 0,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_unit_id) REFERENCES organisational_units(id) ON DELETE SET NULL,
    FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_organisation (organisation_id),
    INDEX idx_parent_unit (parent_unit_id),
    INDEX idx_unit_type (unit_type),
    INDEX idx_manager (manager_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User membership in organisational units
CREATE TABLE IF NOT EXISTS organisational_unit_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    user_id INT NOT NULL,
    
    -- Role within this specific unit (flexible, not constrained)
    role VARCHAR(100) DEFAULT 'member',  -- e.g., "member", "lead", "manager", "coordinator", "admin"
    
    -- Timestamps
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (unit_id) REFERENCES organisational_units(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_unit_member (unit_id, user_id),
    INDEX idx_unit (unit_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



