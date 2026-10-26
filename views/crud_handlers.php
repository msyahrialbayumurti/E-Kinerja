<?php
/**
 * views/crud_handlers.php - JavaScript AJAX Handlers untuk Matriks Penilaian & Eviden
 * Menghubungkan RhkController, EvidenController, dan FeedbackController
 */
?>
<script>
// ==================== GLOBAL CSRF & UTILS ====================
function getCsrfToken() {
    const el = document.getElementById('csrfToken');
    return el ? el.value : '';
}

function showToast(msg, type = 'success') {
    if (typeof appNotify === 'function') {
        appNotify(msg, type);
        return;
    }

    const toast = document.createElement('div');
    const color = type === 'success' ? 'bg-emerald-700' : (type === 'error' ? 'bg-red-700' : 'bg-slate-800');
    toast.className = `fixed bottom-5 right-5 z-50 text-white px-4 py-3 rounded-sm text-xs font-semibold shadow-lg flex items-center gap-2 transition-all ${color}`;
    const message = document.createElement('span');
    message.textContent = msg;
    toast.appendChild(message);
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('hidden');
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

// Close on background click
window.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('bg-black/50')) {
        e.target.classList.add('hidden');
    }
});


// ==================== ADMIN CRUD HANDLERS ====================

// 1. Tambah RHK Baru
function openModalTambahRhk() {
    const form = document.getElementById('formTambahRhk');
    if (form) form.reset();
    openModal('modalTambahRhk');
}

function submitTambahRhk(e) {
    e.preventDefault();
    const form = document.getElementById('formTambahRhk');
    const formData = new FormData(form);
    formData.append('csrf_token', getCsrfToken());

    fetch('api/RhkController.php?action=create_rhk', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modalTambahRhk');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(res.message || 'Gagal membuat RHK', 'error');
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

// 2. Edit RHK
function editRhk(id) {
    fetch(`api/RhkController.php?action=get_rhk&id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                const rhk = res.data;
                document.getElementById('editRhkId').value = rhk.id;
                document.getElementById('editRhkPimpinan').value = rhk.rhk_pimpinan || '';
                document.getElementById('editRhkIndividu').value = rhk.rhk_individu || '';
                document.getElementById('editRhkRencanaAksi').value = rhk.rencana_aksi || '';
                openModal('modalEditRhk');
            } else {
                showToast(res.message || 'Gagal memuat RHK', 'error');
            }
        })
        .catch(err => showToast('Error: ' + err.message, 'error'));
}

function submitEditRhk(e) {
    e.preventDefault();
    const form = document.getElementById('formEditRhk');
    const formData = new FormData(form);
    formData.append('csrf_token', getCsrfToken());

    fetch('api/RhkController.php?action=update_rhk', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modalEditRhk');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(res.message || 'Gagal memperbarui RHK', 'error');
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

// 3. Hapus RHK
function deleteRhk(id) {
    if (confirm('Apakah Anda yakin ingin menghapus RHK ini? Semua aspek indikator, bukti dukung, dan penilaian yang melekat akan ikut terhapus.')) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', getCsrfToken());

        fetch('api/RhkController.php?action=delete_rhk', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(res.message || 'Gagal menghapus RHK', 'error');
            }
        })
        .catch(err => showToast('Error: ' + err.message, 'error'));
    }
}

// 4. Tambah / Edit Indikator
function tambahIndikator(rhk_id) {
    document.getElementById('modalIndikatorTitle').textContent = 'Tambah Aspek & Target Kinerja';
    document.getElementById('formIndikator').reset();
    document.getElementById('indikatorId').value = '';
    document.getElementById('indikatorRhkId').value = rhk_id;
    openModal('modalIndikator');
}

function editIndikator(id) {
    fetch(`api/RhkController.php?action=get_indikator&id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                const ind = res.data;
                document.getElementById('modalIndikatorTitle').textContent = 'Edit Aspek & Target Kinerja';
                document.getElementById('indikatorId').value = ind.id;
                document.getElementById('indikatorRhkId').value = ind.rhk_id;
                document.getElementById('indikatorAspek').value = ind.aspek;
                document.getElementById('indikatorDeskripsi').value = ind.deskripsi_indikator;
                document.getElementById('indikatorTarget').value = ind.target;
                openModal('modalIndikator');
            } else {
                showToast(res.message || 'Gagal memuat indikator', 'error');
            }
        })
        .catch(err => showToast('Error: ' + err.message, 'error'));
}

function submitIndikator(e) {
    e.preventDefault();
    const form = document.getElementById('formIndikator');
    const formData = new FormData(form);
    formData.append('csrf_token', getCsrfToken());

    fetch('api/RhkController.php?action=save_indikator', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modalIndikator');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(res.message || 'Gagal menyimpan indikator', 'error');
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

function deleteIndikator(id) {
    if (confirm('Hapus indikator aspek ini beserta seluruh bukti dukung di dalamnya?')) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', getCsrfToken());

        fetch('api/RhkController.php?action=delete_indikator', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(res.message || 'Gagal menghapus indikator', 'error');
            }
        })
        .catch(err => showToast('Error: ' + err.message, 'error'));
    }
}


// ==================== GURU CRUD HANDLERS ====================

// 1. Upload Bukti Dukung
function openModalUploadBukti(indikator_id) {
    const form = document.getElementById('formUploadBukti');
    if (form) form.reset();
    document.getElementById('uploadIndikatorId').value = indikator_id;
    openModal('modalUploadBukti');
}

function submitUploadBukti(e) {
    e.preventDefault();
    const form = document.getElementById('formUploadBukti');
    const btn = document.getElementById('btnUploadBuktiSubmit');
    const formData = new FormData(form);
    formData.append('csrf_token', getCsrfToken());

    btn.disabled = true;
    btn.innerHTML = '<span>Mengunggah...</span>';

    fetch('api/EvidenController.php?action=upload', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<span>Unggah Bukti</span>';
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modalUploadBukti');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(res.message || 'Gagal mengunggah bukti dukung', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<span>Unggah Bukti</span>';
        showToast('Error: ' + err.message, 'error');
    });
}

// 2. Delete Bukti Dukung
function deleteBukti(id) {
    if (confirm('Apakah Anda yakin ingin menghapus dokumen bukti dukung ini?')) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', getCsrfToken());

        fetch('api/EvidenController.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(res.message || 'Gagal menghapus bukti dukung', 'error');
            }
        })
        .catch(err => showToast('Error: ' + err.message, 'error'));
    }
}


// ==================== KETUA YAYASAN CRUD HANDLERS ====================

// 1. Edit Realisasi
function openModalRealisasi(rhk_id, currentText = '') {
    document.getElementById('realisasiRhkId').value = rhk_id;
    document.getElementById('realisasiText').value = currentText;
    openModal('modalEditRealisasi');
}

function submitEditRealisasi(e) {
    e.preventDefault();
    const form = document.getElementById('formEditRealisasi');
    const formData = new FormData(form);
    formData.append('csrf_token', getCsrfToken());

    fetch('api/FeedbackController.php?action=save_penilaian', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modalEditRealisasi');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(res.message || 'Gagal menyimpan realisasi', 'error');
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

// 2. Feedback Berkelanjutan
function openModalFeedback(rhk_id) {
    document.getElementById('formFeedback').reset();
    document.getElementById('feedbackRhkId').value = rhk_id;

    // Fetch existing penilaian/feedback
    fetch(`api/FeedbackController.php?action=get_penilaian&rhk_id=${rhk_id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                const d = res.data;
                document.getElementById('modalFeedbackTitle').textContent = 'Edit Umpan Balik Kinerja';
                document.getElementById('feedbackTeks').value = d.feedback_teks || '';
                if (d.feedback_simbol) {
                    const radio = document.querySelector(`input[name="feedback_simbol"][value="${d.feedback_simbol}"]`);
                    if (radio) radio.checked = true;
                }
            } else {
                document.getElementById('modalFeedbackTitle').textContent = 'Tambah Umpan Balik Kinerja';
            }
            openModal('modalFeedback');
        })
        .catch(() => openModal('modalFeedback'));
}

function submitFeedback(e) {
    e.preventDefault();
    const form = document.getElementById('formFeedback');
    const formData = new FormData(form);
    formData.append('csrf_token', getCsrfToken());

    fetch('api/FeedbackController.php?action=save_penilaian', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modalFeedback');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(res.message || 'Gagal menyimpan feedback', 'error');
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

function deleteFeedback(rhk_id) {
    if (confirm('Hapus umpan balik kinerja pada RHK ini?')) {
        const formData = new FormData();
        formData.append('rhk_id', rhk_id);
        formData.append('type', 'feedback');
        formData.append('csrf_token', getCsrfToken());

        fetch('api/FeedbackController.php?action=delete_feedback', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(res.message || 'Gagal menghapus feedback', 'error');
            }
        })
        .catch(err => showToast('Error: ' + err.message, 'error'));
    }
}
</script>
