<?php
require 'config.php';

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
    || empty($_SESSION['cart'])
) {
    header('Location: cart.php');
    exit;
}

$name = trim($_POST['TenKhach'] ?? '');
$phone = trim($_POST['SDT'] ?? '');
$address = trim($_POST['DiaChi'] ?? '');
$note = trim($_POST['GhiChu'] ?? '');

if ($name === '' || $phone === '' || $address === '') {
    exit('Vui lòng nhập đủ họ tên, số điện thoại và địa chỉ.');
}

if (!preg_match('/^[0-9 +().-]{9,20}$/', $phone)) {
    exit('Số điện thoại chưa hợp lệ. Hãy quay lại và kiểm tra.');
}

try {
    $pdo->beginTransaction();

    $ids = array_keys($_SESSION['cart']);
    $marks = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare(
        "SELECT MaSP, TenSP, Gia
         FROM SanPham
         WHERE TrangThai = 1
         AND MaSP IN ($marks)"
    );
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    if (count($products) !== count($ids)) {
        throw new Exception('Một số món không còn khả dụng.');
    }

    $total = 0;

    foreach ($products as $p) {
        $total += (float)$p['Gia']
            * (int)$_SESSION['cart'][$p['MaSP']];
    }

    $stmt = $pdo->prepare(
        "INSERT INTO DonHang
        (TenKhach, SDT, DiaChi, GhiChu, TongTien)
        VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $name,
        $phone,
        $address,
        $note,
        $total
    ]);

    $orderId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare(
        "INSERT INTO ChiTietDonHang
        (MaDH, MaSP, TenSP, DonGia, SoLuong, Topping, ThanhTien)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    foreach ($products as $p) {
        $qty = (int)$_SESSION['cart'][$p['MaSP']];
        $lineTotal = (float)$p['Gia'] * $qty;

        $stmt->execute([
            $orderId,
            $p['MaSP'],
            $p['TenSP'],
            $p['Gia'],
            $qty,
            '',
            $lineTotal
        ]);
    }

    $pdo->commit();
    $_SESSION['cart'] = [];

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    exit('Không thể tạo đơn hàng. Vui lòng thử lại.');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công | HaNa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main class="container">
<section>
    <div class="form-card">
        <div class="eyebrow">HaNa cảm ơn bạn</div>

        <h1 style="font-size:36px">
            Đặt hàng thành công!
        </h1>

        <p>
            Mã đơn hàng của bạn là
            <strong>#<?= $orderId ?></strong>.
        </p>

        <p>
            Tổng tiền:
            <strong class="price"><?= tien($total) ?></strong>
        </p>

        <div class="notice">
            Hãy lưu lại số điện thoại đã đặt
            để tra cứu trạng thái đơn hàng.
        </div>

        <a
            class="btn"
            href="tra-cuu.php?phone=<?= urlencode($phone) ?>"
        >
            Tra cứu đơn hàng
        </a>

        <a class="btn btn-light" href="index.php">
            Về trang chủ
        </a>
    </div>
</section>
</main>

</body>
</html>
