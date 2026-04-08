-- ============================================================
--  HEALTHEDU — Database Schema
--  Import ke phpMyAdmin: Import → pilih file ini
-- ============================================================

CREATE DATABASE IF NOT EXISTS healthedu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE healthedu;

-- ─────────────────────────────────────────────
-- USERS (Sign Up / Login)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100)        NOT NULL,
  email      VARCHAR(150)        NOT NULL UNIQUE,
  password   VARCHAR(255)        NOT NULL,   -- bcrypt hash
  created_at DATETIME            DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- BMI LOG
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bmi_log (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT                 NOT NULL,
  bmi        DECIMAL(5,2)        NOT NULL,
  category   VARCHAR(60)         NOT NULL,
  weight     DECIMAL(5,1)        NOT NULL,
  height     DECIMAL(5,1)        NOT NULL,
  age        TINYINT UNSIGNED    NOT NULL,
  gender     ENUM('male','female') NOT NULL,
  recorded_at DATETIME           DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- TDEE LOG
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tdee_log (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT              NOT NULL,
  tdee         INT              NOT NULL,
  bmr          INT              NOT NULL,
  activity     DECIMAL(4,3)     NOT NULL,
  weight       DECIMAL(5,1)     NOT NULL,
  height       DECIMAL(5,1)     NOT NULL,
  age          TINYINT UNSIGNED NOT NULL,
  gender       ENUM('male','female') NOT NULL,
  recorded_at  DATETIME         DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- FOOD LOG
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS food_log (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT             NOT NULL,
  name        VARCHAR(150)    NOT NULL,
  calories    SMALLINT        NOT NULL,
  meal_type   VARCHAR(30)     NOT NULL,
  protein_g   SMALLINT        DEFAULT 0,
  carbs_g     SMALLINT        DEFAULT 0,
  fat_g       SMALLINT        DEFAULT 0,
  log_date    DATE            NOT NULL,
  recorded_at DATETIME        DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- SESSIONS (Token-based auth)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sessions (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT          NOT NULL,
  token      VARCHAR(64)  NOT NULL UNIQUE,
  expires_at DATETIME     NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
