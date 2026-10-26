# ARCHITECTURE.md - Arsitektur Teknis E-Kinerja

# 🏗️ Arsitektur Aplikasi
┌───────────────────────────────────────────────────────────────────────────┐
│                          CLIENT LAYER (Browser)                           │
│   Tailwind CSS UI ──► Vanilla JS (ES6 Fetch) / Multipart FormData         │
└─────────────────────────────────────┬─────────────────────────────────────┘
                                      │ HTTP/AJAX (Session Cookie & CSRF Token)
┌─────────────────────────────────────▼─────────────────────────────────────┐
│                       GATEWAY & SECURITY MIDDLEWARE                       │
│  - Web Server (.htaccess: ModRewrite, No-Script Execution di /uploads/)    │
│  - config/security.php: Session Hardening, Auth Guard, RBAC Check         │
└─────────────────────────────────────┬─────────────────────────────────────┘
                                      │
              ┌───────────────────────┴───────────────────────┐
              │                                               │ [Fetch / AJAX API]
              ▼ [Page View Request]                           ▼
┌───────────────────────────┐                   ┌───────────────────────────┐
│    ROUTER (index.php)     │                   │      API CONTROLLERS      │
│  Whitelisted $_GET['page']│                   │  (JSON Response Header)   │
└─────────────┬─────────────┘                   └─────────────┬─────────────┘
              │                                               │
      ┌───────┴───────┐                               ┌───────┴───────┐
      ▼               ▼                               ▼       ▼       ▼
┌──────────┐    ┌──────────┐                     ┌─────────┐┌────────┐┌─────────┐
│ Layouts  │    │  Views   │                     │  Rhk    ││ Eviden ││Feedback │
│ (Header/ │    │ (Matriks │                     │ (Admin) ││ (Guru) ││(Yayasan)│
│ Sidebar) │    │  8 Kolom)│                     └────┬────┘└───┬────┘└────┬────┘
└──────────┘    └──────────┘                          │         │          │
              │                                       └─────────┼──────────┘
              └───────────────────────┬─────────────────────────┘
                                      │
┌─────────────────────────────────────▼─────────────────────────────────────┐
│                    DATABASE ACCESS LAYER (Singleton PDO)                  │
│  - config/database.php: Persistent Connection, UTF-8MB4                   │
│  - Parameterized Prepared Statements (Cegah SQL Injection)                │
└─────────────────────────────────────┬─────────────────────────────────────┘
                                      │
                                   ┌──▼──┐
                                   │MySQL│
                                   └─────┘

---

## 📦 Komponen Utama

### 1. **Router (index.php)**
```php
Function: Menentukan view mana yang diload berdasarkan parameter $_GET['page']

Alur:
  1. session_start() → Set session cookie (HttpOnly, SameSite=Lax)
  2. Redirect ke login.php jika belum login
  3. Include config/database.php & config/security.php
  4. Include layout/header.php + layout/sidebar.php
  5. Switch($_GET['page']) dengan whitelist sanitasi:
     - profil           → views/profil.php
     - skp_list         → views/skp_list.php
     - penilaian_eviden → views/penilaian_eviden.php (Matriks 8 Kolom)
     - users_manage     → views/users_manage.php (Khusus Admin)
  6. Include layout/footer.php

Supported Pages:
  - profil → views/profil.php
  - skp_list → views/skp_list.php
  - penilaian_eviden → views/penilaian_eviden.php
```

### 2. **Database Layer (config/database.php)**
```php
Function: Inisialisasi koneksi PDO Singleton ke MySQL

Features:
  - PDO connection dengan charset UTF-8MB4
  - Error mode: PDO::ERRMODE_EXCEPTION
  - Default fetch mode: PDO::FETCH_ASSOC
  - Prepared statements emulation disabled (PDO::ATTR_EMULATE_PREPARES => false)

Usage:
  require_once 'config/database.php';
  $db = get_db();
  $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user = $stmt->fetch();
```

### 3. **Layout Components**

#### header.php
- DOCTYPE, meta tags, Tailwind CSS CDN
- Topbar info user profil, NIP, badge role aktif, dan tombol toggle mobile sidebar
- Anti-CSRF meta header untuk request AJAX
- Dropdown menu user
- Menu items: Profil, Daftar SKP, Matriks Penilaian & Eviden, dan Kelola User (khusus Admin)
- Selector tahun anggaran aktif

#### sidebar.php
- Navigasi dinamis berbasis role (admin, guru_pegawai, ketua_yayasan)
- Logo & branding
- Tahun anggaran selector

#### footer.php
- Global toast container untuk notifikasi asinkron
- Modal container (CRUD RHK, Upload Eviden, Form Realisasi & Feedback)
- Closing tags HTML & Core JS Helper

### 4. **Views (Dynamic Content)**

#### profil.php
```php
Purpose: Menampilkan profil pegawai & hierarki atasan
Input: $_SESSION['user_id']
Output: 2 Tab (Data Pribadi dari session/users, Data Atasan Penilai dari relasi users.atasan_id)
```

#### skp_list.php
```php
Purpose: Menampilkan daftar periode SKP tahunan pengguna
Input: $_SESSION['user_id']
Output: Tabel daftar SKP, status badge (Draft, Aktif, Dinilai), dan modal buat periode SKP
```

#### penilaian_eviden.php
```php
Purpose: Matriks Penilaian & Eviden utama sesuai PermenPANRB No. 6 Tahun 2022
Input: $_SESSION['user_id'], $_SESSION['role'], $skp_id
Output: Tabel 8 kolom berdensitas tinggi dengan kendali CRUD berbasis role:
  - Kolom 1: NO
  - Kolom 2: RHK PIMPINAN DIINTERVENSI (Admin: CRUD)
  - Kolom 3: RHK INDIVIDU & RENCANA AKSI (Admin: CRUD)
  - Kolom 4: ASPEK & INDIKATOR [Kuantitas, Kualitas, Waktu] (Admin: CRUD)
  - Kolom 5: TARGET (Admin: CRUD)
  - Kolom 6: BUKTI DUKUNG (Guru: Create & Delete)
  - Kolom 7: REALISASI (Ketua Yayasan: CRUD)
  - Kolom 8: FEEDBACK [Simbol Jempol/Bintang/Catatan/Apresiasi + Kutipan] (Ketua Yayasan: CRUD)
```

### 5. **API Controllers (REST-like)**

#### SKPController.php
```php
Header: Content-Type: application/json
Auth: authorize_role(['admin']) + verify_csrf_token()

POST /api/RhkController.php?action=create_rhk      → Tambah RHK Pimpinan & Individu
POST /api/RhkController.php?action=update_rhk      → Update teks RHK & Rencana Aksi
POST /api/RhkController.php?action=delete_rhk      → Hapus RHK (Cascade ke indikator & eviden)
POST /api/RhkController.php?action=save_indikator  → CRUD Aspek (Kuantitas/Kualitas/Waktu) & Target
```



#### EvidenController.php
```php
Header: Content-Type: application/json
Auth: authorize_role(['guru_pegawai']) + verify_csrf_token()

POST /api/EvidenController.php?action=upload
  - Accept: multipart/form-data (file_eviden, indikator_id, nama_label)
  - Validasi: MIME type via finfo_file() (PDF, JPG, PNG max 5MB)
  - Simpan fisik: /uploads/eviden/ dengan nama acak hash UUID
  - Insert DB: eviden_dukung table
  - Return: JSON success + link file

POST /api/EvidenController.php?action=delete
  - Body: {id, csrf_token}
  - Validasi kepemilikan: user_id record == $_SESSION['user_id']
  - Hapus file fisik via unlink() + Hapus record di database
```
# FeedbackController.php (Khusus Ketua Yayasan / Pimpinan)
```PHP
Header: Content-Type: application/json
Auth: authorize_role(['ketua_yayasan']) + verify_csrf_token()

POST /api/FeedbackController.php?action=save_penilaian
  - Body: {rhk_id, realisasi, feedback_teks, feedback_simbol, csrf_token}
  - Preset Simbol: 'jempol' (Apresiasi Standar), 'bintang' (Istimewa), 'catatan' (Perlu Evaluasi), 'apresiasi' (Target Tuntas)
  - Upsert DB: INSERT ... ON DUPLICATE KEY UPDATE di tabel penilaian_realisasi
  - Return: JSON success

POST /api/FeedbackController.php?action=delete_feedback
  - Body: {rhk_id, csrf_token}
  - Reset / kosongkan nilai realisasi dan feedback
```
## 🔐 Security Architecture

### Authentication
```
Login flow:
  1. User input username/NIP + password
  2. Query user via Prepared Statement PDO
  3. password_verify($password, $stored_hash)
  4. session_regenerate_id(true) (Cegah Session Fixation)
  5. Set $_SESSION (user_id, role, nama_lengkap, nip)
  6. Redirect ke index.php?page=penilaian_eviden

Session validation:
  - Cek isset($_SESSION['user_id']) pada index.php dan semua API controller
  - Auto-timeout setelah 30 menit inaktivitas
```

### Authorization
```
Validasi Berlapis Backend (config/security.php):
  - admin: Akses CRUD RHK Pimpinan, RHK Individu, Rencana Aksi, Aspek, dan Target
  - guru_pegawai: Akses Create & Delete Bukti Dukung (hanya file miliknya sendiri)
  - ketua_yayasan: Akses CRUD Realisasi & Feedback (simbol & catatan evaluasi)

Implementasi:
  - Setiap API controller memanggil authorize_role(['allowed_role'])
  - Melempar HTTP 403 Forbidden bila role tidak sesuai
```

### Data Protection
```
- PDO Prepared Statements: Parameterized queries untuk mencegah SQL Injection
- htmlspecialchars(): Sanitasi output konteks HTML untuk mencegah Stored XSS
- Validasi Upload Biner: Sniffing MIME biner via finfo_file(), bukan percaya ekstensi browser
- Storage Hardening: Folder /uploads/eviden/ dilindungi file .htaccess (Options -Indexes + FilesMatch deny script, tanpa php_flag)
- Random Filename: Penamaan file dengan bin2hex(random_bytes(16)) mencegah path traversal
- CSRF Token: Token unik berbasis sesi divalidasi pada setiap mutasi POST, termasuk login
- Login Throttle: Maksimal 5 kegagalan per username+IP dan per IP dalam 15 menit
- Session Fingerprint: Hash password disimpan di sesi; perubahan password membatalkan sesi lain
- Security Headers: CSP dasar, X-Frame-Options DENY, nosniff, Referrer-Policy, Permissions-Policy
```

---

## 💾 Database Architecture

### Table Relationships (ER Diagram)

```
users (PK: id)
  │
  ├── 1:M skp (as user_id)
  │     │
  │     └── 1:M skp_rhk
  │           │
  │           ├── 1:M skp_rhk_indikator
  │           │     │
  │           │     └── 1:M eviden_dukung (FK: user_id -> users)
  │           │
  │           └── 1:1 penilaian_realisasi (FK: penilai_id -> users)
  │
  └── 1:M users (Self-referencing: atasan_id -> users.id)
```

### Key Tables

| Tabel | PK | Foreign Keys | Role Pengelola | Purpose |
|---|---|---|---|---|
| `users` | id | atasan_id | Admin | Data akun master, kredensial login (hash bcrypt), NIP, jabatan, unit kerja, & role (`admin`, `guru_pegawai`, `ketua_yayasan`) |
| `skp` | id | user_id | Admin / Sistem | Data induk Sasaran Kinerja Pegawai tahunan milik user pegawai |
| `skp_rhk` | id | skp_id | Admin (CRUD) | Menyimpan data RHK Pimpinan Diintervensi, RHK Individu, dan butir-butir Rencana Aksi |
| `skp_rhk_indikator` | id | rhk_id | Admin (CRUD) | Detail multi-aspek (`kuantitas`, `kualitas`, `waktu`), deskripsi indikator, dan besaran Target |
| `eviden_dukung` | id | indikator_id, user_id | Guru (Create & Delete) | File fisik bukti dukung di `/uploads/eviden/`, nama label dokumen, nama file asli, dan tautan eksternal |
| `penilaian_realisasi` | id | rhk_id, penilai_id | Ketua Yayasan (CRUD) | Narasi hasil capaian kerja riil (Realisasi) serta penetapan simbol reaksi (`jempol`, `bintang`, `catatan`, `apresiasi`) dan catatan evaluasi pimpinan (Feedback) |

---

## 🔄 Request/Response Flow

### Typical Request Flow (GET profil)

```
1. Browser: GET /index.php?page=penilaian_eviden
2. Server (index.php):
   - Cek session auth → Valid
   - Load layout/header.php
   - Load layout/sidebar.php (Render menu sesuai role sesi)
   - Load views/penilaian_eviden.php:
       * Query Eager Loading: skp_rhk JOIN skp_rhk_indikator JOIN eviden_dukung JOIN penilaian_realisasi
       * Render baris tabel 8 kolom
       * Render tombol aksi spesifik:
           - Admin          → Tombol CRUD RHK & Target
           - Guru           → Tombol "+ Tambah Bukti Dukung" & Tombol Hapus file sendiri
           - Ketua Yayasan  → Tombol "Edit Realisasi" & "Beri Feedback"
   - Load layout/footer.php (Inisialisasi modal & event handler JS)
3. Browser: Render antarmuka matriks
```

### API Flow: Admin Menyimpan RHK & Target

```
1. Browser (Admin): Submit form modal RHK via fetch()
   POST /api/RhkController.php?action=create_rhk
2. Server:
   - authorize_role(['admin']) → Valid
   - verify_csrf_token() → Valid
   - INSERT INTO skp_rhk & skp_rhk_indikator (Prepared Statement)
   - Return JSON: {"success": true, "message": "RHK berhasil dibuat"}
3. Browser: Tutup modal, tampilkan toast sukses, perbarui baris tabel
```

### API Flow: Guru Mengunggah Bukti Dukung

```
1. Browser (Guru): Pilih file PDF/Gambar + input nama label
   POST /api/EvidenController.php?action=upload (FormData)
2. Server:
   - authorize_role(['guru_pegawai']) → Valid
   - Cek finfo_file() == application/pdf | image/jpeg | image/png
   - Cek ukuran file <= 5MB
   - Pindahkan file ke /uploads/eviden/{random_hash}.pdf
   - INSERT INTO eviden_dukung (indikator_id, user_id, nama_label, file_path)
   - Return JSON: {"success": true, "message": "Bukti dukung berhasil diunggah"}
3. Browser: Tampilkan kartu dokumen di kolom BUKTI DUKUNG lengkap dengan link & tombol hapus
```
### API Flow: Ketua Yayasan Mengisi Realisasi & Feedback

```
1. Browser (Ketua Yayasan): Pilih Simbol (Jempol/Bintang/Catatan/Apresiasi) + input realisasi & catatan
   POST /api/FeedbackController.php?action=save_penilaian
2. Server:
   - authorize_role(['ketua_yayasan']) → Valid
   - verify_csrf_token() → Valid
   - INSERT INTO penilaian_realisasi (...) ON DUPLICATE KEY UPDATE ...
   - Return JSON: {"success": true, "message": "Realisasi & feedback berhasil disimpan"}
3. Browser: Render narasi realisasi dan kartu feedback berbingkai emas dengan simbol yang dipilih
```
---

## 📱 Data Flow Diagram

```
┌────────────────────────────────────────────────────────┐
│                        Browser                         │
└──────────────┬──────────────────────────▲──────────────┘
               │                          │
   HTTP GET/POST (Page)            JSON Response (AJAX)
               │                          │
┌──────────────▼──────────────────────────┴──────────────┐
│                   index.php / Router                   │
└──────┬──────────────────────────────────▲──────────────┘
       │                                  │
       ├─► views/profil.php ──────────────┤
       ├─► views/skp_list.php ────────────┤
       ├─► views/penilaian_eviden.php ────┤
       │                                  │
       └─► api/RhkController.php ─────────┤ (Akses Admin)
       └─► api/EvidenController.php ──────┤ (Akses Guru)
       └─► api/FeedbackController.php ────┘ (Akses Ketua Yayasan)
               │
               ▼ (PDO Parameterized)
       ┌───────────────┐
       │ MySQL Database│
       └───────────────┘
```

---

## 🛠️ Configuration & Environment

### config/database.php
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ekinerja_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
```

### config/security.php
```php
// Hardening sesi
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_only_cookies', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timeout sesi 30 menit
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

function authorize_role(array $allowed_roles): void {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles, true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
        exit;
    }
}
```
### config/security.php
```
display_errors = Off
log_errors = On
error_log = /logs/php_errors.log
upload_max_filesize = 10M
post_max_size = 12M
date.timezone = "Asia/Jakarta"
```

---

## 📊 Performance Considerations

1. **Database Indexing**
- Indeks pencarian riwayat SKP per user & tahun
CREATE INDEX idx_skp_user_tahun ON skp(user_id, tahun_anggaran);
- Indeks cascading indikator per RHK
CREATE INDEX idx_indikator_rhk ON skp_rhk_indikator(rhk_id, aspek);
- Indeks penarikan bukti dukung per user & indikator
CREATE INDEX idx_eviden_lookup ON eviden_dukung(indikator_id, user_id);

2. **Query Optimization**
- Hindari mengeksekusi query database di dalam loop rendering HTML.
- Ambil data matriks dalam satu kali penarikan terstruktur menggunakan LEFT JOIN gabungan, lalu susun ke dalam nested associative array sebelum dikirim ke view.

3. **Caching** (future)
   - Session data cached
   - Consider Redis untuk frequently accessed data

4. **File Handling**
   - Random filename prevent collision
   - Check file size before processing
   - Implement cleanup untuk old uploads

5. **Storage & Cleanup Management**
- Hapus berkas fisik di disk via unlink() saat record bukti dukung dihapus.
- Berikan izin direktori chmod 0755 pada folder /uploads/eviden/.
- Simpan nama file acak untuk mencegah konflik nama berkas antar-pegawai.

