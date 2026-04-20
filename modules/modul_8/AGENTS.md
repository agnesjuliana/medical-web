## Context Navigation

When you need to understand the codebase, docs, or any files in this project:

1. ALWAYS query the knowledge graph first: `/graphify query "your question"`
2. Only read raw files if I explicitly say "read the file" or "look at the raw file"
3. Use `graphify-out/wiki/index.md` as your navigation entrypoint for browsing structure

Codex will review your output once you are done

## graphify

This project has a graphify knowledge graph at graphify-out/.

Rules:

- Before answering architecture or codebase questions, read graphify-out/GRAPH_REPORT.md for god nodes and community structure
- If graphify-out/wiki/index.md exists, navigate it instead of reading raw files
- After modifying code files in this session, run `graphify update .` to keep the graph current (AST-only, no API cost)

# PRD — Calorie Tracker MVP (modul_8) (Mobile First UI)

> Spec produk untuk aplikasi tracking kalori di `modules/modul_8`. Dokumen ini jadi sumber kebenaran; update di sini kalau scope berubah.

## 1. Product Overview

- **Nama produk:** Calorie Tracker (modul 8)
- **Tujuan:** MVP tugas kuliah — aplikasi mobile-first untuk tracking asupan kalori, makro (protein/karbo/lemak), aktivitas olahraga, langkah harian, dan skor kesehatan.
- **Target user:** mahasiswa / user umum yang ingin monitor nutrisi harian; login tunggal via sistem auth medical-web (`window.__USER__`).
- **Kompleksitas:** Advanced — PWA installable, offline-capable, animasi transisi halus, AI food recognition.
- **Storage:** Backend PHP + PostgreSQL (Prisma-hosted) via `config/database.php` & `core/session.php` yang sudah ada.

## 2. User Flow (high-level)

1. User login di sistem medical-web (PHP) → diarahkan ke `modules/modul_8/`.
2. Kalau profil belum lengkap → **Onboarding** 6 langkah (Gender → Activity level → BB/TB → Birth date → Goal → selesai dengan kalkulasi TDEE).
3. Setelah onboarding → **Home** (dashboard kalori + makro + recently eaten).
4. Tab bar bawah: **Home / Progress / Settings**.
5. Floating "+" button di Home / Progress → modal pilih: Log Exercise, Log Food, Saved Foods, Food Database, **Scan Food (AI)**, Scan Barcode.

## 3. Screen Spec (mengikuti sketch)

| #   | Screen                  | Elemen utama                                                                                               |
| --- | ----------------------- | ---------------------------------------------------------------------------------------------------------- |
| 1   | Onboarding — Welcome    | Logo/foto makanan + CTA mulai                                                                              |
| 2   | Onboarding — Gender     | Male / Female                                                                                              |
| 3   | Onboarding — Activity   | 0–2 (pemula) / 3–5 (aktif) / 6+ (atlet)                                                                    |
| 4   | Onboarding — Body       | Height (cm) + Weight (kg)                                                                                  |
| 5   | Onboarding — Birth date | Bulan / Tanggal / Tahun                                                                                    |
| 6   | Onboarding — Goal       | Turun / Maintain / Naik berat                                                                              |
| 7   | Home                    | Weekday strip, Calories Left ring, ring Protein/Carbs/Fats, Recently Eaten list, foto makanan terbaru      |
| 8   | Progress                | Step counter (0/10.000), kalori dibakar hari ini, **Health Score** card, weekly chart                      |
| 9   | Log Exercise modal      | Run / Weight lift / Describe / Manual                                                                      |
| 10  | Set Intensity           | High / Medium / Low + Duration (15/30/45/60 menit)                                                         |
| 11  | Exercise Result         | Kalori terbakar dihitung, notif muncul                                                                     |
| 12  | Food Database           | Search Open Food Facts by nama                                                                             |
| 13  | Saved Foods             | List makanan yang sudah di-save user, quick-add                                                            |
| 14  | Scan Barcode            | Kamera live ZXing → lookup barcode di Open Food Facts                                                      |
| 15  | **Scan Food (AI)**      | Ambil foto makanan → LLM vision → prediksi nama + estimasi kalori/makro → review & konfirmasi sebelum save |

## 4. Feature Scope (MVP)

### In-scope

**Onboarding & profile**

- Wizard 6 langkah dengan kalkulasi target kalori harian (rumus **Mifflin-St Jeor** → TDEE × activity factor → ± deficit/surplus 500 kcal sesuai goal).

**Home dashboard**

- Ring kalori sisa, ring makro (protein/karbo/lemak), weekday strip 7 hari terakhir.
- List "recently eaten" + foto makanan terbaru.

**Log food — 5 cara input:**

1. **Manual** — nama + kalori + protein/karbo/lemak + foto opsional.
2. **Saved Foods** — quick-add dari item yang sudah di-save.
3. **Food Database** — search Open Food Facts (gratis, tanpa API key).
4. **Scan Barcode** — `@zxing/browser` via kamera HP → lookup Open Food Facts by barcode.
5. **Scan Food dengan AI** — ambil/upload foto → backend panggil LLM vision → return estimasi makanan + kalori + makro → user review & edit sebelum simpan.

**Log exercise**

- Run / Weight lift / Describe (free text) / Manual (input kalori langsung).
- Untuk Run & Weight: pilih intensity + duration → kalkulasi otomatis **MET × weight × hours**.

**Progress**

- Step counter manual (input user), kalori dibakar, health score.
- Weekly chart kalori in/out via `recharts`.

**PWA & UX**

- Manifest + service worker via `vite-plugin-pwa`, offline shell, stale-while-revalidate untuk API.
- Page transition (View Transitions API), ring fill animasi.

### Out-of-scope (MVP)

- Integrasi pedometer native (butuh Capacitor). Fallback: input manual.
- Meal plan / recipe suggestion.
- Social features, sharing.
- Push notifications.
- Upload foto ke cloud storage eksternal.

## 5. AI Food Scan — detail spec

Feature ini adalah value-add utama selain barcode/DB lookup.

**Flow:**

1. User tap "Scan Food (AI)" dari `+` menu.
2. Pilih: **Ambil foto** (kamera) atau **Upload dari gallery**.
3. Frontend resize foto max 1024px, convert ke base64 JPEG quality 0.85.
4. POST ke `api.php?action=ai_scan_food` dengan `image_b64`.
5. Backend panggil LLM vision API:
   - **Provider:** Anthropic Claude (model: `claude-sonnet-4-6`, mendukung vision).
   - **API key:** disimpan di `.env` sebagai `ANTHROPIC_API_KEY`, load via `config/database.php` env parser.
   - **Prompt:** structured output — minta LLM return JSON `{ items: [{ name, estimated_grams, calories, protein_g, carbs_g, fats_g, confidence }], notes }`.
   - **Prompt cache:** system prompt (instruksi + format JSON) di-cache karena statis → hemat token.
6. Backend validate JSON response, return ke frontend.
7. Frontend tampilkan **review screen**: list prediksi dengan confidence badge, semua field editable.
8. User konfirmasi → simpan sebagai meal (source = `ai_scan`, simpan photo base64 di kolom `photo_url`).

**Error handling:**

- Kalau LLM gagal parse / confidence terlalu rendah → fallback ke form manual dengan foto sudah terisi.
- Rate limit per user: max 20 scan/hari (tracked di `m8_ai_scan_quota`).
- Timeout 30 detik; kalau overrun tampilkan toast "AI sedang sibuk, coba lagi atau input manual".

**Privacy:**

- Foto makanan dikirim ke Anthropic API untuk inference saja — disebutkan di consent screen pertama kali user pakai fitur ini.
- Tidak ada data user lain (nama, email) yang dikirim bersama gambar.

## 6. Technical Spec

**Frontend (`modules/modul_8/app`):**

- Vite 8 + React 19 + TypeScript (existing).
- Tailwind 4 + shadcn/ui (existing, 50+ komponen).
- **Routing:** `react-router-dom` v6 (hash router agar kompatibel dengan PHP base path `/medical-web/modules/modul_8/`).
- **State:** `zustand` untuk client store (current user profile, daily totals cache).
- **Forms:** `react-hook-form` + `zod` untuk onboarding & log dialogs.
- **API client:** native `fetch` + tipis wrapper di `src/lib/api.ts` dengan base URL `BASE_URL + '/modules/modul_8/api.php'`.
- **Chart:** `recharts` (existing).
- **Barcode:** `@zxing/browser` + `@zxing/library`.
- **Camera & image resize:** `react-webcam` atau native `<input type="file" capture>` + canvas resize.
- **PWA:** `vite-plugin-pwa` dengan workbox (precaching + runtime caching).
- **Animasi:** CSS transitions + View Transitions API; Framer Motion optional.

**Backend (`modules/modul_8/`):**

- `index.php` (existing) — serve React app, pass `window.__USER__`.
- `api.php` (baru) — single endpoint, dispatch berdasarkan `action` param:
  - `get_profile`, `save_profile` (upsert)
  - `log_meal`, `list_meals` (by date), `delete_meal`
  - `log_exercise`, `list_exercises`, `delete_exercise`
  - `set_steps`, `get_steps`
  - `save_food`, `list_saved_foods`
  - `ai_scan_food` — panggil Anthropic Claude vision API, return JSON prediksi
  - `get_dashboard` — aggregate meals + exercises + steps + targets untuk tanggal tertentu
- Auth guard: reuse `requireLogin()` dari `core/auth.php`; user_id dari `$_SESSION['user_id']`.
- DB: PostgreSQL via `getDBConnection()` (PDO singleton di `config/database.php`).
- HTTP client ke Anthropic: pakai cURL dalam PHP (tidak perlu library tambahan).

**Database schema (prefix `m8_` untuk isolasi):**

- `m8_user_profiles (user_id PK, gender, birth_date, height_cm, weight_kg, activity_level, goal, daily_calorie_target, daily_protein_g, daily_carbs_g, daily_fats_g, onboarded_at)`
- `m8_meals (id, user_id, log_date, name, calories, protein_g, carbs_g, fats_g, photo_url, source)`
- `m8_exercises (id, user_id, log_date, kind, description, intensity, duration_min, calories_burned)`
- `m8_daily_steps (user_id, log_date, steps, step_goal, PK composite)`
- `m8_saved_foods (id, user_id, name, calories, protein_g, carbs_g, fats_g, serving)`
- `m8_ai_scan_quota (user_id, log_date, scan_count, PK composite)` — untuk rate limit harian AI scan.

## 7. Kalkulasi Business Rules

- **BMR (Mifflin-St Jeor):**
  - Pria: `10·kg + 6.25·cm − 5·age + 5`
  - Wanita: `10·kg + 6.25·cm − 5·age − 161`
- **TDEE = BMR × activity factor:** beginner 1.375, active 1.55, atlet 1.725.
- **Target harian:** goal `lose` → TDEE − 500; `maintain` → TDEE; `gain` → TDEE + 500.
- **Makro split default:** protein 30% / carbs 40% / fats 30% dari target kalori. Konversi: 1g protein = 4 kcal, 1g carbs = 4 kcal, 1g fat = 9 kcal.
- **Kalori terbakar (MET formula):** `MET × weight_kg × duration_hours`. MET table: run low 7 / med 9.8 / high 11.5; weight low 3 / med 4.5 / high 6.
- **Health score (0–100):** mulai 100; kurangi berdasarkan seberapa jauh net calorie dari target dan deviasi makro.

## 8. Design System

- **Theme:** dark-first (bg-slate-950), card bg-slate-800, accent aqua/orange (existing ActivityRings palette).
- **Typography:** font-semibold untuk headers, font-medium untuk labels.
- **Mobile-first:** layout ≤ 480px primary; desktop ≥ 768px center 4xl container.
- **Safe-area:** `env(safe-area-inset-bottom)` untuk TabBar bawah.

## 9. Milestones (fase eksekusi)

1. **Foundation** — schema.sql + api.php skeleton + deps install (router, zustand, rhf, zod, vite-plugin-pwa, zxing).
2. **Onboarding** — 6-step wizard + TDEE calc + save profile.
3. **Home** — dashboard wiring + log food dialog (manual) + recently eaten.
4. **Exercise** — log exercise modal + intensity/duration + MET calc.
5. **Progress** — step input + health score + weekly chart.
6. **Food DB + Barcode** — Open Food Facts integration + ZXing.
7. **AI Food Scan** — camera capture + Anthropic Claude vision integration + review UI + quota.
8. **PWA** — manifest + service worker + offline shell + install prompt.
9. **Polish** — animasi transisi, empty states, loading skeletons, testing end-to-end.

## 10. Success Criteria

- Onboarding selesai < 60 detik, profile tersimpan di DB.
- Add meal / exercise < 10 detik, langsung tercermin di ring tanpa refresh.
- AI food scan return prediksi < 10 detik untuk foto rata-rata, dengan field semua pre-filled.
- App installable sebagai PWA di Chrome Android & iOS Safari.
- Offline: shell + last-seen dashboard data tetap bisa dibuka.
- Lighthouse PWA score ≥ 90.

## 11. Open Questions

- AI scan: LLM provider — Anthropic Claude confirmed? Atau mau opsi OpenAI/Gemini?
- Foto makanan: inline base64 di DB cukup untuk MVP? (alternatif: upload ke filesystem `/uploads/modul_8/`)
- Unit: metric (kg, cm) saja — atau perlu imperial juga?

## 12. Referensi code existing

- `config/database.php` — `getDBConnection()` PDO singleton, parser `.env`.
- `core/auth.php` — `requireLogin()`.
- `core/session.php` — `getCurrentUser()`, `$_SESSION['user_id']`.
- `modules/modul_8/index.php` — entrypoint yang inject `window.__USER__`.
- `modules/modul_8/app/package.json` — React 19, Tailwind 4, recharts, shadcn sudah terpasang.
- `modules/modul_8/app/src/screens/Home.tsx` — pattern ActivityRings + TabBar.
