<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white"/>
  <img src="https://img.shields.io/badge/TailwindCSS-CDN-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white"/>
  <img src="https://img.shields.io/badge/Chart.js-4.x-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white"/>
  <img src="https://img.shields.io/badge/XAMPP-Apache-FB7A24?style=for-the-badge&logo=xampp&logoColor=white"/>
</p>

# 🏥 MedWeb — Medical Web Application

**MedWeb** adalah platform web modular untuk aplikasi kesehatan yang dibangun dengan arsitektur PHP native dan sistem autentikasi SSO (Single Sign-On) terpusat. Proyek ini dirancang sebagai kerangka kerja multi-modul di mana setiap modul bersifat independen namun berbagi autentikasi, layout, dan komponen UI yang sama.


---

## 📑 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Tech Stack](#-tech-stack)
- [Struktur Proyek](#-struktur-proyek)
- [Instalasi & Setup](#-instalasi--setup)
- [Modul 9 — SIMRS-TB](#-modul-9--simrs-tb)

- [Database Schema](#-database-schema)
- [Component Kit](#-component-kit)
- [Kontributor](#-kontributor)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Utama

- 🔐 **Autentikasi SSO** — Sistem login/register terpusat yang berlaku di semua modul
- 🧩 **Arsitektur Modular** — 14 modul independen (modul_0 s/d modul_13) dengan kemampuan database sendiri
- 🎨 **Component Kit** — Library komponen UI reusable (Button, Card, Table, Input, Modal, Badge, dll.)
- 📱 **Responsive Design** — Tampilan optimal di desktop, tablet, dan mobile
- 🏥 **SIMRS-TB** (Modul 9) — Sistem Informasi Manajemen Rumah Sakit Tuberkulosis dengan AI Screening

---

## 🛠 Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Backend** | PHP 8.x (Native, tanpa framework) |
| **Database** | MySQL 8.0 / MariaDB 10.x |
| **Frontend** | TailwindCSS (CDN), Vanilla JavaScript |
| **Charting** | Chart.js 4.x (CDN) |
| **Font** | Google Fonts — Inter |
| **Server** | Apache (XAMPP) |

---

## 📁 Struktur Proyek

```
medical-web/
├── 📄 index.php                  # Module Hub (halaman utama)
├── 📄 README.md
│
├── 🔧 config/
│   ├── database.php              # Konfigurasi PDO & koneksi database
│   ├── schema.sql                # Schema tabel users (SSO)
│   └── schema-modul9.sql         # Schema 11 tabel SIMRS-TB
│
├── 🔐 auth/
│   ├── login.php                 # Halaman login
│   ├── register.php              # Halaman registrasi
│   ├── process_login.php         # Proses autentikasi
│   ├── process_register.php      # Proses registrasi
│   └── logout.php                # Logout & destroy session
│
├── ⚙️ core/
│   ├── auth.php                  # Helper autentikasi (requireLogin, dll)
│   ├── session.php               # Manajemen session aman
│   └── validator.php             # Validasi input
│
├── 🎨 components/
│   └── components.php            # Library komponen UI (10+ komponen)
│
├── 🖼 layout/
│   ├── header.php                # HTML head, Tailwind config, fonts
│   ├── navbar.php                # Top navigation bar responsive
│   └── footer.php                # Footer & script includes
│
├── 📦 assets/
│   └── js/
│       ├── components.js         # JS untuk modal, dropdown, sidebar
│       └── validation.js         # Client-side form validation
│
├── 📚 modules/
│   ├── modul_0/                  # Component Kit Examples
│   ├── modul_1/ ~ modul_8/      # Modul lain (siap dikembangkan)
│   ├── modul_9/                  # ⭐ SIMRS-TB (Tuberkulosis)
│   │   ├── _sidebar.php          # Shared dark sidebar navigation
│   │   ├── index.php             # Dashboard utama
│   │   ├── screening.php         # Skrining AI Batuk
│   │   ├── rekam-medis.php       # Rekam Medis Digital
│   │   ├── farmasi.php           # Farmasi & PMO
│   │   ├── jadwal.php            # Jadwal Kontrol
│   │   ├── monitoring.php        # Monitoring Kepatuhan
│   │   └── analitik.php          # Analitik & SITB
│   └── modul_10/ ~ modul_13/    # Modul lain (siap dikembangkan)
│
└── 📸 docs/                      # Dokumentasi proyek
```

---

## 🚀 Instalasi & Setup

### Prasyarat

- [XAMPP](https://www.apachefriends.org/) (PHP 8.x + MySQL + Apache)
- Web browser modern (Chrome, Firefox, Edge)

### Langkah Instalasi

**1. Clone Repository**

```bash
git clone https://github.com/agnesjuliana/medical-web.git
```

**2. Pindahkan ke XAMPP**

```bash
# Pindahkan folder ke htdocs
mv medical-web C:/xampp/htdocs/
```

**3. Buat Database**

Buka **phpMyAdmin** (`http://localhost/phpmyadmin`) → jalankan SQL berikut:

```sql
-- Buat database & tabel users
SOURCE C:/xampp/htdocs/medical-web/config/schema.sql;

-- Buat tabel SIMRS-TB (Modul 9)
SOURCE C:/xampp/htdocs/medical-web/config/schema-modul9.sql;
```

Atau import file secara manual melalui tab **Import** di phpMyAdmin.

**4. Konfigurasi Database**

Edit `config/database.php` jika perlu:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'backbone_medweb');
define('DB_USER', 'root');       // Sesuaikan
define('DB_PASS', '');           // Sesuaikan
```

**5. Jalankan**

- Start **Apache** dan **MySQL** di XAMPP Control Panel
- Buka browser → `http://localhost/medical-web`
- Register akun baru → Login → Pilih modul

---

## 🫁 Modul 9 — SIMRS-TB

**SIMRS-TB** (Sistem Informasi Manajemen Rumah Sakit Tuberkulosis) adalah modul utama yang mengotomasi seluruh *user journey* pengobatan pasien TB.

### Ideasi & Konsep

> Sebuah platform web terintegrasi yang diperkuat dengan teknologi **Deep Learning** untuk menganalisis akustik suara batuk sebagai metode skrining awal yang **instan dan non-invasif**. Sistem ini mengotomasi seluruh alur pengobatan pasien mulai dari deteksi dini, rekam medis digital, manajemen farmasi, hingga sinkronisasi dengan **SITB Kementerian Kesehatan**.

### 7 Halaman Utama

| # | Halaman | Deskripsi |
|---|---------|-----------|
| 1 | **🏠 Dashboard** | Statistik real-time, grafik tren kasus, alert pasien risiko, jadwal hari ini |
| 2 | **🎙️ Skrining AI Batuk** | Rekam/upload suara batuk → analisis deep learning → confidence score & rujukan |
| 3 | **📋 Rekam Medis** | Data pasien, progress pengobatan, timeline hasil lab (BTA, GeneXpert, Rontgen) |
| 4 | **💊 Farmasi & PMO** | Stok obat TB, distribusi resep, log Pengawas Menelan Obat |
| 5 | **📅 Jadwal Kontrol** | Kalender interaktif, alert kontrol, penjadwalan |
| 6 | **✅ Monitoring Kepatuhan** | Heatmap 30 hari, circular progress, klasifikasi risiko drop-out |
| 7 | **📊 Analitik & SITB** | Multi-chart dashboard, export laporan, simulasi sinkronisasi SITB Kemenkes |

### Fitur Interaktif

- 🎤 **Audio Recording** — Tombol rekam suara batuk dengan waveform visualization
- 🧠 **AI Analysis Simulation** — Animated progress bar dengan step-by-step (MFCC, CNN inference)
- 📊 **6+ Chart.js Charts** — Line, bar, doughnut, pie charts dengan data dinamis
- 🟩🟥 **Compliance Heatmap** — Grid 30 hari per pasien (hijau = patuh, merah = tidak patuh)
- 🔄 **SITB Sync Animation** — Simulasi sinkronisasi ke server Kementerian Kesehatan
- 📱 **Responsive Sidebar** — Dark sidebar toggle di mobile dengan overlay
- 🗂 **Tab Navigation** — Switch antar panel (Stok/Distribusi/PMO) tanpa reload

---

## 🗄 Database Schema

### Backbone (SSO)

| Tabel | Keterangan |
|-------|------------|
| `users` | Akun pengguna (nama, email, password bcrypt) |

### SIMRS-TB (Modul 9) — 11 Tabel

| Tabel | Keterangan |
|-------|------------|
| `tb_patients` | Data pasien TB (demografi, kategori, fase pengobatan, status) |
| `tb_screenings` | Riwayat skrining suara batuk AI + confidence score |
| `tb_medical_records` | Rekam medis digital (keluhan, pemeriksaan, diagnosis) |
| `tb_lab_results` | Hasil laboratorium (BTA, GeneXpert, Rontgen, Kultur) |
| `tb_drug_inventory` | Inventaris obat TB (stok, batas minimum, kadaluarsa) |
| `tb_prescriptions` | Resep & distribusi obat ke pasien |
| `tb_pmo_logs` | Catatan Pengawas Menelan Obat (PMO) |
| `tb_appointments` | Jadwal kontrol & kunjungan |
| `tb_compliance_logs` | Log kepatuhan minum obat harian |
| `tb_notifications` | Alarm & notifikasi terpusat |
| `tb_sitb_sync_logs` | Log sinkronisasi ke SITB Kemenkes |

### ER Diagram (Simplified)

```
users ─────────────┐
                    │
tb_patients ───────┤──── tb_screenings
    │               │
    ├── tb_medical_records ──── tb_lab_results
    │
    ├── tb_prescriptions ──── tb_drug_inventory
    │
    ├── tb_pmo_logs
    │
    ├── tb_appointments
    │
    ├── tb_compliance_logs
    │
    └── tb_notifications
                    │
         tb_sitb_sync_logs
```

---

## 🎨 Component Kit

MedWeb menyediakan library komponen PHP reusable di `components/components.php`:

```php
// Button
<?= component_button('Simpan', ['variant' => 'primary', 'icon' => '...']) ?>

// Card
<?= component_card(['title' => 'Judul', 'content' => '...']) ?>

// Input
<?= component_input('email', ['label' => 'Email', 'type' => 'email', 'required' => true]) ?>

// Table
<?= component_table(['Nama', 'Email'], [['John', 'john@mail.com']]) ?>

// Badge
<?= component_badge('Active', 'success') ?>

// Alert
<?= component_alert('Berhasil!', 'success', ['dismissible' => true]) ?>

// Modal
<?= component_modal('myModal', ['title' => 'Dialog', 'content' => '...']) ?>

// Stat Card
<?= component_stat('Total Users', '1,234', ['trend' => '+12%']) ?>
```

**Variant tersedia:** `primary` · `secondary` · `outline` · `ghost` · `destructive`

**Badge colors:** `default` · `primary` · `success` · `warning` · `error` · `info`

---

## 👨‍💻 Kontributor

<table>
  <tr>
    <td align="center">
      <strong>Agnes Juliana</strong><br/>
      <sub>Developer</sub>
    </td>
  </tr>
</table>

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan akademis sebagai tugas mata kuliah.

---

<p align="center">
  <sub>Built with ❤️ using PHP • TailwindCSS • MySQL • Chart.js</sub>
</p>
