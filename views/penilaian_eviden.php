<?php
/**
 * views/penilaian_eviden.php - Matriks Penilaian & Eviden Multi-Role (8 Kolom Sesuai AGENTS.md)
 * BKN & PermenPANRB No. 6 Tahun 2022
 */

require __DIR__ . '/data/penilaian_eviden_data.php';
?>

<!-- CSRF Token -->
<input type="hidden" id="csrfToken" value="<?= $csrf_token ?>">

<!-- Header Page -->
<div class="mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Matriks Penilaian & Eviden Kinerja</h1>
        <p class="text-xs text-slate-500 mt-0.5">Penetapan RHK, Target Kinerja, Pengunggahan Bukti Dukung, serta Realisasi & Feedback Berkelanjutan</p>
    </div>

    <!-- Right Actions -->
    <?php if ($role === 'admin' && $current_skp_id > 0): ?>
    <div class="flex w-full items-center gap-2 md:w-auto">
        <button onclick="openModalTambahRhk()" class="mobile-action bg-[#1e3a8a] text-white hover:bg-blue-800 px-3.5 py-2 text-xs font-bold uppercase tracking-wider rounded-sm shadow-sm flex items-center gap-1.5 transition-colors md:w-auto">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Tambah RHK Baru
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Ringkasan Metrik -->
<div class="mb-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Butir RHK</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $total_rhk ?></div>
        </div>
    </div>
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h10"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Indikator</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $total_indikator ?></div>
        </div>
    </div>
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Bukti Dukung</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $total_eviden ?></div>
        </div>
    </div>
    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.98 10.1c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        </div>
        <div class="min-w-0">
            <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500">Sudah Dinilai</div>
            <div class="text-xl font-bold leading-tight text-slate-900"><?= $total_dinilai ?><span class="text-xs font-medium text-slate-400">/<?= $total_rhk ?></span></div>
        </div>
    </div>
</div>

<!-- Filter & Selector Bar -->
<div class="mb-6 flex flex-col gap-4 rounded-sm border border-slate-300 bg-white p-4 shadow-sm lg:flex-row lg:items-end lg:justify-between">
    <div class="flex w-full flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:gap-4 lg:w-auto">
        <!-- Selector Pegawai (Admin & Ketua Yayasan) -->
        <?php if ($role === 'admin' || $role === 'ketua_yayasan'): ?>
        <div class="flex w-full flex-col sm:w-auto">
            <label class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Pilih Pegawai / Guru:</label>
            <select onchange="location.href='index.php?page=penilaian_eviden&pegawai_id=' + this.value" class="w-full text-xs font-semibold border border-slate-300 px-3 py-1.5 bg-white text-slate-800 focus:outline-none focus:border-slate-500 sm:w-auto sm:min-w-[240px]">
                <?php foreach ($list_pegawai as $lp): ?>
                <option value="<?= $lp['id'] ?>" <?= $lp['id'] == $selected_pegawai_id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lp['nama']) ?> (<?= htmlspecialchars($lp['nip']) ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="h-8 border-l border-slate-200 hidden md:block"></div>
        <?php endif; ?>

        <!-- Selector Periode SKP -->
        <div class="flex w-full flex-col sm:w-auto">
            <label class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Periode SKP:</label>
            <select onchange="location.href='index.php?page=penilaian_eviden&pegawai_id=<?= $selected_pegawai_id ?>&skp_id=' + this.value" class="w-full text-xs font-semibold border border-slate-300 px-3 py-1.5 bg-white text-slate-800 focus:outline-none focus:border-slate-500 sm:w-auto sm:min-w-[200px]">
                <?php if (count($pegawai_skps) > 0): ?>
                    <?php foreach ($pegawai_skps as $pskp): ?>
                    <option value="<?= $pskp['id'] ?>" <?= $pskp['id'] == $current_skp_id ? 'selected' : '' ?>>
                        Tahun <?= htmlspecialchars($pskp['tahun_anggaran']) ?> (<?= ucfirst($pskp['status']) ?>)
                    </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="0">-- Belum ada SKP --</option>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <!-- Active Pegawai Summary Mini Card -->
    <?php if ($active_skp): ?>
    <div class="flex w-full flex-col gap-3 border-t border-slate-200 pt-3 text-xs sm:flex-row sm:items-center lg:w-auto lg:border-l lg:border-t-0 lg:pl-4 lg:pt-0">
        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center bg-slate-800 text-xs font-bold text-white">
            <?= strtoupper(substr($active_skp['nama'] ?? 'P', 0, 1)) ?>
        </div>
        <div class="min-w-0 flex-1">
            <div class="truncate font-bold text-slate-800"><?= htmlspecialchars($active_skp['nama']) ?></div>
            <div class="break-anywhere text-[11px] font-mono text-slate-500">NIP: <?= htmlspecialchars($active_skp['nip']) ?> | <?= htmlspecialchars($active_skp['jabatan'] ?? 'Guru') ?></div>
        </div>
        <span class="inline-block self-start whitespace-nowrap rounded-sm border px-2 py-0.5 text-[10px] font-bold uppercase sm:ml-2 sm:self-auto <?= $status_badge[$active_skp['status']]['bg'] ?? 'bg-slate-100' ?>">
            <?= $status_badge[$active_skp['status']]['label'] ?? $active_skp['status'] ?>
        </span>
    </div>
    <?php endif; ?>
</div>

<!-- Main 8-Column Matrix Table -->
<div class="mb-8 overflow-hidden rounded-sm border border-slate-300 bg-white shadow-sm">
    <div class="flex items-center justify-between gap-2 border-b border-slate-200 bg-slate-50 px-3 py-2 text-[10px] font-semibold text-slate-500 lg:hidden">
        <span>Geser tabel ke samping untuk melihat semua kolom.</span>
        <span aria-hidden="true">&harr;</span>
    </div>
    <div class="table-scroll overflow-x-auto">
        <table class="w-full text-left border-collapse table-compact min-w-[1280px]">
            <thead>
                <tr class="table-header border-b-2 border-slate-400 bg-slate-100">
                    <th class="w-8 text-center py-2.5">No</th>
                    <th class="w-56 py-2.5">RHK Pimpinan Diintervensi</th>
                    <th class="w-60 py-2.5">RHK Individu & Rencana Aksi</th>
                    <th class="w-52 py-2.5">Aspek & Indikator Kinerja</th>
                    <th class="w-24 text-center py-2.5">Target</th>
                    <th class="w-64 py-2.5">Bukti Dukung (Eviden)</th>
                    <th class="w-60 py-2.5">Realisasi (Capaian Riil)</th>
                    <th class="w-64 py-2.5">Umpan Balik (Ongoing Feedback)</th>
                </tr>
            </thead>
            <tbody class="text-xs text-slate-800 align-top divide-y divide-slate-200">
                <?php if (!empty($rhk_list)): ?>
                    <?php foreach ($rhk_list as $index => $rhk): 
                        $indikators = $rhk['indikators'];
                        $penilaian = $rhk['penilaian'];
                        $realisasiText = $penilaian['realisasi'] ?? '';
                        $feedbackSimbol = $penilaian['feedback_simbol'] ?? '';
                        $feedbackTeks = $penilaian['feedback_teks'] ?? '';
                        $penilaiNama = $penilaian['penilai_nama'] ?? 'Atasan Penilai';
                    ?>
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <!-- 1. No -->
                        <td class="text-center font-bold text-slate-600 py-3 bg-slate-50/50">
                            <?= $index + 1 ?>
                        </td>

                        <!-- 2. RHK Pimpinan Diintervensi -->
                        <td class="py-3 px-3">
                            <div class="font-semibold text-slate-900 leading-snug">
                                <?= htmlspecialchars($rhk['rhk_pimpinan']) ?>
                            </div>
                            <?php if ($role === 'admin'): ?>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center gap-2">
                                <button onclick="editRhk(<?= $rhk['id'] ?>)" class="text-blue-700 hover:text-blue-900 font-bold text-[11px] flex items-center gap-1">
                                    ✏️ Edit RHK
                                </button>
                                <span class="text-slate-300">|</span>
                                <button onclick="deleteRhk(<?= $rhk['id'] ?>)" class="text-red-600 hover:text-red-800 font-bold text-[11px] flex items-center gap-1">
                                    🗑️ Hapus
                                </button>
                            </div>
                            <?php endif; ?>
                        </td>

                        <!-- 3. RHK Individu & Rencana Aksi -->
                        <td class="py-3 px-3">
                            <div class="font-semibold text-slate-900 leading-snug">
                                <?= htmlspecialchars($rhk['rhk_individu']) ?>
                            </div>
                            <?php if (!empty($rhk['rencana_aksi'])): ?>
                            <div class="mt-2 bg-slate-50 border border-slate-200 p-2 rounded-sm text-[11px] text-slate-600 leading-relaxed font-mono">
                                <div class="font-bold text-slate-700 mb-0.5 uppercase text-[9px] tracking-wider">Rencana Aksi:</div>
                                <?= nl2br(htmlspecialchars($rhk['rencana_aksi'])) ?>
                            </div>
                            <?php endif; ?>

                            <?php if ($role === 'admin'): ?>
                            <div class="mt-2.5 pt-2 border-t border-slate-100">
                                <button onclick="tambahIndikator(<?= $rhk['id'] ?>)" class="bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 px-2 py-1 text-[11px] font-bold rounded-sm inline-flex items-center gap-1">
                                    ➕ Tambah Aspek & Target
                                </button>
                            </div>
                            <?php endif; ?>
                        </td>

                        <!-- 4. Aspek & Indikator Kinerja -->
                        <td class="p-0">
                            <?php if (!empty($indikators)): ?>
                                <?php foreach ($indikators as $iIdx => $ind): 
                                    $aspekBadge = [
                                        'KUANTITAS' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'KUALITAS' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        'WAKTU' => 'bg-amber-100 text-amber-800 border-amber-200'
                                    ][$ind['aspek']] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                                ?>
                                <div class="px-3 py-2.5 <?= $iIdx > 0 ? 'border-t border-slate-200' : '' ?>">
                                    <span class="inline-block text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-sm border <?= $aspekBadge ?> mb-1">
                                        <?= htmlspecialchars($ind['aspek']) ?>
                                    </span>
                                    <div class="text-[11px] text-slate-800 leading-snug font-medium">
                                        <?= htmlspecialchars($ind['deskripsi_indikator']) ?>
                                    </div>

                                    <?php if ($role === 'admin'): ?>
                                    <div class="mt-1 flex items-center gap-1 text-[10px]">
                                        <button onclick="editIndikator(<?= $ind['id'] ?>)" class="text-blue-600 hover:underline">✏️ Edit</button>
                                        <span class="text-slate-300">·</span>
                                        <button onclick="deleteIndikator(<?= $ind['id'] ?>)" class="text-red-600 hover:underline">🗑️ Hapus</button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-3 text-slate-400 italic text-[11px]">Belum ada indikator aspek.</div>
                            <?php endif; ?>
                        </td>

                        <!-- 5. Target -->
                        <td class="p-0">
                            <?php if (!empty($indikators)): ?>
                                <?php foreach ($indikators as $iIdx => $ind): ?>
                                <div class="px-2 py-2.5 flex items-center justify-center text-center font-bold text-slate-800 text-xs <?= $iIdx > 0 ? 'border-t border-slate-200' : '' ?> min-h-[58px]">
                                    <?= htmlspecialchars($ind['target']) ?>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-3 text-center text-slate-400">-</div>
                            <?php endif; ?>
                        </td>

                        <!-- 6. Bukti Dukung (Eviden) -->
                        <td class="p-0">
                            <?php if (!empty($indikators)): ?>
                                <?php foreach ($indikators as $iIdx => $ind): ?>
                                <div class="p-2.5 <?= $iIdx > 0 ? 'border-t border-slate-200' : '' ?>">
                                    <!-- List Eviden Dokumen -->
                                    <?php if (!empty($ind['eviden'])): ?>
                                    <div class="space-y-1.5 mb-2">
                                        <?php foreach ($ind['eviden'] as $evd): ?>
                                        <div class="border border-slate-200 bg-slate-50 p-2 rounded-sm text-xs">
                                            <div class="flex items-start justify-between gap-1">
                                                <div class="flex items-start gap-1.5 min-w-0">
                                                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                                    </svg>
                                                    <div class="truncate">
                                                        <?php if (!empty($evd['file_path'])): ?>
                                                        <a href="uploads/eviden/<?= htmlspecialchars($evd['file_path']) ?>" target="_blank" class="text-blue-700 hover:underline font-bold text-[11px] block truncate" title="<?= htmlspecialchars($evd['original_name']) ?>">
                                                            <?= htmlspecialchars($evd['nama_label']) ?>
                                                        </a>
                                                        <div class="text-[10px] text-slate-500 truncate font-mono"><?= htmlspecialchars($evd['original_name']) ?></div>
                                                        <?php elseif (!empty($evd['link_eksternal'])): ?>
                                                        <?php $external_url = safe_external_url($evd['link_eksternal']); ?>
                                                        <?php if ($external_url !== ''): ?>
                                                        <a href="<?= htmlspecialchars($external_url, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:underline font-bold text-[11px] block truncate">
                                                            <?= htmlspecialchars($evd['nama_label']) ?> ↗
                                                        </a>
                                                        <div class="text-[10px] text-slate-500 truncate"><?= htmlspecialchars($external_url, ENT_QUOTES, 'UTF-8') ?></div>
                                                        <?php else: ?>
                                                        <div class="text-[10px] text-red-600">Tautan eksternal tidak valid.</div>
                                                        <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- Delete button for owner or admin -->
                                                <?php if ($evd['user_id'] == $user_id || $role === 'admin'): ?>
                                                <button onclick="deleteBukti(<?= $evd['id'] ?>)" class="text-red-500 hover:text-red-700 text-xs shrink-0 font-bold px-1" title="Hapus Bukti Dukung">
                                                    ✕
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Upload button for guru -->
                                    <?php if ($role === 'guru_pegawai' || $role === 'admin'): ?>
                                    <button onclick="openModalUploadBukti(<?= $ind['id'] ?>)" class="w-full bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 py-1 px-2 text-[10px] font-bold rounded-sm flex items-center justify-center gap-1 transition-colors">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                        + Tambah Bukti Dukung
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-3 text-slate-400 italic text-[11px]">-</div>
                            <?php endif; ?>
                        </td>

                        <!-- 7. Realisasi (Capaian Riil) -->
                        <td class="py-3 px-3">
                            <div class="min-h-[70px] border border-slate-200 bg-white p-2.5 rounded-sm text-[11px] text-slate-800 leading-relaxed">
                                <?php if (!empty($realisasiText)): ?>
                                    <?= nl2br(htmlspecialchars($realisasiText)) ?>
                                <?php else: ?>
                                    <span class="text-slate-400 italic">Belum ada catatan realisasi capaian kerja.</span>
                                <?php endif; ?>
                            </div>

                            <!-- Ketua Yayasan action -->
                            <?php if ($role === 'ketua_yayasan' || $role === 'admin'): ?>
                            <div class="mt-2">
                                <button onclick="openModalRealisasi(<?= $rhk['id'] ?>, <?= htmlspecialchars(json_encode($realisasiText)) ?>)" class="bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 px-2.5 py-1 text-[11px] font-bold rounded-sm inline-flex items-center gap-1">
                                    ✏️ Isi / Edit Realisasi
                                </button>
                            </div>
                            <?php endif; ?>
                        </td>

                        <!-- 8. Feedback (Ongoing Feedback) -->
                        <td class="py-3 px-3">
                            <?php if (!empty($feedbackTeks) || !empty($feedbackSimbol)): 
                                $simbolBadge = [
                                    'jempol' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-300', 'icon' => '👍 Jempol', 'desc' => 'Sesuai ekspektasi'],
                                    'bintang' => ['bg' => 'bg-blue-100 text-blue-800 border-blue-300', 'icon' => '⭐ Bintang', 'desc' => 'Melampaui target'],
                                    'apresiasi' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-300', 'icon' => '🎯 Apresiasi', 'desc' => 'Penyelesaian rapi'],
                                    'catatan' => ['bg' => 'bg-orange-100 text-orange-800 border-orange-300', 'icon' => '⚠️ Catatan', 'desc' => 'Perlu perbaikan']
                                ][$feedbackSimbol] ?? ['bg' => 'bg-slate-100 text-slate-800 border-slate-300', 'icon' => '💬 Catatan', 'desc' => ''];
                            ?>
                            <div class="border border-amber-300 bg-amber-50/30 p-2.5 rounded-sm shadow-sm text-xs">
                                <div class="flex items-center justify-between gap-1 mb-1.5">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase px-2 py-0.5 rounded-sm border <?= $simbolBadge['bg'] ?>">
                                        <?= $simbolBadge['icon'] ?>
                                    </span>
                                    <span class="text-[10px] text-slate-500 font-medium">oleh <?= htmlspecialchars($penilaiNama) ?></span>
                                </div>
                                <p class="text-slate-800 italic text-[11px] leading-relaxed">
                                    "<?= htmlspecialchars($feedbackTeks) ?>"
                                </p>

                                <?php if ($role === 'ketua_yayasan' || $role === 'admin'): ?>
                                <div class="mt-2 pt-1.5 border-t border-amber-200/60 flex items-center gap-2">
                                    <button onclick="openModalFeedback(<?= $rhk['id'] ?>)" class="text-blue-700 hover:underline font-bold text-[10px]">
                                        ✏️ Edit Feedback
                                    </button>
                                    <span class="text-slate-300">·</span>
                                    <button onclick="deleteFeedback(<?= $rhk['id'] ?>)" class="text-red-600 hover:underline font-bold text-[10px]">
                                        🗑️ Hapus
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <div class="border border-slate-200 bg-slate-50 p-2.5 rounded-sm text-slate-400 italic text-[11px]">
                                Belum ada umpan balik dari pimpinan.
                            </div>
                            <?php if ($role === 'ketua_yayasan' || $role === 'admin'): ?>
                            <div class="mt-2">
                                <button onclick="openModalFeedback(<?= $rhk['id'] ?>)" class="w-full bg-amber-100 hover:bg-amber-200 border border-amber-300 text-amber-900 py-1 px-2 text-[11px] font-bold rounded-sm transition-colors flex items-center justify-center gap-1">
                                    ➕ Beri Feedback
                                </button>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-10 text-slate-500">
                            <div class="text-sm font-semibold mb-1">Belum ada Butir RHK pada SKP ini</div>
                            <?php if ($role === 'admin'): ?>
                            <p class="text-xs text-slate-400 mb-3">Sebagai Administrator, Anda dapat mulai menambahkan butir RHK Pimpinan dan Individu.</p>
                            <button onclick="openModalTambahRhk()" class="bg-[#1e3a8a] text-white hover:bg-blue-800 px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-sm inline-flex items-center gap-1.5">
                                + Tambah RHK Pertama
                            </button>
                            <?php else: ?>
                            <p class="text-xs text-slate-400">Hubungi Administrator untuk menyusun matriks rencana kinerja periode ini.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Include Modal Dialogs -->
<?php require_once __DIR__ . '/modals_penilaian.php'; ?>

<!-- Include AJAX CRUD Handlers -->
<?php require_once __DIR__ . '/crud_handlers.php'; ?>
