<?php
session_start();
include 'config/koneksi.php'; // Sesuaikan path jika berbeda

$login_message = "";
$success_message = "";
$username_val = "";

// Cek apakah ada pesan sukses dari registrasi
if (isset($_SESSION['admin_registration_success'])) {
    $success_message = $_SESSION['admin_registration_success'];
    unset($_SESSION['admin_registration_success']);
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

            // Verifikasi password dan cek role
            if (password_verify($password, $user['password'])) {
                if ($user['role'] === 'admin') {
                    $_SESSION['id_akun'] = $user['id_akun'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? $user['username'];

                    // Arahkan ke dashboard admin
                    header("Location: admin/admin_dashboard.php");
                    exit();
                } else {
                    $login_message = "Akses ditolak. Akun ini bukan admin.";
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
    <title>Login Admin - Sistem Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
    body {
        background: linear-gradient(to right, #007bff, #28a745);
        /* Blue to Green gradient for Admin */
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
        background: linear-gradient(45deg, #0056b3, #007bff);
        /* Darker blue gradient */
        color: white;
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    /* Adjust image size for smaller screens */
    .card-right img {
        max-width: 50%; /* Default max-width for img */
        height: auto; /* Maintain aspect ratio */
    }

    @media (max-width: 767.98px) { /* For devices smaller than md (medium) */
        .card-right {
            padding: 2rem; /* Slightly less padding on small screens */
        }
        .card-right img {
            max-width: 60%; /* Make image slightly larger on small screens */
            margin-bottom: 1.5rem !important; /* Adjust margin for spacing */
        }
        .card-right h3 {
            font-size: 1.1rem; /* Smaller heading on small screens */
            margin-bottom: 1.5rem !important;
        }
        .card-right p.lead {
            font-size: 0.9rem; /* Smaller lead text */
        }
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

    .text-danger-js {
        font-size: 0.85rem;
        color: #dc3545;
        margin-top: 0rem;
        margin-left: 0.5rem;
    }
    </style>
</head>

<body>
    <?php if ($login_message || $success_message): ?>
    <div class="error-card">
        <div class="alert <?php echo $login_message ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show d-flex align-items-center"
            role="alert">
            <i
                class="bi <?php echo $login_message ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'; ?> me-2"></i>
            <div><?php echo htmlspecialchars($login_message ?: $success_message); ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card rounded-5 shadow-lg border-0">
                    <div class="row g-0">
                        <div class="col-12 col-md-6 card-left"> <h2 class="text-center mb-4 text-primary fw-bold">Login Admin</h2>
                            <form method="POST" id="loginFormAdmin">
                                <div class="mb-1 input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control form-control-lg rounded-end" name="username"
                                        id="usernameAdmin" placeholder="Username"
                                        value="<?php echo htmlspecialchars($username_val); ?>">
                                </div>
                                <div id="error-username-admin-js" class="text-danger-js"></div>
                                <div class="mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control form-control-lg rounded-end"
                                            name="password" id="passwordAdmin" placeholder="Password">
                                    </div>
                                    <div id="error-password-admin-js" class="text-danger-js"></div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill btn-hover-effect">
                                        <i class="bi bi-shield-lock-fill me-2"></i> Login sebagai Admin
                                    </button>
                                </div>
                                <p class="text-center mt-3">Belum punya akun admin?
                                    <a href="admin_register.php"
                                        class="text-decoration-none fw-bold text-primary">Daftar di sini</a>
                                </p>
                            </form>
                        </div>
                        <div class="col-12 col-md-6 card-right rounded-end-5">
                            <img src="https://i.pinimg.com/736x/6a/44/f0/6a44f0e35b10e6ed063eeebf7ed844f9.jpg"
                                alt="Ilustrasi Admin"
                                class="img-fluid mb-4 rounded-circle border border-white border-3">
                            <h3 class="fw-bold display-10 mb-3">"Manajemen yang baik adalah seni membuat masalah begitu
                                menarik dan solusinya begitu konstruktif sehingga setiap orang ingin mengerjakannya dan
                                mengatasinya."</h3>
                            <p class="lead">- Paul Hawken</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginFormAdmin = document.getElementById('loginFormAdmin');
        const usernameAdminInput = document.getElementById('usernameAdmin');
        const passwordAdminInput = document.getElementById('passwordAdmin');
        const errorUsernameAdminJs = document.getElementById('error-username-admin-js');
        const errorPasswordAdminJs = document.getElementById('error-password-admin-js');

        if (loginFormAdmin) {
            loginFormAdmin.addEventListener('submit', function(event) {
                let isValid = true;

                errorUsernameAdminJs.textContent = '';
                errorPasswordAdminJs.textContent = '';

                const existingJsErrorCard = document.querySelector('.error-card.js-validation-error');
                if (existingJsErrorCard) {
                    existingJsErrorCard.remove();
                }

                if (usernameAdminInput.value.trim() === '') {
                    errorUsernameAdminJs.textContent = 'Username tidak boleh kosong.';
                    isValid = false;
                }

                if (passwordAdminInput.value.trim() === '') {
                    errorPasswordAdminJs.textContent = 'Password tidak boleh kosong.';
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
        }
    });
    </script>
</body>

</html>
<?php mysqli_close($koneksi); ?>