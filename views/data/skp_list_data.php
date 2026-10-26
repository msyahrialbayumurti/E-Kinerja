<?php
/**
 * views/data/skp_list_data.php - Query & ringkasan data daftar SKP.
 * Di-include oleh views/skp_list.php setelah session dan $pdo tersedia.
 */

$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? 'guru_pegawai';
$csrf_token = generate_csrf_token();

$filter_user_id = isset($_GET['pegawai_id']) ? intval($_GET['pegawai_id']) : 0;
$filter_tahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : 0;
if ($filter_tahun > 0 && !in_array($filter_tahun, $tahun_options ?? [], true)) {
    $filter_tahun = 0;
}
$filter_tahun_param = $filter_tahun > 0 ? '&tahun=' . $filter_tahun : '';

$list_pegawai = [];
if ($role === 'admin' || $role === 'ketua_yayasan') {
    $stmtP = $pdo->query("SELECT id, nip, nama, jabatan FROM users WHERE role = 'guru_pegawai' ORDER BY nama ASC");
    $list_pegawai = $stmtP->fetchAll();
}

$skps = [];
try {
    $sql = "
        SELECT s.*, u.nama, u.nip, u.jabatan, u.pangkat_golongan
        FROM skp s
        JOIN users u ON s.user_id = u.id
    ";
    $where = [];
    $params = [];

    if ($role === 'admin') {
        if ($filter_user_id > 0) {
            $where[] = "s.user_id = ?";
            $params[] = $filter_user_id;
        }
    } elseif ($role === 'ketua_yayasan') {
        $where[] = "u.role = 'guru_pegawai'";
        if ($filter_user_id > 0) {
            $where[] = "s.user_id = ?";
            $params[] = $filter_user_id;
        }
    } else {
        $where[] = "s.user_id = ?";
        $params[] = $user_id;
    }

    if ($filter_tahun > 0) {
        $where[] = "s.tahun_anggaran = ?";
        $params[] = $filter_tahun;
    }

    if ($where) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    $sql .= " ORDER BY s.tahun_anggaran DESC" . ($role === 'guru_pegawai' ? '' : ', u.nama ASC');

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $skps = $stmt->fetchAll();
} catch (PDOException $e) {
    $skps = [];
}

$status_badge = [
    'draft' => ['bg' => 'bg-slate-100 text-slate-700 border-slate-300', 'label' => 'Draft'],
    'persetujuan' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-300', 'label' => 'Menunggu Persetujuan'],
    'aktif' => ['bg' => 'bg-blue-100 text-blue-800 border-blue-300', 'label' => 'Aktif'],
    'selesai' => ['bg' => 'bg-green-100 text-green-800 border-green-300', 'label' => 'Selesai'],
    'dinilai' => ['bg' => 'bg-purple-100 text-purple-800 border-purple-300', 'label' => 'Dinilai']
];

$count_total = count($skps);
$count_aktif = count(array_filter($skps, static fn($s) => ($s['status'] ?? '') === 'aktif'));
$count_menunggu = count(array_filter($skps, static fn($s) => in_array($s['status'] ?? '', ['draft', 'persetujuan'], true)));
$count_selesai = count(array_filter($skps, static fn($s) => in_array($s['status'] ?? '', ['selesai', 'dinilai'], true)));
