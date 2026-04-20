-- ============================================================
-- Modul 8 — Calorie Tracker (PostgreSQL) — MVP Schema
-- Prefix: m8_  |  Normalized to 3NF
-- user_id merujuk ke tabel users di sistem medical-web
-- (no cross-schema FK; enforced di layer aplikasi)
--
-- Tabel OUT-OF-SCOPE untuk MVP (implementasi fase berikutnya):
--   m8_exercises, m8_daily_steps, m8_progress_photos,
--   m8_streaks, m8_badges, m8_user_badges
-- ============================================================


-- ─── 1. USER PROFILES ────────────────────────────────────────
-- Satu baris per user; menyimpan data onboarding + target harian
-- yang di-cache dari kalkulasi Mifflin-St Jeor + TDEE.
CREATE TABLE IF NOT EXISTS m8_user_profiles (
    user_id              INTEGER      PRIMARY KEY,
    gender               TEXT         NOT NULL
                                      CHECK (gender IN ('male', 'female')),
    birth_date           DATE         NOT NULL,
    height_cm            NUMERIC(5,2) NOT NULL CHECK (height_cm > 0),
    weight_kg            NUMERIC(5,2) NOT NULL CHECK (weight_kg > 0),
    activity_level       TEXT         NOT NULL
                                      CHECK (activity_level IN ('beginner', 'active', 'athlete')),
    goal                 TEXT         NOT NULL
                                      CHECK (goal IN ('lose', 'maintain', 'gain')),
    goal_weight_kg       NUMERIC(5,2) CHECK (goal_weight_kg > 0),
    step_goal            INTEGER      NOT NULL DEFAULT 10000,
    barriers             TEXT[]       NOT NULL DEFAULT '{}',
    -- Target harian (computed + cached dari rumus BMR/TDEE)
    daily_calorie_target INTEGER      NOT NULL DEFAULT 2000,
    daily_protein_g      INTEGER      NOT NULL DEFAULT 150,
    daily_carbs_g        INTEGER      NOT NULL DEFAULT 250,
    daily_fats_g         INTEGER      NOT NULL DEFAULT 67,
    daily_fiber_g        INTEGER      NOT NULL DEFAULT 30,
    daily_sugar_g        INTEGER      NOT NULL DEFAULT 50,
    daily_sodium_mg      INTEGER      NOT NULL DEFAULT 2300,
    onboarded_at         TIMESTAMPTZ,
    created_at           TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at           TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);


-- ─── 2. WEIGHT LOGS ──────────────────────────────────────────
-- Dipisah dari profil agar riwayat berat badan terjaga (1:N).
-- m8_user_profiles.weight_kg = snapshot saat onboarding;
-- nilai terkini diambil dari MAX(log_date) di tabel ini.
CREATE TABLE IF NOT EXISTS m8_weight_logs (
    id         BIGSERIAL    PRIMARY KEY,
    user_id    INTEGER      NOT NULL
                            REFERENCES m8_user_profiles(user_id) ON DELETE CASCADE,
    weight_kg  NUMERIC(5,2) NOT NULL CHECK (weight_kg > 0),
    log_date   DATE         NOT NULL DEFAULT CURRENT_DATE,
    note       TEXT,
    created_at TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS m8_weight_logs_user_date_idx
    ON m8_weight_logs (user_id, log_date DESC);


-- ─── 3. SAVED FOODS ──────────────────────────────────────────
-- Makanan yang disimpan user untuk quick-add.
-- Didefinisikan sebelum m8_meals karena meals mereferensikannya.
CREATE TABLE IF NOT EXISTS m8_saved_foods (
    id           BIGSERIAL    PRIMARY KEY,
    user_id      INTEGER      NOT NULL
                              REFERENCES m8_user_profiles(user_id) ON DELETE CASCADE,
    name         TEXT         NOT NULL,
    brand        TEXT,
    calories     INTEGER      NOT NULL CHECK (calories >= 0),
    protein_g    NUMERIC(6,2) NOT NULL DEFAULT 0,
    carbs_g      NUMERIC(6,2) NOT NULL DEFAULT 0,
    fats_g       NUMERIC(6,2) NOT NULL DEFAULT 0,
    fiber_g      NUMERIC(6,2) NOT NULL DEFAULT 0,
    sugar_g      NUMERIC(6,2) NOT NULL DEFAULT 0,
    sodium_mg    NUMERIC(7,2) NOT NULL DEFAULT 0,
    serving_size NUMERIC(7,2),
    serving_unit TEXT,                            -- 'g', 'ml', 'piece', dll
    barcode      TEXT,
    source       TEXT         NOT NULL DEFAULT 'manual'
                              CHECK (source IN ('manual', 'database', 'barcode')),
    created_at   TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at   TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS m8_saved_foods_user_idx
    ON m8_saved_foods (user_id);
CREATE INDEX IF NOT EXISTS m8_saved_foods_barcode_idx
    ON m8_saved_foods (barcode) WHERE barcode IS NOT NULL;


-- ─── 4. MEALS ────────────────────────────────────────────────
-- Log makanan harian. ai_confidence hanya relevan saat
-- source = 'ai_scan'; nullable untuk semua source lainnya.
CREATE TABLE IF NOT EXISTS m8_meals (
    id             BIGSERIAL    PRIMARY KEY,
    user_id        INTEGER      NOT NULL
                                REFERENCES m8_user_profiles(user_id) ON DELETE CASCADE,
    log_date       DATE         NOT NULL DEFAULT CURRENT_DATE,
    meal_type      TEXT         CHECK (meal_type IN ('breakfast', 'lunch', 'dinner', 'snack')),
    name           TEXT         NOT NULL,
    calories       INTEGER      NOT NULL DEFAULT 0 CHECK (calories >= 0),
    protein_g      NUMERIC(6,2) NOT NULL DEFAULT 0,
    carbs_g        NUMERIC(6,2) NOT NULL DEFAULT 0,
    fats_g         NUMERIC(6,2) NOT NULL DEFAULT 0,
    fiber_g        NUMERIC(6,2) NOT NULL DEFAULT 0,
    sugar_g        NUMERIC(6,2) NOT NULL DEFAULT 0,
    sodium_mg      NUMERIC(7,2) NOT NULL DEFAULT 0,
    serving_size   NUMERIC(7,2),
    photo_url      TEXT,
    source         TEXT         NOT NULL DEFAULT 'manual'
                                CHECK (source IN
                                    ('manual', 'saved', 'database', 'barcode', 'ai_scan')),
    ai_confidence  NUMERIC(4,3) CHECK (ai_confidence BETWEEN 0 AND 1),
    saved_food_id  BIGINT       REFERENCES m8_saved_foods(id) ON DELETE SET NULL,
    created_at     TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at     TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS m8_meals_user_date_idx
    ON m8_meals (user_id, log_date);
CREATE INDEX IF NOT EXISTS m8_meals_user_date_created_desc_idx
    ON m8_meals (user_id, log_date, created_at DESC);
CREATE INDEX IF NOT EXISTS m8_meals_source_idx
    ON m8_meals (source);


-- ─── 5. WATER LOGS ───────────────────────────────────────────
-- Setiap tap "+250ml / +500ml / +750ml" menghasilkan satu baris.
-- Total harian dihitung via SUM(amount_ml) GROUP BY log_date.
CREATE TABLE IF NOT EXISTS m8_water_logs (
    id         BIGSERIAL   PRIMARY KEY,
    user_id    INTEGER     NOT NULL
                           REFERENCES m8_user_profiles(user_id) ON DELETE CASCADE,
    log_date   DATE        NOT NULL DEFAULT CURRENT_DATE,
    amount_ml  INTEGER     NOT NULL CHECK (amount_ml > 0),
    logged_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS m8_water_logs_user_date_idx
    ON m8_water_logs (user_id, log_date);


-- ─── 6. DAILY HEALTH SCORES ──────────────────────────────────
-- Dihitung saat dashboard di-refresh; disimpan agar Progress chart
-- bisa menampilkan tren historis tanpa rekalkulasi ulang.
CREATE TABLE IF NOT EXISTS m8_daily_health_scores (
    user_id               INTEGER      NOT NULL
                                       REFERENCES m8_user_profiles(user_id) ON DELETE CASCADE,
    log_date              DATE         NOT NULL,
    score                 SMALLINT     NOT NULL DEFAULT 100
                                       CHECK (score BETWEEN 0 AND 100),
    calorie_deviation_pct NUMERIC(5,2),   -- % jarak net kalori dari target
    macro_deviation_pct   NUMERIC(5,2),   -- rata-rata % deviasi ketiga makro
    computed_at           TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    created_at            TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at            TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    PRIMARY KEY (user_id, log_date)
);


-- ─── 7. AI SCAN QUOTA ────────────────────────────────────────
-- Rate limit: max 20 scan/hari per user; diperiksa + di-increment
-- atomik di api.php sebelum memanggil Anthropic API.
CREATE TABLE IF NOT EXISTS m8_ai_scan_quota (
    user_id    INTEGER     NOT NULL
                           REFERENCES m8_user_profiles(user_id) ON DELETE CASCADE,
    log_date   DATE        NOT NULL DEFAULT CURRENT_DATE,
    scan_count SMALLINT    NOT NULL DEFAULT 0 CHECK (scan_count >= 0),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (user_id, log_date)
);
