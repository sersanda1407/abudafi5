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

// Daftar stok utama
$stok_utama = ['Air Galon', 'Kopi (Stok Toples)', 'Milo (Stok Toples)', 'Teh (Stok Toples)', 'Gas'];
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-file-alt"></i> Laporan Harian</h2>
    </div>
</div>

<?php
// Cek apakah sedang mode edit
$edit_mode = false;
$laporan_edit = null;
$detail_pengeluaran_edit = [];

if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $query_edit = "SELECT * FROM laporan_harian WHERE id = $edit_id";
    $result_edit = mysqli_query($conn, $query_edit);
    
    if (mysqli_num_rows($result_edit) > 0) {
        $laporan_edit = mysqli_fetch_assoc($result_edit);
        $edit_mode = true;
        
        // Ambil detail pengeluaran
        $query_detail = "SELECT * FROM detail_pengeluaran WHERE laporan_id = $edit_id";
        $result_detail = mysqli_query($conn, $query_detail);
        while ($row = mysqli_fetch_assoc($result_detail)) {
            $detail_pengeluaran_edit[] = $row;
        }
    }
}

// Cek apakah sudah ada laporan untuk shift hari ini (kecuali yang sedang diedit)
$tanggal_hari_ini = date('Y-m-d');
$query_cek_shift = "SELECT shift FROM laporan_harian WHERE tanggal = '$tanggal_hari_ini'";
if ($edit_mode) {
    $query_cek_shift .= " AND id != " . $laporan_edit['id'];
}
$result_cek_shift = mysqli_query($conn, $query_cek_shift);
$shift_terpakai = [];

while ($row = mysqli_fetch_assoc($result_cek_shift)) {
    $shift_terpakai[] = $row['shift'];
}

$shift_pagi_sudah_ada = in_array('pagi', $shift_terpakai);
$shift_sore_sudah_ada = in_array('sore', $shift_terpakai);
?>

<!-- Form Input/Edit Laporan -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>
                    <i class="fas <?php echo $edit_mode ? 'fa-edit' : 'fa-plus'; ?>"></i> 
                    <?php echo $edit_mode ? 'Edit Laporan Harian' : 'Input Laporan Harian'; ?>
                </h5>
                <small class="text-muted">Setiap shift hanya bisa diinput 1 kali per hari</small>
            </div>
            <div class="card-body">
                <!-- Alert Info Shift -->
                <?php if (!$edit_mode && ($shift_pagi_sudah_ada || $shift_sore_sudah_ada)): ?>
                <div class="alert alert-info">
                    <strong>Shift yang sudah diinput hari ini:</strong><br>
                    <?php 
                    if ($shift_pagi_sudah_ada) echo '✓ Shift Pagi/Sore (08.00 - 16.00)<br>';
                    if ($shift_sore_sudah_ada) echo '✓ Shift Sore/Malam (16.00 - 00.00)';
                    ?>
                </div>
                <?php endif; ?>

                <?php if ($edit_mode): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Mode Edit:</strong> Anda sedang mengedit laporan shift <?php echo ucfirst($laporan_edit['shift']); ?> tanggal <?php echo date('d/m/Y', strtotime($laporan_edit['tanggal'])); ?>
                </div>
                <?php endif; ?>

                <form action="" method="POST" id="formLaporan">
                    <input type="hidden" name="edit_id" value="<?php echo $edit_mode ? $laporan_edit['id'] : ''; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift" class="form-label">Shift</label>
                                <select class="form-select" id="shift" name="shift" required 
                                    <?php echo ($edit_mode ? '' : (($shift_pagi_sudah_ada && $shift_sore_sudah_ada) ? 'disabled' : '')); ?>>
                                    <option value="">Pilih Shift</option>
                                    <option value="pagi" <?php 
                                        if ($edit_mode && $laporan_edit['shift'] == 'pagi') echo 'selected';
                                        elseif (!$edit_mode && $shift_pagi_sudah_ada) echo 'disabled';
                                    ?>>
                                        Pagi/Sore (08.00 - 16.00) <?php echo (!$edit_mode && $shift_pagi_sudah_ada) ? '✓' : ''; ?>
                                    </option>
                                    <option value="sore" <?php 
                                        if ($edit_mode && $laporan_edit['shift'] == 'sore') echo 'selected';
                                        elseif (!$edit_mode && $shift_sore_sudah_ada) echo 'disabled';
                                    ?>>
                                        Sore/Malam (16.00 - 00.00) <?php echo (!$edit_mode && $shift_sore_sudah_ada) ? '✓' : ''; ?>
                                    </option>
                                </select>
                                <?php if (!$edit_mode && $shift_pagi_sudah_ada && $shift_sore_sudah_ada): ?>
                                <div class="text-danger mt-1">
                                    <small><i class="fas fa-info-circle"></i> Semua shift untuk hari ini sudah diinput</small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pemasukan" class="form-label">Pemasukan (Rp)</label>
                                <input type="number" class="form-control" id="pemasukan" name="pemasukan" required
                                    value="<?php echo $edit_mode ? $laporan_edit['pemasukan'] : ''; ?>"
                                    <?php echo (!$edit_mode && $shift_pagi_sudah_ada && $shift_sore_sudah_ada) ? 'disabled' : ''; ?>>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pengeluaran</label>
                        <div id="pengeluaran-container">
                            <?php if ($edit_mode && count($detail_pengeluaran_edit) > 0): ?>
                                <?php foreach ($detail_pengeluaran_edit as $index => $pengeluaran): ?>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" name="keterangan[]" 
                                        placeholder="Keterangan pengeluaran" value="<?php echo $pengeluaran['keterangan']; ?>">
                                    <input type="number" class="form-control" name="jumlah_pengeluaran[]" 
                                        placeholder="Jumlah" value="<?php echo $pengeluaran['jumlah']; ?>">
                                    <button type="button" class="btn btn-outline-danger" onclick="hapusPengeluaran(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="keterangan[]" placeholder="Keterangan pengeluaran"
                                    <?php echo (!$edit_mode && $shift_pagi_sudah_ada && $shift_sore_sudah_ada) ? 'disabled' : ''; ?>>
                                <input type="number" class="form-control" name="jumlah_pengeluaran[]" placeholder="Jumlah"
                                    <?php echo (!$edit_mode && $shift_pagi_sudah_ada && $shift_sore_sudah_ada) ? 'disabled' : ''; ?>>
                                <button type="button" class="btn btn-outline-danger" onclick="hapusPengeluaran(this)"
                                    <?php echo (!$edit_mode && $shift_pagi_sudah_ada && $shift_sore_sudah_ada) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="tambahPengeluaran()"
                            <?php echo (!$edit_mode && $shift_pagi_sudah_ada && $shift_sore_sudah_ada) ? 'disabled' : ''; ?>>
                            <i class="fas fa-plus"></i> Tambah Pengeluaran
                        </button>
                    </div>
                    
                    <!-- Stok Utama -->
                    <div class="mb-4">
                        <label class="form-label">Perhitungan Stok Harian - <span class="text-primary">Stok Utama</span></label>
                        <small class="text-muted d-block">Ubah angka stok utama yang berubah hari ini:</small>
                        
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th width="120">Stok Saat Ini</th>
                                        <th width="120">Stok Baru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_utama = "SELECT * FROM stok_barang WHERE nama_barang IN ('Air Galon', 'Kopi (Stok Toples)', 'Milo (Stok Toples)', 'Teh (Stok Toples)', 'Gas') ORDER BY 
                                                  CASE 
                                                    WHEN nama_barang = 'Air Galon' THEN 1
                                                    WHEN nama_barang = 'Kopi (Stok Toples)' THEN 2
                                                    WHEN nama_barang = 'Milo (Stok Toples)' THEN 3
                                                    WHEN nama_barang = 'Teh (Stok Toples)' THEN 4
                                                    WHEN nama_barang = 'Gas' THEN 5
                                                    ELSE 6
                                                  END";
                                    $result_utama = mysqli_query($conn, $query_utama);
                                    while ($row = mysqli_fetch_assoc($result_utama)) {
                                        $jumlah_tampil = formatAngka($row['jumlah']);
                                        echo '<tr>';
                                        echo '<td><strong>' . $row['nama_barang'] . '</strong></td>';
                                        echo '<td>' . $jumlah_tampil . ' ' . $row['satuan'] . '</td>';
                                        echo '<td>';
                                        echo '<input type="hidden" name="stok_id[]" value="' . $row['id'] . '">';
                                        echo '<input type="number" step="0.1" class="form-control form-control-sm" name="stok_baru[]" value="' . formatAngka($row['jumlah']) . '"';
                                        echo (!$edit_mode && $shift_pagi_sudah_ada && $shift_sore_sudah_ada) ? ' disabled' : '';
                                        echo '>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Stok Lainnya -->
                    <div class="mb-3">
                        <label class="form-label">Perhitungan Stok Harian - <span class="text-success">Stok Lainnya</span></label>
                        <small class="text-muted d-block">Ubah angka stok lainnya yang berubah hari ini:</small>
                        
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered">
                                <thead class="table-success">
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th width="120">Stok Saat Ini</th>
                                        <th width="120">Stok Baru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_lainnya = "SELECT * FROM stok_barang WHERE nama_barang NOT IN ('Air Galon', 'Kopi (Stok Toples)', 'Milo (Stok Toples)', 'Teh (Stok Toples)', 'Gas') ORDER BY nama_barang";
                                    $result_lainnya = mysqli_query($conn, $query_lainnya);
                                    while ($row = mysqli_fetch_assoc($result_lainnya)) {
                                        $jumlah_tampil = formatAngka($row['jumlah']);
                                        echo '<tr>';
                                        echo '<td>' . $row['nama_barang'] . '</td>';
                                        echo '<td>' . $jumlah_tampil . ' ' . $row['satuan'] . '</td>';
                                        echo '<td>';
                                        echo '<input type="hidden" name="stok_id[]" value="' . $row['id'] . '">';
                                        echo '<input type="number" step="0.1" class="form-control form-control-sm" name="stok_baru[]" value="' . formatAngka($row['jumlah']) . '"';
                                        echo (!$edit_mode && $shift_pagi_sudah_ada && $shift_sore_sudah_ada) ? ' disabled' : '';
                                        echo '>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($edit_mode || !($shift_pagi_sudah_ada && $shift_sore_sudah_ada)): ?>
                    <button type="submit" class="btn btn-primary" name="<?php echo $edit_mode ? 'update_laporan' : 'simpan_laporan'; ?>">
                        <i class="fas fa-save"></i> <?php echo $edit_mode ? 'Update Laporan' : 'Simpan Laporan'; ?>
                    </button>
                    
                    <?php if ($edit_mode): ?>
                    <a href="laporan-harian.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal Edit
                    </a>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <button type="button" class="btn btn-secondary" disabled>
                        <i class="fas fa-check"></i> Semua Shift Sudah Diinput
                    </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Preview Perhitungan -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calculator"></i> Preview Perhitungan</h5>
            </div>
            <div class="card-body">
                <div id="preview-calculation" class="text-center">
                    <?php if ($edit_mode): ?>
                    <h4><?php echo number_format($laporan_edit['pemasukan'], 0, ',', '.'); ?> - <?php echo number_format($laporan_edit['pengeluaran'], 0, ',', '.'); ?></h4>
                    <h3 class="<?php echo $laporan_edit['status'] == 'LABA' ? 'text-success' : 'text-danger'; ?>">
                        <?php
                        if ($laporan_edit['status'] == 'RUGI') {
                            echo '= - ' . number_format(abs($laporan_edit['total']), 0, ',', '.') . ' (' . $laporan_edit['status'] . ')';
                        } else {
                            echo '= ' . number_format(abs($laporan_edit['total']), 0, ',', '.') . ' (' . $laporan_edit['status'] . ')';
                        }
                        ?>
                    </h3>
                    <?php else: ?>
                    <p class="text-muted">Isi form untuk melihat preview perhitungan</p>
                    <?php endif; ?>
                </div>

                <!-- Info Laporan Hari Ini -->
                <div class="mt-4">
                    <h6>Laporan Hari Ini</h6>
                    <?php
                    $query_hari_ini = "SELECT * FROM laporan_harian WHERE tanggal = '$tanggal_hari_ini'";
                    if ($edit_mode) {
                        $query_hari_ini .= " AND id != " . $laporan_edit['id'];
                    }
                    $query_hari_ini .= " ORDER BY shift";
                    $result_hari_ini = mysqli_query($conn, $query_hari_ini);
                    
                    if (mysqli_num_rows($result_hari_ini) > 0) {
                        while ($row = mysqli_fetch_assoc($result_hari_ini)) {
                            $badge_class = $row['status'] == 'LABA' ? 'bg-success' : 'bg-danger';
                            echo '<div class="d-flex justify-content-between align-items-center border-bottom py-2">';
                            echo '<div>';
                            echo '<strong>' . ucfirst($row['shift']) . '</strong><br>';
                            echo '<small>Pemasukan: Rp ' . number_format($row['pemasukan'], 0, ',', '.') . '</small>';
                            echo '</div>';
                            echo '<div>';
                            echo '<span class="badge ' . $badge_class . '">' . $row['status'] . '</span><br>';
                            echo '<a href="laporan-harian.php?edit=' . $row['id'] . '" class="btn btn-sm btn-outline-warning mt-1">';
                            echo '<i class="fas fa-edit"></i>';
                            echo '</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="text-muted">Belum ada laporan hari ini</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Riwayat Laporan -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-history"></i> Riwayat Laporan (10 Terakhir)</h5>
                <a href="cetak-laporan.php" class="btn btn-sm btn-success">
                    <i class="fas fa-print"></i> Cetak Semua Laporan
                </a>
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
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM laporan_harian ORDER BY tanggal DESC, shift LIMIT 10";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                $badge_class = $row['status'] == 'LABA' ? 'bg-success' : 'bg-danger';
                                $is_editable = ($row['tanggal'] == $tanggal_hari_ini); // Hanya bisa edit laporan hari ini
                                
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
                                echo '<td><span class="badge ' . $badge_class . '">' . $row['status'] . '</span></td>';
                                echo '<td>';
                                echo '<div class="btn-group">';
                                if ($is_editable) {
                                    echo '<a href="laporan-harian.php?edit=' . $row['id'] . '" class="btn btn-sm btn-warning">';
                                    echo '<i class="fas fa-edit"></i> Edit';
                                    echo '</a>';
                                }
                                echo '<a href="cetak-laporan.php?tanggal=' . $row['tanggal'] . '" class="btn btn-sm btn-info" target="_blank">';
                                echo '<i class="fas fa-print"></i> Cetak';
                                echo '</a>';
                                echo '</div>';
                                echo '</td>';
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
let pengeluaranCount = <?php echo $edit_mode ? count($detail_pengeluaran_edit) : 1; ?>;

function tambahPengeluaran() {
    const container = document.getElementById('pengeluaran-container');
    const newInput = document.createElement('div');
    newInput.className = 'input-group mb-2';
    newInput.innerHTML = `
        <input type="text" class="form-control" name="keterangan[]" placeholder="Keterangan pengeluaran">
        <input type="number" class="form-control" name="jumlah_pengeluaran[]" placeholder="Jumlah">
        <button type="button" class="btn btn-outline-danger" onclick="hapusPengeluaran(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(newInput);
    pengeluaranCount++;
}

function hapusPengeluaran(button) {
    if (pengeluaranCount > 1) {
        button.parentElement.remove();
        pengeluaranCount--;
    }
}

// Update preview perhitungan
document.getElementById('pemasukan').addEventListener('input', updatePreview);
document.querySelectorAll('input[name="jumlah_pengeluaran[]"]').forEach(input => {
    input.addEventListener('input', updatePreview);
});

function updatePreview() {
    const pemasukan = parseFloat(document.getElementById('pemasukan').value) || 0;
    let totalPengeluaran = 0;
    
    document.querySelectorAll('input[name="jumlah_pengeluaran[]"]').forEach(input => {
        totalPengeluaran += parseFloat(input.value) || 0;
    });
    
    const total = pemasukan - totalPengeluaran;
    const status = total >= 0 ? 'LABA' : 'RUGI';
    
    const preview = document.getElementById('preview-calculation');
    
    if (status === 'RUGI') {
        preview.innerHTML = `
            <h4>${pemasukan.toLocaleString('id-ID')} - ${totalPengeluaran.toLocaleString('id-ID')}</h4>
            <h3 class="text-danger">
                = - ${Math.abs(total).toLocaleString('id-ID')} (${status})
            </h3>
        `;
    } else {
        preview.innerHTML = `
            <h4>${pemasukan.toLocaleString('id-ID')} - ${totalPengeluaran.toLocaleString('id-ID')}</h4>
            <h3 class="text-success">
                = ${Math.abs(total).toLocaleString('id-ID')} (${status})
            </h3>
        `;
    }
}

// Validasi sebelum submit
document.getElementById('formLaporan').addEventListener('submit', function(e) {
    const shift = document.getElementById('shift').value;
    const pemasukan = document.getElementById('pemasukan').value;
    
    if (!shift) {
        e.preventDefault();
        alert('Pilih shift terlebih dahulu!');
        return false;
    }
    
    if (!pemasukan || pemasukan <= 0) {
        e.preventDefault();
        alert('Masukkan jumlah pemasukan yang valid!');
        return false;
    }
});

// Auto update preview jika dalam mode edit
<?php if ($edit_mode): ?>
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
});
<?php endif; ?>

// Format input stok saat blur - bulatkan jika .0
document.querySelectorAll('input[name="stok_baru[]"]').forEach(input => {
    input.addEventListener('blur', function(e) {
        const value = parseFloat(this.value);
        if (!isNaN(value)) {
            // Cek jika nilai memiliki .0 di belakang koma
            if (value === Math.floor(value)) {
                this.value = Math.floor(value);
            }
        }
    });
});

// Format input pemasukan dan pengeluaran saat blur
document.getElementById('pemasukan').addEventListener('blur', function(e) {
    const value = parseFloat(this.value);
    if (!isNaN(value) && value === Math.floor(value)) {
        this.value = Math.floor(value);
    }
});

document.querySelectorAll('input[name="jumlah_pengeluaran[]"]').forEach(input => {
    input.addEventListener('blur', function(e) {
        const value = parseFloat(this.value);
        if (!isNaN(value) && value === Math.floor(value)) {
            this.value = Math.floor(value);
        }
    });
});
</script>

<?php
// Proses Simpan Laporan Baru
if (isset($_POST['simpan_laporan'])) {
    $shift = $_POST['shift'];
    $pemasukan = $_POST['pemasukan'];
    $keterangan = $_POST['keterangan'];
    $jumlah_pengeluaran = $_POST['jumlah_pengeluaran'];
    $stok_id = $_POST['stok_id'];
    $stok_baru = $_POST['stok_baru'];
    
    // Cek apakah shift sudah ada untuk hari ini
    $tanggal = date('Y-m-d');
    $query_cek = "SELECT id FROM laporan_harian WHERE tanggal = '$tanggal' AND shift = '$shift'";
    $result_cek = mysqli_query($conn, $query_cek);
    
    if (mysqli_num_rows($result_cek) > 0) {
        echo '<script>alert("Error: Laporan untuk shift ' . $shift . ' hari ini sudah ada!");</script>';
    } else {
        // Hitung total pengeluaran
        $total_pengeluaran = 0;
        for ($i = 0; $i < count($jumlah_pengeluaran); $i++) {
            if (!empty($keterangan[$i]) && !empty($jumlah_pengeluaran[$i])) {
                $total_pengeluaran += $jumlah_pengeluaran[$i];
            }
        }
        
        // Hitung total dan status
        $total = $pemasukan - $total_pengeluaran;
        $status = $total >= 0 ? 'LABA' : 'RUGI';
        
        // Simpan laporan harian
        $query = "INSERT INTO laporan_harian (tanggal, shift, pemasukan, pengeluaran, total, status) 
                  VALUES ('$tanggal', '$shift', $pemasukan, $total_pengeluaran, $total, '$status')";
        
        if (mysqli_query($conn, $query)) {
            $laporan_id = mysqli_insert_id($conn);
            
            // Simpan detail pengeluaran
            for ($i = 0; $i < count($jumlah_pengeluaran); $i++) {
                if (!empty($keterangan[$i]) && !empty($jumlah_pengeluaran[$i])) {
                    $ket = mysqli_real_escape_string($conn, $keterangan[$i]);
                    $jumlah = $jumlah_pengeluaran[$i];
                    $query_detail = "INSERT INTO detail_pengeluaran (laporan_id, keterangan, jumlah) 
                                   VALUES ($laporan_id, '$ket', $jumlah)";
                    mysqli_query($conn, $query_detail);
                }
            }
            
            // Update stok barang
            for ($i = 0; $i < count($stok_id); $i++) {
                $id = $stok_id[$i];
                $jumlah_baru = $stok_baru[$i];
                $query_stok = "UPDATE stok_barang SET jumlah = $jumlah_baru WHERE id = $id";
                mysqli_query($conn, $query_stok);
            }
            
            echo '<script>alert("Laporan shift ' . $shift . ' berhasil disimpan!"); window.location.href = "laporan-harian.php";</script>';
        } else {
            echo '<script>alert("Error: ' . mysqli_error($conn) . '");</script>';
        }
    }
}

// Proses Update Laporan
if (isset($_POST['update_laporan'])) {
    $edit_id = intval($_POST['edit_id']);
    $shift = $_POST['shift'];
    $pemasukan = $_POST['pemasukan'];
    $keterangan = $_POST['keterangan'];
    $jumlah_pengeluaran = $_POST['jumlah_pengeluaran'];
    $stok_id = $_POST['stok_id'];
    $stok_baru = $_POST['stok_baru'];
    
    // Cek apakah shift sudah ada untuk hari ini (kecuali laporan yang sedang diedit)
    $tanggal = date('Y-m-d');
    $query_cek = "SELECT id FROM laporan_harian WHERE tanggal = '$tanggal' AND shift = '$shift' AND id != $edit_id";
    $result_cek = mysqli_query($conn, $query_cek);
    
    if (mysqli_num_rows($result_cek) > 0) {
        echo '<script>alert("Error: Laporan untuk shift ' . $shift . ' hari ini sudah ada!");</script>';
    } else {
        // Hitung total pengeluaran
        $total_pengeluaran = 0;
        for ($i = 0; $i < count($jumlah_pengeluaran); $i++) {
            if (!empty($keterangan[$i]) && !empty($jumlah_pengeluaran[$i])) {
                $total_pengeluaran += $jumlah_pengeluaran[$i];
            }
        }
        
        // Hitung total dan status
        $total = $pemasukan - $total_pengeluaran;
        $status = $total >= 0 ? 'LABA' : 'RUGI';
        
        // Update laporan harian
        $query = "UPDATE laporan_harian SET 
                  shift = '$shift', 
                  pemasukan = $pemasukan, 
                  pengeluaran = $total_pengeluaran, 
                  total = $total, 
                  status = '$status' 
                  WHERE id = $edit_id";
        
        if (mysqli_query($conn, $query)) {
            // Hapus detail pengeluaran lama
            $query_hapus_detail = "DELETE FROM detail_pengeluaran WHERE laporan_id = $edit_id";
            mysqli_query($conn, $query_hapus_detail);
            
            // Simpan detail pengeluaran baru
            for ($i = 0; $i < count($jumlah_pengeluaran); $i++) {
                if (!empty($keterangan[$i]) && !empty($jumlah_pengeluaran[$i])) {
                    $ket = mysqli_real_escape_string($conn, $keterangan[$i]);
                    $jumlah = $jumlah_pengeluaran[$i];
                    $query_detail = "INSERT INTO detail_pengeluaran (laporan_id, keterangan, jumlah) 
                                   VALUES ($edit_id, '$ket', $jumlah)";
                    mysqli_query($conn, $query_detail);
                }
            }
            
            // Update stok barang
            for ($i = 0; $i < count($stok_id); $i++) {
                $id = $stok_id[$i];
                $jumlah_baru = $stok_baru[$i];
                $query_stok = "UPDATE stok_barang SET jumlah = $jumlah_baru WHERE id = $id";
                mysqli_query($conn, $query_stok);
            }
            
            echo '<script>alert("Laporan berhasil diupdate!"); window.location.href = "laporan-harian.php";</script>';
        } else {
            echo '<script>alert("Error: ' . mysqli_error($conn) . '");</script>';
        }
    }
}

include 'includes/footer.php';
?>