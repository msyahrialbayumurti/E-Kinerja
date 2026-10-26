/**
 * assets/js/toast.js
 * Notifikasi melayang non-blocking untuk seluruh halaman.
 */

function appNotify(message, type = 'success') {
    const container = document.getElementById('appToast');
    if (!container) {
        alert(message);
        return;
    }

    const palette = {
        success: { bar: 'bg-emerald-500', icon: 'text-emerald-600', ring: 'ring-emerald-100' },
        error: { bar: 'bg-red-500', icon: 'text-red-600', ring: 'ring-red-100' },
        info: { bar: 'bg-blue-500', icon: 'text-blue-600', ring: 'ring-blue-100' }
    };
    const color = palette[type] || palette.info;
    const iconPath = type === 'error'
        ? 'M12 9v4m0 4h.01M10.29 3.86l-8.18 14.14A2 2 0 003.82 21h16.36a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'
        : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';

    const toast = document.createElement('div');
    toast.className = 'pointer-events-auto relative flex items-start gap-3 overflow-hidden rounded-xl border border-slate-200 bg-white p-3 pl-4 pr-4 shadow-xl';
    toast.style.animation = 'toastIn 0.25s ease-out both';
    toast.innerHTML =
        '<span class="absolute left-0 top-0 h-full w-1 ' + color.bar + '"></span>' +
        '<span class="mt-0.5 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full ring-4 ' + color.ring + ' ' + color.icon + '">' +
            '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' + iconPath + '"/></svg>' +
        '</span>' +
        '<span class="toast-message text-xs font-medium leading-relaxed text-slate-700"></span>';
    toast.querySelector('.toast-message').textContent = message;

    container.appendChild(toast);
    setTimeout(function () {
        toast.style.transition = 'opacity .25s ease, transform .25s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(8px)';
        setTimeout(function () { toast.remove(); }, 260);
    }, 3500);
}
