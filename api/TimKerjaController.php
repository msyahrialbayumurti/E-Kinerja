<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

require_login();

$role = $_SESSION['role'] ?? 'guest';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$response = ['success' => false, 'message' => 'Action tidak valid', 'data' => null];

if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak: hanya Administrator yang berhak mengelola tim kerja', 'data' => null]);
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
        $nama_tim = trim($_POST['nama_tim'] ?? '');
        $ketua_id = intval($_POST['ketua_id'] ?? 0);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $status = $_POST['status'] ?? 'aktif';
        $dasar_sk = trim($_POST['dasar_sk'] ?? '');

        if ($nama_tim === '') {
            throw new Exception('Nama tim kerja wajib diisi');
        }
        if ($ketua_id <= 0) {
            throw new Exception('Ketua tim wajib dipilih');
        }
        if (!in_array($status, ['aktif', 'nonaktif'], true)) {
            throw new Exception('Status tim kerja tidak valid');
        }

        $chk = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $chk->execute([$ketua_id]);
        if (!$chk->fetch()) {
            throw new Exception('Ketua tim tidak ditemukan');
        }

        $member_ids = [];
        if (isset($_POST['members']) && is_array($_POST['members'])) {
            foreach ($_POST['members'] as $member_id) {
                $member_id = intval($member_id);
                if ($member_id > 0) {
                    $member_ids[$member_id] = $member_id;
                }
            }
        }

        if (!empty($member_ids)) {
            $placeholders = implode(',', array_fill(0, count($member_ids), '?'));
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id IN ($placeholders)");
            $stmt->execute(array_values($member_ids));
            $found = array_map('intval', array_column($stmt->fetchAll(), 'id'));
            $member_ids = array_intersect_key($member_ids, array_flip($found));
        }

        $pdo->beginTransaction();

        if ($id > 0) {
            $chk = $pdo->prepare("SELECT id FROM tim_kerja WHERE id = ?");
            $chk->execute([$id]);
            if (!$chk->fetch()) {
                throw new Exception('Tim kerja tidak ditemukan');
            }

            $existing_positions = [];
            $stmt = $pdo->prepare("SELECT anggota_id, posisi FROM anggota_tim_kerja WHERE tim_kerja_id = ?");
            $stmt->execute([$id]);
            foreach ($stmt->fetchAll() as $row) {
                $existing_positions[(int) $row['anggota_id']] = $row['posisi'];
            }

            $stmt = $pdo->prepare("UPDATE tim_kerja SET nama_tim = ?, ketua_id = ?, deskripsi = ?, status = ?, dasar_sk = ? WHERE id = ?");
            $stmt->execute([$nama_tim, $ketua_id, $deskripsi, $status, $dasar_sk, $id]);

            $pdo->prepare("DELETE FROM anggota_tim_kerja WHERE tim_kerja_id = ?")->execute([$id]);

            $stmt = $pdo->prepare("INSERT INTO anggota_tim_kerja (tim_kerja_id, anggota_id, posisi) VALUES (?, ?, ?)");
            foreach ($member_ids as $member_id) {
                $stmt->execute([$id, $member_id, $existing_positions[$member_id] ?? 'Anggota']);
            }

            $message = 'Tim kerja berhasil diperbarui';
        } else {
            $stmt = $pdo->prepare("INSERT INTO tim_kerja (nama_tim, ketua_id, deskripsi, status, dasar_sk) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nama_tim, $ketua_id, $deskripsi, $status, $dasar_sk]);
            $id = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO anggota_tim_kerja (tim_kerja_id, anggota_id, posisi) VALUES (?, ?, ?)");
            foreach ($member_ids as $member_id) {
                $stmt->execute([$id, $member_id, 'Anggota']);
            }

            $message = 'Tim kerja berhasil dibuat';
        }

        $pdo->commit();
        $response = ['success' => true, 'message' => $message, 'data' => ['id' => $id]];
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception('ID tim kerja tidak valid');
        }

        $chk = $pdo->prepare("SELECT id FROM tim_kerja WHERE id = ?");
        $chk->execute([$id]);
        if (!$chk->fetch()) {
            throw new Exception('Tim kerja tidak ditemukan');
        }

        $pdo->prepare("DELETE FROM tim_kerja WHERE id = ?")->execute([$id]);
        $response = ['success' => true, 'message' => 'Tim kerja berhasil dihapus', 'data' => ['id' => $id]];
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
