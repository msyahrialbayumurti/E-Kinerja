<?php
$role = $_SESSION['role'] ?? 'guest';
$can_follow_up = in_array($role, ['admin', 'ketua_yayasan'], true);
$filter_tahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : 0;
if ($filter_tahun > 0 && !in_array($filter_tahun, $tahun_options ?? [], true)) {
    $filter_tahun = 0;
}

$sql = "
    SELECT r.*, u.nama, u.nip, s.tahun_anggaran
    FROM rekap_keberatan r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN penilaian p ON r.penilaian_id = p.id
    LEFT JOIN skp s ON p.skp_id = s.id
";
$params = [];
if ($filter_tahun > 0) {
    $sql .= " WHERE s.tahun_anggaran = ?";
    $params[] = $filter_tahun;
}
$sql .= " ORDER BY r.created_at DESC, r.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$badges = [
    'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
    'diproses' => 'bg-blue-100 text-blue-800 border-blue-200',
    'ditanggapi' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'ditolak' => 'bg-red-100 text-red-800 border-red-200',
];

$json_flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
$keberatan_json = json_encode($rows, $json_flags);
?>

<div class="mb-6 flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Rekap Keberatan</h1>
        <p class="text-sm text-slate-500 mt-1">Daftar pengajuan keberatan hasil penilaian SKP oleh pegawai</p>
    </div>
    <div class="text-xs font-semibold text-slate-500">
        <?= count($rows) ?> pengajuan
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-sm shadow-sm overflow-hidden">
    <div class="table-scroll overflow-x-auto">
        <table class="w-full min-w-[900px] text-left border-collapse table-compact">
            <thead>
                <tr class="table-header border-b border-slate-200">
                    <th class="w-12 text-center border-r border-slate-200">No</th>
                    <th class="border-r border-slate-200">Nama Pegawai Pengaju</th>
                    <th class="w-32 border-r border-slate-200">Periode Penilaian</th>
                    <th class="border-r border-slate-200">Alasan Keberatan (Ringkasan)</th>
                    <th class="w-36 border-r border-slate-200">Tanggal Pengajuan</th>
                    <th class="w-32 border-r border-slate-200">Status</th>
                    <th class="w-40 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-slate-700 bg-white text-sm align-top">
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-sm font-medium">Belum ada pengajuan keberatan yang masuk.</span>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $index => $row): ?>
                        <?php
                        $badge = $badges[$row['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                        $row_id = (int) $row['id'];
                        $alasan = $row['alasan_keberatan'] ?? '';
                        $alasan_short = function_exists('mb_strimwidth')
                            ? mb_strimwidth($alasan, 0, 150, '...', 'UTF-8')
                            : $alasan;
                        ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                            <td class="text-center border-r border-slate-200 py-3"><?= $index + 1 ?></td>
                            <td class="border-r border-slate-200 py-3">
                                <div class="font-bold text-slate-800"><?= htmlspecialchars($row['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="text-xs text-slate-500 font-mono"><?= htmlspecialchars($row['nip'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td class="border-r border-slate-200 py-3 font-medium">
                                <?= $row['tahun_anggaran'] ? 'Tahun ' . htmlspecialchars((string) $row['tahun_anggaran'], ENT_QUOTES, 'UTF-8') : '-' ?>
                            </td>
                            <td class="border-r border-slate-200 py-3" title="<?= htmlspecialchars($alasan, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($alasan_short, ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="border-r border-slate-200 py-3 text-xs font-mono">
                                <?= $row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-' ?>
                            </td>
                            <td class="border-r border-slate-200 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-bold border <?= $badge ?>">
                                    <?= htmlspecialchars(ucfirst($row['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="p-2 text-center py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="toggleKeberatanDetail(<?= $row_id ?>)" class="bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 px-2.5 py-1 text-xs font-semibold rounded-sm">
                                        Detail
                                    </button>
                                    <?php if ($can_follow_up): ?>
                                        <button type="button" onclick="openKeberatanModal(<?= $row_id ?>)" class="bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 px-2.5 py-1 text-xs font-semibold rounded-sm">
                                            Tindak Lanjut
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr id="keberatanDetail<?= $row_id ?>" class="hidden bg-slate-50 border-b border-slate-200">
                            <td colspan="7" class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                    <div class="bg-white border border-slate-200 rounded-sm p-3">
                                        <div class="font-bold uppercase tracking-wider text-slate-500 mb-1">Alasan Lengkap</div>
                                        <div class="text-slate-700 whitespace-pre-line"><?= htmlspecialchars($alasan ?: '-', ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <div class="bg-white border border-slate-200 rounded-sm p-3">
                                        <div class="font-bold uppercase tracking-wider text-slate-500 mb-1">Tanggapan</div>
                                        <div class="text-slate-700 whitespace-pre-line"><?= htmlspecialchars($row['tanggapan'] ?: 'Belum ada tanggapan.', ENT_QUOTES, 'UTF-8') ?></div>
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

<?php if ($can_follow_up): ?>
    <div id="modalKeberatan" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" onclick="if (event.target === this) closeKeberatanModal()">
        <div class="modal-panel max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-sm border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 bg-slate-50">
                <h2 class="text-sm font-bold text-slate-800">Tindak Lanjut Keberatan</h2>
                <button type="button" onclick="closeKeberatanModal()" class="text-slate-500 hover:text-slate-800 text-lg leading-none">&times;</button>
            </div>
            <form id="formKeberatan" onsubmit="submitKeberatanForm(event)" class="p-5 space-y-3 text-xs">
                <input type="hidden" name="id" id="keberatanId" value="0">

                <div class="bg-slate-50 border border-slate-200 rounded-sm px-3 py-2">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Pegawai Pengaju</div>
                    <div id="keberatanNama" class="font-bold text-slate-800">-</div>
                </div>

                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Alasan Keberatan</div>
                    <div id="keberatanAlasan" class="text-slate-700 whitespace-pre-line bg-white border border-slate-200 rounded-sm px-3 py-2 max-h-32 overflow-y-auto">-</div>
                </div>

                <div>
                    <label for="keberatanStatus" class="block font-bold text-slate-700 mb-1">Status Tindak Lanjut</label>
                    <select name="status" id="keberatanStatus" onchange="toggleKeberatanTanggapanRequired()" class="w-full border border-slate-300 rounded-sm px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <option value="diproses">Diproses</option>
                        <option value="ditanggapi">Ditanggapi</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <div>
                    <label for="keberatanTanggapan" class="block font-bold text-slate-700 mb-1">Tanggapan</label>
                    <textarea name="tanggapan" id="keberatanTanggapan" rows="4" class="w-full border border-slate-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200" placeholder="Wajib diisi untuk status ditanggapi atau ditolak"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                    <button type="button" onclick="closeKeberatanModal()" class="px-4 py-2 border border-slate-300 rounded-sm text-slate-700 font-semibold hover:bg-slate-50">Batal</button>
                    <button type="submit" id="keberatanSubmitBtn" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-sm font-bold">Simpan Tindak Lanjut</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
const keberatanData = <?= $keberatan_json ?: '[]' ?>;

function toggleKeberatanDetail(id) {
    const row = document.getElementById('keberatanDetail' + id);
    if (row) {
        row.classList.toggle('hidden');
    }
}

function toggleKeberatanTanggapanRequired() {
    const status = document.getElementById('keberatanStatus').value;
    document.getElementById('keberatanTanggapan').required = status === 'ditanggapi' || status === 'ditolak';
}

function openKeberatanModal(id) {
    const row = keberatanData.find(item => Number(item.id) === Number(id));
    if (!row) return;

    document.getElementById('keberatanId').value = row.id;
    document.getElementById('keberatanNama').textContent = (row.nama || '-') + (row.tahun_anggaran ? ' — Tahun ' + row.tahun_anggaran : '');
    document.getElementById('keberatanAlasan').textContent = row.alasan_keberatan || '-';
    document.getElementById('keberatanStatus').value = ['diproses', 'ditanggapi', 'ditolak'].includes(row.status) ? row.status : 'diproses';
    document.getElementById('keberatanTanggapan').value = row.tanggapan || '';
    toggleKeberatanTanggapanRequired();

    const modal = document.getElementById('modalKeberatan');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => document.getElementById('keberatanStatus').focus(), 50);
}

function closeKeberatanModal() {
    const modal = document.getElementById('modalKeberatan');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function submitKeberatanForm(event) {
    event.preventDefault();
    const form = event.target;
    const btn = document.getElementById('keberatanSubmitBtn');
    const formData = new FormData(form);
    formData.append('csrf_token', document.getElementById('topbarCsrfToken').value);

    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    try {
        const response = await fetch('api/KeberatanController.php?action=update_status', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (data.success) {
            appNotify(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            appNotify(data.message || 'Gagal menyimpan tindak lanjut', 'error');
        }
    } catch (error) {
        appNotify('Gagal menyimpan tindak lanjut', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan Tindak Lanjut';
    }
}
</script>
