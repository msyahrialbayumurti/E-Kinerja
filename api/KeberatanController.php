<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

require_login();

$role = $_SESSION['role'] ?? 'guest';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$response = ['success' => false, 'message' => 'Action tidak valid', 'data' => null];

if (!in_array($role, ['admin', 'ketua_yayasan'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak: hanya Administrator dan Ketua Yayasan yang berhak menindaklanjuti keberatan', 'data' => null]);
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
    $tanggapan = trim($_POST['tanggapan'] ?? '');

    if ($id <= 0) {
        throw new Exception('ID keberatan tidak valid');
    }
    if (!in_array($status, ['diproses', 'ditanggapi', 'ditolak'], true)) {
        throw new Exception('Status tindak lanjut tidak valid');
    }
    if (in_array($status, ['ditanggapi', 'ditolak'], true) && $tanggapan === '') {
        throw new Exception('Tanggapan wajib diisi untuk status ditanggapi atau ditolak');
    }

    $chk = $pdo->prepare("SELECT id FROM rekap_keberatan WHERE id = ?");
    $chk->execute([$id]);
    if (!$chk->fetch()) {
        throw new Exception('Data keberatan tidak ditemukan');
    }

    $stmt = $pdo->prepare("UPDATE rekap_keberatan SET status = ?, tanggapan = ? WHERE id = ?");
    $stmt->execute([$status, $tanggapan !== '' ? $tanggapan : null, $id]);

    $response = [
        'success' => true,
        'message' => 'Tindak lanjut keberatan berhasil disimpan',
        'data' => ['id' => $id, 'status' => $status],
    ];
} catch (Throwable $e) {
    $response = ['success' => false, 'message' => api_error_message($e), 'data' => null];
}

echo json_encode($response);
exit;
