<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

checkAdmin();

// Proactive migration: ensure admin_note exists for rejection messages
$check_col = $conn->query("SHOW COLUMNS FROM orders LIKE 'admin_note'");
if ($check_col->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN admin_note TEXT NULL AFTER note");
}

// Handle Delete Order (DISABLED for Superadmin view-only)
/*
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_order') {
    // ... logic removed for view-only ...
}
*/

// Update status handler
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    // Superadmin is VIEW-ONLY
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') {
        header("Location: orders.php?msg=readonly");
        exit;
    }

    $oid = $_POST['order_id'];
    $status = $_POST['status'];
    $admin_note = isset($_POST['admin_note']) ? $_POST['admin_note'] : null;

    if ($admin_note) {
        $stmt = $conn->prepare("UPDATE orders SET status = ?, admin_note = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $admin_note, $oid);
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $oid);
    }
    $stmt->execute();

    // Refresh to prevent resubmission
    header("Location: orders.php?status=" . (isset($_GET['status']) ? $_GET['status'] : 'all'));
    exit;
}

// Filter Status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

$where_conditions = [];
if ($status_filter != 'all') {
    $where_conditions[] = "o.status = '$status_filter'";
}
if (!empty($search_query)) {
    $search = $conn->real_escape_string($search_query);
    $where_conditions[] = "(o.id LIKE '%$search%' OR u.name LIKE '%$search%')";
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Fetch orders
$sql = "SELECT o.*, COALESCE(u.name, 'Admin/Staff') as user_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        $where_clause 
        ORDER BY o.id DESC 
        LIMIT $start, $limit";
$result = $conn->query($sql);

// Count per status (stat cards)
$count_pending = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'")->fetch_assoc()['c'];
$count_process = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'processing'")->fetch_assoc()['c'];
$count_completed = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'completed'")->fetch_assoc()['c'];
$count_cancelled = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'cancelled'")->fetch_assoc()['c'];

// Total pages
$count_sql = "SELECT COUNT(*) as c FROM orders o LEFT JOIN users u ON o.user_id = u.id $where_clause";
$total_rows = $conn->query($count_sql)->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $limit);
?>

<?php require_once '../../includes/admin_header.php'; ?>

<!-- Animate CSS -->
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

    .stat-card-warning {
        background: linear-gradient(135deg, #fceb0b 0%, #f59e0b 100%);
    }

    .stat-card-info {
        background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
    }

    .stat-card-success {
        background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
    }

    .stat-card-danger {
        background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
    }

    .icon-bg-large {
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 6rem;
        opacity: 0.15;
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

<!-- Header -->
<div class="d-flex justify-content-between align-items-end mb-5 animate__animated animate__fadeInDown animate__fast">
    <div>
        <h6 class="text-uppercase text-muted fw-bold small mb-1">Manajemen Pesanan</h6>
        <h2 class="fw-bold text-dark display-6">Pesanan Masuk</h2>
        <p class="text-muted mb-0">Kelola dan proses pesanan yang masuk secara real-time.</p>
    </div>
    <div class="d-none d-md-block">
        <button class="btn btn-outline-dark rounded-pill px-4" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
        </button>
    </div>
</div>

<!-- Status Stats -->
<div class="row g-3 mb-5">
    <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__fast delay-100">
        <a href="orders.php?status=pending" class="text-decoration-none">
            <div
                class="card border-0 h-100 text-white overflow-hidden rounded-4 stat-card-warning shadow-sm hover-scale">
                <div class="card-body p-3 position-relative">
                    <h5 class="fw-bold mb-0 text-white text-shadow"><?= $count_pending ?></h5>
                    <small class="text-white-50 font-monospace text-uppercase fw-bold">Pending</small>
                    <i class="bi bi-hourglass-split icon-bg-large"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__fast delay-200">
        <a href="orders.php?status=processing" class="text-decoration-none">
            <div class="card border-0 h-100 text-white overflow-hidden rounded-4 stat-card-info shadow-sm hover-scale">
                <div class="card-body p-3 position-relative">
                    <h5 class="fw-bold mb-0 text-white text-shadow"><?= $count_process ?></h5>
                    <small class="text-white-50 font-monospace text-uppercase fw-bold">Processing</small>
                    <i class="bi bi-fire icon-bg-large"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__fast delay-300">
        <a href="orders.php?status=completed" class="text-decoration-none">
            <div
                class="card border-0 h-100 text-white overflow-hidden rounded-4 stat-card-success shadow-sm hover-scale">
                <div class="card-body p-3 position-relative">
                    <h5 class="fw-bold mb-0 text-white text-shadow"><?= $count_completed ?></h5>
                    <small class="text-white-50 font-monospace text-uppercase fw-bold">Completed</small>
                    <i class="bi bi-check-circle-fill icon-bg-large"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__fast delay-400">
        <a href="orders.php?status=cancelled" class="text-decoration-none">
            <div
                class="card border-0 h-100 text-white overflow-hidden rounded-4 stat-card-danger shadow-sm hover-scale">
                <div class="card-body p-3 position-relative">
                    <h5 class="fw-bold mb-0 text-white text-shadow"><?= $count_cancelled ?></h5>
                    <small class="text-white-50 font-monospace text-uppercase fw-bold">Cancelled</small>
                    <i class="bi bi-x-circle-fill icon-bg-large"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Main Card -->
<div class="glass-card mb-5 animate__animated animate__fadeInUp animate__fast delay-400">
    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-center p-4 border-bottom border-light">
        <!-- Replaced Tabs with Title -->
        <h5 class="fw-bold fs-5 text-dark mb-3 mb-md-0"><i class="bi bi-list-check me-2 text-secondary"></i>Daftar
            Riwayat Pesanan</h5>

        <!-- Functional Search Form -->
        <form method="GET" action="orders.php" class="input-group" style="max-width: 300px;">
            <?php if ($status_filter != 'all'): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            <?php endif; ?>
            <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Cari Order ID/Nama..."
                value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" class="btn btn-primary rounded-end"><i class="bi bi-arrow-right"></i></button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-modern align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">No. Order</th>
                    <th>Pelanggan</th>
                    <th>Menu Pesanan</th>
                    <th>Pembayaran</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="pe-4 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <tr class="hover-bg-light transition-base">
                            <td class="ps-4 fw-bold text-primary font-monospace">
                                #<?= $order['id'] ?>
                                <div class="small text-muted fw-normal mt-1">
                                    <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($order['created_at'])) ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle text-primary d-flex align-items-center justify-content-center me-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($order['user_name']) ?></div>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            <span class="badge bg-light text-dark border">
                                                Meja <?= $order['table_number'] ?>
                                            </span>
                                            <span
                                                class="badge <?= $order['order_type'] === 'takeaway' ? 'bg-info bg-opacity-10 text-info border-info' : 'bg-primary bg-opacity-10 text-primary border-primary' ?> border">
                                                <?= $order['order_type'] === 'takeaway' ? 'Take Away' : 'Makan di Tempat' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="max-width: 300px;">
                                <?php if (!empty($order['note'])): ?>
                                    <span
                                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 mb-1">
                                        Note: <?= htmlspecialchars($order['note']) ?>
                                    </span>
                                <?php endif; ?>
                                <div class="small text-truncate text-secondary">
                                    <?= htmlspecialchars($order['items']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2 align-items-start">
                                    <span
                                        class="badge rounded-pill border px-3 py-2 <?= ($order['payment_method'] == 'qris') ? 'bg-primary text-primary bg-opacity-10 border-primary border-opacity-25' : 'bg-success text-success bg-opacity-10 border-success border-opacity-25' ?>">
                                        <?php if ($order['payment_method'] == 'qris'): ?>
                                            <i class="bi bi-qr-code me-1"></i> QRIS
                                        <?php else: ?>
                                            <i class="bi bi-cash me-1"></i> Tunai
                                        <?php endif; ?>
                                    </span>
                                    <?php if (!empty($order['payment_proof'])): ?>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalOrder<?= $order['id'] ?>"
                                            class="badge bg-info text-info bg-opacity-10 border border-info border-opacity-25 text-decoration-none">
                                            <i class="bi bi-image me-1"></i> Lihat Bukti
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="fw-bold text-dark fs-6">
                                Rp <?= number_format($order['total_amount'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'superadmin'): ?>
                                        <form method="POST" class="d-flex align-items-center gap-2">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <?php if ($order['status'] == 'pending'): ?>
                                                <input type="hidden" name="admin_note" value="">
                                                <button type="submit" name="status" value="processing"
                                                    class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm hover-lift">
                                                    <i class="bi bi-play-fill me-1"></i> Proses
                                                </button>
                                                <button type="submit" name="status" value="cancelled"
                                                    onclick="this.form.admin_note.value = '<?= $order['payment_method'] === 'qris' ? 'Maaf, Makanan Tidak Tersedia, Silahkan Konfirmasi Pengembalian Dana Ke Kasir' : 'Maaf, Pesanan Tidak Tersedia' ?>'; return confirm('Yakin ingin menolak pesanan ini?')"
                                                    class="btn btn-outline-danger btn-sm rounded-circle" title="Tolak">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            <?php elseif ($order['status'] == 'processing'): ?>
                                                <button type="submit" name="status" value="completed"
                                                    class="btn btn-success btn-sm rounded-pill px-3 shadow-sm hover-lift">
                                                    <i class="bi bi-check-lg me-1"></i> Selesai
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (in_array($order['status'], ['completed', 'cancelled']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin')): ?>
                                        <?php
                                        $badge_class = match ($order['status']) {
                                            'completed' => 'bg-success text-success bg-opacity-10 border-success border-opacity-25',
                                            'cancelled' => 'bg-danger text-danger bg-opacity-10 border-danger border-opacity-25',
                                            'pending' => 'bg-warning text-warning bg-opacity-10 border-warning border-opacity-25',
                                            'processing' => 'bg-primary text-primary bg-opacity-10 border-primary border-opacity-25',
                                            default => 'bg-secondary text-secondary bg-opacity-10'
                                        };
                                        ?>
                                        <span class="badge border <?= $badge_class ?> rounded-pill px-3">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button class="btn btn-sm btn-light text-primary rounded-circle shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalOrder<?= $order['id'] ?>"
                                        title="Lihat Detail">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    <a href="print_receipt.php?id=<?= $order['id'] ?>" target="_blank"
                                        class="btn btn-sm btn-success text-white rounded-circle shadow-sm" title="Cetak Struk">
                                        <i class="bi bi-printer-fill"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-light p-4 rounded-circle mb-3">
                                    <i class="bi bi-inbox fs-1 text-muted opacity-50"></i>
                                </div>
                                <h5 class="text-muted fw-bold">Belum ada pesanan</h5>
                                <p class="text-muted small">Pesanan baru akan muncul di sini.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center p-4 border-top border-light">
            <small class="text-muted">Halaman <?= $page ?> dari <?= $total_pages ?></small>
            <nav>
                <ul class="pagination mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link border-0 text-dark rounded-circle mx-1"
                            href="?page=<?= $page - 1 ?>&status=<?= $status_filter ?>"><i
                                class="bi bi-chevron-left"></i></a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link border-0 rounded-circle mx-1 <?= ($page == $i) ? 'bg-primary text-white shadow-sm' : 'text-dark' ?>"
                                href="?page=<?= $i ?>&status=<?= $status_filter ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link border-0 text-dark rounded-circle mx-1"
                            href="?page=<?= $page + 1 ?>&status=<?= $status_filter ?>"><i
                                class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Modals (Moved Outside to fix Stacking Context) -->
<?php
if ($result->num_rows > 0):
    $result->data_seek(0); // Reset pointer
    while ($order = $result->fetch_assoc()):
        ?>
        <div class="modal fade" id="modalOrder<?= $order['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">Detail Pesanan #<?= $order['id'] ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="p-4 bg-light text-center border-bottom">
                            <h3 class="fw-bold text-primary mb-0">Rp
                                <?= number_format($order['total_amount'], 0, ',', '.') ?>
                            </h3>
                            <div class="mt-2">
                                <span
                                    class="badge rounded-pill border px-3 py-1 <?= ($order['payment_method'] == 'qris') ? 'bg-primary text-primary bg-opacity-10 border-primary border-opacity-25' : 'bg-success text-success bg-opacity-10 border-success border-opacity-25' ?>">
                                    <?= ($order['payment_method'] == 'qris') ? 'QRIS' : 'TUNAI' ?>
                                </span>
                            </div>
                            <small
                                class="text-muted text-uppercase fw-bold mt-2 d-block"><?= date('d F Y, H:i', strtotime($order['created_at'])) ?></small>

                            <?php if (!empty($order['payment_proof'])): ?>
                                <div class="mt-3">
                                    <div class="small fw-bold text-muted mb-1">Bukti Pembayaran:</div>
                                    <img src="../assets/uploads/proofs/<?= $order['payment_proof'] ?>"
                                        class="img-thumbnail rounded shadow-sm" style="max-height: 200px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4 text-start">
                            <div class="row mb-4">
                                <div class="col-6">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Pelanggan</small>
                                    <div class="fw-bold text-dark fs-5">
                                        <?= htmlspecialchars($order['user_name']) ?>
                                    </div>
                                </div>
                                <div class="col-6 text-end">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Meja / Jenis</small>
                                    <div class="badge bg-primary">No. <?= $order['table_number'] ?></div>
                                    <div class="badge <?= $order['order_type'] === 'takeaway' ? 'bg-info' : 'bg-success' ?>">
                                        <?= $order['order_type'] === 'takeaway' ? 'TAKE AWAY' : 'DINE IN' ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($order['note'])): ?>
                                <div
                                    class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark d-flex align-items-center mb-4">
                                    <i class="bi bi-exclamation-circle-fill text-warning me-2 fs-5"></i>
                                    <div><strong>Catatan:</strong> <?= htmlspecialchars($order['note']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <h6 class="fw-bold border-bottom pb-2 mb-3 text-secondary">Rincian Menu</h6>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr class="border-bottom">
                                            <th class="text-muted small">Item</th>
                                            <th class="text-muted small text-center">Qty</th>
                                            <th class="text-muted small text-end">Harga</th>
                                            <th class="text-muted small text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Fetch order items with prices
                                        $items_sql = "SELECT oi.*, p.name 
                                                     FROM order_items oi 
                                                     LEFT JOIN products p ON oi.product_id = p.id 
                                                     WHERE oi.order_id = ?";
                                        $items_stmt = $conn->prepare($items_sql);
                                        $items_stmt->bind_param("i", $order['id']);
                                        $items_stmt->execute();
                                        $items_result = $items_stmt->get_result();

                                        while ($item = $items_result->fetch_assoc()):
                                            $item_subtotal = $item['price'] * $item['quantity'];
                                            ?>
                                            <tr>
                                                <td class="fw-medium"><?= htmlspecialchars($item['name']) ?></td>
                                                <td class="text-center">x<?= $item['quantity'] ?></td>
                                                <td class="text-end">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                                <td class="text-end fw-bold">Rp <?= number_format($item_subtotal, 0, ',', '.') ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 d-flex justify-content-between">
                        <div></div> <!-- Spacer -->

                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    endwhile;
endif;
?>

<?php require_once '../../includes/admin_footer.php'; ?>