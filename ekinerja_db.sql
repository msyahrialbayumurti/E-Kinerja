CREATE DATABASE IF NOT EXISTS ekinerja_db;
USE ekinerja_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(20) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    pangkat_golongan VARCHAR(50),
    jabatan VARCHAR(100),
    role ENUM('admin', 'ketua_yayasan', 'guru_pegawai') DEFAULT 'guru_pegawai',
    atasan_id INT NULL,
    email VARCHAR(100) NULL,
    no_hp VARCHAR(30) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (atasan_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT IGNORE INTO users (id, nip, username, password, nama, pangkat_golongan, jabatan, role, atasan_id, email, no_hp) VALUES 
(1, '111111111111111111', 'admin', '$2y$10$rcvXqxUgFjWN6y2HDPC89.v09vWID03PC95meBTODTZ9iXgsoUVlO', 'Administrator', 'Golongan IV/a', 'Kepala Admin', 'admin', NULL, 'admin@sekolah.sch.id', '081122334455'),
(2, '222222222222222222', 'ketua', '$2y$10$WK1trhMYlRiYnGnxjJZWTOpMDmp/zw/OpaCEJZtVDcW1PsbmUMR6i', 'Dr. Ir. Ahmad Yani, M.Si.', 'Golongan IV/b', 'Ketua Yayasan / Pimpinan', 'ketua_yayasan', NULL, 'ketua.yayasan@sekolah.sch.id', '081298765432'),
(3, '333333333333333333', 'guru', '$2y$10$sgbohLv.c0NTHqWWKrVk3.EahZct8vxcedUdDuqZauts1MShGPFTK', 'Budi Santoso, S.Pd., M.Kom.', 'Golongan III/a', 'Guru Kejuruan / Ahli Pertama', 'guru_pegawai', 2, 'budi.guru@sekolah.sch.id', '081234567890');


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

-- Insert data dummy SKP
INSERT IGNORE INTO skp (user_id, tahun_anggaran, periode_mulai, periode_selesai, status, pendekatan) VALUES
(3, 2026, '2026-01-01', '2026-12-31', 'aktif', 'Kuantitatif'),
(3, 2025, '2025-01-01', '2025-12-31', 'dinilai', 'Kuantitatif');


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

-- Insert data dummy Rencana Aksi
INSERT IGNORE INTO rencana_aksi (skp_id, user_id, triwulan, rhk_indikator, target) VALUES
(1, 3, 1, 'Tersedianya sistem informasi manajemen kinerja yang terintegrasi', '2 Modul'),
(1, 3, 2, 'Terlaksananya pemeliharaan server database secara berkala', '99.9%'),
(1, 3, 3, 'Peningkatan kapasitas infrastruktur IT', '4 Server'),
(1, 3, 4, 'Dokumentasi sistem dan training staff', '100%');


CREATE TABLE IF NOT EXISTS rencana_aksi_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rencana_aksi_id INT NOT NULL,
    deskripsi TEXT NOT NULL,
    nomor_urut INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rencana_aksi_id) REFERENCES rencana_aksi(id) ON DELETE CASCADE
);

-- Insert data dummy Rencana Aksi Item
INSERT IGNORE INTO rencana_aksi_item (rencana_aksi_id, deskripsi, nomor_urut) VALUES
(1, 'Menyusun dokumen requirement dan mock-up modul Profil', 1),
(1, 'Mengembangkan backend controller untuk integrasi database SIASN', 2),
(2, 'Backup database setiap hari Jumat jam 22:00 WIB', 1),
(2, 'Monitoring uptime server dengan tools Zabbix', 2),
(3, 'Procurement server baru dengan spesifikasi tinggi', 1),
(3, 'Setup dan konfigurasi cluster load balancer', 2),
(4, 'Membuat SOP operasional sistem E-Kinerja', 1),
(4, 'Training tim IT dan end user', 2);


CREATE TABLE IF NOT EXISTS eviden_penilaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rencana_aksi_item_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rencana_aksi_item_id) REFERENCES rencana_aksi_item(id) ON DELETE CASCADE
);

-- Insert data dummy Eviden
INSERT IGNORE INTO eviden_penilaian (rencana_aksi_item_id, file_path, original_name) VALUES
(1, 'a1b2c3d4e5f6.pdf', 'Laporan_Deployment_Modul_Profil.pdf'),
(2, 'b2c3d4e5f6a7.pdf', 'Backend_Integration_Documentation.pdf'),
(4, 'c3d4e5f6a7b8.jpg', 'Zabbix_Monitoring_Screenshot.jpg');


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

-- Insert data dummy Penilaian
INSERT IGNORE INTO penilaian (skp_id, penilai_id, rencana_aksi_id, realisasi, feedback, skor) VALUES
(1, 2, 1, 'Telah diselesaikan 2 modul (Profil dan SKP) sesuai target dengan hasil UAT tanpa major bug, diselesaikan dalam waktu 2.5 bulan.', 'Pekerjaan sangat baik, lanjutkan untuk modul berikutnya dengan standar kualitas yang sama.', 85.5),
(1, 2, 2, 'Integrasi database SIASN berhasil dilakukan dengan success rate 99.8%, semua field mapping sudah sesuai dengan requirement.', 'Excellent work! Integration testing hasil memuaskan. Dokumentasi perlu ditambahkan.', 88.0),
(1, 2, 3, 'Server infrastructure setup 80% selesai, masih menunggu approval vendor untuk 2 server tambahan.', 'Progres baik, pastikan jadwal tetap sesuai timeline yang disepakati.', 78.0);



-- Bukti Dukung (Uploaded by Guru)
CREATE TABLE IF NOT EXISTS bukti_dukung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penilaian_id INT NOT NULL,
    user_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    nama_label VARCHAR(255),
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penilaian_id) REFERENCES penilaian(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Feedback (Added by Ketua Yayasan)
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penilaian_id INT NOT NULL,
    penilai_id INT NOT NULL,
    simbol ENUM('jempol', 'bintang', 'apresiasi', 'catatan') DEFAULT 'jempol',
    feedback_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penilaian_id) REFERENCES penilaian(id) ON DELETE CASCADE,
    FOREIGN KEY (penilai_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table SKP RHK (PermenPANRB No. 6 Tahun 2022)
CREATE TABLE IF NOT EXISTS skp_rhk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skp_id INT NOT NULL,
    rhk_pimpinan TEXT NOT NULL,
    rhk_individu TEXT NOT NULL,
    rencana_aksi TEXT,
    urutan INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skp_id) REFERENCES skp(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS skp_rhk_indikator (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rhk_id INT NOT NULL,
    aspek ENUM('KUANTITAS', 'KUALITAS', 'WAKTU') NOT NULL,
    deskripsi_indikator TEXT NOT NULL,
    target VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rhk_id) REFERENCES skp_rhk(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS eviden_dukung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    indikator_id INT NOT NULL,
    user_id INT NOT NULL,
    nama_label VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    link_eksternal VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (indikator_id) REFERENCES skp_rhk_indikator(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS penilaian_realisasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rhk_id INT NOT NULL,
    penilai_id INT NOT NULL,
    realisasi TEXT NULL,
    feedback_simbol ENUM('jempol', 'bintang', 'catatan', 'apresiasi') NULL,
    feedback_teks TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rhk (rhk_id),
    FOREIGN KEY (rhk_id) REFERENCES skp_rhk(id) ON DELETE CASCADE,
    FOREIGN KEY (penilai_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO skp_rhk (id, skp_id, rhk_pimpinan, rhk_individu, rencana_aksi) VALUES
(1, 1, 'Terwujudnya implementasi tata kelola sekolah unggul dan pembelajaran digital inovatif', 'Tersedianya perangkat pembelajaran digital (modul & asesmen online) serta terlaksananya evaluasi kinerja pembelajaran', '• Menyusun modul pembelajaran semester genap\n• Mengembangkan asesmen online terintegrasi\n• Melaksanakan evaluasi dan rekapitulasi nilai siswa');

INSERT IGNORE INTO skp_rhk_indikator (id, rhk_id, aspek, deskripsi_indikator, target) VALUES
(1, 1, 'KUANTITAS', 'Jumlah perangkat modul pembelajaran interaktif yang dibuat dan diimplementasikan', '4 Modul'),
(2, 1, 'KUALITAS', 'Persentase kelayakan materi ajar sesuai standar kurikulum nasional', '95%'),
(3, 1, 'WAKTU', 'Ketepatan durasi penyelesaian modul dan pelaporan hasil belajar', '6 Bulan');

INSERT IGNORE INTO penilaian_realisasi (rhk_id, penilai_id, realisasi, feedback_simbol, feedback_teks) VALUES
(1, 2, 'Telah diselesaikan 4 modul pembelajaran digital semester genap tepat waktu dengan capaian serapan materi siswa mencapai 96%.', 'bintang', 'Pekerjaan luar biasa dan tuntas! Teruskan inovasi pembuatan media ajar digital ini.');

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

INSERT IGNORE INTO tim_kerja (nama_tim, ketua_id, deskripsi, status, dasar_sk) VALUES
(1, 'Tim Transformasi Digital Pendidikan', 2, 'Tim koordinasi transformasi digital di sektor pendidikan', 'aktif', 'SK.001/Pusdatin/2026');


CREATE TABLE IF NOT EXISTS anggota_tim_kerja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tim_kerja_id INT NOT NULL,
    anggota_id INT NOT NULL,
    posisi VARCHAR(100),
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tim_kerja_id) REFERENCES tim_kerja(id) ON DELETE CASCADE,
    FOREIGN KEY (anggota_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tim_anggota (tim_kerja_id, anggota_id)
);


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


CREATE TABLE IF NOT EXISTS rekap_keberatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penilaian_id INT NOT NULL,
    user_id INT NOT NULL,
    alasan_keberatan TEXT NOT NULL,
    status ENUM('pending', 'diproses', 'ditanggapi', 'ditolak') DEFAULT 'pending',
    tanggapan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penilaian_id) REFERENCES penilaian(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


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
