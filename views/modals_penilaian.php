<?php
/**
 * views/modals_penilaian.php - Modal Dialogs Lengkap untuk Matriks Penilaian & Eviden Multi-Role
 * Sesuai AGENTS.md (Admin, Guru Pegawai, Ketua Yayasan)
 */
?>

<!-- ==================== ADMIN MODALS ==================== -->

<!-- Modal Tambah RHK Baru (Khusus Admin) -->
<div id="modalTambahRhk" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel flex max-h-[90vh] w-full max-w-2xl flex-col rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-slate-100 flex justify-between items-center flex-shrink-0">
            <h2 class="text-base font-bold text-slate-800 uppercase tracking-wide">➕ Tambah RHK Baru (Admin)</h2>
            <button onclick="closeModal('modalTambahRhk')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formTambahRhk" onsubmit="submitTambahRhk(event)" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            <input type="hidden" name="skp_id" value="<?= htmlspecialchars($current_skp_id ?? '') ?>">

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">RHK Pimpinan Yang Diintervensi <span class="text-red-500">*</span></label>
                <textarea name="rhk_pimpinan" rows="3" required placeholder="Tuliskan butir rencana hasil kerja pimpinan yang diintervensi pegawai..." class="w-full border border-slate-300 p-2 text-xs focus:outline-none focus:border-slate-500"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Rencana Hasil Kerja (RHK) Individu <span class="text-red-500">*</span></label>
                <textarea name="rhk_individu" rows="3" required placeholder="Tuliskan butir rencana hasil kerja individu pegawai..." class="w-full border border-slate-300 p-2 text-xs focus:outline-none focus:border-slate-500"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Rencana Aksi (Butir-butir aksi)</label>
                <textarea name="rencana_aksi" rows="3" placeholder="• Langkah aksi 1&#10;• Langkah aksi 2&#10;• Langkah aksi 3" class="w-full border border-slate-300 p-2 text-xs focus:outline-none focus:border-slate-500 font-mono"></textarea>
            </div>

            <div class="border-t border-slate-200 pt-3">
                <div class="font-bold text-slate-800 mb-2 uppercase text-[11px]">Indikator & Target Awal (Opsional)</div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Aspek</label>
                        <select name="aspek" class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
                            <option value="KUANTITAS">Kuantitas</option>
                            <option value="KUALITAS">Kualitas</option>
                            <option value="WAKTU">Waktu</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-semibold text-slate-600 mb-1">Tolak Ukur / Indikator</label>
                        <input type="text" name="deskripsi_indikator" placeholder="Contoh: Jumlah modul ajar yang diselesaikan" class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
                    </div>
                </div>
                <div class="mt-2">
                    <label class="block font-semibold text-slate-600 mb-1">Besaran Target</label>
                    <input type="text" name="target" placeholder="Contoh: 4 Modul / 100% / 6 Bulan" class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalTambahRhk')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="bg-[#1e3a8a] text-white hover:bg-blue-800 px-4 py-2 rounded-sm font-bold uppercase tracking-wider">
                    Simpan RHK
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit RHK (Admin) -->
<div id="modalEditRhk" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel flex max-h-[90vh] w-full max-w-2xl flex-col rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-slate-100 flex justify-between items-center flex-shrink-0">
            <h2 class="text-base font-bold text-slate-800 uppercase tracking-wide">✏️ Edit RHK & Rencana Aksi</h2>
            <button onclick="closeModal('modalEditRhk')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formEditRhk" onsubmit="submitEditRhk(event)" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            <input type="hidden" name="id" id="editRhkId" value="">

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">RHK Pimpinan Diintervensi <span class="text-red-500">*</span></label>
                <textarea name="rhk_pimpinan" id="editRhkPimpinan" rows="3" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none focus:border-slate-500"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">RHK Individu <span class="text-red-500">*</span></label>
                <textarea name="rhk_individu" id="editRhkIndividu" rows="3" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none focus:border-slate-500"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Rencana Aksi</label>
                <textarea name="rencana_aksi" id="editRhkRencanaAksi" rows="4" class="w-full border border-slate-300 p-2 text-xs focus:outline-none focus:border-slate-500 font-mono"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditRhk')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="bg-[#1e3a8a] text-white hover:bg-blue-800 px-4 py-2 rounded-sm font-bold uppercase tracking-wider">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah/Edit Aspek & Target (Admin) -->
<div id="modalIndikator" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel flex max-h-[90vh] w-full max-w-lg flex-col rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-slate-100 flex justify-between items-center flex-shrink-0">
            <h2 id="modalIndikatorTitle" class="text-base font-bold text-slate-800 uppercase tracking-wide">Tambah Aspek & Target</h2>
            <button onclick="closeModal('modalIndikator')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formIndikator" onsubmit="submitIndikator(event)" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            <input type="hidden" name="id" id="indikatorId" value="">
            <input type="hidden" name="rhk_id" id="indikatorRhkId" value="">

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Aspek Kinerja <span class="text-red-500">*</span></label>
                <select name="aspek" id="indikatorAspek" required class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
                    <option value="KUANTITAS">KUANTITAS (Jumlah / Volume Keluaran)</option>
                    <option value="KUALITAS">KUALITAS (Mutu / Standar Kelayakan)</option>
                    <option value="WAKTU">WAKTU (Ketepatan Durasi Pelaksanaan)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi Indikator / Tolak Ukur <span class="text-red-500">*</span></label>
                <textarea name="deskripsi_indikator" id="indikatorDeskripsi" rows="3" required placeholder="Contoh: Jumlah modul yang selesai dikembangkan..." class="w-full border border-slate-300 p-2 text-xs focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Besaran Target Kinerja <span class="text-red-500">*</span></label>
                <input type="text" name="target" id="indikatorTarget" required placeholder="Contoh: 2 Laporan / 100% / 3 Bulan" class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalIndikator')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="bg-[#1e3a8a] text-white hover:bg-blue-800 px-4 py-2 rounded-sm font-bold uppercase tracking-wider">
                    Simpan Indikator
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ==================== GURU MODALS ==================== -->

<!-- Modal Upload Bukti Dukung (Khusus Guru / Pegawai) -->
<div id="modalUploadBukti" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel flex max-h-[90vh] w-full max-w-lg flex-col rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-slate-100 flex justify-between items-center flex-shrink-0">
            <h2 class="text-base font-bold text-slate-800 uppercase tracking-wide">📤 Unggah Bukti Dukung / Eviden</h2>
            <button onclick="closeModal('modalUploadBukti')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formUploadBukti" onsubmit="submitUploadBukti(event)" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            <input type="hidden" name="indikator_id" id="uploadIndikatorId" value="">

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama / Label Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="nama_label" id="uploadNamaLabel" required placeholder="Contoh: Laporan Modul Pembelajaran Semester Genap" class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Berkas Dokumen (PDF, JPG, PNG - Maks. 5MB)</label>
                <input type="file" name="file_eviden" id="uploadFileEviden" accept=".pdf,.jpg,.jpeg,.png" class="w-full border border-slate-300 p-2 text-xs bg-slate-50">
                <p class="text-[11px] text-slate-500 mt-1">Diverifikasi secara biner (MIME type). Format: PDF, JPG, PNG maksimal 5 MB.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Link Tautan Eksternal (Opsional / Google Drive)</label>
                <input type="url" name="link_eksternal" id="uploadLinkEksternal" placeholder="https://drive.google.com/..." class="w-full border border-slate-300 p-2 text-xs focus:outline-none">
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalUploadBukti')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm font-semibold">
                    Batal
                </button>
                <button type="submit" id="btnUploadBuktiSubmit" class="bg-emerald-700 text-white hover:bg-emerald-800 px-4 py-2 rounded-sm font-bold uppercase tracking-wider flex items-center gap-1.5">
                    <span>Unggah Bukti</span>
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ==================== KETUA YAYASAN MODALS ==================== -->

<!-- Modal Edit Realisasi (Ketua Yayasan / Atasan) -->
<div id="modalEditRealisasi" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel flex max-h-[90vh] w-full max-w-lg flex-col rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-slate-100 flex justify-between items-center flex-shrink-0">
            <h2 class="text-base font-bold text-slate-800 uppercase tracking-wide">📝 Pengisian Capaian Realisasi</h2>
            <button onclick="closeModal('modalEditRealisasi')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formEditRealisasi" onsubmit="submitEditRealisasi(event)" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            <input type="hidden" name="rhk_id" id="realisasiRhkId" value="">

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Narasi Capaian Realisasi Riil Kerja <span class="text-red-500">*</span></label>
                <textarea name="realisasi" id="realisasiText" rows="5" required placeholder="Tuliskan narasi pencapaian kerja berdasarkan data dukung yang telah ditelaah..." class="w-full border border-slate-300 p-2.5 text-xs focus:outline-none focus:border-slate-500 leading-relaxed"></textarea>
                <p class="text-[11px] text-slate-500 mt-1">Nyatakan kuantitas, kualitas, dan rentang waktu penyelesaian riil yang dicapai pegawai.</p>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditRealisasi')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="bg-blue-800 text-white hover:bg-blue-900 px-4 py-2 rounded-sm font-bold uppercase tracking-wider">
                    Simpan Realisasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Beri/Edit Feedback Berkelanjutan (Ketua Yayasan / Atasan) -->
<div id="modalFeedback" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-panel flex max-h-[90vh] w-full max-w-lg flex-col rounded-sm border border-slate-300 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4 bg-amber-50 flex justify-between items-center flex-shrink-0">
            <h2 id="modalFeedbackTitle" class="text-base font-bold text-amber-900 uppercase tracking-wide">⭐ Umpan Balik Berkelanjutan (Ongoing Feedback)</h2>
            <button onclick="closeModal('modalFeedback')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <form id="formFeedback" onsubmit="submitFeedback(event)" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            <input type="hidden" name="rhk_id" id="feedbackRhkId" value="">

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-2">Pilih Simbol Visual Suasana Kinerja <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <label class="border border-slate-300 p-2.5 rounded-sm flex items-center gap-2 cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                        <input type="radio" name="feedback_simbol" value="jempol" class="text-amber-600 focus:ring-0">
                        <div>
                            <div class="font-bold text-slate-800">👍 Jempol</div>
                            <div class="text-[10px] text-slate-500 leading-tight">Sesuai ekspektasi pimpinan</div>
                        </div>
                    </label>
                    <label class="border border-slate-300 p-2.5 rounded-sm flex items-center gap-2 cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="feedback_simbol" value="bintang" class="text-blue-600 focus:ring-0">
                        <div>
                            <div class="font-bold text-slate-800">⭐ Bintang</div>
                            <div class="text-[10px] text-slate-500 leading-tight">Kinerja melampaui target</div>
                        </div>
                    </label>
                    <label class="border border-slate-300 p-2.5 rounded-sm flex items-center gap-2 cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="feedback_simbol" value="apresiasi" class="text-emerald-600 focus:ring-0">
                        <div>
                            <div class="font-bold text-slate-800">🎯 Apresiasi</div>
                            <div class="text-[10px] text-slate-500 leading-tight">Penyelesaian rapi & tuntas</div>
                        </div>
                    </label>
                    <label class="border border-slate-300 p-2.5 rounded-sm flex items-center gap-2 cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                        <input type="radio" name="feedback_simbol" value="catatan" class="text-orange-600 focus:ring-0">
                        <div>
                            <div class="font-bold text-slate-800">⚠️ Catatan</div>
                            <div class="text-[10px] text-slate-500 leading-tight">Perlu perbaikan / perhatian khusus</div>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Catatan Evaluasi Resmi Atasan <span class="text-red-500">*</span></label>
                <textarea name="feedback_teks" id="feedbackTeks" rows="4" required placeholder="Tuliskan catatan apresiasi, saran perbaikan, atau arahan tindak lanjut resmi pimpinan..." class="w-full border border-slate-300 p-2.5 text-xs focus:outline-none focus:border-amber-500 leading-relaxed"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalFeedback')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="bg-amber-600 text-white hover:bg-amber-700 px-4 py-2 rounded-sm font-bold uppercase tracking-wider">
                    Simpan Feedback
                </button>
            </div>
        </form>
    </div>
</div>
