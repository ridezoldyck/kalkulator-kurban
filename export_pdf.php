<?php
session_start();
require 'vendor/autoload.php'; // Dompdf via composer

use Dompdf\Dompdf;
use Dompdf\Options;

// Cek data
if (empty($_SESSION['hasil']) || empty($_SESSION['form_data'])) {
    die("❌ Tidak ada data untuk dicetak. Silakan hitung pembagian terlebih dahulu.");
}

$hasil = $_SESSION['hasil'];
$form = $_SESSION['form_data'];
unset($_SESSION['hasil'], $_SESSION['form_data']); // hapus setelah ambil

$html = '
    <h2 style="text-align:center;">Laporan Pembagian Daging Kurban</h2>
    <p><strong>Nama DKM:</strong> ' . htmlspecialchars($form['nama_dkm']) . '</p>
    <hr>
    <table border="1" cellspacing="0" cellpadding="6" width="100%">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th>Jenis</th>
                <th>Jumlah (kg)</th>
            </tr>
        </thead>
        <tbody>';

foreach ($hasil as $key => $value) {
    $label = ucwords(str_replace('_', ' ', $key));
    $html .= "<tr><td>{$label}</td><td>{$value} kg</td></tr>";
}

$html .= '</tbody></table>';
$html .= '<br><p style="text-align:right;">Dicetak pada: ' . date('d-m-Y H:i') . '</p>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('laporan_kurban_' . date('Ymd_His') . '.pdf', ['Attachment' => true]);
exit;
