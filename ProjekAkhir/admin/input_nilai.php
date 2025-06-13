<?php
session_start();
include '../config/koneksi.php';

$id_tugas = '';
$judul_tugas = '';
$pesan_sukses = "";
$pesan_error = "";

if (isset($_GET['id_tugas'])) {
    $id_tugas = mysqli_real_escape_string($koneksi, $_GET['id_tugas']);
    $query_tugas = "SELECT judul_tugas FROM tugas WHERE id_tugas = '$id_tugas'";
    $result_tugas = mysqli_query($koneksi, $query_tugas);
    if (mysqli_num_rows($result_tugas) > 0) {
        $tugas_data = mysqli_fetch_assoc($result_tugas);
        $judul_tugas = $tugas_data['judul_tugas'];
    } else {
        $pesan_error = "Tugas tidak ditemukan.";
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_POST['submit_nilai'])) {
    $id_akun = mysqli_real_escape_string($koneksi, $_POST['id_akun']);
    $nilai_tugas = mysqli_real_escape_string($koneksi, $_POST['nilai_tugas']);
    $komentar = mysqli_real_escape_string($koneksi, $_POST['komentar']);

    if (!is_numeric($nilai_tugas) || $nilai_tugas < 0 || $nilai_tugas > 100) {
        $pesan_error = "Nilai harus angka antara 0 dan 100.";
    } else {
        $check_nilai_sql = "SELECT id_nilai FROM nilai WHERE id_akun = '$id_akun' AND id_tugas = '$id_tugas'";
        $check_nilai_result = mysqli_query($koneksi, $check_nilai_sql);

        if (mysqli_num_rows($check_nilai_result) > 0) {
            $update_nilai_sql = "UPDATE nilai SET nilai_tugas = '$nilai_tugas', komentar = '$komentar' WHERE id_akun = '$id_akun' AND id_tugas = '$id_tugas'";
            if (mysqli_query($koneksi, $update_nilai_sql)) {
                $pesan_sukses = "Nilai mahasiswa berhasil diperbarui!";
            } else {
                $pesan_error = "Error: " . mysqli_error($koneksi);
            }
        } else {
            $insert_nilai_sql = "INSERT INTO nilai (id_akun, id_tugas, nilai_tugas, komentar) VALUES ('$id_akun', '$id_tugas', '$nilai_tugas', '$komentar')";
            if (mysqli_query($koneksi, $insert_nilai_sql)) {
                $pesan_sukses = "Nilai mahasiswa berhasil diinput!";
            } else {
                $pesan_error = "Error: " . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai Tugas: <?php echo htmlspecialchars($judul_tugas); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tambah_tugas.php">Tambah Tugas</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Input/Edit Nilai untuk Tugas: "<?php echo htmlspecialchars($judul_tugas); ?>"</h2>
        <?php if ($pesan_sukses): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $pesan_sukses; ?>
        </div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $pesan_error; ?>
        </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nama Mahasiswa</th>
                        <th>File Dikumpulkan</th>
                        <th>Tanggal Upload</th>
                        <th>Nilai Saat Ini</th>
                        <th>Komentar Saat Ini</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_pengumpulan = "SELECT 
                                            p.id_akun, 
                                            a.nama_lengkap, 
                                            p.nama_file, 
                                            p.tanggal_upload,
                                            n.nilai_tugas,
                                            n.komentar
                                        FROM 
                                            pengumpulan p
                                        JOIN 
                                            akun a ON p.id_akun = a.id_akun
                                        LEFT JOIN
                                            nilai n ON p.id_akun = n.id_akun AND p.id_tugas = n.id_tugas
                                        WHERE 
                                            p.id_tugas = '$id_tugas'";
                    $result_pengumpulan = mysqli_query($koneksi, $query_pengumpulan);

                    if (mysqli_num_rows($result_pengumpulan) > 0) {
                        while ($data = mysqli_fetch_assoc($result_pengumpulan)) {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                        <td>
                            <a href="../uploads/<?php echo urlencode($data['nama_file']); ?>" target="_blank"><i
                                class="fas fa-file-alt"></i> <?php echo htmlspecialchars($data['nama_file']); ?></a>
                        </td>
                        <td><?php echo date('d M Y, H:i', strtotime($data['tanggal_upload'])); ?></td>
                        <td><?php echo (isset($data['nilai_tugas']) && $data['nilai_tugas'] !== null) ? htmlspecialchars($data['nilai_tugas']) : '-'; ?>
                        </td>
                        <td><?php echo (isset($data['komentar']) && $data['komentar'] !== null) ? nl2br(htmlspecialchars($data['komentar'])) : '-'; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#nilaiModal<?php echo $data['id_akun']; ?>">
                                <i class="fas fa-edit"></i> Input/Edit Nilai
                            </button>

                            <div class="modal fade" id="nilaiModal<?php echo $data['id_akun']; ?>" tabindex="-1"
                                aria-labelledby="nilaiModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="nilaiModalLabel">Nilai Tugas:
                                                <?php echo htmlspecialchars($data['nama_lengkap']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_tugas"
                                                    value="<?php echo htmlspecialchars($id_tugas); ?>">
                                                <input type="hidden" name="id_akun"
                                                    value="<?php echo htmlspecialchars($data['id_akun']); ?>">
                                                <div class="mb-3">
                                                    <label for="nilai_tugas_<?php echo $data['id_akun']; ?>"
                                                        class="form-label">Nilai Tugas (0-100)</label>
                                                    <input type="number" class="form-control"
                                                        id="nilai_tugas_<?php echo $data['id_akun']; ?>"
                                                        name="nilai_tugas" min="0" max="100"
                                                        value="<?php echo (isset($data['nilai_tugas']) && $data['nilai_tugas'] !== null) ? htmlspecialchars($data['nilai_tugas']) : ''; ?>"
                                                        required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="komentar_<?php echo $data['id_akun']; ?>"
                                                        class="form-label">Komentar Dosen (Opsional)</label>
                                                    <textarea class="form-control"
                                                        id="komentar_<?php echo $data['id_akun']; ?>" name="komentar"
                                                        rows="3"><?php echo (isset($data['komentar']) && $data['komentar'] !== null) ? htmlspecialchars($data['komentar']) : ''; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="submit_nilai" class="btn btn-primary">Simpan
                                                    Nilai</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'><div class='empty-state'><p>Belum ada mahasiswa yang mengumpulkan tugas ini.</p></div></td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Kembali ke Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
<?php
mysqli_close($koneksi);
?>