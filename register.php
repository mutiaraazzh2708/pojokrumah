<?php
require_once './config/database.php';
require_once './includes/functions.php';

checkGuest(); // Redirect if already logged in

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Silakan isi semua kolom.";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email sudah terdaftar. Silakan login.";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // $role = 'user'; // No longer needed in db insert if table is separated or doesn't have role col

            // Assuming users table might still have role column from old schema, we can specify it if we want to be safe, 
            // OR if new schema 'users' doesn't have it. My setup script created 'users' WITHOUT role column.
            // So I should remove 'role' from INSERT.

            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan sistem. Coba lagi nanti.";
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
    <title>Daftar - Cafe Pojok Rumah</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?= time() ?>">


</head>

<body>

    <div class="auth-bg">
        <div class="auth-overlay"></div>

        <div class="auth-card animated fadeIn">
            <!-- Left Side: Image -->
            <div class="auth-image-side"
                style="background-image: url('https://images.unsplash.com/photo-1511920170033-f8396924c348?q=80&w=2574&auto=format&fit=crop');">
                <div class="auth-image-overlay"></div>
                <div class="position-relative z-2">
                    <h2 class="display-4 fw-bold mb-3">Gabung Bersama Kami</h2>
                    <p class="fs-5 opacity-75 mb-0">Jadilah bagian dari komunitas pecinta kopi. Dapatkan promo menarik
                        dan kemudahan pemesanan.</p>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="auth-form-side">
                <div class="text-center mb-4">
                    <a href="index.php" class="text-decoration-none">
                        <h3 class="fw-bold text-dark mb-1">Buat Akun Baru</h3>
                    </a>
                    <p class="text-muted small">Isi data diri Anda dengan benar</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <div>
                            <?= $success ?> <a href="login.php" class="fw-bold text-success text-decoration-underline">Login
                                disini</a>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nama Lengkap"
                            required value="<?= isset($name) ? $name : '' ?>">
                        <label for="name">Nama Lengkap</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com"
                            required value="<?= isset($email) ? $email : '' ?>">
                        <label for="email">Alamat Email</label>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <div class="password-toggle-wrapper">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Password" required>
                                    <label for="password">Password</label>
                                    <button type="button" class="btn-toggle-password" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="password-toggle-wrapper">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" placeholder="Konfirmasi" required>
                                    <label for="confirm_password">Konfirmasi</label>
                                    <button type="button" class="btn-toggle-password" onclick="togglePassword('confirm_password', this)" aria-label="Toggle confirm password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="text-center">
                        <button type="submit" class="btn btn-auth mb-4" style="width: auto; min-width: 280px; display: inline-block;">
                            DAFTAR SEKARANG
                        </button>
                    </div>


                    <div class="text-center">
                        <span class="text-muted">Sudah punya akun?</span>
                        <a href="login.php" class="text-primary fw-bold text-decoration-none ms-1">Login disini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</body>

</html>