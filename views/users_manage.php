<?php
/**
 * views/users_manage.php - Manajemen Pengguna Khusus Administrator
 */
if (!has_role('admin')) {
    echo "<div class='p-4 bg-red-100 text-red-700 font-bold'>Akses Ditolak: Halaman khusus Administrator</div>";
    return;
}

// Fetch all users
$stmt = $pdo->query("
    SELECT u.*, a.nama as nama_atasan 
    FROM users u 
    LEFT JOIN users a ON u.atasan_id = a.id 
    ORDER BY u.role ASC, u.nama ASC
");
$all_users = $stmt->fetchAll();

// Fetch potential atasan (ketua_yayasan / admin)
$stmtAtasan = $pdo->query("SELECT id, nama, jabatan FROM users WHERE role IN ('ketua_yayasan', 'admin') ORDER BY nama ASC");
$list_atasan = $stmtAtasan->fetchAll();

$csrf_token = generate_csrf_token();

$count_total = count($all_users);
$count_admin = count(array_filter($all_users, static fn($u) => ($u['role'] ?? '') === 'admin'));
$count_ketua = count(array_filter($all_users, static fn($u) => ($u['role'] ?? '') === 'ketua_yayasan'));
$count_guru = count(array_filter($all_users, static fn($u) => ($u['role'] ?? '') === 'guru_pegawai'));
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Manajemen Pengguna & Pegawai</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola master data akun, peran akses sistem, dan relasi atasan penilai</p>
    </div>
    <div class="w-full md:w-auto">
        <button onclick="openModalTambahUser()" class="mobile-action bg-[#1e3a8a] text-white hover:bg-blue-800 px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-sm shadow-sm flex items-center gap-2 md:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Pengguna Baru
        </button>
    </div>
</div>

<!-- CSRF Token -->
<input type="hidden" id="csrfTokenUsers" value="<?= $csrf_token ?>">

<!-- Ringkasan Pengguna -->
<div class="mb-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Total Pengguna</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $count_total ?></div>
        </div>
    </div>
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Administrator</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $count_admin ?></div>
        </div>
    </div>
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Ketua Yayasan</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $count_ketua ?></div>
        </div>
    </div>
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Guru / Pegawai</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $count_guru ?></div>
        </div>
    </div>
</div>

<!-- Tabel Master Pengguna -->
<div class="bg-white border border-slate-300 rounded-sm shadow-sm overflow-hidden mb-8">
    <div class="table-scroll overflow-x-auto">
        <table class="w-full text-left border-collapse table-compact min-w-[900px]">
            <thead>
                <tr class="table-header border-b border-slate-300 bg-slate-100">
                    <th class="w-10 text-center py-2.5">No</th>
                    <th class="w-48 py-2.5">Nama Pegawai & NIP</th>
                    <th class="w-32 py-2.5">Username</th>
                    <th class="w-36 py-2.5">Peran / Role</th>
                    <th class="w-48 py-2.5">Jabatan & Pangkat</th>
                    <th class="w-44 py-2.5">Atasan Penilai</th>
                    <th class="w-40 py-2.5">Kontak</th>
                    <th class="w-28 text-center py-2.5">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-xs text-slate-800 divide-y divide-slate-200">
                <?php if (count($all_users) > 0): ?>
                    <?php foreach ($all_users as $idx => $u): 
                        $roleBadge = [
                            'admin' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                            'ketua_yayasan' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'guru_pegawai' => 'bg-blue-100 text-blue-800 border-blue-200'
                        ][$u['role']] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                        $roleLabel = [
                            'admin' => 'Administrator',
                            'ketua_yayasan' => 'Ketua Yayasan',
                            'guru_pegawai' => 'Guru / Pegawai'
                        ][$u['role']] ?? $u['role'];
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="text-center font-mono py-2"><?= $idx + 1 ?></td>
                        <td class="py-2">
                            <div class="font-bold text-slate-900"><?= htmlspecialchars($u['nama']) ?></div>
                            <div class="text-[11px] font-mono text-slate-500">NIP: <?= htmlspecialchars($u['nip']) ?></div>
                        </td>
                        <td class="py-2 font-mono font-medium text-slate-700"><?= htmlspecialchars($u['username']) ?></td>
                        <td class="py-2">
                            <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase rounded-sm border <?= $roleBadge ?>">
                                <?= $roleLabel ?>
                            </span>
                        </td>
                        <td class="py-2">
                            <div class="font-medium text-slate-800"><?= htmlspecialchars($u['jabatan'] ?? '-') ?></div>
                            <div class="text-[11px] text-slate-500"><?= htmlspecialchars($u['pangkat_golongan'] ?? '-') ?></div>
                        </td>
                        <td class="py-2">
                            <div class="text-slate-800 font-medium"><?= htmlspecialchars($u['nama_atasan'] ?? '-') ?></div>
                        </td>
                        <td class="py-2 text-[11px] text-slate-600">
                            <div><?= htmlspecialchars($u['email'] ?? '-') ?></div>
                            <div class="font-mono text-slate-500"><?= htmlspecialchars($u['no_hp'] ?? '-') ?></div>
                        </td>
                        <td class="py-2 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button onclick="editUser(<?= $u['id'] ?>)" class="bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 px-2 py-1 text-[11px] font-semibold rounded-sm">
                                    Edit
                                </button>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <button data-nama="<?= htmlspecialchars($u['nama'], ENT_QUOTES, 'UTF-8') ?>" onclick="deleteUser(<?= (int) $u['id'] ?>, this.dataset.nama)" class="bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 px-2 py-1 text-[11px] font-semibold rounded-sm">
                                    Hapus
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-6 text-slate-500">Belum ada data pengguna</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit User -->
<div id="modalUserForm" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel flex max-h-[90vh] w-full max-w-2xl flex-col rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-slate-100 flex justify-between items-center flex-shrink-0">
            <h2 id="modalUserTitle" class="text-base font-bold text-slate-800 uppercase tracking-wide">Tambah Pengguna Baru</h2>
            <button onclick="closeModalUser()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formUser" onsubmit="submitUserForm(event)" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            <input type="hidden" name="id" id="userId" value="">
            <input type="hidden" name="action" id="userAction" value="create_user">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">NIP Pegawai <span class="text-red-500">*</span></label>
                    <input type="text" name="nip" id="userNip" required class="w-full border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:border-slate-500 font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" id="userUsername" required class="w-full border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:border-slate-500 font-mono">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap & Gelar <span class="text-red-500">*</span></label>
                <input type="text" name="nama" id="userNama" required class="w-full border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:border-slate-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Peran / Role <span class="text-red-500">*</span></label>
                    <select name="role" id="userRole" required class="w-full border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:border-slate-500">
                        <option value="guru_pegawai">Guru / Pegawai Pelaksana</option>
                        <option value="ketua_yayasan">Ketua Yayasan / Atasan Penilai</option>
                        <option value="admin">Administrator IT / Kepegawaian</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Atasan Penilai Langsung</label>
                    <select name="atasan_id" id="userAtasanId" class="w-full border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:border-slate-500">
                        <option value="">-- Tanpa Atasan (Mandiri) --</option>
                        <?php foreach ($list_atasan as $at): ?>
                        <option value="<?= $at['id'] ?>"><?= htmlspecialchars($at['nama']) ?> (<?= htmlspecialchars($at['jabatan'] ?? 'Pimpinan') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jabatan</label>
                    <input type="text" name="jabatan" id="userJabatan" placeholder="Misal: Guru Ahli Pertama / Kepala IT" class="w-full border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:border-slate-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Pangkat / Golongan</label>
                    <input type="text" name="pangkat_golongan" id="userPangkat" placeholder="Misal: Penata Muda - III/a" class="w-full border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:border-slate-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Email</label>
                    <input type="email" name="email" id="userEmail" class="w-full border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:border-slate-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp / HP</label>
                    <input type="text" name="no_hp" id="userNoHp" class="w-full border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:border-slate-500 font-mono">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">
                    Password 
                    <span id="passwordNotice" class="text-slate-400 font-normal lowercase">(kosongkan jika tidak ingin mengubah password)</span>
                </label>
                <input type="password" name="password" id="userPassword" minlength="8" class="w-full border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:border-slate-500">
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModalUser()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm text-xs font-semibold">
                    Batal
                </button>
                <button type="submit" class="bg-[#1e3a8a] text-white hover:bg-blue-800 px-4 py-2 rounded-sm text-xs font-bold uppercase tracking-wider">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/users-manage.js"></script>
