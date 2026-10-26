<?php
/**
 * config/panduan.php - Helper Buku Panduan.
 *
 * Semua halaman panduan (banner, halaman baca, dan PDF) mengambil isi
 * dari config/panduan_data.php agar tidak ada duplikasi konten.
 */

if (!function_exists('panduan_topics')) {
    function panduan_topics(): array
    {
        static $topics = null;

        if ($topics === null) {
            $topics = require __DIR__ . '/panduan_data.php';
        }

        return $topics;
    }
}

if (!function_exists('panduan_topic_aliases')) {
    function panduan_topic_aliases(): array
    {
        return [
            'pegawai' => 'guru_pegawai',
            'guru' => 'guru_pegawai',
            'penilai' => 'ketua_yayasan',
            'ketua' => 'ketua_yayasan',
            'yayasan' => 'ketua_yayasan',
            'administrator' => 'admin',
        ];
    }
}

if (!function_exists('resolve_panduan_topic')) {
    function resolve_panduan_topic(?string $topic): string
    {
        $key = strtolower(trim((string) $topic));
        $aliases = panduan_topic_aliases();
        $key = $aliases[$key] ?? $key;

        return array_key_exists($key, panduan_topics()) ? $key : 'guru_pegawai';
    }
}

if (!function_exists('get_panduan_topic')) {
    function get_panduan_topic(?string $topic): array
    {
        return panduan_topics()[resolve_panduan_topic($topic)];
    }
}

if (!function_exists('panduan_accent')) {
    function panduan_accent(string $accent): array
    {
        $palette = [
            'blue' => [
                'text' => 'text-blue-700',
                'soft' => 'bg-blue-50',
                'softText' => 'text-blue-900',
                'border' => 'border-blue-200',
                'ring' => 'ring-blue-200',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
                'solid' => 'bg-blue-700',
                'hover' => 'hover:bg-blue-800',
            ],
            'purple' => [
                'text' => 'text-purple-700',
                'soft' => 'bg-purple-50',
                'softText' => 'text-purple-900',
                'border' => 'border-purple-200',
                'ring' => 'ring-purple-200',
                'badge' => 'bg-purple-100 text-purple-800 border-purple-200',
                'solid' => 'bg-purple-700',
                'hover' => 'hover:bg-purple-800',
            ],
            'red' => [
                'text' => 'text-red-700',
                'soft' => 'bg-red-50',
                'softText' => 'text-red-900',
                'border' => 'border-red-200',
                'ring' => 'ring-red-200',
                'badge' => 'bg-red-100 text-red-800 border-red-200',
                'solid' => 'bg-red-700',
                'hover' => 'hover:bg-red-800',
            ],
            'emerald' => [
                'text' => 'text-emerald-700',
                'soft' => 'bg-emerald-50',
                'softText' => 'text-emerald-900',
                'border' => 'border-emerald-200',
                'ring' => 'ring-emerald-200',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'solid' => 'bg-emerald-700',
                'hover' => 'hover:bg-emerald-800',
            ],
            'amber' => [
                'text' => 'text-amber-700',
                'soft' => 'bg-amber-50',
                'softText' => 'text-amber-900',
                'border' => 'border-amber-200',
                'ring' => 'ring-amber-200',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
                'solid' => 'bg-amber-600',
                'hover' => 'hover:bg-amber-700',
            ],
        ];

        return $palette[$accent] ?? $palette['blue'];
    }
}

if (!function_exists('panduan_icon')) {
    function panduan_icon(string $name, string $class = 'h-5 w-5'): string
    {
        $paths = [
            'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
            'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'settings' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
            'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'book' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
            'download' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>',
            'print' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2z"/>',
        ];

        return '<svg class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" fill="none" stroke="currentColor" viewBox="0 0 24 24">'
            . ($paths[$name] ?? $paths['book'])
            . '</svg>';
    }
}
