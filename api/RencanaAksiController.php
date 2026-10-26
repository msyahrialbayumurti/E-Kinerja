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

if (!in_array($role, ['admin', 'guru_pegawai'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak: hanya Administrator dan Guru/Pegawai yang berhak mengelola rencana aksi', 'data' => null]);
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

    if ($action === 'save') {
        $id = intval($_POST['id'] ?? 0);
        $target_user_id = $role === 'admin' ? intval($_POST['user_id'] ?? 0) : $user_id;
        $triwulan = intval($_POST['triwulan'] ?? 0);
        $rhk_indikator = trim($_POST['rhk_indikator'] ?? '');
        $target = trim($_POST['target'] ?? '');
        $items_raw = $_POST['items'] ?? '';

        if ($target_user_id <= 0) {
            throw new Exception('Pegawai pemilik rencana aksi tidak valid');
        }
        if ($role === 'admin') {
            $chk = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'guru_pegawai'");
            $chk->execute([$target_user_id]);
            if (!$chk->fetch()) {
                throw new Exception('Pegawai pemilik rencana aksi tidak ditemukan');
            }
        }
        if ($triwulan < 1 || $triwulan > 4) {
            throw new Exception('Triwulan harus berada di antara 1 sampai 4');
        }
        if ($rhk_indikator === '') {
            throw new Exception('RHK / indikator wajib diisi');
        }
        if ($target === '') {
            throw new Exception('Target wajib diisi');
        }
        if ((function_exists('mb_strlen') ? mb_strlen($target) : strlen($target)) > 50) {
            throw new Exception('Target maksimal 50 karakter');
        }

        $items = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $items_raw) as $line) {
            $line = trim($line);
            if ($line !== '') {
                $items[] = $line;
            }
        }
        if (count($items) > 50) {
            throw new Exception('Rencana aksi maksimal 50 baris');
        }

        $pdo->beginTransaction();

        if ($id > 0) {
            $chk = $pdo->prepare("SELECT id, user_id FROM rencana_aksi WHERE id = ?");
            $chk->execute([$id]);
            $existing = $chk->fetch();
            if (!$existing) {
                throw new Exception('Rencana aksi tidak ditemukan');
            }
            if ($role === 'guru_pegawai' && (int) $existing['user_id'] !== $user_id) {
                http_response_code(403);
                throw new Exception('Akses ditolak: rencana aksi ini bukan milik Anda');
            }

            $stmt = $pdo->prepare("UPDATE rencana_aksi SET triwulan = ?, rhk_indikator = ?, target = ? WHERE id = ?");
            $stmt->execute([$triwulan, $rhk_indikator, $target, $id]);

            $pdo->prepare("DELETE FROM rencana_aksi_item WHERE rencana_aksi_id = ?")->execute([$id]);
            $message = 'Rencana aksi berhasil diperbarui';
        } else {
            $stmt = $pdo->prepare("INSERT INTO rencana_aksi (user_id, triwulan, rhk_indikator, target) VALUES (?, ?, ?, ?)");
            $stmt->execute([$target_user_id, $triwulan, $rhk_indikator, $target]);
            $id = (int) $pdo->lastInsertId();
            $message = 'Rencana aksi berhasil dibuat';
        }

        $stmt = $pdo->prepare("INSERT INTO rencana_aksi_item (rencana_aksi_id, deskripsi) VALUES (?, ?)");
        foreach ($items as $item) {
            $stmt->execute([$id, $item]);
        }

        $pdo->commit();
        $response = ['success' => true, 'message' => $message, 'data' => ['id' => $id]];
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception('ID rencana aksi tidak valid');
        }

        $chk = $pdo->prepare("SELECT id, user_id FROM rencana_aksi WHERE id = ?");
        $chk->execute([$id]);
        $existing = $chk->fetch();
        if (!$existing) {
            throw new Exception('Rencana aksi tidak ditemukan');
        }
        if ($role === 'guru_pegawai' && (int) $existing['user_id'] !== $user_id) {
            http_response_code(403);
            throw new Exception('Akses ditolak: rencana aksi ini bukan milik Anda');
        }

        $pdo->prepare("DELETE FROM rencana_aksi WHERE id = ?")->execute([$id]);
        $response = ['success' => true, 'message' => 'Rencana aksi berhasil dihapus', 'data' => ['id' => $id]];
    } else {
        throw new Exception('Action tidak valid');
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response = ['success' => false, 'message' => api_error_message($e), 'data' => null];
}

echo json_encode($response);
exit;
