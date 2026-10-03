<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

checkSuperAdmin();

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Filter Category
$cat_filter = isset($_GET['category']) ? $_GET['category'] : '';
$where_clause = "";
if (!empty($cat_filter)) {
    $where_clause = "WHERE category = '$cat_filter'";
}

// Fetch products
$sql = "SELECT * FROM products $where_clause ORDER BY id DESC LIMIT $start, $limit";
$result = $conn->query($sql);

// Count Stats
$total_products = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$total_available = $conn->query("SELECT COUNT(*) as c FROM products WHERE is_available = 1")->fetch_assoc()['c'];
$total_out = $conn->query("SELECT COUNT(*) as c FROM products WHERE is_available = 0")->fetch_assoc()['c'];

// Total pages calculation
$count_query = "SELECT COUNT(*) as count FROM products $where_clause";
$total_result = $conn->query($count_query)->fetch_assoc();
$total_pages = ceil($total_result['count'] / $limit);
?>

<?php require_once '../../includes/admin_header.php'; ?>

<!-- Animation & Styles -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
        border-radius: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(31, 38, 135, 0.1);
    }

    .stat-card-gradient-1 {
        background: linear-gradient(135deg, #FF6B6B 0%, #EE5253 100%);
    }

    .stat-card-gradient-2 {
        background: linear-gradient(135deg, #10ac84 0%, #1dd1a1 100%);
    }

    .stat-card-gradient-3 {
        background: linear-gradient(135deg, #5f27cd 0%, #341f97 100%);
    }

    .icon-bg-large {
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 8rem;
        opacity: 0.1;
        transform: rotate(-15deg);
    }

    .table-modern thead th {
        border-top: none;
        border-bottom: 2px solid #f3f4f6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #6b7280;
    }

    .table-modern td {
        border-bottom: 1px solid #f3f4f6;
        padding: 1rem 0.5rem;
        vertical-align: middle;
    }

    .nav-pills .nav-link {
        border-radius: 50rem;
        padding: 0.6rem 1.7rem;
        font-weight: 700;
        /* Thicker font */
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: #1f2937 !important;
        /* Very dark grey/black for maximum visibility */
        background: #ffffff;
        border: 2px solid #e5e7eb;
        /* Thicker border */
        margin-right: 0.75rem;
        display: flex;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .nav-pills .nav-link i {
        color: var(--primary-color);
        /* Icons in primary color */
        margin-right: 8px;
    }

    .nav-pills .nav-link:hover {
        background: #f3f4f6;
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }

    .nav-pills .nav-link.active {
        background: var(--primary-color) !important;
        color: white !important;
        border-color: var(--primary-color) !important;
        box-shadow: 0 4px 15px rgba(212, 163, 115, 0.4);
    }

    .nav-pills .nav-link.active i {
        color: white;
    }

    .delay-100 {
        animation-delay: 0.1s;
    }

    .delay-200 {
        animation-delay: 0.2s;
    }

    .delay-300 {
        animation-delay: 0.3s;
    }

    .delay-400 {
        animation-delay: 0.4s;
    }
</style>

<!-- Header Section -->
<div class="d-flex justify-content-between align-items-end mb-5 animate__animated animate__fadeInDown animate__fast">
    <div>
        <h6 class="text-uppercase text-muted fw-bold small mb-1">Manajemen Produk</h6>
        <h2 class="fw-bold text-dark display-6">Daftar Menu Kafe</h2>
        <p class="text-muted mb-0">Kelola ketersediaan, harga, dan detail menu Anda dengan mudah.</p>
    </div>
    <div>
        <a href="product_add.php"
            class="btn btn-primary rounded-pill px-4 py-2 shadow-sm d-flex align-items-center hover-scale">
            <i class="bi bi-plus-circle-fill me-2 fs-5"></i> Tambah Menu
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-4 animate__animated animate__fadeInUp animate__fast delay-100">
        <div class="card border-0 h-100 text-white overflow-hidden rounded-4 stat-card-gradient-3 shadow-lg">
            <div class="card-body p-4 position-relative">
                <h6 class="text-uppercase opacity-75 fw-bold small mb-2">Total Menu</h6>
                <h2 class="display-4 fw-bold mb-0"><?= $total_products ?></h2>
                <div class="mt-4 d-flex align-items-center opacity-75">
                    <i class="bi bi-grid me-2"></i> Semua Kategori
                </div>
                <i class="bi bi-grid-fill icon-bg-large"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 animate__animated animate__fadeInUp animate__fast delay-200">
        <div class="card border-0 h-100 text-white overflow-hidden rounded-4 stat-card-gradient-2 shadow-lg">
            <div class="card-body p-4 position-relative">
                <h6 class="text-uppercase opacity-75 fw-bold small mb-2">Menu Tersedia</h6>
                <h2 class="display-4 fw-bold mb-0"><?= $total_available ?></h2>
                <div class="mt-4 d-flex align-items-center opacity-75">
                    <i class="bi bi-check-circle me-2"></i> Siap Dipesan
                </div>
                <i class="bi bi-check-circle-fill icon-bg-large"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 animate__animated animate__fadeInUp animate__fast delay-300">
        <div class="card border-0 h-100 text-white overflow-hidden rounded-4 stat-card-gradient-1 shadow-lg">
            <div class="card-body p-4 position-relative">
                <h6 class="text-uppercase opacity-75 fw-bold small mb-2">Stok Habis / Non-Aktif</h6>
                <h2 class="display-4 fw-bold mb-0"><?= $total_out ?></h2>
                <div class="mt-4 d-flex align-items-center opacity-75">
                    <i class="bi bi-x-circle me-2"></i> Perlu Tindakan
                </div>
                <i class="bi bi-slash-circle-fill icon-bg-large"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Card -->
<div class="glass-card mb-5 animate__animated animate__fadeInUp animate__fast delay-400">
    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-center p-4 border-bottom border-light">
        <!-- Filter Tabs -->
        <ul class="nav nav-pills mb-3 mb-md-0">
            <li class="nav-item">
                <a class="nav-link <?= empty($cat_filter) ? 'active' : '' ?>" href="products.php">
                    <i class="bi bi-grid me-1"></i> Semua
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($cat_filter == 'food') ? 'active' : '' ?>" href="products.php?category=food">
                    <i class="bi bi-egg-fried me-1"></i> Makanan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($cat_filter == 'drink') ? 'active' : '' ?>" href="products.php?category=drink">
                    <i class="bi bi-cup-hot me-1"></i> Minuman
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($cat_filter == 'snack') ? 'active' : '' ?>" href="products.php?category=snack">
                    <i class="bi bi-cookie me-1"></i> Camilan
                </a>
            </li>
        </ul>

        <div class="input-group" style="max-width: 250px;">
            <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control border-0 bg-light" placeholder="Cari menu...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-modern align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Detail Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th class="pe-4 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover-bg-light transition-base">
                            <td class="ps-4">
                                <div class="d-flex align-items-center py-2">
                                    <div class="position-relative">
                                        <?php if ($row['image']): ?>
                                            <img src="<?= BASE_URL . 'assets/uploads/' . $row['image'] ?>" alt=""
                                                class="rounded-3 shadow-sm" width="60" height="60" style="object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-secondary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center text-secondary"
                                                style="width: 60px; height: 60px;">
                                                <i class="bi bi-image fs-4"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-3">
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($row['name']) ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars($row['description']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $badges = [
                                    'food' => ['color' => '#f59e0b', 'icon' => 'bi-basket'],
                                    'drink' => ['color' => '#3b82f6', 'icon' => 'bi-cup-straw'],
                                    'snack' => ['color' => '#ec4899', 'icon' => 'bi-cookie'],
                                ];
                                $b = $badges[$row['category']] ?? ['color' => '#6b7280', 'icon' => 'bi-tag'];
                                ?>
                                <span class="badge border bg-white text-dark rounded-pill px-3 py-2 fw-normal"
                                    style="border-color: <?= $b['color'] ?> !important; color: <?= $b['color'] ?> !important;">
                                    <i class="bi <?= $b['icon'] ?> me-1"></i> <?= ucfirst($row['category']) ?>
                                </span>
                            </td>
                            <td class="fw-bold text-dark fs-6 font-monospace">
                                Rp <?= number_format($row['price'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" <?= $row['is_available'] ? 'checked' : '' ?> onclick="return false;">
                                    <label
                                        class="form-check-label small <?= $row['is_available'] ? 'text-success fw-bold' : 'text-danger' ?>">
                                        <?= $row['is_available'] ? 'Aktif' : 'Non-Aktif' ?>
                                    </label>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <a href="product_edit.php?id=<?= $row['id'] ?>"
                                    class="btn btn-sm btn-outline-primary rounded-circle shadow-sm mx-1"
                                    data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="product_delete.php?id=<?= $row['id'] ?>"
                                    class="btn btn-sm btn-outline-danger rounded-circle shadow-sm mx-1"
                                    onclick="return confirm('Yakin hapus menu ini?');" data-bs-toggle="tooltip" title="Hapus">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-light p-4 rounded-circle mb-3">
                                    <i class="bi bi-search fs-1 text-muted opacity-50"></i>
                                </div>
                                <h5 class="text-muted fw-bold">Belum ada menu</h5>
                                <p class="text-muted small">Silakan tambah menu baru untuk memulai.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center p-4 border-top border-light">
            <small class="text-muted">Menampilkan <?= $result->num_rows ?> dari <?= $total_result['count'] ?> data</small>
            <nav>
                <ul class="pagination mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link border-0 text-dark rounded-circle mx-1"
                            href="?page=<?= $page - 1 ?>&category=<?= $cat_filter ?>"><i class="bi bi-chevron-left"></i></a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link border-0 rounded-circle mx-1 <?= ($page == $i) ? 'bg-primary text-white shadow-sm' : 'text-dark' ?>"
                                href="?page=<?= $i ?>&category=<?= $cat_filter ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link border-0 text-dark rounded-circle mx-1"
                            href="?page=<?= $page + 1 ?>&category=<?= $cat_filter ?>"><i
                                class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/admin_footer.php'; ?>