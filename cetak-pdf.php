<?php
require_once 'config/database.php';
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

if (isset($_GET['tanggal'])) {
    $tanggal = $_GET['tanggal'];
    
    // Data untuk laporan
    $hari_indo = [
        'Sunday' => 'MINGGU',
        'Monday' => 'SENIN',
        'Tuesday' => 'SELASA',
        'Wednesday' => 'RABU',
        'Thursday' => 'KAMIS',
        'Friday' => 'JUMAT',
        'Saturday' => 'SABTU'
    ];
    $bulan_indo = [
        '01' => 'JANUARI', '02' => 'FEBRUARI', '03' => 'MARET', '04' => 'APRIL',
        '05' => 'MEI', '06' => 'JUNI', '07' => 'JULI', '08' => 'AGUSTUS',
        '09' => 'SEPTEMBER', '10' => 'OKTOBER', '11' => 'NOVEMBER', '12' => 'DESEMBER'
    ];
    
    $hari = $hari_indo[date('l', strtotime($tanggal))];
    $bulan = $bulan_indo[date('m', strtotime($tanggal))];
    $tahun = date('Y', strtotime($tanggal));
    $tanggal_format = date('d', strtotime($tanggal));
    
    // Ambil data laporan
    $query = "SELECT * FROM laporan_harian WHERE tanggal = '$tanggal' ORDER BY shift";
    $result = mysqli_query($conn, $query);
    $laporan_shift = [];
    $total_pemasukan = 0;
    $total_pengeluaran = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $laporan_shift[] = $row;
        $total_pemasukan += $row['pemasukan'];
        $total_pengeluaran += $row['pengeluaran'];
    }
    
    $total_keseluruhan = $total_pemasukan - $total_pengeluaran;
    $status_keseluruhan = $total_keseluruhan >= 0 ? 'LABA' : 'RUGI';
    
    // Ambil detail pengeluaran
    $detail_pengeluaran = [];
    foreach ($laporan_shift as $laporan) {
        $query_detail = "SELECT * FROM detail_pengeluaran WHERE laporan_id = " . $laporan['id'];
        $result_detail = mysqli_query($conn, $query_detail);
        while ($row = mysqli_fetch_assoc($result_detail)) {
            $detail_pengeluaran[] = $row;
        }
    }
    
    // Ambil stok barang
    $query_stok = "SELECT * FROM stok_barang ORDER BY 
                  CASE 
                    WHEN nama_barang IN ('Air Galon', 'Kopi (Stok Toples)', 'Milo (Stok Toples)', 'Teh (Stok Toples)', 'Gas') THEN 1
                    ELSE 2
                  END,
                  CASE 
                    WHEN nama_barang = 'Air Galon' THEN 1
                    WHEN nama_barang = 'Kopi (Stok Toples)' THEN 2
                    WHEN nama_barang = 'Milo (Stok Toples)' THEN 3
                    WHEN nama_barang = 'Teh (Stok Toples)' THEN 4
                    WHEN nama_barang = 'Gas' THEN 5
                    ELSE 6
                  END,
                  nama_barang";
    $result_stok = mysqli_query($conn, $query_stok);
    $stok_barang = [];
    while ($row = mysqli_fetch_assoc($result_stok)) {
        $stok_barang[] = $row;
    }
    
    // Data shift
    $shift_pagi = 0;
    $shift_sore = 0;
    $laporan_pagi = null;
    $laporan_sore = null;
    
    foreach ($laporan_shift as $laporan) {
        if ($laporan['shift'] == 'pagi') {
            $shift_pagi = $laporan['pemasukan'];
            $laporan_pagi = $laporan;
        }
        if ($laporan['shift'] == 'sore') {
            $shift_sore = $laporan['pemasukan'];
            $laporan_sore = $laporan;
        }
    }

    // Format nama file
    $nama_file = "LAPORAN HARIAN_ABU DAFI 5_" . date('d-m-Y', strtotime($tanggal)) . ".pdf";

    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Sistem POS Abu Dafi 5');
    $pdf->SetAuthor('Abu Dafi 5');
    $pdf->SetTitle('Laporan Harian - ' . $tanggal);
    $pdf->SetSubject('Laporan Harian');

    // Remove header and footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // ==============================================
    // HALAMAN 1: LAPORAN HARIAN
    // ==============================================
    $pdf->AddPage();
    
    // Judul
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 8, 'LAPORAN HARIAN', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'KEDAI KOPI ABU DAFI 5', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, $hari . ', ' . $tanggal_format . ' ' . $bulan . ' ' . $tahun, 0, 1, 'C');
    $pdf->Ln(8);

    // Ringkasan Shift
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'RINGKASAN SHIFT', 0, 1);
    
    $html = '<table border="1" cellpadding="3" style="font-size:11px;">
        <tr>
            <th width="50%" style="font-weight:bold; text-align:center;">Shift Pagi/Sore (08.00 - 16.00)</th>
            <th width="50%" style="font-weight:bold; text-align:center;">Shift Sore/Malam (16.00 - 00.00)</th>
        </tr>
        <tr>
            <td style="text-align:center;">Rp ' . number_format($shift_pagi, 0, ',', '.') . '</td>
            <td style="text-align:center;">Rp ' . number_format($shift_sore, 0, ',', '.') . '</td>
        </tr>
    </table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(6);

    // Pengeluaran
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 6, 'PENGELUARAN', 0, 1);
    
    if (count($detail_pengeluaran) > 0) {
        $html = '<table border="1" cellpadding="5" style="font-size:10px;">
            <tr>
                <th width="10%" style="font-weight:bold; text-align:center;">No</th>
                <th width="60%" style="font-weight:bold;">Keterangan</th>
                <th width="30%" style="font-weight:bold; text-align:right;">Jumlah</th>
            </tr>';
        
        foreach ($detail_pengeluaran as $index => $pengeluaran) {
            $html .= '<tr>
                <td style="text-align:center;">' . ($index + 1) . '</td>
                <td>' . $pengeluaran['keterangan'] . '</td>
                <td style="text-align:right;">Rp ' . number_format($pengeluaran['jumlah'], 0, ',', '.') . '</td>
            </tr>';
        }
        
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
    } else {
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 8, 'Tidak ada pengeluaran', 0, 1);
    }
    
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 8, 'TOTAL PENGELUARAN = Rp ' . number_format($total_pengeluaran, 0, ',', '.'), 0, 1);
    $pdf->Ln(8);

    // Total Keseluruhan
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'TOTAL KESELURUHAN', 0, 1);
    $pdf->SetFont('helvetica', 'B', 11);
    
    $total_text = number_format($total_pemasukan, 0, ',', '.') . ' - ' . 
                 number_format($total_pengeluaran, 0, ',', '.') . ' = ';
    
    if ($status_keseluruhan == 'RUGI') {
        $total_text .= '- ' . number_format(abs($total_keseluruhan), 0, ',', '.') . ' (' . $status_keseluruhan . ')';
        $pdf->SetTextColor(255, 0, 0);
    } else {
        $total_text .= number_format(abs($total_keseluruhan), 0, ',', '.') . ' (' . $status_keseluruhan . ')';
        $pdf->SetTextColor(0, 128, 0);
    }
    
    $pdf->Cell(0, 8, $total_text, 0, 1);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(8);

    // Sisa Stok
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'SISA STOK BARANG', 0, 1);
    
    $stok_formatted = [];
    foreach ($stok_barang as $stok) {
        $nama = $stok['nama_barang'];
        $jumlah = $stok['jumlah'];
        $satuan = $stok['satuan'];
        
        if (floor($jumlah) == $jumlah) {
            $jumlah = intval($jumlah);
        }
        
        $stok_formatted[] = $jumlah . " " . $satuan . " " . $nama;
    }
    
    // Bagi stok menjadi 2 kolom
    $mid = ceil(count($stok_formatted) / 2);
    $html = '<table border="0" cellpadding="3" style="font-size:12px;"><tr>';
    
    for ($col = 0; $col < 2; $col++) {
        $html .= '<td width="50%" valign="top">';
        for ($i = $col * $mid; $i < min(($col + 1) * $mid, count($stok_formatted)); $i++) {
            $html .= ($i + 1) . '. ' . $stok_formatted[$i] . '<br>';
        }
        $html .= '</td>';
    }
    
    $html .= '</tr></table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(6);

    // Lain-lain
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'LAIN - LAIN', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    
    $roti_stok = 0;
    foreach ($stok_barang as $stok) {
        if (strpos($stok['nama_barang'], 'Roti') !== false) {
            $roti_stok = $stok['jumlah'];
            if (floor($roti_stok) == $roti_stok) {
                $roti_stok = intval($roti_stok);
            }
            break;
        }
    }
    
    $pdf->Cell(0, 6, 'Roti laku = 0', 0, 1);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 6, '*Stok roti: ' . $roti_stok . ' *', 0, 1);

    // ==============================================
    // HALAMAN 2: FOTO SHIFT PAGI
    // ==============================================
    $pdf->AddPage();
    
    // Header
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'FOTO DOKUMENTASI STOK', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'SHIFT PAGI/SORE (08.00 - 16.00)', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, $hari . ', ' . $tanggal_format . ' ' . $bulan . ' ' . $tahun, 0, 1, 'C');
    $pdf->Ln(8);

    if ($laporan_pagi) {
        // UKURAN GAMBAR SESUAI KODINGAN SAYA: 70mm width, 65mm height
        $image_width = 65;
        $image_height = 60;
        
        // Hitung posisi X untuk center gambar
        $page_width = 210 - 30; // Lebar A4 minus margin
        $image_x = (($page_width - $image_width) / 2) + 15; // Center + margin kiri
        
        // Foto Kopi
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'KOPI', 0, 1, 'C');
        if (!empty($laporan_pagi['foto_kopi']) && file_exists('uploads/' . $laporan_pagi['foto_kopi'])) {
            $pdf->Image('uploads/' . $laporan_pagi['foto_kopi'], $image_x, $pdf->GetY(), $image_width, $image_height, '', '', 'T', false, 300, 'C');
            $pdf->Ln($image_height + 2);
            $pdf->SetFont('helvetica', 'I', 8);
        } else {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 10, 'Tidak ada foto', 0, 1, 'C');
        }
        $pdf->Ln(10);
        
        // Foto Teh
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'TEH', 0, 1, 'C');
        if (!empty($laporan_pagi['foto_teh']) && file_exists('uploads/' . $laporan_pagi['foto_teh'])) {
            $pdf->Image('uploads/' . $laporan_pagi['foto_teh'], $image_x, $pdf->GetY(), $image_width, $image_height, '', '', 'T', false, 300, 'C');
            $pdf->Ln($image_height + 2);
            $pdf->SetFont('helvetica', 'I', 8);
        } else {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 10, 'Tidak ada foto', 0, 1, 'C');
        }
        $pdf->Ln(10);
        
        // Foto Milo
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'MILO', 0, 1, 'C');
        if (!empty($laporan_pagi['foto_milo']) && file_exists('uploads/' . $laporan_pagi['foto_milo'])) {
            $pdf->Image('uploads/' . $laporan_pagi['foto_milo'], $image_x, $pdf->GetY(), $image_width, $image_height, '', '', 'T', false, 300, 'C');
            $pdf->Ln($image_height + 2);
            $pdf->SetFont('helvetica', 'I', 8);
        } else {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 10, 'Tidak ada foto', 0, 1, 'C');
        }
    } else {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetY(100);
        $pdf->Cell(0, 10, 'SHIFT PAGI BELUM DIINPUT', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Data laporan untuk shift pagi/sore belum tersedia', 0, 1, 'C');
    }

    // ==============================================
    // HALAMAN 3: FOTO SHIFT SORE
    // ==============================================
    $pdf->AddPage();
    
    // Header
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'FOTO DOKUMENTASI STOK', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'SHIFT SORE/MALAM (16.00 - 00.00)', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, $hari . ', ' . $tanggal_format . ' ' . $bulan . ' ' . $tahun, 0, 1, 'C');
    $pdf->Ln(8);

    if ($laporan_sore) {
        // UKURAN GAMBAR SESUAI KODINGAN SAYA: 70mm width, 65mm height
        $image_width = 65;
        $image_height = 60;
        
        // Hitung posisi X untuk center gambar
        $page_width = 210 - 30; // Lebar A4 minus margin
        $image_x = (($page_width - $image_width) / 2) + 15; // Center + margin kiri
        
        // Foto Kopi
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'KOPI', 0, 1, 'C');
        if (!empty($laporan_sore['foto_kopi']) && file_exists('uploads/' . $laporan_sore['foto_kopi'])) {
            $pdf->Image('uploads/' . $laporan_sore['foto_kopi'], $image_x, $pdf->GetY(), $image_width, $image_height, '', '', 'T', false, 300, 'C');
            $pdf->Ln($image_height + 2);
            $pdf->SetFont('helvetica', 'I', 8);
        } else {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 10, 'Tidak ada foto', 0, 1, 'C');
        }
        $pdf->Ln(10);
        
        // Foto Teh
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'TEH', 0, 1, 'C');
        if (!empty($laporan_sore['foto_teh']) && file_exists('uploads/' . $laporan_sore['foto_teh'])) {
            $pdf->Image('uploads/' . $laporan_sore['foto_teh'], $image_x, $pdf->GetY(), $image_width, $image_height, '', '', 'T', false, 300, 'C');
            $pdf->Ln($image_height + 2);
            $pdf->SetFont('helvetica', 'I', 8);
        } else {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 10, 'Tidak ada foto', 0, 1, 'C');
        }
        $pdf->Ln(10);
        
        // Foto Milo
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'MILO', 0, 1, 'C');
        if (!empty($laporan_sore['foto_milo']) && file_exists('uploads/' . $laporan_sore['foto_milo'])) {
            $pdf->Image('uploads/' . $laporan_sore['foto_milo'], $image_x, $pdf->GetY(), $image_width, $image_height, '', '', 'T', false, 300, 'C');
            $pdf->Ln($image_height + 2);
            $pdf->SetFont('helvetica', 'I', 8);
        } else {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 10, 'Tidak ada foto', 0, 1, 'C');
        }
    } else {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetY(100);
        $pdf->Cell(0, 10, 'SHIFT SORE BELUM DIINPUT', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Data laporan untuk shift sore/malam belum tersedia', 0, 1, 'C');
    }

    // Output PDF
    $pdf->Output($nama_file, 'D');
    exit;
} else {
    echo "Tanggal tidak ditemukan";
}
?>