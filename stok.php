<?php include 'includes/header.php'; ?>

<?php
// Fungsi untuk format angka genap
function formatAngka($angka) {
    return ($angka == intval($angka)) ? intval($angka) : $angka;
}

// Daftar stok utama
$stok_utama = ['Air Galon', 'Kopi (Stok Toples)', 'Milo (Stok Toples)', 'Teh (Stok Toples)', 'Gas'];
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-boxes"></i> Kelola Stok Barang</h2>
    </div>
</div>

<!-- Form Tambah Stok -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> Tambah Stok Barang Baru</h5>
            </div>
            <div class="card-body">
                <form action="" method="POST" id="formStok">
                    <div class="mb-3">
                        <label for="nama_barang" class="form-label">Nama Barang</label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jumlah" class="form-label">Jumlah</label>
                                <input type="number" step="0.1" class="form-control" id="jumlah" name="jumlah" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="satuan" class="form-label">Satuan</label>
                                <input type="text" class="form-control" id="satuan" name="satuan" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" name="simpan">
                        <i class="fas fa-save"></i> Simpan Barang
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Stok -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Informasi Stok</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-end">
                            <h3 class="text-primary">
                                <?php
                                $query_total = "SELECT COUNT(*) as total FROM stok_barang";
                                $result_total = mysqli_query($conn, $query_total);
                                $total_barang = mysqli_fetch_assoc($result_total)['total'];
                                echo $total_barang;
                                ?>
                            </h3>
                            <small class="text-muted">Total Barang</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <h3 class="text-success">
                                <?php
                                $query_stok_utama = "SELECT COUNT(*) as utama FROM stok_barang WHERE nama_barang IN ('Air Galon', 'Kopi (Stok Toples)', 'Milo (Stok Toples)', 'Teh (Stok Toples)', 'Gas')";
                                $result_stok_utama = mysqli_query($conn, $query_stok_utama);
                                $stok_utama_count = mysqli_fetch_assoc($result_stok_utama)['utama'];
                                echo $stok_utama_count;
                                ?>
                            </h3>
                            <small class="text-muted">Stok Utama</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <h3 class="text-warning">
                            <?php
                            $query_stok_habis = "SELECT COUNT(*) as habis FROM stok_barang WHERE jumlah = 0";
                            $result_stok_habis = mysqli_query($conn, $query_stok_habis);
                            $stok_habis = mysqli_fetch_assoc($result_stok_habis)['habis'];
                            echo $stok_habis;
                            ?>
                        </h3>
                        <small class="text-muted">Stok Habis</small>
                    </div>
                </div>
                <hr>
                <div class="mt-3">
                    <h6>Barang dengan Stok Sedikit:</h6>
                    <?php
                    $query_stok_sedikit = "SELECT * FROM stok_barang WHERE jumlah > 0 AND jumlah <= 5 ORDER BY jumlah ASC LIMIT 5";
                    $result_stok_sedikit = mysqli_query($conn, $query_stok_sedikit);
                    
                    if (mysqli_num_rows($result_stok_sedikit) > 0) {
                        while ($row = mysqli_fetch_assoc($result_stok_sedikit)) {
                            $jumlah_format = formatAngka($row['jumlah']);
                            echo '<div class="d-flex justify-content-between border-bottom py-1">';
                            echo '<span>' . $row['nama_barang'] . '</span>';
                            echo '<span class="badge bg-warning">' . $jumlah_format . ' ' . $row['satuan'] . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="text-muted">Tidak ada barang dengan stok sedikit</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stok Utama -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-star"></i> Stok Utama</h5>
                <span class="badge bg-light text-dark"><?php echo $stok_utama_count; ?> barang</span>
            </div>
            <div class="card-body">
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
                
                if (mysqli_num_rows($result_utama) > 0): 
                ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Barang</th>
                                <th width="120">Jumlah</th>
                                <th width="100">Satuan</th>
                                <th width="150">Status</th>
                                <th width="120" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no_utama = 1;
                            while ($row = mysqli_fetch_assoc($result_utama)) {
                                $status_class = ($row['jumlah'] == 0) ? 'danger' : (($row['jumlah'] <= 5) ? 'warning' : 'success');
                                $status_text = ($row['jumlah'] == 0) ? 'Habis' : (($row['jumlah'] <= 5) ? 'Sedikit' : 'Tersedia');
                                
                                $jumlah_tampil = formatAngka($row['jumlah']);
                                
                                echo '<tr>';
                                echo '<td>' . $no_utama++ . '</td>';
                                echo '<td><strong>' . $row['nama_barang'] . '</strong></td>';
                                echo '<td><strong class="fs-6">' . $jumlah_tampil . '</strong></td>';
                                echo '<td>' . $row['satuan'] . '</td>';
                                echo '<td><span class="badge bg-' . $status_class . '">' . $status_text . '</span></td>';
                                echo '<td class="text-center">';
                                echo '<button class="btn btn-sm btn-warning me-1" data-bs-toggle="modal" data-bs-target="#editModal" 
                                        onclick="setEditData(' . $row['id'] . ', \'' . addslashes($row['nama_barang']) . '\', ' . $row['jumlah'] . ', \'' . addslashes($row['satuan']) . '\', true)">';
                                echo '<i class="fas fa-edit"></i>';
                                echo '</button>';
                                echo '<button class="btn btn-sm btn-danger" onclick="hapusStok(' . $row['id'] . ', \'' . addslashes($row['nama_barang']) . '\', true)">';
                                echo '<i class="fas fa-trash"></i>';
                                echo '</button>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                    <h6 class="text-muted">Stok utama belum lengkap</h6>
                    <p class="text-muted small">Silakan tambah stok utama: Air Galon, Kopi, Milo, Teh, dan Gas</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Stok Lainnya -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-boxes"></i> Stok Lainnya</h5>
                <?php
                $query_lainnya_count = "SELECT COUNT(*) as lainnya FROM stok_barang WHERE nama_barang NOT IN ('Air Galon', 'Kopi (Stok Toples)', 'Milo (Stok Toples)', 'Teh (Stok Toples)', 'Gas')";
                $result_lainnya_count = mysqli_query($conn, $query_lainnya_count);
                $stok_lainnya_count = mysqli_fetch_assoc($result_lainnya_count)['lainnya'];
                ?>
                <span class="badge bg-light text-dark"><?php echo $stok_lainnya_count; ?> barang</span>
            </div>
            <div class="card-body">
                <?php if ($stok_lainnya_count > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-success">
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Barang</th>
                                <th width="120">Jumlah</th>
                                <th width="100">Satuan</th>
                                <th width="150">Status</th>
                                <th width="120" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query_lainnya = "SELECT * FROM stok_barang WHERE nama_barang NOT IN ('Air Galon', 'Kopi (Stok Toples)', 'Milo (Stok Toples)', 'Teh (Stok Toples)', 'Gas') ORDER BY nama_barang";
                            $result_lainnya = mysqli_query($conn, $query_lainnya);
                            $no_lainnya = 1;
                            
                            while ($row = mysqli_fetch_assoc($result_lainnya)) {
                                $status_class = ($row['jumlah'] == 0) ? 'danger' : (($row['jumlah'] <= 5) ? 'warning' : 'success');
                                $status_text = ($row['jumlah'] == 0) ? 'Habis' : (($row['jumlah'] <= 5) ? 'Sedikit' : 'Tersedia');
                                
                                $jumlah_tampil = formatAngka($row['jumlah']);
                                
                                echo '<tr>';
                                echo '<td>' . $no_lainnya++ . '</td>';
                                echo '<td>' . $row['nama_barang'] . '</td>';
                                echo '<td><strong>' . $jumlah_tampil . '</strong></td>';
                                echo '<td>' . $row['satuan'] . '</td>';
                                echo '<td><span class="badge bg-' . $status_class . '">' . $status_text . '</span></td>';
                                echo '<td class="text-center">';
                                echo '<button class="btn btn-sm btn-warning me-1" data-bs-toggle="modal" data-bs-target="#editModal" 
                                        onclick="setEditData(' . $row['id'] . ', \'' . addslashes($row['nama_barang']) . '\', ' . $row['jumlah'] . ', \'' . addslashes($row['satuan']) . '\', false)">';
                                echo '<i class="fas fa-edit"></i>';
                                echo '</button>';
                                echo '<button class="btn btn-sm btn-danger" onclick="hapusStok(' . $row['id'] . ', \'' . addslashes($row['nama_barang']) . '\', false)">';
                                echo '<i class="fas fa-trash"></i>';
                                echo '</button>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada stok lainnya</h5>
                    <p class="text-muted">Silakan tambah barang baru menggunakan form di atas</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Stok -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel"><i class="fas fa-edit"></i> Edit Stok Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formEditStok">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <input type="hidden" name="is_stok_utama" id="is_stok_utama">
                    <div class="mb-3">
                        <label for="edit_nama_barang" class="form-label">Nama Barang</label>
                        <input type="text" class="form-control" id="edit_nama_barang" name="edit_nama_barang" required>
                        <div id="namaHelp" class="form-text text-warning" style="display: none;">
                            <i class="fas fa-info-circle"></i> Nama barang utama tidak dapat diubah
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_jumlah" class="form-label">Jumlah</label>
                                <input type="number" step="0.1" class="form-control" id="edit_jumlah" name="edit_jumlah" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_satuan" class="form-label">Satuan</label>
                                <input type="text" class="form-control" id="edit_satuan" name="edit_satuan" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" name="update">
                        <i class="fas fa-save"></i> Update Barang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setEditData(id, nama, jumlah, satuan, isStokUtama) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama_barang').value = nama;
    document.getElementById('edit_jumlah').value = jumlah;
    document.getElementById('edit_satuan').value = satuan;
    document.getElementById('is_stok_utama').value = isStokUtama ? '1' : '0';
    
    const namaInput = document.getElementById('edit_nama_barang');
    const helpText = document.getElementById('namaHelp');
    
    if (isStokUtama) {
        // Nonaktifkan input nama untuk stok utama
        namaInput.disabled = true;
        namaInput.classList.add('bg-light');
        helpText.style.display = 'block';
        
        // Update judul modal
        document.getElementById('editModalLabel').innerHTML = '<i class="fas fa-edit"></i> Edit Stok Utama';
    } else {
        // Aktifkan input nama untuk stok lainnya
        namaInput.disabled = false;
        namaInput.classList.remove('bg-light');
        helpText.style.display = 'none';
        
        // Update judul modal
        document.getElementById('editModalLabel').innerHTML = '<i class="fas fa-edit"></i> Edit Stok Barang';
    }
}

function hapusStok(id, nama, isStokUtama) {
    if (isStokUtama) {
        alert('Stok utama "' + nama + '" tidak dapat dihapus!');
        return false;
    }
    
    if (confirm('Apakah Anda yakin ingin menghapus stok "' + nama + '"?')) {
        // Menggunakan form untuk hapus
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const inputAction = document.createElement('input');
        inputAction.type = 'hidden';
        inputAction.name = 'action';
        inputAction.value = 'delete';
        form.appendChild(inputAction);
        
        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'delete_id';
        inputId.value = id;
        form.appendChild(inputId);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Format input jumlah saat ketik
document.getElementById('jumlah').addEventListener('blur', function(e) {
    const value = parseFloat(this.value);
    if (!isNaN(value) && value === Math.floor(value)) {
        this.value = Math.floor(value);
    }
});

document.getElementById('edit_jumlah').addEventListener('blur', function(e) {
    const value = parseFloat(this.value);
    if (!isNaN(value) && value === Math.floor(value)) {
        this.value = Math.floor(value);
    }
});

// Validasi form tambah
document.getElementById('formStok').addEventListener('submit', function(e) {
    const nama = document.getElementById('nama_barang').value;
    const jumlah = document.getElementById('jumlah').value;
    const satuan = document.getElementById('satuan').value;
    
    if (!nama || !jumlah || !satuan) {
        e.preventDefault();
        alert('Semua field harus diisi!');
        return false;
    }
});

// Validasi form edit
document.getElementById('formEditStok').addEventListener('submit', function(e) {
    const nama = document.getElementById('edit_nama_barang').value;
    const jumlah = document.getElementById('edit_jumlah').value;
    const satuan = document.getElementById('edit_satuan').value;
    
    if (!nama || !jumlah || !satuan) {
        e.preventDefault();
        alert('Semua field harus diisi!');
        return false;
    }
});

// Reset modal ketika ditutup
$('#editModal').on('hidden.bs.modal', function () {
    const namaInput = document.getElementById('edit_nama_barang');
    namaInput.disabled = false;
    namaInput.classList.remove('bg-light');
    document.getElementById('namaHelp').style.display = 'none';
});

// Auto focus pada input pertama di modal
$('#editModal').on('shown.bs.modal', function () {
    const isStokUtama = document.getElementById('is_stok_utama').value === '1';
    if (!isStokUtama) {
        $('#edit_nama_barang').focus();
    } else {
        $('#edit_jumlah').focus();
    }
});
</script>

<?php
// Proses Simpan Stok Baru
if (isset($_POST['simpan'])) {
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $jumlah = $_POST['jumlah'];
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    
    // Cek apakah barang sudah ada
    $query_cek = "SELECT id FROM stok_barang WHERE nama_barang = '$nama_barang'";
    $result_cek = mysqli_query($conn, $query_cek);
    
    if (mysqli_num_rows($result_cek) > 0) {
        echo '<script>alert("Error: Barang dengan nama \'' . $nama_barang . '\' sudah ada!");</script>';
    } else {
        // Insert baru
        $query = "INSERT INTO stok_barang (nama_barang, jumlah, satuan) VALUES ('$nama_barang', $jumlah, '$satuan')";
        
        if (mysqli_query($conn, $query)) {
            echo '<script>alert("Barang berhasil ditambahkan!"); window.location.href = "stok.php";</script>';
        } else {
            echo '<script>alert("Error: ' . mysqli_error($conn) . '");</script>';
        }
    }
}

// Proses Update Stok
if (isset($_POST['update'])) {
    $id = intval($_POST['edit_id']);
    $is_stok_utama = $_POST['is_stok_utama'] ?? '0';
    $nama_barang = mysqli_real_escape_string($conn, $_POST['edit_nama_barang']);
    $jumlah = $_POST['edit_jumlah'];
    $satuan = mysqli_real_escape_string($conn, $_POST['edit_satuan']);
    
    // Untuk stok utama, gunakan nama asli dari database
    if ($is_stok_utama == '1') {
        $query_nama_asli = "SELECT nama_barang FROM stok_barang WHERE id = $id";
        $result_nama_asli = mysqli_query($conn, $query_nama_asli);
        if ($row = mysqli_fetch_assoc($result_nama_asli)) {
            $nama_barang = $row['nama_barang'];
        }
    } else {
        // Cek apakah nama barang sudah ada (kecuali untuk barang yang sedang diedit)
        $query_cek = "SELECT id FROM stok_barang WHERE nama_barang = '$nama_barang' AND id != $id";
        $result_cek = mysqli_query($conn, $query_cek);
        
        if (mysqli_num_rows($result_cek) > 0) {
            echo '<script>alert("Error: Barang dengan nama \'' . $nama_barang . '\' sudah ada!");</script>';
            exit;
        }
    }
    
    // Update
    $query = "UPDATE stok_barang SET nama_barang='$nama_barang', jumlah=$jumlah, satuan='$satuan' WHERE id=$id";
    
    if (mysqli_query($conn, $query)) {
        echo '<script>alert("Barang berhasil diupdate!"); window.location.href = "stok.php";</script>';
    } else {
        echo '<script>alert("Error: ' . mysqli_error($conn) . '");</script>';
    }
}

// Proses Hapus Stok
if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    
    // Cek apakah stok utama
    $query_cek_utama = "SELECT nama_barang FROM stok_barang WHERE id = $id AND nama_barang IN ('Air Galon', 'Kopi (Stok Toples)', 'Milo (Stok Toples)', 'Teh (Stok Toples)', 'Gas')";
    $result_cek_utama = mysqli_query($conn, $query_cek_utama);
    
    if (mysqli_num_rows($result_cek_utama) > 0) {
        echo '<script>alert("Error: Stok utama tidak dapat dihapus!"); window.location.href = "stok.php";</script>';
    } else {
        // Hapus stok
        $query = "DELETE FROM stok_barang WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            echo '<script>alert("Barang berhasil dihapus!"); window.location.href = "stok.php";</script>';
        } else {
            echo '<script>alert("Error: ' . mysqli_error($conn) . '");</script>';
        }
    }
}

include 'includes/footer.php';
?>