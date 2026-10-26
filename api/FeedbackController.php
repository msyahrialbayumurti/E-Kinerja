<?php
/**
 * api/FeedbackController.php - Khusus Atasan / Ketua Yayasan: CRUD Realisasi & Feedback
 * Sesuai AGENTS.md & PermenPANRB No. 6 Tahun 2022
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

require_login();

$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? 'guest';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$response = ['success' => false, 'message' => 'Action tidak valid', 'data' => null];

try {
    // 1. SAVE PENILAIAN (REALISASI & FEEDBACK)
    if ($action === 'save_penilaian') {
        if ($method !== 'POST') {
            http_response_code(405);
            throw new Exception('Method not allowed');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) {
            http_response_code(403);
            throw new Exception('Token CSRF tidak valid atau sesi telah berakhir');
        }

        // Only ketua_yayasan and admin can evaluate
        if (!in_array($role, ['ketua_yayasan', 'admin'])) {
            http_response_code(403);
            throw new Exception('Akses ditolak: Hanya Atasan / Ketua Yayasan yang berhak memberikan realisasi & umpan balik');
        }

        $rhk_id = intval($_POST['rhk_id'] ?? 0);
        $realisasi = trim($_POST['realisasi'] ?? '');
        $feedback_teks = trim($_POST['feedback_teks'] ?? '');
        $feedback_simbol = strtolower(trim($_POST['feedback_simbol'] ?? ''));

        if ($rhk_id <= 0) {
            throw new Exception('RHK ID tidak valid');
        }

        $valid_simbols = ['jempol', 'bintang', 'catatan', 'apresiasi'];
        if (!empty($feedback_simbol) && !in_array($feedback_simbol, $valid_simbols)) {
            throw new Exception('Simbol feedback harus salah satu dari: jempol, bintang, catatan, apresiasi');
        }

        // Check RHK exists
        $chk = $pdo->prepare("SELECT id FROM skp_rhk WHERE id = ?");
        $chk->execute([$rhk_id]);
        if (!$chk->fetch()) {
            throw new Exception('Data RHK tidak ditemukan');
        }

        // UPSERT into penilaian_realisasi
        $stmt = $pdo->prepare("
            INSERT INTO penilaian_realisasi (rhk_id, penilai_id, realisasi, feedback_simbol, feedback_teks, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                penilai_id = VALUES(penilai_id),
                realisasi = IF(VALUES(realisasi) != '', VALUES(realisasi), realisasi),
                feedback_simbol = IF(VALUES(feedback_simbol) IS NOT NULL AND VALUES(feedback_simbol) != '', VALUES(feedback_simbol), feedback_simbol),
                feedback_teks = IF(VALUES(feedback_teks) != '', VALUES(feedback_teks), feedback_teks),
                updated_at = NOW()
        ");
        $stmt->execute([$rhk_id, $user_id, $realisasi, $feedback_simbol ?: null, $feedback_teks]);

        $response = [
            'success' => true,
            'message' => 'Realisasi dan feedback berhasil disimpan',
            'data' => [
                'rhk_id' => $rhk_id,
                'realisasi' => $realisasi,
                'feedback_simbol' => $feedback_simbol,
                'feedback_teks' => $feedback_teks
            ]
        ];
    }

    // 2. DELETE FEEDBACK / RESET PENILAIAN
    elseif ($action === 'delete_feedback') {
        if ($method !== 'POST') {
            http_response_code(405);
            throw new Exception('Method not allowed');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) {
            http_response_code(403);
            throw new Exception('Token CSRF tidak valid atau sesi telah berakhir');
        }

        if (!in_array($role, ['ketua_yayasan', 'admin'])) {
            http_response_code(403);
            throw new Exception('Akses ditolak');
        }

        $rhk_id = intval($_POST['rhk_id'] ?? 0);
        $type = $_POST['type'] ?? 'all'; // 'feedback' or 'all'

        if ($type === 'feedback') {
            $stmt = $pdo->prepare("UPDATE penilaian_realisasi SET feedback_simbol = NULL, feedback_teks = NULL WHERE rhk_id = ?");
            $stmt->execute([$rhk_id]);
            $response = ['success' => true, 'message' => 'Feedback berhasil dihapus'];
        } else {
            $stmt = $pdo->prepare("DELETE FROM penilaian_realisasi WHERE rhk_id = ?");
            $stmt->execute([$rhk_id]);
            $response = ['success' => true, 'message' => 'Data penilaian & feedback berhasil direset'];
        }
    }

    // 3. GET PENILAIAN DETAIL
    elseif ($action === 'get_penilaian') {
        if (!in_array($role, ['ketua_yayasan', 'admin'], true)) {
            http_response_code(403);
            throw new Exception('Akses ditolak');
        }

        $rhk_id = intval($_GET['rhk_id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT pr.*, u.nama as nama_penilai, u.jabatan as jabatan_penilai
            FROM penilaian_realisasi pr
            LEFT JOIN users u ON pr.penilai_id = u.id
            WHERE pr.rhk_id = ?
        ");
        $stmt->execute([$rhk_id]);
        $data = $stmt->fetch();

        $response = ['success' => true, 'data' => $data ?: null];
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
