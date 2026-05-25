-- Reference schema untuk SPK Bansos.
-- Sumber utama schema tetap migration Laravel di folder database/migrations.
-- File ini diselaraskan sebagai referensi MySQL/XAMPP.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS generated_letters;
DROP TABLE IF EXISTS document_templates;
DROP TABLE IF EXISTS calon_penerimas;
DROP TABLE IF EXISTS bansos_periods;
DROP TABLE IF EXISTS personal_access_tokens;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS lingkungan_stasis;
DROP TABLE IF EXISTS lingkungan_parokis;
DROP TABLE IF EXISTS stasis;
DROP TABLE IF EXISTS failed_jobs;
DROP TABLE IF EXISTS job_batches;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS cache_locks;
DROP TABLE IF EXISTS cache;

CREATE TABLE cache (
  `key` VARCHAR(255) NOT NULL PRIMARY KEY,
  value MEDIUMTEXT NOT NULL,
  expiration INT NOT NULL,
  INDEX cache_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
  `key` VARCHAR(255) NOT NULL PRIMARY KEY,
  owner VARCHAR(255) NOT NULL,
  expiration INT NOT NULL,
  INDEX cache_locks_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jobs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  queue VARCHAR(255) NOT NULL,
  payload LONGTEXT NOT NULL,
  attempts TINYINT UNSIGNED NOT NULL,
  reserved_at INT UNSIGNED NULL,
  available_at INT UNSIGNED NOT NULL,
  created_at INT UNSIGNED NOT NULL,
  INDEX jobs_queue_index (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_batches (
  id VARCHAR(255) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  total_jobs INT NOT NULL,
  pending_jobs INT NOT NULL,
  failed_jobs INT NOT NULL,
  failed_job_ids LONGTEXT NOT NULL,
  options MEDIUMTEXT NULL,
  cancelled_at INT NULL,
  created_at INT NOT NULL,
  finished_at INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE failed_jobs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid VARCHAR(255) NOT NULL UNIQUE,
  connection TEXT NOT NULL,
  queue TEXT NOT NULL,
  payload LONGTEXT NOT NULL,
  exception LONGTEXT NOT NULL,
  failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stasis (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_stasi VARCHAR(100) NOT NULL UNIQUE,
  kode_stasi VARCHAR(20) NOT NULL UNIQUE,
  alamat TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lingkungan_parokis (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_lingkungan_paroki VARCHAR(100) NOT NULL UNIQUE,
  kode_wilayah VARCHAR(20) NOT NULL UNIQUE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lingkungan_stasis (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  stasi_id BIGINT UNSIGNED NOT NULL,
  nama_lingkungan_stasi VARCHAR(100) NOT NULL,
  kode_lingkungan VARCHAR(20) NOT NULL UNIQUE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT lingkungan_stasis_stasi_id_foreign FOREIGN KEY (stasi_id) REFERENCES stasis(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('super_admin','paroki','ketua_lingkungan_paroki','stasi','ketua_lingkungan_stasi') NOT NULL DEFAULT 'ketua_lingkungan_stasi',
  stasi_id BIGINT UNSIGNED NULL,
  lingkungan_paroki_id BIGINT UNSIGNED NULL,
  lingkungan_stasi_id BIGINT UNSIGNED NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_user_hierarchy (role, stasi_id, lingkungan_paroki_id, lingkungan_stasi_id),
  CONSTRAINT users_stasi_id_foreign FOREIGN KEY (stasi_id) REFERENCES stasis(id) ON DELETE SET NULL,
  CONSTRAINT users_lingkungan_paroki_id_foreign FOREIGN KEY (lingkungan_paroki_id) REFERENCES lingkungan_parokis(id) ON DELETE SET NULL,
  CONSTRAINT users_lingkungan_stasi_id_foreign FOREIGN KEY (lingkungan_stasi_id) REFERENCES lingkungan_stasis(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
  email VARCHAR(255) NOT NULL PRIMARY KEY,
  token VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
  id VARCHAR(255) NOT NULL PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  payload LONGTEXT NOT NULL,
  last_activity INT NOT NULL,
  INDEX sessions_user_id_index (user_id),
  INDEX sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE personal_access_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tokenable_type VARCHAR(255) NOT NULL,
  tokenable_id BIGINT UNSIGNED NOT NULL,
  name TEXT NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  abilities TEXT NULL,
  last_used_at TIMESTAMP NULL,
  expires_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id),
  INDEX personal_access_tokens_expires_at_index (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bansos_periods (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_periode VARCHAR(100) NOT NULL,
  tahun SMALLINT UNSIGNED NOT NULL,
  status_periode ENUM('aktif','proses_perankingan','selesai','arsip') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE calon_penerimas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bansos_period_id BIGINT UNSIGNED NOT NULL,
  lingkungan_stasi_id BIGINT UNSIGNED NOT NULL,
  stasi_id BIGINT UNSIGNED NOT NULL,
  nik VARCHAR(16) NOT NULL,
  nama_lengkap VARCHAR(150) NOT NULL,
  alamat_kristen TEXT NULL,
  pendapatan_keluarga DECIMAL(12,2) NOT NULL,
  jumlah_tanggungan INT UNSIGNED NOT NULL,
  status_tempat_tinggal ENUM('milik_sendiri','sewa','numpang') NOT NULL,
  status_hubungan ENUM('lajang','menikah','cerai') NOT NULL,
  urgensi_tambahan_tekstual TEXT NULL,
  saw_score DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
  rank_global INT UNSIGNED NULL,
  rank_internal_stasi INT UNSIGNED NULL,
  status_alur ENUM('draft','diajukan_ke_stasi','disetujui_stasi','diranking_lingkungan_paroki','disetujui_paroki','ditolak') NOT NULL DEFAULT 'draft',
  is_penerima_sah TINYINT(1) NOT NULL DEFAULT 0,
  nominal_bansos_disetujui DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_calon_bansos_period_status (bansos_period_id, status_alur),
  INDEX idx_calon_nik (nik),
  CONSTRAINT calon_penerimas_bansos_period_id_foreign FOREIGN KEY (bansos_period_id) REFERENCES bansos_periods(id) ON DELETE CASCADE,
  CONSTRAINT calon_penerimas_lingkungan_stasi_id_foreign FOREIGN KEY (lingkungan_stasi_id) REFERENCES lingkungan_stasis(id) ON DELETE CASCADE,
  CONSTRAINT calon_penerimas_stasi_id_foreign FOREIGN KEY (stasi_id) REFERENCES stasis(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE document_templates (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  type TEXT NULL,
  content LONGTEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE generated_letters (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  document_template_id BIGINT UNSIGNED NOT NULL,
  calon_penerima_id BIGINT UNSIGNED NULL,
  bansos_period_id BIGINT UNSIGNED NULL,
  title VARCHAR(255) NULL,
  content LONGTEXT NULL,
  file_path VARCHAR(255) NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT generated_letters_document_template_id_foreign FOREIGN KEY (document_template_id) REFERENCES document_templates(id) ON DELETE CASCADE,
  CONSTRAINT generated_letters_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  action VARCHAR(255) NOT NULL,
  model_type VARCHAR(255) NULL,
  model_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  meta LONGTEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT activity_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
