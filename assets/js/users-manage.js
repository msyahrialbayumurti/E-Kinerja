function openModalTambahUser() {
    document.getElementById('modalUserTitle').textContent = 'Tambah Pengguna Baru';
    document.getElementById('userAction').value = 'create_user';
    document.getElementById('userId').value = '';
    document.getElementById('formUser').reset();
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordNotice').textContent = '(wajib untuk user baru)';
    document.getElementById('modalUserForm').classList.remove('hidden');
}

function editUser(id) {
    fetch(`api/UserController.php?action=get_user&id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                const u = res.data;
                document.getElementById('modalUserTitle').textContent = 'Edit Data Pengguna';
                document.getElementById('userAction').value = 'update_user';
                document.getElementById('userId').value = u.id;
                document.getElementById('userNip').value = u.nip || '';
                document.getElementById('userUsername').value = u.username || '';
                document.getElementById('userNama').value = u.nama || '';
                document.getElementById('userRole').value = u.role || 'guru_pegawai';
                document.getElementById('userAtasanId').value = u.atasan_id || '';
                document.getElementById('userJabatan').value = u.jabatan || '';
                document.getElementById('userPangkat').value = u.pangkat_golongan || '';
                document.getElementById('userEmail').value = u.email || '';
                document.getElementById('userNoHp').value = u.no_hp || '';
                document.getElementById('userPassword').value = '';
                document.getElementById('userPassword').required = false;
                document.getElementById('passwordNotice').textContent = '(kosongkan jika tidak ingin mengubah password)';
                document.getElementById('modalUserForm').classList.remove('hidden');
            } else {
                appNotify(res.message || 'Gagal mengambil data user', 'error');
            }
        })
        .catch(err => appNotify('Error: ' + err.message, 'error'));
}

function closeModalUser() {
    document.getElementById('modalUserForm').classList.add('hidden');
}

function submitUserForm(e) {
    e.preventDefault();
    const form = document.getElementById('formUser');
    const formData = new FormData(form);
    const csrf = document.getElementById('csrfTokenUsers').value;
    formData.append('csrf_token', csrf);

    const action = document.getElementById('userAction').value;

    fetch(`api/UserController.php?action=${action}`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            closeModalUser();
            appNotify(res.message, 'success');
            setTimeout(() => location.reload(), 700);
        } else {
            appNotify(res.message || 'Gagal menyimpan pengguna', 'error');
        }
    })
    .catch(err => appNotify('Error: ' + err.message, 'error'));
}

function deleteUser(id, nama) {
    if (confirm(`Apakah Anda yakin ingin menghapus pengguna "${nama}"? Semua data terkait akan ikut terhapus.`)) {
        const csrf = document.getElementById('csrfTokenUsers').value;
        const formData = new FormData();
        formData.append('csrf_token', csrf);
        formData.append('id', id);

        fetch('api/UserController.php?action=delete_user', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                appNotify(res.message, 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                appNotify(res.message || 'Gagal menghapus pengguna', 'error');
            }
        })
        .catch(err => appNotify('Error: ' + err.message, 'error'));
    }
}
