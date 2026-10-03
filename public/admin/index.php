<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

checkAdmin();

// --- 1. Key Metrics ---
$stats = [];
$stats['products'] = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$stats['orders'] = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$stats['pending'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$today = date('Y-m-d');
$stats['income'] = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = '$today' AND status != 'cancelled'")->fetch_assoc()['total'] ?? 0;

// --- 2. Recent Transactions ---
$recent_sales = $conn->query("SELECT o.*, COALESCE(u.name, 'Admin/Staff') as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.status = 'completed' ORDER BY o.created_at DESC LIMIT 5");

// --- 3. Chart Data: Income Trend (Last 7 Days) ---
$trend_labels = [];
$trend_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $trend_labels[] = date('D, d M', strtotime($date));

    $res = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = '$date' AND status = 'completed'");
    $trend_data[] = $res->fetch_assoc()['total'] ?? 0;
}

// --- 4. Chart Data: Order Status Distribution ---
$status_counts = [];
$statuses = ['completed', 'cancelled', 'processing', 'pending'];
foreach ($statuses as $s) {
    $res = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = '$s'");
    $status_counts[] = $res->fetch_assoc()['count'];
}
?>

<?php require_once '../../includes/admin_header.php'; ?>

<!-- Animation Library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card-gradient-2 {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stat-card-gradient-3 {
        background: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%);
    }

    .stat-card-gradient-4 {
        background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
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

    .delay-500 {
        animation-delay: 0.5s;
    }
</style>

<!-- Welcome Header -->
<div class="d-flex justify-content-between align-items-end mb-5 animate__animated animate__fadeInDown animate__fast">
    <div>
        <h6 class="text-uppercase text-muted fw-bold small mb-1">Overview</h6>
        <h2 class="fw-bold text-dark display-6">Selamat Datang, Admin! 👋</h2>
        <p class="text-muted mb-0">Berikut adalah ringkasan performa kafe Anda hari ini.</p>
    </div>
    <div class="d-none d-md-block text-end">
        <p class="mb-0 fw-bold fs-5 text-dark" id="digital-clock"><?= date('H:i') ?></p>
        <p class="small text-muted mb-0"><?= date('l, d F Y') ?></p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-5">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
        <div class="col-md-3 animate__animated animate__fadeInUp animate__fast delay-100">
            <div class="card border-0 h-100 text-white overflow-hidden rounded-4 stat-card-gradient-1 shadow-lg">
                <div class="card-body p-4 position-relative">
                    <h6 class="text-uppercase opacity-75 fw-bold small mb-2">Total Menu</h6>
                    <h2 class="display-4 fw-bold mb-0"><?= $stats['products'] ?></h2>
                    <div class="mt-4 d-flex align-items-center opacity-75">
                        <i class="bi bi-box-seam me-2"></i> Item Tersedia
                    </div>
                    <i class="bi bi-grid-fill icon-bg-large"></i>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-md-3 animate__animated animate__fadeInUp animate__fast delay-200">
        <div class="card border-0 h-100 text-white overflow-hidden rounded-4 stat-card-gradient-2 shadow-lg">
            <div class="card-body p-4 position-relative">
                <h6 class="text-uppercase opacity-75 fw-bold small mb-2">Total Pesanan</h6>
                <h2 class="display-4 fw-bold mb-0"><?= $stats['orders'] ?></h2>
                <div class="mt-4 d-flex align-items-center opacity-75">
                    <i class="bi bi-bag-check me-2"></i> Transaksi
                </div>
                <i class="bi bi-receipt icon-bg-large"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3 animate__animated animate__fadeInUp animate__fast delay-300">
        <div class="card border-0 h-100 text-white overflow-hidden rounded-4 stat-card-gradient-3 shadow-lg">
            <div class="card-body p-4 position-relative">
                <h6 class="text-uppercase opacity-75 fw-bold small mb-2">Pesanan Pending</h6>
                <h2 class="display-4 fw-bold mb-0"><?= $stats['pending'] ?></h2>
                <div class="mt-4">
                    <a href="orders.php"
                        class="text-white text-decoration-none fw-bold border-bottom border-white pb-1">
                        Proses Sekarang <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <i class="bi bi-hourglass-split icon-bg-large"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3 animate__animated animate__fadeInUp animate__fast delay-400">
        <div class="card border-0 h-100 text-white overflow-hidden rounded-4 stat-card-gradient-4 shadow-lg">
            <div class="card-body p-4 position-relative">
                <h6 class="text-uppercase opacity-75 fw-bold small mb-2">Pendapatan Hari Ini</h6>
                <h2 class="display-6 fw-bold mb-0">Rp <?= number_format($stats['income'] / 1000, 0) ?>k</h2>
                <div class="mt-4 d-flex align-items-center opacity-75">
                    <small>Total: Rp <?= number_format($stats['income'], 0, ',', '.') ?></small>
                </div>
                <i class="bi bi-wallet2 icon-bg-large"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="row g-4">
    <!-- Chart Section -->
    <div class="col-lg-8 animate__animated animate__fadeIn animate__fast delay-500">
        <div class="glass-card h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-dark">Tren Pendapatan Mingguan</h5>
                <button class="btn btn-sm btn-light rounded-pill px-3">
                    <i class="bi bi-calendar3 me-1"></i> 7 Hari Terakhir
                </button>
            </div>
            <div style="height: 300px; width: 100%;">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Doughnut Chart Section -->
    <div class="col-lg-4 animate__animated animate__fadeIn animate__fast delay-500">
        <div class="glass-card h-100 p-4">
            <h5 class="fw-bold mb-4 text-dark text-center">Distribusi Status</h5>
            <div class="position-relative" style="height: 250px;">
                <canvas id="statusChart"></canvas>
                <!-- Center Text Overlay -->
                <div class="position-absolute top-50 start-50 translate-middle text-center pointer-events-none">
                    <h2 class="fw-bold mb-0 text-dark"><?= array_sum($status_counts) ?></h2>
                    <small class="text-muted">Total</small>
                </div>
            </div>
            <div class="mt-4 text-center">
                <a href="reports.php" class="btn btn-outline-primary rounded-pill btn-sm px-4">Lihat Laporan Detail</a>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col-12 animate__animated animate__fadeInUp animate__fast delay-500 mt-5">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-dark">Transaksi Terakhir (Selesai)</h5>
                <a href="orders.php" class="text-decoration-none fw-bold small">Lihat Semua</a>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Detail Meja</th>
                            <th>Total Pembayaran</th>
                            <th class="text-end pe-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_sales->num_rows > 0): ?>
                            <?php while ($sale = $recent_sales->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-3">
                                        <span class="badge bg-light text-primary border fw-bold rounded-pill px-3">
                                            #<?= $sale['id'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle text-primary d-flex align-items-center justify-content-center me-3"
                                                style="width: 35px; height: 35px;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($sale['user_name']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted"><i class="bi bi-ui-checks-grid me-1"></i> Meja
                                            <?= $sale['table_number'] ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success fs-6">Rp
                                            <?= number_format($sale['total_amount'], 0, ',', '.') ?></span>
                                    </td>
                                    <td class="text-end pe-3 text-muted small">
                                        <?= date('d M Y, H:i', strtotime($sale['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="60"
                                        class="mb-3 opacity-50">
                                    <p class="text-muted">Belum ada transaksi yang selesai hari ini.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Live Clock
    setInterval(() => {
        const now = new Date();
        document.getElementById('digital-clock').innerText = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }, 1000);

    // Income Chart Configuration
    const ctxIncome = document.getElementById('incomeChart').getContext('2d');
    const gradientIncome = ctxIncome.createLinearGradient(0, 0, 0, 400);
    gradientIncome.addColorStop(0, 'rgba(99, 102, 241, 0.5)'); // Top color
    gradientIncome.addColorStop(1, 'rgba(99, 102, 241, 0.0)'); // Bottom color

    new Chart(ctxIncome, {
        type: 'line',
        data: {
            labels: <?= json_encode($trend_labels) ?>,
            datasets: [{
                label: 'Total Omset',
                data: <?= json_encode($trend_data) ?>,
                borderColor: '#6366f1',
                backgroundColor: gradientIncome,
                borderWidth: 3,
                tension: 0.4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#6366f1',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    titleFont: { size: 14 },
                    bodyFont: { size: 14 },
                    displayColors: false,
                    callbacks: {
                        label: function (context) {
                            return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        borderDash: [5, 5]
                    },
                    ticks: {
                        callback: function (value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Status Chart Configuration
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Selesai', 'Batal', 'Proses', 'Pending'],
            datasets: [{
                data: <?= json_encode($status_counts) ?>,
                backgroundColor: [
                    '#10b981', // Emerald for success
                    '#ef4444', // Red for cancelled
                    '#3b82f6', // Blue for processing
                    '#f59e0b'  // Amber for pending
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12 }
                    }
                }
            }
        }
    });
</script>

<?php require_once '../../includes/admin_footer.php'; ?>