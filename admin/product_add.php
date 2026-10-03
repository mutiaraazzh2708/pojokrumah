<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

checkSuperAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $category = $_POST['category'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    // Image Upload Handling
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = $_FILES['image']['type'];
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
                $image = $new_filename;
            } else {
                $error = "Gagal mengupload gambar.";
            }
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, image, is_available) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssi", $name, $description, $price, $category, $image, $is_available);

        if ($stmt->execute()) {
            $success = "Menu berhasil ditambahkan.";
            // Reset form
            $name = '';
            $description = '';
            $price = '';
            $category = '';
        } else {
            $error = "Gagal menyimpan data: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<?php require_once '../includes/admin_header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Tambah Menu Baru</h5>
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
                            value="<?= isset($name) ? $name : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control"
                            rows="3"><?= isset($description) ? $description : '' ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" required
                                value="<?= isset($price) ? $price : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="food">Makanan</option>
                                <option value="drink">Minuman</option>
                                <option value="snack">Camilan</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto Menu</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="form-text">Format: JPG, PNG, WebP. Maks 2MB.</div>
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_available" id="isAvailable" checked>
                        <label class="form-check-label" for="isAvailable">Tersedia untuk dipesan</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2 rounded-pill fw-bold">Simpan Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>