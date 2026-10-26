/**
 * assets/js/navigation.js
 * Interaksi sidebar dan pemilih tahun anggaran.
 */

function toggleSidebar() {
    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!sidebar || !overlay) return;

    const isOpen = !sidebar.classList.contains('-translate-x-full');
    if (isOpen) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    } else {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;

    closeUserMenu();

    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar && overlay && !overlay.classList.contains('hidden')) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
});

function toggleUserMenu(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('userMenu');
    const button = document.querySelector('[aria-controls="userMenu"]');
    if (!menu) return;

    const isOpen = menu.classList.toggle('user-menu-open');
    if (button) button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

function closeUserMenu() {
    const menu = document.getElementById('userMenu');
    const button = document.querySelector('[aria-controls="userMenu"]');
    if (!menu) return;

    menu.classList.remove('user-menu-open');
    if (button) button.setAttribute('aria-expanded', 'false');
}

document.addEventListener('click', function (event) {
    if (event.target.closest('#userMenuWrap')) return;
    closeUserMenu();
});

function changeTahun(tahun) {
    const url = new URL(window.location.href);
    url.searchParams.set('tahun', tahun);
    if (!url.searchParams.get('page')) {
        url.searchParams.set('page', 'penilaian_eviden');
    }
    window.location.href = url.toString();
}
