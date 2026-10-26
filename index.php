<?php
require_once 'config/database.php';
require_once 'config/security.php';

// Check authentication
require_login();

// Whitelist pages untuk security
$allowed_pages = [
    'profil',
    'skp_list',
    'rencana_aksi',
    'penilaian_eviden',
    'users_manage',
    'buku_panduan',
    'tim_kerja',
    'angka_kredit',
    'persetujuan_pak',
    'rekap_keberatan'
];

$page = $_GET['page'] ?? 'profil';
$page = in_array($page, $allowed_pages) ? $page : 'profil';

$page_roles = [
    'users_manage' => ['admin'],
    'tim_kerja' => ['admin', 'ketua_yayasan'],
    'persetujuan_pak' => ['admin', 'ketua_yayasan'],
    'rekap_keberatan' => ['admin', 'ketua_yayasan'],
    'rencana_aksi' => ['admin', 'ketua_yayasan', 'guru_pegawai'],
    'angka_kredit' => ['admin', 'ketua_yayasan', 'guru_pegawai'],
];

$current_role = $_SESSION['role'] ?? 'guru_pegawai';
if (isset($page_roles[$page]) && !in_array($current_role, $page_roles[$page], true)) {
    $page = 'profil';
}

require_once 'layout/header.php';
require_once 'layout/sidebar.php';

switch ($page) {
    case 'profil':
        require_once 'views/profil.php';
        break;
    case 'users_manage':
        require_once 'views/users_manage.php';
        break;
    case 'skp_list':
        require_once 'views/skp_list.php';
        break;
    case 'rencana_aksi':
        require_once 'views/rencana_aksi.php';
        break;
    case 'penilaian_eviden':
        require_once 'views/penilaian_eviden.php';
        break;
    case 'buku_panduan':
        require_once 'views/buku_panduan.php';
        break;

    case 'tim_kerja':
        require_once 'views/tim_kerja.php';
        break;
    case 'angka_kredit':
        require_once 'views/angka_kredit.php';
        break;
    case 'persetujuan_pak':
        require_once 'views/persetujuan_pak.php';
        break;
    case 'rekap_keberatan':
        require_once 'views/rekap_keberatan.php';
        break;
    default:
        require_once 'views/profil.php';
        break;
}

require_once 'layout/footer.php';
