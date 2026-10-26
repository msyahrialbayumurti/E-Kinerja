<?php
$role = $_SESSION['role'] ?? 'guest';
$is_admin = $role === 'admin';

$team_stmt = $pdo->query("
    SELECT t.*, u.nama AS ketua_nama, u.nip AS ketua_nip
    FROM tim_kerja t
    JOIN users u ON t.ketua_id = u.id
    ORDER BY t.nama_tim ASC
");
$teams = $team_stmt->fetchAll();

$members_by_team = [];
if ($teams) {
    $team_ids = array_map('intval', array_column($teams, 'id'));
    $placeholders = implode(',', array_fill(0, count($team_ids), '?'));
    $member_stmt = $pdo->prepare("
        SELECT a.tim_kerja_id, a.anggota_id, u.nama, u.nip, a.posisi
        FROM anggota_tim_kerja a
        JOIN users u ON a.anggota_id = u.id
        WHERE a.tim_kerja_id IN ($placeholders)
        ORDER BY a.id ASC
    ");
    $member_stmt->execute($team_ids);

    foreach ($member_stmt->fetchAll() as $member) {
        $members_by_team[(int) $member['tim_kerja_id']][] = $member;
    }
}

$all_users = $pdo->query("SELECT id, nama, nip, role FROM users ORDER BY nama ASC")->fetchAll();
$json_flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
$teams_json = json_encode($teams, $json_flags);
$team_members_json = json_encode($members_by_team, $json_flags);
?>

<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-end gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Daftar Tim Kerja</h1>
        <p class="text-sm text-slate-500 mt-1">Pembagian dan keanggotaan tim kerja di unit organisasi</p>
    </div>
    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center sm:gap-3">
        <div class="text-xs font-semibold text-slate-500">
            <?= count($teams) ?> tim terdaftar
        </div>
        <?php if ($is_admin): ?>
            <button type="button" onclick="openTeamModal(0)" class="mobile-action bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs font-bold rounded-sm inline-flex items-center gap-2 sm:w-auto">
                <span class="text-base leading-none">+</span> Tambah Tim Kerja
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-sm shadow-sm overflow-hidden">
    <div class="table-scroll overflow-x-auto">
        <table class="w-full min-w-[820px] text-left border-collapse table-compact">
            <thead>
                <tr class="table-header border-b border-slate-200">
                    <th class="w-12 text-center border-r border-slate-200">No</th>
                    <th class="border-r border-slate-200">Nama Tim Kerja</th>
                    <th class="border-r border-slate-200">Ketua Tim</th>
                    <th class="border-r border-slate-200 text-center">Jumlah Anggota</th>
                    <th class="border-r border-slate-200">Status</th>
                    <th class="w-44 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-slate-700 bg-white text-sm">
                <?php if (!$teams): ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">
                            Belum ada data tim kerja.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($teams as $index => $team): ?>
                        <?php
                        $team_id = (int) $team['id'];
                        $members = $members_by_team[$team_id] ?? [];
                        $is_active = $team['status'] === 'aktif';
                        ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                            <td class="text-center border-r border-slate-200"><?= $index + 1 ?></td>
                            <td class="border-r border-slate-200 font-bold text-slate-800">
                                <?= htmlspecialchars($team['nama_tim'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($team['dasar_sk'])): ?>
                                    <div class="text-xs font-normal text-slate-500 mt-1">
                                        Dasar: <?= htmlspecialchars($team['dasar_sk'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="border-r border-slate-200">
                                <?= htmlspecialchars($team['ketua_nama'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                                <span class="text-xs text-slate-500"><?= htmlspecialchars($team['ketua_nip'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td class="border-r border-slate-200 text-center font-bold"><?= count($members) ?> Orang</td>
                            <td class="border-r border-slate-200">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-bold border <?= $is_active ? 'bg-green-100 text-green-800 border-green-200' : 'bg-slate-100 text-slate-600 border-slate-200' ?>">
                                    <?= $is_active ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td class="p-2 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="toggleTeamDetail(<?= $team_id ?>)" class="bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 px-2.5 py-1 text-xs font-semibold rounded-sm">
                                        Detail
                                    </button>
                                    <?php if ($is_admin): ?>
                                        <button type="button" onclick="openTeamModal(<?= $team_id ?>)" class="bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 px-2.5 py-1 text-xs font-semibold rounded-sm">
                                            Edit
                                        </button>
                                        <button type="button" onclick="deleteTeam(<?= $team_id ?>)" class="bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 px-2.5 py-1 text-xs font-semibold rounded-sm">
                                            Hapus
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr id="teamDetail<?= $team_id ?>" class="hidden bg-slate-50 border-b border-slate-200">
                            <td colspan="6" class="p-4">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                    Anggota <?= htmlspecialchars($team['nama_tim'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <?php if (!$members): ?>
                                    <div class="text-xs text-slate-500">Belum ada anggota terdaftar.</div>
                                <?php else: ?>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <?php foreach ($members as $member): ?>
                                            <div class="bg-white border border-slate-200 rounded-sm px-3 py-2">
                                                <div class="text-xs font-bold text-slate-800">
                                                    <?= htmlspecialchars($member['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                                <div class="text-[11px] text-slate-500 font-mono">
                                                    <?= htmlspecialchars($member['nip'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                                <div class="text-[11px] text-slate-600 mt-1">
                                                    <?= htmlspecialchars($member['posisi'] ?: 'Anggota', ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($is_admin): ?>
    <div id="modalTim" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" onclick="if (event.target === this) closeTeamModal()">
        <div class="modal-panel max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-sm border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 bg-slate-50">
                <h2 id="modalTimTitle" class="text-sm font-bold text-slate-800">Tambah Tim Kerja</h2>
                <button type="button" onclick="closeTeamModal()" class="text-slate-500 hover:text-slate-800 text-lg leading-none">&times;</button>
            </div>
            <form id="formTim" onsubmit="submitTeamForm(event)" class="p-5 space-y-3 text-xs">
                <input type="hidden" name="id" id="timId" value="0">

                <div>
                    <label for="timNama" class="block font-bold text-slate-700 mb-1">Nama Tim Kerja <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_tim" id="timNama" required class="w-full border border-slate-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="timKetua" class="block font-bold text-slate-700 mb-1">Ketua Tim <span class="text-red-500">*</span></label>
                        <select name="ketua_id" id="timKetua" required class="w-full border border-slate-300 rounded-sm px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <option value="">Pilih ketua tim</option>
                            <?php foreach ($all_users as $u): ?>
                                <option value="<?= (int) $u['id'] ?>">
                                    <?= htmlspecialchars($u['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u['role'] ?? '', ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="timStatus" class="block font-bold text-slate-700 mb-1">Status</label>
                        <select name="status" id="timStatus" class="w-full border border-slate-300 rounded-sm px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="timDasarSk" class="block font-bold text-slate-700 mb-1">Dasar SK</label>
                    <input type="text" name="dasar_sk" id="timDasarSk" class="w-full border border-slate-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200" placeholder="Contoh: SK Yayasan No. 12/2026">
                </div>

                <div>
                    <label for="timDeskripsi" class="block font-bold text-slate-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" id="timDeskripsi" rows="2" class="w-full border border-slate-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200"></textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-700">Anggota Tim</label>
                        <span class="text-[11px] text-slate-500">Centang pegawai yang menjadi anggota</span>
                    </div>
                    <div class="max-h-44 overflow-y-auto border border-slate-300 rounded-sm p-2 grid grid-cols-1 sm:grid-cols-2 gap-1">
                        <?php foreach ($all_users as $u): ?>
                            <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded-sm cursor-pointer">
                                <input type="checkbox" name="members[]" value="<?= (int) $u['id'] ?>" class="tim-member-checkbox accent-blue-600">
                                <span class="text-slate-700"><?= htmlspecialchars($u['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="ml-auto text-[10px] text-slate-400"><?= htmlspecialchars($u['role'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                    <button type="button" onclick="closeTeamModal()" class="px-4 py-2 border border-slate-300 rounded-sm text-slate-700 font-semibold hover:bg-slate-50">Batal</button>
                    <button type="submit" id="timSubmitBtn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-sm font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
const teamsData = <?= $teams_json ?: '[]' ?>;
const teamMembers = <?= $team_members_json ?: '{}' ?>;

function toggleTeamDetail(id) {
    const row = document.getElementById('teamDetail' + id);
    if (row) {
        row.classList.toggle('hidden');
    }
}

function openTeamModal(id) {
    const teamId = Number(id) || 0;
    const form = document.getElementById('formTim');
    if (!form) return;

    form.reset();
    document.getElementById('timId').value = teamId;
    document.getElementById('modalTimTitle').textContent = teamId > 0 ? 'Edit Tim Kerja' : 'Tambah Tim Kerja';
    document.querySelectorAll('.tim-member-checkbox').forEach(cb => { cb.checked = false; });

    if (teamId > 0) {
        const team = teamsData.find(t => Number(t.id) === teamId);
        if (team) {
            document.getElementById('timNama').value = team.nama_tim || '';
            document.getElementById('timKetua').value = String(team.ketua_id || '');
            document.getElementById('timDasarSk').value = team.dasar_sk || '';
            document.getElementById('timStatus').value = team.status || 'aktif';
            document.getElementById('timDeskripsi').value = team.deskripsi || '';

            const rows = teamMembers[teamId] || teamMembers[String(teamId)] || [];
            rows.forEach(member => {
                const checkbox = document.querySelector('.tim-member-checkbox[value="' + member.anggota_id + '"]');
                if (checkbox) checkbox.checked = true;
            });
        }
    }

    const modal = document.getElementById('modalTim');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => document.getElementById('timNama').focus(), 50);
}

function closeTeamModal() {
    const modal = document.getElementById('modalTim');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function submitTeamForm(event) {
    event.preventDefault();
    const form = event.target;
    const btn = document.getElementById('timSubmitBtn');
    const formData = new FormData(form);
    formData.append('csrf_token', document.getElementById('topbarCsrfToken').value);

    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    try {
        const response = await fetch('api/TimKerjaController.php?action=save', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (data.success) {
            appNotify(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            appNotify(data.message || 'Gagal menyimpan tim kerja', 'error');
        }
    } catch (error) {
        appNotify('Gagal menyimpan tim kerja', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan';
    }
}

async function deleteTeam(id) {
    if (!confirm('Hapus tim kerja ini? Seluruh keanggotaan tim juga akan dihapus.')) return;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('csrf_token', document.getElementById('topbarCsrfToken').value);

    try {
        const response = await fetch('api/TimKerjaController.php?action=delete', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (data.success) {
            appNotify(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            appNotify(data.message || 'Gagal menghapus tim kerja', 'error');
        }
    } catch (error) {
        appNotify('Gagal menghapus tim kerja', 'error');
    }
}
</script>
