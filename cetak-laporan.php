<?php include 'includes/header.php'; ?>

<?php
// Fungsi untuk format angka genap dan bulatkan jika 0 di belakang koma
function formatAngka($angka) {
    // Cek jika angka memiliki desimal .0
    if (is_numeric($angka) && floor($angka) != $angka) {
        // Jika ada desimal, cek apakah desimalnya 0
        $parts = explode('.', $angka);
        if (isset($parts[1]) && intval($parts[1]) == 0) {
            return intval($angka);
        }
    }
    return ($angka == intval($angka)) ? intval($angka) : $angka;
}
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-print"></i> Cetak & Kirim Laporan</h2>
    </div>
</div>

<!-- Form Pilih Tanggal -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calendar"></i> Pilih Tanggal Laporan</h5>
            </div>
            <div class="card-body">
                <form action="" method="GET">
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal Laporan</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" 
                               value="<?php echo $_GET['tanggal'] ?? date('Y-m-d'); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tampilkan Laporan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($_GET['tanggal'])) {
    $tanggal = $_GET['tanggal'];
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
    
    // Ambil stok barang dengan urutan yang sama seperti di halaman stok
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
?>
<!-- Laporan Harian -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Laporan Harian - <?php echo "$hari, {$tanggal_format}.$bulan $tahun"; ?></h5>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" onclick="copyToClipboard()">
                    <i class="fas fa-copy"></i> Copy Teks Laporan
                </button>
                    <a href="cetak-pdf.php?tanggal=<?php echo $tanggal; ?>" class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Cetak PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Ringkasan Shift -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Pagi/Sore (08.00 - 16.00)</h6>
                        <p class="fs-4">
                            <?php
                            $shift_pagi = 0;
                            $laporan_pagi = null;
                            foreach ($laporan_shift as $laporan) {
                                if ($laporan['shift'] == 'pagi') {
                                    $shift_pagi = $laporan['pemasukan'];
                                    $laporan_pagi = $laporan;
                                    echo 'Rp ' . number_format($laporan['pemasukan'], 0, ',', '.');
                                    break;
                                }
                            }
                            if ($shift_pagi == 0) echo '0';
                            ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6>Sore/Malam (16.00 - 00.00)</h6>
                        <p class="fs-4">
                            <?php
                            $shift_sore = 0;
                            $laporan_sore = null;
                            foreach ($laporan_shift as $laporan) {
                                if ($laporan['shift'] == 'sore') {
                                    $shift_sore = $laporan['pemasukan'];
                                    $laporan_sore = $laporan;
                                    echo 'Rp ' . number_format($laporan['pemasukan'], 0, ',', '.');
                                    break;
                                }
                            }
                            if ($shift_sore == 0) echo '0';
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Foto Dokumentasi -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6>FOTO DOKUMENTASI STOK</h6>
                        <div class="row">
                            <!-- Shift Pagi -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-sun text-warning"></i> Shift Pagi/Sore</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php if ($laporan_pagi): ?>
                                                <div class="col-4 text-center">
                                                    <h6>Kopi</h6>
                                                    <?php if (!empty($laporan_pagi['foto_kopi'])): ?>
                                                        <img src="uploads/<?php echo $laporan_pagi['foto_kopi']; ?>" 
                                                             class="img-thumbnail" style="max-height: 100px; max-width: 100%;">
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <i class="fas fa-coffee fa-2x mb-2"></i><br>
                                                            <small>Tidak ada foto</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <h6>Teh</h6>
                                                    <?php if (!empty($laporan_pagi['foto_teh'])): ?>
                                                        <img src="uploads/<?php echo $laporan_pagi['foto_teh']; ?>" 
                                                             class="img-thumbnail" style="max-height: 100px; max-width: 100%;">
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <i class="fas fa-mug-hot fa-2x mb-2"></i><br>
                                                            <small>Tidak ada foto</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <h6>Milo</h6>
                                                    <?php if (!empty($laporan_pagi['foto_milo'])): ?>
                                                        <img src="uploads/<?php echo $laporan_pagi['foto_milo']; ?>" 
                                                             class="img-thumbnail" style="max-height: 100px; max-width: 100%;">
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <i class="fas fa-coffee fa-2x mb-2"></i><br>
                                                            <small>Tidak ada foto</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="col-12 text-center text-muted">
                                                    <i class="fas fa-times-circle fa-2x mb-2"></i><br>
                                                    <small>Shift pagi belum diinput</small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Shift Sore -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-moon text-primary"></i> Shift Sore/Malam</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php if ($laporan_sore): ?>
                                                <div class="col-4 text-center">
                                                    <h6>Kopi</h6>
                                                    <?php if (!empty($laporan_sore['foto_kopi'])): ?>
                                                        <img src="uploads/<?php echo $laporan_sore['foto_kopi']; ?>" 
                                                             class="img-thumbnail" style="max-height: 100px; max-width: 100%;">
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <i class="fas fa-coffee fa-2x mb-2"></i><br>
                                                            <small>Tidak ada foto</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <h6>Teh</h6>
                                                    <?php if (!empty($laporan_sore['foto_teh'])): ?>
                                                        <img src="uploads/<?php echo $laporan_sore['foto_teh']; ?>" 
                                                             class="img-thumbnail" style="max-height: 100px; max-width: 100%;">
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <i class="fas fa-mug-hot fa-2x mb-2"></i><br>
                                                            <small>Tidak ada foto</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <h6>Milo</h6>
                                                    <?php if (!empty($laporan_sore['foto_milo'])): ?>
                                                        <img src="uploads/<?php echo $laporan_sore['foto_milo']; ?>" 
                                                             class="img-thumbnail" style="max-height: 100px; max-width: 100%;">
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <i class="fas fa-coffee fa-2x mb-2"></i><br>
                                                            <small>Tidak ada foto</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="col-12 text-center text-muted">
                                                    <i class="fas fa-times-circle fa-2x mb-2"></i><br>
                                                    <small>Shift sore belum diinput</small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detail Pengeluaran -->
                <h6>PENGELUARAN</h6>
                <?php
                $counter = 1;
                foreach ($detail_pengeluaran as $pengeluaran) {
                    echo '<p>' . $counter++ . '. ' . $pengeluaran['keterangan'] . ' : Rp ' . 
                         number_format($pengeluaran['jumlah'], 0, ',', '.') . '</p>';
                }
                if ($counter == 1) {
                    echo '<p class="text-muted">Tidak ada pengeluaran</p>';
                }
                ?>
                
                <!-- Total Pengeluaran -->
                <div class="mt-3">
                    <h6>TOTAL PENGELUARAN = Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></h6>
                </div>
                
                <!-- Total -->
                <div class="mt-4 p-3 bg-light rounded">
                    <h4>TOTAL KESELURUHAN</h4>
                    <h3 class="<?php echo $status_keseluruhan == 'LABA' ? 'text-success' : 'text-danger'; ?>">
                        <?php
                        if ($status_keseluruhan == 'RUGI') {
                            echo number_format($total_pemasukan, 0, ',', '.') . ' - ' . 
                                 number_format($total_pengeluaran, 0, ',', '.') . ' = ' . 
                                 '- ' . number_format(abs($total_keseluruhan), 0, ',', '.') . 
                                 ' (' . $status_keseluruhan . ')';
                        } else {
                            echo number_format($total_pemasukan, 0, ',', '.') . ' - ' . 
                                 number_format($total_pengeluaran, 0, ',', '.') . ' = ' . 
                                 number_format(abs($total_keseluruhan), 0, ',', '.') . 
                                 ' (' . $status_keseluruhan . ')';
                        }
                        ?>
                    </h3>
                </div>
                
                <!-- Sisa Stok -->
                <div class="mt-4">
                    <h6>SISA STOK BARANG</h6>
                    <div class="row">
                        <?php
                        // Format stok - gunakan nama dan format yang sama seperti di database stok
                        $stok_formatted = [];
                        foreach ($stok_barang as $stok) {
                            $nama = $stok['nama_barang'];
                            $jumlah = formatAngka($stok['jumlah']);
                            $satuan = $stok['satuan'];
                            
                            // Tampilkan persis seperti di database: "Jumlah Satuan Nama Barang"
                            $stok_formatted[] = $jumlah . " " . $satuan . " " . $nama;
                        }
                        
                        // Bagi menjadi 2 kolom
                        $mid = ceil(count($stok_formatted) / 2);
                        for ($col = 0; $col < 2; $col++) {
                            echo '<div class="col-md-6">';
                            for ($i = $col * $mid; $i < min(($col + 1) * $mid, count($stok_formatted)); $i++) {
                                echo '<div class="d-flex justify-content-between border-bottom py-1">';
                                echo '<span>' . ($i + 1) . '. ' . $stok_formatted[$i] . '</span>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Lain-lain -->
                <div class="mt-4">
                    <h6>Lain - lain</h6>
                    <?php
                    $roti_stok = 0;
                    foreach ($stok_barang as $stok) {
                        if (strpos($stok['nama_barang'], 'Roti') !== false) {
                            $roti_stok = formatAngka($stok['jumlah']);
                            break;
                        }
                    }
                    ?>
                    <p>Roti laku = 0</p>
                    <p><strong>*Stok roti: <?php echo $roti_stok; ?>*</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Text untuk WhatsApp -->
<textarea id="waText" style="display: none;">
<?php
// Format teks untuk WhatsApp
$wa_text = "*LAPORAN HARIAN - $hari, {$tanggal_format} $bulan $tahun*\n";

// Shift
$wa_text .= "*PAGI/SORE*: " . number_format($shift_pagi, 0, ',', '.') . "\n";
$wa_text .= "*SORE/MALAM*: " . number_format($shift_sore, 0, ',', '.') . "\n\n";

// Pengeluaran
$wa_text .= "*PENGELUARAN*\n";
$counter = 1;
foreach ($detail_pengeluaran as $pengeluaran) {
    $wa_text .= $counter++ . ". " . $pengeluaran['keterangan'] . " : " . number_format($pengeluaran['jumlah'], 0, ',', '.') . "\n";
}
if ($counter == 1) {
    $wa_text .= "Tidak ada pengeluaran\n";
}

$wa_text .= "\n*TOTAL PENGELUARAN = " . number_format($total_pengeluaran, 0, ',', '.') . "*\n\n";

// Total Keseluruhan
$wa_text .= "*TOTAL KESELURUHAN*\n";
if ($status_keseluruhan == 'RUGI') {
    $wa_text .= number_format($total_pemasukan, 0, ',', '.') . " - " . number_format($total_pengeluaran, 0, ',', '.') . " = -" . number_format(abs($total_keseluruhan), 0, ',', '.') . " ($status_keseluruhan)\n\n";
} else {
    $wa_text .= number_format($total_pemasukan, 0, ',', '.') . " - " . number_format($total_pengeluaran, 0, ',', '.') . " = " . number_format(abs($total_keseluruhan), 0, ',', '.') . " ($status_keseluruhan)\n\n";
}

// Stok Barang
$wa_text .= "*STOK BARANG*\n";
foreach ($stok_formatted as $index => $stok) {
    $wa_text .= ($index + 1) . ". " . $stok . "\n";
}

// Lain-lain
$wa_text .= "\n*Lain - lain*\n";
$wa_text .= "Roti laku = 0\n";
$wa_text .= "*Stok roti: $roti_stok*\n\n";
$wa_text .= "Abu Dafi 5 Ok🔥";

echo $wa_text;
?>
</textarea>

<script>
function kirimWhatsApp() {
    const waText = document.getElementById('waText').value;
    const phoneNumber = prompt('Masukkan nomor WhatsApp (contoh: 6281234567890):');
    
    if (phoneNumber) {
        const encodedText = encodeURIComponent(waText);
        const waUrl = `https://wa.me/${phoneNumber}?text=${encodedText}`;
        window.open(waUrl, '_blank');
    }
}

// Fungsi untuk copy teks
function copyToClipboard() {
    const waText = document.getElementById('waText');
    waText.style.display = 'block';
    waText.select();
    waText.setSelectionRange(0, 99999);
    document.execCommand('copy');
    waText.style.display = 'none';
    
    alert('Teks laporan berhasil disalin ke clipboard!');
}
</script>

<!-- Tombol Tambahan -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <button type="button" class="btn btn-outline-primary me-2" onclick="copyToClipboard()">
                    <i class="fas fa-copy"></i> Copy Teks Laporan
                </button>
                <small class="text-muted">Salin teks untuk dibagikan ke media lain</small>
            </div>
        </div>
    </div>
</div>

<?php } else { ?>
<!-- Default view ketika belum memilih tanggal -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Pilih Tanggal untuk Melihat Laporan</h4>
                <p class="text-muted">Silakan pilih tanggal laporan yang ingin dilihat</p>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php include 'includes/footer.php'; ?>