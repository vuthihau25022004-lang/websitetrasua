<?php
require 'config.php';

$phone = trim($_GET['phone'] ?? $_POST['SDT'] ?? '');
$orders = [];

if ($phone !== '') {
    $stmt = $pdo->prepare(
        "SELECT *
         FROM DonHang
         WHERE SDT = ?
         ORDER BY NgayDat DESC"
    );

    $stmt->execute([$phone]);
    $orders = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Tra cứu đơn hàng | HaNa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="container nav">
        <a class="logo" href="index.php">
            HaNa
            <small>TRA CỨU ĐƠN HÀNG</small>
        </a>

        <nav>
            <a href="index.php">Trang chủ</a>
            <a href="cart.php">Giỏ hàng</a>
        </nav>
    </div>
</header>

<main class="container">
<section>
    <div class="form-card">
        <h2>Tra cứu đơn hàng</h2>

        <p class="muted">
            Nhập đúng số điện thoại bạn đã sử dụng khi đặt hàng.
        </p>

        <form method="post">
            <label>Số điện thoại</label>

            <input
                name="SDT"
                required
                value="<?= e($phone) ?>"
                placeholder="Ví dụ: 09xxxxxxxx"
            >

            <button class="btn" style="margin-top:12px">
                Tra cứu
            </button>
        </form>
    </div>

    <?php if ($phone !== ''): ?>
        <h2 style="margin-top:28px">Kết quả tra cứu</h2>

        <?php if (!$orders): ?>
            <div class="notice">
                Chưa tìm thấy đơn hàng với số điện thoại này.
            </div>
        <?php else: ?>

            <?php foreach ($orders as $o): ?>
                <div class="panel" style="margin-bottom:12px">
                    <div class="section-head">
                        <div>
                            <strong>
                                Đơn hàng #<?= $o['MaDH'] ?>
                            </strong>

                            <div class="muted">
                                <?= e($o['NgayDat']) ?>
                            </div>
                        </div>

                        <strong><?= e($o['TrangThai']) ?></strong>
                    </div>

                    <p>
                        Người nhận: <?= e($o['TenKhach']) ?>
                        · Địa chỉ: <?= e($o['DiaChi']) ?>
                    </p>

                    <p>
                        Tổng tiền:
                        <strong class="price">
                            <?= tien($o['TongTien']) ?>
                        </strong>
                    </p>

                    <details>
                        <summary>Xem chi tiết món</summary>

                        <?php
                        $stmt = $pdo->prepare(
                            "SELECT *
                             FROM ChiTietDonHang
                             WHERE MaDH = ?"
                        );

                        $stmt->execute([$o['MaDH']]);

                        foreach ($stmt->fetchAll() as $d):
                        ?>
                            <p>
                                <?= e($d['TenSP']) ?>
                                × <?= $d['SoLuong'] ?>
                                — <?= tien($d['ThanhTien']) ?>
                            </p>
                        <?php endforeach; ?>
                    </details>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
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
