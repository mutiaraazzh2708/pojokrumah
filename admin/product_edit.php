<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

checkSuperAdmin();

if (!isset($_GET['id'])) {
    redirect('admin/products.php');
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    redirect('admin/products.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $category = $_POST['category'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    // Default to old image
    $image = $product['image'];

    // Image Upload Handling
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $filesize = $_FILES['image']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Hanya file JPG, JPEG, PNG, dan WEBP yang diperbolehkan.";
        } elseif ($filesize > 2000000) { // 2MB
            $error = "Ukuran file maksimal 2MB.";
        } else {
            $new_filename = uniqid() . "." . $ext;
            $destination = "../assets/uploads/" . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                // Delete old image if exists
                if ($product['image'] && file_exists("../assets/uploads/" . $product['image'])) {
                    unlink("../assets/uploads/" . $product['image']);
                }
                $image = $new_filename;
            } else {
                $error = "Gagal mengupload gambar.";
            }
        }
    }

    if (empty($error)) {
        $stmt_update = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=?, image=?, is_available=? WHERE id=?");
        $stmt_update->bind_param("ssdssii", $name, $description, $price, $category, $image, $is_available, $id);

        if ($stmt_update->execute()) {
            $success = "Menu berhasil diperbarui.";
            // Refresh data
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Gagal menyimpan perubahan: " . $stmt_update->error;
        }
    }
}
?>

<?php require_once '../includes/admin_header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Edit Menu</h5>
                <a href="products.php" class="btn btn-sm btn-outline-secondary">Kembali</a>
            </div>
            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nama Menu</label>
                        <input type="text" name="name" class="form-control" required
                            value="<?= htmlspecialchars($product['name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control"
                            rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" required
                                value="<?= $product['price'] ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="food" <?= $product['category'] == 'food' ? 'selected' : '' ?>>Makanan
                                </option>
                                <option value="drink" <?= $product['category'] == 'drink' ? 'selected' : '' ?>>Minuman
                                </option>
                                <option value="snack" <?= $product['category'] == 'snack' ? 'selected' : '' ?>>Camilan
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto Menu</label>
                        <?php if ($product['image']): ?>
                            <div class="mb-2">
                                <img src="<?= BASE_URL . 'assets/uploads/' . $product['image'] ?>" class="rounded"
                                    width="100">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="form-text">Biarkan kosong jika tidak ingin mengubah foto.</div>
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_available" id="isAvailable"
                            <?= $product['is_available'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isAvailable">Tersedia untuk dipesan</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2 rounded-pill fw-bold">Simpan
                            Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>