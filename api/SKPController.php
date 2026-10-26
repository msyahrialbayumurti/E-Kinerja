<?php
/**
 * api/SKPController.php - SKP CRUD Operations Multi-Role
 * Admin: Full CRUD all users | Guru: CRUD own draft | Ketua: Monitoring & Status Approval
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

require_login();

$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? 'guest';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$response = [
    'success' => false,
    'message' => 'Action tidak valid',
    'data' => null
];

try {
    // 1. CREATE SKP
    if ($action === 'create') {
        if ($method !== 'POST') throw new Exception('Method not allowed');
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) throw new Exception('CSRF token invalid');

        if (!in_array($role, ['admin', 'guru_pegawai'], true)) {
            http_response_code(403);
            throw new Exception('Akses ditolak: Hanya Admin atau Guru yang dapat membuat SKP');
        }

        // Admin can specify user_id; others use own user_id
        $target_user_id = ($role === 'admin' && !empty($_POST['user_id'])) ? intval($_POST['user_id']) : $user_id;
        $tahun_anggaran = intval($_POST['tahun_anggaran'] ?? date('Y'));
        $periode_mulai = $_POST['periode_mulai'] ?? ($tahun_anggaran . '-01-01');
        $periode_selesai = $_POST['periode_selesai'] ?? ($tahun_anggaran . '-12-31');
        $pendekatan = $_POST['pendekatan'] ?? 'Kuantitatif';
        if (!in_array($pendekatan, ['Kuantitatif', 'Kualitatif'], true)) {
            throw new Exception('Pendekatan tidak valid');
        }
        $status = ($role === 'admin') ? ($_POST['status'] ?? 'aktif') : 'draft';

        // Check duplicate
        $chk = $pdo->prepare("SELECT id FROM skp WHERE user_id = ? AND tahun_anggaran = ?");
        $chk->execute([$target_user_id, $tahun_anggaran]);
        if ($chk->fetch()) {
            throw new Exception("SKP untuk pegawai tersebut pada tahun $tahun_anggaran sudah ada");
        }

        $stmt = $pdo->prepare("
            INSERT INTO skp (user_id, tahun_anggaran, periode_mulai, periode_selesai, status, pendekatan, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$target_user_id, $tahun_anggaran, $periode_mulai, $periode_selesai, $status, $pendekatan]);

        $response = [
            'success' => true,
            'message' => 'SKP berhasil dibuat',
            'data' => ['id' => (int)$pdo->lastInsertId()]
        ];
    }

    // 2. UPDATE SKP
    elseif ($action === 'update') {
        if ($method !== 'POST') throw new Exception('Method not allowed');
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) throw new Exception('CSRF token invalid');

        $id = intval($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? null;
        $pendekatan = $_POST['pendekatan'] ?? 'Kuantitatif';
        if (in_array($role, ['admin', 'guru_pegawai'], true) && !in_array($pendekatan, ['Kuantitatif', 'Kualitatif'], true)) {
            throw new Exception('Pendekatan tidak valid');
        }
        $periode_mulai = $_POST['periode_mulai'] ?? null;
        $periode_selesai = $_POST['periode_selesai'] ?? null;

        // Check SKP exists
        $chk = $pdo->prepare("SELECT * FROM skp WHERE id = ?");
        $chk->execute([$id]);
        $skp = $chk->fetch();
        if (!$skp) throw new Exception('SKP tidak ditemukan');

        $allowed_statuses = ['draft', 'persetujuan', 'aktif', 'selesai', 'dinilai'];

        if ($role === 'guru_pegawai') {
            if ((int)$skp['user_id'] !== (int)$user_id) {
                http_response_code(403);
                throw new Exception('Akses ditolak');
            }
            if ($skp['status'] !== 'draft') {
                http_response_code(403);
                throw new Exception('Hanya SKP berstatus Draft yang dapat diubah oleh pegawai');
            }

            $stmt = $pdo->prepare("
                UPDATE skp 
                SET pendekatan = ?,
                    periode_mulai = COALESCE(?, periode_mulai),
                    periode_selesai = COALESCE(?, periode_selesai)
                WHERE id = ?
            ");
            $stmt->execute([$pendekatan, $periode_mulai, $periode_selesai, $id]);
        } elseif ($role === 'ketua_yayasan') {
            if (!in_array($status, $allowed_statuses, true)) throw new Exception('Status tidak valid');

            $stmt = $pdo->prepare("UPDATE skp SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        } elseif ($role === 'admin') {
            if ($status !== null && !in_array($status, $allowed_statuses, true)) throw new Exception('Status tidak valid');

            $stmt = $pdo->prepare("
                UPDATE skp 
                SET pendekatan = ?,
                    status = COALESCE(?, status),
                    periode_mulai = COALESCE(?, periode_mulai),
                    periode_selesai = COALESCE(?, periode_selesai)
                WHERE id = ?
            ");
            $stmt->execute([$pendekatan, $status, $periode_mulai, $periode_selesai, $id]);
        } else {
            http_response_code(403);
            throw new Exception('Akses ditolak');
        }

        $response = [
            'success' => true,
            'message' => 'SKP berhasil diperbarui'
        ];
    }

    // 3. UPDATE STATUS (Approval for Ketua Yayasan & Admin)
    elseif ($action === 'update_status') {
        if ($method !== 'POST') throw new Exception('Method not allowed');
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) throw new Exception('CSRF token invalid');

        if (!in_array($role, ['ketua_yayasan', 'admin'])) {
            throw new Exception('Akses ditolak: Hanya Pimpinan/Admin yang dapat menyetujui status SKP');
        }

        $id = intval($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $allowed_statuses = ['draft', 'persetujuan', 'aktif', 'selesai', 'dinilai'];
        if (!in_array($status, $allowed_statuses)) throw new Exception('Status tidak valid');

        $stmt = $pdo->prepare("UPDATE skp SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        $response = [
            'success' => true,
            'message' => 'Status SKP berhasil diperbarui menjadi: ' . ucfirst($status)
        ];
    }

    // 4. DELETE SKP
    elseif ($action === 'delete') {
        if ($method !== 'POST') throw new Exception('Method not allowed');
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) throw new Exception('CSRF token invalid');

        $id = intval($_POST['id'] ?? 0);

        $chk = $pdo->prepare("SELECT * FROM skp WHERE id = ?");
        $chk->execute([$id]);
        $skp = $chk->fetch();
        if (!$skp) throw new Exception('SKP tidak ditemukan');

        if ($role !== 'admin' && $skp['user_id'] != $user_id) {
            throw new Exception('Akses ditolak');
        }

        // Guru can only delete draft SKP
        if ($role === 'guru_pegawai' && $skp['status'] !== 'draft') {
            throw new Exception('Hanya SKP dengan status Draft yang dapat dihapus oleh pegawai');
        }

        $stmt = $pdo->prepare("DELETE FROM skp WHERE id = ?");
        $stmt->execute([$id]);

        $response = [
            'success' => true,
            'message' => 'SKP berhasil dihapus'
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
