<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAdmin();

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($order_id <= 0) {
    die("Invalid order ID");
}

// Fetch order details
$sql = "SELECT o.*, COALESCE(u.name, 'Admin/Staff') as user_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Order not found");
}

$order = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan #
        <?= $order['id'] ?>
    </title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            background: white;
            color: black;
        }

        .receipt {
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            margin: 2px 0;
        }

        .section {
            margin: 10px 0;
            font-size: 12px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }

        .info-label {
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .items {
            margin: 10px 0;
        }

        .item {
            margin: 5px 0;
            font-size: 11px;
        }

        .total-section {
            margin-top: 10px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 2px dashed #000;
            padding-top: 10px;
            font-size: 11px;
        }

        .print-btn {
            margin: 20px auto;
            display: block;
            padding: 10px 30px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-btn:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ Cetak Struk</button>

    <div class="receipt">
        <div class="header">
            <h1>CAFE POJOK RUMAH</h1>
            <p>Jl. Contoh No. 123, Kota</p>
            <p>Telp: 0812-3456-7890</p>
        </div>

        <div class="section">
            <div class="info-row">
                <span class="info-label">No. Pesanan:</span>
                <span>#
                    <?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal:</span>
                <span>
                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Kasir:</span>
                <span>
                    <?= htmlspecialchars(isset($_SESSION['username']) ? $_SESSION['username'] : (isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin')) ?>
                </span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="section">
            <div class="info-row">
                <span class="info-label">Pelanggan:</span>
                <span>
                    <?= htmlspecialchars($order['user_name']) ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Meja:</span>
                <span>
                    <?= $order['table_number'] ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Jenis:</span>
                <span>
                    <?= $order['order_type'] === 'takeaway' ? 'TAKE AWAY' : 'DINE IN' ?>
                </span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="section items">
            <div class="section-title">Pesanan:</div>
            <?php
            // Fetch order items with prices
            $items_sql = "SELECT oi.*, p.name 
                         FROM order_items oi 
                         LEFT JOIN products p ON oi.product_id = p.id 
                         WHERE oi.order_id = ?";
            $items_stmt = $conn->prepare($items_sql);
            $items_stmt->bind_param("i", $order_id);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();

            while ($item = $items_result->fetch_assoc()):
                $item_subtotal = $item['price'] * $item['quantity'];
                ?>
                <div class="item" style="display: flex; justify-content: space-between; margin: 3px 0;">
                    <div style="flex: 1;">
                        <?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)
                        <div style="font-size: 10px; color: #666;">
                            @Rp <?= number_format($item['price'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div style="font-weight: bold;">
                        Rp <?= number_format($item_subtotal, 0, ',', '.') ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if (!empty($order['note'])): ?>
            <div class="section">
                <div class="section-title">Catatan:</div>
                <div style="font-size: 11px; font-style: italic;">
                    <?= htmlspecialchars($order['note']) ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="divider"></div>

        <div class="section">
            <div class="info-row">
                <span class="info-label">Metode Bayar:</span>
                <span>
                    <?= $order['payment_method'] === 'qris' ? 'QRIS' : 'TUNAI' ?>
                </span>
            </div>
        </div>

        <div class="total-section">
            <?php
            // Calculate subtotal and tax (10%)
            $total = $order['total_amount'];
            $subtotal = $total / 1.1; // Remove tax to get subtotal
            $tax = $total - $subtotal; // Tax amount
            ?>
            <div class="info-row">
                <span>Subtotal:</span>
                <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
            </div>
            <div class="info-row">
                <span>Pajak (10%):</span>
                <span>Rp <?= number_format($tax, 0, ',', '.') ?></span>
            </div>
            <div class="divider" style="margin: 5px 0;"></div>
            <div class="total-row">
                <span>TOTAL:</span>
                <span>Rp
                    <?= number_format($order['total_amount'], 0, ',', '.') ?>
                </span>
            </div>
        </div>

        <div class="footer">
            <p>Terima Kasih Atas Kunjungan Anda!</p>
            <p>Selamat Menikmati</p>
            <p style="margin-top: 10px;">===========================</p>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>

</html>