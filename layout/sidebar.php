<?php
/**
 * layout/sidebar.php - Navigasi utama & topbar.
 * Menu per role diatur di config/navigation.php.
 */

require_once __DIR__ . '/../config/navigation.php';

if (!function_exists('render_nav_icon')) {
    function render_nav_icon(string $name): string
    {
        $paths = [
            'book' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
            'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
            'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9h6m-6 4h6"/>',
            'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 5a1 1 0 011-1h6a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 14a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1v-5zm10-3a1 1 0 011-1h4a1 1 0 011 1v8a1 1 0 01-1 1h-4a1 1 0 01-1-1v-8z"/>',
        ];

        return '<svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . ($paths[$name] ?? $paths['dashboard']) . '</svg>';
    }
}

$userRole = $_SESSION['role'] ?? 'guru_pegawai';
$currentPage = $_GET['page'] ?? 'profil';
$namaUser = $_SESSION['nama'] ?? 'Guest';
$roleLabel = navigation_role_labels()[$userRole] ?? 'Pengguna';
$initial = strtoupper(substr($namaUser, 0, 1));

$tahun_options = [];
try {
    $tahun_stmt = $pdo->query("SELECT DISTINCT tahun_anggaran FROM skp ORDER BY tahun_anggaran DESC");
    $tahun_options = array_map('intval', array_column($tahun_stmt->fetchAll(), 'tahun_anggaran'));
} catch (Throwable $e) {
    $tahun_options = [];
}
if (empty($tahun_options)) {
    $tahun_options = [(int) date('Y')];
}

$selected_tahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : (int) $tahun_options[0];
if (!in_array($selected_tahun, $tahun_options, true)) {
    $selected_tahun = (int) $tahun_options[0];
}

$navItems = navigation_items($userRole);
foreach ($navItems as &$navItem) {
    $navItem['url'] = 'index.php?page=' . urlencode($navItem['page']);
    if ($selected_tahun > 0) {
        $navItem['url'] .= '&tahun=' . $selected_tahun;
    }
}
unset($navItem);

$bulanIndo = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
$tanggalHariIni = date('d') . ' ' . ($bulanIndo[(int) date('n')] ?? '') . ' ' . date('Y');
?>

<!-- Overlay mobile -->
<div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/60 backdrop-blur-[2px] lg:hidden" onclick="toggleSidebar()"></div>

<input type="hidden" id="topbarCsrfToken" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

<aside id="appSidebar" class="fixed inset-y-0 left-0 z-40 flex h-screen w-64 flex-shrink-0 -translate-x-full flex-col border-r border-white/5 bg-gradient-to-b from-[#0b1224] via-[#0f172a] to-[#0f172a] text-slate-300 transition-transform duration-300 ease-out lg:static lg:inset-auto lg:z-auto lg:translate-x-0">

    <!-- Logo -->
    <div class="flex h-[70px] flex-shrink-0 items-center gap-3 border-b border-white/5 px-5">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#1e3a8a] to-[#0f172a] ring-1 ring-white/10">
            <svg class="h-6 w-6 text-[#eab308]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" fill="#1e3a8a"/>
                <path d="M8 12l3 3 5-5" stroke="#eab308" stroke-width="2" stroke-linecap="square"/>
            </svg>
        </div>
        <div class="min-w-0">
            <div class="truncate text-[15px] font-bold uppercase leading-none tracking-tight text-white">Integrita</div>
            <div class="mt-1 truncate text-[9px] uppercase leading-tight tracking-[0.14em] text-slate-400">BKN Performance Portal</div>
        </div>
    </div>

    <!-- Navigasi -->
    <nav class="flex-1 overflow-y-auto py-4">
        <div class="px-5 pb-2 text-[9px] font-bold uppercase tracking-[0.16em] text-slate-500">Menu Utama</div>
        <ul class="space-y-1 px-3">
            <?php foreach ($navItems as $item):
                $isActive = strpos($item['url'], 'page=' . $currentPage) !== false;
            ?>
            <li>
                <a href="<?= htmlspecialchars($item['url']) ?>"
                   class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs transition-colors duration-150 <?= $isActive ? 'bg-white/10 font-semibold text-white' : 'text-slate-400 hover:bg-white/5 hover:text-slate-100' ?>">
                    <span class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-full bg-[#eab308] transition-opacity duration-150 <?= $isActive ? 'opacity-100' : 'opacity-0 group-hover:opacity-40' ?>"></span>
                    <span class="<?= $isActive ? 'text-[#eab308]' : 'text-slate-500 group-hover:text-slate-300' ?>"><?= render_nav_icon($item['icon']) ?></span>
                    <span class="truncate"><?= htmlspecialchars($item['label']) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="mx-5 mt-6 rounded-xl border border-white/5 bg-white/[0.03] p-3">
            <div class="flex items-center gap-2">
                <span class="flex h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                <span class="text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Sistem Aktif</span>
            </div>
            <p class="mt-1.5 text-[10px] leading-relaxed text-slate-500">Terhubung dengan basis data kepegawaian.</p>
        </div>
    </nav>

    <!-- Footer sidebar -->
    <div class="flex-shrink-0 border-t border-white/5 p-4">
        <div class="flex items-center gap-2.5 rounded-xl bg-white/[0.04] px-3 py-2.5">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-700/70 text-[11px] font-bold text-white"><?= htmlspecialchars($initial) ?></div>
            <div class="min-w-0">
                <div class="truncate text-[11px] font-semibold text-slate-200"><?= htmlspecialchars($namaUser) ?></div>
                <div class="truncate text-[9px] uppercase tracking-wider text-slate-500"><?= htmlspecialchars($roleLabel) ?></div>
            </div>
        </div>
        <div class="mt-3 text-center text-[9px] uppercase tracking-[0.12em] text-slate-600">E-Kinerja v1.0 &middot; BKN</div>
    </div>
</aside>

<div class="app-shell flex min-w-0 flex-1 flex-col overflow-hidden bg-slate-100">

    <!-- Topbar -->
    <header class="sticky top-0 z-20 flex h-[70px] flex-shrink-0 items-center justify-between gap-3 border-b border-slate-200 bg-white/80 px-4 backdrop-blur lg:px-6">

        <!-- Kiri -->
        <div class="flex min-w-0 items-center gap-2 sm:gap-3">
            <button type="button" onclick="toggleSidebar()" class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 lg:hidden" aria-label="Buka menu navigasi">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <div class="hidden items-center gap-2 sm:flex">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Portal Kinerja ASN</span>
            </div>

            <div class="hidden h-7 w-px bg-slate-200 sm:block"></div>

            <div class="flex min-w-0 items-center gap-1.5 sm:gap-2">
                <label for="tahunAnggaran" class="hidden text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500 md:block">Tahun Anggaran</label>
                <select id="tahunAnggaran" onchange="changeTahun(this.value)" aria-label="Tahun anggaran" class="h-8 max-w-[5.5rem] rounded-lg border border-slate-200 bg-white px-2 pr-6 text-xs font-semibold text-slate-700 focus:border-blue-500 sm:max-w-none">
                    <?php foreach ($tahun_options as $tahun): ?>
                        <option value="<?= (int) $tahun ?>" <?= $selected_tahun === (int) $tahun ? 'selected' : '' ?>><?= (int) $tahun ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Kanan -->
        <div class="flex flex-shrink-0 items-center gap-1.5 sm:gap-4">
            <div class="hidden text-right lg:block">
                <div class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-400">Hari Ini</div>
                <div class="text-[11px] font-semibold text-slate-700"><?= htmlspecialchars($tanggalHariIni) ?></div>
            </div>

            <!-- User menu -->
            <div id="userMenuWrap" class="group relative">
                <button type="button" onclick="toggleUserMenu(event)" aria-expanded="false" aria-controls="userMenu" class="flex items-center gap-2.5 rounded-lg px-1.5 py-1.5 hover:bg-slate-100/80">
                    <div class="relative">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-slate-800 to-slate-700 text-xs font-bold text-white shadow-sm"><?= htmlspecialchars($initial) ?></div>
                        <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                    </div>
                    <div class="hidden text-left sm:block">
                        <div class="max-w-[150px] truncate text-[12px] font-semibold leading-tight text-slate-800"><?= htmlspecialchars($namaUser) ?></div>
                        <div class="text-[9px] uppercase tracking-wider text-slate-500"><?= htmlspecialchars($roleLabel) ?></div>
                    </div>
                    <svg class="hidden h-4 w-4 text-slate-400 transition-transform duration-200 group-hover:rotate-180 sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div id="userMenu" class="invisible absolute right-0 top-full z-50 mt-2 w-52 translate-y-1 rounded-xl border border-slate-200 bg-white p-1.5 opacity-0 shadow-xl transition-all duration-150 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
                    <div class="border-b border-slate-100 px-3 py-2 sm:hidden">
                        <div class="truncate text-xs font-semibold text-slate-800"><?= htmlspecialchars($namaUser) ?></div>
                        <div class="text-[10px] uppercase tracking-wider text-slate-500"><?= htmlspecialchars($roleLabel) ?></div>
                    </div>
                    <a href="index.php?page=profil" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil Saya
                    </a>
                    <div class="my-1 border-t border-slate-100"></div>
                    <a href="logout.php" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-3 pb-8 sm:p-4 lg:p-6">
<script src="assets/js/navigation.js"></script>
