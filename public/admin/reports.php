<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

checkAdmin();

// Set default date range to this month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Fetch Sales Data for Table
$sql = "SELECT o.*, COALESCE(u.name, 'Admin/Staff') as user_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.status = 'completed' 
        AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);

$total_income = 0;
$sales_data = [];
while ($row = $result->fetch_assoc()) {
    $sales_data[] = $row;
    $total_income += $row['total_amount'];
}

// Fetch Chart Data (Last 7 Days from End Date)
$chart_labels = [];
$chart_values = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("$end_date -$i days"));
    $chart_labels[] = date('d M', strtotime($date));

    $res = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = '$date' AND status = 'completed'");
    $daily = $res->fetch_assoc();
    $chart_values[] = $daily['total'] ?? 0;
}
?>

<?php require_once '../../includes/admin_header.php'; ?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h2 class="fw-bold mb-0">Laporan Penjualan</h2>
    <button onclick="window.print()" class="btn btn-outline-secondary">
        <i class="bi bi-printer me-2"></i> Cetak / Simpan PDF
    </button>
</div>

<!-- Filter Section -->
<div class="card border-0 shadow-sm mb-4 no-print">
    <div class="card-body">
        <form action="" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-2"></i> Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Chart Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Grafik Pendapatan (7 Hari Terakhir)</h5>
    </div>
    <div class="card-body">
        <canvas id="salesChart" height="100"></canvas>
    </div>
</div>

<!-- Report Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h5 class="mb-0 fw-bold">Rincian Transaksi</h5>
        <div class="d-flex flex-wrap gap-2">
            <?php
            $qris_total = 0;
            $cash_total = 0;
            foreach ($sales_data as $row) {
                if ($row['payment_method'] == 'qris') $qris_total += $row['total_amount'];
                else $cash_total += $row['total_amount'];
            }
            ?>
            <span class="badge bg-primary fs-7">QRIS: Rp <?= number_format($qris_total, 0, ',', '.') ?></span>
            <span class="badge bg-success fs-7">TUNAI: Rp <?= number_format($cash_total, 0, ',', '.') ?></span>
            <span class="badge bg-dark fs-6 ms-md-2">Total: Rp <?= number_format($total_income, 0, ',', '.') ?></span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">No. Order</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Meja</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                        <th class="text-center">Bukti</th>
                        <th class="pe-4 text-end">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sales_data)): ?>
                        <?php foreach ($sales_data as $row): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $row['id'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                <td><?= htmlspecialchars($row['user_name']) ?></td>
                                <td>Meja <?= $row['table_number'] ?></td>
                                <td>
                                    <?php if ($row['payment_method'] == 'qris'): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3">QRIS</span>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">TUNAI</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-success bg-opacity-10 text-success">Selesai</span></td>
                                <td class="text-center">
                                    <?php if (!empty($row['payment_proof'])): ?>
                                        <button class="btn btn-sm btn-light text-primary rounded-circle shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalOrder<?= $row['id'] ?>">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted fs-7">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end fw-bold text-dark">
                                    Rp <?= number_format($row['total_amount'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Tidak ada data penjualan pada periode ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modals for Details -->
<?php foreach ($sales_data as $row): ?>
    <div class="modal fade" id="modalOrder<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Detail Pesanan #<?= $row['id'] ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-4 bg-light text-center border-bottom">
                        <h3 class="fw-bold text-primary mb-0">Rp
                            <?= number_format($row['total_amount'], 0, ',', '.') ?>
                        </h3>
                        <div class="mt-2">
                            <span
                                class="badge rounded-pill border px-3 py-1 <?= ($row['payment_method'] == 'qris') ? 'bg-primary text-primary bg-opacity-10 border-primary border-opacity-25' : 'bg-success text-success bg-opacity-10 border-success border-opacity-25' ?>">
                                <?= ($row['payment_method'] == 'qris') ? 'QRIS' : 'TUNAI' ?>
                            </span>
                        </div>
                        <small
                            class="text-muted text-uppercase fw-bold mt-2 d-block"><?= date('d F Y, H:i', strtotime($row['created_at'])) ?></small>

                        <?php if (!empty($row['payment_proof'])): ?>
                            <div class="mt-3">
                                <div class="small fw-bold text-muted mb-1">Bukti Pembayaran:</div>
                                <img src="../assets/uploads/proofs/<?= $row['payment_proof'] ?>"
                                    class="img-thumbnail rounded shadow-sm" style="max-height: 250px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 text-start">
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1 font-monospace">Pelanggan</small>
                                <div class="fw-bold text-dark">
                                    <?= htmlspecialchars($row['user_name']) ?>
                                </div>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1 font-monospace">Meja</small>
                                <div class="badge bg-primary">No. <?= $row['table_number'] ?></div>
                            </div>
                        </div>
                        
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-secondary small text-uppercase">Rincian Menu</h6>
                        <div class="small text-muted">
                            <?= htmlspecialchars($row['items']) ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Print Styles -->
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        .admin-content,
        .admin-content * {
            visibility: visible;
        }

        .admin-content {
            position: absolute;
            left: 0;
            top: 0;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .sidebar,
        .no-print {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>

<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: <?= json_encode($chart_values) ?>,
                borderColor: '#d4a373',
                backgroundColor: 'rgba(212, 163, 115, 0.2)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [2, 4]
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>

<?php require_once '../../includes/admin_footer.php'; ?>