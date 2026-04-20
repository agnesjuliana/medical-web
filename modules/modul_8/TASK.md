# API Implementation Plan

---

- [ ] **Task 1: Backend Foundation — api.php Router, Auth Guard & Response Utilities**
  - **Objective**: Create the single-entry-point `api.php` file that handles authentication, request parsing, JSON response formatting, and action-based routing. This is the prerequisite for every subsequent task.
  - **Target Files**: `modules/modul_8/api.php`
  - **Low-Level Implementation Steps**:
    1. Create file `modules/modul_8/api.php`. At the top, require these three files in order:
       ```php
       require_once __DIR__ . '/../../config/database.php';
       require_once __DIR__ . '/../../core/auth.php';
       require_once __DIR__ . '/../../core/session.php';
       ```
    2. Set response headers immediately after the requires (before any output):
       ```php
       header('Content-Type: application/json; charset=utf-8');
       header('X-Content-Type-Options: nosniff');
       ```
    3. Call `requireLogin()` (from `core/auth.php`). This function already redirects unauthenticated users — do NOT modify it. If it returns, the user is authenticated.
    4. Read `$userId` from session: `$userId = (int) $_SESSION['user_id'];`. This is the only trusted source of user identity — never read user_id from `$_GET` or `$_POST`.
    5. Parse the action from request: `$action = $_GET['action'] ?? $_POST['action'] ?? '';`. If action is empty, output `{"error":"action required"}` with HTTP 400 and `exit`.
    6. Parse the JSON request body once and store globally:
       ```php
       $body = json_decode(file_get_contents('php://input'), true) ?? [];
       ```
    7. Get the PDO connection: `$pdo = getDBConnection();`
    8. Define two helper functions (place before the router switch):
       - `function json_success($data, int $code = 200): never` — calls `http_response_code($code)`, echoes `json_encode(['data' => $data])`, then `exit`.
       - `function json_error(string $message, int $code = 400): never` — calls `http_response_code($code)`, echoes `json_encode(['error' => $message])`, then `exit`.
    9. Build the action router using a `switch ($action)` block. Add one `case` per action string. Unsupported actions fall to `default:` which calls `json_error('Unknown action', 404)`. The initial skeleton cases (to be filled in Tasks 2–4):
       ```
       case 'get_profile'    →  (Task 2)
       case 'save_profile'   →  (Task 2)
       case 'get_dashboard'  →  (Task 2)
       case 'list_meals'     →  (Task 3)
       case 'log_meal'       →  (Task 3)
       case 'delete_meal'    →  (Task 3)
       case 'list_saved_foods'  →  (Task 3)
       case 'save_food'      →  (Task 3)
       case 'delete_saved_food' →  (Task 3)
       case 'log_water'      →  (Task 3)
       case 'list_weight_logs'  →  (Task 3)
       case 'log_weight'     →  (Task 3)
       case 'get_health_scores' →  (Task 4)
       case 'ai_scan_food'   →  (Task 4)
       case 'get_ai_quota'   →  (Task 4)
       ```
    10. Wrap the entire switch in a try/catch: catch `PDOException` → call `json_error('Database error', 500)`. This prevents raw SQL errors from leaking to the client.
  - **Expected Outcome / Acceptance Criteria**: `GET modules/modul_8/api.php?action=unknown` returns HTTP 404 `{"error":"Unknown action"}`. `GET api.php` (no action) returns HTTP 400 `{"error":"action required"}`. An unauthenticated request is redirected by `requireLogin()`. No PHP warnings or notices are emitted.

---

- [ ] **Task 2: User Profile & Dashboard Endpoints**
  - **Objective**: Implement the `get_profile`, `save_profile`, and `get_dashboard` action handlers inside `api.php`. Profile save must compute and persist TDEE-based daily targets. Dashboard must aggregate today's meals, water, and compute remaining nutrients.
  - **Target Files**: `modules/modul_8/api.php`
  - **Low-Level Implementation Steps**:

    **--- Action: get_profile ---**
    1. Inside `case 'get_profile':`, run this prepared statement:
       ```sql
       SELECT user_id, gender, birth_date, height_cm, weight_kg,
              activity_level, goal, goal_weight_kg, step_goal,
              barriers, daily_calorie_target, daily_protein_g,
              daily_carbs_g, daily_fats_g, onboarded_at
       FROM m8_user_profiles
       WHERE user_id = ?
       ```
       Bind `[$userId]`.
    2. If `$stmt->rowCount() === 0`, call `json_error('Profile not found', 404)`.
    3. Fetch the row as associative array. Cast numeric columns (`height_cm`, `weight_kg`, `goal_weight_kg`, `daily_calorie_target`, `daily_protein_g`, `daily_carbs_g`, `daily_fats_g`, `step_goal`) to the correct PHP types (int or float). The `barriers` column is a PostgreSQL `TEXT[]` — PDO returns it as a string like `{item1,item2}`. Parse it: `$row['barriers'] = array_filter(explode(',', trim($row['barriers'], '{}')));`
    4. Call `json_success($row)`.

    **--- Action: save_profile ---** 5. Inside `case 'save_profile':`, read and validate these fields from `$body`. For each field, if missing or invalid type, call `json_error('Invalid field: <name>', 422)`:
    - `gender` → must be `'male'` or `'female'` (string, exact match)
    - `birth_date` → must be a string matching regex `/^\d{4}-\d{2}-\d{2}$/`
    - `height_cm` → must be numeric and > 0
    - `weight_kg` → must be numeric and > 0
    - `activity_level` → must be one of `['beginner', 'active', 'athlete']`
    - `goal` → must be one of `['lose', 'maintain', 'gain']`
    - `goal_weight_kg` → optional, numeric > 0 if present, else null
    - `step_goal` → optional int, default 10000
    - `barriers` → optional array of strings, default `[]`
    6. Compute BMR using Mifflin-St Jeor. First, calculate age from `birth_date`:
       ```php
       $age = (int) date_diff(date_create($birth_date), date_create('today'))->y;
       ```
       Then:
       ```php
       $bmr = (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age);
       $bmr += ($gender === 'male') ? 5 : -161;
       ```
    7. Multiply BMR by activity factor: `beginner → 1.375`, `active → 1.55`, `athlete → 1.725`.
       ```php
       $factors = ['beginner' => 1.375, 'active' => 1.55, 'athlete' => 1.725];
       $tdee = $bmr * $factors[$activity_level];
       ```
    8. Adjust TDEE by goal: `lose → $tdee - 500`, `maintain → $tdee`, `gain → $tdee + 500`. Round to int.
    9. Compute macro targets from calorie target:
       - `protein_g = round(($calorie_target * 0.30) / 4)`
       - `carbs_g   = round(($calorie_target * 0.40) / 4)`
       - `fats_g    = round(($calorie_target * 0.30) / 9)`
    10. Convert barriers array to PostgreSQL array literal: `'{' . implode(',', array_map('pg_escape_string_is_not_available_use_pdo', $barriers)) . '}'`. Instead, pass barriers as a PHP array joined to string in the bind: `'{' . implode(',', $barriers) . '}'` (barriers strings have already been validated as safe strings from the JSON body).
    11. Execute UPSERT with a single prepared statement:
        ```sql
        INSERT INTO m8_user_profiles
            (user_id, gender, birth_date, height_cm, weight_kg,
             activity_level, goal, goal_weight_kg, step_goal, barriers,
             daily_calorie_target, daily_protein_g, daily_carbs_g,
             daily_fats_g, onboarded_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
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
            onboarded_at         = COALESCE(m8_user_profiles.onboarded_at, NOW()),
            updated_at           = NOW()
        ```
        Bind parameters in order: `[$userId, $gender, $birth_date, $height_cm, $weight_kg, $activity_level, $goal, $goal_weight_kg, $step_goal, $barriers_str, $calorie_target, $protein_g, $carbs_g, $fats_g]`.
    12. Call `json_success(['saved' => true, 'daily_calorie_target' => $calorie_target], 200)`.

    **--- Action: get_dashboard ---** 13. Inside `case 'get_dashboard':`, read `$date = $_GET['date'] ?? date('Y-m-d')`. Validate that `$date` matches `/^\d{4}-\d{2}-\d{2}$/`, else call `json_error('Invalid date', 422)`. 14. Fetch user profile targets with the same query from step 1. If not found, call `json_error('Profile not found. Complete onboarding first.', 404)`. 15. Fetch daily meal aggregates:
    `sql
    SELECT COALESCE(SUM(calories),0) AS total_calories,
           COALESCE(SUM(protein_g),0) AS total_protein_g,
           COALESCE(SUM(carbs_g),0)   AS total_carbs_g,
           COALESCE(SUM(fats_g),0)    AS total_fats_g,
           COALESCE(SUM(fiber_g),0)   AS total_fiber_g
    FROM m8_meals
    WHERE user_id = ? AND log_date = ?
    `
    Bind `[$userId, $date]`. 16. Fetch daily water total:
    `sql
    SELECT COALESCE(SUM(amount_ml),0) AS total_water_ml
    FROM m8_water_logs
    WHERE user_id = ? AND log_date = ?
    `
    Bind `[$userId, $date]`. 17. Fetch the 5 most recent meals for "recently eaten" list:
    `sql
    SELECT id, meal_type, name, calories, protein_g, carbs_g, fats_g,
           photo_url, source, created_at
    FROM m8_meals
    WHERE user_id = ? AND log_date = ?
    ORDER BY created_at DESC
    LIMIT 5
    `
    Bind `[$userId, $date]`. 18. Compute remaining values: `calories_remaining = profile.daily_calorie_target - total_calories`. Apply same subtraction for protein_g, carbs_g, fats_g. Values can go negative (user exceeded target) — do NOT clamp to 0. 19. Call `json_success` with this exact shape:
    `json
    {
      "date": "2026-04-19",
      "targets": {
        "calories": 1800,
        "protein_g": 135,
        "carbs_g": 180,
        "fats_g": 60
      },
      "consumed": {
        "calories": 620,
        "protein_g": 42,
        "carbs_g": 80,
        "fats_g": 18,
        "fiber_g": 5,
        "water_ml": 750
      },
      "remaining": {
        "calories": 1180,
        "protein_g": 93,
        "carbs_g": 100,
        "fats_g": 42
      },
      "recent_meals": [ ...array of meal rows... ]
    }
    `

  - **Expected Outcome / Acceptance Criteria**:
    - `GET api.php?action=get_profile` returns HTTP 200 with user profile data, or HTTP 404 if not onboarded yet.
    - `POST api.php?action=save_profile` with valid body returns HTTP 200 `{"data":{"saved":true,"daily_calorie_target":1800}}`. Sending the same request again (upsert) returns the same success response without error.
    - `POST api.php?action=save_profile` with `gender:"invalid"` returns HTTP 422 `{"error":"Invalid field: gender"}`.
    - `GET api.php?action=get_dashboard` returns HTTP 200 with the nested structure above.

---

- [ ] **Task 3: Food & Nutrition Logging Endpoints**
  - **Objective**: Implement all CRUD action handlers for meals, saved foods, water logs, and weight logs inside `api.php`. Every delete and fetch operation must enforce `user_id` ownership to prevent IDOR.
  - **Target Files**: `modules/modul_8/api.php`
  - **Low-Level Implementation Steps**:

    **--- Action: list_meals ---**
    1. Read `$date = $_GET['date'] ?? date('Y-m-d')`. Validate date format `/^\d{4}-\d{2}-\d{2}$/`, else `json_error('Invalid date', 422)`.
    2. Run:
       ```sql
       SELECT id, meal_type, name, calories, protein_g, carbs_g, fats_g,
              fiber_g, sugar_g, sodium_mg, serving_size, photo_url,
              source, ai_confidence, saved_food_id, created_at
       FROM m8_meals
       WHERE user_id = ? AND log_date = ?
       ORDER BY created_at ASC
       ```
       Bind `[$userId, $date]`.
    3. Call `json_success($stmt->fetchAll(PDO::FETCH_ASSOC))`.

    **--- Action: log_meal ---** 4. Read from `$body` and validate:
    - `name` → required, non-empty string (trim and check `strlen > 0`)
    - `calories` → required, int ≥ 0
    - `protein_g`, `carbs_g`, `fats_g` → required, numeric ≥ 0
    - `fiber_g`, `sugar_g`, `sodium_mg` → optional, numeric ≥ 0, default 0
    - `meal_type` → optional, must be one of `['breakfast','lunch','dinner','snack']` or null
    - `log_date` → optional, string matching date regex, default `date('Y-m-d')`
    - `serving_size` → optional, numeric > 0 or null
    - `photo_url` → optional, string or null (base64 data URI for MVP)
    - `source` → optional, must be one of `['manual','saved','database','barcode','ai_scan']`, default `'manual'`
    - `ai_confidence` → optional, float between 0 and 1, required only when `source === 'ai_scan'`
    - `saved_food_id` → optional, int or null
    5. Run INSERT:
       ```sql
       INSERT INTO m8_meals
           (user_id, log_date, meal_type, name, calories, protein_g,
            carbs_g, fats_g, fiber_g, sugar_g, sodium_mg,
            serving_size, photo_url, source, ai_confidence, saved_food_id)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
       RETURNING id, created_at
       ```
       Bind all 16 values in order.
    6. Fetch the returned row. Call `json_success(['id' => $row['id'], 'created_at' => $row['created_at']], 201)`.

    **--- Action: delete_meal ---** 7. Read `$id = (int)($body['id'] ?? 0)`. If `$id <= 0`, call `json_error('Invalid id', 422)`. 8. Run:

    ```sql
    DELETE FROM m8_meals WHERE id = ? AND user_id = ?
    ```

    Bind `[$id, $userId]`. The `AND user_id = ?` clause is mandatory — it prevents any user from deleting another user's meal. 9. If `$stmt->rowCount() === 0`, call `json_error('Meal not found', 404)`. 10. Call `json_success(['deleted' => true])`.

    **--- Action: list_saved_foods ---** 11. Run:
    `sql
    SELECT id, name, brand, calories, protein_g, carbs_g, fats_g,
           fiber_g, sugar_g, sodium_mg, serving_size, serving_unit,
           barcode, source, created_at
    FROM m8_saved_foods
    WHERE user_id = ?
    ORDER BY created_at DESC
    `
    Bind `[$userId]`. Call `json_success($rows)`.

    **--- Action: save_food ---** 12. Read from `$body` and validate: - `name` → required, non-empty string - `calories` → required, int ≥ 0 - `protein_g`, `carbs_g`, `fats_g` → required, numeric ≥ 0 - `fiber_g`, `sugar_g`, `sodium_mg` → optional numeric ≥ 0, default 0 - `brand` → optional string or null - `serving_size` → optional numeric > 0 or null - `serving_unit` → optional string or null (e.g., `'g'`, `'ml'`, `'piece'`) - `barcode` → optional string or null - `source` → optional, must be one of `['manual','database','barcode']`, default `'manual'` 13. Run:
    `sql
    INSERT INTO m8_saved_foods
        (user_id, name, brand, calories, protein_g, carbs_g, fats_g,
         fiber_g, sugar_g, sodium_mg, serving_size, serving_unit, barcode, source)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    RETURNING id, created_at
    ` 14. Call `json_success(['id' => $row['id']], 201)`.

    **--- Action: delete_saved_food ---** 15. Read `$id = (int)($body['id'] ?? 0)`. If `$id <= 0`, call `json_error('Invalid id', 422)`. 16. Run:
    `sql
    DELETE FROM m8_saved_foods WHERE id = ? AND user_id = ?
    `
    Bind `[$id, $userId]`. If `rowCount() === 0`, call `json_error('Saved food not found', 404)`. 17. Call `json_success(['deleted' => true])`.

    **--- Action: log_water ---** 18. Read from `$body`: - `amount_ml` → required, int > 0 (valid values: 250, 500, 750, or any positive int) - `log_date` → optional, date string, default `date('Y-m-d')` 19. If `amount_ml <= 0`, call `json_error('amount_ml must be > 0', 422)`. 20. Run:
    `sql
    INSERT INTO m8_water_logs (user_id, log_date, amount_ml)
    VALUES (?, ?, ?)
    RETURNING id, logged_at
    ` 21. Call `json_success(['id' => $row['id'], 'logged_at' => $row['logged_at']], 201)`.

    **--- Action: list_weight_logs ---** 22. Read `$limit = min((int)($_GET['limit'] ?? 30), 90)`. Clamp between 1 and 90. 23. Run:
    `sql
    SELECT id, weight_kg, log_date, note, created_at
    FROM m8_weight_logs
    WHERE user_id = ?
    ORDER BY log_date DESC
    LIMIT ?
    `
    Bind `[$userId, $limit]`. Call `json_success($rows)`.

    **--- Action: log_weight ---** 24. Read from `$body`: - `weight_kg` → required, numeric > 0 - `log_date` → optional, date string, default `date('Y-m-d')` - `note` → optional string or null 25. Run:
    `sql
    INSERT INTO m8_weight_logs (user_id, weight_kg, log_date, note)
    VALUES (?, ?, ?, ?)
    RETURNING id, created_at
    ` 26. Also update the profile's current weight snapshot:
    `sql
    UPDATE m8_user_profiles SET weight_kg = ?, updated_at = NOW()
    WHERE user_id = ?
    `
    Bind `[$weight_kg, $userId]`. This keeps the profile weight in sync with the latest log. 27. Call `json_success(['id' => $row['id']], 201)`.

  - **Expected Outcome / Acceptance Criteria**:
    - `POST api.php?action=log_meal` with valid body returns HTTP 201 `{"data":{"id":42,"created_at":"..."}}`.
    - `POST api.php?action=delete_meal` with `{"id":42}` from a different user returns HTTP 404 (IDOR blocked by `user_id` clause).
    - `POST api.php?action=log_water` with `{"amount_ml":-100}` returns HTTP 422.
    - `GET api.php?action=list_meals&date=2026-04-19` returns HTTP 200 with an array (may be empty `[]`).
    - `GET api.php?action=list_saved_foods` returns HTTP 200 with array sorted newest-first.

---

- [ ] **Task 4: AI Food Scan & Health Score Endpoints**
  - **Objective**: Implement `ai_scan_food`, `get_ai_quota`, and `get_health_scores` action handlers. AI scan must enforce a 20-scan/day rate limit atomically using SELECT FOR UPDATE, call the Gemini AI API via cURL, and return structured predictions. Health score reads from `m8_daily_health_scores`.
  - **Target Files**: `modules/modul_8/api.php`
  - **Low-Level Implementation Steps**:

    **--- Action: get_ai_quota ---**
    1. Run:
       ```sql
       SELECT scan_count FROM m8_ai_scan_quota
       WHERE user_id = ? AND log_date = CURRENT_DATE
       ```
       Bind `[$userId]`. If no row found, `scan_count = 0`.
    2. Call `json_success(['used' => (int)$scan_count, 'limit' => 20, 'remaining' => max(0, 20 - (int)$scan_count)])`.

    **--- Action: ai_scan_food ---** 3. Read `$image_b64 = $body['image_b64'] ?? ''`. If empty, call `json_error('image_b64 required', 422)`. 4. Validate that `$image_b64` starts with `data:image/` (the frontend must send a data URI). Strip the `data:image/...;base64,` prefix to get raw base64: `$rawB64 = preg_replace('/^data:image\/[a-z]+;base64,/', '', $image_b64)`. If `base64_decode($rawB64, true) === false`, call `json_error('Invalid image encoding', 422)`. 5. **Rate limit check (atomic)**: Begin a transaction with `$pdo->beginTransaction()`. 6. Inside the transaction, run:

    ```sql
    SELECT scan_count FROM m8_ai_scan_quota
    WHERE user_id = ? AND log_date = CURRENT_DATE
    FOR UPDATE
    ```

    Bind `[$userId]`. `FOR UPDATE` locks the row to prevent concurrent increments. 7. If no row found, `$currentCount = 0`. If `$currentCount >= 20`, call `$pdo->rollBack()` then `json_error('Daily AI scan limit reached (20/day)', 429)`. 8. UPSERT the quota row:

    ```sql
    INSERT INTO m8_ai_scan_quota (user_id, log_date, scan_count)
    VALUES (?, CURRENT_DATE, 1)
    ON CONFLICT (user_id, log_date) DO UPDATE
        SET scan_count = m8_ai_scan_quota.scan_count + 1,
            updated_at = NOW()
    ```

    Bind `[$userId]`. Then `$pdo->commit()`. 9. Read the Anthropic API key: `$apiKey = getenv('ANTHROPIC_API_KEY')`. If empty, call `json_error('AI service not configured', 503)`. 10. Build the request payload for the Anthropic Messages API. Use model `claude-haiku-4-5-20251001` (cheaper, sufficient for food detection). The payload must be a PHP array encoded to JSON:
    `php
    $payload = [
      'model'      => 'claude-haiku-4-5-20251001',
      'max_tokens' => 1024,
      'system'     => 'You are a food nutrition expert. When given a food photo, identify all visible food items and estimate their nutritional content. Always respond with valid JSON only — no prose, no markdown code fences.',
      'messages'   => [[
        'role'    => 'user',
        'content' => [
          [
            'type'   => 'image',
            'source' => [
              'type'       => 'base64',
              'media_type' => 'image/jpeg',
              'data'       => $rawB64,
            ],
          ],
          [
            'type' => 'text',
            'text' => 'Analyze this food photo and return JSON with this exact schema: {"items":[{"name":"string","estimated_grams":number,"calories":number,"protein_g":number,"carbs_g":number,"fats_g":number,"confidence":number}],"notes":"string"}. confidence is 0.0–1.0. Return ONLY valid JSON.',
          ],
        ],
      ]],
    ];
    ` 11. Send the cURL request:
    `php
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => json_encode($payload),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT        => 30,
      CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
      ],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    ` 12. If `$response === false` or `$httpCode !== 200`, call `json_error('AI service error, please try again or log manually', 502)`. 13. Decode the Anthropic response: `$anthropicData = json_decode($response, true)`. Extract the text content: `$text = $anthropicData['content'][0]['text'] ?? ''`. 14. Attempt to parse the LLM JSON output: `$prediction = json_decode($text, true)`. If `$prediction === null` or `!isset($prediction['items'])`, call `json_error('AI could not parse the food image. Please log manually.', 422)`. 15. Validate each item in `$prediction['items']`: ensure keys `name`, `calories`, `protein_g`, `carbs_g`, `fats_g`, `confidence` exist and are numeric where expected. Skip malformed items rather than rejecting the whole response. 16. Call `json_success($prediction)`. The frontend will display the items in a review screen; the user must explicitly confirm before any meal is saved (saving calls `log_meal` separately).

    **--- Action: get_health_scores ---** 17. Read `$days = min((int)($_GET['days'] ?? 7), 30)`. Clamp between 1 and 30. 18. Run:
    `sql
    SELECT log_date, score, calorie_deviation_pct,
           macro_deviation_pct, computed_at
    FROM m8_daily_health_scores
    WHERE user_id = ?
    ORDER BY log_date DESC
    LIMIT ?
    `
    Bind `[$userId, $days]`. Call `json_success($rows)`.

    **--- Health Score Computation (called from get_dashboard) ---** 19. The health score for a given date is computed and **upserted into `m8_daily_health_scores`** each time `get_dashboard` is called for that date. Add this logic to the `get_dashboard` case in Task 2, after computing `$consumed`. 20. Compute calorie deviation: `$calDev = abs($consumed['calories'] - $targets['calories']) / max($targets['calories'], 1) * 100`. Round to 2 decimal places. 21. Compute macro deviation (average of protein, carbs, fats): calculate per-macro `abs(consumed - target) / max(target, 1) * 100` for each, then average the three. Round to 2 decimal places. 22. Compute score: start at 100. Subtract `min(30, $calDev * 0.5)` for calorie deviation. Subtract `min(30, $macroDev * 0.5)` for macro deviation. Clamp result between 0 and 100. Cast to int. 23. Upsert the score:
    `sql
    INSERT INTO m8_daily_health_scores
        (user_id, log_date, score, calorie_deviation_pct, macro_deviation_pct)
    VALUES (?, ?, ?, ?, ?)
    ON CONFLICT (user_id, log_date) DO UPDATE SET
        score                 = EXCLUDED.score,
        calorie_deviation_pct = EXCLUDED.calorie_deviation_pct,
        macro_deviation_pct   = EXCLUDED.macro_deviation_pct,
        computed_at           = NOW(),
        updated_at            = NOW()
    `
    Bind `[$userId, $date, $score, $calDev, $macroDev]`. 24. Include the score in the `get_dashboard` response under key `"health_score": 84`.

  - **Expected Outcome / Acceptance Criteria**:
    - `GET api.php?action=get_ai_quota` returns HTTP 200 `{"data":{"used":3,"limit":20,"remaining":17}}`.
    - `POST api.php?action=ai_scan_food` with a valid base64 JPEG returns HTTP 200 with `{"data":{"items":[...],"notes":"..."}}`.
    - `POST api.php?action=ai_scan_food` after 20 uses in one day returns HTTP 429 `{"error":"Daily AI scan limit reached (20/day)"}`.
    - `POST api.php?action=ai_scan_food` with missing `image_b64` returns HTTP 422.
    - `GET api.php?action=get_health_scores&days=7` returns HTTP 200 with up to 7 rows sorted newest-first.
    - `GET api.php?action=get_dashboard` includes `"health_score": <int>` in the response body and a new row is upserted into `m8_daily_health_scores`.
