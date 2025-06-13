<?php
session_start(); // TAMBAHKAN INI DI BARIS PALING ATAS

// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['id_akun']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php"); // Redirect ke halaman login utama jika tidak login atau bukan admin
    exit();
}

include '../config/koneksi.php'; // Pertahankan hanya satu include ini

$pesan_sukses = "";
$pesan_error = "";

if (isset($_POST['submit_tambah_tugas'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul_tugas']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi_tugas']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan_dosen']);
    $deadline = mysqli_real_escape_string($koneksi, $_POST['deadline_tugas']);

    $nama_file_tugas = null;
    $target_dir = "../uploads/tugas_admin/";

    // --- Validasi Sisi Server (PHP) ---
    // Definisikan batas maksimum karakter
    $max_judul = 50;
    $max_deskripsi = 25; // Contoh, sesuaikan jika perlu
    $max_catatan = 25; // Contoh, sesuaikan jika perlu

    // Validasi field utama tidak boleh kosong
    if (empty($judul) || empty($deskripsi) || empty($deadline)) {
        $pesan_error = "Judul, deskripsi, dan deadline tidak boleh kosong.";
    }
    // Validasi panjang karakter untuk judul
    elseif (strlen($judul) > $max_judul) {
        $pesan_error = "Judul tugas terlalu panjang (maks. {$max_judul} karakter).";
    }
    // Tambahkan validasi panjang karakter untuk deskripsi dan catatan jika ingin
    // elseif (strlen($deskripsi) > $max_deskripsi) {
    //     $pesan_error = "Deskripsi tugas terlalu panjang (maks. {$max_deskripsi} karakter).";
    // }
    // elseif (strlen($catatan) > $max_catatan) {
    //     $pesan_error = "Catatan dosen terlalu panjang (maks. {$max_catatan} karakter).";
    // }
    else {
        // Process file upload only if main fields and basic length validations are valid
        if (isset($_FILES['file_tugas_admin']) && $_FILES['file_tugas_admin']['error'] == 0) {
            $original_file_name = basename($_FILES["file_tugas_admin"]["name"]);
            $file_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
            $allowed_extensions = array("docx", "pdf");

            if (in_array($file_extension, $allowed_extensions)) {
                // Ensure directory exists
                if (!is_dir($target_dir)) {
                    if (!mkdir($target_dir, 0777, true)) { // Added check for mkdir success
                        $pesan_error = "Gagal membuat direktori upload. Mohon periksa izin server.";
                    }
                }

                if (empty($pesan_error)) { // Proceed only if directory creation was successful
                    $nama_file_tugas = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9_\-.]/", "", $original_file_name); // Sanitize filename
                    $target_file = $target_dir . $nama_file_tugas;

                    if (!move_uploaded_file($_FILES["file_tugas_admin"]["tmp_name"], $target_file)) {
                        $pesan_error = "Maaf, terjadi kesalahan saat mengunggah file tugas.";
                        $nama_file_tugas = null; // Reset if upload fails
                    }
                }
            } else {
                $pesan_error = "Maaf, hanya file DOCX dan PDF yang diperbolehkan untuk file tugas.";
            }
        }

        // Only insert into DB if no error occurred so far (including file upload errors)
        if (empty($pesan_error)) {
            $query_insert = "INSERT INTO tugas (judul_tugas, deskripsi_tugas, catatan_dosen, deadline, nama_file_tugas_admin) VALUES ('$judul', '$deskripsi', '$catatan', '$deadline', ";
            $query_insert .= ($nama_file_tugas) ? "'$nama_file_tugas')" : "NULL)";

            if (mysqli_query($koneksi, $query_insert)) {
                $pesan_sukses = "Tugas berhasil ditambahkan!";
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
    <title>Tambah Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Optional: Add CSS for character counter */
        .form-text {
            font-size: 0.875em; /* Bootstrap's default for .form-text */
            color: #6c757d; /* Bootstrap's default for .form-text */
            text-align: right; /* Align counter to the right */
            display: block; /* Ensure it takes full width */
        }
    </style>
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
                    <li class="nav-item">
                        <a class="nav-link" href="../config/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Tambah Tugas Baru</h2>
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
        <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="judul_tugas" class="form-label">Judul Tugas</label>
                <input type="text" class="form-control" id="judul_tugas" name="judul_tugas" maxlength="50" required>
                <div class="invalid-feedback">
                    Judul tugas tidak boleh kosong dan maksimal 50 karakter.
                </div>
                <small class="form-text text-muted">
                    <span id="judulCharCount">0</span>/<span id="judulMaxChars">50</span> karakter
                </small>
            </div>
            <div class="mb-3">
                <label for="deskripsi_tugas" class="form-label">Deskripsi Tugas</label>
                <textarea class="form-control" id="deskripsi_tugas" name="deskripsi_tugas" rows="5" maxlength="25" required></textarea>
                <div class="invalid-feedback">
                    Deskripsi tugas tidak boleh kosong dan maksimal 1000 karakter.
                </div>
                <small class="form-text text-muted">
                    <span id="deskripsiCharCount">0</span>/<span id="deskripsiMaxChars">1000</span> karakter
                </small>
            </div>
            <div class="mb-3">
                <label for="catatan_dosen" class="form-label">Catatan Dosen (Opsional)</label>
                <textarea class="form-control" id="catatan_dosen" name="catatan_dosen" rows="3" maxlength="25"></textarea>
                <small class="form-text text-muted">
                    <span id="catatanCharCount">0</span>/<span id="catatanMaxChars">500</span> karakter
                </small>
            </div>
            <div class="mb-3">
                <label for="deadline_tugas" class="form-label">Deadline</label>
                <input type="datetime-local" class="form-control" id="deadline_tugas" name="deadline_tugas" required>
                <div class="invalid-feedback">
                    Deadline tidak boleh kosong.
                </div>
            </div>
            <div class="mb-3">
                <label for="file_tugas_admin" class="form-label">File Tugas (DOCX/PDF - Opsional)</label>
                <input type="file" class="form-control" id="file_tugas_admin" name="file_tugas_admin"
                    accept=".docx,.pdf">
                <div class="form-text">Unggah file tugas jika ada (misal: lembar soal, studi kasus).</div>
            </div>
            <button type="submit" name="submit_tambah_tugas" class="btn btn-primary"><i class="fas fa-plus-circle"></i>
                Tambah Tugas</button>
            <a href="admin_dashboard.php" class="btn btn-secondary">Batal</a>
        </form>
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
                // Set max length from attribute if not already set in HTML (fallback)
                if (inputElement.maxLength === -1) {
                    inputElement.maxLength = parseInt(maxCharsElement.textContent);
                }
                maxCharsElement.textContent = inputElement.maxLength; // Ensure max chars displayed is accurate

                function updateCount() {
                    charCountElement.textContent = inputElement.value.length;
                }

                updateCount(); // Update count on page load
                inputElement.addEventListener('input', updateCount); // Update count on input
            }
        }

        // Setup counter untuk setiap input
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