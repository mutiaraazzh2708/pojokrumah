<?php
// Determine current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Count pending orders for notification
$pending_check = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$pending_notif = $pending_check ? $pending_check->fetch_assoc()['total'] : 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cafe Pojok Rumah</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>

    <div class="admin-wrapper">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Modern Sidebar -->
        <nav class="sidebar d-flex flex-column justify-content-between" id="adminSidebar">
            <div>
                <div class="sidebar-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white bg-opacity-25 rounded-3 p-2 d-flex align-items-center justify-content-center"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-cup-hot-fill text-white fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold text-white tracking-wide">Cafe Pojok</h5>
                                <small class="text-white-50" style="font-size: 0.75rem;">Admin Panel v2.0</small>
                            </div>
                        </div>
                        <!-- Close for mobile -->
                        <button class="btn btn-link text-white d-lg-none p-0" id="closeSidebar">
                            <i class="bi bi-x-lg fs-4"></i>
                        </button>
                    </div>
                </div>

                <div class="sidebar-content mt-4">
                    <p class="text-uppercase text-white-50 fw-bold mb-3 px-3"
                        style="font-size: 0.7rem; letter-spacing: 1px;">Main Menu</p>
                    <ul class="nav flex-column gap-2">
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="index.php">
                                <i class="bi bi-grid-1x2-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= (in_array($current_page, ['products.php', 'product_add.php', 'product_edit.php'])) ? 'active' : '' ?>"
                                    href="products.php">
                                    <i class="bi bi-cup-straw"></i>
                                    <span>Kelola Menu</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page == 'orders.php') ? 'active' : '' ?> d-flex justify-content-between align-items-center"
                                href="orders.php">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-receipt-cutoff"></i>
                                    <span>Pesanan</span>
                                </div>
                                <?php if ($pending_notif > 0): ?>
                                    <span
                                        class="badge bg-danger rounded-pill shadow-sm animate__animated animate__pulse animate__infinite"
                                        style="font-size: 0.7rem;"><?= $pending_notif ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page == 'reports.php') ? 'active' : '' ?>"
                                href="reports.php">
                                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                                <span>Laporan</span>
                            </a>
                        </li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($current_page == 'users.php') ? 'active' : '' ?>" href="users.php">
                                    <i class="bi bi-people-fill"></i>
                                    <span>Kelola Admin</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- User Profile Bottom -->
            <div class="p-3">
                <div
                    class="bg-white bg-opacity-10 rounded-4 p-3 d-flex align-items-center justify-content-between border border-white border-opacity-10 backdrop-blur">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                            style="width: 35px; height: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            A
                        </div>
                        <div class="lh-1">
                            <div class="fw-bold text-white fs-6">
                                <?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrator') ?>
                            </div>
                            <small class="text-white-50" style="font-size: 0.7rem;">
                                <?= (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') ? 'Superadmin' : 'Admin Staff' ?>
                            </small>
                        </div>
                    </div>
                    <a href="../logout.php"
                        class="btn btn-icon btn-sm text-danger bg-white bg-opacity-10 hover-bg-white" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="admin-content">
            <!-- Mobile Toggle -->
            <div class="bg-white p-3 mb-4 d-lg-none rounded-3 shadow-sm d-flex align-items-center gap-3">
                <button class="btn btn-dark" type="button" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h6 class="mb-0 fw-bold">Admin Panel</h6>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const sidebar = document.getElementById('adminSidebar');
                    const overlay = document.getElementById('sidebarOverlay');
                    const toggleBtn = document.getElementById('toggleSidebar');
                    const closeBtn = document.getElementById('closeSidebar');

                    function toggleSidebar() {
                        sidebar.classList.toggle('show');
                        overlay.classList.toggle('show');
                    }

                    if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
                    if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
                    if (overlay) overlay.addEventListener('click', toggleSidebar);
                });
            </script>