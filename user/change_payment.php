<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth();

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Verify order belongs to user and is pending
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: orders.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_method = $_POST['payment_method'];

    if (in_array($new_method, ['cash', 'qris'])) {
        $stmt = $conn->prepare("UPDATE orders SET payment_method = ? WHERE id = ?");
        $stmt->bind_param("si", $new_method, $order_id);

        if ($stmt->execute()) {
            if ($new_method === 'qris') {
                header("Location: ../payment.php?id=" . $order_id);
            } else {
                header("Location: orders.php?success=1");
            }
            exit;
        } else {
            $error = "Gagal memperbarui metode pembayaran.";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h3 class="fw-bold mb-4">Ganti Metode Pembayaran</h3>
                    <p class="text-muted mb-4">Pesanan #
                        <?= $order['id'] ?> - Total: <strong>Rp
                            <?= number_format($order['total_amount'], 0, ',', '.') ?>
                        </strong>
                    </p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="cash"
                                    <?= $order['payment_method'] == 'cash' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="pay_cash">
                                    <i class="bi bi-cash fs-3 d-block mb-2"></i>
                                    Tunai / Kasir
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="payment_method" id="pay_qris" value="qris"
                                    <?= $order['payment_method'] == 'qris' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="pay_qris">
                                    <i class="bi bi-qr-code-scan fs-3 d-block mb-2"></i>
                                    QRIS (Transfer)
                                </label>
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow">
                                Simpan Perubahan
                            </button>
                            <a href="orders.php" class="btn btn-link text-muted mt-2">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>