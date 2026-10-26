<?php
/**
 * views/skp_list.php - Daftar & Manajemen SKP Multi-Role
 * Admin: Master SKP semua pegawai | Guru: SKP Sendiri | Ketua Yayasan: Monitoring & Persetujuan
 */

require __DIR__ . '/data/skp_list_data.php';

$js_literal = static function ($value): string {
    $encoded = json_encode(
        $value,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
    );
    return $encoded === false ? 'null' : $encoded;
};
?>

<!-- CSRF Token -->
<input type="hidden" id="csrfTokenSkp" value="<?= $csrf_token ?>">

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            <?= $role === 'admin' ? 'Master Data Sasaran Kinerja Pegawai (SKP)' : ($role === 'ketua_yayasan' ? 'Monitoring & Persetujuan SKP Bawahan' : 'Daftar SKP Saya') ?>
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            <?= $role === 'ketua_yayasan' ? 'Verifikasi status persetujuan SKP dan evaluasi kinerja berkala pegawai' : 'Kelola periode tahunan Sasaran Kinerja Pegawai sesuai PermenPANRB No. 6 Tahun 2022' ?>
        </p>
    </div>
    <div class="w-full md:w-auto">
        <?php if ($role !== 'ketua_yayasan'): ?>
        <button onclick="openModalTambahSKP()" class="mobile-action bg-[#1e3a8a] text-white hover:bg-blue-800 px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-sm shadow-sm flex items-center gap-2 md:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Periode SKP
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Ringkasan Status SKP -->
<div class="mb-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Total SKP</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $count_total ?></div>
        </div>
    </div>
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Aktif</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $count_aktif ?></div>
        </div>
    </div>
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Draft / Menunggu</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $count_menunggu ?></div>
        </div>
    </div>
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Selesai / Dinilai</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $count_selesai ?></div>
        </div>
    </div>
</div>

<!-- Filter Bar for Admin / Ketua Yayasan -->
<?php if ($role === 'admin' || $role === 'ketua_yayasan'): ?>
<div class="mb-6 flex flex-col gap-3 rounded-sm border border-slate-300 bg-white p-3.5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center sm:gap-3">
        <span class="text-xs font-bold uppercase text-slate-700">Filter Pegawai:</span>
        <select onchange="location.href='index.php?page=skp_list&pegawai_id=' + this.value + '<?= $filter_tahun_param ?>'" class="w-full text-xs border border-slate-300 px-3 py-1.5 bg-white text-slate-800 focus:outline-none focus:border-slate-500 sm:w-auto sm:min-w-[260px]">
            <option value="0">-- Tampilkan Semua Pegawai --</option>
            <?php foreach ($list_pegawai as $lp): ?>
            <option value="<?= $lp['id'] ?>" <?= $lp['id'] == $filter_user_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($lp['nama']) ?> (<?= htmlspecialchars($lp['nip']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="text-xs text-slate-500">
        Total: <span class="font-bold text-slate-800"><?= count($skps) ?></span> Dokumen SKP
    </div>
</div>
<?php endif; ?>

<!-- Tabel Daftar SKP -->
<div class="bg-white border border-slate-300 rounded-sm shadow-sm overflow-hidden mb-8">
    <div class="table-scroll overflow-x-auto">
        <table class="w-full text-left border-collapse table-compact min-w-[1000px]">
            <thead>
                <tr class="table-header border-b border-slate-300 bg-slate-100">
                    <th class="w-10 text-center py-2.5">No</th>
                    <?php if ($role === 'admin' || $role === 'ketua_yayasan'): ?>
                    <th class="w-56 py-2.5">Pegawai / Guru</th>
                    <?php endif; ?>
                    <th class="w-48 py-2.5">Periode Pelaksanaan</th>
                    <th class="w-32 py-2.5">Tahun Anggaran</th>
                    <th class="w-36 py-2.5">Pendekatan</th>
                    <th class="w-44 py-2.5">Status SKP</th>
                    <th class="w-56 text-center py-2.5">Aksi & Matriks</th>
                </tr>
            </thead>
            <tbody class="text-xs text-slate-800 divide-y divide-slate-200">
                <?php if (count($skps) > 0): ?>
                    <?php foreach ($skps as $index => $skp): 
                        $badge = $status_badge[$skp['status']] ?? ['bg' => 'bg-slate-100 text-slate-700 border-slate-300', 'label' => ucfirst($skp['status'])];
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="text-center font-mono py-2.5"><?= $index + 1 ?></td>

                        <?php if ($role === 'admin' || $role === 'ketua_yayasan'): ?>
                        <td class="py-2.5">
                            <div class="font-bold text-slate-900"><?= htmlspecialchars($skp['nama'] ?? 'Pegawai') ?></div>
                            <div class="text-[11px] font-mono text-slate-500">NIP: <?= htmlspecialchars($skp['nip'] ?? '-') ?></div>
                        </td>
                        <?php endif; ?>

                        <td class="py-2.5 font-medium text-slate-800">
                            <?= date('d M Y', strtotime($skp['periode_mulai'])) ?> s.d. <?= date('d M Y', strtotime($skp['periode_selesai'])) ?>
                        </td>

                        <td class="py-2.5 font-bold font-mono text-slate-800">
                            <?= htmlspecialchars($skp['tahun_anggaran']) ?>
                        </td>

                        <td class="py-2.5">
                            <span class="inline-block px-2 py-0.5 text-[11px] font-semibold bg-slate-100 border border-slate-300 text-slate-700">
                                <?= htmlspecialchars($skp['pendekatan'] ?? 'Kuantitatif') ?>
                            </span>
                        </td>

                        <td class="py-2.5">
                            <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase rounded-sm border <?= $badge['bg'] ?>">
                                <?= $badge['label'] ?>
                            </span>
                        </td>

                        <td class="py-2.5 text-center">
                            <div class="flex items-center justify-center flex-wrap gap-1.5">
                                <a href="index.php?page=penilaian_eviden&pegawai_id=<?= $skp['user_id'] ?>&skp_id=<?= $skp['id'] ?>" class="bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 px-2 py-1 text-[11px] font-bold rounded-sm inline-flex items-center gap-1">
                                    📊 Matriks & Eviden
                                </a>

                                <?php if ($role === 'ketua_yayasan'): ?>
                                <button onclick='openModalStatus(<?= (int) $skp['id'] ?>, <?= $js_literal($skp['status']) ?>, <?= $js_literal($skp['nama']) ?>)' class="bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-300 px-2 py-1 text-[11px] font-bold rounded-sm">
                                    ⚙️ Status
                                </button>
                                <?php endif; ?>

                                <?php if ($role === 'admin'): ?>
                                <button onclick='openModalEditSKP(<?= (int) $skp['id'] ?>, <?= (int) $skp['tahun_anggaran'] ?>, <?= $js_literal($skp['periode_mulai']) ?>, <?= $js_literal($skp['periode_selesai']) ?>, <?= $js_literal($skp['status']) ?>, <?= $js_literal($skp['pendekatan']) ?>)' class="bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300 px-2 py-1 text-[11px] font-semibold rounded-sm">
                                    Edit
                                </button>
                                <button onclick="hapusSKP(<?= $skp['id'] ?>)" class="bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 px-2 py-1 text-[11px] font-semibold rounded-sm">
                                    Hapus
                                </button>
                                <?php elseif ($role === 'guru_pegawai' && $skp['status'] === 'draft'): ?>
                                <button onclick='openModalEditSKP(<?= (int) $skp['id'] ?>, <?= (int) $skp['tahun_anggaran'] ?>, <?= $js_literal($skp['periode_mulai']) ?>, <?= $js_literal($skp['periode_selesai']) ?>, <?= $js_literal($skp['status']) ?>, <?= $js_literal($skp['pendekatan']) ?>)' class="bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300 px-2 py-1 text-[11px] font-semibold rounded-sm">
                                    Edit
                                </button>
                                <button onclick="hapusSKP(<?= $skp['id'] ?>)" class="bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 px-2 py-1 text-[11px] font-semibold rounded-sm">
                                    Hapus
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-500 italic">
                            Belum ada dokumen SKP pada kriteria ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/partials/skp_list_modals.php'; ?>

<script src="assets/js/skp-list.js"></script>
