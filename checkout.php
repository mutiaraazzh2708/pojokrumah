<?php
require_once './config/database.php';
require_once './includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    redirect('index.php');
}

// Prepare data
$ids = implode(',', array_keys($_SESSION['cart']));
$sql = "SELECT id, name, price FROM products WHERE id IN ($ids)";
$result = $conn->query($sql);

$total_amount = 0;
$items_summary = []; // Array to hold item strings
$order_items_data = []; // Array to hold data for order_items table

while ($row = $result->fetch_assoc()) {
    $qty = $_SESSION['cart'][$row['id']];
    $subtotal = $row['price'] * $qty;
    $total_amount += $subtotal;

    // Format: "Nasi Goreng (x2)"
    $items_summary[] = $row['name'] . " (x" . $qty . ")";

    // Store for DB insert later
    $order_items_data[] = [
        'product_id' => $row['id'],
        'quantity' => $qty,
        'price' => $row['price']
    ];
}

// Join all items into one string, e.g., "Nasi Goreng (x2), Es Teh (x1)"
$items_string = implode(", ", $items_summary);

// Add tax
$final_total = $total_amount * 1.1;

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table_number = isset($_POST['table_number']) ? (int) $_POST['table_number'] : 0;
    $note = isset($_POST['note']) ? trim($_POST['note']) : '';
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
    $order_type = isset($_POST['order_type']) ? $_POST['order_type'] : '';

    // Validate order type
    if (empty($order_type)) {
        $error = "Silakan pilih jenis pesanan.";
    } elseif ($order_type === 'dinein' && $table_number <= 0) {
        // Only require table number for dine-in
        $error = "Silakan masukkan nomor meja yang valid.";
    } elseif (empty($payment_method)) {
        $error = "Silakan pilih metode pembayaran.";
    } else {
        // Use Transaction for data integrity
        $conn->begin_transaction();

        try {
            $user_id = $_SESSION['user_id'];
            $final_total_int = (int) $final_total;

            // For takeaway, set table_number to 0
            if ($order_type === 'takeaway') {
                $table_number = 0;
            }

            $stmt = $conn->prepare("INSERT INTO orders (user_id, table_number, order_type, total_amount, items, note, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            // Correct types: i (user_id), i (table_number), s (order_type), i (total_amount), s (items), s (note), s (payment_method)
            $stmt->bind_param("iisisss", $user_id, $table_number, $order_type, $final_total_int, $items_string, $note, $payment_method);

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $new_order_id = $conn->insert_id;

            // Insert into order_items
            $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            if (!$stmt_items) {
                throw new Exception("Prepare items failed: " . $conn->error);
            }

            foreach ($order_items_data as $item) {
                $pid = (int) $item['product_id'];
                $pqty = (int) $item['quantity'];
                $pprice = (int) $item['price'];
                $stmt_items->bind_param("iiii", $new_order_id, $pid, $pqty, $pprice);
                if (!$stmt_items->execute()) {
                    throw new Exception("Execute item failed: " . $stmt_items->error);
                }
            }

            // Commit transaction
            $conn->commit();

            unset($_SESSION['cart']);
            redirect("payment.php?id=$new_order_id");

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<?php require_once './includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-receipt display-1 text-primary"></i>
                        <h3 class="fw-bold mt-3">Informasi Pemesanan</h3>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <!-- Hidden field to pass order_type from cart -->
                        <input type="hidden" name="order_type"
                            value="<?= isset($_POST['order_type']) ? htmlspecialchars($_POST['order_type']) : '' ?>">

                        <?php
                        $order_type = isset($_POST['order_type']) ? $_POST['order_type'] : '';
                        $is_takeaway = ($order_type === 'takeaway');
                        ?>

                        <?php if (!$is_takeaway): ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase">Nomor Meja <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="table_number" class="form-control form-control-lg"
                                    placeholder="Contoh: 14" required min="1">
                            </div>
                        <?php else: ?>
                            <!-- Hidden field for takeaway orders -->
                            <input type="hidden" name="table_number" value="0">
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Take Away</strong> - Nomor meja tidak diperlukan
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Catatan (Opsional)</label>
                            <textarea name="note" class="form-control" rows="2"
                                placeholder="Contoh: Tanpa gula, es dipisah..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Metode Pembayaran</label>
                            <div class="d-flex gap-2">
                                <div class="form-check border p-3 rounded w-100 position-relative">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_cash"
                                        value="cash" checked>
                                    <label class="form-check-label w-100 stretched-link fw-semibold" for="pay_cash">
                                        <i class="bi bi-cash-stack me-2 text-success"></i> Tunai
                                    </label>
                                </div>
                                <div class="form-check border p-3 rounded w-100 position-relative">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_qris"
                                        value="qris">
                                    <label class="form-check-label w-100 stretched-link fw-semibold" for="pay_qris">
                                        <i class="bi bi-qr-code-scan me-2 text-primary"></i> QRIS
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Order Summary Box -->
                        <div class="bg-light p-4 rounded-4 mb-4">
                            <div class="mb-3 border-bottom pb-2">
                                <h6 class="fw-bold mb-2">Item Dipesan</h6>
                                <p class="small text-muted mb-0 fst-italic"><?= htmlspecialchars($items_string) ?></p>
                            </div>

                            <div class="d-flex justify-content-between mb-2 small text-muted">
                                <span>Subtotal</span>
                                <span>Rp <?= number_format($total_amount, 0, ',', '.') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 small text-muted">
                                <span>Pajak & Layanan (10%)</span>
                                <span>Rp <?= number_format($total_amount * 0.1, 0, ',', '.') ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">Total Tagihan</span>
                                <span class="fw-bold text-primary fs-4">Rp
                                    <?= number_format($final_total, 0, ',', '.') ?></span>
                            </div>
                        </div>

                        <button type="submit"
                            class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm text-white">
                            Konfirmasi Pesanan <i class="bi bi-check-circle-fill ms-2"></i>
                        </button>
                        <a href="cart.php" class="btn btn-link w-100 text-muted mt-3 text-decoration-none small">Kembali
                            ke Keranjang</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once './includes/footer.php'; ?>