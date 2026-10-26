function getCsrfSkp() {
    return document.getElementById('csrfTokenSkp').value;
}

function openModalTambahSKP() {
    document.getElementById('modalTambahSKP').classList.remove('hidden');
}

function closeModalTambahSKP() {
    document.getElementById('modalTambahSKP').classList.add('hidden');
}

function submitTambahSKP(e) {
    e.preventDefault();
    const form = document.getElementById('formTambahSKP');
    const formData = new FormData(form);
    formData.append('csrf_token', getCsrfSkp());

    fetch('api/SKPController.php?action=create', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            appNotify(res.message, 'success');
            closeModalTambahSKP();
            setTimeout(() => location.reload(), 700);
        } else {
            appNotify(res.message || 'Gagal membuat SKP', 'error');
        }
    })
    .catch(err => appNotify('Error: ' + err.message, 'error'));
}

function openModalEditSKP(id, tahun, mulai, selesai, status, pendekatan) {
    document.getElementById('editSkpId').value = id;
    document.getElementById('editSkpMulai').value = mulai;
    document.getElementById('editSkpSelesai').value = selesai;
    document.getElementById('editSkpPendekatan').value = pendekatan;
    const statusEl = document.getElementById('editSkpStatus');
    if (statusEl) statusEl.value = status;
    document.getElementById('modalEditSKP').classList.remove('hidden');
}

function closeModalEditSKP() {
    document.getElementById('modalEditSKP').classList.add('hidden');
}

function submitEditSKP(e) {
    e.preventDefault();
    const form = document.getElementById('formEditSKP');
    const formData = new FormData(form);
    formData.append('csrf_token', getCsrfSkp());

    fetch('api/SKPController.php?action=update', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            appNotify(res.message, 'success');
            closeModalEditSKP();
            setTimeout(() => location.reload(), 700);
        } else {
            appNotify(res.message || 'Gagal memperbarui SKP', 'error');
        }
    })
    .catch(err => appNotify('Error: ' + err.message, 'error'));
}

function openModalStatus(id, currentStatus, nama) {
    document.getElementById('statusSkpId').value = id;
    document.getElementById('statusSelect').value = currentStatus;
    document.getElementById('statusPegawaiNama').textContent = nama;
    document.getElementById('modalStatus').classList.remove('hidden');
}

function closeModalStatus() {
    document.getElementById('modalStatus').classList.add('hidden');
}

function submitStatus(e) {
    e.preventDefault();
    const form = document.getElementById('formStatus');
    const formData = new FormData(form);
    formData.append('csrf_token', getCsrfSkp());

    fetch('api/SKPController.php?action=update_status', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            appNotify(res.message, 'success');
            closeModalStatus();
            setTimeout(() => location.reload(), 700);
        } else {
            appNotify(res.message || 'Gagal memperbarui status', 'error');
        }
    })
    .catch(err => appNotify('Error: ' + err.message, 'error'));
}

function hapusSKP(id) {
    if (confirm('Hapus SKP ini? Semua data butir RHK, bukti dukung, dan penilaian di dalamnya akan terhapus.')) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', getCsrfSkp());

        fetch('api/SKPController.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                appNotify(res.message, 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                appNotify(res.message || 'Gagal menghapus SKP', 'error');
            }
        })
        .catch(err => appNotify('Error: ' + err.message, 'error'));
    }
}
