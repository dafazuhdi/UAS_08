<?php
session_start();

if (!isset($_SESSION['id_akun']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../index.php");
    exit();
}

include '../config/koneksi.php';

$id_akun_mahasiswa = $_SESSION['id_akun'];
$nama_lengkap_mahasiswa = $_SESSION['nama_lengkap'] ?? $_SESSION['username'];

$query_tasks = "
    SELECT
        t.*,
        t.nama_file_tugas_admin, -- Fetch file uploaded by admin
        p.id_pengumpulan,
        p.nama_file AS submitted_file_name,
        p.tanggal_upload,
        n.nilai_tugas,
        n.komentar AS komentar_dosen
    FROM
        tugas t
    LEFT JOIN
        pengumpulan p ON t.id_tugas = p.id_tugas AND p.id_akun = '$id_akun_mahasiswa'
    LEFT JOIN
        nilai n ON p.id_akun = n.id_akun AND p.id_tugas = n.id_tugas
    ORDER BY
        t.deadline ASC";
$result_tasks = mysqli_query($koneksi, $query_tasks);

$pesan_sukses = "";
$pesan_error = "";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Sistem Pengumpulan Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
    }

    .hero-banner {
        background-color: #e3f2fd;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-bottom: 1px solid #cce5ff;
    }

    .card-task {
        transition: transform 0.2s ease-in-out;
    }

    .card-task:hover {
        transform: translateY(-5px);
    }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-mortarboard-fill me-2"></i>Sistem Tugas Kampus
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#"><i
                                class="bi bi-house-door-fill me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i
                                class="bi bi-person-fill me-1"></i><?php echo htmlspecialchars($nama_lengkap_mahasiswa); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item text-danger" href="../config/logout.php"><i
                                        class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="flex-grow-1">
        <div class="container mt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-light p-2 rounded-3 shadow-sm">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tugas Anda</li>
                </ol>
            </nav>

            <h3 class="mb-4 text-dark fw-bold"><i class="bi bi-clipboard-check me-2"></i>Daftar Tugas Anda</h3>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
                <?php if (mysqli_num_rows($result_tasks) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result_tasks)):
                    $is_submitted = !empty($row['submitted_file_name']);
                    $status_badge_class = $is_submitted ? 'bg-success' : 'bg-warning text-dark';
                    $status_icon_class = $is_submitted ? 'bi-cloud-arrow-up' : 'bi-upload';
                    $status_text = $is_submitted ? 'Sudah Upload' : 'Belum Upload';
                    $card_border_class = $is_submitted ? 'border-success' : '';
                    $current_time = new DateTime();
                    $deadline_time = new DateTime($row['deadline']);
                    $is_overdue = $current_time > $deadline_time;

                    if ($is_overdue && !$is_submitted) {
                        $status_badge_class = 'bg-danger text-white';
                        $status_text = 'Terlambat';
                        $card_border_class = 'border-danger';
                    }

                    $grade_display = 'Belum Dinilai';
                    $grade_badge_class = 'bg-info text-dark';
                    if ($is_submitted && isset($row['nilai_tugas']) && $row['nilai_tugas'] !== null) {
                        $grade_display = htmlspecialchars($row['nilai_tugas']) . '/100';
                        $grade_badge_class = 'bg-primary';
                    }
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm rounded-3 card-task <?php echo $card_border_class; ?>">
                        <div class="card-body">
                            <h5 class="card-title text-primary fw-bold">
                                <?php echo htmlspecialchars($row['judul_tugas']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted">ID Tugas:
                                <?php echo htmlspecialchars($row['id_tugas']); ?></h6>
                            <p class="card-text small text-truncate mb-2">Deskripsi:
                                <?php echo htmlspecialchars($row['deskripsi_tugas']); ?></p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="bi bi-calendar-event me-2"></i>Deadline: <span
                                        class="fw-semibold"><?php echo date('d F Y H:i', strtotime($row['deadline'])); ?></span>
                                </li>
                                <li><i class="bi <?php echo $status_icon_class; ?> me-2"></i>Status: <span
                                        class="badge <?php echo $status_badge_class; ?>"><?php echo $status_text; ?></span>
                                </li>
                                <?php if ($is_submitted): ?>
                                <li><i class="bi bi-clock-history me-2"></i>Diupload pada: <span
                                        class="fw-semibold"><?php echo date('d F Y H:i', strtotime($row['tanggal_upload'])); ?></span>
                                </li>
                                <?php endif; ?>
                            </ul>

                            <?php if (!empty($row['nama_file_tugas_admin'])): ?>
                            <div class="mb-3">
                                <a href="../uploads/tugas_admin/<?php echo urlencode($row['nama_file_tugas_admin']); ?>"
                                    target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Lihat File Tugas
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if (!$is_submitted): ?>
                            <form action="upload_tugas.php" method="POST" enctype="multipart/form-data"
                                class="needs-validation" novalidate>
                                <input type="hidden" name="id_tugas" value="<?php echo $row['id_tugas']; ?>">
                                <input type="hidden" name="id_akun" value="<?php echo $id_akun_mahasiswa; ?>">
                                <div class="input-group mb-3">
                                    <input type="file" class="form-control" name="file_tugas"
                                        id="uploadFile_<?php echo $row['id_tugas']; ?>" required>
                                    <button class="btn btn-outline-primary" type="submit" name="submit_upload"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Upload Tugas">
                                        <i class="bi bi-upload"></i> Upload
                                    </button>
                                    <div class="invalid-feedback">
                                        Pilih file untuk diunggah.
                                    </div>
                                </div>
                            </form>
                            <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge <?php echo $grade_badge_class; ?> py-2 px-3 rounded-pill"><i
                                        class="bi bi-star-fill me-1"></i>Nilai: <?php echo $grade_display; ?></span>

                                <div>
                                    <a href="../uploads/<?php echo htmlspecialchars($row['submitted_file_name']); ?>"
                                        target="_blank" class="btn btn-sm btn-outline-info me-2"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat File">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-warning me-2" data-bs-toggle="modal"
                                        data-bs-target="#editUploadModal"
                                        data-id_tugas="<?php echo $row['id_tugas']; ?>"
                                        data-id_akun="<?php echo $id_akun_mahasiswa; ?>"
                                        data-old_file="<?php echo htmlspecialchars($row['submitted_file_name']); ?>"
                                        title="Ganti File">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="hapus_tugas.php?id_tugas=<?php echo $row['id_tugas']; ?>&id_akun=<?php echo $id_akun_mahasiswa; ?>"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini? File yang sudah diunggah juga akan dihapus.');"
                                        class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Hapus Tugas">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <p class="card-text"><small class="text-muted">Nama File:
                                    <?php echo htmlspecialchars($row['submitted_file_name']); ?></small></p>
                            <?php if (isset($row['komentar_dosen']) && $row['komentar_dosen'] !== null): ?>
                            <p class="card-text"><small class="text-info">Komentar Dosen:
                                    <?php echo nl2br(htmlspecialchars($row['komentar_dosen'])); ?></small></p>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php if (!empty($row['catatan_dosen'])): ?>
                            <p class="card-text"><small class="text-info">Catatan Dosen:
                                    <?php echo nl2br(htmlspecialchars($row['catatan_dosen'])); ?></small></p>
                            <?php endif; ?>
                            <p class="card-text"><small class="text-muted">Ukuran file maksimal: DOCX/PDF</small></p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        Tidak ada tugas yang tersedia saat ini.
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="modal fade" id="editUploadModal" tabindex="-1" aria-labelledby="editUploadModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editUploadModalLabel">Edit Unggahan Tugas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="edit_tugas.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" name="id_tugas" id="edit_id_tugas">
                                <input type="hidden" name="id_akun" id="edit_id_akun">
                                <input type="hidden" name="old_file" id="edit_old_file">
                                <div class="mb-3">
                                    <label for="file_tugas_edit" class="form-label">Pilih file baru (DOCX/PDF)</label>
                                    <input type="file" class="form-control" id="file_tugas_edit" name="file_tugas_edit"
                                        required>
                                </div>
                                <p class="text-muted small">Mengunggah file baru akan menggantikan file sebelumnya.
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="submit_edit_upload" class="btn btn-primary">Simpan
                                    Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <h3 class="mb-4 text-dark fw-bold"><i class="bi bi-chat-dots-fill me-2"></i>Catatan Dosen</h3>
            <div class="accordion mb-5" id="notesAccordion">
                <div class="accordion-item shadow-sm rounded-3">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            Catatan Umum: Perhatikan Penamaan File
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                        data-bs-parent="#notesAccordion">
                        <div class="accordion-body">
                            <div class="alert alert-info border-0 rounded-3" role="alert">
                                <h4 class="alert-heading"><i class="bi bi-info-circle-fill me-2"></i>Penting!</h4>
                                <p>Mohon perhatikan format penamaan file tugas yang sudah disepakati (misal:
                                    NIM_NamaMK_NamaTugas.pdf). Ini akan mempermudah proses penilaian. Terima kasih.</p>
                                <hr>
                                <p class="mb-0 small text-muted">Dikeluarkan pada: 10 Juni 2025</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="bg-dark text-white-50 py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Sistem Pengumpulan Tugas. Dibuat dengan <i
                    class="bi bi-heart-fill text-danger"></i> Bootstrap 5.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Populate Edit Upload Modal with data
    var editUploadModal = document.getElementById('editUploadModal');
    editUploadModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var id_tugas = button.getAttribute('data-id_tugas');
        var id_akun = button.getAttribute('data-id_akun');
        var old_file = button.getAttribute('data-old_file');

        var modalIdTugas = editUploadModal.querySelector('#edit_id_tugas');
        var modalIdAkun = editUploadModal.querySelector('#edit_id_akun');
        var modalOldFile = editUploadModal.querySelector('#edit_old_file');

        modalIdTugas.value = id_tugas;
        modalIdAkun.value = id_akun;
        modalOldFile.value = old_file;
    });

    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
    })();
    </script>
</body>

</html>
<?php
mysqli_close($koneksi);
?>