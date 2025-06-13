<?php
session_start();
include '../config/koneksi.php';

$pesan_sukses = "";
$pesan_error = "";
$tugas_data = [];

if (isset($_GET['id_tugas'])) {
    $id_tugas = mysqli_real_escape_string($koneksi, $_GET['id_tugas']);
    // Ensure nama_file_tugas_admin is selected
    $query_get_tugas = "SELECT * FROM tugas WHERE id_tugas = '$id_tugas'";
    $result_get_tugas = mysqli_query($koneksi, $query_get_tugas);

    if (mysqli_num_rows($result_get_tugas) > 0) {
        $tugas_data = mysqli_fetch_assoc($result_get_tugas);
    } else {
        $pesan_error = "Tugas tidak ditemukan.";
    }
}

if (isset($_POST['submit_edit_tugas'])) {
    $id_tugas = mysqli_real_escape_string($koneksi, $_POST['id_tugas']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul_tugas']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi_tugas']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan_dosen']);
    $deadline = mysqli_real_escape_string($koneksi, $_POST['deadline_tugas']);

    // Fetch current tugas_data again to ensure it's up-to-date, especially for file handling
    $current_tugas_query = "SELECT nama_file_tugas_admin FROM tugas WHERE id_tugas = '$id_tugas'";
    $current_tugas_result = mysqli_query($koneksi, $current_tugas_query);
    $current_tugas_data = mysqli_fetch_assoc($current_tugas_result);
    $nama_file_tugas_admin_lama = $current_tugas_data['nama_file_tugas_admin'];


    $nama_file_tugas_admin_baru = $nama_file_tugas_admin_lama; // Start with the old file name
    $target_dir = "../uploads/tugas_admin/";

    // Process upload file tugas baru if exists
    if (isset($_FILES['file_tugas_admin_edit']) && $_FILES['file_tugas_admin_edit']['error'] == 0) {
        $original_file_name = basename($_FILES["file_tugas_admin_edit"]["name"]);
        $file_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
        $allowed_extensions = array("docx", "pdf");

        if (in_array($file_extension, $allowed_extensions)) {
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {
                    $pesan_error = "Gagal membuat direktori upload. Mohon periksa izin server.";
                }
            }

            if (empty($pesan_error)) {
                $nama_file_tugas_admin_baru = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9_\-.]/", "", $original_file_name); // Sanitize filename
                $target_file = $target_dir . $nama_file_tugas_admin_baru;

                // Delete old file if a new one is successfully uploaded
                if (!empty($nama_file_tugas_admin_lama) && file_exists($target_dir . $nama_file_tugas_admin_lama)) {
                    unlink($target_dir . $nama_file_tugas_admin_lama);
                }

                if (!move_uploaded_file($_FILES["file_tugas_admin_edit"]["tmp_name"], $target_file)) {
                    $pesan_error = "Maaf, terjadi kesalahan saat mengunggah file tugas baru.";
                    $nama_file_tugas_admin_baru = $nama_file_tugas_admin_lama; // Revert to old name if new upload fails
                }
            }
        } else {
            $pesan_error = "Maaf, hanya file DOCX dan PDF yang diperbolehkan untuk file tugas.";
            $nama_file_tugas_admin_baru = $nama_file_tugas_admin_lama; // Revert to old name if extension not valid
        }
    } else if (isset($_POST['remove_file_tugas_admin']) && $_POST['remove_file_tugas_admin'] == 'yes') {
        // Hapus file jika checkbox "Hapus File" dicentang
        if (!empty($nama_file_tugas_admin_lama) && file_exists($target_dir . $nama_file_tugas_admin_lama)) {
            unlink($target_dir . $nama_file_tugas_admin_lama);
        }
        $nama_file_tugas_admin_baru = NULL; // Set ke NULL di database
    }

    // --- Validasi Sisi Server (PHP) ---
    if (empty($judul) || empty($deskripsi) || empty($catatan) || empty($deadline)) { // Tambahkan $catatan
        $pesan_error = "Judul, deskripsi, catatan dosen, dan deadline tidak boleh kosong.";
    } else {
        // Optional: Tambahkan validasi panjang karakter di sisi server
        // Sesuaikan nilai maksimal dengan yang Anda inginkan
        $max_judul = 50;
        $max_deskripsi = 25; // Contoh
        $max_catatan = 25; // Contoh

        if (strlen($judul) > $max_judul) {
            $pesan_error = "Judul tugas terlalu panjang (maks. {$max_judul} karakter).";
        } elseif (strlen($deskripsi) > $max_deskripsi) {
            $pesan_error = "Deskripsi tugas terlalu panjang (maks. {$max_deskripsi} karakter).";
        } elseif (strlen($catatan) > $max_catatan) { // Validasi catatan dosen
            $pesan_error = "Catatan dosen terlalu panjang (maks. {$max_catatan} karakter).";
        } else {
            // Perbarui query UPDATE untuk kolom nama_file_tugas_admin
            $query_update = "UPDATE tugas SET judul_tugas = '$judul', deskripsi_tugas = '$deskripsi', catatan_dosen = '$catatan', deadline = '$deadline', nama_file_tugas_admin = ";
            $query_update .= ($nama_file_tugas_admin_baru !== null) ? "'$nama_file_tugas_admin_baru' WHERE id_tugas = '$id_tugas'" : "NULL WHERE id_tugas = '$id_tugas'";


            if (mysqli_query($koneksi, $query_update)) {
                $pesan_sukses = "Tugas berhasil diperbarui!";
                header("refresh:2;url=admin_dashboard.php");
                exit(); // Always exit after header redirect
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
    <title>Edit Tugas</title>
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
                        <a class="nav-link active" aria-current="page" href="tambah_tugas.php">Tambah Tugas</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Edit Tugas</h2>
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

        <?php if (!empty($tugas_data)): ?>
        <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="id_tugas" value="<?php echo htmlspecialchars($tugas_data['id_tugas']); ?>">
            <div class="mb-3">
                <label for="judul_tugas" class="form-label">Judul Tugas</label>
                <input type="text" class="form-control" id="judul_tugas" name="judul_tugas"
                    value="<?php echo htmlspecialchars($tugas_data['judul_tugas']); ?>" maxlength="50" required>
                <div class="invalid-feedback">
                    Judul tugas tidak boleh kosong.
                </div>
                <small class="form-text text-muted">
                    <span id="judulCharCount">0</span>/<span id="judulMaxChars">50</span>karakter
                </small>
            </div>
            <div class="mb-3">
                <label for="deskripsi_tugas" class="form-label">Deskripsi Tugas</label>
                <textarea class="form-control" id="deskripsi_tugas" name="deskripsi_tugas" rows="5"
                    maxlength="25" required><?php echo htmlspecialchars($tugas_data['deskripsi_tugas']); ?></textarea>
                <div class="invalid-feedback">
                    Deskripsi tugas tidak boleh kosong.
                </div>
                <small class="form-text text-muted">
                    <span id="deskripsiCharCount">0</span>/<span id="deskripsiMaxChars">25</span> karakter
                </small>
            </div>
            <div class="mb-3">
                <label for="catatan_dosen" class="form-label">Catatan Dosen</label> <textarea class="form-control" id="catatan_dosen" name="catatan_dosen" rows="3"
                    maxlength="25" required><?php echo htmlspecialchars($tugas_data['catatan_dosen']); ?></textarea>
                <div class="invalid-feedback">
                    Catatan dosen tidak boleh kosong.
                </div>
                <small class="form-text text-muted">
                    <span id="catatanCharCount">0</span>/<span id="catatanMaxChars">25</span> karakter
                </small>
            </div>
            <div class="mb-3">
                <label for="deadline_tugas" class="form-label">Deadline</label>
                <input type="datetime-local" class="form-control" id="deadline_tugas" name="deadline_tugas"
                    value="<?php echo date('Y-m-d\TH:i', strtotime($tugas_data['deadline'])); ?>" required>
                <div class="invalid-feedback">
                    Deadline tidak boleh kosong.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">File Tugas Saat Ini</label>
                <?php if (!empty($tugas_data['nama_file_tugas_admin'])): ?>
                <p>
                    <a href="../uploads/tugas_admin/<?php echo urlencode($tugas_data['nama_file_tugas_admin']); ?>"
                        target="_blank" class="btn btn-sm btn-info">
                        <i class="fas fa-file-alt"></i> Lihat File:
                        <?php echo htmlspecialchars($tugas_data['nama_file_tugas_admin']); ?>
                    </a>
                </p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="yes" id="remove_file_tugas_admin"
                        name="remove_file_tugas_admin">
                    <label class="form-check-label" for="remove_file_tugas_admin">
                        Hapus file tugas ini
                    </label>
                </div>
                <?php else: ?>
                <p>Tidak ada file tugas yang diunggah.</p>
                <?php endif; ?>
                <label for="file_tugas_admin_edit" class="form-label mt-2">Ganti File Tugas (DOCX/PDF -
                    Opsional)</label>
                <input type="file" class="form-control" id="file_tugas_admin_edit" name="file_tugas_admin_edit"
                    accept=".docx,.pdf">
                <div class="form-text">Unggah file baru untuk menggantikan yang lama, atau biarkan kosong jika tidak
                    ingin mengubah.</div>
            </div>

            <button type="submit" name="submit_edit_tugas" class="btn btn-primary"><i class="fas fa-save"></i> Simpan
                Perubahan</button>
            <a href="admin_dashboard.php" class="btn btn-secondary">Batal</a>
        </form>
        <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Tugas tidak ditemukan atau ID tidak valid.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi validasi Bootstrap
        (function() {
            'use strict';
            var form = document.querySelector('.needs-validation');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        })();

        // Fungsi untuk mengupdate hitungan karakter
        function setupCharCounter(inputId, charCountId, maxCharsId) {
            const inputElement = document.getElementById(inputId);
            const charCountElement = document.getElementById(charCountId);
            const maxCharsElement = document.getElementById(maxCharsId);

            if (inputElement && charCountElement && maxCharsElement) {
                if (inputElement.maxLength === -1) {
                    inputElement.maxLength = parseInt(maxCharsElement.textContent);
                }
                maxCharsElement.textContent = inputElement.maxLength;

                function updateCount() {
                    charCountElement.textContent = inputElement.value.length;
                }

                updateCount();
                inputElement.addEventListener('input', updateCount);
            }
        }

        setupCharCounter('judul_tugas', 'judulCharCount', 'judulMaxChars');
        setupCharCounter('deskripsi_tugas', 'deskripsiCharCount', 'deskripsiMaxChars');
        setupCharCounter('catatan_dosen', 'catatanCharCount', 'catatanMaxChars');
    });
    </script>
</body>

</html>
<?php
mysqli_close($koneksi);
?>