-- Care Core connector: staff are read from Care Core (the single source of truth). Safe to run more than once is NOT guaranteed by MySQL for
-- ALTER ... ADD COLUMN, so the application also creates these itself on first use (CareCore::ensureSchema()); run this by hand only if you prefer.
ALTER TABLE employees
  ADD COLUMN care_core_person_id CHAR(36) NULL,
  ADD COLUMN care_core_employment_id CHAR(36) NULL,
  ADD COLUMN leaves_on DATE NULL,
  ADD COLUMN signature_path VARCHAR(255) NULL,
  ADD COLUMN care_core_photo_hash CHAR(64) NULL,
  ADD COLUMN care_core_signature_hash CHAR(64) NULL,
  ADD COLUMN last_synced_from_care_core TIMESTAMP NULL,
  ADD UNIQUE KEY uq_cc_person (organisation_id, care_core_person_id);

CREATE TABLE IF NOT EXISTS care_core_events (
  event_id        CHAR(36) NOT NULL PRIMARY KEY,
  organisation_id INT NOT NULL,
  type            VARCHAR(60) NOT NULL,
  outcome         VARCHAR(20) NOT NULL DEFAULT 'ok',
  detail          VARCHAR(255) NULL,
  received_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cc_events_org (organisation_id, received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One-time sign-in links (no passwords): only a hash of the token is stored; used once, short life.
CREATE TABLE IF NOT EXISTS login_links (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NOT NULL,
  token_hash   CHAR(64) NOT NULL,
  expires_at   DATETIME NOT NULL,
  used_at      DATETIME NULL,
  requested_ip VARCHAR(45) NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_login_link_token (token_hash),
  INDEX idx_login_links_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
