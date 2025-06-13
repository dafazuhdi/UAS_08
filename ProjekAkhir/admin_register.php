<?php
// Pastikan session_start() ada di bagian paling atas jika nanti ada penggunaan sesi di halaman ini
// session_start();
include 'config/koneksi.php'; // Sesuaikan path jika berbeda

$pesan_sukses = "";
$pesan_error = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']); // MENAMBAHKAN: Ambil nilai email dari form
    $role = 'admin'; // Role otomatis admin untuk halaman ini

    // MENAMBAHKAN: Validasi email tidak boleh kosong
    if (empty($username) || empty($password) || empty($nama_lengkap) || empty($email)) {
        $pesan_error = "Semua kolom (Username, Nama Lengkap, Email, Password) wajib diisi.";
    } else {
        // Cek apakah username sudah ada
        $stmt_check_username = mysqli_prepare($koneksi, "SELECT id_akun FROM akun WHERE username = ?");
        mysqli_stmt_bind_param($stmt_check_username, "s", $username);
        mysqli_stmt_execute($stmt_check_username);
        mysqli_stmt_store_result($stmt_check_username);

        if (mysqli_stmt_num_rows($stmt_check_username) > 0) {
            $pesan_error = "Username sudah terdaftar. Gunakan username lain.";
            mysqli_stmt_close($stmt_check_username);
        } else {
            mysqli_stmt_close($stmt_check_username); // Tutup statement setelah cek username

            // MENAMBAHKAN: Cek apakah email sudah ada (karena error menunjukkan UNIQUE key 'email')
            $stmt_check_email = mysqli_prepare($koneksi, "SELECT id_akun FROM akun WHERE email = ?");
            mysqli_stmt_bind_param($stmt_check_email, "s", $email);
            mysqli_stmt_execute($stmt_check_email);
            mysqli_stmt_store_result($stmt_check_email);

            if (mysqli_stmt_num_rows($stmt_check_email) > 0) {
                $pesan_error = "Email sudah terdaftar. Gunakan email lain.";
                mysqli_stmt_close($stmt_check_email);
            } else {
                mysqli_stmt_close($stmt_check_email); // Tutup statement setelah cek email

                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Masukkan data ke database (MENAMBAHKAN 'email' di query dan bind_param)
                $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO akun (username, password, nama_lengkap, email, role) VALUES (?, ?, ?, ?, ?)");
                // 'sssss' karena ada 5 string parameter (username, password, nama_lengkap, email, role)
                mysqli_stmt_bind_param($stmt_insert, "sssss", $username, $hashed_password, $nama_lengkap, $email, $role);

                if (mysqli_stmt_execute($stmt_insert)) {
                    $pesan_sukses = "Registrasi admin berhasil! Anda sekarang bisa login.";
                    // Jika Anda ingin redirect otomatis setelah sukses, uncomment baris ini:
                    // header("refresh:3;url=admin_login.php");
                    // exit();
                } else {
                    $pesan_error = "Registrasi gagal: " . mysqli_error($koneksi);
                }
                mysqli_stmt_close($stmt_insert);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
    body {
        background: linear-gradient(to right, #007bff, #28a745);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .card {
        border-radius: 1rem !important;
        overflow: hidden;
    }

    .card-left {
        padding: 3rem;
    }

    .card-right {
        background: linear-gradient(45deg, #0056b3, #007bff);
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

    .text-danger-js {
        font-size: 0.85rem;
        color: #dc3545;
        margin-top: 0rem;
        margin-left: 0.5rem;
    }
    </style>
</head>

<body>
    <?php if ($pesan_sukses || $pesan_error): ?>
    <div class="error-card">
        <div class="alert <?php echo $pesan_sukses ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show d-flex align-items-center"
            role="alert">
            <i
                class="bi <?php echo $pesan_sukses ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
            <div><?php echo htmlspecialchars($pesan_sukses ?: $pesan_error); ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card rounded-5 shadow-lg border-0">
                    <div class="row g-0">
                        <div class="col-md-12 card-left">
                            <h2 class="text-center mb-4 text-primary fw-bold">Registrasi Admin</h2>
                            <form method="POST" id="registerFormAdmin">
                                <div class="mb-1 input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control form-control-lg rounded-end" name="username"
                                        id="usernameRegAdmin" placeholder="Username">
                                </div>
                                <div id="error-username-reg-admin-js" class="text-danger-js"></div>

                                <div class="mb-1 input-group">
                                    <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                    <input type="text" class="form-control form-control-lg rounded-end"
                                        name="nama_lengkap" id="namaLengkapRegAdmin" placeholder="Nama Lengkap">
                                </div>
                                <div id="error-nama-lengkap-reg-admin-js" class="text-danger-js"></div>

                                <div class="mb-1 input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" class="form-control form-control-lg rounded-end" name="email"
                                        id="emailRegAdmin" placeholder="Email">
                                </div>
                                <div id="error-email-reg-admin-js" class="text-danger-js"></div>
                                <div class="mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control form-control-lg rounded-end"
                                            name="password" id="passwordRegAdmin" placeholder="Password">
                                    </div>
                                    <div id="error-password-reg-admin-js" class="text-danger-js"></div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" name="register"
                                        class="btn btn-primary btn-lg rounded-pill btn-hover-effect">
                                        <i class="bi bi-person-plus-fill me-2"></i> Register Admin
                                    </button>
                                </div>
                                <p class="text-center mt-3">Sudah punya akun?
                                    <a href="admin_login.php" class="text-decoration-none fw-bold text-primary">Login di
                                        sini</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const registerFormAdmin = document.getElementById('registerFormAdmin');
        const usernameRegAdminInput = document.getElementById('usernameRegAdmin');
        const namaLengkapRegAdminInput = document.getElementById('namaLengkapRegAdmin');
        const emailRegAdminInput = document.getElementById('emailRegAdmin'); // Pastikan ID ini benar
        const passwordRegAdminInput = document.getElementById('passwordRegAdmin');

        const errorUsernameRegAdminJs = document.getElementById('error-username-reg-admin-js');
        const errorNamaLengkapRegAdminJs = document.getElementById('error-nama-lengkap-reg-admin-js');
        const errorEmailRegAdminJs = document.getElementById(
            'error-email-reg-admin-js'); // Pastikan ID ini benar
        const errorPasswordRegAdminJs = document.getElementById('error-password-reg-admin-js');

        if (registerFormAdmin) {
            registerFormAdmin.addEventListener('submit', function(event) {
                let isValid = true;

                // Reset error messages
                errorUsernameRegAdminJs.textContent = '';
                errorNamaLengkapRegAdminJs.textContent = '';
                errorEmailRegAdminJs.textContent = '';
                errorPasswordRegAdminJs.textContent = '';

                const existingJsErrorCard = document.querySelector('.error-card.js-validation-error');
                if (existingJsErrorCard) {
                    existingJsErrorCard.remove();
                }

                // Validate each field
                if (usernameRegAdminInput.value.trim() === '') {
                    errorUsernameRegAdminJs.textContent = 'Username tidak boleh kosong.';
                    isValid = false;
                }

                if (namaLengkapRegAdminInput.value.trim() === '') {
                    errorNamaLengkapRegAdminJs.textContent = 'Nama Lengkap tidak boleh kosong.';
                    isValid = false;
                }

                // Validasi Email
                if (emailRegAdminInput.value.trim() === '') {
                    errorEmailRegAdminJs.textContent = 'Email tidak boleh kosong.';
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailRegAdminInput.value.trim())) {
                    // Validasi format email sederhana
                    errorEmailRegAdminJs.textContent = 'Format email tidak valid.';
                    isValid = false;
                }

                if (passwordRegAdminInput.value.trim() === '') {
                    errorPasswordRegAdminJs.textContent = 'Password tidak boleh kosong.';
                    isValid = false;
                }

                if (!isValid) {
                    event.preventDefault(); // Prevent form submission

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

                    // Auto-close alert after 5 seconds
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