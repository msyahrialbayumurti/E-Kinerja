<?php
/**
 * api/ProfilController.php - Profile Management Operations
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

require_login();

$user_id = $_SESSION['user_id'] ?? 0;
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$response = [
    'success' => false,
    'message' => 'Action tidak valid',
    'data' => null
];

try {
    // 1. UPDATE PROFILE CONTACT
    if ($action === 'update_profile') {
        if ($method !== 'POST') throw new Exception('Method not allowed');
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) throw new Exception('CSRF token invalid');

        $email = trim($_POST['email'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $nama = trim($_POST['nama'] ?? '');

        $stmt = $pdo->prepare("
            UPDATE users 
            SET email = ?, no_hp = ?, nama = IF(? != '', ?, nama)
            WHERE id = ?
        ");
        $stmt->execute([$email, $no_hp, $nama, $nama, $user_id]);

        if (!empty($nama)) {
            $_SESSION['nama'] = $nama;
        }

        $response = [
            'success' => true,
            'message' => 'Data profil berhasil diperbarui'
        ];
    }

    // 2. CHANGE PASSWORD
    elseif ($action === 'change_password') {
        if ($method !== 'POST') throw new Exception('Method not allowed');
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) throw new Exception('CSRF token invalid');

        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password)) {
            throw new Exception('Password saat ini dan password baru wajib diisi');
        }
        if (strlen($new_password) < 8) {
            throw new Exception('Password baru minimal 8 karakter');
        }
        if ($new_password !== $confirm_password) {
            throw new Exception('Konfirmasi password baru tidak cocok');
        }

        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($current_password, $row['password'])) {
            throw new Exception('Password saat ini salah');
        }

        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->execute([$new_hash, $user_id]);

        $_SESSION['auth_fingerprint'] = auth_fingerprint($new_hash);
        session_regenerate_id(true);

        $response = [
            'success' => true,
            'message' => 'Password berhasil diperbarui'
        ];
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
