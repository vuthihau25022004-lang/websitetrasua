<?php
require 'config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = trim($_POST['MaSP'] ?? '');

    if ($action === 'add' && $id !== '') {
        $stmt = $pdo->prepare(
            "SELECT MaSP FROM SanPham
             WHERE MaSP = ? AND TrangThai = 1"
        );
        $stmt->execute([$id]);

        if ($stmt->fetch()) {
            $_SESSION['cart'][$id] = min(
                50,
                (int)($_SESSION['cart'][$id] ?? 0) + 1
            );
        }
    } elseif ($action === 'update') {
        foreach (($_POST['qty'] ?? []) as $pid => $qty) {
            $qty = max(0, min(50, (int)$qty));

            if ($qty === 0) {
                unset($_SESSION['cart'][$pid]);
            } else {
                $_SESSION['cart'][$pid] = $qty;
            }
        }
    } elseif (
        $action === 'remove'
        && isset($_SESSION['cart'][$id])
    ) {
        unset($_SESSION['cart'][$id]);
    }

    header('Location: cart.php');
    exit;
}

$items = [];
$total = 0;

if ($_SESSION['cart']) {
    $ids = array_keys($_SESSION['cart']);
    $marks = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare(
        "SELECT MaSP, TenSP, Gia
         FROM SanPham
         WHERE TrangThai = 1
         AND MaSP IN ($marks)"
    );
    $stmt->execute($ids);

    foreach ($stmt->fetchAll() as $p) {
        $p['qty'] = (int)$_SESSION['cart'][$p['MaSP']];
        $p['line'] = $p['qty'] * (float)$p['Gia'];

        $total += $p['line'];
        $items[] = $p;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng | HaNa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="container nav">
        <a class="logo" href="index.php">
            HaNa
            <small>GIỎ HÀNG</small>
        </a>

        <nav>
            <a href="index.php">← Tiếp tục chọn món</a>
            <a href="tra-cuu.php">Tra cứu đơn</a>
        </nav>
    </div>
</header>

<main class="container">
<section>
    <h2>Giỏ hàng của bạn</h2>

    <?php if (!$items): ?>
        <div class="notice">
            Giỏ hàng đang trống.
            <a href="index.php">
                <strong>Chọn đồ uống tại đây.</strong>
            </a>
        </div>
    <?php else: ?>

        <form method="post">
            <input type="hidden" name="action" value="update">

            <?php foreach ($items as $it): ?>
                <div class="cart-line">
                    <div>
                        <strong><?= e($it['TenSP']) ?></strong>

                        <div class="muted">
                            <?= tien($it['Gia']) ?> / món
                        </div>
                    </div>

                    <input
                        type="number"
                        min="0"
                        max="50"
                        name="qty[<?= e($it['MaSP']) ?>]"
                        value="<?= $it['qty'] ?>"
                    >

                    <div class="line-total">
                        <strong><?= tien($it['line']) ?></strong>

                        <button
                            class="btn btn-danger btn-small"
                            form="rm-<?= e($it['MaSP']) ?>"
                        >
                            Xóa
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <p style="text-align:right;font-size:20px">
                Tổng cộng:
                <strong class="price">
                    <?= tien($total) ?>
                </strong>
            </p>

            <button class="btn btn-light">
                Cập nhật số lượng
            </button>
        </form>

        <?php foreach ($items as $it): ?>
            <form
                id="rm-<?= e($it['MaSP']) ?>"
                method="post"
            >
                <input type="hidden" name="action" value="remove">
                <input
                    type="hidden"
                    name="MaSP"
                    value="<?= e($it['MaSP']) ?>"
                >
            </form>
        <?php endforeach; ?>

        <div class="form-card" style="margin-top:24px">
            <h2>Thông tin nhận hàng</h2>

            <form action="dat-hang.php" method="post">
                <div class="two-col">
                    <div>
                        <label>Họ và tên *</label>
                        <input
                            name="TenKhach"
                            required
                            maxlength="100"
                        >
                    </div>

                    <div>
                        <label>Số điện thoại *</label>
                        <input
                            name="SDT"
                            required
                            pattern="[0-9 +().-]{9,20}"
                            maxlength="20"
                            placeholder="Số điện thoại để tra cứu"
                        >
                    </div>
                </div>

                <label>Địa chỉ nhận hàng *</label>
                <input
                    name="DiaChi"
                    required
                    maxlength="255"
                    placeholder="Nhập địa chỉ giao hàng"
                >

                <label>Ghi chú</label>
                <textarea
                    name="GhiChu"
                    rows="3"
                    maxlength="500"
                    placeholder="Ít đá, ít đường..."
                ></textarea>

                <p class="muted">
                    Đơn hàng sẽ ở trạng thái
                    “Chờ xác nhận” sau khi đặt thành công.
                </p>

                <button class="btn">
                    Xác nhận đặt hàng
                </button>
            </form>
        </div>

    <?php endif; ?>
</section>
</main>

<footer>
    <div class="container">
        <strong>HaNa</strong>
        · <?= SDT_QUAN ?>
        · <?= DIA_CHI_QUAN ?>
    </div>
</footer>

</body>
</html>
