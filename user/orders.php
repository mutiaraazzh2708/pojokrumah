<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth();

$user_id = $_SESSION['user_id'];
// Fetch orders
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php require_once '../includes/header.php'; ?>

<!-- Animate.css for easy animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<style>
    /* Premium Gradient Background */
    .page-bg {
        background: radial-gradient(circle at top right, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
    }

    /* Card Styling */
    .order-card {
        border: none;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .order-card:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.1);
        background: rgba(255, 255, 255, 0.95);
    }

    /* Status Pulse Animation */
    @keyframes pulse-ring {
        0% {
            box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(2, 132, 199, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(2, 132, 199, 0);
        }
    }

    .status-pulse {
        animation: pulse-ring 2s infinite;
    }

    /* Staggered Animation Delay for cards */
    .card-enter-0 {
        animation-delay: 0.1s;
    }

    .card-enter-1 {
        animation-delay: 0.2s;
    }

    .card-enter-2 {
        animation-delay: 0.3s;
    }

    .card-enter-3 {
        animation-delay: 0.4s;
    }

    .card-enter-4 {
        animation-delay: 0.5s;
    }

    /* Custom Badges */
    .badge-modern {
        padding: 8px 16px;
        border-radius: 12px;
        font-weight: 600;
        letter-spacing: 0.3px;
        font-size: 0.85rem;
    }

    .status-completed {
        background: #dcfce7;
        color: #166534;
    }

    .status-processing {
        background: #e0f2fe;
        color: #075985;
        border: 1px solid rgba(2, 132, 199, 0.2);
    }

    .status-pending {
        background: #fef9c3;
        color: #854d0e;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .table-number-box {
        width: 60px;
        height: 60px;
        background: var(--primary-color);
        color: white;
        border-radius: 18px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 16px rgba(212, 163, 115, 0.3);
    }
</style>

<div class="page-bg pb-5">
    <!-- Header Section with FadeInDown -->
    <div class="container pt-5 pb-4 animate__animated animate__fadeInDown">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h6 class="text-uppercase text-primary fw-bold mb-2 tracking-wide">Customer Dashboard</h6>
                <h1 class="display-5 fw-bold text-dark mb-0">Riwayat Pesanan</h1>
            </div>
            <div class="d-none d-md-block">
                <a href="../index.php" class="btn btn-outline-dark rounded-pill px-4">
                    <i class="bi bi-plus-lg me-2"></i>Pesan Lagi
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php $i = 0;
                while ($order = $result->fetch_assoc()): ?>
                    <div class="col-12 animate__animated animate__fadeInUp card-enter-<?= $i++ % 5 ?>">
                        <div class="order-card p-4">
                            <div class="row align-items-center">
                                <!-- Col 1: Icon & ID -->
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($order['table_number'] > 0): ?>
                                            <!-- Dine In - Show Table Number -->
                                            <div class="table-number-box">
                                                <span class="small opacity-75" style="font-size: 0.7rem;">MEJA</span>
                                                <span class="fs-4 fw-bold lh-1"><?= $order['table_number'] ?></span>
                                            </div>
                                        <?php else: ?>
                                            <!-- Take Away - Show Custom Icon -->
                                            <div class="table-number-box" style="background: #d4a373; padding: 8px; box-shadow: none;">
                                                <img src="../assets/images/takeaway-icon.png" alt="Take Away" style="width: 44px; height: 44px; object-fit: contain;">
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <span class="text-muted small d-block">Order ID</span>
                                            <span class="fw-bold fs-5 text-dark">#<?= $order['id'] ?></span>
                                            <div class="small text-muted mt-1">
                                                <?= date('d M, H:i', strtotime($order['created_at'])) ?>
                                            </div>
                                            <?php if ($order['table_number'] == 0): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 mt-1" style="font-size: 0.7rem;">
                                                    <i class="bi bi-bag me-1"></i>Take Away
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Col 2: Items -->
                                <div class="col-md-5 mb-3 mb-md-0 ps-md-4">
                                    <h6 class="text-muted text-uppercase small fw-bold mb-2">Menu Dipesan</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php
                                        // Show pills for items
                                        $items = explode(',', $order['items']);
                                        $shown = 0;
                                        foreach ($items as $item):
                                            if ($shown < 2):
                                                ?>
                                                <span class="badge bg-light text-dark border fw-normal px-3 py-2 rounded-pill">
                                                    <?= trim($item) ?>
                                                </span>
                                                <?php
                                                $shown++;
                                            endif;
                                        endforeach;
                                        if (count($items) > 2):
                                            ?>
                                            <span class="badge bg-light text-muted border fw-normal px-2 py-2 rounded-pill">
                                                +<?= count($items) - 2 ?> lainnya
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($order['admin_note']) && $order['status'] === 'cancelled'): ?>
                                        <div
                                            class="mt-3 p-2 rounded-3 bg-danger bg-opacity-10 text-danger small border border-danger border-opacity-10 d-flex align-items-center">
                                            <i class="bi bi-info-circle-fill me-2"></i>
                                            <div>
                                                <strong>Alasan Pembatalan:</strong><br>
                                                <?= htmlspecialchars($order['admin_note']) ?>
                                            </div>
                                        </div>
                                    <?php elseif (!empty($order['note'])): ?>
                                        <div class="mt-2 text-warning small">
                                            <i class="bi bi-sticky-fill me-1"></i> <?= htmlspecialchars($order['note']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Col 3: Status & Total -->
                                <div
                                    class="col-md-4 d-flex justify-content-between justify-content-md-end align-items-center gap-4 ps-md-4">
                                    <div class="text-end">
                                        <span class="text-muted small d-block mb-1">Status</span>
                                        <?php
                                        $st_map = [
                                            'completed' => ['Selesai', 'status-completed', 'bi-check-all'],
                                            'processing' => ['Diproses', 'status-processing status-pulse', 'bi-hourglass-split'],
                                            'pending' => ['Menunggu', 'status-pending', 'bi-clock'],
                                            'cancelled' => ['Batal', 'status-cancelled', 'bi-x'],
                                        ];
                                        $st = $st_map[$order['status']] ?? $st_map['pending'];
                                        ?>
                                        <div class="badge-modern <?= $st[1] ?> d-inline-flex align-items-center">
                                            <i class="bi <?= $st[2] ?> me-2"></i> <?= $st[0] ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-muted small d-block mb-1">Pembayaran</span>
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <span class="fw-bold text-dark text-uppercase fs-6">
                                                <?php
                                                $pm = $order['payment_method'] ?? 'cash';
                                                echo ($pm == 'cash') ? 'Tunai' : (($pm == 'qris') ? 'QRIS' : $pm);
                                                ?>
                                            </span>
                                            <?php if ($order['status'] === 'pending' && empty($order['payment_proof'])): ?>
                                                <a href="change_payment.php?id=<?= $order['id'] ?>"
                                                    class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2"
                                                    style="font-size: 0.65rem;">
                                                    Ganti
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-muted small d-block mb-1">Total</span>
                                        <span class="fw-bold text-dark fs-5">
                                            Rp <?= number_format($order['total_amount'], 0, ',', '.') ?>
                                        </span>
                                    </div>

                                    <!-- Late Upload Button for QRIS -->
                                    <?php if ($order['payment_method'] === 'qris' && $order['status'] === 'pending' && empty($order['payment_proof'])): ?>
                                        <div class="text-end ms-2">
                                            <span class="d-block mb-1 opacity-0">Action</span> <!-- Spacer -->
                                            <a href="../payment.php?id=<?= $order['id'] ?>"
                                                class="btn btn-sm btn-primary rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                <i class="bi bi-upload me-1"></i> Upload
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Expandable Details (Optional, keeping it simple for now) -->
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 animate__animated animate__fadeIn">
                    <div class="mb-4">
                        <div class="d-inline-block p-5 rounded-circle bg-white shadow-sm">
                            <i class="bi bi-bag-x display-4 text-muted opacity-50"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-3">Belum ada pesanan</h3>
                    <p class="text-muted mb-4">Mulai pesan sekarang dan nikmati hidangan terbaik kami.</p>
                    <a href="../index.php" class="btn btn-primary rounded-pill px-5 py-3 shadow fw-bold transition-hover">
                        Lihat Menu
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>