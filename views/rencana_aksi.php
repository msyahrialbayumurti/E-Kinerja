<?php
$role = $_SESSION['role'] ?? 'guru_pegawai';
$user_id = (int) ($_SESSION['user_id'] ?? 0);
$can_manage = in_array($role, ['admin', 'guru_pegawai'], true);

$list_pegawai = [];

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
        : ($list_pegawai[0]['id'] ?? 0);
} else {
    $selected_pegawai_id = $user_id;
}

$triwulan = isset($_GET['triwulan']) ? intval($_GET['triwulan']) : 1;
if ($triwulan < 1 || $triwulan > 4) {
    $triwulan = 1;
}

$pegawai_stmt = $pdo->prepare("
    SELECT u.*, a.nama AS atasan_nama, a.nip AS atasan_nip, a.jabatan AS atasan_jabatan
    FROM users u
    LEFT JOIN users a ON u.atasan_id = a.id
    WHERE u.id = ?
");
$pegawai_stmt->execute([$selected_pegawai_id]);
$pegawai = $pegawai_stmt->fetch();

$rencana_stmt = $pdo->prepare("
    SELECT *
    FROM rencana_aksi
    WHERE user_id = ? AND triwulan = ?
    ORDER BY id ASC
");
$rencana_stmt->execute([$selected_pegawai_id, $triwulan]);
$rencana_list = $rencana_stmt->fetchAll();

$items_by_rencana = [];
if ($rencana_list) {
    $rencana_ids = array_map('intval', array_column($rencana_list, 'id'));
    $placeholders = implode(',', array_fill(0, count($rencana_ids), '?'));
    $item_stmt = $pdo->prepare("
        SELECT *
        FROM rencana_aksi_item
        WHERE rencana_aksi_id IN ($placeholders)
        ORDER BY id ASC
    ");
    $item_stmt->execute($rencana_ids);

    foreach ($item_stmt->fetchAll() as $item) {
        $items_by_rencana[(int) $item['rencana_aksi_id']][] = $item;
    }
}

$json_flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
$rencana_json = json_encode($rencana_list, $json_flags);
$items_json = json_encode($items_by_rencana, $json_flags);
?>

<div class="mb-4 flex flex-col justify-between gap-3 border-b border-slate-300 pb-3 sm:flex-row sm:items-end">
    <div>
        <h1 class="text-xl font-bold text-slate-900 uppercase tracking-tight">Rencana Aksi</h1>
        <p class="text-[11px] font-semibold text-slate-600 mt-0.5 uppercase tracking-wider">Pemecahan Target SKP ke dalam Rencana Aksi (Triwulanan)</p>
    </div>

    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center">
        <?php if ($can_manage): ?>
            <button type="button" onclick="openRencanaModal(0)" class="mobile-action bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 text-xs font-bold rounded-sm inline-flex items-center gap-1.5 sm:w-auto">
                <span class="text-base leading-none">+</span> Tambah Rencana Aksi
            </button>
        <?php endif; ?>

        <form method="GET" class="flex w-full flex-col gap-2 rounded-sm border border-slate-300 bg-slate-100 px-3 py-1.5 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center">
            <input type="hidden" name="page" value="rencana_aksi">

            <?php if ($role === 'admin' || $role === 'ketua_yayasan'): ?>
                <label for="pegawaiIdRa" class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Pegawai:</label>
                <select id="pegawaiIdRa" name="pegawai_id" onchange="this.form.submit()" class="w-full rounded-sm border border-slate-400 bg-white px-2 py-1 text-xs font-semibold text-slate-900 focus:border-slate-800 focus:outline-none sm:w-auto sm:py-0.5">
                    <?php foreach ($list_pegawai as $item_pegawai): ?>
                        <option value="<?= (int) $item_pegawai['id'] ?>" <?= $selected_pegawai_id === (int) $item_pegawai['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($item_pegawai['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <label for="triwulanRa" class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Periode:</label>
            <select id="triwulanRa" name="triwulan" onchange="this.form.submit()" class="w-full rounded-sm border border-slate-400 bg-white px-2 py-1 text-xs font-semibold text-slate-900 focus:border-slate-800 focus:outline-none sm:w-auto sm:py-0.5">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <option value="<?= $i ?>" <?= $triwulan === $i ? 'selected' : '' ?>>Triwulan <?= $i ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
</div>

<?php if (!$pegawai): ?>
    <div class="bg-white border border-slate-200 rounded-sm p-8 text-center text-slate-500">
        Data pegawai tidak ditemukan.
    </div>
<?php else: ?>
    <div class="border border-slate-300 bg-white mb-5">
        <div class="grid grid-cols-1 md:grid-cols-2 md:divide-x divide-slate-300">
            <div>
                <div class="bg-slate-200 border-b border-slate-300 px-3 py-1 text-[10px] font-bold text-slate-800 uppercase tracking-widest text-center">Pegawai Yang Dinilai</div>
                <table class="w-full text-xs text-left table-fixed">
                    <tr class="border-b border-slate-200"><th class="w-24 px-3 py-1 bg-slate-50 font-semibold border-r border-slate-200">Nama</th><td class="px-3 py-1 font-bold text-slate-900"><?= htmlspecialchars($pegawai['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr class="border-b border-slate-200"><th class="w-24 px-3 py-1 bg-slate-50 font-semibold border-r border-slate-200">NIP</th><td class="px-3 py-1 font-mono text-slate-800"><?= htmlspecialchars($pegawai['nip'] ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th class="w-24 px-3 py-1 bg-slate-50 font-semibold border-r border-slate-200">Pangkat/Gol</th><td class="px-3 py-1 text-slate-800"><?= htmlspecialchars($pegawai['pangkat_golongan'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
                </table>
            </div>
            <div>
                <div class="bg-slate-200 border-b border-slate-300 px-3 py-1 text-[10px] font-bold text-slate-800 uppercase tracking-widest text-center">Pejabat Penilai Kinerja</div>
                <table class="w-full text-xs text-left table-fixed">
                    <tr class="border-b border-slate-200"><th class="w-24 px-3 py-1 bg-slate-50 font-semibold border-r border-slate-200">Nama</th><td class="px-3 py-1 font-bold text-slate-900"><?= htmlspecialchars($pegawai['atasan_nama'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr class="border-b border-slate-200"><th class="w-24 px-3 py-1 bg-slate-50 font-semibold border-r border-slate-200">NIP</th><td class="px-3 py-1 font-mono text-slate-800"><?= htmlspecialchars($pegawai['atasan_nip'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th class="w-24 px-3 py-1 bg-slate-50 font-semibold border-r border-slate-200">Jabatan</th><td class="px-3 py-1 text-slate-800"><?= htmlspecialchars($pegawai['atasan_jabatan'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="border border-slate-400 bg-white">
        <div class="table-scroll overflow-x-auto">
        <table class="w-full min-w-[960px] text-left border-collapse table-compact">
            <thead>
                <tr class="table-header">
                    <th class="w-10 text-center">No</th>
                    <th class="w-[35%]">Rencana Hasil Kerja (RHK) & Indikator</th>
                    <th class="w-[15%] text-center">Target</th>
                    <th class="<?= $can_manage ? 'w-[45%]' : 'w-[50%]' ?>">Rencana Aksi List</th>
                    <?php if ($can_manage): ?>
                        <th class="w-24 text-center">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="align-top">
                <?php if (!$rencana_list): ?>
                    <tr>
                        <td colspan="<?= $can_manage ? 5 : 4 ?>" class="p-8 text-center text-slate-500">
                            Belum ada rencana aksi untuk Triwulan <?= $triwulan ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rencana_list as $index => $rencana): ?>
                        <?php
                        $rencana_id = (int) $rencana['id'];
                        $items = $items_by_rencana[$rencana_id] ?? [];
                        ?>
                        <tr class="border-t border-slate-300">
                            <td class="text-center font-bold text-slate-600 bg-slate-50"><?= $index + 1 ?></td>
                            <td class="bg-white">
                                <div class="font-bold text-slate-900 leading-tight"><?= htmlspecialchars($rencana['rhk_indikator'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td class="text-center font-bold font-mono text-slate-800 bg-slate-50"><?= htmlspecialchars($rencana['target'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="p-0 bg-white">
                                <table class="w-full h-full border-collapse">
                                    <?php if (!$items): ?>
                                        <tr class="bg-white">
                                            <td class="w-8 text-center border-r border-slate-200 font-bold text-slate-400 bg-slate-50 text-[10px]">-</td>
                                            <td class="px-2 py-1.5 leading-tight border-r border-slate-200 text-slate-400 italic">Belum ada rencana aksi terdaftar.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($items as $item_index => $item): ?>
                                            <tr class="border-b border-slate-200 bg-white hover:bg-slate-50 transition-none">
                                                <td class="w-8 text-center border-r border-slate-200 font-bold text-slate-500 bg-slate-50 text-[10px]"><?= $index + 1 ?>.<?= $item_index + 1 ?></td>
                                                <td class="px-2 py-1.5 leading-tight border-r border-slate-200 text-slate-800"><?= htmlspecialchars($item['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </table>
                            </td>
                            <?php if ($can_manage): ?>
                                <td class="p-2 text-center align-middle">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" onclick="openRencanaModal(<?= $rencana_id ?>)" class="bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 px-2 py-1 text-[11px] font-semibold rounded-sm">
                                            Edit
                                        </button>
                                        <button type="button" onclick="deleteRencana(<?= $rencana_id ?>)" class="bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 px-2 py-1 text-[11px] font-semibold rounded-sm">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
<?php endif; ?>

<?php if ($can_manage): ?>
    <div id="modalRencana" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" onclick="if (event.target === this) closeRencanaModal()">
        <div class="modal-panel max-h-[92vh] w-full max-w-xl overflow-y-auto rounded-sm border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 bg-slate-50">
                <h2 id="modalRencanaTitle" class="text-sm font-bold text-slate-800">Tambah Rencana Aksi</h2>
                <button type="button" onclick="closeRencanaModal()" class="text-slate-500 hover:text-slate-800 text-lg leading-none">&times;</button>
            </div>
            <form id="formRencana" onsubmit="submitRencanaForm(event)" class="p-5 space-y-3 text-xs">
                <input type="hidden" name="id" id="rencanaId" value="0">
                <?php if ($role === 'admin'): ?>
                    <input type="hidden" name="user_id" id="rencanaUserId" value="<?= $selected_pegawai_id ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="rencanaTriwulan" class="block font-bold text-slate-700 mb-1">Triwulan</label>
                        <select name="triwulan" id="rencanaTriwulan" class="w-full border border-slate-300 rounded-sm px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <option value="<?= $i ?>">Triwulan <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="rencanaTarget" class="block font-bold text-slate-700 mb-1">Target</label>
                        <input type="text" name="target" id="rencanaTarget" maxlength="50" required class="w-full border border-slate-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200" placeholder="Contoh: 100 Dokumen">
                    </div>
                </div>

                <div>
                    <label for="rencanaRhk" class="block font-bold text-slate-700 mb-1">RHK / Indikator <span class="text-red-500">*</span></label>
                    <textarea name="rhk_indikator" id="rencanaRhk" rows="3" required class="w-full border border-slate-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200"></textarea>
                </div>

                <div>
                    <label for="rencanaItems" class="block font-bold text-slate-700 mb-1">Rencana Aksi List</label>
                    <textarea name="items" id="rencanaItems" rows="6" class="w-full border border-slate-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200" placeholder="Tulis satu rencana aksi per baris"></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Satu baris mewakili satu butir rencana aksi.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                    <button type="button" onclick="closeRencanaModal()" class="px-4 py-2 border border-slate-300 rounded-sm text-slate-700 font-semibold hover:bg-slate-50">Batal</button>
                    <button type="submit" id="rencanaSubmitBtn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-sm font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
const rencanaData = <?= $rencana_json ?: '[]' ?>;
const rencanaItems = <?= $items_json ?: '{}' ?>;

function openRencanaModal(id) {
    const rencanaId = Number(id) || 0;
    const form = document.getElementById('formRencana');
    if (!form) return;

    form.reset();
    document.getElementById('rencanaId').value = rencanaId;
    document.getElementById('rencanaTriwulan').value = '<?= $triwulan ?>';
    document.getElementById('rencanaRhk').value = '';
    document.getElementById('rencanaTarget').value = '';
    document.getElementById('rencanaItems').value = '';
    document.getElementById('modalRencanaTitle').textContent = rencanaId > 0 ? 'Edit Rencana Aksi' : 'Tambah Rencana Aksi';

    if (rencanaId > 0) {
        const row = rencanaData.find(item => Number(item.id) === rencanaId);
        if (row) {
            document.getElementById('rencanaTriwulan').value = String(row.triwulan || '<?= $triwulan ?>');
            document.getElementById('rencanaRhk').value = row.rhk_indikator || '';
            document.getElementById('rencanaTarget').value = row.target || '';
            const items = rencanaItems[rencanaId] || rencanaItems[String(rencanaId)] || [];
            document.getElementById('rencanaItems').value = items.map(item => item.deskripsi).join('\n');
        }
    }

    const modal = document.getElementById('modalRencana');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => document.getElementById('rencanaRhk').focus(), 50);
}

function closeRencanaModal() {
    const modal = document.getElementById('modalRencana');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function submitRencanaForm(event) {
    event.preventDefault();
    const form = event.target;
    const btn = document.getElementById('rencanaSubmitBtn');
    const formData = new FormData(form);
    formData.append('csrf_token', document.getElementById('topbarCsrfToken').value);

    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    try {
        const response = await fetch('api/RencanaAksiController.php?action=save', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (data.success) {
            appNotify(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            appNotify(data.message || 'Gagal menyimpan rencana aksi', 'error');
        }
    } catch (error) {
        appNotify('Gagal menyimpan rencana aksi', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan';
    }
}

async function deleteRencana(id) {
    if (!confirm('Hapus rencana aksi ini beserta seluruh butirnya?')) return;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('csrf_token', document.getElementById('topbarCsrfToken').value);

    try {
        const response = await fetch('api/RencanaAksiController.php?action=delete', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (data.success) {
            appNotify(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            appNotify(data.message || 'Gagal menghapus rencana aksi', 'error');
        }
    } catch (error) {
        appNotify('Gagal menghapus rencana aksi', 'error');
    }
}
</script>
