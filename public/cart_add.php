<?php
require_once '../includes/functions.php';

// Check if request is AJAX
$isAjax = isset($_POST['ajax']) && $_POST['ajax'] == 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if product already in cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // Remove if quantity <= 0
    if ($_SESSION['cart'][$product_id] <= 0) {
        unset($_SESSION['cart'][$product_id]);
        $new_qty = 0;
    } else {
        $new_qty = $_SESSION['cart'][$product_id];
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'new_qty' => $new_qty,
            'cart_count' => array_sum($_SESSION['cart'])
        ]);
        exit;
    }
}

// Redirect back to where they came from or referer
if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    redirect('index.php');
}
exit;
?>