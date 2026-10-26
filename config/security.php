<?php
/**
 * security.php - Session & Authorization Guard
 * Handles authentication, CSRF protection, role-based access control
 */

// Jangan pernah tampilkan detail error PHP ke browser.
if (!defined('EKINERJA_DEBUG')) {
    define('EKINERJA_DEBUG', false);
}
ini_set('display_errors', EKINERJA_DEBUG ? '1' : '0');
ini_set('display_startup_errors', EKINERJA_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$is_https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443');

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $is_https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Content-Security-Policy: object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
if ($is_https) {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

function terminate_session(): void {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

// Session timeout (30 minutes)
$timeout_duration = 1800;
if (isset($_SESSION['last_activity'])) {
    $elapsed = time() - $_SESSION['last_activity'];
    if ($elapsed > $timeout_duration) {
        terminate_session();
        header("Location: /E-Kinerja/views/login.php?timeout=1");
        exit;
    }
}
$_SESSION['last_activity'] = time();

// CSRF Token generation & validation
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

// Get CSRF token untuk digunakan di form
function csrf_token() {
    return generate_csrf_token();
}

function auth_fingerprint($password_hash): string {
    return hash('sha256', (string) $password_hash);
}

function login_attempt_keys($username): array {
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $user = strtolower(trim((string) $username));

    return [
        hash('sha256', 'user|' . $user . '|' . $ip),
        hash('sha256', 'ip|' . $ip),
    ];
}

function login_attempt_file($key): string {
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ekinerja_login_' . $key . '.json';
}

function login_attempt_state($key): array {
    $file = login_attempt_file($key);
    $empty = ['count' => 0, 'first' => 0, 'last' => 0];

    if (!is_file($file)) {
        return $empty;
    }

    $data = json_decode((string) @file_get_contents($file), true);
    if (!is_array($data)) {
        @unlink($file);
        return $empty;
    }

    $first = (int) ($data['first'] ?? 0);
    $count = (int) ($data['count'] ?? 0);
    $last = (int) ($data['last'] ?? 0);

    if ($first <= 0 || (time() - $first) > 900) {
        @unlink($file);
        return $empty;
    }

    return ['count' => $count, 'first' => $first, 'last' => $last];
}

function login_throttle_remaining($username): int {
    $remaining = 0;

    foreach (login_attempt_keys($username) as $key) {
        $state = login_attempt_state($key);
        if ($state['count'] >= 5) {
            $remaining = max($remaining, 900 - (time() - $state['first']));
        }
    }

    return max(0, $remaining);
}

function record_failed_login($username): void {
    foreach (login_attempt_keys($username) as $key) {
        $file = login_attempt_file($key);
        $handle = @fopen($file, 'c+');
        if (!$handle) {
            continue;
        }

        if (flock($handle, LOCK_EX)) {
            rewind($handle);
            $data = json_decode((string) stream_get_contents($handle), true);
            $now = time();

            if (!is_array($data) || empty($data['first']) || ($now - (int) $data['first']) > 900) {
                $data = ['count' => 1, 'first' => $now, 'last' => $now];
            } else {
                $data['count'] = (int) ($data['count'] ?? 0) + 1;
                $data['last'] = $now;
            }

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($data));
            fflush($handle);
            flock($handle, LOCK_UN);
        }

        fclose($handle);
        @chmod($file, 0600);
    }
}

function clear_login_attempts($username): void {
    $keys = login_attempt_keys($username);
    @unlink(login_attempt_file($keys[0]));
}

function api_error_message(Throwable $e): string {
    $message = trim((string) $e->getMessage());
    $sensitive = $e instanceof PDOException
        || $e instanceof Error
        || preg_match('/(SQLSTATE|Stack trace|PDOException|mysqli_sql_exception|\.php:\d+|[A-Z]:\\\\|\/var\/www|\/home\/)/i', $message);

    if ($sensitive) {
        error_log('API error: ' . $e);
        return 'Terjadi kesalahan pada server.';
    }

    return $message !== '' ? $message : 'Terjadi kesalahan pada server.';
}

// Role-based access control
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /E-Kinerja/views/login.php");
        exit;
    }

    global $pdo;
    if (!($pdo instanceof PDO)) {
        terminate_session();
        header("Location: /E-Kinerja/views/login.php?error=db");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, nama, nip, role, password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([(int) $_SESSION['user_id']]);
        $user = $stmt->fetch();
    } catch (Throwable $e) {
        error_log("Session validation error: " . $e->getMessage());
        $user = false;
    }

    if (!$user) {
        terminate_session();
        header("Location: /E-Kinerja/views/login.php?expired=1");
        exit;
    }

    $fingerprint = auth_fingerprint($user['password'] ?? '');
    if (empty($_SESSION['auth_fingerprint']) || !hash_equals((string) $_SESSION['auth_fingerprint'], $fingerprint)) {
        terminate_session();
        header("Location: /E-Kinerja/views/login.php?expired=1");
        exit;
    }

    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['nip'] = $user['nip'];
}

function require_role($required_roles) {
    require_login();
    
    $user_role = $_SESSION['role'] ?? 'guest';
    $roles = is_array($required_roles) ? $required_roles : [$required_roles];
    
    if (!in_array($user_role, $roles)) {
        http_response_code(403);
        die("Akses ditolak. Role Anda tidak memiliki izin untuk halaman ini.");
    }
}

// Get current user info from session
function current_user() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'nama' => $_SESSION['nama'] ?? null,
        'nip' => $_SESSION['nip'] ?? null,
        'role' => $_SESSION['role'] ?? 'guest'
    ];
}

// Check if user has specific role
function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Sanitize & escape output
function safe_output($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

// Validate & sanitize input
function safe_input($data) {
    return trim(stripslashes($data ?? ''));
}

// Hanya izinkan tautan eksternal http/https yang valid.
function safe_external_url($url) {
    $url = trim((string) ($url ?? ''));

    if ($url === '' || strlen($url) > 2048) {
        return '';
    }
    if (preg_match('/[\x00-\x1F\x7F]/', $url)) {
        return '';
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return '';
    }

    return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
}
?>
