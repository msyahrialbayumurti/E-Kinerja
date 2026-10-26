<?php
$role = $_SESSION['role'] ?? 'guru_pegawai';
$user_id = (int) ($_SESSION['user_id'] ?? 0);

$list_pegawai = [];
$selected_pegawai_id = $user_id;

if ($role === 'admin' || $role === 'ketua_yayasan') {
    $list_pegawai = $pdo->query("
        SELECT id, nama
        FROM users
        WHERE role = 'guru_pegawai'
        ORDER BY nama ASC
    ")->fetchAll();

    $allowed_pegawai_ids = array_map('intval', array_column($list_pegawai, 'id'));
    $requested_pegawai_id = isset($_GET['pegawai_id']) ? intval($_GET['pegawai_id']) : 0;
    $selected_pegawai_id = in_array($requested_pegawai_id, $allowed_pegawai_ids, true)
        ? $requested_pegawai_id
        : 0;
}

$filter_tahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : 0;
if ($filter_tahun > 0 && !in_array($filter_tahun, $tahun_options ?? [], true)) {
    $filter_tahun = 0;
}

$sql = "
    SELECT ak.*, u.nama, u.nip
    FROM angka_kredit ak
    JOIN users u ON ak.user_id = u.id
";
$where = [];
$params = [];

if ($selected_pegawai_id > 0) {
    $where[] = "ak.user_id = ?";
    $params[] = $selected_pegawai_id;
}
if ($filter_tahun > 0) {
    $where[] = "ak.tahun = ?";
    $params[] = $filter_tahun;
}
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY ak.tahun DESC, u.nama ASC, ak.id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$total = 0;
foreach ($rows as $row) {
    $total += (float) ($row['kredit_diperoleh'] ?? 0);
}

$show_pegawai = in_array($role, ['admin', 'ketua_yayasan'], true);
$status_badges = [
    'diakui' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
    'ditolak' => 'bg-red-100 text-red-800 border-red-200',
];
?>

<div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Angka Kredit</h1>
        <p class="text-sm text-slate-500 mt-1">Konversi dan capaian Angka Kredit fungsional berdasarkan SKP</p>
    </div>

    <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto">
        <?php if ($role === 'admin' || $role === 'ketua_yayasan'): ?>
            <form method="GET" class="flex w-full flex-col items-stretch gap-2 rounded-sm border border-slate-200 bg-white px-3 py-2 shadow-sm sm:w-auto sm:flex-row sm:items-center">
                <input type="hidden" name="page" value="angka_kredit">
                <?php if ($filter_tahun > 0): ?>
                    <input type="hidden" name="tahun" value="<?= (int) $filter_tahun ?>">
                <?php endif; ?>
                <label for="pegawaiIdAk" class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Pegawai</label>
                <select id="pegawaiIdAk" name="pegawai_id" onchange="this.form.submit()" class="w-full rounded-sm border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700 sm:w-auto">
                    <option value="0" <?= $selected_pegawai_id === 0 ? 'selected' : '' ?>>Semua Pegawai</option>
                    <?php foreach ($list_pegawai as $pegawai): ?>
                        <option value="<?= (int) $pegawai['id'] ?>" <?= $selected_pegawai_id === (int) $pegawai['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pegawai['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>

        <button type="button" onclick="window.print()" class="no-print mobile-action inline-flex items-center gap-2 rounded-sm border border-blue-900 bg-[#1e3a8a] px-3 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-800 sm:w-auto">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2z"/></svg>
            Cetak Halaman
        </button>
    </div>
</div>

<div class="bg-blue-50 border border-blue-200 rounded-sm p-4 mb-6 flex gap-4 text-sm text-blue-800 shadow-sm">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <div>
        <strong>Informasi:</strong> Data Angka Kredit ditampilkan langsung dari basis data kepegawaian. Gunakan tombol Cetak Halaman untuk menyimpan halaman ini sebagai PDF.
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-sm shadow-sm overflow-hidden">
    <div class="table-scroll overflow-x-auto">
        <table class="w-full min-w-[760px] text-left border-collapse table-compact print:min-w-0">
            <thead>
                <tr class="table-header border-b border-slate-200">
                    <th class="w-12 text-center border-r border-slate-200">No</th>
                    <?php if ($show_pegawai): ?>
                        <th class="border-r border-slate-200">Pegawai</th>
                    <?php endif; ?>
                    <th class="w-28 border-r border-slate-200">Tahun</th>
                    <th class="border-r border-slate-200">Kegiatan</th>
                    <th class="w-28 text-center border-r border-slate-200">Angka Kredit</th>
                    <th class="w-28 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="text-slate-700 bg-white text-sm">
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="<?= $show_pegawai ? 6 : 5 ?>" class="p-8 text-center text-slate-500">
                            Belum ada data Angka Kredit untuk filter ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $index => $row): ?>
                        <?php $badge = $status_badges[$row['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200'; ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                            <td class="text-center border-r border-slate-200"><?= $index + 1 ?></td>
                            <?php if ($show_pegawai): ?>
                                <td class="border-r border-slate-200">
                                    <div class="font-bold text-slate-800"><?= htmlspecialchars($row['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($row['nip'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                            <?php endif; ?>
                            <td class="border-r border-slate-200 font-bold"><?= htmlspecialchars((string) ($row['tahun'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="border-r border-slate-200"><?= htmlspecialchars($row['kegiatan'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="border-r border-slate-200 text-center font-mono font-bold text-slate-900 bg-slate-50">
                                <?= number_format((float) ($row['kredit_diperoleh'] ?? 0), 2, '.', ',') ?>
                            </td>
                            <td class="text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-bold border <?= $badge ?>">
                                    <?= htmlspecialchars(ucfirst($row['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot class="bg-slate-100 border-t-2 border-slate-300">
                <tr>
                    <td colspan="<?= $show_pegawai ? 4 : 3 ?>" class="text-right py-3 pr-4 font-bold text-slate-700">TOTAL ANGKA KREDIT KUMULATIF</td>
                    <td class="text-center font-mono font-bold text-lg text-slate-900"><?= number_format($total, 2, '.', ',') ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
