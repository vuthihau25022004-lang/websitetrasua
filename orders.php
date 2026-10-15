<?php
require 'config.php';
yeuCauDangNhap();

$statuses = [
    'Chờ xác nhận',
    'Đã xác nhận',
    'Đang pha chế',
    'Đang giao',
    'Hoàn thành',
    'Đã hủy'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['MaDH'] ?? 0);
    $status = $_POST['TrangThai'] ?? '';

    if (in_array($status, $statuses, true)) {
        $stmt = $pdo->prepare(
            "UPDATE DonHang SET TrangThai = ? WHERE MaDH = ?"
        );

        $stmt->execute([$status, $id]);
    }

    header('Location: orders.php');
    exit;
}

$filter = $_GET['status'] ?? '';

$sql = "SELECT * FROM DonHang";
$params = [];

if (in_array($filter, $statuses, true)) {
    $sql .= " WHERE TrangThai = ?";
    $params[] = $filter;
}

$sql .= " ORDER BY NgayDat DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng | HaNa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">

<div class="admin-nav">
    <div class="container">
        <div>
            <strong>HaNa Admin</strong>
            &nbsp;
            <a href="admin.php">Tổng quan</a>
            <a href="products.php">Quản lý món</a>
            <a href="orders.php">Đơn hàng</a>
        </div>

        <a href="logout.php">Đăng xuất</a>
    </div>
</div>

<main class="container admin-main">
    <h1 style="font-size:32px">Quản lý đơn hàng</h1>

    <form
        method="get"
        class="searchbar"
        style="margin:18px 0"
    >
        <select name="status">
            <option value="">Tất cả trạng thái</option>

            <?php foreach ($statuses as $st): ?>
                <option
                    value="<?= e($st) ?>"
                    <?= $filter === $st ? 'selected' : '' ?>
                >
                    <?= e($st) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn">Lọc</button>
    </form>

    <div class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Liên hệ/địa chỉ</th>
                        <th>Ngày đặt</th>
                        <th>Chi tiết</th>
                        <th>Tổng tiền</th>
                        <th>Cập nhật trạng thái</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?= $o['MaDH'] ?></td>

                        <td>
                            <?= e($o['TenKhach']) ?><br>
                            <small><?= e($o['SDT']) ?></small>
                        </td>

                        <td>
                            <?= e($o['DiaChi']) ?><br>
                            <small><?= e($o['GhiChu']) ?></small>
                        </td>

                        <td><?= e($o['NgayDat']) ?></td>

                        <td>
                            <details>
                                <summary>Xem món</summary>

                                <?php
                                $detail = $pdo->prepare(
                                    "SELECT * FROM ChiTietDonHang
                                     WHERE MaDH = ?"
                                );

                                $detail->execute([$o['MaDH']]);

                                foreach ($detail->fetchAll() as $item):
                                ?>
                                    <p>
                                        <?= e($item['TenSP']) ?>
                                        × <?= $item['SoLuong'] ?>
                                        — <?= tien($item['ThanhTien']) ?>
                                    </p>
                                <?php endforeach; ?>
                            </details>
                        </td>

                        <td><?= tien($o['TongTien']) ?></td>

                        <td>
                            <form method="post" class="inline-form">
                                <input
                                    type="hidden"
                                    name="MaDH"
                                    value="<?= $o['MaDH'] ?>"
                                >

                                <select name="TrangThai">
                                    <?php foreach ($statuses as $st): ?>
                                        <option
                                            value="<?= e($st) ?>"
                                            <?= $o['TrangThai'] === $st ? 'selected' : '' ?>
                                        >
                                            <?= e($st) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button class="btn btn-small">
                                    Lưu
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$orders): ?>
                    <tr>
                        <td colspan="7">
                            Không có đơn hàng phù hợp.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
