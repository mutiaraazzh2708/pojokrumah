<?php
require_once './config/database.php';
require_once './includes/functions.php';

checkAuth();

if (!isset($_GET['id'])) {
    redirect('user/orders.php');
}

$order_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('user/orders.php');
}

$order = $result->fetch_assoc();

$success_msg = '';
$error_msg = '';

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['payment_proof'])) {
    $file = $_FILES['payment_proof'];
    $transaction_id = isset($_POST['transaction_id']) ? htmlspecialchars(trim($_POST['transaction_id'])) : '';
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (empty($transaction_id)) {
        $error_msg = "Mohon masukkan Nomor Referensi / Transaction ID.";
    } elseif (!in_array($ext, $allowed)) {
        $error_msg = "Hanya file JPG, JPEG, PNG, dan WEBP yang diperbolehkan.";
    } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB
        $error_msg = "Ukuran file maksimal 5MB.";
    } else {
        $new_name = "proof_" . $order_id . "_" . time() . "." . $ext;
        $target_dir = "assets/uploads/proofs/";

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $target_dir . $new_name)) {
            $stmt = $conn->prepare("UPDATE orders SET payment_proof = ?, transaction_id = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_name, $transaction_id, $order_id);
            if ($stmt->execute()) {
                $success_msg = "Bukti pembayaran berhasil diupload! Menunggu verifikasi admin.";
                // Refresh data
                $order['payment_proof'] = $new_name;
                $order['transaction_id'] = $transaction_id;
            } else {
                $error_msg = "Gagal update database: " . $conn->error;
            }
        } else {
            $error_msg = "Gagal mengupload file.";
        }
    }
}
?>

<?php require_once './includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="fw-bold mb-0">Pembayaran</h4>
                    <p class="mb-0 opacity-75">Order #
                        <?= $order_id ?>
                    </p>
                </div>
                <div class="card-body p-5 text-center">

                    <?php if (isset($success_msg) && $success_msg): ?>
                        <div class="alert alert-success rounded-4 mb-4 fw-bold">
                            <i class="bi bi-check-circle-fill me-2"></i> <?= $success_msg ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_msg) && $error_msg): ?>
                        <div class="alert alert-danger rounded-4 mb-4">
                            <i class="bi bi-exclamation-circle-fill me-2"></i> <?= $error_msg ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small fw-bold">Total Tagihan</h6>
                        <h2 class="display-4 fw-bold text-primary">Rp
                            <?= number_format($order['total_amount'], 0, ',', '.') ?>
                        </h2>
                    </div>

                    <?php if ($order['payment_method'] === 'qris'): ?>
                        <!-- QRIS Section -->
                        <div class="bg-light p-4 rounded-4 mb-4 border">
                            <h5 class="fw-bold mb-3">Scan QRIS</h5>
                            <div class="bg-white p-3 rounded shadow-sm mb-3 mx-auto d-block" style="width: fit-content;">
                                <!-- Real QRIS -->
                                <img src="assets/images/qris.jpg" alt="Scan QRIS" class="img-fluid rounded d-block mx-auto"
                                    style="max-width: 300px; width: 100%;">
                            </div>
                            <p class="small text-muted mb-0">Scan menggunakan aplikasi e-wallet apa saja (GoPay, OVO, Dana,
                                dll) atau Mobile Banking.</p>
                        </div>

                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-1"></i> Screenshot layar ini jika perlu bukti pembayaran.
                        </div>

                        <!-- Upload Proof Section -->
                        <div class="mb-4">
                            <?php if (!empty($order['payment_proof'])): ?>
                                <div class="bg-success bg-opacity-10 border border-success border-opacity-25 rounded-4 p-4">
                                    <i class="bi bi-check-circle-fill text-success display-4 mb-2"></i>
                                    <h5 class="fw-bold text-success">Bukti Pembayaran Terkirim</h5>
                                    <p class="text-muted small mb-0">Terima kasih, pembayaran Anda sedang diverifikasi.</p>
                                    <div class="mt-3">
                                        <img src="assets/uploads/proofs/<?= $order['payment_proof'] ?>"
                                            class="img-thumbnail rounded shadow-sm" style="max-height: 150px;">
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card border-dashed bg-light rounded-4">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-upload me-2"></i>Upload Bukti
                                            Pembayaran</h6>
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="mb-3">
                                                <label class="form-label text-start d-block fw-bold small">Nomor Referensi / Transaction ID</label>
                                                <input type="text" name="transaction_id" class="form-control"
                                                    placeholder="Contoh: 123456XXXXXX" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-start d-block fw-bold small">Upload Bukti</label>
                                                <input type="file" name="payment_proof" class="form-control" required
                                                    accept="image/*">
                                                <div class="form-text small">Format: JPG, PNG, WEBP (Max 5MB)</div>
                                            </div>
                                            <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold shadow-sm">
                                                Kirim Bukti Bayar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <!-- Cash Section -->
                        <div class="bg-light p-5 rounded-4 mb-4 border">
                            <div class="mb-3">
                                <span class="display-1 text-success"><i class="bi bi-cash-coin"></i></span>
                            </div>
                            <h5 class="fw-bold mb-2">Instruksi Pembayaran Tunai</h5>
                            <p class="text-muted mb-0">Silakan menuju kasir dan sebutkan <strong>Nomor Meja
                                    <?= $order['table_number'] ?>
                                </strong> atau <strong>Order ID #
                                    <?= $order_id ?>
                                </strong> untuk melakukan pembayaran.</p>
                        </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <a href="user/orders.php" class="btn btn-outline-primary rounded-pill py-3 fw-bold">
                            Cek Status Pesanan
                        </a>
                        <a href="index.php" class="btn btn-link text-muted text-decoration-none small">
                            Kembali ke Beranda
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once './includes/footer.php'; ?>