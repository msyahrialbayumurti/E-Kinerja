<?php
/**
 * views/buku_panduan.php - Halaman Buku Panduan Multi-Role.
 * Isi panduan diambil dari config/panduan_data.php (satu sumber data).
 */

require_once __DIR__ . '/../config/panduan.php';

$currentRole = $_SESSION['role'] ?? 'guru_pegawai';
$topic = get_panduan_topic($currentRole);
$accent = panduan_accent($topic['accent']);
$downloadUrl = 'api/PanduanDownloadController.php';
?>

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-blue-700">Pusat Bantuan</div>
        <h1 class="mt-1 text-2xl font-bold text-slate-800">Buku Panduan</h1>
        <p class="mt-1 max-w-3xl text-sm leading-relaxed text-slate-500">
            Panduan ini disesuaikan dengan peran Anda sebagai <strong><?= htmlspecialchars($topic['label'], ENT_QUOTES, 'UTF-8') ?></strong>. Baca langsung di halaman ini atau unduh dalam bentuk PDF.
        </p>
    </div>
    <a href="<?= htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8') ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#1e3a8a] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-blue-800 sm:w-auto">
        <?= panduan_icon('download', 'h-4 w-4') ?>
        Unduh PDF Panduan Ini
    </a>
</div>

<article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <header class="border-b border-slate-200 p-5 sm:p-6 <?= $accent['soft'] ?>">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-white/80 <?= $accent['text'] ?> ring-1 <?= $accent['border'] ?>">
                    <?= panduan_icon($topic['icon'], 'h-6 w-6') ?>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-[0.14em] <?= $accent['text'] ?>"><?= htmlspecialchars($topic['label'], ENT_QUOTES, 'UTF-8') ?></div>
                    <h2 class="mt-1 text-xl font-bold leading-tight text-slate-900"><?= htmlspecialchars($topic['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-600"><?= htmlspecialchars($topic['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
            <a href="<?= htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8') ?>" class="inline-flex w-full flex-shrink-0 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 sm:w-auto">
                <?= panduan_icon('download', 'h-4 w-4') ?>
                Unduh PDF
            </a>
        </div>
    </header>

    <div class="p-5 sm:p-7">
        <div class="rounded-lg border <?= $accent['border'] . ' ' . $accent['soft'] ?> p-4">
            <p class="text-sm leading-relaxed <?= $accent['softText'] ?>">
                <strong>Cara memakai panduan ini:</strong> baca satu langkah, lalu langsung praktikkan. Tidak perlu terburu-buru. Jika ada yang belum jelas, lihat bagian <strong>Butuh bantuan?</strong> di bagian bawah.
            </p>
            <p class="mt-2 text-sm leading-relaxed <?= $accent['softText'] ?>"><?= htmlspecialchars($topic['intro'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <section class="mt-8" aria-labelledby="judul-langkah">
            <h3 id="judul-langkah" class="text-base font-bold text-slate-900">Langkah-langkah</h3>
            <ol class="mt-4 space-y-5">
                <?php foreach ($topic['steps'] as $index => $step): ?>
                    <li class="flex gap-3 sm:gap-4">
                        <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold text-white <?= $accent['solid'] ?>"><?= $index + 1 ?></span>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-slate-800"><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <ul class="mt-2 list-disc space-y-1.5 pl-5 text-sm leading-relaxed text-slate-600">
                                <?php foreach ($step['items'] as $item): ?>
                                    <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>

        <?php if (!empty($topic['symbols'])): ?>
            <section class="mt-8" aria-labelledby="judul-simbol">
                <h3 id="judul-simbol" class="text-base font-bold text-slate-900">Arti simbol umpan balik</h3>
                <div class="table-scroll mt-3 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full min-w-[420px] text-left text-sm">
                        <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="w-36 px-4 py-2.5 font-bold">Simbol</th>
                                <th class="px-4 py-2.5 font-bold">Artinya</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($topic['symbols'] as $symbol): ?>
                                <?php $symbolAccent = panduan_accent($symbol['accent']); ?>
                                <tr>
                                    <td class="px-4 py-2.5">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider <?= $symbolAccent['badge'] ?>">
                                            <?= htmlspecialchars($symbol['label'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 leading-relaxed text-slate-600"><?= htmlspecialchars($symbol['meaning'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <section class="mt-8" aria-labelledby="judul-ingat">
            <h3 id="judul-ingat" class="text-base font-bold text-slate-900">Hal yang perlu diingat</h3>
            <ul class="mt-3 list-disc space-y-1.5 rounded-lg border border-amber-200 bg-amber-50 p-4 pl-8 text-sm leading-relaxed text-amber-900">
                <?php foreach ($topic['tips'] as $tip): ?>
                    <li><?= htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="mt-8" aria-labelledby="judul-istilah">
            <h3 id="judul-istilah" class="text-base font-bold text-slate-900">Istilah penting</h3>
            <div class="table-scroll mt-3 overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full min-w-[420px] text-left text-sm">
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($topic['terms'] as $term => $meaning): ?>
                            <tr>
                                <th class="w-36 bg-slate-50 px-4 py-2.5 align-top font-bold text-slate-700"><?= htmlspecialchars($term, ENT_QUOTES, 'UTF-8') ?></th>
                                <td class="px-4 py-2.5 leading-relaxed text-slate-600"><?= htmlspecialchars($meaning, ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-8" aria-labelledby="judul-bantuan">
            <h3 id="judul-bantuan" class="text-base font-bold text-slate-900">Butuh bantuan?</h3>
            <ul class="mt-3 list-disc space-y-1.5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 pl-8 text-sm leading-relaxed text-emerald-900">
                <?php foreach ($topic['help'] as $help): ?>
                    <li><?= htmlspecialchars($help, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>
</article>
