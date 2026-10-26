<?php
/**
 * api/RhkController.php - Khusus Admin: CRUD RHK, Rencana Aksi, Aspek & Target
 * Sesuai AGENTS.md & PermenPANRB No. 6 Tahun 2022
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

require_login();

$role = $_SESSION['role'] ?? 'guest';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$response = ['success' => false, 'message' => 'Action tidak valid', 'data' => null];

try {
    if ($method !== 'POST' && $role !== 'admin') {
        http_response_code(403);
        throw new Exception('Akses ditolak: Hanya Administrator yang berhak melihat detail RHK & Target');
    }

    // Only Admin can perform mutations on RHK and Indikators
    if ($method === 'POST') {
        if ($role !== 'admin') {
            http_response_code(403);
            throw new Exception('Akses ditolak: Hanya Administrator yang berhak mengelola RHK & Target');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) {
            http_response_code(403);
            throw new Exception('Token CSRF tidak valid atau sesi telah berakhir');
        }
    }

    // 1. CREATE RHK
    if ($action === 'create_rhk') {
        $skp_id = intval($_POST['skp_id'] ?? 0);
        $rhk_pimpinan = trim($_POST['rhk_pimpinan'] ?? '');
        $rhk_individu = trim($_POST['rhk_individu'] ?? '');
        $rencana_aksi = trim($_POST['rencana_aksi'] ?? '');

        if ($skp_id <= 0 || empty($rhk_pimpinan) || empty($rhk_individu)) {
            throw new Exception('SKP ID, RHK Pimpinan, dan RHK Individu wajib diisi');
        }

        // Check if SKP exists
        $chk = $pdo->prepare("SELECT id FROM skp WHERE id = ?");
        $chk->execute([$skp_id]);
        if (!$chk->fetch()) {
            throw new Exception('SKP tidak ditemukan');
        }

        $stmt = $pdo->prepare("
            INSERT INTO skp_rhk (skp_id, rhk_pimpinan, rhk_individu, rencana_aksi, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$skp_id, $rhk_pimpinan, $rhk_individu, $rencana_aksi]);
        $new_id = (int)$pdo->lastInsertId();

        // If initial indikator provided
        $aspek = trim($_POST['aspek'] ?? '');
        $deskripsi = trim($_POST['deskripsi_indikator'] ?? '');
        $target = trim($_POST['target'] ?? '');
        if (!empty($aspek) && !empty($deskripsi) && !empty($target)) {
            $stmtInd = $pdo->prepare("
                INSERT INTO skp_rhk_indikator (rhk_id, aspek, deskripsi_indikator, target)
                VALUES (?, ?, ?, ?)
            ");
            $stmtInd->execute([$new_id, strtoupper($aspek), $deskripsi, $target]);
        }

        $response = [
            'success' => true,
            'message' => 'RHK berhasil dibuat',
            'id' => $new_id
        ];
    }

    // 2. UPDATE RHK
    elseif ($action === 'update_rhk') {
        $id = intval($_POST['id'] ?? 0);
        $rhk_pimpinan = trim($_POST['rhk_pimpinan'] ?? '');
        $rhk_individu = trim($_POST['rhk_individu'] ?? '');
        $rencana_aksi = trim($_POST['rencana_aksi'] ?? '');

        if ($id <= 0 || empty($rhk_pimpinan) || empty($rhk_individu)) {
            throw new Exception('ID RHK, RHK Pimpinan, dan RHK Individu wajib diisi');
        }

        $stmt = $pdo->prepare("
            UPDATE skp_rhk 
            SET rhk_pimpinan = ?, rhk_individu = ?, rencana_aksi = ?
            WHERE id = ?
        ");
        $stmt->execute([$rhk_pimpinan, $rhk_individu, $rencana_aksi, $id]);

        $response = [
            'success' => true,
            'message' => 'RHK berhasil diperbarui'
        ];
    }

    // 3. DELETE RHK
    elseif ($action === 'delete_rhk') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('ID RHK tidak valid');

        $stmt = $pdo->prepare("DELETE FROM skp_rhk WHERE id = ?");
        $stmt->execute([$id]);

        $response = [
            'success' => true,
            'message' => 'RHK beserta aspek dan target berhasil dihapus'
        ];
    }

    // 4. SAVE INDIKATOR (CREATE OR UPDATE)
    elseif ($action === 'save_indikator') {
        $id = intval($_POST['id'] ?? 0);
        $rhk_id = intval($_POST['rhk_id'] ?? 0);
        $aspek = strtoupper(trim($_POST['aspek'] ?? 'KUANTITAS'));
        $deskripsi = trim($_POST['deskripsi_indikator'] ?? '');
        $target = trim($_POST['target'] ?? '');

        if (!in_array($aspek, ['KUANTITAS', 'KUALITAS', 'WAKTU'])) {
            throw new Exception('Aspek harus KUANTITAS, KUALITAS, atau WAKTU');
        }
        if (empty($deskripsi) || empty($target)) {
            throw new Exception('Deskripsi indikator dan Target wajib diisi');
        }

        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("
                UPDATE skp_rhk_indikator 
                SET aspek = ?, deskripsi_indikator = ?, target = ?
                WHERE id = ?
            ");
            $stmt->execute([$aspek, $deskripsi, $target, $id]);
            $response = [
                'success' => true,
                'message' => 'Indikator dan target berhasil diperbarui'
            ];
        } else {
            // Create
            if ($rhk_id <= 0) throw new Exception('RHK ID tidak valid');
            $stmt = $pdo->prepare("
                INSERT INTO skp_rhk_indikator (rhk_id, aspek, deskripsi_indikator, target)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$rhk_id, $aspek, $deskripsi, $target]);
            $response = [
                'success' => true,
                'message' => 'Indikator dan target berhasil disimpan',
                'id' => (int)$pdo->lastInsertId()
            ];
        }
    }

    // 5. DELETE INDIKATOR
    elseif ($action === 'delete_indikator') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('ID indikator tidak valid');

        $stmt = $pdo->prepare("DELETE FROM skp_rhk_indikator WHERE id = ?");
        $stmt->execute([$id]);

        $response = [
            'success' => true,
            'message' => 'Indikator dan target berhasil dihapus'
        ];
    }

    // 6. GET RHK DETAIL
    elseif ($action === 'get_rhk') {
        $id = intval($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM skp_rhk WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if (!$data) throw new Exception('Data RHK tidak ditemukan');

        $response = ['success' => true, 'data' => $data];
    }

    // 7. GET INDIKATOR DETAIL
    elseif ($action === 'get_indikator') {
        $id = intval($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM skp_rhk_indikator WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if (!$data) throw new Exception('Data Indikator tidak ditemukan');

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
