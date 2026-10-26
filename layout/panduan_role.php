<?php
/**
 * layout/panduan_role.php - Banner & modal panduan sesuai role.
 * Isi diambil dari config/panduan_data.php agar tidak ada duplikasi.
 */

require_once __DIR__ . '/../config/panduan.php';

$panduanData = get_panduan_topic($role ?? ($_SESSION['role'] ?? 'guru_pegawai'));
$panduanAccent = panduan_accent($panduanData['accent']);
$panduanDownloadUrl = 'api/PanduanDownloadController.php';
?>

<div class="mb-5 overflow-hidden rounded-xl border <?= $panduanAccent['border'] ?> bg-gradient-to-r from-blue-50 via-white to-amber-50 shadow-sm">
    <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
        <div class="flex items-start gap-3">
            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl <?= $panduanAccent['soft'] . ' ' . $panduanAccent['text'] ?> ring-1 <?= $panduanAccent['border'] ?>">
                <?= panduan_icon($panduanData['icon'], 'h-6 w-6') ?>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold uppercase tracking-[0.14em] <?= $panduanAccent['text'] ?>">Panduan Penggunaan</div>
                <h2 class="mt-0.5 text-base font-bold text-slate-900">Panduan untuk <?= htmlspecialchars($panduanData['label'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="mt-1 max-w-2xl text-xs leading-relaxed text-slate-600"><?= htmlspecialchars($panduanData['summary'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
        <div class="flex w-full flex-shrink-0 flex-col gap-2 sm:w-auto sm:flex-row">
            <button type="button" onclick="openPanduanRole()" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#1e3a8a] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-blue-800 sm:w-auto">
                <?= panduan_icon('book', 'h-4 w-4') ?>
                Lihat Panduan
            </button>
            <a href="<?= htmlspecialchars($panduanDownloadUrl, ENT_QUOTES, 'UTF-8') ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-blue-300 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-blue-800 shadow-sm hover:bg-blue-50 sm:w-auto">
                <?= panduan_icon('download', 'h-4 w-4') ?>
                Unduh PDF
            </a>
        </div>
    </div>
</div>

<div id="modalPanduanRole" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/60 p-4" onclick="if (event.target === this) closePanduanRole()">
    <div class="modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
        <div class="flex flex-shrink-0 items-start justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-4 sm:px-5">
            <div>
                <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-blue-700">Buku Panduan</div>
                <h2 class="mt-0.5 text-base font-bold text-slate-900"><?= htmlspecialchars($panduanData['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <button type="button" onclick="closePanduanRole()" class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-xl leading-none text-slate-500 hover:bg-slate-200 hover:text-slate-800" aria-label="Tutup panduan">&times;</button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-4 py-5 sm:px-6">
            <div class="rounded-lg border <?= $panduanAccent['border'] . ' ' . $panduanAccent['soft'] ?> p-3 text-xs leading-relaxed <?= $panduanAccent['softText'] ?>">
                <strong>Untuk siapa panduan ini?</strong> Panduan ini khusus untuk <strong><?= htmlspecialchars($panduanData['label'], ENT_QUOTES, 'UTF-8') ?></strong>. Ikuti langkahnya satu per satu. Tidak perlu terburu-buru.
            </div>

            <h3 class="mt-5 text-sm font-bold text-slate-900">Langkah-langkah</h3>
            <ol class="mt-3 space-y-4">
                <?php foreach ($panduanData['steps'] as $index => $step): ?>
                    <li class="flex gap-3">
                        <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full text-[11px] font-bold text-white <?= $panduanAccent['solid'] ?>"><?= $index + 1 ?></span>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-slate-800"><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <ul class="mt-1.5 list-disc space-y-1 pl-4 text-sm leading-relaxed text-slate-700">
                                <?php foreach ($step['items'] as $item): ?>
                                    <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>

            <h3 class="mt-5 text-sm font-bold text-slate-900">Hal yang perlu diingat</h3>
            <ul class="mt-2 list-disc space-y-1.5 pl-5 text-sm leading-relaxed text-slate-700">
                <?php foreach ($panduanData['tips'] as $tip): ?>
                    <li><?= htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>

            <h3 class="mt-5 text-sm font-bold text-slate-900">Istilah penting</h3>
            <div class="table-scroll mt-2 overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full min-w-[360px] text-left text-xs">
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($panduanData['terms'] as $term => $meaning): ?>
                            <tr>
                                <th class="w-28 bg-slate-50 px-3 py-2 align-top font-bold text-slate-700"><?= htmlspecialchars($term, ENT_QUOTES, 'UTF-8') ?></th>
                                <td class="px-3 py-2 text-slate-600"><?= htmlspecialchars($meaning, ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs leading-relaxed text-amber-900">
                <strong>Butuh bantuan?</strong> Hubungi Admin Kepegawaian. Sebutkan nama, menu yang dibuka, dan pesan kesalahan yang muncul. Jangan pernah membagikan password kepada siapa pun.
            </div>
        </div>

        <div class="flex flex-shrink-0 flex-col gap-2 border-t border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <a href="index.php?page=buku_panduan" class="text-xs font-bold text-blue-700 hover:text-blue-900">Buka halaman Buku Panduan</a>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="<?= htmlspecialchars($panduanDownloadUrl, ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#1e3a8a] px-4 py-2 text-xs font-bold text-white hover:bg-blue-800">
                    <?= panduan_icon('download', 'h-4 w-4') ?>
                    Unduh PDF
                </a>
                <button type="button" onclick="closePanduanRole()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function openPanduanRole() {
    const modal = document.getElementById('modalPanduanRole');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closePanduanRole() {
    const modal = document.getElementById('modalPanduanRole');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closePanduanRole();
});
</script>
