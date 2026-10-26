<?php
/**
 * api/PanduanDownloadController.php
 * Membuat dan mengunduh Buku Panduan dalam format PDF.
 *
 * Panduan yang diunduh selalu mengikuti role pengguna yang sedang login.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/panduan.php';
require_once __DIR__ . '/../layout/panduan_pdf.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

$topicKey = resolve_panduan_topic($_SESSION['role'] ?? 'guru_pegawai');
$topic = get_panduan_topic($topicKey);
$dateLabel = (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('j F Y');

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('defaultPaperSize', 'A4');

$dompdf = new Dompdf($options);
$dompdf->loadHtml(render_panduan_pdf_html($topic, $dateLabel), 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
if ($font) {
    $dompdf->getCanvas()->page_text(
        244,
        812,
        'Halaman {PAGE_NUM} dari {PAGE_COUNT}',
        $font,
        8,
        [0.45, 0.5, 0.58]
    );
}

$filename = 'Panduan-' . $topic['slug'] . '-' . date('Ymd') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
