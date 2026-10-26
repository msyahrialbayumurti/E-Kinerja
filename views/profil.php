<?php
/**
 * views/profil.php - Profil Pegawai Dinamis (Data Pribadi & Data Atasan)
 * Sesuai AGENTS.md
 */

$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? 'guru_pegawai';
$csrf_token = generate_csrf_token();

// Fetch current user and their direct superior from DB
$stmtUser = $pdo->prepare("
    SELECT u.*, 
           a.nama as atasan_nama, 
           a.nip as atasan_nip, 
           a.jabatan as atasan_jabatan, 
           a.pangkat_golongan as atasan_pangkat, 
           a.email as atasan_email, 
           a.no_hp as atasan_no_hp 
    FROM users u 
    LEFT JOIN users a ON u.atasan_id = a.id 
    WHERE u.id = ?
");
$stmtUser->execute([$user_id]);
$current_user_data = $stmtUser->fetch();

$title = 'Profil Pegawai';
if ($role === 'admin') {
    $title = 'Profil Administrator Kepegawaian';
} elseif ($role === 'ketua_yayasan') {
    $title = 'Profil Ketua Yayasan / Pimpinan Penilai';
}

$initialProfil = strtoupper(substr($current_user_data['nama'] ?? 'U', 0, 1));
$roleProfilLabel = [
    'admin' => 'Administrator',
    'ketua_yayasan' => 'Ketua Yayasan',
    'guru_pegawai' => 'Guru / Pegawai',
][$role] ?? ucfirst($role);
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($title) ?></h1>
        <p class="text-sm text-slate-500 mt-1">Data identitas kepegawaian dan hierarki atasan penilai kinerja</p>
    </div>
    <div class="flex flex-col gap-2 sm:flex-row">
        <button onclick="openModalEditProfil()" class="flex items-center justify-center gap-1.5 rounded-sm bg-[#1e3a8a] px-3.5 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-blue-800">
            ✏️ Edit Kontak Profil
        </button>
        <button onclick="openModalGantiPassword()" class="flex items-center justify-center gap-1.5 rounded-sm bg-slate-700 px-3.5 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-slate-800">
            🔒 Ganti Password
        </button>
    </div>
</div>

<?php require __DIR__ . '/../layout/panduan_role.php'; ?>

<input type="hidden" id="csrfTokenProfil" value="<?= $csrf_token ?>">

<!-- Kartu Identitas -->
<div class="mb-5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="relative overflow-hidden bg-gradient-to-r from-[#0f172a] via-[#172554] to-[#1e3a8a] p-6 text-white">
        <div class="pointer-events-none absolute -right-10 -top-16 h-48 w-48 rounded-full bg-blue-400/10 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-20 left-1/3 h-40 w-40 rounded-full bg-[#ca8a04]/10 blur-2xl"></div>

        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-white/10 text-2xl font-bold text-white ring-1 ring-white/20">
                    <?= htmlspecialchars($initialProfil) ?>
                </div>
                <div class="min-w-0">
                    <h2 class="truncate text-lg font-bold leading-tight"><?= htmlspecialchars($current_user_data['nama'] ?? 'Pengguna') ?></h2>
                    <p class="mt-0.5 font-mono text-[11px] text-slate-300">NIP: <?= htmlspecialchars($current_user_data['nip'] ?? '-') ?></p>
                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                        <span class="rounded-md bg-[#eab308]/15 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-[#facc15] ring-1 ring-[#eab308]/25"><?= htmlspecialchars($roleProfilLabel) ?></span>
                        <span class="rounded-md bg-white/5 px-2 py-0.5 text-[10px] text-slate-300 ring-1 ring-white/10"><?= htmlspecialchars($current_user_data['jabatan'] ?? 'Jabatan belum diisi') ?></span>
                        <span class="rounded-md bg-white/5 px-2 py-0.5 text-[10px] text-slate-300 ring-1 ring-white/10"><?= htmlspecialchars($current_user_data['pangkat_golongan'] ?? 'Golongan -') ?></span>
                    </div>
                </div>
            </div>

            <div class="grid flex-shrink-0 grid-cols-2 gap-2 sm:w-56">
                <div class="rounded-xl bg-white/5 px-3 py-2.5 ring-1 ring-white/10">
                    <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-400">Status</div>
                    <div class="mt-0.5 flex items-center gap-1.5 text-xs font-semibold text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Aktif
                    </div>
                </div>
                <div class="rounded-xl bg-white/5 px-3 py-2.5 ring-1 ring-white/10">
                    <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-400">Atasan Penilai</div>
                    <div class="mt-0.5 truncate text-xs font-semibold text-slate-200" title="<?= htmlspecialchars($current_user_data['atasan_nama'] ?? '-') ?>">
                        <?= htmlspecialchars($current_user_data['atasan_nama'] ?? '—') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Navigation & Cards -->
<div class="bg-white border border-slate-300 rounded-sm shadow-sm overflow-hidden" x-data="{ tab: 'pribadi' }">
    <div class="border-b border-slate-200 bg-slate-50 px-2 sm:px-4">
        <nav class="table-scroll -mb-px flex gap-2 overflow-x-auto sm:gap-6">
            <button @click="tab = 'pribadi'" :class="{'border-[#ca8a04] text-slate-900 font-bold': tab === 'pribadi', 'border-transparent text-slate-500 hover:text-slate-700': tab !== 'pribadi'}" class="flex-shrink-0 whitespace-nowrap border-b-2 px-2 py-3 text-[11px] uppercase tracking-wider transition-colors sm:text-xs">
                Tab 1: Data Pribadi Pegawai
            </button>
            <button @click="tab = 'atasan'" :class="{'border-[#ca8a04] text-slate-900 font-bold': tab === 'atasan', 'border-transparent text-slate-500 hover:text-slate-700': tab !== 'atasan'}" class="flex-shrink-0 whitespace-nowrap border-b-2 px-2 py-3 text-[11px] uppercase tracking-wider transition-colors sm:text-xs">
                Tab 2: Data Atasan Penilai
            </button>
        </nav>
    </div>

    <!-- TAB 1: DATA PRIBADI -->
    <div x-show="tab === 'pribadi'" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2 flex items-center gap-2">
                    <span class="w-2 h-2 bg-[#ca8a04] inline-block"></span>
                    Informasi Identitas Kepegawaian
                </h3>
                <dl class="grid grid-cols-3 gap-y-3.5 gap-x-4 text-xs">
                    <dt class="text-slate-500 font-medium">NIP</dt>
                    <dd class="col-span-2 text-slate-900 font-bold font-mono"><?= htmlspecialchars($current_user_data['nip'] ?? '-') ?></dd>

                    <dt class="text-slate-500 font-medium">Nama Lengkap</dt>
                    <dd class="col-span-2 text-slate-900 font-bold"><?= htmlspecialchars($current_user_data['nama'] ?? '-') ?></dd>

                    <dt class="text-slate-500 font-medium">Username Akun</dt>
                    <dd class="col-span-2 text-slate-700 font-mono"><?= htmlspecialchars($current_user_data['username'] ?? '-') ?></dd>

                    <dt class="text-slate-500 font-medium">Peran / Hak Akses</dt>
                    <dd class="col-span-2">
                        <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase rounded-sm bg-blue-100 text-blue-800 border border-blue-200">
                            <?= htmlspecialchars($current_user_data['role'] ?? '-') ?>
                        </span>
                    </dd>

                    <dt class="text-slate-500 font-medium">Pangkat / Golongan</dt>
                    <dd class="col-span-2 text-slate-800 font-medium"><?= htmlspecialchars($current_user_data['pangkat_golongan'] ?? '-') ?></dd>

                    <dt class="text-slate-500 font-medium">Jabatan</dt>
                    <dd class="col-span-2 text-slate-800 font-medium"><?= htmlspecialchars($current_user_data['jabatan'] ?? '-') ?></dd>
                </dl>
            </div>

            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2 flex items-center gap-2">
                    <span class="w-2 h-2 bg-blue-700 inline-block"></span>
                    Kontak & Unit Kerja
                </h3>
                <dl class="grid grid-cols-3 gap-y-3.5 gap-x-4 text-xs">
                    <dt class="text-slate-500 font-medium">Email Resmi</dt>
                    <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($current_user_data['email'] ?? '-') ?></dd>

                    <dt class="text-slate-500 font-medium">Nomor Telepon/WA</dt>
                    <dd class="col-span-2 text-slate-800 font-mono"><?= htmlspecialchars($current_user_data['no_hp'] ?? '-') ?></dd>

                    <dt class="text-slate-500 font-medium">Unit Kerja</dt>
                    <dd class="col-span-2 text-slate-800">Unit Pelaksana Pendidikan & Pengajaran</dd>

                    <dt class="text-slate-500 font-medium">Terdaftar Sejak</dt>
                    <dd class="col-span-2 text-slate-600 font-mono"><?= date('d M Y H:i', strtotime($current_user_data['created_at'] ?? 'now')) ?> WIB</dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- TAB 2: DATA ATASAN -->
    <div x-show="tab === 'atasan'" class="p-6">
        <?php if (!empty($current_user_data['atasan_nama'])): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2 flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-600 inline-block"></span>
                    Identitas Pejabat Penilai Kinerja
                </h3>
                <dl class="grid grid-cols-3 gap-y-3.5 gap-x-4 text-xs">
                    <dt class="text-slate-500 font-medium">Nama Atasan</dt>
                    <dd class="col-span-2 text-slate-900 font-bold"><?= htmlspecialchars($current_user_data['atasan_nama']) ?></dd>

                    <dt class="text-slate-500 font-medium">NIP Atasan</dt>
                    <dd class="col-span-2 text-slate-900 font-bold font-mono"><?= htmlspecialchars($current_user_data['atasan_nip'] ?? '-') ?></dd>

                    <dt class="text-slate-500 font-medium">Jabatan</dt>
                    <dd class="col-span-2 text-slate-800 font-medium"><?= htmlspecialchars($current_user_data['atasan_jabatan'] ?? '-') ?></dd>

                    <dt class="text-slate-500 font-medium">Pangkat / Golongan</dt>
                    <dd class="col-span-2 text-slate-800 font-medium"><?= htmlspecialchars($current_user_data['atasan_pangkat'] ?? '-') ?></dd>
                </dl>
            </div>

            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2 flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-600 inline-block"></span>
                    Kontak & Jam Kerja Atasan
                </h3>
                <dl class="grid grid-cols-3 gap-y-3.5 gap-x-4 text-xs">
                    <dt class="text-slate-500 font-medium">Email Kantor</dt>
                    <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($current_user_data['atasan_email'] ?? '-') ?></dd>

                    <dt class="text-slate-500 font-medium">No. Kontak / WA</dt>
                    <dd class="col-span-2 text-slate-800 font-mono"><?= htmlspecialchars($current_user_data['atasan_no_hp'] ?? '-') ?></dd>

                    <dt class="text-slate-500 font-medium">Wewenang</dt>
                    <dd class="col-span-2 text-slate-700">Penetapan ekspektasi kinerja, verifikasi bukti eviden, dan umpan balik berkelanjutan</dd>
                </dl>
            </div>
        </div>
        <?php else: ?>
        <div class="py-10 text-center text-slate-500">
            <div class="text-base font-bold text-slate-800 mb-1">Tidak Memiliki Atasan Penilai Langsung</div>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                Akun Anda berkedudukan sebagai Pimpinan Tertinggi (Ketua Yayasan) atau Administrator Sistem yang tidak memerlukan atasan penilai dalam matriks evaluasi kinerja.
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Edit Kontak Profil -->
<div id="modalEditProfil" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel max-h-[92vh] w-full max-w-md overflow-y-auto rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-slate-100 flex justify-between items-center">
            <h2 class="text-base font-bold text-slate-800 uppercase tracking-wide">Edit Data Kontak Profil</h2>
            <button onclick="closeModalProfil('modalEditProfil')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formEditProfil" onsubmit="submitEditProfil(event)" class="p-6 space-y-4 text-xs">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($current_user_data['nama'] ?? '') ?>" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($current_user_data['email'] ?? '') ?>" class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp / HP</label>
                <input type="text" name="no_hp" value="<?= htmlspecialchars($current_user_data['no_hp'] ?? '') ?>" class="w-full border border-slate-300 p-2 text-xs focus:outline-none font-mono">
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModalProfil('modalEditProfil')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="bg-[#1e3a8a] text-white hover:bg-blue-800 px-4 py-2 rounded-sm font-bold uppercase tracking-wider">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ganti Password -->
<div id="modalGantiPassword" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel max-h-[92vh] w-full max-w-md overflow-y-auto rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-slate-100 flex justify-between items-center">
            <h2 class="text-base font-bold text-slate-800 uppercase tracking-wide">Ganti Password Akun</h2>
            <button onclick="closeModalProfil('modalGantiPassword')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formGantiPassword" onsubmit="submitGantiPassword(event)" class="p-6 space-y-4 text-xs">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Password Saat Ini <span class="text-red-500">*</span></label>
                <input type="password" name="current_password" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Password Baru <span class="text-red-500">*</span></label>
                <input type="password" name="new_password" minlength="8" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Ulangi Password Baru <span class="text-red-500">*</span></label>
                <input type="password" name="confirm_password" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModalProfil('modalGantiPassword')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="bg-slate-800 text-white hover:bg-slate-900 px-4 py-2 rounded-sm font-bold uppercase tracking-wider">
                    Perbarui Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function getCsrfProfil() {
    return document.getElementById('csrfTokenProfil').value;
}

function openModalEditProfil() {
    document.getElementById('modalEditProfil').classList.remove('hidden');
}

function openModalGantiPassword() {
    document.getElementById('formGantiPassword').reset();
    document.getElementById('modalGantiPassword').classList.remove('hidden');
}

function closeModalProfil(id) {
    document.getElementById(id).classList.add('hidden');
}

function submitEditProfil(e) {
    e.preventDefault();
    const form = document.getElementById('formEditProfil');
    const formData = new FormData(form);
    formData.append('csrf_token', getCsrfProfil());

    fetch('api/ProfilController.php?action=update_profile', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            appNotify(res.message, 'success');
            closeModalProfil('modalEditProfil');
            setTimeout(() => location.reload(), 700);
        } else {
            appNotify(res.message || 'Gagal memperbarui profil', 'error');
        }
    })
    .catch(err => appNotify('Error: ' + err.message, 'error'));
}

function submitGantiPassword(e) {
    e.preventDefault();
    const form = document.getElementById('formGantiPassword');
    const formData = new FormData(form);
    formData.append('csrf_token', getCsrfProfil());

    fetch('api/ProfilController.php?action=change_password', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            appNotify(res.message, 'success');
            closeModalProfil('modalGantiPassword');
            document.getElementById('formGantiPassword').reset();
        } else {
            appNotify(res.message || 'Gagal mengganti password', 'error');
        }
    })
    .catch(err => appNotify('Error: ' + err.message, 'error'));
}
</script>
