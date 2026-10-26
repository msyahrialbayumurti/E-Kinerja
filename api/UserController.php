<?php
/**
 * api/UserController.php - Khusus Admin: CRUD Master Data User Pegawai & Pimpinan
 * Sesuai AGENTS.md
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

require_login();

// Only admin
if (!has_role('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak: Hanya Administrator yang berhak mengelola data pengguna']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$response = ['success' => false, 'message' => 'Action tidak valid', 'data' => null];

try {
    // 1. CREATE USER
    if ($action === 'create_user') {
        if ($method !== 'POST') throw new Exception('Method not allowed');
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) throw new Exception('CSRF token invalid');

        $nip = trim($_POST['nip'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $pangkat_golongan = trim($_POST['pangkat_golongan'] ?? '');
        $jabatan = trim($_POST['jabatan'] ?? '');
        $role = trim($_POST['role'] ?? 'guru_pegawai');
        $atasan_id = !empty($_POST['atasan_id']) ? intval($_POST['atasan_id']) : null;
        $email = trim($_POST['email'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');

        if (empty($nip) || empty($username) || empty($password) || empty($nama)) {
            throw new Exception('NIP, Username, Password, dan Nama Lengkap wajib diisi');
        }
        if (strlen($password) < 8) {
            throw new Exception('Password minimal 8 karakter');
        }

        if (!in_array($role, ['admin', 'ketua_yayasan', 'guru_pegawai'])) {
            throw new Exception('Role tidak valid');
        }

        // Check duplicate username or nip
        $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR nip = ?");
        $chk->execute([$username, $nip]);
        if ($chk->fetch()) {
            throw new Exception('Username atau NIP sudah terdaftar di sistem');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (nip, username, password, nama, pangkat_golongan, jabatan, role, atasan_id, email, no_hp, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$nip, $username, $hash, $nama, $pangkat_golongan, $jabatan, $role, $atasan_id, $email, $no_hp]);

        $response = [
            'success' => true,
            'message' => 'Pengguna baru berhasil ditambahkan',
            'data' => ['id' => (int)$pdo->lastInsertId()]
        ];
    }

    // 2. UPDATE USER
    elseif ($action === 'update_user') {
        if ($method !== 'POST') throw new Exception('Method not allowed');
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) throw new Exception('CSRF token invalid');

        $id = intval($_POST['id'] ?? 0);
        $nip = trim($_POST['nip'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $pangkat_golongan = trim($_POST['pangkat_golongan'] ?? '');
        $jabatan = trim($_POST['jabatan'] ?? '');
        $role = trim($_POST['role'] ?? 'guru_pegawai');
        $atasan_id = !empty($_POST['atasan_id']) ? intval($_POST['atasan_id']) : null;
        $email = trim($_POST['email'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($id <= 0 || empty($nip) || empty($username) || empty($nama)) {
            throw new Exception('ID, NIP, Username, dan Nama Lengkap wajib diisi');
        }
        if (!empty($password) && strlen($password) < 8) {
            throw new Exception('Password minimal 8 karakter');
        }

        // Check duplicate username or nip for other user
        $chk = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR nip = ?) AND id != ?");
        $chk->execute([$username, $nip, $id]);
        if ($chk->fetch()) {
            throw new Exception('Username atau NIP sudah digunakan pengguna lain');
        }

        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE users 
                SET nip = ?, username = ?, password = ?, nama = ?, pangkat_golongan = ?, jabatan = ?, role = ?, atasan_id = ?, email = ?, no_hp = ?
                WHERE id = ?
            ");
            $stmt->execute([$nip, $username, $hash, $nama, $pangkat_golongan, $jabatan, $role, $atasan_id, $email, $no_hp, $id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET nip = ?, username = ?, nama = ?, pangkat_golongan = ?, jabatan = ?, role = ?, atasan_id = ?, email = ?, no_hp = ?
                WHERE id = ?
            ");
            $stmt->execute([$nip, $username, $nama, $pangkat_golongan, $jabatan, $role, $atasan_id, $email, $no_hp, $id]);
        }

        if (!empty($password) && $id === (int) ($_SESSION['user_id'] ?? 0)) {
            $_SESSION['auth_fingerprint'] = auth_fingerprint($hash);
            session_regenerate_id(true);
        }

        $response = [
            'success' => true,
            'message' => 'Data pengguna berhasil diperbarui'
        ];
    }

    // 3. DELETE USER
    elseif ($action === 'delete_user') {
        if ($method !== 'POST') throw new Exception('Method not allowed');
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) throw new Exception('CSRF token invalid');

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('ID pengguna tidak valid');

        // Cannot delete self
        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            throw new Exception('Anda tidak dapat menghapus akun Anda sendiri');
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        $response = [
            'success' => true,
            'message' => 'Pengguna berhasil dihapus'
        ];
    }

    // 4. GET SINGLE USER
    elseif ($action === 'get_user') {
        $id = intval($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT id, nip, username, nama, pangkat_golongan, jabatan, role, atasan_id, email, no_hp FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) throw new Exception('Pengguna tidak ditemukan');

        $response = ['success' => true, 'data' => $data];
    }

} catch (Throwable $e) {
    $response = [
        'success' => false,
        'message' => api_error_message($e),
        'data' => null
    ];
}

echo json_encode($response);
exit;
