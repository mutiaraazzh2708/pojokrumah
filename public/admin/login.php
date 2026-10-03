<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

checkGuest(); // Redirect if already logged in

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Silakan isi semua kolom.";
    } else {
        // Query from ADMINS table
        $stmt = $conn->prepare("SELECT id, name, password, role FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Login Success as Admin
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role']; // Fetch role from DB

                header("Location: index.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Email tidak ditemukan atau bukan akun Admin.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Cafe Pojok Rumah</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?= time() ?>">

    <style>
        .auth-image-side {
            background-image: url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?q=80&w=2070&auto=format&fit=crop') !important;
            background-size: cover !important;
            background-position: center !important;
        }

        .auth-image-overlay {
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.7)) !important;
        }
    </style>
</head>

<body>

    <div class="auth-bg">
        <div class="auth-overlay"></div>

        <div class="auth-card animated fadeIn">
            <!-- Left Side: Image / Admin Branding -->
            <div class="auth-image-side">
                <div class="auth-image-overlay"></div>
                <div class="position-relative z-2">
                    <h2 class="display-4 fw-bold mb-3">Admin Portal</h2>
                    <p class="fs-5 opacity-75 mb-0">Kelola pesanan, produk, dan laporan Cafe Pojok Rumah dari sini.</p>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="auth-form-side">
                <div class="text-center mb-5">
                    <a href="<?= BASE_URL ?>index.php" class="text-decoration-none">
                        <h3 class="fw-bold text-dark mb-1">
                            <i class="bi bi-shield-lock-fill text-primary me-2"></i>Admin Login
                        </h3>
                    </a>
                    <p class="text-muted small">Masuk sebagai Administrator</p>
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
                        <label for="email">Email Admin</label>
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
                            MASUK ADMIN <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>

                    <div class="text-center mt-4">
                        <a href="<?= BASE_URL ?>login.php"
                            class="text-muted small text-decoration-none border-bottom border-secondary pb-1">Bukan
                            Admin? Masuk sebagai Customer</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>