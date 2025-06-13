<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            overflow: hidden;
        }

        .card-left {
            padding: 3rem;
        }

        .card-right {
            background: linear-gradient(45deg, #FFC107, #FF5722, #00BCD4, #8BC34A);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .btn-hover-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .text-danger {
            margin-top: 2px;
            margin-bottom: 6px;
            font-size: 0.85rem;
        }

        .input-group {
            margin-bottom: 6px;
        }

        .error-card {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            width: 90%;
            max-width: 500px;
        }
    </style>
</head>

<body>
    <?php
    session_start();
    include "config/koneksi.php";

    $nama_lengkap_val = '';
    $email_val = '';
    $username_val = '';
    $nim_val = '';
    $role_val = 'mahasiswa';

    $register_message = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $role = 'mahasiswa';

        $nama_lengkap_val = $nama_lengkap;
        $email_val = $email;
        $username_val = $username;


        $nim = !empty($_POST['nim']) ? trim($_POST['nim']) : NULL;
        $nim_val = $nim;

        if (empty($nama_lengkap) || empty($email) || empty($username) || empty($password) || empty($nim)) {
            $register_message = "Semua kolom wajib diisi.";
        }

        if (empty($register_message)) {
            $stmt_check_username = mysqli_prepare($koneksi, "SELECT username FROM akun WHERE username = ?");
            mysqli_stmt_bind_param($stmt_check_username, "s", $username);
            mysqli_stmt_execute($stmt_check_username);
            mysqli_stmt_store_result($stmt_check_username);

            if (mysqli_stmt_num_rows($stmt_check_username) > 0) {
                $register_message = "Username sudah terdaftar. Silakan gunakan username lain.";
            }
            mysqli_stmt_close($stmt_check_username);
        }

        if (empty($register_message)) {
            $stmt_check_email = mysqli_prepare($koneksi, "SELECT email FROM akun WHERE email = ?");
            mysqli_stmt_bind_param($stmt_check_email, "s", $email);
            mysqli_stmt_execute($stmt_check_email);
            mysqli_stmt_store_result($stmt_check_email);

            if (mysqli_stmt_num_rows($stmt_check_email) > 0) {
                $register_message = "Email sudah terdaftar. Silakan gunakan email lain.";
            }
            mysqli_stmt_close($stmt_check_email);
        }

        if (empty($register_message)) {
            $stmt_check_nim = mysqli_prepare($koneksi, "SELECT nim FROM akun WHERE nim = ?");
            mysqli_stmt_bind_param($stmt_check_nim, "s", $nim);
            mysqli_stmt_execute($stmt_check_nim);
            mysqli_stmt_store_result($stmt_check_nim);

            if (mysqli_stmt_num_rows($stmt_check_nim) > 0) {
                $register_message = "NIM sudah terdaftar.";
            }
            mysqli_stmt_close($stmt_check_nim);
        }

        if (empty($register_message)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO akun (role, nim, nama_lengkap, email, username, password) VALUES (?, ?, ?, ?, ?, ?)");

            error_log("DEBUG: Variabel yang akan di-bind di register.php: Role=" . ($role ?? 'NULL') .
                ", NIM=" . ($nim ?? 'NULL') .
                ", Nama=" . ($nama_lengkap ?? 'NULL') .
                ", Email=" . ($email ?? 'NULL') .
                ", Username=" . ($username ?? 'NULL') .
                ", PasswordHash= [HASHED]");

            mysqli_stmt_bind_param($stmt_insert, "ssssss", $role, $nim, $nama_lengkap, $email, $username, $hashed_password);

            if (mysqli_stmt_execute($stmt_insert)) {
                $_SESSION['registration_success_message'] = "Akun Anda berhasil dibuat! Silakan login.";
                error_log("DEBUG: Akun berhasil didaftar untuk username: " . $username . " dengan role: " . $role);
                header("Location: index.php");
                exit();
            } else {
                $register_message = "Terjadi kesalahan saat pendaftaran: " . mysqli_error($koneksi);
                error_log("ERROR: Gagal insert akun di register.php: " . mysqli_error($koneksi));
            }
            mysqli_stmt_close($stmt_insert);
        }
    }
    ?>

    <?php if (!empty($register_message)) : ?>
        <div class="error-card">
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?php echo $register_message; ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card rounded-5 shadow-lg border-0">
                    <div class="row g-0">
                        <div class="col-md-6 card-left">
                            <h2 class="card-title text-center mb-4 text-success fw-bold">Daftar Akun Baru</h2>
                            <form id="formRegister" method="POST" novalidate>
                                <div class="mb-1 input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control form-control-lg rounded-end" name="nama_lengkap" id="nama_lengkap" placeholder="Nama Lengkap" required value="<?php echo htmlspecialchars($nama_lengkap_val); ?>">
                                </div>
                                <div id="error-nama_lengkap" class="text-danger small ms-2 mt-1 mb-2"></div>

                                <input type="hidden" name="role" value="mahasiswa">

                                <div class="mb-1 input-group" id="nim-group">
                                    <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                                    <input type="text" class="form-control form-control-lg rounded-end" name="nim" id="nim" placeholder="NIM" required value="<?php echo htmlspecialchars($nim_val); ?>">
                                </div>

                                <div id="error-nomor_induk" class="text-danger small ms-2 mt-1 mb-2"></div>

                                <div class="mb-1 input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" class="form-control form-control-lg rounded-end" name="email" id="email" placeholder="Email" required value="<?php echo htmlspecialchars($email_val); ?>">
                                </div>
                                <div id="error-email" class="text-danger small ms-2 mt-1 mb-2"></div>

                                <div class="mb-1 input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control form-control-lg rounded-end" name="username" id="username" placeholder="Username" required value="<?php echo htmlspecialchars($username_val); ?>">
                                </div>
                                <div id="error-username" class="text-danger small ms-2 mt-1 mb-2"></div>

                                <div class="mb-1 input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control form-control-lg rounded-end" name="password" id="password" placeholder="Password" required>
                                </div>
                                <div id="error-password" class="text-danger small ms-2 mt-1 mb-2"></div>

                                <div class="d-grid gap-2 mt-3 mb-2">
                                    <button type="submit" class="btn btn-success btn-lg rounded-pill btn-hover-effect">
                                        <i class="bi bi-person-plus-fill me-2"></i> Daftar Sekarang
                                    </button>
                                </div>

                                <p class="text-center mt-2">Sudah punya akun? <a href="index.php" class="text-decoration-none fw-bold text-success">Login di sini</a></p>
                            </form>

                        </div>
                        <div class="col-md-6 card-right rounded-end-5 d-none d-md-flex">
                            <img src="https://i.pinimg.com/736x/c5/a2/9e/c5a29e3eee65d5583f9ab5e3fba73449.jpg" alt="Ilustrasi Belajar" class="img-fluid w-50 mb-4 rounded-circle border border-white border-3">
                            <h3 class="fw-bold display-6 mb-3">"Masa depan adalah milik mereka yang mempersiapkan
                                dirinya hari ini."</h3>
                            <p class="lead">- Malcolm X</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formRegister');
            const nimInput = document.getElementById('nim');

            form.addEventListener('submit', function(e) {
                document.querySelectorAll('.text-danger').forEach(el => el.textContent = '');

                const nama_lengkap = document.getElementById('nama_lengkap').value.trim();
                const email = document.getElementById('email').value.trim();
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value.trim();
                const nomor_induk = nimInput.value.trim();

                let isValidClientSide = true;

                if (!nama_lengkap) {
                    document.getElementById('error-nama_lengkap').textContent = 'Nama lengkap wajib diisi.';
                    isValidClientSide = false;
                } else if (nama_lengkap.length < 3) {
                    document.getElementById('error-nama_lengkap').textContent =
                        'Nama lengkap minimal 3 karakter.';
                    isValidClientSide = false;
                }

                if (!nomor_induk) {
                    document.getElementById('error-nomor_induk').textContent =
                        'NIM wajib diisi.';
                    isValidClientSide = false;
                } else if (!/^\d+$/.test(nomor_induk)) {
                    document.getElementById('error-nomor_induk').textContent =
                        'NIM harus berupa angka.';
                    isValidClientSide = false;
                } else if (nomor_induk.length < 8) {
                    document.getElementById('error-nomor_induk').textContent = 'NIM minimal 8 digit.';
                    isValidClientSide = false;
                }

                if (!email) {
                    document.getElementById('error-email').textContent = 'Email wajib diisi.';
                    isValidClientSide = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    document.getElementById('error-email').textContent = 'Format email tidak valid.';
                    isValidClientSide = false;
                } else {
                    const validDomains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com',
                        'student.ac.id', 'univ.ac.id'
                    ];
                    const emailDomain = email.split('@')[1];
                    if (!validDomains.includes(emailDomain)) {
                        document.getElementById('error-email').textContent =
                            'Email harus menggunakan domain yang valid (misal: @gmail.com atau domain akademik).';
                        isValidClientSide = false;
                    }
                }

                if (!username) {
                    document.getElementById('error-username').textContent = 'Username wajib diisi.';
                    isValidClientSide = false;
                } else if (username.length < 5) {
                    document.getElementById('error-username').textContent = 'Username minimal 5 karakter.';
                    isValidClientSide = false;
                } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                    document.getElementById('error-username').textContent =
                        'Username hanya boleh mengandung huruf, angka, dan underscore.';
                    isValidClientSide = false;
                }

                if (!password) {
                    document.getElementById('error-password').textContent = 'Password wajib diisi.';
                    isValidClientSide = false;
                } else if (password.length < 8) {
                    document.getElementById('error-password').textContent = 'Password minimal 8 karakter.';
                    isValidClientSide = false;
                } else if (!/[A-Z]/.test(password)) {
                    document.getElementById('error-password').textContent =
                        'Password harus mengandung minimal 1 huruf besar.';
                    isValidClientSide = false;
                } else if (!/[0-9]/.test(password)) {
                    document.getElementById('error-password').textContent =
                        'Password harus mengandung minimal 1 angka.';
                    isValidClientSide = false;
                }

                if (!isValidClientSide) {
                    e.preventDefault();
                    const errorCard = document.createElement('div');
                    errorCard.className = 'error-card';
                    errorCard.innerHTML = `
                                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <div>Harap perbaiki semua error yang ditampilkan di form.</div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;
                    document.body.appendChild(errorCard);

                    setTimeout(() => {
                        const alert = errorCard.querySelector('.alert');
                        if (alert) {
                            const bsAlert = new bootstrap.Alert(alert);
                            bsAlert.close();
                        }
                        errorCard.remove();
                    }, 5000);
                }
            });
        });
    </script>
</body>

</html>