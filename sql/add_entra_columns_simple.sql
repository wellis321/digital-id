-- Simple migration to add Entra integration columns
-- Run this if you get "Unknown column 'entra_enabled'" error

ALTER TABLE organisations 
ADD COLUMN entra_enabled BOOLEAN DEFAULT FALSE,
ADD COLUMN entra_tenant_id VARCHAR(255) NULL,
ADD COLUMN entra_client_id VARCHAR(255) NULL;

