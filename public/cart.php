<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Update Quantity
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_qty'])) {
    $pid = $_POST['product_id'];
    $qty = (int) $_POST['quantity'];

    if ($qty > 0) {
        $_SESSION['cart'][$pid] = $qty;
    } else {
        unset($_SESSION['cart'][$pid]);
    }
}

// Handle Remove Item
if (isset($_GET['remove'])) {
    $pid = $_GET['remove'];
    unset($_SESSION['cart'][$pid]);
    header("Location: cart.php");
    exit;
}

// Fetch Product Details
$cart_items = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $row['qty'] = $_SESSION['cart'][$row['id']];
        $row['subtotal'] = $row['price'] * $row['qty'];
        $subtotal += $row['subtotal'];
        $cart_items[] = $row;
    }
}

$tax = $subtotal * 0.1;
$total = $subtotal + $tax;
?>

<?php require_once '../includes/header.php'; ?>

<!-- Animation Library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<style>
    body {
        background-color: #f8f9fa;
    }

    .cart-header-bg {
        background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
        padding: 80px 0 60px;
        position: relative;
        overflow: hidden;
    }

    .cart-header-bg::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 40px;
        background: #f8f9fa;
        border-radius: 40px 40px 0 0;
    }

    .cart-item-card {
        background: white;
        border-radius: 20px;
        border: none;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .cart-item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
    }

    .qty-btn {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: none;
        background: #f1f5f9;
        color: #334155;
        font-weight: bold;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qty-btn:hover {
        background: var(--primary-color);
        color: white;
    }

    .summary-card {
        background: white;
        border-radius: 24px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
        position: sticky;
        top: 100px;
    }

    .form-control-modern {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 14px 16px;
        /* Larger padding for touch */
        transition: all 0.3s;
        font-size: 16px;
        /* Prevents zoom on iOS */
        min-height: 50px;
        /* Better touch target */
    }

    .form-control-modern:focus {
        background: white;
        box-shadow: 0 0 0 4px rgba(212, 163, 115, 0.1);
        border-color: var(--primary-color);
    }

    /* Fix dropdown overflow on mobile */
    .form-select.form-control-modern {
        max-width: 100%;
        word-wrap: break-word;
        white-space: normal;
    }

    .form-select.form-control-modern option {
        white-space: normal;
        word-wrap: break-word;
    }

    /* Ensure proper mobile responsiveness */
    @media (max-width: 576px) {

        .form-control-modern,
        .form-select.form-control-modern {
            font-size: 14px;
            padding: 12px 14px;
            width: 100%;
            max-width: 100%;
        }
    }


    /* Staggered entry animation */
    .cart-item-0 {
        animation-delay: 0.1s;
    }

    .cart-item-1 {
        animation-delay: 0.2s;
    }

    .cart-item-2 {
        animation-delay: 0.3s;
    }

    /* Simple Order Type Buttons */
    .order-type-simple {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .order-type-btn-simple {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 16px 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
        font-weight: 500;
        color: #6c757d;
    }

    .order-type-btn-simple i {
        font-size: 24px;
        color: var(--primary-color);
    }

    .order-type-btn-simple:hover {
        border-color: var(--primary-color);
        background: #fff8f0;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(212, 163, 115, 0.2);
    }

    .order-type-btn-simple.active {
        border-color: var(--primary-color);
        background: var(--primary-color);
        color: white;
    }

    .order-type-btn-simple.active i {
        color: white;
    }

    @media (max-width: 576px) {
        .order-type-btn-simple {
            padding: 14px 10px;
            font-size: 13px;
        }

        .order-type-btn-simple i {
            font-size: 20px;
        }
    }
</style>

<!-- Header Hero -->
<div class="cart-header-bg text-center text-white mb-4 animate__animated animate__fadeIn">
    <div class="container position-relative z-2">
        <h1 class="display-5 fw-bold mb-2">Keranjang Belanja</h1>
        <p class="text-white-50 fs-5 fw-light">Selesaikan pesananmu dan nikmati hidangannya.</p>
    </div>
</div>

<div class="container pb-5" style="min-height: 60vh;">
    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5 animate__animated animate__fadeInUp">
            <div class="mb-4 position-relative d-inline-block">
                <div class="bg-white rounded-circle p-5 shadow-sm"
                    style="width: 200px; height: 200px; display: flex; align-items: center; justify-content: center;">
                    <img src="https://cdn-icons-png.flaticon.com/512/2038/2038854.png" width="100" class="opacity-50"
                        alt="Empty Cart">
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-3">Wah, keranjangmu kosong!</h3>
            <p class="text-muted col-md-6 mx-auto mb-4 fs-5">
                Sepertinya kamu belum memilih menu favoritmu hari ini.
            </p>
            <a href="index.php" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow hover-lift">
                <i class="bi bi-cart-plus me-2"></i>Mulai Pesan
            </a>
        </div>
    <?php else: ?>
        <div class="row g-5">
            <!-- Left Side: Items List -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4 ps-1">
                    <h5 class="fw-bold text-secondary mb-0">Item Terpilih (<?= count($cart_items) ?>)</h5>
                    <a href="cart.php?clear=all" class="text-danger text-decoration-none small fw-bold"
                        onclick="return confirm('Kosongkan keranjang?')">
                        <i class="bi bi-trash me-1"></i>Hapus Semua
                    </a>
                </div>

                <div class="d-flex flex-column gap-3">
                    <?php $i = 0;
                    foreach ($cart_items as $item): ?>
                        <div class="cart-item-card p-3 animate__animated animate__fadeInUp cart-item-<?= $i++ % 3 ?>">
                            <div class="row align-items-center">
                                <!-- Image -->
                                <div class="col-3 col-sm-2 col-md-2">
                                    <div class="rounded-4 overflow-hidden position-relative" style="aspect-ratio: 1/1;">
                                        <?php if ($item['image']): ?>
                                            <img src="<?= BASE_URL . 'assets/uploads/' . $item['image'] ?>"
                                                class="w-100 h-100 object-fit-cover" alt="Product">
                                        <?php else: ?>
                                            <div
                                                class="bg-light w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                                <i class="bi bi-image fs-4"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="col-9 col-sm-10 col-md-10">
                                    <div class="row h-100 align-items-center g-0">
                                        <div class="col-md-5 mb-3 mb-md-0 pe-md-3">
                                            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($item['name']) ?></h5>
                                            <div class="text-primary fw-bold">Rp
                                                <?= number_format($item['price'], 0, ',', '.') ?>
                                            </div>
                                            <small
                                                class="text-muted d-block text-truncate"><?= htmlspecialchars($item['description']) ?></small>
                                        </div>

                                        <!-- Qty Control -->
                                        <div class="col-6 col-md-4 d-flex justify-content-md-center">
                                            <form method="POST"
                                                class="d-flex align-items-center bg-light rounded-pill p-1 border">
                                                <input type="hidden" name="update_qty" value="1">
                                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">

                                                <button type="button" onclick="this.form.quantity.value--; this.form.submit()"
                                                    class="qty-btn bg-white shadow-sm">
                                                    <i class="bi bi-dash"></i>
                                                </button>

                                                <input type="number" name="quantity"
                                                    class="form-control border-0 bg-transparent text-center fw-bold px-0 mx-2"
                                                    value="<?= $item['qty'] ?>" min="1" onchange="this.form.submit()"
                                                    style="width: 50px; -moz-appearance: textfield;">

                                                <button type="button" onclick="this.form.quantity.value++; this.form.submit()"
                                                    class="qty-btn bg-white shadow-sm">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Subtotal & Delete -->
                                        <div class="col-6 col-md-3 text-end ps-3">
                                            <div class="fw-bold fs-5 text-dark">Rp
                                                <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                            </div>
                                            <a href="cart.php?remove=<?= $item['id'] ?>"
                                                class="btn btn-link text-muted p-0 text-decoration-none small mt-1"
                                                onclick="return confirm('Hapus item?')">
                                                <i class="bi bi-x-circle me-1"></i>Hapus
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 text-center text-md-start animate__animated animate__fadeIn">
                    <a href="index.php" class="btn btn-outline-dark rounded-pill px-4 fw-bold">
                        <i class="bi bi-arrow-left me-2"></i>Pilih Menu Lain
                    </a>
                </div>
            </div>

            <!-- Right Side: Checkout Summary -->
            <div class="col-lg-4">
                <div class="summary-card p-4 animate__animated animate__fadeInRight animate__delay-1s">
                    <h4 class="fw-bold mb-4">Informasi Pemesanan</h4>

                    <form action="checkout.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted ps-2">Nomor Meja <span
                                    class="text-danger" id="table_required_indicator"
                                    style="display: none;">*</span></label>
                            <input type="number" name="table_number" id="table_number"
                                class="form-control form-control-modern" placeholder="Masukkan No. Meja" min="1" max="10">
                            <div id="table_error" class="text-danger small ps-2 mt-1" style="display: none;">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <span id="table_error_message"></span>
                            </div>
                            <small class="text-muted ps-2" id="table_helper_text" style="display: none;">
                                <i class="bi bi-info-circle me-1"></i>Wajib diisi untuk Makan di Tempat
                            </small>
                        </div>



                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted ps-2">Catatan
                                (Opsional)</label>
                            <textarea name="note" class="form-control form-control-modern" rows="3"
                                placeholder="Contoh: Tanpa gula, es dipisah, nasi lebih sedikit, pedas sedang, dll..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted ps-2">Jenis Pesanan</label>
                            <input type="hidden" name="order_type" id="order_type_input" value="dinein" required>
                            <div class="order-type-simple">
                                <button type="button" class="order-type-btn-simple active" data-value="dinein">
                                    <i class="bi bi-shop"></i>
                                    <span>Makan di Tempat</span>
                                </button>
                                <button type="button" class="order-type-btn-simple" data-value="takeaway">
                                    <i class="bi bi-bag"></i>
                                    <span>Take Away</span>
                                </button>
                            </div>
                        </div>


                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted ps-2">Metode Pembayaran</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check bg-light border p-3 rounded-4 position-relative">
                                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_cash"
                                        value="cash" checked>
                                    <label class="form-check-label w-100 stretched-link fw-semibold ms-2" for="pay_cash">
                                        <i class="bi bi-cash-stack me-2 text-success"></i> Tunai
                                    </label>
                                </div>
                                <div class="form-check bg-light border p-3 rounded-4 position-relative">
                                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_qris"
                                        value="qris">
                                    <label class="form-check-label w-100 stretched-link fw-semibold ms-2" for="pay_qris">
                                        <i class="bi bi-qr-code-scan me-2 text-primary"></i> QRIS
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light rounded-4 p-3 mb-4">
                            <div class="d-flex justify-content-between mb-2 text-muted">
                                <span>Subtotal</span>
                                <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                            </div>
                            <div
                                class="d-flex justify-content-between mb-3 pb-3 border-bottom border-light-subtle text-muted">
                                <span>Pajak & Layanan (10%)</span>
                                <span>Rp <?= number_format($tax, 0, ',', '.') ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">Total Tagihan</span>
                                <span class="fw-bold fs-3 text-primary">Rp <?= number_format($total, 0, ',', '.') ?></span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-lg hover-scale">
                            Konfirmasi Pesanan <i class="bi bi-check-circle-fill ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Get form elements
    const tableInput = document.getElementById('table_number');
    const tableError = document.getElementById('table_error');
    const tableErrorMessage = document.getElementById('table_error_message');
    const checkoutForm = document.querySelector('form[action="checkout.php"]');
    const orderTypeInput = document.getElementById('order_type_input');
    const orderTypeBtns = document.querySelectorAll('.order-type-btn-simple');
    const tableNumberContainer = tableInput ? tableInput.closest('.mb-3') : null;

    // Handle order type button clicks
    orderTypeBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const value = this.getAttribute('data-value');

            // Remove active class from all buttons
            orderTypeBtns.forEach(b => b.classList.remove('active'));

            // Add active class to clicked button
            this.classList.add('active');

            // Update hidden input
            orderTypeInput.value = value;

            // Trigger table number field toggle
            toggleTableNumberField();
        });
    });

    // Function to toggle table number field based on order type
    function toggleTableNumberField() {
        const orderType = orderTypeInput.value;
        const tableRequiredIndicator = document.getElementById('table_required_indicator');
        const tableHelperText = document.getElementById('table_helper_text');

        if (orderType === 'takeaway') {
            // Hide table number field and remove required attribute
            if (tableNumberContainer) {
                tableNumberContainer.style.display = 'none';
            }
            if (tableInput) {
                tableInput.removeAttribute('required');
                tableInput.value = ''; // Clear value
                hideError();
            }
            // Hide required indicator and helper text
            if (tableRequiredIndicator) tableRequiredIndicator.style.display = 'none';
            if (tableHelperText) tableHelperText.style.display = 'none';
        } else if (orderType === 'dinein') {
            // Show table number field and add required attribute
            if (tableNumberContainer) {
                tableNumberContainer.style.display = 'block';
            }
            if (tableInput) {
                tableInput.setAttribute('required', 'required');
            }
            // Show required indicator and helper text
            if (tableRequiredIndicator) tableRequiredIndicator.style.display = 'inline';
            if (tableHelperText) tableHelperText.style.display = 'block';
        } else {
            // No order type selected yet - hide field
            if (tableNumberContainer) {
                tableNumberContainer.style.display = 'none';
            }
            if (tableInput) {
                tableInput.removeAttribute('required');
            }
            // Hide indicators
            if (tableRequiredIndicator) tableRequiredIndicator.style.display = 'none';
            if (tableHelperText) tableHelperText.style.display = 'none';
        }
    }

    // Table Number Validation (only when required)
    function validateTableNumber() {
        // Skip validation if table number is not required (takeaway)
        if (orderTypeInput.value === 'takeaway') {
            return true;
        }

        const value = parseInt(tableInput.value);

        if (isNaN(value) || value < 1) {
            showError('Nomor meja harus minimal 1');
            return false;
        } else if (value > 10) {
            showError('Nomor meja maksimal 10');
            return false;
        } else {
            hideError();
            return true;
        }
    }

    function showError(message) {
        tableErrorMessage.textContent = message;
        tableError.style.display = 'block';
        tableInput.classList.add('is-invalid');
        tableInput.style.borderColor = '#dc3545';
    }

    function hideError() {
        tableError.style.display = 'none';
        tableInput.classList.remove('is-invalid');
        tableInput.style.borderColor = '';
    }

    // Validate on input (only when field is visible)
    if (tableInput) {
        tableInput.addEventListener('input', function () {
            if (orderTypeInput.value === 'dinein') {
                validateTableNumber();
            }
        });

        tableInput.addEventListener('blur', function () {
            if (this.value && orderTypeInput.value === 'dinein') {
                validateTableNumber();
            }
        });
    }

    // Validate on form submit
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            // Only validate table number if order type is dine in
            if (orderTypeInput.value === 'dinein') {
                if (!validateTableNumber()) {
                    e.preventDefault();
                    tableInput.focus();

                    // Show alert for extra emphasis
                    alert('⚠️ Nomor meja harus antara 1-10. Silakan periksa kembali.');
                    return false;
                }
            }
        });
    }

    // Initialize UI based on default selection
    toggleTableNumberField();
</script>

<?php require_once '../includes/footer.php'; ?>