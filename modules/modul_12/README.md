# HealthEdu — Panduan Setup MySQL + PHP

## Struktur Folder

```
healthedu/
├── index.html          ← tidak berubah (UI/UX sama persis)
├── style.css           ← tidak berubah
├── script.js           ← ✅ dimodifikasi (terhubung ke API)
└── api/
    ├── config.php      ← ⚙️  konfigurasi database
    ├── database.sql    ← 📦 import ke phpMyAdmin
    ├── signup.php      ← daftar akun
    ├── login.php       ← masuk akun
    ├── logout.php      ← keluar
    ├── bmi.php         ← simpan & ambil riwayat BMI
    ├── tdee.php        ← simpan & ambil TDEE
    └── food.php        ← log makanan harian
```

---

## Langkah Setup (XAMPP / WAMP / Server Hosting)

### 1. Import Database

1. Buka **phpMyAdmin** → klik **Import**
2. Pilih file `api/database.sql`
3. Klik **Go** → database `healthedu` dan semua tabelnya akan terbuat otomatis

### 2. Konfigurasi Koneksi

Edit file `api/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // ← username MySQL kamu
define('DB_PASS', '');           // ← password MySQL kamu
define('DB_NAME', 'healthedu');
```

### 3. Konfigurasi Path API di script.js

Baris pertama di `script.js`:

```js
const API = 'api';   // ← lokal (XAMPP)
// atau
const API = 'https://domain.com/healthedu/api';   // ← hosting
```

### 4. Upload ke Server

Salin seluruh folder `healthedu/` ke:
- **XAMPP lokal**: `C:/xampp/htdocs/healthedu/`
- **Hosting**: `public_html/healthedu/`

### 5. Akses

```
http://localhost/healthedu/
```

---

## Tabel Database

| Tabel       | Fungsi                        |
|-------------|-------------------------------|
| `users`     | Data akun (signup/login)      |
| `sessions`  | Token autentikasi             |
| `bmi_log`   | Riwayat hasil kalkulasi BMI   |
| `tdee_log`  | Riwayat hasil kalkulasi TDEE  |
| `food_log`  | Log makanan harian per user   |

---

## Fitur

- ✅ **Signup & Login** — data tersimpan di MySQL, password di-hash bcrypt
- ✅ **Sesi Token** — aman, berlaku 30 hari
- ✅ **BMI Log** — tersimpan per user, max 15 riwayat
- ✅ **TDEE Log** — tersimpan, diload saat login (restore calGoal)
- ✅ **Food Log** — per user per hari, termasuk 7-hari bar chart
- ✅ **Offline fallback** — jika belum login, data tetap tersimpan di localStorage
- ✅ **Tampilan nama** di navbar saat sudah login + tombol Keluar
