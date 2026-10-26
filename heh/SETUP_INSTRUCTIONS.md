# 🚀 E-Kinerja Setup Instructions

## MASALAH YANG SUDAH DIPERBAIKI:

### ✅ Database Schema
- Database tables sudah di-update dengan benar:
  - `eviden_dukung` - untuk upload file oleh guru
  - `penilaian_realisasi` - untuk realisasi dan feedback dari ketua yayasan
- File: `ekinerja_db.sql`

### ✅ Backend API
- `api/RhkController.php`, `api/EvidenController.php`, `api/FeedbackController.php` - CRUD operations lengkap
- Semua role-based access control sudah benar
- File upload validation sudah implemented

### ✅ Frontend
- Role-based UI - buttons hanya muncul untuk role yang tepat
- Modal dialogs positioning - bottom-sheet style
- AJAX handlers dengan CSRF token protection

---

## 📋 LANGKAH SETUP (PENTING!):

### 1️⃣ **Import Database**
Akses: `http://localhost/E-Kinerja/setup.php`

Click tombol: **🚀 Setup Database**

✅ Database akan di-import otomatis

> Jika `setup.php` menampilkan 403 setelah instalasi, jangan hapus lock. Untuk instalasi baru dari nol, hapus `config/.installed` terlebih dahulu.

### 2️⃣ **Login & Test**
Akses: `http://localhost/E-Kinerja`

Akun seed: `admin`, `guru`, dan `ketua`. Password awal diberikan terpisah oleh administrator dan wajib diganti setelah login pertama. Password default `123456` sudah tidak digunakan.

### 3️⃣ **Test CRUD Operations**

#### **Admin Role (Login sebagai: admin)**
- ✏️ Edit RHK Pimpinan - Click tombol ✏️
- 🗑️ Delete RHK Pimpinan - Click tombol 🗑️
- ✏️ Edit RHK Individu - Click tombol ✏️
- ✏️ Edit Aspek (KUANTITAS, KUALITAS, WAKTU) - Click ✏️
- 🗑️ Delete Aspek - Click 🗑️
- ✏️ Edit Target - Click ✏️

**Expected:** Modal dialog muncul dari bawah, form terisi, bisa edit & simpan ke database

#### **Guru Role (Login sebagai: guru)**
- 📤 Upload Bukti Dukung - Click tombol "📤 Tambah Bukti Dukung"
- 🗑️ Delete Bukti - Click 🗑️ di setiap bukti

**Expected:** File di-upload ke `/uploads/eviden/` dan record di-simpan ke DB

#### **Ketua Yayasan Role (Login sebagai: ketua)**
- ✏️ Edit Realisasi - Klik di kolom Realisasi, modal muncul
- ➕ Tambah Feedback - Click tombol "➕ Tambah Feedback"
- ✏️ Edit Feedback - Click ✏️ Edit di feedback card
- 🗑️ Delete Feedback - Click 🗑️ di feedback

**Expected:** Data tersimpan ke DB, tampilan update otomatis

---

## ✅ VERIFIKASI BERHASIL:

1. **Database import OK?** 
   - Setup page menunjukkan "✅ Database siap digunakan"

2. **CRUD operations working?**
   - Saat click tombol, modal muncul dari bawah
   - Form terisi dengan data dari DB
   - Setelah submit, toast notification muncul (hijau = success, merah = error)
   - Halaman reload, data berubah di tabel

3. **Data masuk database?**
   - Akses phpMyAdmin
   - Check tables: `eviden_dukung`, `penilaian_realisasi`
   - Lihat records yang baru di-insert

---

## 🆘 TROUBLESHOOTING:

### Jika CRUD tidak bekerja:

1. **Check browser console** (F12 → Console)
   - Lihat error message di network tab

2. **Check API response**
   - Network tab → controller API terkait → Response
   - Lihat error dari API

3. **Check database connection**
   - Pastikan `ekinerja_db` database sudah di-import
   - Cek username/password di `config/database.php` atau environment variable `EKINERJA_DB_*`

4. **Check folder permissions**
   - `/uploads/eviden/` harus writable (chmod 755)

---

## 📝 NOTES:

- Database sudah punya 3 test users (admin, guru, ketua)
- Dummy data sudah di-insert untuk testing
- All CRUD operations hit controller API sesuai peran (`RhkController`, `EvidenController`, `FeedbackController`)
- CSRF token protection aktif di semua forms
- File upload validation active (PDF/JPG/PNG max 5MB)

---

## ✨ PROJECT STATUS: READY FOR PRODUCTION

Semua komponen sudah implemented dan siap digunakan!

**Enjoy E-Kinerja! 🎉**
