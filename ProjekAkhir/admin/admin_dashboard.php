<?php
session_start();

if (!isset($_SESSION['id_akun']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include '../config/koneksi.php';

$nama_lengkap_admin = $_SESSION['nama_lengkap'] ?? $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    body {
        background-color: #f8f9fa;
    }

    .task-card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        margin-bottom: 20px;
    }

    .task-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .task-card .card-header {
        background-color: #343a40;
        color: white;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        min-height: 60px;
        display: flex;
        align-items: center;
        padding: 0.75rem 1.25rem; /* Ensure consistent padding */
    }

    .empty-state {
        text-align: center;
        padding: 50px 0;
        color: #6c757d;
    }

    .empty-state img {
        max-width: 200px;
        margin-bottom: 20px;
        height: auto; /* Ensure image scales proportionally */
    }

    /* CSS for Uniform Button Size and Responsiveness */
    .task-actions {
        display: flex;
        flex-wrap: wrap; /* Allows buttons to wrap to the next line */
        gap: 8px; /* Consistent spacing between buttons */
        align-items: stretch;
        justify-content: center; /* Center buttons when they wrap */
    }

    .task-actions .btn {
        flex-grow: 1; /* Allows buttons to grow and fill available space */
        min-width: 100px; /* Minimum width for buttons */
        /* max-width: 180px;  Consider if you want to limit max width, or let flex-grow handle it */
        text-align: center;
        height: 40px; /* Uniform height for buttons */
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 0.9rem; /* Adjust font size for better fit on small screens */
    }

    /* Media query for very small devices (e.g., phones in portrait) */
    @media (max-width: 575.98px) {
        .task-actions {
            flex-direction: column; /* Stack buttons vertically */
            gap: 10px; /* More space when stacked */
        }
        .task-actions .btn {
            width: 100%; /* Make buttons take full width when stacked */
            margin: 0; /* Remove any lingering margins */
        }
        .card-header .card-title {
            font-size: 1.1rem; /* Slightly smaller title for small screens */
        }
    }

    /* Media query for small to medium devices */
    @media (min-width: 576px) and (max-width: 767.98px) {
        .task-actions .btn {
            font-size: 0.85rem; /* Smaller font for slightly more compact buttons */
        }
    }

    /* Adjust container padding for very small screens */
    @media (max-width: 575.98px) {
        .container.mt-4 {
            padding-left: 15px;
            padding-right: 15px;
        }
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
                        <a class="nav-link active" aria-current="page" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarAdminDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($nama_lengkap_admin); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarAdminDropdown">
                            <li><a class="dropdown-item text-danger" href="../config/logout_admin.php"><i
                                        class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4 text-center text-md-start">Daftar Tugas</h2> <div class="d-flex justify-content-center justify-content-md-end mb-3"> <a href="tambah_tugas.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Tambah Tugas Baru</a>
        </div>
        <div class="row">
            <?php
            $query_tugas = "SELECT
                                t.*,
                                COUNT(DISTINCT p.id_akun) AS jumlah_pengumpul,
                                COUNT(DISTINCT n.id_akun) AS jumlah_dinilai
                            FROM
                                tugas t
                            LEFT JOIN
                                pengumpulan p ON t.id_tugas = p.id_tugas
                            LEFT JOIN
                                nilai n ON t.id_tugas = n.id_tugas
                            GROUP BY
                                t.id_tugas
                            ORDER BY
                                t.deadline ASC";
            $result_tugas = mysqli_query($koneksi, $query_tugas);

            if (mysqli_num_rows($result_tugas) > 0) {
                while ($tugas = mysqli_fetch_assoc($result_tugas)) {
            ?>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <div class="card task-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($tugas['judul_tugas']); ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><strong>Deskripsi:</strong>
                            <?php echo nl2br(htmlspecialchars($tugas['deskripsi_tugas'])); ?></p>
                        <?php if (!empty($tugas['catatan_dosen'])): ?>
                        <p class="card-text"><strong>Catatan Dosen:</strong>
                            <?php echo nl2br(htmlspecialchars($tugas['catatan_dosen'])); ?></p>
                        <?php endif; ?>
                        <p class="card-text"><strong>Deadline:</strong> <i class="far fa-clock me-1"></i>
                            <?php echo date('d M Y, H:i', strtotime($tugas['deadline'])); ?></p>
                        <hr>
                        <p class="card-text">
                            <i class="fas fa-users me-1"></i> Jumlah Pengumpul: <span
                                class="badge bg-info"><?php echo $tugas['jumlah_pengumpul']; ?></span>
                        </p>
                        <p class="card-text">
                            <i class="fas fa-check-circle me-1"></i> Mahasiswa Dinilai: <span
                                class="badge bg-success"><?php echo $tugas['jumlah_dinilai']; ?></span>
                        </p>
                        <hr>
                        <div class="task-actions">
                            <a href="input_nilai.php?id_tugas=<?php echo $tugas['id_tugas']; ?>"
                                class="btn btn-sm btn-info"><i class="fas fa-clipboard-check me-1"></i> Input Nilai</a>
                            <a href="edit_tugas.php?id_tugas=<?php echo $tugas['id_tugas']; ?>"
                                class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i> Edit Tugas</a>
                            <a href="hapus_tugas.php?id_tugas=<?php echo $tugas['id_tugas']; ?>"
                                class="btn btn-sm btn-danger">
                                <i class="fas fa-trash-alt me-1"></i> Hapus Tugas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
            ?>
            <div class="col-12 empty-state">
                <img src="https://via.placeholder.com/150" alt="No tasks icon"> <h3>Belum ada tugas yang tersedia.</h3>
                <p>Klik tombol "Tambah Tugas Baru" untuk mulai membuat tugas.</p>
            </div>
            <?php
            }
            ?>
        </div>
    </div>

    <div class="modal fade" id="viewSubmissionsModal" tabindex="-1" aria-labelledby="viewSubmissionsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg"> <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSubmissionsModalLabel">Daftar Pengumpul Tugas: <span
                            id="modalTaskTitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive"> <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Mahasiswa</th>
                                    <th>File Dikumpulkan</th>
                                    <th>Tanggal Upload</th>
                                    <th>Nilai</th>
                                    <th>Komentar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="submissionsTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
    var viewSubmissionsModal = document.getElementById('viewSubmissionsModal');
    viewSubmissionsModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var id_tugas = button.getAttribute('data-id_tugas');
        var judul_tugas = button.getAttribute('data-judul_tugas');

        var modalTitle = viewSubmissionsModal.querySelector('#modalTaskTitle');
        modalTitle.textContent = judul_tugas;

        var submissionsTableBody = viewSubmissionsModal.querySelector('#submissionsTableBody');
        submissionsTableBody.innerHTML = '';

        fetch('get_submissions.php?id_tugas=' + id_tugas)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    data.forEach(submission => {
                        var row = `
                                <tr>
                                    <td>${submission.nama_lengkap}</td>
                                    <td><a href="../uploads/${encodeURIComponent(submission.nama_file)}" target="_blank"><i class="fas fa-file-alt me-1"></i> ${submission.nama_file}</a></td>
                                    <td>${new Date(submission.tanggal_upload).toLocaleString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</td>
                                    <td>${submission.nilai_tugas !== null ? submission.nilai_tugas : '-'}</td>
                                    <td>${submission.komentar !== null ? submission.komentar : '-'}</td>
                                    <td>
                                        <a href="input_nilai.php?id_tugas=${id_tugas}" class="btn btn-sm btn-primary">Input/Edit Nilai</a>
                                    </td>
                                </tr>
                            `;
                        submissionsTableBody.innerHTML += row;
                    });
                } else {
                    submissionsTableBody.innerHTML =
                        `<tr><td colspan="6" class="text-center"><div class="empty-state"><p>Belum ada mahasiswa yang mengumpulkan tugas ini.</p></div></td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error fetching submissions:', error);
                submissionsTableBody.innerHTML =
                    `<tr><td colspan="6" class="text-center text-danger">Gagal memuat data pengumpulan.</td></tr>`;
            });
    });
    </script>
</body>

</html>
<?php
mysqli_close($koneksi);
?>