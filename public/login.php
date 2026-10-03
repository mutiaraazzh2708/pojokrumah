<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

checkGuest(); // Redirect if already logged in

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Silakan isi semua kolom.";
    } else {
        // Query only from USERS table (Customers)
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Login Success as Customer
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = 'user'; // Explicitly set role

                header("Location: index.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            // Optional: Check if it's an admin trying to login here to give a hint
            $stmt_admin = $conn->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt_admin->bind_param("s", $email);
            $stmt_admin->execute();
            if ($stmt_admin->get_result()->num_rows > 0) {
                $error = "Akun ini terdaftar sebagai Admin. Silakan login di <a href='admin/login.php' class='alert-link'>Portal Admin</a>.";
            } else {
                $error = "Email tidak ditemukan.";
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
    <title>Login - Cafe Pojok Rumah</title>
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
            <div class="auth-image-side">
                <div class="auth-image-overlay"></div>
                <div class="position-relative z-2">
                    <h2 class="display-4 fw-bold mb-3">Selamat Datang Kembali!</h2>
                    <p class="fs-5 opacity-75 mb-0">Nikmati suasana tenang dan kopi terbaik hanya di Cafe Pojok Rumah.
                        Masuk untuk mulai memesan.</p>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="auth-form-side">
                <div class="text-center mb-5">
                    <a href="index.php" class="text-decoration-none">
                        <h3 class="fw-bold text-dark mb-1">
                            <i class="bi bi-cup-hot-fill text-primary me-2"></i>Login
                        </h3>
                    </a>
                    <p class="text-muted small">Masuk ke akun Anda</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com"
                            required>
                        <label for="email">Alamat Email</label>
                    </div>
                    <div class="password-toggle-wrapper mb-4">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                                required>
                            <label for="password">Password</label>
                            <button type="button" class="btn-toggle-password" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

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

                    <div class="text-center">
                        <button type="submit" class="btn btn-auth mb-4" style="width: auto; min-width: 280px; display: inline-block;">
                            MASUK SEKARANG <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>

                    <div class="text-center">
                        <span class="text-muted">Belum punya akun?</span>
                        <a href="register.php" class="text-primary fw-bold text-decoration-none ms-1">Daftar disini</a>
                    </div>

                    <div class="text-center mt-4">
                        <a href="index.php"
                            class="text-muted small text-decoration-none border-bottom border-secondary pb-1">Kembali ke
                            Beranda</a>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-muted small mb-2">Anda seorang Admin?</p>
                        <a href="admin/login.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-shield-lock-fill me-2"></i>Login sebagai Admin
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>