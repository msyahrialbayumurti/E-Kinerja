<?php
/**
 * config/panduan_data.php
 * Sumber tunggal isi Buku Panduan E-Kinerja.
 *
 * Bahasa sengaja dibuat sederhana, singkat, dan berurutan agar mudah
 * diikuti oleh pengguna dari berbagai usia. Setiap langkah berisi judul
 * dan butir-butir petunjuk yang dapat ditampilkan di layar maupun PDF.
 */

return [
    'guru_pegawai' => [
        'label' => 'Guru / Pegawai',
        'title' => 'Panduan Penggunaan untuk Guru / Pegawai',
        'slug' => 'guru-pegawai',
        'icon' => 'user',
        'accent' => 'blue',
        'summary' => 'Panduan sederhana untuk melihat target kerja, mengunggah bukti dukung, dan memantau penilaian pimpinan.',
        'intro' => 'Panduan ini dibuat agar mudah diikuti. Bapak/Ibu cukup mengerjakan satu langkah setiap kali. Tidak perlu terburu-buru. Jika ada yang belum jelas, bertanyalah kepada Admin Kepegawaian atau pimpinan.',
        'steps' => [
            [
                'title' => 'Masuk ke aplikasi',
                'items' => [
                    'Buka alamat E-Kinerja melalui browser di komputer atau ponsel.',
                    'Isi username atau NIP, lalu isi password.',
                    'Klik tombol Masuk.',
                    'Jika lupa password, hubungi Admin Kepegawaian. Jangan meminjam akun orang lain.',
                ],
            ],
            [
                'title' => 'Periksa data pribadi',
                'items' => [
                    'Buka menu Profil Pegawai.',
                    'Periksa nama, NIP, jabatan, unit kerja, dan nama atasan penilai.',
                    'Jika ada data yang salah atau belum lengkap, hubungi Admin.',
                    'Jangan mengubah data kepegawaian sendiri.',
                ],
            ],
            [
                'title' => 'Lihat target kerja tahun ini',
                'items' => [
                    'Buka menu SKP Saya.',
                    'Pilih tahun anggaran yang benar.',
                    'Baca target kerja, ukuran keberhasilan, dan batas waktunya.',
                    'Catat target tersebut sebagai acuan bekerja sepanjang tahun.',
                ],
            ],
            [
                'title' => 'Isi rencana aksi',
                'items' => [
                    'Buka menu Rencana Aksi.',
                    'Pilih triwulan yang akan direncanakan.',
                    'Klik tombol Tambah Rencana Aksi.',
                    'Tulis kegiatan yang akan dilakukan dengan kalimat singkat dan jelas.',
                    'Klik Simpan. Ulangi untuk triwulan berikutnya.',
                ],
            ],
            [
                'title' => 'Unggah bukti dukung',
                'items' => [
                    'Buka menu Matriks Penilaian & Eviden.',
                    'Cari baris pekerjaan yang sudah selesai.',
                    'Klik tombol Tambah Bukti Dukung.',
                    'Pilih file PDF, JPG, atau PNG. Ukuran file paling besar 5 MB.',
                    'Beri nama yang mudah dikenali, misalnya "Laporan Kegiatan Januari".',
                    'Klik Simpan. Bukti akan terlihat oleh pimpinan.',
                ],
            ],
            [
                'title' => 'Lihat penilaian pimpinan',
                'items' => [
                    'Buka kembali Matriks Penilaian & Eviden.',
                    'Lihat kolom Realisasi dan Feedback.',
                    'Baca catatan pimpinan dengan tenang.',
                    'Jika ada nilai atau catatan yang belum jelas, tanyakan langsung kepada pimpinan.',
                ],
            ],
        ],
        'tips' => [
            'Simpan file bukti di komputer atau ponsel sebelum mengunggah.',
            'Unggah bukti sedikit-sedikit setiap bulan. Jangan menunggu akhir tahun.',
            'Gunakan nama file yang jelas agar pimpinan mudah memeriksa.',
            'Jangan mengunggah data pribadi yang sensitif, seperti KTP atau nomor rekening.',
            'Selalu klik Keluar setelah selesai, terutama jika memakai komputer bersama.',
        ],
        'terms' => [
            'SKP' => 'Daftar target kerja tahunan yang harus dicapai.',
            'RHK' => 'Hasil kerja utama yang menjadi tanggung jawab pegawai.',
            'Rencana Aksi' => 'Kegiatan yang akan dilakukan untuk mencapai target.',
            'Eviden' => 'Bukti dukung, seperti surat, foto, laporan, atau tautan.',
            'Realisasi' => 'Hasil kerja yang benar-benar sudah dicapai.',
            'Feedback' => 'Umpan balik atau catatan dari pimpinan.',
            'PAK' => 'Penetapan Angka Kredit untuk kenaikan pangkat.',
        ],
        'symbols' => [
            ['key' => 'jempol', 'label' => 'Jempol', 'meaning' => 'Target sudah terpenuhi dengan baik.', 'accent' => 'amber'],
            ['key' => 'bintang', 'label' => 'Bintang', 'meaning' => 'Hasil kerja sangat baik, bahkan melebihi target.', 'accent' => 'blue'],
            ['key' => 'apresiasi', 'label' => 'Apresiasi', 'meaning' => 'Pekerjaan selesai dengan rapi dan tuntas.', 'accent' => 'emerald'],
            ['key' => 'catatan', 'label' => 'Catatan', 'meaning' => 'Masih perlu perbaikan atau perhatian khusus.', 'accent' => 'orange'],
        ],
        'help' => [
            'Hubungi Admin Kepegawaian jika akun tidak bisa dipakai.',
            'Sebutkan nama, menu yang dibuka, dan pesan kesalahan yang muncul.',
            'Jangan pernah membagikan password kepada siapa pun.',
        ],
    ],

    'ketua_yayasan' => [
        'label' => 'Ketua Yayasan',
        'title' => 'Panduan Penggunaan untuk Ketua Yayasan',
        'slug' => 'ketua-yayasan',
        'icon' => 'check',
        'accent' => 'purple',
        'summary' => 'Panduan sederhana untuk menilai kinerja pegawai, memberi umpan balik, dan memverifikasi angka kredit.',
        'intro' => 'Panduan ini membantu Bapak/Ibu menilai hasil kerja pegawai dengan adil. Periksa bukti dukung dengan teliti, lalu berikan catatan yang sopan dan mudah dipahami.',
        'steps' => [
            [
                'title' => 'Masuk dan periksa profil',
                'items' => [
                    'Masuk menggunakan akun Ketua Yayasan.',
                    'Buka menu Profil untuk memastikan nama dan jabatan sudah benar.',
                    'Jika ada data yang salah, hubungi Admin.',
                ],
            ],
            [
                'title' => 'Lihat tim kerja',
                'items' => [
                    'Buka menu Tim Kerja.',
                    'Lihat daftar pegawai yang menjadi tanggung jawab Bapak/Ibu.',
                    'Perhatikan siapa yang sudah dinilai dan siapa yang belum.',
                ],
            ],
            [
                'title' => 'Buka halaman penilaian',
                'items' => [
                    'Buka menu Penilaian & Feedback atau Matriks Penilaian & Eviden.',
                    'Pilih nama pegawai dan tahun anggaran yang akan dinilai.',
                    'Pastikan pegawai dan tahun yang dipilih sudah benar.',
                ],
            ],
            [
                'title' => 'Periksa target dan bukti dukung',
                'items' => [
                    'Baca RHK Pimpinan, RHK Individu, dan Rencana Aksi pegawai.',
                    'Periksa aspek Kuantitas, Kualitas, dan Waktu beserta targetnya.',
                    'Buka setiap bukti dukung yang diunggah pegawai.',
                    'Jika bukti kurang jelas, mintalah pegawai melengkapinya.',
                ],
            ],
            [
                'title' => 'Isi kolom realisasi',
                'items' => [
                    'Tulis hasil kerja yang benar-benar dicapai pegawai.',
                    'Jelaskan jumlah pekerjaan, mutu hasil, dan ketepatan waktu.',
                    'Gunakan kalimat singkat agar mudah dipahami.',
                    'Klik Simpan setelah selesai mengisi.',
                ],
            ],
            [
                'title' => 'Berikan umpan balik',
                'items' => [
                    'Pilih simbol yang sesuai dengan hasil kerja pegawai.',
                    'Tulis catatan yang menjelaskan alasan pilihan tersebut.',
                    'Sampaikan pujian atau saran perbaikan dengan bahasa yang sopan.',
                    'Klik Simpan. Pegawai akan melihat catatan tersebut.',
                ],
            ],
            [
                'title' => 'Setujui angka kredit dan tanggapi keberatan',
                'items' => [
                    'Buka menu Persetujuan PAK untuk memilih Disetujui, Revisi, atau Ditolak.',
                    'Tulis catatan jika memilih Revisi atau Ditolak.',
                    'Buka menu Rekap Keberatan untuk menanggapi keberatan pegawai.',
                    'Buka menu Angka Kredit untuk memantau hasil akhir.',
                ],
            ],
        ],
        'tips' => [
            'Periksa bukti dukung sebelum memberi nilai.',
            'Catatan wajib diisi jika memilih Revisi atau Ditolak.',
            'Sampaikan alasan dengan bahasa yang sopan dan mudah dipahami.',
            'Jangan mengubah target yang sudah disepakati tanpa berdiskusi dengan Admin.',
            'Selesaikan penilaian tepat waktu agar pegawai tidak menunggu.',
        ],
        'terms' => [
            'SKP' => 'Daftar target kerja tahunan yang harus dicapai.',
            'RHK' => 'Hasil kerja utama yang menjadi tanggung jawab pegawai.',
            'Eviden' => 'Bukti dukung, seperti surat, foto, laporan, atau tautan.',
            'Realisasi' => 'Hasil kerja yang benar-benar sudah dicapai.',
            'Feedback' => 'Umpan balik atau catatan dari pimpinan.',
            'PAK' => 'Penetapan Angka Kredit untuk kenaikan pangkat.',
        ],
        'symbols' => [
            ['key' => 'jempol', 'label' => 'Jempol', 'meaning' => 'Target sudah terpenuhi dengan baik.', 'accent' => 'amber'],
            ['key' => 'bintang', 'label' => 'Bintang', 'meaning' => 'Hasil kerja sangat baik, bahkan melebihi target.', 'accent' => 'blue'],
            ['key' => 'apresiasi', 'label' => 'Apresiasi', 'meaning' => 'Pekerjaan selesai dengan rapi dan tuntas.', 'accent' => 'emerald'],
            ['key' => 'catatan', 'label' => 'Catatan', 'meaning' => 'Masih perlu perbaikan atau perhatian khusus.', 'accent' => 'orange'],
        ],
        'help' => [
            'Hubungi Admin Kepegawaian jika ada data pegawai yang tidak muncul.',
            'Sebutkan nama pegawai, tahun anggaran, dan menu yang dibuka.',
            'Jangan membagikan akun pimpinan kepada orang lain.',
        ],
    ],

    'admin' => [
        'label' => 'Administrator',
        'title' => 'Panduan Penggunaan untuk Administrator',
        'slug' => 'administrator',
        'icon' => 'settings',
        'accent' => 'red',
        'summary' => 'Panduan singkat mengelola akun, target kerja, tim kerja, dan data penilaian.',
        'intro' => 'Panduan ini membantu Admin menjaga data tetap rapi dan aman. Periksa data sebelum menekan tombol Simpan atau Hapus. Lakukan pencadangan data secara berkala.',
        'steps' => [
            [
                'title' => 'Masuk dan periksa profil',
                'items' => [
                    'Masuk menggunakan akun Administrator.',
                    'Buka menu Profil untuk memastikan nama dan data sudah benar.',
                    'Jangan membagikan password admin kepada siapa pun.',
                ],
            ],
            [
                'title' => 'Kelola akun pengguna',
                'items' => [
                    'Buka menu Kelola Pengguna.',
                    'Klik Tambah Pengguna untuk membuat akun baru.',
                    'Isi nama, NIP, username, password, peran, dan atasan penilai.',
                    'Periksa kembali data sebelum menyimpan.',
                    'Gunakan tombol Edit atau Hapus dengan hati-hati.',
                ],
            ],
            [
                'title' => 'Buat dan atur SKP',
                'items' => [
                    'Buka menu Master SKP.',
                    'Klik Tambah SKP Baru.',
                    'Pilih pegawai, tahun anggaran, dan periode penilaian.',
                    'Simpan, lalu buka detail SKP untuk menambah RHK.',
                ],
            ],
            [
                'title' => 'Isi RHK, aspek, dan target',
                'items' => [
                    'Buka Matriks Penilaian & Eviden.',
                    'Pilih pegawai dan SKP yang akan diisi.',
                    'Klik Tambah RHK, lalu isi RHK Pimpinan, RHK Individu, dan Rencana Aksi.',
                    'Tambahkan indikator untuk aspek Kuantitas, Kualitas, dan Waktu.',
                    'Isi target dengan angka atau ukuran yang jelas.',
                ],
            ],
            [
                'title' => 'Atur tim kerja dan pantau penilaian',
                'items' => [
                    'Buka menu Tim Kerja untuk mengatur ketua dan anggota tim.',
                    'Buka Matriks Penilaian & Eviden untuk melihat kemajuan.',
                    'Periksa apakah pegawai sudah mengunggah bukti dukung.',
                    'Periksa apakah pimpinan sudah mengisi realisasi dan feedback.',
                    'Ingatkan tim yang belum menyelesaikan kewajibannya.',
                ],
            ],
            [
                'title' => 'Verifikasi PAK dan keberatan',
                'items' => [
                    'Buka menu Persetujuan PAK untuk memeriksa usulan angka kredit.',
                    'Buka menu Rekap Keberatan untuk melihat keberatan pegawai.',
                    'Buka menu Angka Kredit untuk memeriksa rekap akhir.',
                    'Simpan catatan penting untuk keperluan audit.',
                ],
            ],
        ],
        'tips' => [
            'Periksa data sebelum menekan tombol Simpan atau Hapus.',
            'Gunakan bahasa yang singkat dan jelas saat mengisi catatan.',
            'Minta konfirmasi pimpinan sebelum mengubah target yang sudah disepakati.',
            'Lakukan pencadangan (backup) database secara berkala.',
            'Jangan membuat akun ganda untuk pegawai yang sama.',
        ],
        'terms' => [
            'SKP' => 'Daftar target kerja tahunan.',
            'RHK' => 'Hasil kerja utama yang harus dicapai.',
            'Rencana Aksi' => 'Kegiatan untuk mencapai target kerja.',
            'Eviden' => 'Bukti dukung, seperti surat, foto, atau laporan.',
            'Realisasi' => 'Hasil kerja yang benar-benar dicapai.',
            'Feedback' => 'Umpan balik atau catatan dari pimpinan.',
            'PAK' => 'Penetapan Angka Kredit.',
        ],
        'symbols' => [],
        'help' => [
            'Hubungi pengembang sistem jika muncul pesan kesalahan yang tidak dikenal.',
            'Sebutkan menu yang dibuka, langkah yang dilakukan, dan pesan kesalahan.',
            'Simpan salinan database sebelum melakukan perubahan besar.',
        ],
    ],
];
