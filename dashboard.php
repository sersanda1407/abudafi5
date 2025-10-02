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
        <h2 class="mb-4"><i class="fas fa-chart-line"></i> Dashboard</h2>
    </div>
</div>

<!-- Ringkasan Statistik -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5><i class="fas fa-money-bill-wave"></i> Total Pemasukan</h5>
                <h3>
                    <?php
                    $query = "SELECT SUM(pemasukan) as total FROM laporan_harian WHERE DATE(tanggal) = CURDATE()";
                    $result = mysqli_query($conn, $query);
                    $row = mysqli_fetch_assoc($result);
                    echo 'Rp ' . number_format($row['total'] ?? 0, 0, ',', '.');
                    ?>
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5><i class="fas fa-receipt"></i> Total Pengeluaran</h5>
                <h3>
                    <?php
                    $query = "SELECT SUM(pengeluaran) as total FROM laporan_harian WHERE DATE(tanggal) = CURDATE()";
                    $result = mysqli_query($conn, $query);
                    $row = mysqli_fetch_assoc($result);
                    echo 'Rp ' . number_format($row['total'] ?? 0, 0, ',', '.');
                    ?>
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5><i class="fas fa-chart-line"></i> Laba Hari Ini</h5>
                <h3>
                    <?php
                    $query = "SELECT SUM(total) as total FROM laporan_harian WHERE DATE(tanggal) = CURDATE() AND status = 'LABA'";
                    $result = mysqli_query($conn, $query);
                    $row = mysqli_fetch_assoc($result);
                    echo 'Rp ' . number_format($row['total'] ?? 0, 0, ',', '.');
                    ?>
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5><i class="fas fa-exclamation-triangle"></i> Rugi Hari Ini</h5>
                <h3>
                    <?php
                    $query = "SELECT SUM(total) as total FROM laporan_harian WHERE DATE(tanggal) = CURDATE() AND status = 'RUGI'";
                    $result = mysqli_query($conn, $query);
                    $row = mysqli_fetch_assoc($result);
                    echo '- Rp ' . number_format(abs($row['total'] ?? 0), 0, ',', '.');
                    ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Grafik Laporan Harian -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Grafik Laporan Harian (7 Hari Terakhir)</h5>
            </div>
            <div class="card-body">
                <canvas id="laporanChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Sisa Stok Barang -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-boxes"></i> Sisa Stok Barang</h5>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <?php
                // Ambil stok dengan urutan yang sama seperti di halaman stok
                $query = "SELECT * FROM stok_barang ORDER BY 
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
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $jumlah_tampil = formatAngka($row['jumlah']);
                    echo '<div class="d-flex justify-content-between border-bottom py-2">';
                    echo '<span>' . $row['nama_barang'] . '</span>';
                    echo '<span class="badge bg-primary">' . $jumlah_tampil . ' ' . $row['satuan'] . '</span>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Laporan Hari Ini -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Laporan Hari Ini</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                <th>Pemasukan</th>
                                <th>Pengeluaran</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM laporan_harian WHERE DATE(tanggal) = CURDATE() ORDER BY shift";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<tr>';
                                echo '<td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>';
                                echo '<td>' . ucfirst($row['shift']) . '</td>';
                                echo '<td>Rp ' . number_format($row['pemasukan'], 0, ',', '.') . '</td>';
                                echo '<td>Rp ' . number_format($row['pengeluaran'], 0, ',', '.') . '</td>';
                                echo '<td>';
                                if ($row['status'] == 'RUGI') {
                                    echo '- Rp ' . number_format(abs($row['total']), 0, ',', '.');
                                } else {
                                    echo 'Rp ' . number_format(abs($row['total']), 0, ',', '.');
                                }
                                echo '</td>';
                                echo '<td><span class="badge ' . ($row['status'] == 'LABA' ? 'bg-success' : 'bg-danger') . '">' . $row['status'] . '</span></td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Grafik Laporan
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('laporanChart').getContext('2d');
    
    // Data dari PHP (dummy data untuk contoh)
    const labels = <?php
        $dates = [];
        $pemasukan = [];
        $pengeluaran = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = date('d/m', strtotime($date));
            
            $query = "SELECT SUM(pemasukan) as pemasukan, SUM(pengeluaran) as pengeluaran 
                     FROM laporan_harian WHERE DATE(tanggal) = '$date'";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            
            $pemasukan[] = $row['pemasukan'] ?? 0;
            $pengeluaran[] = $row['pengeluaran'] ?? 0;
        }
        
        echo "['" . implode("','", $dates) . "']";
    ?>;
    
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: <?php echo '[' . implode(',', $pemasukan) . ']'; ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Pengeluaran',
                    data: <?php echo '[' . implode(',', $pengeluaran) . ']'; ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>