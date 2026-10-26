<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

require_login();

$role = $_SESSION['role'] ?? 'guest';
$user_id = (int) ($_SESSION['user_id'] ?? 0);
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$response = ['success' => false, 'message' => 'Action tidak valid', 'data' => null];

if (!in_array($role, ['admin', 'ketua_yayasan'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak: hanya Administrator dan Ketua Yayasan yang berhak memverifikasi PAK', 'data' => null]);
    exit;
}

try {
    if ($method !== 'POST') {
        http_response_code(405);
        throw new Exception('Method not allowed');
    }

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        throw new Exception('Token CSRF tidak valid atau sesi telah berakhir');
    }

    if ($action !== 'update_status') {
        throw new Exception('Action tidak valid');
    }

    $id = intval($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $catatan = trim($_POST['catatan'] ?? '');

    if ($id <= 0) {
        throw new Exception('ID persetujuan PAK tidak valid');
    }
    if (!in_array($status, ['disetujui', 'ditolak', 'revisi'], true)) {
        throw new Exception('Status verifikasi tidak valid');
    }
    if ($status !== 'disetujui' && $catatan === '') {
        throw new Exception('Catatan wajib diisi untuk status ditolak atau revisi');
    }

    $chk = $pdo->prepare("SELECT id FROM persetujuan_pak WHERE id = ?");
    $chk->execute([$id]);
    if (!$chk->fetch()) {
        throw new Exception('Data persetujuan PAK tidak ditemukan');
    }

    $stmt = $pdo->prepare("
        UPDATE persetujuan_pak
        SET status = ?, catatan = ?, disetujui_oleh = ?, tanggal_persetujuan = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$status, $catatan !== '' ? $catatan : null, $user_id, $id]);

    $response = [
        'success' => true,
        'message' => 'Status persetujuan PAK berhasil diperbarui',
        'data' => ['id' => $id, 'status' => $status],
    ];
} catch (Throwable $e) {
    $response = ['success' => false, 'message' => api_error_message($e), 'data' => null];
}

echo json_encode($response);
exit;
