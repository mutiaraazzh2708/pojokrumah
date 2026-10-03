<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

checkSuperAdmin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Get image filename to delete file
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $product = $res->fetch_assoc();
        if ($product['image'] && file_exists("../assets/uploads/" . $product['image'])) {
            unlink("../assets/uploads/" . $product['image']);
        }

        $del_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        $del_stmt->execute();
    }
}

redirect('admin/products.php');
?>