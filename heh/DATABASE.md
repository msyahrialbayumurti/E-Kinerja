# DATABASE.md - Skema & Relasi Database E-Kinerja

## 📊 Entity Relationship Diagram (ERD)

```
┌────────────────┐
│     users      │
├────────────────┤
│ id (PK)        │
│ nip            │
│ username       │
│ password       │
│ nama           │
│ pangkat_golongan
│ jabatan        │
│ role           │ (enum: admin, ketua_yayasan, guru_pegawai)
│ created_at     │
└────────────────┘
        │
        │ 1:M
        ├─────────────────────┬─────────────────────┬──────────────┐
        │                     │                     │              │
        ▼                     ▼                     ▼              ▼
┌──────────────┐      ┌──────────────┐      ┌──────────────┐  ┌──────────────┐
│     skp      │      │  tim_kerja   │      │ angka_kredit │  │    users     │
│ (as user_id) │      │(as ketua_id) │      │              │  │(as penilai)  │
└──────────────┘      └──────────────┘      └──────────────┘  └──────────────┘
        │
        │ 1:M
        ▼
┌──────────────────────┐
│   rencana_aksi       │
├──────────────────────┤
│ id (PK)              │
│ skp_id (FK)          │
│ user_id (FK)         │
│ triwulan             │
│ rhk_indikator        │
│ target               │
└──────────────────────┘
        │
        │ 1:M
        ▼
┌──────────────────────┐
│ rencana_aksi_item    │
├──────────────────────┤
│ id (PK)              │
│ rencana_aksi_id (FK) │
│ deskripsi            │
│ nomor_urut           │
└──────────────────────┘
        │
        │ 1:M
        ▼
┌──────────────────────┐
│  eviden_penilaian    │
├──────────────────────┤
│ id (PK)              │
│ rencana_aksi_item_id │
│ file_path            │
│ original_name        │
│ uploaded_at          │
└──────────────────────┘
```

---

## 📋 Definisi Tabel

### 1. **users** - Data Pengguna Sistem

```sql
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(20) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    pangkat_golongan VARCHAR(50),
    jabatan VARCHAR(100),
    role ENUM('admin', 'ketua_yayasan', 'guru_pegawai') DEFAULT 'guru_pegawai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | INT AUTO_INCREMENT PRIMARY KEY | Unique user identifier |
| nip | VARCHAR(20) UNIQUE | Nomor Induk Pegawai (BKN standard) |
| username | VARCHAR(50) UNIQUE | Username untuk login |
| password | VARCHAR(255) | Hash bcrypt (password_hash) |
| nama | VARCHAR(100) | Nama lengkap pegawai |
| pangkat_golongan | VARCHAR(50) | Contoh: "Penata Tingkat I - III/d" |
| jabatan | VARCHAR(100) | Contoh: "Pranata Komputer Ahli Muda" |
| role | ENUM | guru_pegawai (default), ketua_yayasan, admin |
| created_at | TIMESTAMP | Waktu pembuatan user |

**Test Accounts:**
```
admin     (role: admin)        - password diberikan administrator
ketua     (role: ketua_yayasan) - password diberikan administrator
guru      (role: guru_pegawai)  - password diberikan administrator
```

Password awal wajib diganti setelah login pertama. Password default `123456` sudah tidak digunakan.

---

### 2. **skp** - Sasaran Kinerja Pegawai

```sql
CREATE TABLE IF NOT EXISTS skp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tahun_anggaran YEAR NOT NULL,
    periode_mulai DATE NOT NULL,
    periode_selesai DATE NOT NULL,
    status ENUM('draft', 'persetujuan', 'aktif', 'selesai', 'dinilai') DEFAULT 'draft',
    pendekatan VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_tahun (user_id, tahun_anggaran)
);
```

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | INT AUTO_INCREMENT PRIMARY KEY | Unique SKP identifier |
| user_id | INT | Foreign key ke users.id |
| tahun_anggaran | YEAR | Tahun untuk SKP ini (contoh: 2026) |
| periode_mulai | DATE | Tanggal mulai periode (contoh: 2026-01-01) |
| periode_selesai | DATE | Tanggal selesai periode (contoh: 2026-12-31) |
| status | ENUM | draft → persetujuan → aktif → selesai → dinilai |
| pendekatan | VARCHAR(100) | "Kuantitatif" atau "Kualitatif" |
| created_at | TIMESTAMP | Waktu pembuatan SKP |
| unique_key | - | 1 user hanya boleh 1 SKP per tahun |

**Status Flow:**
```
draft → persetujuan → aktif → selesai → dinilai
```

---

### 3. **rencana_aksi** - Rencana Aksi Per Triwulan

```sql
CREATE TABLE IF NOT EXISTS rencana_aksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skp_id INT NOT NULL,
    user_id INT NOT NULL,
    triwulan TINYINT NOT NULL,
    rhk_indikator TEXT NOT NULL,
    target VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skp_id) REFERENCES skp(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | INT AUTO_INCREMENT PRIMARY KEY | Unique identifier |
| skp_id | INT | FK ke skp.id |
| user_id | INT | FK ke users.id (denormalisasi untuk performa) |
| triwulan | TINYINT | 1, 2, 3, atau 4 |
| rhk_indikator | TEXT | Rencana Hasil Kerja & Indikator |
| target | VARCHAR(100) | Target kuantitatif (contoh: "2 Modul", "99.9%") |
| created_at | TIMESTAMP | Waktu pembuatan |

**Contoh Data:**
```
triwulan=1, rhk_indikator="Tersedianya sistem informasi manajemen kinerja"
target="2 Modul"
```

---

### 4. **rencana_aksi_item** - Item Detail Rencana Aksi

```sql
CREATE TABLE IF NOT EXISTS rencana_aksi_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rencana_aksi_id INT NOT NULL,
    deskripsi TEXT NOT NULL,
    nomor_urut INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rencana_aksi_id) REFERENCES rencana_aksi(id) ON DELETE CASCADE
);
```

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | INT AUTO_INCREMENT PRIMARY KEY | Unique identifier |
| rencana_aksi_id | INT | FK ke rencana_aksi.id |
| deskripsi | TEXT | Detail item rencana aksi |
| nomor_urut | INT | 1.1, 1.2, 2.1, dll |
| created_at | TIMESTAMP | Waktu pembuatan |

**Contoh Data:**
```
nomor_urut=1.1, deskripsi="Menyusun dokumen requirement dan mock-up modul Profil"
nomor_urut=1.2, deskripsi="Mengembangkan backend controller untuk integrasi database SIASN"
```

---

### 5. **eviden_penilaian** - Bukti Dukung (File Upload)

```sql
CREATE TABLE IF NOT EXISTS eviden_penilaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rencana_aksi_item_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rencana_aksi_item_id) REFERENCES rencana_aksi_item(id) ON DELETE CASCADE
);
```

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | INT AUTO_INCREMENT PRIMARY KEY | Unique identifier |
| rencana_aksi_item_id | INT | FK ke rencana_aksi_item.id |
| file_path | VARCHAR(255) | Path file (random filename untuk security) |
| original_name | VARCHAR(255) | Nama original file dari user |
| uploaded_at | TIMESTAMP | Waktu upload |

**Contoh Data:**
```
file_path="a1b2c3d4e5f6.pdf"
original_name="Laporan_Deployment_Modul_Profil.pdf"
```

---

### 6. **penilaian** - Hasil Penilaian Kinerja

```sql
CREATE TABLE IF NOT EXISTS penilaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skp_id INT NOT NULL,
    penilai_id INT NOT NULL,
    rencana_aksi_id INT,
    realisasi TEXT,
    feedback TEXT,
    skor DECIMAL(3,1),
    tanggal_penilaian TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skp_id) REFERENCES skp(id) ON DELETE CASCADE,
    FOREIGN KEY (penilai_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rencana_aksi_id) REFERENCES rencana_aksi(id) ON DELETE SET NULL
);
```

| Kolom | Tipe | Keterangan |
|-------|------|----------|
| id | INT AUTO_INCREMENT PRIMARY KEY | Unique identifier |
| skp_id | INT | FK ke skp.id |
| penilai_id | INT | FK ke users.id (atasan yang menilai) |
| rencana_aksi_id | INT | FK ke rencana_aksi.id (optional) |
| realisasi | TEXT | Realisasi/hasil kerja actual |
| feedback | TEXT | Umpan balik dari penilai |
| skor | DECIMAL(3,1) | Nilai penilaian (0.0 - 100.0) |
| tanggal_penilaian | TIMESTAMP | Waktu penilaian |

**Struktur Penilaian:**
Setiap penilaian terbagi menjadi 3 aspek indikator:
- **Kuantitas** - Jumlah/volume hasil kerja yang dicapai
- **Kualitas** - Tingkat kualitas & standar hasil kerja
- **Waktu** - Ketepatan waktu penyelesaian pekerjaan

Setiap aspek memiliki:
- **Indikator** - Deskripsi apa yang diukur
- **Target** - Standar yang diharapkan
- **Realisasi** - Hasil actual yang dicapai
- **Rating** - Status tercapai/sebagian/tidak (Hijau/Oranye/Merah)

---

### 7. **tim_kerja** - Daftar Tim Kerja (Future)

```sql
CREATE TABLE IF NOT EXISTS tim_kerja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_tim VARCHAR(255) NOT NULL,
    ketua_id INT NOT NULL,
    deskripsi TEXT,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    dasar_sk VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ketua_id) REFERENCES users(id) ON DELETE RESTRICT
);
```

---

### 8. **persetujuan_pak** - Persetujuan Atasan (Future)

```sql
CREATE TABLE IF NOT EXISTS persetujuan_pak (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skp_id INT NOT NULL,
    status ENUM('pending', 'disetujui', 'ditolak', 'revisi') DEFAULT 'pending',
    catatan TEXT,
    disetujui_oleh INT,
    tanggal_persetujuan TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skp_id) REFERENCES skp(id) ON DELETE CASCADE,
    FOREIGN KEY (disetujui_oleh) REFERENCES users(id) ON DELETE SET NULL
);
```

---

### 9. **angka_kredit** - Angka Kredit (Future)

```sql
CREATE TABLE IF NOT EXISTS angka_kredit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tahun YEAR NOT NULL,
    kegiatan VARCHAR(255) NOT NULL,
    kredit_diperoleh DECIMAL(5,2),
    status ENUM('pending', 'diakui', 'ditolak') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 🔍 Indexing Strategy

```sql
-- Performance optimization untuk query sering
CREATE INDEX idx_user_id ON skp(user_id);
CREATE INDEX idx_skp_id ON rencana_aksi(skp_id);
CREATE INDEX idx_penilai_id ON penilaian(penilai_id);
CREATE INDEX idx_tahun ON skp(tahun_anggaran);
CREATE INDEX idx_status ON skp(status);
```

---

## 🔄 Query Examples

### Get All SKP for User
```sql
SELECT id, tahun_anggaran, status, periode_mulai, periode_selesai
FROM skp
WHERE user_id = 333
ORDER BY tahun_anggaran DESC;
```

### Get Rencana Aksi with Items
```sql
SELECT ra.*, rai.deskripsi, rai.nomor_urut
FROM rencana_aksi ra
LEFT JOIN rencana_aksi_item rai ON ra.id = rai.rencana_aksi_id
WHERE ra.skp_id = 5
ORDER BY ra.triwulan, rai.nomor_urut;
```

### Get Penilaian with Details
```sql
SELECT p.*, u.nama as penilai_nama, ra.rhk_indikator
FROM penilaian p
JOIN users u ON p.penilai_id = u.id
LEFT JOIN rencana_aksi ra ON p.rencana_aksi_id = ra.id
WHERE p.skp_id = 5
ORDER BY p.tanggal_penilaian DESC;
```

### Get Eviden for Rencana Aksi Item
```sql
SELECT ep.*, rai.deskripsi
FROM eviden_penilaian ep
JOIN rencana_aksi_item rai ON ep.rencana_aksi_item_id = rai.id
WHERE rai.rencana_aksi_id = 7
ORDER BY ep.uploaded_at DESC;
```

---

## 📌 Constraints & Rules

1. **Unique Constraint:** User hanya bisa punya 1 SKP per tahun
2. **Foreign Keys:** ON DELETE CASCADE untuk data hierarchy
3. **Password:** Hash dengan bcrypt (password_hash PHP)
4. **File Path:** Random generated untuk security (prevent directory traversal)
5. **Timezone:** UTC+7 (Asia/Jakarta)

---

## 🚀 Maintenance

### Backup
```bash
mysqldump -u root ekinerja_db > ekinerja_db_backup.sql
```

### Restore
```bash
mysql -u root ekinerja_db < ekinerja_db_backup.sql
```

### Check Size
```sql
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.tables
WHERE table_schema = 'ekinerja_db'
ORDER BY size_mb DESC;
```

