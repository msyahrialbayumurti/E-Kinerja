<?php
/**
 * layout/panduan_pdf.php - Template PDF Buku Panduan.
 * Dipakai oleh api/PanduanDownloadController.php.
 */

if (!function_exists('render_panduan_pdf_html')) {
    function render_panduan_pdf_html(array $topic, string $dateLabel): string
    {
        $e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

        $stepsHtml = '';
        foreach ($topic['steps'] as $index => $step) {
            $items = '';
            foreach ($step['items'] as $item) {
                $items .= '<li>' . $e($item) . '</li>';
            }

            $stepsHtml .= '
            <div class="step">
                <table class="step-head" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="step-num">' . ($index + 1) . '</td>
                        <td class="step-title">' . $e($step['title']) . '</td>
                    </tr>
                </table>
                <ul>' . $items . '</ul>
            </div>';
        }

        $symbolsHtml = '';
        if (!empty($topic['symbols'])) {
            $rows = '';
            foreach ($topic['symbols'] as $symbol) {
                $rows .= '<tr><td class="symbol-name">' . $e($symbol['label']) . '</td><td>' . $e($symbol['meaning']) . '</td></tr>';
            }
            $symbolsHtml = '
            <div class="section-block">
                <h2>Arti Simbol Umpan Balik</h2>
                <table class="data">
                    <thead><tr><th style="width:32%">Simbol</th><th>Artinya</th></tr></thead>
                    <tbody>' . $rows . '</tbody>
                </table>
            </div>';
        }

        $tipsHtml = '';
        foreach ($topic['tips'] as $tip) {
            $tipsHtml .= '<li>' . $e($tip) . '</li>';
        }

        $termsHtml = '';
        foreach ($topic['terms'] as $term => $meaning) {
            $termsHtml .= '<tr><th>' . $e($term) . '</th><td>' . $e($meaning) . '</td></tr>';
        }

        $helpHtml = '';
        foreach ($topic['help'] as $help) {
            $helpHtml .= '<li>' . $e($help) . '</li>';
        }

        $sectionList = '';
        foreach ($topic['steps'] as $index => $step) {
            $sectionList .= '<li>' . $e($step['title']) . '</li>';
        }

        return '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>' . $e($topic['title']) . '</title>
    <style>
        @page { margin: 16mm 15mm 22mm 15mm; }
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9.5pt;
            line-height: 1.5;
            color: #1f2937;
            margin: 0;
        }
        .cover {
            background: #0f172a;
            color: #ffffff;
            padding: 20px 24px 16px 24px;
            border-bottom: 4px solid #ca8a04;
        }
        .cover .eyebrow {
            font-size: 7pt;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #cbd5e1;
            margin-bottom: 7px;
        }
        .cover h1 {
            font-size: 17pt;
            line-height: 1.2;
            margin: 0 0 5px 0;
            color: #ffffff;
        }
        .cover .subtitle {
            font-size: 9pt;
            color: #e2e8f0;
            margin: 0;
        }
        .cover .meta {
            margin-top: 11px;
            padding-top: 8px;
            border-top: 1px solid rgba(255,255,255,0.18);
            font-size: 7.5pt;
            color: #cbd5e1;
        }
        .intro {
            margin: 12px 0 14px 0;
            padding: 9px 12px;
            background: #eff6ff;
            border-left: 4px solid #1e3a8a;
            font-size: 9pt;
            color: #1e3a8a;
        }
        .intro strong { color: #0f172a; }
        .toc {
            margin: 0 0 14px 0;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            page-break-inside: avoid;
        }
        .toc-title {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .toc ol {
            margin: 0 0 0 15px;
            padding: 0;
            font-size: 8.5pt;
            color: #475569;
        }
        .toc li { margin-bottom: 1px; }
        h2 {
            font-size: 11.5pt;
            color: #1e3a8a;
            margin: 16px 0 7px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #dbeafe;
            page-break-after: avoid;
        }
        .step {
            page-break-inside: avoid;
            margin-bottom: 10px;
        }
        .section-block {
            page-break-inside: avoid;
        }
        .glossary {
            page-break-before: always;
        }
        .step-head {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
            page-break-after: avoid;
        }
        .step-num {
            width: 20px;
            height: 20px;
            background: #1e3a8a;
            color: #ffffff;
            text-align: center;
            vertical-align: middle;
            font-size: 8.5pt;
            font-weight: bold;
            border-radius: 10px;
        }
        .step-title {
            padding-left: 8px;
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            vertical-align: middle;
        }
        ul {
            margin: 0 0 0 16px;
            padding: 0;
        }
        li {
            margin-bottom: 2px;
            font-size: 9pt;
            color: #334155;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        table.data th,
        table.data td {
            border: 1px solid #e2e8f0;
            padding: 5px 7px;
            font-size: 8.5pt;
            text-align: left;
            vertical-align: top;
        }
        table.data th {
            background: #f1f5f9;
            color: #334155;
            font-weight: bold;
        }
        table.data .symbol-name {
            width: 30%;
            font-weight: bold;
            color: #1e3a8a;
        }
        .note {
            page-break-inside: avoid;
            margin-top: 14px;
            padding: 10px 12px;
            background: #fffbeb;
            border: 1px solid #fde68a;
        }
        .note-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #92400e;
            margin-bottom: 4px;
        }
        .note ul { margin-left: 16px; }
        .note li { color: #78350f; }
        .help {
            page-break-inside: avoid;
            margin-top: 14px;
            padding: 10px 12px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }
        .help-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #166534;
            margin-bottom: 4px;
        }
        .help ul { margin-left: 16px; }
        .help li { color: #14532d; }
    </style>
</head>
<body>
    <div class="cover">
        <div class="eyebrow">E-Kinerja | Buku Panduan Pengguna</div>
        <h1>' . $e($topic['title']) . '</h1>
        <p class="subtitle">' . $e($topic['summary']) . '</p>
        <div class="meta">Untuk: ' . $e($topic['label']) . ' &nbsp;|&nbsp; Diunduh: ' . $e($dateLabel) . '</div>
    </div>

    <div class="intro">
        <strong>Cara memakai panduan ini:</strong> baca satu langkah, lalu langsung praktikkan. Tidak perlu terburu-buru.
        ' . $e($topic['intro']) . '
    </div>

    <div class="toc">
        <div class="toc-title">Isi Panduan</div>
        <ol>' . $sectionList . '</ol>
    </div>

    <h2>Langkah-Langkah</h2>
    ' . $stepsHtml . '
    ' . $symbolsHtml . '

    <div class="note">
        <div class="note-title">Hal yang perlu diingat</div>
        <ul>' . $tipsHtml . '</ul>
    </div>

    <div class="section-block glossary">
        <h2>Istilah Penting</h2>
        <table class="data">
            <tbody>' . $termsHtml . '</tbody>
        </table>
    </div>

    <div class="help">
        <div class="help-title">Butuh bantuan?</div>
        <ul>' . $helpHtml . '</ul>
    </div>
</body>
</html>';
    }
}
