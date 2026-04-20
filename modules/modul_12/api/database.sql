CREATE DATABASE IF NOT EXISTS healthedu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE healthedu;

-- We keep the users table structure but it will stay empty 
-- because we use 'backbone_medweb.users' for login now.
CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100)        NOT NULL,
  email      VARCHAR(150)        NOT NULL UNIQUE,
  password   VARCHAR(255)        NOT NULL,
  created_at DATETIME            DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- BMI LOG (Updated: user_id is NULL and user_email added)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bmi_log (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NULL,             -- Made NULL for SSO
  user_email  VARCHAR(150),         -- Our new SSO bridge
  bmi         DECIMAL(5,2) NOT NULL,
  category    VARCHAR(60)  NOT NULL,
  weight      DECIMAL(5,1) NOT NULL,
  height      DECIMAL(5,1) NOT NULL,
  age         TINYINT UNSIGNED NOT NULL,
  gender      ENUM('male','female') NOT NULL,
  recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_email)                -- Performance index
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- TDEE LOG
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tdee_log (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NULL,
  user_email   VARCHAR(150),
  tdee         INT NOT NULL,
  bmr          INT NOT NULL,
  activity     DECIMAL(4,3) NOT NULL,
  weight       DECIMAL(5,1) NOT NULL,
  height       DECIMAL(5,1) NOT NULL,
  age          TINYINT UNSIGNED NOT NULL,
  gender       ENUM('male','female') NOT NULL,
  recorded_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_email)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- FOOD LOG
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS food_log (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NULL,
  user_email  VARCHAR(150),
  name        VARCHAR(150) NOT NULL,
  calories    SMALLINT NOT NULL,
  meal_type   VARCHAR(30) NOT NULL,
  protein_g   SMALLINT DEFAULT 0,
  carbs_g     SMALLINT DEFAULT 0,
  fat_g       SMALLINT DEFAULT 0,
  log_date    DATE NOT NULL,
  recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_email)
) ENGINE=InnoDB;