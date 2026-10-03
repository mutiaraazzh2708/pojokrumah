<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe Pojok Rumah</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top glass-effect">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>index.php">
                <i class="bi bi-cup-hot-fill me-2"></i>Cafe Pojok Rumah
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>index.php">Beranda</a>
                    </li>

                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/index.php">Dashboard</a>
                        </li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>admin/products.php">Menu</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/orders.php">Pesanan</a>
                        </li>
                    <?php elseif (isLoggedIn()): ?>
                        <!-- Customer Links -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>user/orders.php">Riwayat</a>
                        </li>
                        <!-- Cart moved to floating button -->
                    <?php endif; ?>

                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item ms-lg-3">
                            <a href="<?= BASE_URL ?>logout.php"
                                class="btn btn-outline-danger btn-sm px-4 rounded-pill">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a href="<?= BASE_URL ?>login.php" class="btn btn-primary btn-sm px-4 rounded-pill">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content">