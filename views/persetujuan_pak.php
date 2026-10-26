<?php
$role = $_SESSION['role'] ?? 'guest';
$can_verify = in_array($role, ['admin', 'ketua_yayasan'], true);
$status_filter = $_GET['status'] ?? '';
$allowed_statuses = ['pending', 'disetujui', 'ditolak', 'revisi'];

if ($status_filter !== '' && !in_array($status_filter, $allowed_statuses, true)) {
    $status_filter = '';
}

$filter_tahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : 0;
if ($filter_tahun > 0 && !in_array($filter_tahun, $tahun_options ?? [], true)) {
    $filter_tahun = 0;
}

$counts = ['pending' => 0, 'disetujui' => 0, 'ditolak' => 0, 'revisi' => 0];
$count_sql = "SELECT p.status, COUNT(*) AS total FROM persetujuan_pak p JOIN skp s ON p.skp_id = s.id";
$count_params = [];
if ($filter_tahun > 0) {
    $count_sql .= " WHERE s.tahun_anggaran = ?";
    $count_params[] = $filter_tahun;
}
$count_sql .= " GROUP BY p.status";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
foreach ($count_stmt->fetchAll() as $count_row) {
    $counts[$count_row['status']] = (int) $count_row['total'];
}
$total_all = array_sum($counts);

$sql = "
    SELECT p.*, s.tahun_anggaran, s.status AS skp_status, u.nama, u.nip,
           (
               SELECT COALESCE(SUM(ak.kredit_diperoleh), 0)
               FROM angka_kredit ak
               WHERE ak.user_id = s.user_id AND ak.tahun = s.tahun_anggaran
           ) AS total_ak,
           ap.nama AS approver_nama
    FROM persetujuan_pak p
    JOIN skp s ON p.skp_id = s.id
    JOIN users u ON s.user_id = u.id
    LEFT JOIN users ap ON p.disetujui_oleh = ap.id
";
$params = [];
$where = [];

if ($status_filter !== '') {
    $where[] = "p.status = ?";
    $params[] = $status_filter;
}
if ($filter_tahun > 0) {
    $where[] = "s.tahun_anggaran = ?";
    $params[] = $filter_tahun;
}
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY FIELD(p.status, 'pending', 'revisi', 'disetujui', 'ditolak'), p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$badges = [
    'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
    'revisi' => 'bg-orange-100 text-orange-800 border-orange-200',
    'disetujui' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'ditolak' => 'bg-red-100 text-red-800 border-red-200',
];

$tabs = [
    '' => ['label' => 'Semua', 'count' => $total_all],
    'pending' => ['label' => 'Pending', 'count' => $counts['pending']],
    'revisi' => ['label' => 'Revisi', 'count' => $counts['revisi']],
    'disetujui' => ['label' => 'Disetujui', 'count' => $counts['disetujui']],
    'ditolak' => ['label' => 'Ditolak', 'count' => $counts['ditolak']],
];

$json_flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
$pak_json = json_encode($rows, $json_flags);
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Persetujuan PAK</h1>
    <p class="text-sm text-slate-500 mt-1">Alur verifikasi dan penetapan Angka Kredit oleh pejabat berwenang</p>
</div>

<div class="border-b border-slate-200 mb-6">
    <nav class="flex flex-wrap gap-x-6 gap-y-2">
        <?php foreach ($tabs as $key => $tab): ?>
            <?php
            $is_active = $status_filter === $key;
            $href = 'index.php?page=persetujuan_pak' . ($key !== '' ? '&amp;status=' . urlencode($key) : '');
            if ($filter_tahun > 0) {
                $href .= '&amp;tahun=' . (int) $filter_tahun;
            }
            ?>
            <a href="<?= $href ?>" class="border-b-2 py-2 px-1 text-sm <?= $is_active ? 'border-blue-700 font-bold text-blue-700' : 'border-transparent font-medium text-slate-500 hover:text-slate-700' ?>">
                <?= htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8') ?> (<?= (int) $tab['count'] ?>)
            </a>
        <?php endforeach; ?>
    </nav>
</div>

<div class="bg-white border border-slate-200 rounded-sm shadow-sm overflow-hidden">
    <div class="table-scroll overflow-x-auto">
        <table class="w-full min-w-[980px] text-left border-collapse table-compact">
            <thead>
                <tr class="table-header border-b border-slate-200">
                    <th class="w-12 text-center border-r border-slate-200">No</th>
                    <th class="border-r border-slate-200">Nama Pegawai</th>
                    <th class="w-24 border-r border-slate-200">Tahun</th>
                    <th class="w-28 text-center border-r border-slate-200">Total AK</th>
                    <th class="w-32 border-r border-slate-200">Status</th>
                    <th class="border-r border-slate-200">Catatan</th>
                    <th class="w-36 border-r border-slate-200">Tanggal</th>
                    <th class="w-36 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-slate-700 bg-white text-sm">
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-500">
                            Tidak ada data persetujuan PAK untuk filter ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $index => $row): ?>
                        <?php
                        $badge = $badges[$row['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                        $row_id = (int) $row['id'];
                        ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                            <td class="text-center border-r border-slate-200"><?= $index + 1 ?></td>
                            <td class="border-r border-slate-200">
                                <div class="font-bold text-slate-800"><?= htmlspecialchars($row['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($row['nip'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td class="border-r border-slate-200 font-bold"><?= htmlspecialchars((string) ($row['tahun_anggaran'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="border-r border-slate-200 text-center font-mono font-bold text-slate-900">
                                <?= number_format((float) ($row['total_ak'] ?? 0), 2, '.', ',') ?>
                            </td>
                            <td class="border-r border-slate-200">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-bold border <?= $badge ?>">
                                    <?= htmlspecialchars(ucfirst($row['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="border-r border-slate-200 text-xs">
                                <?= htmlspecialchars($row['catatan'] ?: '-', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="border-r border-slate-200 text-xs font-mono">
                                <?= $row['tanggal_persetujuan'] ? date('d/m/Y H:i', strtotime($row['tanggal_persetujuan'])) : '-' ?>
                            </td>
                            <td class="p-2 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="togglePakDetail(<?= $row_id ?>)" class="bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 px-2.5 py-1 text-xs font-semibold rounded-sm">
                                        Detail
                                    </button>
                                    <?php if ($can_verify): ?>
                                        <button type="button" onclick="openPakModal(<?= $row_id ?>)" class="bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 px-2.5 py-1 text-xs font-semibold rounded-sm">
                                            Verifikasi
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr id="pakDetail<?= $row_id ?>" class="hidden bg-slate-50 border-b border-slate-200">
                            <td colspan="8" class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                                    <div class="bg-white border border-slate-200 rounded-sm p-2">
                                        <div class="font-bold uppercase tracking-wider text-slate-500 mb-1">Status SKP</div>
                                        <div class="font-semibold text-slate-800"><?= htmlspecialchars(ucfirst($row['skp_status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <div class="bg-white border border-slate-200 rounded-sm p-2">
                                        <div class="font-bold uppercase tracking-wider text-slate-500 mb-1">Diputuskan Oleh</div>
                                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($row['approver_nama'] ?: '-', ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <div class="bg-white border border-slate-200 rounded-sm p-2">
                                        <div class="font-bold uppercase tracking-wider text-slate-500 mb-1">Catatan Verifikasi</div>
                                        <div class="text-slate-700"><?= htmlspecialchars($row['catatan'] ?: 'Belum ada catatan.', ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($can_verify): ?>
    <div id="modalPak" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" onclick="if (event.target === this) closePakModal()">
        <div class="modal-panel max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-sm border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 bg-slate-50">
                <h2 class="text-sm font-bold text-slate-800">Verifikasi Persetujuan PAK</h2>
                <button type="button" onclick="closePakModal()" class="text-slate-500 hover:text-slate-800 text-lg leading-none">&times;</button>
            </div>
            <form id="formPak" onsubmit="submitPakForm(event)" class="p-5 space-y-3 text-xs">
                <input type="hidden" name="id" id="pakId" value="0">

                <div class="bg-slate-50 border border-slate-200 rounded-sm px-3 py-2">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Pegawai</div>
                    <div id="pakNama" class="font-bold text-slate-800">-</div>
                </div>

                <div>
                    <label for="pakStatus" class="block font-bold text-slate-700 mb-1">Keputusan</label>
                    <select name="status" id="pakStatus" onchange="togglePakCatatanRequired()" class="w-full border border-slate-300 rounded-sm px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <option value="disetujui">Disetujui</option>
                        <option value="revisi">Revisi</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <div>
                    <label for="pakCatatan" class="block font-bold text-slate-700 mb-1">Catatan Verifikasi</label>
                    <textarea name="catatan" id="pakCatatan" rows="4" class="w-full border border-slate-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200" placeholder="Wajib diisi untuk keputusan revisi atau ditolak"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                    <button type="button" onclick="closePakModal()" class="px-4 py-2 border border-slate-300 rounded-sm text-slate-700 font-semibold hover:bg-slate-50">Batal</button>
                    <button type="submit" id="pakSubmitBtn" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-sm font-bold">Simpan Keputusan</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
const pakData = <?= $pak_json ?: '[]' ?>;

function togglePakDetail(id) {
    const row = document.getElementById('pakDetail' + id);
    if (row) {
        row.classList.toggle('hidden');
    }
}

function togglePakCatatanRequired() {
    const status = document.getElementById('pakStatus').value;
    document.getElementById('pakCatatan').required = status !== 'disetujui';
}

function openPakModal(id) {
    const row = pakData.find(item => Number(item.id) === Number(id));
    if (!row) return;

    document.getElementById('pakId').value = row.id;
    document.getElementById('pakNama').textContent = (row.nama || '-') + ' — ' + (row.tahun_anggaran || '-');
    document.getElementById('pakStatus').value = ['disetujui', 'revisi', 'ditolak'].includes(row.status) ? row.status : 'disetujui';
    document.getElementById('pakCatatan').value = row.catatan || '';
    togglePakCatatanRequired();

    const modal = document.getElementById('modalPak');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => document.getElementById('pakStatus').focus(), 50);
}

function closePakModal() {
    const modal = document.getElementById('modalPak');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function submitPakForm(event) {
    event.preventDefault();
    const form = event.target;
    const btn = document.getElementById('pakSubmitBtn');
    const formData = new FormData(form);
    formData.append('csrf_token', document.getElementById('topbarCsrfToken').value);

    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    try {
        const response = await fetch('api/PakController.php?action=update_status', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (data.success) {
            appNotify(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            appNotify(data.message || 'Gagal menyimpan keputusan PAK', 'error');
        }
    } catch (error) {
        appNotify('Gagal menyimpan keputusan PAK', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan Keputusan';
    }
}
</script>
