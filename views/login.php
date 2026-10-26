<?php
require_once '../config/security.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../config/database.php';

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf)) {
        $error = 'Sesi login kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } else if (empty($username) || empty($password)) {
        $error = 'Username dan Password wajib diisi.';
    } else {
        $remaining = login_throttle_remaining($username);
        if ($remaining > 0) {
            header('Retry-After: ' . $remaining);
            $error = 'Terlalu banyak percobaan login. Coba lagi dalam ' . (int) ceil($remaining / 60) . ' menit.';
        } else if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, username, nama, nip, role, password FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    clear_login_attempts($username);
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['nip'] = $user['nip'];
                    $_SESSION['auth_fingerprint'] = auth_fingerprint($user['password']);
                    $_SESSION['last_activity'] = time();

                    header("Location: ../index.php");
                    exit;
                } else {
                    record_failed_login($username);
                    $error = 'Username atau Password salah.';
                }
            } catch (PDOException $e) {
                error_log("Login Error: " . $e->getMessage());
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        } else {
            $error = 'Koneksi database gagal. Silakan hubungi administrator.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="shortcut icon" href="../assets/favicon.svg">
    <title>Masuk - Integrita</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            font-feature-settings: "cv11", "ss01";
            background-color: #eef2f7;
            background-image:
                radial-gradient(900px 500px at 85% 0%, rgba(30, 58, 138, 0.14), transparent 60%),
                radial-gradient(700px 500px at 0% 100%, rgba(202, 138, 4, 0.10), transparent 55%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        }
        .brand-grid {
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.045) 1px, transparent 1px);
            background-size: 26px 26px;
            background-position: center;
        }
        input {
            transition: border-color 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease;
        }
        input:focus {
            outline: none;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: none; }
        }
        .auth-card { animation: fadeUp 0.45s ease-out both; }
        #loginSubmit:disabled { opacity: 0.7; cursor: not-allowed; }
    </style>
</head>
<body class="flex min-h-screen items-center justify-center overflow-x-hidden p-4 sm:p-6">

    <div class="auth-card grid w-full min-w-0 max-w-4xl overflow-hidden rounded-2xl border border-white/60 bg-white shadow-2xl shadow-slate-900/20 md:grid-cols-2" style="max-width: min(56rem, calc(100vw - 2rem));">

        <!-- Panel brand -->
        <div class="brand-grid relative hidden flex-col justify-between bg-gradient-to-br from-[#0b1224] via-[#0f172a] to-[#172554] p-9 text-white md:flex">
            <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-blue-500/10 blur-2xl"></div>
            <div class="absolute -bottom-20 -left-10 h-56 w-56 rounded-full bg-[#ca8a04]/10 blur-2xl"></div>

            <div class="relative">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/5 ring-1 ring-white/10">
                        <svg class="h-7 w-7 text-[#eab308]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" fill="#1e3a8a"/>
                            <path d="M8 12l3 3 5-5" stroke="#eab308" stroke-width="2" stroke-linecap="square"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[16px] font-bold uppercase leading-none tracking-tight">Integrita</div>
                        <div class="mt-1 text-[9px] uppercase tracking-[0.16em] text-slate-400">BKN Performance Portal</div>
                    </div>
                </div>

                <h1 class="mt-10 text-[26px] font-bold leading-tight tracking-tight">
                    Kelola kinerja ASN<br>
                    <span class="text-[#eab308]">lebih terukur.</span>
                </h1>
                <p class="mt-3 max-w-xs text-[12px] leading-relaxed text-slate-400">
                    Sistem informasi penilaian kinerja terintegrasi SKP, bukti dukung, serta umpan balik berkelanjutan.
                </p>

                <ul class="mt-8 space-y-3 text-[12px] text-slate-300">
                    <li class="flex items-start gap-2.5">
                        <span class="mt-0.5 flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-400">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        Matriks RHK &amp; target kinerja PermenPANRB 6/2022
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-0.5 flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-400">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        Verifikasi bukti dukung biner &amp; aman
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-0.5 flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-400">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        Realisasi &amp; feedback pimpinan berkelanjutan
                    </li>
                </ul>
            </div>

            <div class="relative mt-10 border-t border-white/10 pt-4 text-[9px] uppercase tracking-[0.14em] text-slate-500">
                BKN &middot; PermenPANRB No. 6 Tahun 2022
            </div>
        </div>

        <!-- Panel form -->
        <div class="min-w-0 p-7 sm:p-10">
            <div class="mb-7 flex items-center gap-3 md:hidden">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0f172a]">
                    <svg class="h-6 w-6 text-[#eab308]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" fill="#1e3a8a"/>
                        <path d="M8 12l3 3 5-5" stroke="#eab308" stroke-width="2" stroke-linecap="square"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-bold uppercase tracking-tight text-slate-900">Integrita</div>
                    <div class="text-[9px] uppercase tracking-[0.14em] text-slate-500">BKN Performance Portal</div>
                </div>
            </div>

            <h2 class="text-xl font-bold tracking-tight text-slate-900">Selamat datang kembali</h2>
            <p class="mt-1 text-xs text-slate-500">Masuk untuk melanjutkan pengelolaan kinerja Anda.</p>

            <?php if ($error): ?>
                <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700" role="alert">
                    <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14.14A2 2 0 003.82 21h16.36a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="mt-6 space-y-4" onsubmit="handleLoginSubmit()">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <div>
                    <label for="username" class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">Username / NIP</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <input id="username" type="text" name="username" value="<?= htmlspecialchars($username) ?>" autocomplete="username" required autofocus
                               class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-800 placeholder:text-slate-400"
                               placeholder="Masukkan username">
                    </div>
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">Password</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input id="password" type="password" name="password" autocomplete="current-password" required
                               class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-9 pr-10 text-sm text-slate-800 placeholder:text-slate-400"
                               placeholder="Masukkan password">
                        <button type="button" onclick="togglePassword()" class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tampilkan password">
                            <svg id="eyeIcon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <button id="loginSubmit" type="submit"
                        class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-[#1e3a8a] py-2.5 text-[11px] font-bold uppercase tracking-[0.12em] text-white shadow-lg shadow-blue-900/20 transition-colors hover:bg-[#172554]">
                    <span id="loginSubmitText">Masuk ke Portal</span>
                    <svg id="loginSpinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </button>
            </form>

            <p class="mt-6 text-center text-[10px] text-slate-400">
                &copy; <?= date('Y') ?> Integrita &middot; Sistem Informasi Kinerja
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.style.opacity = isPassword ? '0.45' : '1';
        }

        function handleLoginSubmit() {
            const button = document.getElementById('loginSubmit');
            const text = document.getElementById('loginSubmitText');
            const spinner = document.getElementById('loginSpinner');
            if (button) button.disabled = true;
            if (text) text.textContent = 'Memproses...';
            if (spinner) spinner.classList.remove('hidden');
        }
    </script>
</body>
</html>
