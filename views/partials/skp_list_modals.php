<!-- Modal Tambah SKP -->
<div id="modalTambahSKP" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel max-h-[92vh] w-full max-w-md overflow-y-auto rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-slate-100 flex justify-between items-center">
            <h2 class="text-base font-bold text-slate-800 uppercase tracking-wide">Tambah Periode SKP</h2>
            <button onclick="closeModalTambahSKP()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formTambahSKP" onsubmit="submitTambahSKP(event)" class="p-6 space-y-4 text-xs">
            <?php if ($role === 'admin'): ?>
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Pegawai <span class="text-red-500">*</span></label>
                <select name="user_id" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
                    <?php foreach ($list_pegawai as $lp): ?>
                    <option value="<?= $lp['id'] ?>"><?= htmlspecialchars($lp['nama']) ?> (NIP: <?= htmlspecialchars($lp['nip']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Tahun Anggaran <span class="text-red-500">*</span></label>
                <input type="number" name="tahun_anggaran" value="<?= $filter_tahun > 0 ? (int) $filter_tahun : date('Y') ?>" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none font-mono">
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Periode Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="periode_mulai" value="<?= date('Y-01-01') ?>" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Periode Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="periode_selesai" value="<?= date('Y-12-31') ?>" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none font-mono">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Pendekatan</label>
                <select name="pendekatan" class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
                    <option value="Kuantitatif">Kuantitatif (PermenPANRB 6/2022)</option>
                    <option value="Kualitatif">Kualitatif</option>
                </select>
            </div>

            <?php if ($role === 'admin'): ?>
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Status Awal SKP</label>
                <select name="status" class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
                    <option value="aktif">Aktif</option>
                    <option value="draft">Draft</option>
                    <option value="persetujuan">Menunggu Persetujuan</option>
                </select>
            </div>
            <?php endif; ?>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModalTambahSKP()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="bg-[#1e3a8a] text-white hover:bg-blue-800 px-4 py-2 rounded-sm font-bold uppercase tracking-wider">
                    Simpan SKP
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit SKP -->
<div id="modalEditSKP" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel max-h-[92vh] w-full max-w-md overflow-y-auto rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-slate-100 flex justify-between items-center">
            <h2 class="text-base font-bold text-slate-800 uppercase tracking-wide">Edit SKP</h2>
            <button onclick="closeModalEditSKP()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formEditSKP" onsubmit="submitEditSKP(event)" class="p-6 space-y-4 text-xs">
            <input type="hidden" name="id" id="editSkpId">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Periode Mulai</label>
                    <input type="date" name="periode_mulai" id="editSkpMulai" required class="w-full border border-slate-300 p-2 text-xs font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Periode Selesai</label>
                    <input type="date" name="periode_selesai" id="editSkpSelesai" required class="w-full border border-slate-300 p-2 text-xs font-mono">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Pendekatan</label>
                <select name="pendekatan" id="editSkpPendekatan" class="w-full border border-slate-300 p-2 text-xs">
                    <option value="Kuantitatif">Kuantitatif</option>
                    <option value="Kualitatif">Kualitatif</option>
                </select>
            </div>

            <?php if ($role === 'admin'): ?>
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Status SKP</label>
                <select name="status" id="editSkpStatus" class="w-full border border-slate-300 p-2 text-xs">
                    <option value="draft">Draft</option>
                    <option value="persetujuan">Menunggu Persetujuan</option>
                    <option value="aktif">Aktif</option>
                    <option value="selesai">Selesai</option>
                    <option value="dinilai">Dinilai</option>
                </select>
            </div>
            <?php endif; ?>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModalEditSKP()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="bg-[#1e3a8a] text-white hover:bg-blue-800 px-4 py-2 rounded-sm font-bold uppercase tracking-wider">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ubah Status Persetujuan (Ketua Yayasan) -->
<div id="modalStatus" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel max-h-[92vh] w-full max-w-sm overflow-y-auto rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-amber-50 flex justify-between items-center">
            <h2 class="text-base font-bold text-amber-900 uppercase tracking-wide">Ubah Status SKP</h2>
            <button onclick="closeModalStatus()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formStatus" onsubmit="submitStatus(event)" class="p-6 space-y-4 text-xs">
            <input type="hidden" name="id" id="statusSkpId">

            <div>
                <div class="text-slate-600 mb-2">Pegawai: <strong id="statusPegawaiNama" class="text-slate-900"></strong></div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Status Baru <span class="text-red-500">*</span></label>
                <select name="status" id="statusSelect" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
                    <option value="persetujuan">Menunggu Persetujuan</option>
                    <option value="aktif">Aktif (Setujui SKP)</option>
                    <option value="draft">Kembalikan ke Draft (Revisi)</option>
                    <option value="selesai">Selesai</option>
                    <option value="dinilai">Dinilai Lengkap</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModalStatus()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="bg-amber-600 text-white hover:bg-amber-700 px-4 py-2 rounded-sm font-bold uppercase tracking-wider">
                    Perbarui Status
                </button>
            </div>
        </form>
    </div>
</div>
