<?php
/**
 * views/data/penilaian_eviden_data.php
 * Pengambilan data untuk Matriks Penilaian & Eviden.
 * Dipisahkan dari tampilan agar file view tetap ringkas.
 */

$role = $_SESSION['role'] ?? 'guru_pegawai';
$user_id = $_SESSION['user_id'] ?? 0;
$csrf_token = generate_csrf_token();

// 1. Pilih pegawai (Admin & Ketua Yayasan)
$list_pegawai = [];
$selected_pegawai_id = $user_id;

if ($role === 'admin' || $role === 'ketua_yayasan') {
    $stmtPegawai = $pdo->query("
        SELECT id, nip, nama, jabatan
        FROM users
        WHERE role = 'guru_pegawai'
        ORDER BY nama ASC
    ");
    $list_pegawai = $stmtPegawai->fetchAll();

    $allowed_pegawai_ids = array_map('intval', array_column($list_pegawai, 'id'));
    $requested_pegawai_id = isset($_GET['pegawai_id']) ? intval($_GET['pegawai_id']) : 0;

    $selected_pegawai_id = in_array($requested_pegawai_id, $allowed_pegawai_ids, true)
        ? $requested_pegawai_id
        : ($list_pegawai[0]['id'] ?? $user_id);
}

// 2. Periode SKP pegawai terpilih
$stmtSkpList = $pdo->prepare("
    SELECT id, tahun_anggaran, periode_mulai, periode_selesai, status, pendekatan
    FROM skp
    WHERE user_id = ?
    ORDER BY tahun_anggaran DESC
");
$stmtSkpList->execute([$selected_pegawai_id]);
$pegawai_skps = $stmtSkpList->fetchAll();

$filter_tahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : 0;
if ($filter_tahun > 0 && !in_array($filter_tahun, $tahun_options ?? [], true)) {
    $filter_tahun = 0;
}
if ($filter_tahun > 0) {
    $filtered_skps = array_values(array_filter($pegawai_skps, static fn($skp) => (int) $skp['tahun_anggaran'] === $filter_tahun));
    if (!empty($filtered_skps)) {
        $pegawai_skps = $filtered_skps;
    }
}

$current_skp_id = isset($_GET['skp_id']) ? intval($_GET['skp_id']) : ($pegawai_skps[0]['id'] ?? 0);

$allowed_skp_ids = array_map('intval', array_column($pegawai_skps, 'id'));
if ($current_skp_id > 0 && !in_array($current_skp_id, $allowed_skp_ids, true)) {
    $current_skp_id = $pegawai_skps[0]['id'] ?? 0;
}

// 3. Detail SKP & pegawai
$active_skp = null;
if ($current_skp_id > 0) {
    $stmtActive = $pdo->prepare("
        SELECT s.*, u.nama, u.nip, u.jabatan, u.pangkat_golongan
        FROM skp s
        JOIN users u ON s.user_id = u.id
        WHERE s.id = ?
    ");
    $stmtActive->execute([$current_skp_id]);
    $active_skp = $stmtActive->fetch();
}

// 4. RHK, indikator, eviden, dan penilaian
$rhk_list = [];
if ($current_skp_id > 0) {
    $stmtRhk = $pdo->prepare("SELECT * FROM skp_rhk WHERE skp_id = ? ORDER BY urutan ASC, id ASC");
    $stmtRhk->execute([$current_skp_id]);
    $raw_rhks = $stmtRhk->fetchAll();

    foreach ($raw_rhks as $r) {
        $rhk_id = $r['id'];

        $stmtInd = $pdo->prepare("
            SELECT * FROM skp_rhk_indikator
            WHERE rhk_id = ?
            ORDER BY FIELD(aspek, 'KUANTITAS', 'KUALITAS', 'WAKTU'), id ASC
        ");
        $stmtInd->execute([$rhk_id]);
        $indikators = $stmtInd->fetchAll();

        foreach ($indikators as &$ind) {
            $stmtEvd = $pdo->prepare("
                SELECT e.*, u.nama as uploader_nama
                FROM eviden_dukung e
                LEFT JOIN users u ON e.user_id = u.id
                WHERE e.indikator_id = ?
                ORDER BY e.created_at ASC
            ");
            $stmtEvd->execute([$ind['id']]);
            $ind['eviden'] = $stmtEvd->fetchAll();
        }
        unset($ind);

        $stmtPen = $pdo->prepare("
            SELECT pr.*, u.nama as penilai_nama, u.jabatan as penilai_jabatan
            FROM penilaian_realisasi pr
            LEFT JOIN users u ON pr.penilai_id = u.id
            WHERE pr.rhk_id = ?
        ");
        $stmtPen->execute([$rhk_id]);
        $penilaian = $stmtPen->fetch();

        $r['indikators'] = $indikators;
        $r['penilaian'] = $penilaian;
        $rhk_list[] = $r;
    }
}

// Ringkasan metrik matriks
$total_rhk = count($rhk_list);
$total_indikator = 0;
$total_eviden = 0;
$total_dinilai = 0;
foreach ($rhk_list as $r) {
    $total_indikator += count($r['indikators']);
    foreach ($r['indikators'] as $ind) {
        $total_eviden += count($ind['eviden']);
    }
    if (!empty($r['penilaian'])) {
        $total_dinilai++;
    }
}

// Status badge
$status_badge = [
    'draft' => ['bg' => 'bg-slate-100 text-slate-700 border-slate-300', 'label' => 'Draft'],
    'persetujuan' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-300', 'label' => 'Menunggu Persetujuan'],
    'aktif' => ['bg' => 'bg-blue-100 text-blue-800 border-blue-300', 'label' => 'Aktif'],
    'selesai' => ['bg' => 'bg-green-100 text-green-800 border-green-300', 'label' => 'Selesai'],
    'dinilai' => ['bg' => 'bg-purple-100 text-purple-800 border-purple-300', 'label' => 'Dinilai'],
];
