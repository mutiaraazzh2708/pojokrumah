<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

checkSuperAdmin(); // Only Superadmin can access

// Handle Add Admin
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role']; // 'admin' or 'superadmin'

    // Simple validation
    if (empty($name) || empty($email) || empty($password)) {
        $error_msg = "Semua kolom wajib diisi.";
    } else {
        // Check email
        $check = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error_msg = "Email sudah terdaftar.";
        } else {
            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_pw, $role);
            if ($stmt->execute()) {
                $success_msg = "Admin baru berhasil ditambahkan.";
            } else {
                $error_msg = "Gagal menambah admin: " . $conn->error;
            }
        }
    }
}

// Handle Delete Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['admin_id'];
    // Prevent self-delete
    if ($id == $_SESSION['user_id']) {
        $error_msg = "Anda tidak bisa menghapus akun sendiri.";
    } else {
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success_msg = "Admin berhasil dihapus.";
        } else {
            $error_msg = "Gagal menghapus admin.";
        }
    }
}

// Fetch all admins
$admins = $conn->query("SELECT * FROM admins ORDER BY id DESC");
?>

<?php require_once '../../includes/admin_header.php'; ?>

<!-- Animate CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
    <div>
        <h2 class="fw-bold text-dark display-6">Kelola Admin</h2>
        <p class="text-muted mb-0">Tambah atau hapus akun administrator.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal"
        data-bs-target="#addAdminModal">
        <i class="bi bi-person-plus-fill me-2"></i>Tambah Admin
    </button>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success animate__animated animate__fadeIn">
        <?= $success_msg ?>
    </div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="alert alert-danger animate__animated animate__fadeIn">
        <?= $error_msg ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden animate__animated animate__fadeInUp">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase small fw-bold text-muted">Nama</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted">Email</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted">Role</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted">Bergabung</th>
                        <th class="pe-4 py-3 text-end text-uppercase small fw-bold text-muted">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $admins->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <?= htmlspecialchars($row['name']) ?>
                                </div>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['email']) ?>
                            </td>
                            <td>
                                <?php if ($row['role'] === 'superadmin'): ?>
                                    <span
                                        class="badge bg-purple text-dark border border-purple border-opacity-25 bg-opacity-10 rounded-pill px-3">SUPERADMIN</span>
                                <?php else: ?>
                                    <span
                                        class="badge bg-secondary text-secondary border border-secondary border-opacity-25 bg-opacity-10 rounded-pill px-3">ADMIN</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?= date('d M Y', strtotime($row['created_at'])) ?>
                            </td>
                            <td class="pe-4 text-end">
                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" onsubmit="return confirm('Yakin ingin menghapus admin ini?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="admin_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle"
                                            title="Hapus">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border px-2">Anda</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add Admin -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Tambah Admin Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Email</label>
                        <input type="email" name="email" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Password</label>
                        <input type="password" name="password" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Role</label>
                        <select name="role" class="form-select rounded-3">
                            <option value="admin">Admin (Operasional)</option>
                            <option value="superadmin">Superadmin (Akses Penuh)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">Simpan Admin</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/admin_footer.php'; ?>