<?php

/**
 * Seeder data demo E-Kinerja.
 * Jalankan dari terminal: php scripts/seed_demo_data.php
 * Aman dijalankan berulang (idempotent) untuk data dengan ID tetap.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script ini hanya boleh dijalankan melalui CLI.');
}

require __DIR__ . '/../config/database.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Koneksi database gagal. Periksa config/database.php.\n");
    exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function upsert(PDO $pdo, string $table, array $cols, array $values, array $updateCols): void
{
    $place = implode(',', array_fill(0, count($cols), '?'));
    $colList = '`' . implode('`,`', $cols) . '`';
    $sql = "INSERT INTO `$table` ($colList) VALUES ($place)";
    $sets = [];
    foreach ($updateCols as $c) {
        $sets[] = "`$c`=VALUES(`$c`)";
    }
    $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(',', $sets);
    $pdo->prepare($sql)->execute($values);
}

function createEvidenceImage(string $path, string $title, string $subtitle, string $accentHex): void
{
    if (!function_exists('imagecreatetruecolor')) {
        return;
    }

    $w = 900;
    $h = 1200;
    $img = imagecreatetruecolor($w, $h);

    $white = imagecolorallocate($img, 255, 255, 255);
    $navy = imagecolorallocate($img, 15, 23, 42);
    $slate = imagecolorallocate($img, 100, 116, 139);
    $light = imagecolorallocate($img, 241, 245, 249);
    $line = imagecolorallocate($img, 226, 232, 240);
    $accent = imagecolorallocate($img, hexdec(substr($accentHex, 0, 2)), hexdec(substr($accentHex, 2, 2)), hexdec(substr($accentHex, 4, 2)));

    imagefill($img, 0, 0, $white);
    imagefilledrectangle($img, 0, 0, $w, 190, $navy);
    imagefilledrectangle($img, 0, 190, $w, 196, $accent);

    $font = 'C:\\Windows\\Fonts\\segoeui.ttf';
    if (!is_file($font)) {
        $font = 'C:\\Windows\\Fonts\\arial.ttf';
    }
    $bold = 'C:\\Windows\\Fonts\\segoeuib.ttf';
    if (!is_file($bold)) {
        $bold = $font;
    }
    $hasTtf = is_file($font);

    $write = static function ($size, $x, $y, $color, string $text) use ($img, $font, $hasTtf) {
        if ($hasTtf) {
            imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
        } else {
            imagestring($img, 5, $x, $y - 16, $text, $color);
        }
    };

    $write(30, 60, 78, $white, 'INTEGRITA');
    $write(14, 60, 112, imagecolorallocate($img, 203, 213, 225), 'SISTEM INFORMASI KINERJA PENDIDIKAN');
    $write(13, 60, 150, $accent, 'BUKTI DUKUNG KINERJA');

    $write(28, 60, 270, $navy, $title);
    $write(16, 60, 312, $slate, $subtitle);

    imagefilledrectangle($img, 60, 360, $w - 60, 1080, $light);
    imagerectangle($img, 60, 360, $w - 60, 1080, $line);

    $labels = ['Uraian Kegiatan', 'Waktu Pelaksanaan', 'Hasil / Capaian', 'Dokumentasi'];
    $y = 420;
    foreach ($labels as $i => $label) {
        $write(13, 90, $y, $accent, strtoupper($label));
        imagefilledrectangle($img, 90, $y + 12, $w - 90, $y + 14, $line);
        $lineY = $y + 48;
        for ($l = 0; $l < 3; $l++) {
            imagefilledrectangle($img, 90, $lineY, $w - 90 - ($l * 40), $lineY + 6, $line);
            $lineY += 24;
        }
        $y += 170;
    }

    imagefilledrectangle($img, 60, 1110, $w - 60, 1112, $line);
    $write(12, 60, 1150, $slate, 'Dokumen contoh untuk keperluan demonstrasi aplikasi E-Kinerja.');
    $write(12, 60, 1172, $slate, 'Dicetak otomatis oleh seeder data demo.');

    imagepng($img, $path);
    imagedestroy($img);
}

$passwordHash = (string) $pdo->query('SELECT password FROM users WHERE id = 1')->fetchColumn();
if ($passwordHash === '') {
    fwrite(STDERR, "Password hash admin tidak ditemukan.\n");
    exit(1);
}

$gurus = [
    3 => [
        'nama' => 'Budi Santoso, S.Pd., M.Kom.',
        'gol' => 'Golongan III/a',
        'jabatan' => 'Guru Informatika / Ahli Pertama',
        'username' => 'guru',
        'email' => 'budi.santoso@sekolah.sch.id',
        'hp' => '081234567890',
        'mapel' => 'Informatika',
        'eviden' => ['Modul Pembelajaran Digital', 'Dokumentasi Workshop Literasi Digital'],
        'accent' => '2563EB',
    ],
    4 => [
        'nama' => 'Dewi Lestari, S.Pd.',
        'gol' => 'Golongan III/b',
        'jabatan' => 'Guru Bahasa Indonesia / Ahli Muda',
        'username' => 'dewi',
        'email' => 'dewi.lestari@sekolah.sch.id',
        'hp' => '081234567891',
        'mapel' => 'Bahasa Indonesia',
        'eviden' => ['Modul Ajar Bahasa Indonesia', 'Dokumentasi Program Literasi Sekolah'],
        'accent' => '7C3AED',
    ],
    5 => [
        'nama' => 'Ahmad Fauzi, S.Kom.',
        'gol' => 'Golongan III/a',
        'jabatan' => 'Guru Informatika / Ahli Pertama',
        'username' => 'ahmad',
        'email' => 'ahmad.fauzi@sekolah.sch.id',
        'hp' => '081234567892',
        'mapel' => 'Informatika',
        'eviden' => ['Media Ajar Interaktif Informatika', 'Dokumentasi Pemeliharaan Laboratorium'],
        'accent' => '0F766E',
    ],
    6 => [
        'nama' => 'Nurul Hidayah, S.Pd.I.',
        'gol' => 'Golongan III/b',
        'jabatan' => 'Guru Pendidikan Agama Islam / Ahli Muda',
        'username' => 'nurul',
        'email' => 'nurul.hidayah@sekolah.sch.id',
        'hp' => '081234567893',
        'mapel' => 'Pendidikan Agama Islam',
        'eviden' => ['Materi Praktik Ibadah PAI', 'Dokumentasi Pembiasaan Ibadah'],
        'accent' => 'B45309',
    ],
    7 => [
        'nama' => 'Rizky Pratama, S.Pd.',
        'gol' => 'Golongan III/a',
        'jabatan' => 'Guru PJOK / Ahli Pertama',
        'username' => 'rizky',
        'email' => 'rizky.pratama@sekolah.sch.id',
        'hp' => '081234567894',
        'mapel' => 'PJOK',
        'eviden' => ['Program Pembelajaran PJOK', 'Dokumentasi Kompetisi Olahraga'],
        'accent' => 'BE123C',
    ],
    8 => [
        'nama' => 'Siti Aminah, S.E.',
        'gol' => 'Golongan III/c',
        'jabatan' => 'Guru Ekonomi / Ahli Muda',
        'username' => 'siti',
        'email' => 'siti.aminah@sekolah.sch.id',
        'hp' => '081234567895',
        'mapel' => 'Ekonomi',
        'eviden' => ['Modul Proyek Kewirausahaan', 'Dokumentasi Business Center Sekolah'],
        'accent' => '0369A1',
    ],
];

$rhkTemplates = [
    3 => [
        [
            'pimpinan' => 'Terwujudnya implementasi tata kelola sekolah unggul dan pembelajaran digital inovatif',
            'individu' => 'Tersedianya perangkat pembelajaran digital (modul dan asesmen online) serta terlaksananya evaluasi hasil belajar',
            'aksi' => "1. Menyusun modul pembelajaran digital semester genap\n2. Mengembangkan bank soal dan asesmen online\n3. Melaksanakan evaluasi serta rekapitulasi nilai siswa",
            'indikator' => [
                ['KUANTITAS', 'Jumlah perangkat modul pembelajaran digital yang disusun', '4 Modul'],
                ['KUALITAS', 'Persentase kelayakan materi ajar sesuai standar kurikulum', '95%'],
                ['WAKTU', 'Ketepatan waktu penyelesaian modul dan pelaporan', '6 Bulan'],
            ],
        ],
        [
            'pimpinan' => 'Meningkatnya mutu layanan pembelajaran berbasis teknologi',
            'individu' => 'Terlaksananya pelatihan literasi digital bagi guru dan siswa',
            'aksi' => "1. Menyusun kurikulum pelatihan literasi digital\n2. Melaksanakan workshop sebanyak 4 sesi\n3. Melakukan evaluasi dan pendampingan pascapelatihan",
            'indikator' => [
                ['KUANTITAS', 'Jumlah guru yang mengikuti pelatihan literasi digital', '40 Guru'],
                ['KUALITAS', 'Persentase peserta yang lulus asesmen pascapelatihan', '90%'],
                ['WAKTU', 'Jumlah sesi workshop yang terlaksana', '4 Sesi'],
            ],
        ],
    ],
    4 => [
        [
            'pimpinan' => 'Meningkatnya kompetensi literasi peserta didik',
            'individu' => 'Terlaksananya pembelajaran teks naratif dan argumentatif berbasis proyek',
            'aksi' => "1. Menyusun modul ajar teks naratif dan argumentatif\n2. Melaksanakan pembelajaran berbasis proyek\n3. Menilai portofolio dan presentasi siswa",
            'indikator' => [
                ['KUANTITAS', 'Jumlah modul ajar yang dikembangkan', '3 Modul'],
                ['KUALITAS', 'Nilai rata-rata asesmen literasi siswa', '85'],
                ['WAKTU', 'Ketepatan penyelesaian materi pembelajaran', '2 Semester'],
            ],
        ],
        [
            'pimpinan' => 'Terwujudnya budaya membaca di lingkungan sekolah',
            'individu' => 'Terselenggaranya program literasi 15 menit dan pengelolaan pojok baca kelas',
            'aksi' => "1. Menyusun jadwal literasi harian\n2. Menyediakan dan merotasi koleksi pojok baca\n3. Memantau jurnal baca siswa",
            'indikator' => [
                ['KUANTITAS', 'Jumlah kelas dengan pojok baca aktif', '18 Kelas'],
                ['KUALITAS', 'Persentase siswa yang menuntaskan minimal 10 buku', '80%'],
                ['WAKTU', 'Frekuensi pelaksanaan literasi', '5 Hari/Minggu'],
            ],
        ],
    ],
    5 => [
        [
            'pimpinan' => 'Terwujudnya transformasi digital pembelajaran',
            'individu' => 'Mengembangkan media ajar interaktif dan asesmen berbasis TIK',
            'aksi' => "1. Merancang media ajar interaktif\n2. Mengimplementasikan asesmen berbasis TIK\n3. Menganalisis hasil belajar siswa",
            'indikator' => [
                ['KUANTITAS', 'Jumlah media ajar interaktif yang diproduksi', '4 Media'],
                ['KUALITAS', 'Persentase siswa yang mencapai KKM', '90%'],
                ['WAKTU', 'Durasi implementasi media ajar', '6 Bulan'],
            ],
        ],
        [
            'pimpinan' => 'Meningkatnya keamanan dan pemanfaatan laboratorium komputer',
            'individu' => 'Melaksanakan pemeliharaan perangkat serta pembinaan literasi digital siswa',
            'aksi' => "1. Melakukan inventarisasi dan pemeliharaan perangkat\n2. Menyusun SOP penggunaan laboratorium\n3. Membina siswa dalam literasi digital",
            'indikator' => [
                ['KUANTITAS', 'Jumlah unit komputer yang terpelihara', '30 Unit'],
                ['KUALITAS', 'Tingkat kesiapan perangkat laboratorium', '95%'],
                ['WAKTU', 'Periode pemeliharaan berjalan', '12 Bulan'],
            ],
        ],
    ],
    6 => [
        [
            'pimpinan' => 'Meningkatnya pemahaman nilai religius peserta didik',
            'individu' => 'Terlaksananya pembelajaran PAI berbasis praktik ibadah dan akhlak',
            'aksi' => "1. Menyusun materi praktik ibadah\n2. Melaksanakan pembelajaran berbasis praktik\n3. Melakukan penilaian sikap dan pengetahuan",
            'indikator' => [
                ['KUANTITAS', 'Jumlah materi praktik ibadah yang disusun', '8 Materi'],
                ['KUALITAS', 'Nilai rata-rata asesmen PAI siswa', '88'],
                ['WAKTU', 'Ketepatan penyelesaian materi', '2 Semester'],
            ],
        ],
        [
            'pimpinan' => 'Terwujudnya pembiasaan ibadah di lingkungan sekolah',
            'individu' => "Menyelenggarakan program sholat berjamaah dan tadarus Al-Qur'an",
            'aksi' => "1. Menyusun jadwal pembiasaan ibadah\n2. Mendampingi pelaksanaan sholat berjamaah\n3. Memantau tadarus dan hafalan siswa",
            'indikator' => [
                ['KUANTITAS', 'Jumlah program pembiasaan ibadah', '5 Program'],
                ['KUALITAS', 'Persentase siswa yang aktif mengikuti pembiasaan', '90%'],
                ['WAKTU', 'Frekuensi pendampingan ibadah', '12 Bulan'],
            ],
        ],
    ],
    7 => [
        [
            'pimpinan' => 'Meningkatnya kebugaran jasmani peserta didik',
            'individu' => 'Terlaksananya pembelajaran PJOK dan tes kebugaran secara berkala',
            'aksi' => "1. Menyusun program pembelajaran PJOK\n2. Melaksanakan tes kebugaran berkala\n3. Mengolah dan melaporkan hasil tes",
            'indikator' => [
                ['KUANTITAS', 'Jumlah pertemuan pembelajaran PJOK', '12 Pertemuan'],
                ['KUALITAS', 'Persentase siswa dengan kategori kebugaran baik', '85%'],
                ['WAKTU', 'Periode pelaksanaan pembelajaran', '2 Semester'],
            ],
        ],
        [
            'pimpinan' => 'Terwujudnya prestasi olahraga di tingkat sekolah',
            'individu' => 'Melatih dan mendampingi tim sekolah untuk kompetisi antar sekolah',
            'aksi' => "1. Melakukan seleksi atlet sekolah\n2. Menyusun program latihan rutin\n3. Mendampingi tim pada kompetisi",
            'indikator' => [
                ['KUANTITAS', 'Jumlah tim olahraga yang dibina', '2 Tim'],
                ['KUALITAS', 'Jumlah medali pada kompetisi', '3 Medali'],
                ['WAKTU', 'Durasi program pembinaan', '6 Bulan'],
            ],
        ],
    ],
    8 => [
        [
            'pimpinan' => 'Meningkatnya literasi keuangan peserta didik',
            'individu' => 'Terlaksananya pembelajaran ekonomi berbasis proyek kewirausahaan',
            'aksi' => "1. Menyusun modul proyek kewirausahaan\n2. Melaksanakan pembelajaran berbasis proyek\n3. Menilai laporan dan presentasi siswa",
            'indikator' => [
                ['KUANTITAS', 'Jumlah proyek kewirausahaan siswa', '4 Proyek'],
                ['KUALITAS', 'Nilai rata-rata asesmen ekonomi', '90'],
                ['WAKTU', 'Ketepatan penyelesaian proyek', '2 Semester'],
            ],
        ],
        [
            'pimpinan' => 'Terwujudnya kemandirian usaha siswa',
            'individu' => 'Mengembangkan unit business center sekolah',
            'aksi' => "1. Menyusun rencana bisnis unit sekolah\n2. Mengelola operasional business center\n3. Melakukan evaluasi dan pengembangan produk",
            'indikator' => [
                ['KUANTITAS', 'Jumlah produk unggulan yang dikembangkan', '3 Produk'],
                ['KUALITAS', 'Tingkat kepuasan konsumen business center', '85%'],
                ['WAKTU', 'Periode pengelolaan unit usaha', '12 Bulan'],
            ],
        ],
    ],
];

$skps = [
    1 => [3, 2026, 'aktif'],
    2 => [3, 2025, 'dinilai'],
    3 => [4, 2026, 'aktif'],
    4 => [4, 2025, 'selesai'],
    5 => [5, 2026, 'aktif'],
    6 => [5, 2025, 'dinilai'],
    7 => [6, 2026, 'persetujuan'],
    8 => [6, 2025, 'dinilai'],
    9 => [7, 2026, 'draft'],
    10 => [7, 2025, 'selesai'],
    11 => [8, 2026, 'aktif'],
    12 => [8, 2025, 'dinilai'],
];

$feedbackSymbols = ['bintang', 'jempol', 'apresiasi', 'catatan'];
$feedbackTexts = [
    'bintang' => 'Kinerja melampaui target yang ditetapkan. Pertahankan kualitas, konsistensi, dan inovasi dalam pelaksanaan tugas.',
    'jempol' => 'Capaian sesuai ekspektasi pimpinan. Lanjutkan dengan perbaikan berkelanjutan pada periode berikutnya.',
    'apresiasi' => 'Penyelesaian tugas rapi dan tuntas. Dokumentasi bukti dukung tersusun dengan baik dan mudah diverifikasi.',
    'catatan' => 'Perlu perbaikan pada kelengkapan bukti dukung dan ketepatan waktu pelaporan. Koordinasikan dengan pimpinan unit.',
];

$pdo->beginTransaction();

try {
    // 1. Users
    $userRows = [
        [1, '111111111111111111', 'admin', 'Administrator', 'Administrator Sistem', 'Administrator Sistem', 'admin', null, 'admin@sekolah.sch.id', '081200000001'],
        [2, '222222222222222222', 'ketua', 'Dr. Ir. Ahmad Yani, M.Si.', 'Pembina Yayasan', 'Ketua Yayasan', 'ketua_yayasan', null, 'ketua.yayasan@sekolah.sch.id', '081200000002'],
    ];
    foreach ($userRows as $row) {
        upsert(
            $pdo,
            'users',
            ['id', 'nip', 'username', 'password', 'nama', 'pangkat_golongan', 'jabatan', 'role', 'atasan_id', 'email', 'no_hp'],
            [$row[0], $row[1], $row[2], $passwordHash, $row[3], $row[4], $row[5], $row[6], $row[7] === null ? null : $row[7], $row[8], $row[9]],
            ['nip', 'username', 'nama', 'pangkat_golongan', 'jabatan', 'role', 'atasan_id', 'email', 'no_hp']
        );
    }

    foreach ($gurus as $uid => $g) {
        upsert(
            $pdo,
            'users',
            ['id', 'nip', 'username', 'password', 'nama', 'pangkat_golongan', 'jabatan', 'role', 'atasan_id', 'email', 'no_hp'],
            [$uid, str_repeat((string) $uid, 18), $g['username'], $passwordHash, $g['nama'], $g['gol'], $g['jabatan'], 'guru_pegawai', 2, $g['email'], $g['hp']],
            ['nip', 'username', 'nama', 'pangkat_golongan', 'jabatan', 'role', 'atasan_id', 'email', 'no_hp']
        );
    }

    // 2. SKP
    foreach ($skps as $skpId => [$uid, $tahun, $status]) {
        upsert(
            $pdo,
            'skp',
            ['id', 'user_id', 'tahun_anggaran', 'periode_mulai', 'periode_selesai', 'status', 'pendekatan'],
            [$skpId, $uid, $tahun, $tahun . '-01-01', $tahun . '-12-31', $status, 'Kuantitatif'],
            ['user_id', 'tahun_anggaran', 'periode_mulai', 'periode_selesai', 'status', 'pendekatan']
        );
    }

    // 3. RHK + indikator
    $rhkId = 1;
    $rhkMeta = [];
    foreach ($skps as $skpId => [$uid, $tahun, $status]) {
        foreach ($rhkTemplates[$uid] as $index => $rhk) {
            upsert(
                $pdo,
                'skp_rhk',
                ['id', 'skp_id', 'rhk_pimpinan', 'rhk_individu', 'rencana_aksi', 'urutan'],
                [$rhkId, $skpId, $rhk['pimpinan'], $rhk['individu'], $rhk['aksi'], $index + 1],
                ['skp_id', 'rhk_pimpinan', 'rhk_individu', 'rencana_aksi', 'urutan']
            );

            $rhkMeta[$rhkId] = [
                'skp_id' => $skpId,
                'user_id' => $uid,
                'tahun' => $tahun,
                'status' => $status,
                'template' => $rhk,
                'indikator' => [],
            ];

            foreach ($rhk['indikator'] as $i => [$aspek, $deskripsi, $target]) {
                $indId = (3 * ($rhkId - 1)) + $i + 1;
                upsert(
                    $pdo,
                    'skp_rhk_indikator',
                    ['id', 'rhk_id', 'aspek', 'deskripsi_indikator', 'target'],
                    [$indId, $rhkId, $aspek, $deskripsi, $target],
                    ['rhk_id', 'aspek', 'deskripsi_indikator', 'target']
                );
                $rhkMeta[$rhkId]['indikator'][$aspek] = ['id' => $indId, 'deskripsi' => $deskripsi, 'target' => $target];
            }

            $rhkId++;
        }
    }

    // 4. Eviden dukung + file contoh
    $uploadDir = __DIR__ . '/../uploads/eviden';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $evidenId = 1;
    foreach ($gurus as $uid => $g) {
        $skp2026 = array_key_first(array_filter($skps, static fn ($s) => $s[0] === $uid && $s[1] === 2026));
        $rhkIds = [];
        foreach ($rhkMeta as $rid => $meta) {
            if ($meta['skp_id'] === $skp2026) {
                $rhkIds[] = $rid;
            }
        }

        $indIds = [
            $rhkMeta[$rhkIds[0]]['indikator']['KUANTITAS']['id'],
            $rhkMeta[$rhkIds[1]]['indikator']['KUALITAS']['id'],
        ];

        foreach ($g['eviden'] as $i => $label) {
            $fileName = 'demo_eviden_' . $uid . '_' . ($i + 1) . '.png';
            createEvidenceImage(
                $uploadDir . '/' . $fileName,
                $label,
                $g['mapel'] . ' - ' . $g['nama'],
                $g['accent']
            );

            upsert(
                $pdo,
                'eviden_dukung',
                ['id', 'indikator_id', 'user_id', 'nama_label', 'file_path', 'original_name', 'link_eksternal'],
                [$evidenId, $indIds[$i], $uid, $label, $fileName, str_replace([' ', '-'], '_', $label) . '.png', null],
                ['indikator_id', 'user_id', 'nama_label', 'file_path', 'original_name', 'link_eksternal']
            );
            $evidenId++;
        }
    }

    // 5. Penilaian realisasi & feedback
    foreach ($rhkMeta as $rid => $meta) {
        if (!in_array($meta['status'], ['aktif', 'selesai', 'dinilai'], true)) {
            continue;
        }

        $first = $meta['template']['indikator'][0];
        $symbol = $feedbackSymbols[($rid - 1) % count($feedbackSymbols)];
        $realisasi = sprintf(
            'Realisasi periode %d: %s tercapai dengan hasil %s. Bukti dukung kegiatan telah diunggah dan diverifikasi oleh pimpinan.',
            $meta['tahun'],
            $first[1],
            $first[2]
        );

        upsert(
            $pdo,
            'penilaian_realisasi',
            ['rhk_id', 'penilai_id', 'realisasi', 'feedback_simbol', 'feedback_teks'],
            [$rid, 2, $realisasi, $symbol, $feedbackTexts[$symbol]],
            ['penilai_id', 'realisasi', 'feedback_simbol', 'feedback_teks']
        );
    }

    // 6. Tim kerja & anggota
    $teams = [
        [1, 'Tim Transformasi Digital Pendidikan', 2, 'Tim koordinasi transformasi digital dan pemanfaatan TIK di lingkungan sekolah.', 'aktif', 'SK.001/Pusdatin/2026'],
        [2, 'Tim Pengembangan Kurikulum Merdeka', 2, 'Tim penyusunan, implementasi, dan evaluasi kurikulum merdeka.', 'aktif', 'SK.002/Kurikulum/2026'],
        [3, 'Tim Peningkatan Mutu Guru', 2, 'Tim pendampingan dan pengembangan kompetensi profesional guru.', 'aktif', 'SK.003/Mutu/2026'],
    ];
    foreach ($teams as $t) {
        upsert(
            $pdo,
            'tim_kerja',
            ['id', 'nama_tim', 'ketua_id', 'deskripsi', 'status', 'dasar_sk'],
            $t,
            ['nama_tim', 'ketua_id', 'deskripsi', 'status', 'dasar_sk']
        );
    }

    $members = [
        [1, 1, 3, 'Koordinator Teknologi Pembelajaran'],
        [2, 1, 5, 'Anggota / Pengembang Media Digital'],
        [3, 1, 4, 'Anggota / Literasi Digital'],
        [4, 1, 6, 'Anggota / Dokumentasi'],
        [5, 2, 4, 'Koordinator Kurikulum'],
        [6, 2, 8, 'Anggota / Asesmen Pembelajaran'],
        [7, 2, 3, 'Anggota / Integrasi TIK'],
        [8, 3, 6, 'Koordinator Pengembangan Kompetensi'],
        [9, 3, 7, 'Anggota / Pembinaan Prestasi'],
        [10, 3, 8, 'Anggota / Evaluasi Mutu'],
    ];
    foreach ($members as $m) {
        upsert(
            $pdo,
            'anggota_tim_kerja',
            ['id', 'tim_kerja_id', 'anggota_id', 'posisi'],
            $m,
            ['tim_kerja_id', 'anggota_id', 'posisi']
        );
    }

    // 7. Persetujuan PAK
    $pakRows = [
        [1, 2, 'disetujui', 'SKP tahun 2025 telah sesuai dan disetujui.', 2, '2025-12-20 10:00:00'],
        [2, 4, 'disetujui', 'SKP tahun 2025 telah sesuai dan disetujui.', 2, '2025-12-20 10:05:00'],
        [3, 6, 'disetujui', 'SKP tahun 2025 telah sesuai dan disetujui.', 2, '2025-12-20 10:10:00'],
        [4, 8, 'revisi', 'Perbaiki rumusan indikator kualitas agar lebih terukur.', 2, '2025-12-21 09:00:00'],
        [5, 10, 'pending', null, null, null],
        [6, 12, 'disetujui', 'SKP tahun 2025 telah sesuai dan disetujui.', 2, '2025-12-20 10:15:00'],
        [7, 1, 'pending', null, null, null],
        [8, 3, 'pending', null, null, null],
    ];
    foreach ($pakRows as $p) {
        upsert(
            $pdo,
            'persetujuan_pak',
            ['id', 'skp_id', 'status', 'catatan', 'disetujui_oleh', 'tanggal_persetujuan'],
            $p,
            ['skp_id', 'status', 'catatan', 'disetujui_oleh', 'tanggal_persetujuan']
        );
    }

    // 8. Angka kredit
    $akId = 1;
    $akValues = [26.00, 24.50, 28.00, 22.75, 25.50, 27.25];
    foreach ($gurus as $uid => $g) {
        foreach ([2025, 2024] as $tahun) {
            upsert(
                $pdo,
                'angka_kredit',
                ['id', 'user_id', 'tahun', 'kegiatan', 'kredit_diperoleh', 'status'],
                [$akId, $uid, $tahun, 'Pelaksanaan tugas pembelajaran, penilaian, dan pembinaan peserta didik', $akValues[($akId - 1) % count($akValues)], 'diakui'],
                ['user_id', 'tahun', 'kegiatan', 'kredit_diperoleh', 'status']
            );
            $akId++;
        }
    }

    // 9. Rencana aksi (data legacy) + item
    $raId = 1;
    $raItemId = 1;
    foreach ($gurus as $uid => $g) {
        $template = $rhkTemplates[$uid][0];
        foreach ([1, 2, 3, 4] as $triwulan) {
            upsert(
                $pdo,
                'rencana_aksi',
                ['id', 'user_id', 'triwulan', 'rhk_indikator', 'target'],
                [$raId, $uid, $triwulan, $template['indikator'][0][1], $template['indikator'][0][2]],
                ['user_id', 'triwulan', 'rhk_indikator', 'target']
            );

            $items = [
                $template['indikator'][0][1] . ' pada triwulan ' . $triwulan,
                'Monitoring dan evaluasi capaian triwulan ' . $triwulan,
            ];
            foreach ($items as $item) {
                upsert(
                    $pdo,
                    'rencana_aksi_item',
                    ['id', 'rencana_aksi_id', 'deskripsi'],
                    [$raItemId, $raId, $item],
                    ['rencana_aksi_id', 'deskripsi']
                );
                $raItemId++;
            }
            $raId++;
        }
    }

    // 10. Penilaian legacy + eviden penilaian + rekap keberatan
    $nilaiRows = [
        [1, 1, 2, 1, 'Realisasi triwulan 1 mencapai 100% dari target.', 'Capaian baik, lanjutkan.', 90.0],
        [2, 1, 2, 2, 'Realisasi triwulan 2 mencapai 100% dari target.', 'Konsisten dan tepat waktu.', 92.0],
        [3, 1, 2, 3, 'Realisasi triwulan 3 mencapai 100% dari target.', 'Dokumentasi lengkap.', 89.0],
        [4, 1, 2, 4, 'Realisasi triwulan 4 mencapai 100% dari target.', 'Capaian tahunan sangat baik.', 94.0],
        [5, 3, 2, 5, 'Realisasi triwulan 1 mencapai 100% dari target.', 'Capaian baik, lanjutkan.', 88.0],
        [6, 3, 2, 6, 'Realisasi triwulan 2 mencapai 100% dari target.', 'Perlu peningkatan dokumentasi.', 86.0],
    ];
    foreach ($nilaiRows as $n) {
        upsert(
            $pdo,
            'penilaian',
            ['id', 'skp_id', 'penilai_id', 'rencana_aksi_id', 'realisasi', 'feedback', 'skor'],
            $n,
            ['skp_id', 'penilai_id', 'rencana_aksi_id', 'realisasi', 'feedback', 'skor']
        );
    }

    $evidenceFiles = [];
    foreach ($gurus as $uid => $g) {
        $evidenceFiles[$uid] = ['demo_eviden_' . $uid . '_1.png', 'demo_eviden_' . $uid . '_2.png'];
    }

    $epId = 1;
    foreach ([3 => [1, 2, 3, 4], 4 => [5, 6, 7, 8]] as $uid => $itemIds) {
        foreach ($itemIds as $k => $itemId) {
            $file = $evidenceFiles[$uid][$k % 2];
            upsert(
                $pdo,
                'eviden_penilaian',
                ['id', 'rencana_aksi_item_id', 'file_path', 'original_name'],
                [$epId, $itemId, $file, $file],
                ['rencana_aksi_item_id', 'file_path', 'original_name']
            );
            $epId++;
        }
    }

    $keberatanRows = [
        [1, 1, 3, 'Nilai pada indikator kualitas dirasa belum mempertimbangkan bukti dukung tambahan.', 'diproses', null],
        [2, 5, 4, 'Mohon peninjauan kembali atas capaian triwulan 2 yang telah dilengkapi eviden.', 'ditanggapi', 'Telah ditinjau dan nilai disesuaikan berdasarkan eviden tambahan.'],
    ];
    foreach ($keberatanRows as $k) {
        upsert(
            $pdo,
            'rekap_keberatan',
            ['id', 'penilaian_id', 'user_id', 'alasan_keberatan', 'status', 'tanggapan'],
            $k,
            ['penilaian_id', 'user_id', 'alasan_keberatan', 'status', 'tanggapan']
        );
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Seeder gagal: ' . $e->getMessage() . "\n");
    exit(1);
}

$counts = [
    'users' => 'users',
    'skp' => 'skp',
    'skp_rhk' => 'skp_rhk',
    'skp_rhk_indikator' => 'skp_rhk_indikator',
    'eviden_dukung' => 'eviden_dukung',
    'penilaian_realisasi' => 'penilaian_realisasi',
    'tim_kerja' => 'tim_kerja',
    'anggota_tim_kerja' => 'anggota_tim_kerja',
    'persetujuan_pak' => 'persetujuan_pak',
    'angka_kredit' => 'angka_kredit',
    'rencana_aksi' => 'rencana_aksi',
    'rencana_aksi_item' => 'rencana_aksi_item',
    'penilaian' => 'penilaian',
    'eviden_penilaian' => 'eviden_penilaian',
    'rekap_keberatan' => 'rekap_keberatan',
];

echo "Seed data demo berhasil.\n";
foreach ($counts as $label => $table) {
    $n = (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    printf("  %-22s %d\n", $label, $n);
}
