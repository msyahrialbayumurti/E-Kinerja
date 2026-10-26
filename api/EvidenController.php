<?php
/**
 * api/EvidenController.php - Khusus Guru / Pegawai: Upload & Delete Bukti Dukung
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
    // 1. UPLOAD BUKTI DUKUNG
    if ($action === 'upload') {
        if ($method !== 'POST') {
            http_response_code(405);
            throw new Exception('Method not allowed');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) {
            http_response_code(403);
            throw new Exception('Token CSRF tidak valid atau sesi telah berakhir');
        }

        // Only guru_pegawai and admin can upload evidence
        if (!in_array($role, ['guru_pegawai', 'admin'])) {
            http_response_code(403);
            throw new Exception('Akses ditolak: Hanya Guru / Pegawai Pelaksana yang berhak mengunggah bukti dukung');
        }

        $indikator_id = intval($_POST['indikator_id'] ?? 0);
        $nama_label = trim($_POST['nama_label'] ?? '');
        $link_eksternal = trim($_POST['link_eksternal'] ?? '');

        if ($indikator_id <= 0) {
            throw new Exception('Indikator ID tidak valid');
        }
        if (empty($nama_label)) {
            throw new Exception('Nama label dokumen bukti dukung wajib diisi');
        }

        if ($link_eksternal !== '') {
            $validated_link = safe_external_url($link_eksternal);
            if ($validated_link === '') {
                throw new Exception('Link eksternal harus berupa URL http:// atau https:// yang valid');
            }
            $link_eksternal = $validated_link;
        }

        $chk = $pdo->prepare("
            SELECT s.user_id
            FROM skp_rhk_indikator i
            JOIN skp_rhk r ON i.rhk_id = r.id
            JOIN skp s ON r.skp_id = s.id
            WHERE i.id = ?
        ");
        $chk->execute([$indikator_id]);
        $indikator_owner = $chk->fetch();

        if (!$indikator_owner) {
            throw new Exception('Indikator kinerja tidak ditemukan');
        }

        if ($role === 'guru_pegawai' && (int)$indikator_owner['user_id'] !== (int)$user_id) {
            http_response_code(403);
            throw new Exception('Akses ditolak: Indikator bukan milik SKP Anda');
        }

        $safe_filename = '';
        $original_name = '';

        // Check if file is uploaded
        if (isset($_FILES['file_eviden']) && $_FILES['file_eviden']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['file_eviden'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Gagal mengunggah file. Kode error: ' . $file['error']);
            }

            // Max 5MB
            $max_size = 5 * 1024 * 1024;
            if ($file['size'] > $max_size) {
                throw new Exception('Ukuran file melebihi batas maksimal 5 MB');
            }

            // Verify MIME type using finfo_file (secure binary check)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowed_mimes = [
                'application/pdf',
                'image/jpeg',
                'image/png'
            ];

            if (!in_array($mime, $allowed_mimes)) {
                throw new Exception('Format file tidak didukung. Harap unggah file PDF, JPG, atau PNG');
            }

            // Generate safe unique filename
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                throw new Exception('Ekstensi file tidak diizinkan');
            }

            $safe_filename = bin2hex(random_bytes(16)) . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/eviden/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $destination = $upload_dir . $safe_filename;
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new Exception('Gagal memindahkan file ke direktori penyimpanan');
            }

            $original_name = $file['name'];
        } else {
            // If no file, link_eksternal must be present
            if (empty($link_eksternal)) {
                throw new Exception('Pilih file berkas bukti dukung (PDF/JPG/PNG) atau sertakan link eksternal');
            }
            $original_name = 'Link Tautan Eksternal';
            $safe_filename = '';
        }

        // Insert into database
        $stmt = $pdo->prepare("
            INSERT INTO eviden_dukung (indikator_id, user_id, nama_label, file_path, original_name, link_eksternal, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$indikator_id, $user_id, $nama_label, $safe_filename, $original_name, $link_eksternal]);

        $response = [
            'success' => true,
            'message' => 'Bukti dukung berhasil diunggah',
            'data' => [
                'id' => (int)$pdo->lastInsertId(),
                'file_path' => $safe_filename,
                'nama_label' => $nama_label,
                'original_name' => $original_name
            ]
        ];
    }

    // 2. DELETE BUKTI DUKUNG
    elseif ($action === 'delete') {
        if ($method !== 'POST') {
            http_response_code(405);
            throw new Exception('Method not allowed');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) {
            http_response_code(403);
            throw new Exception('Token CSRF tidak valid atau sesi telah berakhir');
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('ID bukti dukung tidak valid');

        // Check ownership
        $stmt = $pdo->prepare("SELECT * FROM eviden_dukung WHERE id = ?");
        $stmt->execute([$id]);
        $eviden = $stmt->fetch();

        if (!$eviden) {
            throw new Exception('Bukti dukung tidak ditemukan');
        }

        // User must be owner or admin
        if ($eviden['user_id'] != $user_id && $role !== 'admin') {
            http_response_code(403);
            throw new Exception('Akses ditolak: Anda hanya dapat menghapus bukti dukung milik Anda sendiri');
        }

        // Delete physical file if exists
        if (!empty($eviden['file_path'])) {
            $file_path = __DIR__ . '/../uploads/eviden/' . $eviden['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Delete from DB
        $del = $pdo->prepare("DELETE FROM eviden_dukung WHERE id = ?");
        $del->execute([$id]);

        $response = [
            'success' => true,
            'message' => 'Bukti dukung berhasil dihapus'
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
