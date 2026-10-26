<?php
/**
 * setup.php - Database Setup & Initialization
 * Import schema dan dummy data ke MySQL
 */

require_once 'config/database.php';
require_once 'config/security.php';

$setup_lock_file = __DIR__ . '/config/.installed';
$setup_locked = file_exists($setup_lock_file);

if ($pdo) {
    try {
        $existing_users = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $setup_locked = $setup_locked || $existing_users > 0;
    } catch (Exception $e) {
        // Pertahankan status lock dari file jika tabel belum tersedia.
    }
}

if ($setup_locked && !has_role('admin')) {
    http_response_code(403);
    die('Akses ditolak. Hanya administrator yang dapat menjalankan setup setelah database berisi data.');
}

$setup_message = '';
$setup_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'setup_database') {
    try {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) {
            throw new Exception('Token CSRF tidak valid');
        }

        // Read SQL file
        $sql_file = __DIR__ . '/ekinerja_db.sql';
        if (!file_exists($sql_file)) {
            throw new Exception('File ekinerja_db.sql tidak ditemukan');
        }
        
        $sql_content = file_get_contents($sql_file);
        
        // Split by ; dan execute tiap statement
        $statements = array_filter(array_map('trim', explode(';', $sql_content)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        @file_put_contents($setup_lock_file, date('c'));
        $setup_locked = true;
        $setup_success = true;
        $setup_message = '✅ Database berhasil di-setup! Skema dan dummy data sudah di-import.';
    } catch (Throwable $e) {
        error_log('Setup error: ' . $e->getMessage());
        $setup_message = '❌ Setup gagal. Periksa log server untuk detail.';
    }
}

// Check database status
$db_status = 'unknown';
if ($pdo) {
    try {
        $result = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = DATABASE()");
        $count = $result->fetch(PDO::FETCH_ASSOC)['table_count'];
        $db_status = $count > 0 ? 'ready' : 'empty';
    } catch (Exception $e) {
        $db_status = 'error';
    }
} else {
    $db_status = 'error';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="shortcut icon" href="assets/favicon.svg">
    <title>E-Kinerja - Database Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-100 to-slate-200 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-8">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-slate-800">E-Kinerja</h1>
            <p class="text-sm text-slate-600 mt-2">Database Setup</p>
        </div>

        <!-- Status Badge -->
        <div class="mb-6 p-4 rounded-lg <?php echo $db_status === 'ready' ? 'bg-green-50 border border-green-200' : ($db_status === 'error' ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200'); ?>">
            <p class="text-sm font-semibold <?php echo $db_status === 'ready' ? 'text-green-800' : ($db_status === 'error' ? 'text-red-800' : 'text-yellow-800'); ?>">
                <?php 
                if ($db_status === 'ready') {
                    echo '✅ Database siap digunakan';
                } elseif ($db_status === 'error') {
                    echo '❌ Koneksi database error';
                } else {
                    echo '⚠️ Database kosong - perlu setup';
                }
                ?>
            </p>
        </div>

        <!-- Setup Message -->
        <?php if ($setup_message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $setup_success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?>">
            <p class="text-sm <?php echo $setup_success ? 'text-green-800' : 'text-red-800'; ?>">
                <?php echo $setup_message; ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Setup Form -->
        <?php if ($db_status !== 'ready'): ?>
        <form method="POST" class="mb-6">
            <input type="hidden" name="action" value="setup_database">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                🚀 Setup Database
            </button>
        </form>
        <?php endif; ?>

        <?php if (!$setup_locked): ?>
        <div class="bg-slate-50 rounded-lg p-4 mb-6 text-xs text-slate-700">
            Akun seed <strong>admin</strong>, <strong>guru</strong>, dan <strong>ketua</strong> tersedia setelah import database.
            Gunakan kredensial awal yang diberikan administrator dan segera ganti password setelah login pertama.
        </div>
        <?php endif; ?>

        <!-- Login Button -->
        <?php if ($db_status === 'ready'): ?>
        <a href="views/login.php" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors text-center block">
            ✅ Lanjut ke Login
        </a>
        <?php endif; ?>

        <!-- Info -->
        <div class="mt-6 pt-6 border-t border-slate-200 text-xs text-slate-600 text-center">
            <p>Database: <strong><?php echo $_ENV['DBNAME'] ?? 'ekinerja_db'; ?></strong></p>
            <p>Status: <strong><?php echo ucfirst($db_status); ?></strong></p>
        </div>
    </div>
</body>
</html>
