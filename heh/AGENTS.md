# AGENTS.md - Aturan & Pedoman Development E-Kinerja

## 📋 Tujuan Project

E-Kinerja adalah aplikasi web berbasis PHP-MySQL untuk manajemen penilaian kinerja pegawai/guru sesuai standar BKN dan PermenPANRB No. 6 Tahun 2022. Aplikasi ini memfasilitasi:

- Pembuatan & tracking SKP (Sasaran Kinerja Pegawai) tahunan.
- Matriks Penilaian & Eviden berdensitas tinggi (RHK Pimpinan, RHK Individu, Rencana Aksi, Aspek, dan Target).
- Pengelolaan bukti dukung/eviden dengan validasi file biner dan penamaan dokumen.
- Pengisian capaian realisasi riil kerja oleh atasan/pimpinan penilai.
- Manajemen umpan balik berkelanjutan (_ongoing feedback_) menggunakan simbol visual apresiasi serta catatan evaluasi resmi.

---

## 🎯 User Roles & Permissions

Sistem menerapkan pembagian hak akses ketat (RBAC) pada setiap kolom matriks penilaian:

| Role              | Deskripsi                      | Akses Menu                                                   | Hak Akses Matriks Penilaian & Eviden                                                                                                                                                                                         |
| ----------------- | ------------------------------ | ------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **admin**         | Administrator Kepegawaian / IT | Kelola User, Pengaturan Sistem, Master SKP, Cetak Laporan    | **CRUD Penuh**: Mengisi & mengelola kolom _RHK Pimpinan Diintervensi_, _RHK Individu & Rencana Aksi_, _Aspek & Indikator (Kuantitas, Kualitas, Waktu)_, dan _Target_.                                                        |
| **guru_pegawai**  | Guru / Pegawai Pelaksana       | Profil Pribadi, Lihat Matriks SKP Sendiri, Riwayat Penilaian | **Create & Delete Bukti Dukung**: Mengunggah berkas bukti dukung (PDF/JPG/PNG max 5MB), memberi nama label dokumen, menambahkan link eksternal, dan menghapus file miliknya sendiri. (Kolom lain _Read-Only_).               |
| **ketua_yayasan** | Atasan / Pimpinan Penilai      | Profil, Monitoring Kinerja Tim/Bawahan, Rekap Penilaian      | **CRUD Realisasi & Feedback**: Mengisi & memperbarui narasi pada kolom _Realisasi_ serta mengelola kolom _Feedback_ dengan memilih simbol rating suasana (_Jempol, Bintang, Catatan, Apresiasi_) dan catatan evaluasi resmi. |

---

## 📁 Struktur Folder Project

```
E-Kinerja/
├── config/
│   ├── database.php             # Koneksi PDO singleton, error handling & credentials
│   └── security.php             # Session guard, proteksi CSRF, & verifikasi role (RBAC)
├── layout/
│   ├── header.php               # HTML Head, Tailwind CSS (CDN), topbar info profil & role
│   ├── sidebar.php              # Navigasi responsif (otomatis menyesuaikan role user aktif)
│   └── footer.php               # Script modal dialog, toast notification, closing HTML
├── views/
│   ├── login.php                # Halaman login multi-role
│   ├── profil.php               # Profil pegawai (Tab Data Pribadi & Tab Data Atasan Penilai)
│   ├── skp_list.php             # Daftar SKP tahunan & status periode
│   ├── penilaian_eviden.php     # Matriks Penilaian & Eviden (Tabel utama 8 kolom)
│   └── users_manage.php         # Khusus Admin: Kelola master data akun pegawai & pimpinan
├── api/
│   ├── RhkController.php        # Khusus Admin: API CRUD RHK, Rencana Aksi, Aspek, & Target
│   ├── EvidenController.php     # Khusus Guru: API Upload & Delete Bukti Dukung
│   └── FeedbackController.php   # Khusus Yayasan: API CRUD Realisasi & Feedback berkala
├── uploads/
│   └── eviden/                  # Direktori penyimpanan file eviden (.htaccess protected)
├── index.php                    # Front Controller & View Router
├── logout.php                   # Sesi invalidasi & destroy
├── ekinerja_db.sql              # Database schema DDL & data seeder lengkap
└── README.md                    # Dokumentasi setup project
```

---

## 🔄 Alur Sistem Utama

### 1. Login & Autentikasi

```
User → login.php → Validasi Prepared Statement PDO (username / nip)
                 → Verifikasi hash password via password_verify()
                 → session_regenerate_id(true)
                 → Set $_SESSION (user_id, role, nama_lengkap, nip)
                 → Redirect ke index.php?page=penilaian_eviden
```

### 2. Navigasi Menu & Router (`index.php`)

```
index.php (Router) → Cek isset($_SESSION['user_id']) (Jika tidak, lempar ke login.php)
                  → $_GET['page'] (Whitelist sanitasi)
                  → page=profil            → views/profil.php
                  → page=skp_list          → views/skp_list.php
                  → page=penilaian_eviden  → views/penilaian_eviden.php
                  → page=users_manage      → views/users_manage.php (Hanya Admin)
```

### 3. Profil Pegawai

```
GET index.php?page=profil
        ↓
    profil.php (2 Tab)
        ├─ Tab "Data Pribadi" → Menampilkan biodata pegawai dari database/session
        └─ Tab "Data Atasan"  → Menampilkan profil pimpinan penilai dari relasi users.atasan_id
```

### 4. Daftar SKP

```
GET index.php?page=skp_list
        ↓
    skp_list.php
        ↓
    Query: SELECT * FROM skp WHERE user_id = ? ORDER BY tahun_anggaran DESC
        ↓
    Tampilkan tabel SKP dengan filter tahun dan badge status (Draft / Aktif / Dinilai)
```

### 5. Matriks Penilaian & Eviden (Kolaborasi 3 Role)

```
GET index.php?page=penilaian_eviden
        ↓
    penilaian_eviden.php (Merender 8 kolom matriks)
        │
        ├─► [ADMIN]
        │   Mengisi RHK Pimpinan Diintervensi, RHK Individu, Rencana Aksi,
        │   serta Aspek (Kuantitas, Kualitas, Waktu) dan Target.
        │
        ├─► [GURU / PEGAWAI]
        │   Melihat target kerja dari Admin, lalu klik "+ Tambah Bukti Dukung".
        │   Mengunggah file (PDF/Gambar) dan memberi label nama dokumen.
        │   Dapat menghapus file yang diunggahnya sendiri.
        │
        └─► [KETUA YAYASAN]
            Meninjau target dan memeriksa bukti dukung guru.
            Mengisi / mengedit kolom "Realisasi" capaian hasil kerja riil.
            Mengisi / mengedit kolom "Feedback" (Pilih Simbol: Jempol/Bintang/Catatan/Apresiasi + catatan teks).
```

---

## 🔗 API Endpoints

Semua endpoint API mengembalikan format JSON standar:

```json
{
  "success": true,
  "message": "Pesan status operasi",
  "data": {}
}
```

### 1. API RHK & Target (Akses Khusus: `admin`)

```
POST /api/RhkController.php?action=create_rhk
    Body: {skp_id, rhk_pimpinan, rhk_individu, rencana_aksi, csrf_token}
    → Return: {success: true, message: "RHK berhasil dibuat", id: 10}

POST /api/RhkController.php?action=update_rhk
    Body: {id, rhk_pimpinan, rhk_individu, rencana_aksi, csrf_token}
    → Return: {success: true, message: "RHK berhasil diperbarui"}

POST /api/RhkController.php?action=delete_rhk
    Body: {id, csrf_token}
    → Return: {success: true, message: "RHK beserta aspek dan target berhasil dihapus"}

POST /api/RhkController.php?action=save_indikator
    Body: {rhk_id, aspek, deskripsi_indikator, target, csrf_token}
    → Return: {success: true, message: "Indikator dan target berhasil disimpan"}
```

### 2. API Bukti Dukung (Akses Khusus: `guru_pegawai`)

```
POST /api/EvidenController.php?action=upload
    Form-Data: {indikator_id, nama_label, file_eviden (File binary), csrf_token}
    → Validasi: MIME type via finfo_file (PDF, JPG, PNG max 5MB)
    → Simpan ke /uploads/eviden/ dengan nama acak hash UUID
    → Insert ke tabel eviden_dukung
    → Return: {success: true, message: "Bukti dukung berhasil diunggah", file_path: "xxx.pdf"}

POST /api/EvidenController.php?action=delete
    Body: {id, csrf_token}
    → Validasi: User ID pemilik file harus sesuai dengan ID sesi
    → Unlink/hapus file fisik di /uploads/eviden/
    → DELETE dari tabel eviden_dukung
    → Return: {success: true, message: "Bukti dukung berhasil dihapus"}
```

### 3. API Realisasi & Feedback (Akses Khusus: `ketua_yayasan`)

```
POST /api/FeedbackController.php?action=save_penilaian
    Body: {rhk_id, realisasi, feedback_teks, feedback_simbol, csrf_token}
    Pilihan simbol: 'jempol' | 'bintang' | 'catatan' | 'apresiasi'
    → Insert / Update (ON DUPLICATE KEY UPDATE) ke tabel penilaian_realisasi
    → Return: {success: true, message: "Realisasi dan feedback berhasil disimpan"}

POST /api/FeedbackController.php?action=delete_feedback
    Body: {rhk_id, csrf_token}
    → Reset nilai realisasi dan feedback
    → Return: {success: true, message: "Data penilaian berhasil dihapus"}
```

---

## 📊 Database Schema (Tabel Utama)

| Tabel                 | Deskripsi                                                           | Relasi Utama                                                                            |
| --------------------- | ------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| `users`               | Data akun pengguna, NIP, peran (admin, ketua_yayasan, guru_pegawai) | `atasan_id` mengarah ke `users.id`                                                      |
| `skp`                 | Sasaran Kinerja Pegawai tahunan per user                            | `user_id` $\rightarrow$ `users.id`                                                      |
| `skp_rhk`             | Butir RHK Pimpinan & Individu beserta butir rencana aksi            | `skp_id` $\rightarrow$ `skp.id` (ON DELETE CASCADE)                                     |
| `skp_rhk_indikator`   | Aspek (Kuantitas, Kualitas, Waktu) & besaran Target per RHK         | `rhk_id` $\rightarrow$ `skp_rhk.id` (ON DELETE CASCADE)                                 |
| `eviden_dukung`       | File bukti dukung yang diunggah guru beserta label dokumen          | `indikator_id` $\rightarrow$ `skp_rhk_indikator.id`, `user_id` $\rightarrow$ `users.id` |
| `penilaian_realisasi` | Narasi realisasi, simbol feedback, dan catatan evaluasi pimpinan    | `rhk_id` $\rightarrow$ `skp_rhk.id`, `penilai_id` $\rightarrow$ `users.id`              |

---

## 🎨 UI/UX Standar

- **Layout:** Header + Sidebar navigasi responsif + Main Content fleksibel.
- **Styling:** Tailwind CSS (CDN), palet warna slate modern, data density tinggi khas instansi (_compact view_).
- **Tabel Matriks (8 Kolom):**
  - Font ukuran 11–13px, padding terukur, pembatas border sel tegas (`border-slate-200`).
  - **Badge Aspek**: `KUANTITAS` (Biru), `KUALITAS` (Ungu), `WAKTU` (Oranye).
  - **Box Bukti Dukung**: Container kartu kecil abu-abu lembut (`bg-slate-50`), ikon tautan rantai biru, teks nama file truncate dengan title popup tooltip. Tombol `+ Tambah Bukti Dukung` dan link hapus merah kecil.
  - **Kartu Feedback**: Border berbingkai kuning-emas (`border-amber-300 bg-amber-50/20`), menampilkan simbol reaksi aktif + nama penilai + kutipan teks miring (_italic_).
- **Simbol Visual Feedback Pimpinan**:
  - `👍 Jempol`: Badge kuning/amber (Ekspektasi pimpinan terpenuhi).
  - `⭐ Bintang`: Badge biru terang (Kinerja luar biasa / melampaui target).
  - `🎯 Apresiasi`: Badge hijau/emerald (Penyelesaian terorganisir dan tuntas).
  - `⚠️ Catatan`: Badge oranye/merah (Perlu perhatian khusus / revisi).
- **Modal Dialog:** Desain clean, fokus pada input data, autofocus input pertama, tertutup otomatis dengan tombol Escape.
- **Notifikasi:** Toast melayang non-blocking di sudut layar (sukses/gagal), tanpa memakai `alert()` bawaan browser.

---

## 🔒 Standar Keamanan & Coding

1. **Anti SQL Injection**: Tidak ada penggabungan string raw SQL. Semua query ke MySQL wajib menggunakan PDO Prepared Statements dengan parameter placeholder `?` atau `:named`.
2. **Proteksi Output (XSS)**: Bungkus semua data dinamis dari database/user dengan:
   ```php
   <?= htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8') ?>
   ```
3. **Validasi File Upload Biner**:
   - MIME type diverifikasi lewat `finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmp_name)`.
   - Whitelist file: `application/pdf`, `image/jpeg`, `image/png`.
   - Ukuran maksimal 5 MB.
   - Penamaan file otomatis di-hash (UUID/hex) untuk mencegah _path traversal_ dan tabrakan nama file.
4. **Proteksi Direktori Uploads**: File `/uploads/eviden/.htaccess` wajib mematikan eksekusi script tanpa `php_flag` (dapat menyebabkan HTTP 500 pada Apache/Laragon):
   ```apache
   Options -Indexes

   <FilesMatch "\.(php|php3|php4|php5|php6|php7|phtml|phps|cgi|pl|asp|aspx|shtml|sh)$">
       Require all denied
   </FilesMatch>
   ```
5. **CSRF Protection**: Seluruh form mutasi data (`POST`) wajib mengirimkan token CSRF terenkripsi yang dicocokkan dengan token sesi, termasuk form login.
6. **Login Rate Limit**: Maksimal 5 kegagalan login per kombinasi username+IP dan per IP dalam 15 menit. Pesan throttling memakai `Retry-After` tanpa membocorkan status akun.
7. **Session Invalidation**: Sesi menyimpan fingerprint hash password. Perubahan password membatalkan sesi lain milik akun tersebut pada request berikutnya.
8. **Lock Setup**: `config/.installed` mencegah `setup.php` dijalankan ulang. Untuk instalasi baru dari nol, hapus file lock tersebut secara manual sebelum membuka `setup.php`.
9. **Kredensial Database**: Gunakan environment variable `EKINERJA_DB_HOST`, `EKINERJA_DB_NAME`, `EKINERJA_DB_USER`, dan `EKINERJA_DB_PASS` di production; fallback `root` tanpa password hanya untuk development lokal.

---

## ⚙️ Setup & Deployment

### Development

```bash
1. Clone / letakkan project di root server lokal:
   C:/laragon/www/E-Kinerja  atau  /opt/lampp/htdocs/E-Kinerja
2. Buka phpMyAdmin / MySQL CLI, buat database baru:
   CREATE DATABASE ekinerja_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
3. Import file database:
   ekinerja_db.sql
4. Sesuaikan kredensial di config/database.php jika diperlukan (default: user 'root', password '')
5. Buka browser: http://localhost/E-Kinerja
6. Login memakai akun seed `admin`, `ketua`, dan `guru` dengan kredensial awal yang diberikan administrator. Password awal tidak lagi `123456`; segera ganti setelah login pertama.
7. Untuk instalasi baru dari nol, hapus `config/.installed` sebelum membuka `setup.php`; file ini mencegah setup dijalankan ulang setelah instalasi.
```

### Production

```bash
1. Database: Update username & password acak yang kuat, pastikan charset utf8mb4.
2. Config: Matikan error display (display_errors = 0, log_errors = 1).
3. Security: Pastikan HTTPS aktif, ubah hashing password jika menggunakan akun live.
4. Upload Folder: Berikan permission 0755 pada folder /uploads/eviden/.
5. .htaccess: Pastikan Apache mod_rewrite aktif dan konfigurasi keamanan folder aktif.
```

---

## 🚀 Roadmap Fitur

| Versi    | Status    | Lingkup Fitur                                                                                  |
| -------- | --------- | ---------------------------------------------------------------------------------------------- |
| **v1.0** | Current   | Profil, Daftar SKP, Matriks Penilaian & Eviden Lengkap (Role RBAC: Admin, Guru, Ketua Yayasan) |
| **v1.1** | Terencana | Export cetak SKP format PDF standar BKN (PermenPANRB 6/2022)                                   |
| **v1.2** | Terencana | Notifikasi email / WhatsApp bot saat pimpinan memberikan feedback                              |
| **v2.0** | Terencana | Perhitungan otomatis Kuadran Kinerja Tahunan & Konversi Angka Kredit Integrasi SIASN BKN       |

---

## 📝 Catatan Konfigurasi Sistem

- **Session Timeout:** 30 menit inaktivitas (auto logout di `config/security.php`)
- **Upload Path:** `uploads/eviden/` (dibuat otomatis jika belum ada)
- **Timezone Server:** `Asia/Jakarta` (UTC+7)
- **Database Collation:** `utf8mb4_unicode_ci` everywhere
