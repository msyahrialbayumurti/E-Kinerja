<?php
/**
 * config/navigation.php - Konfigurasi menu sidebar per role.
 * Dipisahkan dari layout agar mudah dirawat dan diuji.
 */

if (!function_exists('navigation_role_labels')) {
    function navigation_role_labels(): array
    {
        return [
            'admin' => 'Administrator',
            'ketua_yayasan' => 'Ketua Yayasan',
            'guru_pegawai' => 'Guru / Pegawai',
        ];
    }
}

if (!function_exists('navigation_items')) {
    function navigation_items(string $role): array
    {
        $menus = [
            'admin' => [
                ['label' => 'Buku Panduan', 'page' => 'buku_panduan', 'icon' => 'book'],
                ['label' => 'Profil', 'page' => 'profil', 'icon' => 'user'],
                ['label' => 'Kelola Pengguna', 'page' => 'users_manage', 'icon' => 'users'],
                ['label' => 'Master SKP', 'page' => 'skp_list', 'icon' => 'clipboard'],
                ['label' => 'Matriks Penilaian & Eviden', 'page' => 'penilaian_eviden', 'icon' => 'dashboard'],
                ['label' => 'Rencana Aksi', 'page' => 'rencana_aksi', 'icon' => 'clipboard'],
                ['label' => 'Angka Kredit', 'page' => 'angka_kredit', 'icon' => 'dashboard'],
                ['label' => 'Tim Kerja', 'page' => 'tim_kerja', 'icon' => 'users'],
                ['label' => 'Persetujuan PAK', 'page' => 'persetujuan_pak', 'icon' => 'clipboard'],
                ['label' => 'Rekap Keberatan', 'page' => 'rekap_keberatan', 'icon' => 'book'],
            ],
            'ketua_yayasan' => [
                ['label' => 'Buku Panduan', 'page' => 'buku_panduan', 'icon' => 'book'],
                ['label' => 'Profil', 'page' => 'profil', 'icon' => 'user'],
                ['label' => 'Monitoring SKP', 'page' => 'skp_list', 'icon' => 'clipboard'],
                ['label' => 'Penilaian & Feedback', 'page' => 'penilaian_eviden', 'icon' => 'dashboard'],
                ['label' => 'Rencana Aksi', 'page' => 'rencana_aksi', 'icon' => 'clipboard'],
                ['label' => 'Angka Kredit', 'page' => 'angka_kredit', 'icon' => 'dashboard'],
                ['label' => 'Tim Kerja', 'page' => 'tim_kerja', 'icon' => 'users'],
                ['label' => 'Persetujuan PAK', 'page' => 'persetujuan_pak', 'icon' => 'clipboard'],
                ['label' => 'Rekap Keberatan', 'page' => 'rekap_keberatan', 'icon' => 'book'],
            ],
            'guru_pegawai' => [
                ['label' => 'Buku Panduan', 'page' => 'buku_panduan', 'icon' => 'book'],
                ['label' => 'Profil Pegawai', 'page' => 'profil', 'icon' => 'user'],
                ['label' => 'SKP Saya', 'page' => 'skp_list', 'icon' => 'clipboard'],
                ['label' => 'Matriks Penilaian & Eviden', 'page' => 'penilaian_eviden', 'icon' => 'dashboard'],
                ['label' => 'Rencana Aksi', 'page' => 'rencana_aksi', 'icon' => 'clipboard'],
                ['label' => 'Angka Kredit', 'page' => 'angka_kredit', 'icon' => 'dashboard'],
            ],
        ];

        return $menus[$role] ?? $menus['guru_pegawai'];
    }
}
