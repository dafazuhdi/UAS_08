<?php
session_start();
include 'config/koneksi.php'; // Sesuaikan path jika berbeda

$login_message = "";
$success_message = "";
$username_val = "";

if (isset($_SESSION['registration_success_message'])) {
    $success_message = $_SESSION['registration_success_message'];
    unset($_SESSION['registration_success_message']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $username_val = $username;

    if (empty($username) || empty($password)) {
        $login_message = "Username dan password wajib diisi.";
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT id_akun, username, password, role, nama_lengkap FROM akun WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                if ($user['role'] === 'mahasiswa') {
                    $_SESSION['id_akun'] = $user['id_akun'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? $user['username'];

                    header("Location: mahasiswa/index.php");
                    exit();
                } else {
                    $login_message = "Akses ditolak. Hanya mahasiswa yang dapat login dari sini.";
                }
            } else {
                $login_message = "Password salah. Silakan coba lagi.";
            }
        } else {
            $login_message = "Username tidak ditemukan.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Akademik</title>
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
        border-radius: 1rem !important;
    }

    .card-left {
        padding: 3rem;
    }

    .card-right {
        background: linear-gradient(45deg, #FF6B6B, #FFD166, #8DFF8D, #66D1FF);
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

    .error-card {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        width: 90%;
        max-width: 500px;
    }

    .btn-mahasiswa {
        background-color: #0d6efd;
        color: white;
    }

    .text-danger-js {
        font-size: 0.85rem;
        color: #dc3545;
        margin-top: 0rem;
        margin-left: 0.5rem;
    }
    </style>
</head>

<body>
    <?php if ($login_message): ?>
    <div class="error-card">
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div><?php echo htmlspecialchars($login_message); ?></div>
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
                            <h2 class="text-center mb-4 text-primary fw-bold">Selamat Datang!</h2>

                            <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center"
                                role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <div><?php echo htmlspecialchars($success_message); ?></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                            <?php endif; ?>

                            <form method="POST" id="loginForm">
                                <div class="mb-1 input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control form-control-lg rounded-end" name="username"
                                        id="username" placeholder="Username"
                                        value="<?php echo htmlspecialchars($username_val); ?>">
                                </div>
                                <div id="error-username-js" class="text-danger-js"></div>

                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control form-control-lg rounded-end"
                                            name="password" id="password" placeholder="Password">
                                    </div>
                                    <div id="error-password-js" class="text-danger-js"></div>
                                </div>

                                <div class="d-grid gap-2 mb-3">
                                    <button type="submit"
                                        class="btn btn-mahasiswa btn-lg rounded-pill btn-hover-effect">
                                        <i class="bi bi-mortarboard-fill me-2"></i> Login sebagai Mahasiswa
                                    </button>
                                </div>
                                <p class="text-center mt-3">Belum punya akun?
                                    <a href="register.php" class="text-decoration-none fw-bold text-primary">Daftar di
                                        sini</a>
                                </p>
                            </form>
                        </div>
                        <div class="col-md-6 card-right rounded-end-5 d-none d-md-flex">
                            <img src="https://i.pinimg.com/736x/32/36/96/32369624ffa9dfa80de24edff1055592.jpg"
                                alt="Ilustrasi Edukasi"
                                class="img-fluid w-50 mb-4 rounded-circle border border-white border-3">
                            <h3 class="fw-bold display-10 mb-3">"Pendidikan adalah senjata paling ampuh yang bisa kamu
                                gunakan untuk mengubah dunia."</h3>
                            <p class="lead">- Nelson Mandela</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const errorUsernameJs = document.getElementById('error-username-js');
        const errorPasswordJs = document.getElementById('error-password-js');

        loginForm.addEventListener('submit', function(event) {
            let isValid = true;

            errorUsernameJs.textContent = '';
            errorPasswordJs.textContent = '';

            const existingJsErrorCard = document.querySelector('.error-card.js-validation-error');
            if (existingJsErrorCard) {
                existingJsErrorCard.remove();
            }

            if (usernameInput.value.trim() === '') {
                errorUsernameJs.textContent = 'Username tidak boleh kosong.';
                isValid = false;
            }

            if (passwordInput.value.trim() === '') {
                errorPasswordJs.textContent = 'Password tidak boleh kosong.';
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();

                const errorCard = document.createElement('div');
                errorCard.className = 'error-card js-validation-error';
                errorCard.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div>Harap isi semua kolom yang diperlukan.</div>
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