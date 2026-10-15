<?php
require 'config.php';

$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT sp.*, dm.TenDM
    FROM SanPham sp
    JOIN DanhMuc dm ON sp.MaDM = dm.MaDM
    WHERE sp.TrangThai = 1
";

$params = [];

if ($search !== '') {
    $sql .= "
        AND (
            sp.TenSP LIKE ?
            OR sp.MoTa LIKE ?
            OR dm.TenDM LIKE ?
        )
    ";

    $like = "%$search%";
    $params = [$like, $like, $like];
}

$sql .= " ORDER BY dm.MaDM, sp.TenSP";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query(
    "SELECT * FROM DanhMuc ORDER BY MaDM"
)->fetchAll();

$byCategory = [];

foreach ($products as $p) {
    $byCategory[$p['TenDM']][] = $p;
}

$cartCount = array_sum(
    array_map('intval', $_SESSION['cart'] ?? [])
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>HaNa | Trà sữa & đồ uống</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="container nav">
        <a class="logo" href="index.php">
            HaNa
            <small>TEA • COFFEE • YOGURT</small>
        </a>

        <nav>
            <a href="index.php">Trang chủ</a>
            <a href="#menu">Menu</a>
            <a href="cart.php">Giỏ hàng (<?= $cartCount ?>)</a>
            <a href="tra-cuu.php">Tra cứu đơn</a>
            <a href="login.php" class="admin-login">Đăng nhập quản trị</a>
        </nav>
    </div>
</header>

<div class="hero">
    <div class="container hero-grid">
        <div>
            <div class="eyebrow">Tươi ngon mỗi ngày</div>

            <h1>
                Một chút ngọt ngào,<br>
                một ngày thật vui.
            </h1>

            <p>
                Chào mừng bạn đến với HaNa.
                Chọn món yêu thích, thêm topping và
                đặt đồ uống trực tuyến thật dễ dàng.
            </p>

            <a class="btn" href="#menu">
                Khám phá menu ↓
            </a>
        </div>

        <div class="hero-note">
            <div class="eyebrow">HaNa gợi ý</div>

            <strong>
                Trà sữa • Trà trái cây • Coffee
            </strong>

            <p>
                Nguyên liệu thơm ngon, menu đa dạng,
                phù hợp cho những buổi học tập và
                gặp gỡ bạn bè.
            </p>

            <span class="muted">
                Đặt hàng nhanh chóng ngay trên website.
            </span>
        </div>
    </div>
</div>

<main class="container">
    <section id="menu">
        <div class="section-head">
            <div>
                <div class="eyebrow">Thực đơn HaNa</div>
                <h2>Chọn món bạn yêu thích</h2>

                <div class="muted">
                    Giỏ hàng của bạn có <?= $cartCount ?> món.
                </div>
            </div>

            <form class="searchbar" method="get">
                <input
                    name="q"
                    value="<?= e($search) ?>"
                    placeholder="Tìm trà sữa, matcha, coffee..."
                >

                <button class="btn">Tìm món</button>

                <?php if ($search !== ''): ?>
                    <a class="btn btn-light" href="index.php">
                        Xóa
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="category-links">
            <?php foreach ($categories as $c): ?>
                <a
                    class="chip"
                    href="#dm-<?= $c['MaDM'] ?>"
                >
                    <?= e($c['TenDM']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!$products): ?>
            <div class="notice">
                Không tìm thấy món phù hợp.
                Bạn hãy thử từ khóa khác nhé.
            </div>
        <?php endif; ?>

        <?php foreach ($categories as $c): ?>
            <?php
            $items = $byCategory[$c['TenDM']] ?? [];

            if (!$items) {
                continue;
            }
            ?>

            <div
                class="category-section"
                id="dm-<?= $c['MaDM'] ?>"
            >
                <div class="category-title">
                    <h2><?= e($c['TenDM']) ?></h2>

                    <span class="muted">
                        <?= count($items) ?> món
                    </span>
                </div>

                <div class="drink-grid">
                    <?php foreach ($items as $p): ?>
                        <article class="drink">
                            <div class="cup">🥤</div>

                            <h3><?= e($p['TenSP']) ?></h3>

                            <p><?= e($p['MoTa']) ?></p>

                            <div class="drink-bottom">
                                <span class="price">
                                    <?= tien($p['Gia']) ?>
                                </span>

                                <form
                                    action="cart.php"
                                    method="post"
                                >
                                    <input
                                        type="hidden"
                                        name="action"
                                        value="add"
                                    >

                                    <input
                                        type="hidden"
                                        name="MaSP"
                                        value="<?= e($p['MaSP']) ?>"
                                    >

                                    <button class="btn btn-small">
                                        + Đặt món
                                    </button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<footer>
    <div class="container footer-grid">
        <div>
            <strong>HaNa – Trà sữa & đồ uống</strong>
            <div>Hân hạnh phục vụ bạn mỗi ngày.</div>
        </div>

        <div>
            <div>
                <strong>Điện thoại:</strong>
                <?= SDT_QUAN ?>
            </div>

            <div>
                <strong>Địa chỉ:</strong>
                <?= DIA_CHI_QUAN ?>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
