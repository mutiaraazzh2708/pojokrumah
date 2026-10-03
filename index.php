<?php
require_once './config/database.php';
require_once './includes/functions.php';

// Fetch products grouped by category
$categories = ['food' => 'Makanan Utama', 'drink' => 'Minuman Kekinian', 'snack' => 'Camilan Santai'];
$products = [];

foreach ($categories as $key => $label) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND is_available = 1 ORDER BY name ASC");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $products[$key] = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<?php require_once './includes/header.php'; ?>

<!-- Animation Library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<style>
    /* Hero Section */
    .hero-wrapper {
        position: relative;
        height: 100vh;
        min-height: 600px;
        background: url('https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?q=80&w=2678&auto=format&fit=crop') no-repeat center center;
        background-size: cover;
        background-attachment: fixed;
        /* Parallax Effect */
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.8));
        z-index: 1;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
    }

    .hero-title {
        font-size: 5rem;
        font-weight: 800;
        text-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        letter-spacing: -2px;
        opacity: 0;
        /* For animation */
        animation: fadeInUp 1s ease-out forwards 0.5s;
    }

    .hero-subtitle {
        font-size: 1.5rem;
        font-weight: 300;
        opacity: 0;
        animation: fadeInUp 1s ease-out forwards 0.8s;
    }

    .btn-hero {
        padding: 15px 40px;
        font-size: 1.1rem;
        border-radius: 50px;
        transition: all 0.3s;
        opacity: 0;
        animation: fadeInUp 1s ease-out forwards 1.1s;
    }

    .btn-hero:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(212, 163, 115, 0.4);
    }

    /* Scroll Down Indicator */
    .scroll-down {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
        animation: bounce 2s infinite;
        opacity: 0;
        animation: fadeInUp 1s ease-out forwards 1.5s, bounce 2s infinite 2s;
    }

    /* Modern Card */
    .product-card {
        border-radius: 20px;
        border: none;
        background: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .product-image-container {
        height: 200px;
        overflow: hidden;
        position: relative;
        border-radius: 20px 20px 0 0;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .product-card:hover .product-image {
        transform: scale(1.05);
    }

    /* Category Header Pill */
    .category-header {
        display: inline-flex;
        align-items: center;
        background-color: #212529;
        color: white;
        padding: 10px 25px;
        border-radius: 50px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .category-header i {
        margin-right: 10px;
        font-size: 1.2rem;
    }

    .category-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
    }

    /* Add Button Styling */
    /* Add Button Styling */
    .btn-add {
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 8px 0;
        width: 100%;
        font-weight: 600;
        transition: all 0.2s;
        box-shadow: 0 4px 6px rgba(212, 163, 115, 0.2);
    }

    .btn-add:hover {
        background-color: var(--primary-hover);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(212, 163, 115, 0.3);
    }

    /* Quantity Control */
    .qty-control {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--primary-color);
        border-radius: 12px;
        padding: 5px;
        width: 100%;
        box-shadow: 0 4px 6px rgba(212, 163, 115, 0.2);
        height: 40px;
        /* Match typical btn height */
    }

    .btn-qty {
        background: rgba(255, 255, 255, 0.25);
        color: white;
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-weight: 700;
        font-size: 1.2rem;
        transition: all 0.2s;
        line-height: 1;
        padding: 0;
    }

    .btn-qty:hover {
        background: rgba(255, 255, 255, 0.45);
    }

    .qty-display {
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
    }

    /* Scroll Reveal Animation Classes */
    .reveal {
        position: relative;
        transform: translateY(50px);
        opacity: 0;
        transition: all 0.8s ease;
    }

    .reveal.active {
        transform: translateY(0);
        opacity: 1;
    }

    /* Decorations */
    .section-header {
        position: relative;
        display: inline-block;
        margin-bottom: 3rem;
    }

    .section-header::after {
        content: '';
        display: block;
        width: 50px;
        height: 4px;
        background: var(--primary-color);
        margin: 15px auto 0;
        border-radius: 2px;
    }

    @keyframes bounce {

        0%,
        20%,
        50%,
        80%,
        100% {
            transform: translateX(-50%) translateY(0);
        }

        40% {
            transform: translateX(-50%) translateY(-10px);
        }

        60% {
            transform: translateX(-50%) translateY(-5px);
        }
    }

    /* Contact Links Styling */
    .contact-link {
        transition: all 0.3s ease !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
    }

    .contact-link:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3) !important;
    }

    .contact-link.btn-success {
        background: linear-gradient(135deg, #25D366, #128C7E) !important;
        border: none !important;
    }

    .contact-link.btn-success:hover {
        background: linear-gradient(135deg, #128C7E, #25D366) !important;
    }

    .contact-link.btn-outline-light:hover {
        background: linear-gradient(135deg, #E1306C, #C13584) !important;
        border-color: transparent !important;
    }

    .contact-card {
        transition: all 0.3s ease !important;
    }

    .contact-card:hover {
        border-color: rgba(255, 255, 255, 0.3) !important;
        background: rgba(255, 255, 255, 0.05) !important;
    }

    /* Location Card & Link Styling */
    .location-card {
        transition: all 0.3s ease !important;
    }

    .location-card:hover {
        border-color: rgba(255, 193, 7, 0.5) !important;
        background: rgba(255, 193, 7, 0.05) !important;
    }

    .location-link {
        transition: all 0.3s ease !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        background: linear-gradient(135deg, #FFC107, #FF9800) !important;
        border: none !important;
        color: #000 !important;
    }

    .location-link:hover {
        background: linear-gradient(135deg, #FF9800, #FFC107) !important;
        transform: translateY(-3px) !important;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4) !important;
        color: #000 !important;
    }

    /* Floating Cart Button */
    .floating-cart-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1050;
        display: none;
        /* Hidden by default, shown via JS/PHP if items exist */
    }

    .btn-floating-pesan {
        background-color: var(--primary-color);
        color: white;
        padding: 15px 30px;
        font-size: 1.2rem;
        font-weight: 700;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        border: none;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .btn-floating-pesan:hover {
        background-color: var(--primary-hover);
        color: white;
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 3rem;
        }

        .floating-cart-container {
            bottom: 20px;
            right: 20px;
            left: 20px;
            /* Full width on mobile maybe? Or just comfortable right */
            width: auto;
            display: flex;
            justify-content: flex-end;
            /* Or center if we want full width feel */
        }
    }
</style>

<!-- Hero Section -->
<header class="hero-wrapper">
    <div class="hero-overlay"></div>
    <div class="hero-content text-white container">
        <h1 class="hero-title lh-1 mb-3">Pojok Rumah</h1>
        <p class="hero-subtitle mb-5 text-white-50 col-lg-8 mx-auto">
            Tempat nongkrong asik, nugas nyaman, dan seru buat kumpul komunitas. Nikmati suasana outdoor dan bebas
            ekspresi.
        </p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="#menu-list" class="btn btn-primary btn-hero shadow fw-bold border-0"
                style="background-color: #d4a373;">
                <i class="bi bi-book me-2"></i>Lihat Menu
            </a>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-outline-light btn-hero fw-bold">
                    Gabung Sekarang
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="scroll-down text-white">
        <p class="small text-uppercase tracking-wide mb-1">Scroll</p>
        <i class="bi bi-chevron-down fs-4"></i>
    </div>
</header>

<!-- Features Section (Floating) -->
<section class="container" style="margin-top: -80px; position: relative; z-index: 3;">
    <div class="row g-4 reveal">
        <div class="col-md-4">
            <div class="bg-white p-5 rounded-4 shadow-lg text-center h-100 hover-lift">
                <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-4">
                    <i class="bi bi-wifi fs-2"></i>
                </div>
                <h4 class="fw-bold fs-5">Free WiFi 100Mbps</h4>
                <p class="text-muted small">Nongkrong lama tetap asik dengan koneksi internet ngebut.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white p-5 rounded-4 shadow-lg text-center h-100 hover-lift">
                <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-4">
                    <i class="bi bi-tree-fill fs-2"></i>
                </div>
                <h4 class="fw-bold fs-5">Outdoor Vibe</h4>
                <p class="text-muted small">Nikmati udara segar dan suasana santai tanpa sekat ruangan.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white p-5 rounded-4 shadow-lg text-center h-100 hover-lift">
                <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-4">
                    <i class="bi bi-wallet2 fs-2"></i>
                </div>
                <h4 class="fw-bold fs-5">Harga Bersahabat</h4>
                <p class="text-muted small">Rasa bintang lima, harga tetap ramah di kantong pelajar.</p>
            </div>
        </div>
    </div>
</section>

<!-- Menu Section -->
<section id="menu-list" class="py-5 my-5">
    <div class="container reveal">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase tracking-wide">Our Favorites</span>
            <h2 class="display-5 fw-bold mt-2 section-header">Menu Pilihan</h2>
            <p class="text-muted col-lg-6 mx-auto">Kami menyajikan berbagai pilihan rasa yang dibuat dengan hati dan
                bahan berkualitas tinggi.</p>
        </div>

        <!-- Category Tabs (Visual only for vertical scroll spy effect or simplified filter logic could be added) -->
        <div class="d-flex justify-content-center gap-3 mb-5 flex-wrap">
            <?php foreach ($categories as $catKey => $catLabel): ?>
                <a href="#cat-<?= $catKey ?>" class="btn btn-outline-dark rounded-pill px-4 border-2"><?= $catLabel ?></a>
            <?php endforeach; ?>
        </div>

        <?php foreach ($categories as $catKey => $catLabel): ?>
            <?php if (!empty($products[$catKey])): ?>
                <div id="cat-<?= $catKey ?>" class="mb-5 py-4 reveal">
                    <!-- New Pill Header -->
                    <div class="category-header">
                        <i class="bi bi-tag-fill"></i>
                        <h3><?= $catLabel ?></h3>
                    </div>

                    <div class="row g-4">
                        <?php foreach ($products[$catKey] as $product): ?>
                            <div class="col-md-6 col-lg-3">
                                <div class="product-card">
                                    <div class="product-image-container">
                                        <img src="<?= $product['image'] ? BASE_URL . 'assets/uploads/' . $product['image'] : 'https://via.placeholder.com/400x400?text=No+Image' ?>"
                                            class="product-image" alt="<?= $product['name'] ?>">
                                    </div>
                                    <div class="p-4 d-flex flex-column flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="fw-bold text-dark mb-0 text-truncate"
                                                style="max-width: 65%; font-size: 1.1rem;">
                                                <?= htmlspecialchars($product['name']) ?>
                                            </h5>
                                            <span class="fw-bold text-primary" style="font-size: 1rem;">
                                                Rp <?= number_format($product['price'] / 1000, 0) ?>k
                                            </span>
                                        </div>

                                        <!-- Optional: Description hidden to match clean design -->
                                        <!-- <p class="text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?= htmlspecialchars($product['description']) ?>
                                        </p> -->

                                        <div class="mt-auto cart-action-container">
                                            <?php if (isLoggedIn()): ?>
                                                <?php
                                                $currentQty = isset($_SESSION['cart'][$product['id']]) ? $_SESSION['cart'][$product['id']] : 0;
                                                ?>
                                                <?php if ($currentQty > 0): ?>
                                                    <div class="qty-control">
                                                        <button class="btn-qty minus" data-id="<?= $product['id'] ?>">-</button>
                                                        <span class="qty-display"><?= $currentQty ?></span>
                                                        <button class="btn-qty plus" data-id="<?= $product['id'] ?>">+</button>
                                                    </div>
                                                <?php else: ?>
                                                    <button class="btn-add" data-id="<?= $product['id'] ?>">
                                                        Tambah
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="login.php" class="btn btn-add text-decoration-none text-center d-block">
                                                    Tambah
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- Promo / Testimonial Section -->
<section class="py-5 bg-light reveal">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="position-relative">
                    <img src="<?= BASE_URL ?>assets/images/testimonial.jpg" class="img-fluid rounded-4 shadow-lg"
                        alt="Testimonial">

                </div>
            </div>
            <div class="col-lg-6">
                <span class="text-primary fw-bold text-uppercase tracking-wide">Kata Mereka</span>
                <h2 class="display-5 fw-bold mb-4">"Tempat Nongkrong & Nugas Paling Asik!"</h2>
                <p class="lead text-muted mb-4">
                    Gak cuma buat ngopi, tapi pas banget buat nugas karena WiFi-nya kencang. Suasananya santai, cocok
                    juga buat kumpul komunitas atau acara ulang tahun.
                </p>
                <div class="d-flex gap-4 text-center">
                    <div>
                        <h3 class="fw-bold text-dark">500+</h3>
                        <small class="text-muted">Pelanggan Puas</small>
                    </div>
                    <div>
                        <h3 class="fw-bold text-dark">50+</h3>
                        <small class="text-muted">Menu Varian</small>
                    </div>
                    <div>
                        <h3 class="fw-bold text-dark">4.9</h3>
                        <small class="text-muted">Rating Google</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Location with Dark Theme -->
<section class="py-5 bg-dark text-white position-relative overflow-hidden reveal">
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25"
        style="background: url('https://www.transparenttextures.com/patterns/cubes.png');"></div>
    <div class="container position-relative z-2 text-center py-5">
        <h2 class="display-5 fw-bold mb-4">Kunjungi Kami</h2>
        <p class="lead text-white-50 mb-5">Kami menanti kehadiran Anda.</p>

        <div class="row justify-content-center g-4">
            <div class="col-md-3">
                <div class="border border-secondary p-4 rounded-3 h-100">
                    <i class="bi bi-clock text-warning fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold">Jam Buka</h5>
                    <p class="text-white-50 mb-0">Minggu - Kamis: 17:00 - 00:00<br>Jumat - Sabtu: 17:00 - 02:00</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border border-secondary p-4 rounded-3 h-100 location-card">
                    <i class="bi bi-geo-alt text-warning fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold">Lokasi</h5>
                    <p class="text-white-50 mb-3">Jl. Banuaran No. 11<br>RT 01 RW 07, Indonesia</p>
                    <a href="https://www.google.com/maps/search/?api=1&query=-0.9657686181628369,100.38729636795908"
                        target="_blank"
                        class="btn btn-warning btn-sm d-flex align-items-center justify-content-center gap-2 w-100 location-link">
                        <i class="bi bi-map"></i>
                        <span>Buka Google Maps</span>
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border border-secondary p-4 rounded-3 h-100 contact-card">
                    <i class="bi bi-telephone text-warning fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold">Kontak</h5>
                    <div class="d-flex flex-column gap-2">
                        <a href="https://wa.me/6281378405264?text=Halo%20Pojok%20Rumah%2C%20saya%20ingin%20bertanya..."
                            target="_blank"
                            class="btn btn-success btn-sm d-flex align-items-center justify-content-center gap-2 contact-link">
                            <i class="bi bi-whatsapp"></i>
                            <span>+62 813 7840 5264</span>
                        </a>
                        <a href="https://instagram.com/pojokrumah___" target="_blank"
                            class="btn btn-outline-light btn-sm d-flex align-items-center justify-content-center gap-2 contact-link">
                            <i class="bi bi-instagram"></i>
                            <span>@pojokrumah___</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Floating Cart Button -->
<?php
$initial_cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$display_style = $initial_cart_count > 0 ? 'flex' : 'none'; // Use flex to match .floating-cart-container display if shown
?>
<div id="floating-cart-wrapper" class="floating-cart-container" style="display: <?= $display_style ?>;">
    <a href="cart.php" class="btn-floating-pesan">
        <span>Pesan</span>
        <span class="badge bg-white text-dark rounded-pill ms-2"
            id="floating-cart-count"><?= $initial_cart_count ?></span>
    </a>
</div>

<!-- Simple Scroll Reveal Script -->
<script>
    window.addEventListener('scroll', reveal);

    function reveal() {
        var reveals = document.querySelectorAll('.reveal');

        for (var i = 0; i < reveals.length; i++) {
            var windowheight = window.innerHeight;
            var revealtop = reveals[i].getBoundingClientRect().top;
            var revealpoint = 150;

            if (revealtop < windowheight - revealpoint) {
                reveals[i].classList.add('active');
            }
        }
    }
    // Trigger once on load
    reveal();

    // AJAX Cart Logic
    document.addEventListener('DOMContentLoaded', function () {

        async function updateCart(productId, quantity, container) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            formData.append('ajax', 1);

            try {
                const response = await fetch('cart_add.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    renderButton(container, data.new_qty, productId);

                    // Update Floating Button
                    const floatingWrapper = document.getElementById('floating-cart-wrapper');
                    const floatingCount = document.getElementById('floating-cart-count');
                    
                    if (floatingWrapper && floatingCount) {
                        floatingCount.innerText = data.cart_count;
                        if (data.cart_count > 0) {
                            floatingWrapper.style.display = 'flex'; // Show
                        } else {
                            floatingWrapper.style.display = 'none'; // Hide
                        }
                    } else if (data.cart_count > 0) {
                         location.reload(); 
                    }
                }
            } catch (error) {
                console.error('Error updating cart:', error);
            }
        }

        function renderButton(container, qty, productId) {
            if (qty > 0) {
                container.innerHTML = `
                    <div class="qty-control">
                        <button class="btn-qty minus" data-id="${productId}">-</button>
                        <span class="qty-display">${qty}</span>
                        <button class="btn-qty plus" data-id="${productId}">+</button>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <button class="btn-add" data-id="${productId}">
                        Tambah
                    </button>
                `;
            }
        }

        // Event Delegation
        document.addEventListener('click', function (e) {
            const target = e.target;

            // Btn Add
            if (target.classList.contains('btn-add') && target.tagName === 'BUTTON') {
                e.preventDefault();
                const id = target.getAttribute('data-id');
                const container = target.closest('.cart-action-container');
                updateCart(id, 1, container);
            }

            // Btn Plus
            if (target.classList.contains('plus')) {
                e.preventDefault();
                const id = target.getAttribute('data-id');
                const container = target.closest('.cart-action-container');
                updateCart(id, 1, container);
            }

            // Btn Minus
            if (target.classList.contains('minus')) {
                e.preventDefault();
                const id = target.getAttribute('data-id');
                const container = target.closest('.cart-action-container');
                updateCart(id, -1, container);
            }
        });
    });
</script>

<?php require_once './includes/footer.php'; ?>