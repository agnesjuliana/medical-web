# RESTful API Specification — Modul 8 Calorie Tracker

> **Catatan implementasi:** Backend menggunakan PHP single-file `api.php` dengan dispatch `?action=X`. Spec ini menggunakan notasi REST konseptual — implementasinya memetakan setiap endpoint ke action param yang sesuai.
> **Auth model:** Semua endpoint *Require Session* memanfaatkan PHP session cookie (`PHPSESSID`) via `requireLogin()` dari `core/auth.php`. `user_id` diambil dari `$_SESSION['user_id']`, **tidak pernah** dari request body/query params untuk mencegah IDOR.

---

## GROUP 1 — User Profile (Onboarding & Settings)

### 1.1 Get Profile

- **Endpoint:** `GET` `/api/modul8/profiles/me`
- **Tujuan:** Mengambil profil lengkap user yang sedang login, termasuk target harian dan status onboarding.
- **Security/Auth:** Require Session
- **Request Payload / Query Params:** Tidak ada. `user_id` diambil eksklusif dari session.
- **Response (200 OK):**

```json
{
  "data": {
    "gender": "male",
    "birth_date": "2001-02-02",
    "height_cm": 170,
    "weight_kg": 70.0,
    "activity_level": "active",
    "goal": "lose",
    "goal_weight_kg": 65.0,
    "step_goal": 10000,
    "daily_calorie_target": 2350,
    "daily_protein_g": 176,
    "daily_carbs_g": 235,
    "daily_fats_g": 65,
    "daily_fiber_g": 30,
    "daily_sugar_g": 50,
    "daily_sodium_mg": 2300,
    "onboarded_at": "2026-04-19T10:00:00Z"
  }
}
```

- **Response (404):** Profil belum ada → frontend arahkan ke onboarding.
- **Penjelasan Logika Database:** Query single row dari `m8_user_profiles` berdasarkan `user_id` dari session.
- **Secure SQL Query:**

```sql
SELECT gender, birth_date, height_cm, weight_kg, activity_level,
       goal, goal_weight_kg, step_goal, daily_calorie_target,
       daily_protein_g, daily_carbs_g, daily_fats_g,
       daily_fiber_g, daily_sugar_g, daily_sodium_mg, onboarded_at
FROM m8_user_profiles
WHERE user_id = $1;
```

---

### 1.2 Save / Update Profile (Onboarding Submit)

- **Endpoint:** `PUT` `/api/modul8/profiles/me`
- **Tujuan:** Menyimpan atau memperbarui profil user setelah onboarding wizard selesai. Menghitung dan menyimpan TDEE + target makro secara atomik.
- **Security/Auth:** Require Session
- **Request Payload (Body JSON):**

| Field | Type | Required | Validasi |
|---|---|---|---|
| `gender` | string | ✅ | enum: `male`, `female` |
| `birth_date` | string (ISO date) | ✅ | format `YYYY-MM-DD`, usia 10–120 tahun |
| `height_cm` | number | ✅ | 50–300 |
| `weight_kg` | number | ✅ | 20–500 |
| `activity_level` | string | ✅ | enum: `beginner`, `active`, `athlete` |
| `goal` | string | ✅ | enum: `lose`, `maintain`, `gain` |
| `goal_weight_kg` | number | ❌ | 20–500 |
| `step_goal` | integer | ❌ | default 10000 |
| `barriers` | string[] | ❌ | max 10 item |

- **Penjelasan Logika Database:**
  1. Backend kalkulasi BMR menggunakan **Mifflin-St Jeor**, TDEE × activity factor, lalu ±500 kcal sesuai goal.
  2. Derivasi target makro: protein 30% / carbs 40% / fats 30%.
  3. `UPSERT` ke `m8_user_profiles`. Jika `onboarded_at` masih NULL, set ke `NOW()`.
  4. Insert baris awal ke `m8_weight_logs` untuk seed chart progress.

- **Secure SQL Query:**

```sql
-- UPSERT profil
INSERT INTO m8_user_profiles (
    user_id, gender, birth_date, height_cm, weight_kg,
    activity_level, goal, goal_weight_kg, step_goal, barriers,
    daily_calorie_target, daily_protein_g, daily_carbs_g,
    daily_fats_g, daily_fiber_g, daily_sugar_g, daily_sodium_mg,
    onboarded_at, created_at, updated_at
) VALUES (
    $1, $2, $3, $4, $5, $6, $7, $8, $9, $10,
    $11, $12, $13, $14, $15, $16, $17,
    CASE WHEN $18 THEN NOW() ELSE NULL END,
    NOW(), NOW()
)
ON CONFLICT (user_id) DO UPDATE SET
    gender               = EXCLUDED.gender,
    birth_date           = EXCLUDED.birth_date,
    height_cm            = EXCLUDED.height_cm,
    weight_kg            = EXCLUDED.weight_kg,
    activity_level       = EXCLUDED.activity_level,
    goal                 = EXCLUDED.goal,
    goal_weight_kg       = EXCLUDED.goal_weight_kg,
    step_goal            = EXCLUDED.step_goal,
    barriers             = EXCLUDED.barriers,
    daily_calorie_target = EXCLUDED.daily_calorie_target,
    daily_protein_g      = EXCLUDED.daily_protein_g,
    daily_carbs_g        = EXCLUDED.daily_carbs_g,
    daily_fats_g         = EXCLUDED.daily_fats_g,
    daily_fiber_g        = EXCLUDED.daily_fiber_g,
    daily_sugar_g        = EXCLUDED.daily_sugar_g,
    daily_sodium_mg      = EXCLUDED.daily_sodium_mg,
    onboarded_at         = COALESCE(m8_user_profiles.onboarded_at, NOW()),
    updated_at           = NOW();

-- Seed weight log pertama
INSERT INTO m8_weight_logs (user_id, weight_kg, log_date)
VALUES ($1, $2, CURRENT_DATE)
ON CONFLICT DO NOTHING;
```

---

## GROUP 2 — Dashboard (Home Screen)

### 2.1 Get Daily Dashboard

- **Endpoint:** `GET` `/api/modul8/dashboard`
- **Tujuan:** Mengambil semua data agregat yang dibutuhkan Home screen dalam satu request: sisa kalori, makro, nutrisi, langkah, dan air untuk tanggal tertentu.
- **Security/Auth:** Require Session
- **Request Query Params:**

| Param | Type | Required | Keterangan |
|---|---|---|---|
| `date` | string (ISO date) | ❌ | Default: hari ini (`CURRENT_DATE`) |

- **Response (200 OK):**

```json
{
  "data": {
    "date": "2026-04-19",
    "targets": {
      "calories": 2350, "protein_g": 176,
      "carbs_g": 235, "fats_g": 65,
      "fiber_g": 30, "sugar_g": 50,
      "sodium_mg": 2300, "step_goal": 10000
    },
    "consumed": {
      "calories": 800, "protein_g": 60,
      "carbs_g": 90, "fats_g": 30,
      "fiber_g": 8, "sugar_g": 15, "sodium_mg": 600
    },
    "water_ml": 750,
    "health_score": 85
  }
}
```

- **Penjelasan Logika Database:**
  1. Ambil targets dari `m8_user_profiles`.
  2. `SUM` semua nutrisi dari `m8_meals` untuk `(user_id, log_date)`.
  3. `SUM(amount_ml)` dari `m8_water_logs` untuk tanggal yang sama.
  4. Ambil `score` dari `m8_daily_health_scores` jika ada.
  5. Semua query paralel, di-assemble di application layer. **Tidak** menggunakan subquery dinamis.

- **Secure SQL Query:**

```sql
-- Consumed nutrition (meals aggregate)
SELECT
    COALESCE(SUM(calories),  0) AS calories,
    COALESCE(SUM(protein_g), 0) AS protein_g,
    COALESCE(SUM(carbs_g),   0) AS carbs_g,
    COALESCE(SUM(fats_g),    0) AS fats_g,
    COALESCE(SUM(fiber_g),   0) AS fiber_g,
    COALESCE(SUM(sugar_g),   0) AS sugar_g,
    COALESCE(SUM(sodium_mg), 0) AS sodium_mg
FROM m8_meals
WHERE user_id = $1
  AND log_date = $2;

-- Water total
SELECT COALESCE(SUM(amount_ml), 0) AS water_ml
FROM m8_water_logs
WHERE user_id = $1
  AND log_date = $2;

-- Health score
SELECT score
FROM m8_daily_health_scores
WHERE user_id = $1
  AND log_date = $2;
```

---

## GROUP 3 — Meals (Log Makanan)

### 3.1 List Meals by Date

- **Endpoint:** `GET` `/api/modul8/meals`
- **Tujuan:** Mengambil daftar makanan yang sudah di-log untuk tanggal tertentu (tampilan "Recently Uploaded" di Home).
- **Security/Auth:** Require Session
- **Request Query Params:**

| Param | Type | Required | Keterangan |
|---|---|---|---|
| `date` | string (ISO date) | ❌ | Default: hari ini |
| `limit` | integer | ❌ | Default: 20, max: 50 |
| `offset` | integer | ❌ | Default: 0 (pagination) |

- **Penjelasan Logika Database:** SELECT dari `m8_meals` dengan filter `user_id` (dari session) dan `log_date`. `WHERE user_id = $1` wajib ada sebelum kondisi lain untuk memastikan isolasi data antar user.
- **Secure SQL Query:**

```sql
SELECT id, meal_type, name, calories, protein_g, carbs_g,
       fats_g, fiber_g, sugar_g, sodium_mg, photo_url,
       source, ai_confidence, created_at
FROM m8_meals
WHERE user_id  = $1
  AND log_date = $2
ORDER BY created_at DESC
LIMIT $3 OFFSET $4;
```

---

### 3.2 Log a Meal

- **Endpoint:** `POST` `/api/modul8/meals`
- **Tujuan:** Mencatat satu item makanan ke log harian.
- **Security/Auth:** Require Session
- **Request Payload (Body JSON):**

| Field | Type | Required | Keterangan |
|---|---|---|---|
| `name` | string | ✅ | max 255 char |
| `calories` | integer | ✅ | ≥ 0 |
| `protein_g` | number | ❌ | ≥ 0 |
| `carbs_g` | number | ❌ | ≥ 0 |
| `fats_g` | number | ❌ | ≥ 0 |
| `fiber_g` | number | ❌ | ≥ 0 |
| `sugar_g` | number | ❌ | ≥ 0 |
| `sodium_mg` | number | ❌ | ≥ 0 |
| `meal_type` | string | ❌ | enum: `breakfast`, `lunch`, `dinner`, `snack` |
| `source` | string | ✅ | enum: `manual`, `saved`, `database`, `barcode`, `ai_scan` |
| `saved_food_id` | integer | ❌ | FK ke `m8_saved_foods`, hanya jika `source=saved` |
| `photo_url` | string | ❌ | base64 atau URL; max 2MB |
| `ai_confidence` | number | ❌ | 0.0–1.0; hanya jika `source=ai_scan` |
| `log_date` | string (ISO date) | ❌ | Default: hari ini; tidak boleh future date |

- **Penjelasan Logika Database:**
  1. Validasi: Jika `source=saved`, verifikasi bahwa `saved_food_id` dimiliki oleh `user_id` yang sama (cegah IDOR).
  2. Insert ke `m8_meals`. `user_id` **selalu** dari session, bukan dari body.
  3. Setelah insert, trigger rekalkulasi health score (upsert ke `m8_daily_health_scores`).

- **Secure SQL Query:**

```sql
-- Validasi kepemilikan saved_food (jika source = 'saved')
SELECT id FROM m8_saved_foods
WHERE id = $1 AND user_id = $2;

-- Insert meal
INSERT INTO m8_meals (
    user_id, log_date, meal_type, name,
    calories, protein_g, carbs_g, fats_g,
    fiber_g, sugar_g, sodium_mg,
    photo_url, source, ai_confidence, saved_food_id,
    created_at, updated_at
) VALUES (
    $1, $2, $3, $4,
    $5, $6, $7, $8,
    $9, $10, $11,
    $12, $13, $14, $15,
    NOW(), NOW()
)
RETURNING id, created_at;
```

---

### 3.3 Delete a Meal

- **Endpoint:** `DELETE` `/api/modul8/meals/{id}`
- **Tujuan:** Menghapus satu entry makanan dari log.
- **Security/Auth:** Require Session
- **URL Param:** `{id}` — integer, ID meal yang akan dihapus.
- **Penjelasan Logika Database:** DELETE dengan kondisi `id = $1 AND user_id = $2`. Kondisi `user_id` wajib untuk mencegah user menghapus data milik user lain (IDOR). Jika `0 rows affected` → return 404.
- **Secure SQL Query:**

```sql
DELETE FROM m8_meals
WHERE id      = $1
  AND user_id = $2
RETURNING id;
```

- **Response:** `204 No Content` jika berhasil, `404` jika tidak ditemukan atau bukan milik user.

---

## GROUP 4 — Saved Foods

### 4.1 List Saved Foods

- **Endpoint:** `GET` `/api/modul8/saved-foods`
- **Tujuan:** Mengambil daftar makanan tersimpan milik user (screen "Saved Foods").
- **Security/Auth:** Require Session
- **Request Query Params:**

| Param | Type | Keterangan |
|---|---|---|
| `q` | string | Search by name (ILIKE, bukan LIKE — case-insensitive) |
| `limit` | integer | Default: 20 |
| `offset` | integer | Default: 0 |

- **Penjelasan Logika Database:** SELECT dari `m8_saved_foods` dengan filter `user_id`. Jika query `q` ada, tambahkan `ILIKE` dengan wildcard hanya di sisi server untuk mencegah injection via wildcard characters.
- **Secure SQL Query:**

```sql
-- Tanpa search
SELECT id, name, brand, calories, protein_g, carbs_g, fats_g,
       fiber_g, sugar_g, sodium_mg, serving_size, serving_unit, source
FROM m8_saved_foods
WHERE user_id = $1
ORDER BY created_at DESC
LIMIT $2 OFFSET $3;

-- Dengan search (q di-escape wildcard di application layer)
SELECT id, name, brand, calories, protein_g, carbs_g, fats_g,
       fiber_g, sugar_g, sodium_mg, serving_size, serving_unit, source
FROM m8_saved_foods
WHERE user_id = $1
  AND name ILIKE $2
ORDER BY created_at DESC
LIMIT $3 OFFSET $4;
-- $2 = '%' || escaped_q || '%'  (wildcard ditambahkan di PHP, bukan diinject)
```

---

### 4.2 Save a Food

- **Endpoint:** `POST` `/api/modul8/saved-foods`
- **Tujuan:** Menyimpan makanan baru ke daftar saved foods user (dari manual, Open Food Facts, atau barcode scan).
- **Security/Auth:** Require Session
- **Request Payload (Body JSON):**

| Field | Type | Required |
|---|---|---|
| `name` | string | ✅ |
| `brand` | string | ❌ |
| `calories` | integer | ✅ |
| `protein_g` | number | ❌ |
| `carbs_g` | number | ❌ |
| `fats_g` | number | ❌ |
| `fiber_g` | number | ❌ |
| `sugar_g` | number | ❌ |
| `sodium_mg` | number | ❌ |
| `serving_size` | number | ❌ |
| `serving_unit` | string | ❌ |
| `barcode` | string | ❌ |
| `source` | string | ✅ | enum: `manual`, `database`, `barcode` |

- **Secure SQL Query:**

```sql
INSERT INTO m8_saved_foods (
    user_id, name, brand, calories,
    protein_g, carbs_g, fats_g, fiber_g, sugar_g, sodium_mg,
    serving_size, serving_unit, barcode, source,
    created_at, updated_at
) VALUES (
    $1, $2, $3, $4,
    $5, $6, $7, $8, $9, $10,
    $11, $12, $13, $14,
    NOW(), NOW()
)
RETURNING id;
```

---

### 4.3 Delete a Saved Food

- **Endpoint:** `DELETE` `/api/modul8/saved-foods/{id}`
- **Tujuan:** Menghapus item dari daftar saved foods.
- **Security/Auth:** Require Session
- **Penjelasan Logika Database:** Sama dengan delete meal — kondisi `AND user_id = $2` wajib. Karena `m8_meals.saved_food_id` menggunakan `ON DELETE SET NULL`, penghapusan saved food tidak akan menghapus meal log yang sudah ada.
- **Secure SQL Query:**

```sql
DELETE FROM m8_saved_foods
WHERE id      = $1
  AND user_id = $2
RETURNING id;
```

---

## GROUP 5 — Weight Logs (Progress Chart)

### 5.1 List Weight History

- **Endpoint:** `GET` `/api/modul8/weight-logs`
- **Tujuan:** Mengambil riwayat berat badan untuk chart di Progress screen.
- **Security/Auth:** Require Session
- **Request Query Params:**

| Param | Type | Keterangan |
|---|---|---|
| `range` | string | enum: `90d`, `6m`, `1y`, `all`. Default: `90d` |

- **Penjelasan Logika Database:** Filter berdasarkan `user_id` dan rentang waktu. Range `all` tetap menggunakan parameter — **tidak** menggunakan string concatenation untuk menyusun klausa tanggal dinamis.
- **Secure SQL Query:**

```sql
-- Range: 90d
SELECT log_date, weight_kg
FROM m8_weight_logs
WHERE user_id  = $1
  AND log_date >= CURRENT_DATE - INTERVAL '90 days'
ORDER BY log_date ASC;

-- Range: all
SELECT log_date, weight_kg
FROM m8_weight_logs
WHERE user_id = $1
ORDER BY log_date ASC;
```

---

### 5.2 Log a Weight

- **Endpoint:** `POST` `/api/modul8/weight-logs`
- **Tujuan:** Mencatat berat badan baru (dari weigh-in manual di Progress screen atau Account Details).
- **Security/Auth:** Require Session
- **Request Payload (Body JSON):**

| Field | Type | Required | Keterangan |
|---|---|---|---|
| `weight_kg` | number | ✅ | 20–500 |
| `log_date` | string | ❌ | Default: hari ini; tidak boleh future date |
| `note` | string | ❌ | max 500 char |

- **Penjelasan Logika Database:** Insert ke `m8_weight_logs`. Juga lakukan `UPDATE m8_user_profiles SET weight_kg = $2` agar nilai "Current Weight" di dashboard selalu fresh — dua operasi ini di-wrap dalam satu transaction.
- **Secure SQL Query:**

```sql
BEGIN;

INSERT INTO m8_weight_logs (user_id, weight_kg, log_date, note, created_at, updated_at)
VALUES ($1, $2, $3, $4, NOW(), NOW())
RETURNING id;

UPDATE m8_user_profiles
SET weight_kg  = $2,
    updated_at = NOW()
WHERE user_id  = $1;

COMMIT;
```

---

## GROUP 6 — Water Logs

### 6.1 Log Water Intake

- **Endpoint:** `POST` `/api/modul8/water-logs`
- **Tujuan:** Mencatat konsumsi air (setiap tap "+250ml / +500ml / +750ml" dari drawer Home).
- **Security/Auth:** Require Session
- **Request Payload (Body JSON):**

| Field | Type | Required | Keterangan |
|---|---|---|---|
| `amount_ml` | integer | ✅ | Nilai positif; enum sisi server: 50–5000 ml |
| `log_date` | string | ❌ | Default: hari ini |

- **Secure SQL Query:**

```sql
INSERT INTO m8_water_logs (user_id, log_date, amount_ml, logged_at, created_at, updated_at)
VALUES ($1, $2, $3, NOW(), NOW(), NOW())
RETURNING id, amount_ml;
```

- **Response (201 Created):** Mengembalikan `id` dan total air hari ini (`SUM(amount_ml)`).

---

## GROUP 7 — Health Scores

### 7.1 Get Daily Health Score

- **Endpoint:** `GET` `/api/modul8/health-scores`
- **Tujuan:** Mengambil health score harian. Jika belum ada atau sudah stale (computed > 30 menit lalu), trigger kalkulasi ulang di server.
- **Security/Auth:** Require Session
- **Request Query Params:**

| Param | Type | Keterangan |
|---|---|---|
| `date` | string | Default: hari ini |

- **Penjelasan Logika Database:**
  1. Cek apakah baris ada di `m8_daily_health_scores` dan `computed_at > NOW() - INTERVAL '30 minutes'`.
  2. Jika stale/tidak ada: hitung score dari net calorie deviation + macro deviation lalu UPSERT.
  3. Algoritma score: Mulai 100, kurangi poin berdasarkan `|net_cal - target| / target * 100` dan rata-rata deviasi 3 makro.

- **Secure SQL Query:**

```sql
-- Ambil score existing
SELECT score, calorie_deviation_pct, macro_deviation_pct, computed_at
FROM m8_daily_health_scores
WHERE user_id  = $1
  AND log_date = $2;

-- Upsert hasil kalkulasi baru
INSERT INTO m8_daily_health_scores (
    user_id, log_date, score,
    calorie_deviation_pct, macro_deviation_pct,
    computed_at, created_at, updated_at
) VALUES ($1, $2, $3, $4, $5, NOW(), NOW(), NOW())
ON CONFLICT (user_id, log_date) DO UPDATE SET
    score                 = EXCLUDED.score,
    calorie_deviation_pct = EXCLUDED.calorie_deviation_pct,
    macro_deviation_pct   = EXCLUDED.macro_deviation_pct,
    computed_at           = NOW(),
    updated_at            = NOW();
```

---

## GROUP 8 — AI Food Scan

### 8.1 Scan Food with AI

- **Endpoint:** `POST` `/api/modul8/ai-scans`
- **Tujuan:** Menerima foto makanan, memanggil Anthropic Claude vision API, mengembalikan prediksi nama + nutrisi untuk di-review user sebelum disimpan sebagai meal.
- **Security/Auth:** Require Session
- **Request Payload (Body JSON):**

| Field | Type | Required | Keterangan |
|---|---|---|---|
| `image_b64` | string | ✅ | Base64 JPEG, max 2MB setelah decode. **Tidak pernah disimpan di DB** pada endpoint ini |

- **Penjelasan Logika:**
  1. **Rate limit check:** Query `m8_ai_scan_quota`. Jika `scan_count >= 20` → return `429 Too Many Requests`.
  2. Resize validasi gambar di server (header magic bytes check: `FFD8FF` untuk JPEG).
  3. Panggil Anthropic Claude API dengan gambar + system prompt (di-cache karena statis).
  4. Parse JSON response dari LLM, validasi struktur.
  5. Atomic increment `scan_count` di `m8_ai_scan_quota`.
  6. **Tidak menyimpan gambar atau data user ke Anthropic** — hanya gambar makanan yang dikirim.
  7. Return prediksi ke frontend. User harus konfirmasi sebelum `POST /api/modul8/meals`.

- **Response (200 OK):**

```json
{
  "data": {
    "items": [
      {
        "name": "Grilled Chicken Breast",
        "estimated_grams": 150,
        "calories": 248,
        "protein_g": 46.5,
        "carbs_g": 0,
        "fats_g": 5.4,
        "confidence": 0.91
      }
    ],
    "notes": "High confidence detection. Portion estimated from plate size."
  },
  "quota": { "used": 5, "limit": 20 }
}
```

- **Secure SQL Query:**

```sql
-- Rate limit check (SELECT FOR UPDATE untuk concurrency safety)
SELECT scan_count
FROM m8_ai_scan_quota
WHERE user_id  = $1
  AND log_date = CURRENT_DATE
FOR UPDATE;

-- Increment atau insert quota
INSERT INTO m8_ai_scan_quota (user_id, log_date, scan_count, created_at, updated_at)
VALUES ($1, CURRENT_DATE, 1, NOW(), NOW())
ON CONFLICT (user_id, log_date) DO UPDATE
SET scan_count = m8_ai_scan_quota.scan_count + 1,
    updated_at = NOW()
WHERE m8_ai_scan_quota.scan_count < 20;
-- Jika 0 rows affected → quota habis, rollback dan return 429
```

---

### 8.2 Get AI Scan Quota

- **Endpoint:** `GET` `/api/modul8/ai-scans/quota`
- **Tujuan:** Menampilkan sisa quota AI scan harian user (untuk disable button di UI jika sudah 20/20).
- **Security/Auth:** Require Session
- **Secure SQL Query:**

```sql
SELECT scan_count
FROM m8_ai_scan_quota
WHERE user_id  = $1
  AND log_date = CURRENT_DATE;
-- Jika tidak ada baris → scan_count = 0 (belum pernah scan hari ini)
```

---

## HTTP Status Codes Reference

| Code | Kapan digunakan |
|---|---|
| `200 OK` | GET berhasil, data dikembalikan |
| `201 Created` | POST berhasil, resource baru dibuat |
| `204 No Content` | DELETE berhasil |
| `400 Bad Request` | Validasi payload gagal (field required, tipe salah) |
| `401 Unauthorized` | Session tidak valid / sudah expire |
| `403 Forbidden` | User mencoba akses resource milik user lain |
| `404 Not Found` | Resource tidak ditemukan atau bukan milik user |
| `413 Payload Too Large` | Gambar melebihi batas 2MB |
| `422 Unprocessable Entity` | Data valid secara format tapi gagal business rule (misal: future date) |
| `429 Too Many Requests` | AI scan quota harian habis |
| `500 Internal Server Error` | Error tak terduga — **pesan error internal tidak pernah di-expose ke client** |

---

## Prinsip Keamanan Lintas Endpoint

1. **Mandatory `user_id` from session:** Setiap WHERE clause yang mengakses data user **selalu** menyertakan `AND user_id = $session_user_id`. Ini single paling penting untuk mencegah IDOR.
2. **Parameterized everywhere:** Tidak ada satu pun nilai dari request yang di-interpolasi langsung ke string SQL.
3. **Wildcard escaping:** Untuk ILIKE search, karakter `%` dan `_` di input user di-escape di application layer sebelum dijadikan parameter `$N`.
4. **No PII to Anthropic:** Hanya gambar makanan yang dikirim ke Claude API — tidak ada `user_id`, nama, email, atau data profil.
5. **Transaction atomik:** Operasi multi-tabel (log weight + update profil, rate limit check + increment) selalu di-wrap dalam `BEGIN/COMMIT`.

---

## Implementasi Notes (PHP Backend)

- **Session-based auth:** `requireLogin()` dari `core/auth.php` wajib di-call di awal setiap action. `user_id` diambil eksklusif dari `$_SESSION['user_id']` atau `getCurrentUser()['id']`.
- **Request validation:** Gunakan `zod` atau `react-hook-form` di frontend; backend melakukan re-validation sebelum query database.
- **Response format:** Semua response di-wrap dalam struktur `{ data: {...}, error?: ... }` untuk konsistensi.
- **Rate limiting:** Gunakan cache (Redis/APCu) untuk track request count per user per endpoint jika diperlukan.
- **Logging:** Log semua query yang dijalankan (tanpa values, hanya statement template) untuk audit trail dan debugging.
