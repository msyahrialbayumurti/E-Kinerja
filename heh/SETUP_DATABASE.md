# Setup Database E-Kinerja

## 📋 Langkah-Langkah Import Database

### **Metode 1: Menggunakan phpMyAdmin (Termudah)**

#### Step 1: Buka phpMyAdmin
1. Pastikan **Laragon running** (Start All)
2. Buka browser: `http://localhost/phpmyadmin`
3. Login dengan:
   - Username: `root`
   - Password: (kosong)

#### Step 2: Buat Database Baru
1. Klik tab **"Databases"** (di atas)
2. Di field **"Create new database"**, ketik: `ekinerja_db`
3. Pilih Collation: `utf8mb4_unicode_ci`
4. Klik **"Create"**

#### Step 3: Import SQL File
1. Setelah database dibuat, klik tab **"Import"**
2. Klik **"Choose File"** dan pilih: `d:\laragon\www\E-Kinerja\ekinerja_db.sql`
3. Scroll ke bawah, klik **"Import"**
4. Tunggu sampai muncul pesan **"Import has been successfully finished"**

#### Step 4: Verifikasi Database
1. Di sidebar kiri, klik `ekinerja_db`
2. Pastikan tabel-tabel ini muncul:
   - ✅ `users` (3 test accounts)
   - ✅ `skp` (2 SKP dummy)
   - ✅ `rencana_aksi` (4 rencana aksi)
   - ✅ `rencana_aksi_item` (8 item)
   - ✅ `eviden_penilaian` (3 file)
   - ✅ `penilaian` (3 penilaian)

---

### **Metode 2: Menggunakan MySQL Command Line (Alternatif)**

Buka Command Prompt & jalankan:

```bash
cd d:\laragon\bin\mysql\mysql-8.0.30-winx64\bin

mysql -u root -p < d:\laragon\www\E-Kinerja\ekinerja_db.sql
```

Tekan Enter (password kosong).

---

## 🧑‍💻 Test Accounts (Setelah Database Import Selesai)

| Username | Password | Role | NIP |
|----------|----------|------|-----|
| `admin` | Diberikan administrator | Administrator | 111111111111111111 |
| `ketua` | Diberikan administrator | Ketua Yayasan | 222222222222222222 |
| `guru` | Diberikan administrator | Guru Pelaksana | 333333333333333333 |

Password awal wajib diganti setelah login pertama. Password default `123456` sudah tidak digunakan.

---

## 📊 Data Dummy yang Sudah Terisi

### **Users (3 Akun)**
- Admin: Untuk mengelola sistem
- Ketua Yayasan: Untuk memberi penilaian
- Guru: Untuk input data & bukti dukung

### **SKP (2 Tahun)**
- 2026: Status **Aktif** (sedang berjalan)
- 2025: Status **Dinilai** (sudah selesai penilaian)

### **Rencana Aksi (4 per SKP)**
- Triwulan I-IV dengan target untuk setiap aspek

### **Penilaian (3 Penilaian)**
- Dengan feedback dari Ketua Yayasan
- Score: 85.5, 88.0, 78.0
- Setiap penilaian punya 3 aspek (Kuantitas, Kualitas, Waktu)

---

## ✅ Setelah Import Selesai

1. Akses aplikasi: `http://localhost/E-Kinerja`
2. Redirect otomatis ke: `http://localhost/E-Kinerja/views/login.php`
3. Login dengan salah satu test account di atas
4. Explore 4 menu:
   - **Buku Panduan** - Dokumentasi
   - **Profil** - Data pribadi & atasan
   - **SKP** - Daftar SKP (2026 & 2025)
   - **Penilaian & Eviden** - Matriks penilaian (3 penilaian dengan 3 aspek)

---

## ⚠️ Troubleshooting

### **Error: "Access denied for user 'root'@'localhost'"**
- Pastikan MySQL running (Start All di Laragon)
- Password field kosong (tidak ada password)

### **Error: "Database 'ekinerja_db' already exists"**
- Database sudah ada dari import sebelumnya
- Bisa diabaikan atau hapus database lama via phpMyAdmin

### **Error: "Table already exists"**
- Gunakan `DROP DATABASE ekinerja_db;` dulu, baru import ulang

### **Halaman Blank atau Error**
- Cek browser console (F12)
- Pastikan database benar-benar terisi (lihat di phpMyAdmin)
- Clear browser cache (Ctrl+Shift+Delete)

---

## 📝 File yang Digunakan

- **Database Schema**: `ekinerja_db.sql` (182 lines)
- **Database Config**: `config/database.php`
- **Security Config**: `config/security.php`

