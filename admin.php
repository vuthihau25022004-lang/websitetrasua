<?php
require 'config.php';
yeuCauDangNhap();

$today = new DateTimeImmutable('today');

$weekStart = $today
    ->modify('monday this week')
    ->format('Y-m-d 00:00:00');

$nextWeek = $today
    ->modify('monday next week')
    ->format('Y-m-d 00:00:00');

$monthStart = $today
    ->modify('first day of this month')
    ->format('Y-m-d 00:00:00');

$nextMonth = $today
    ->modify('first day of next month')
    ->format('Y-m-d 00:00:00');

function statOrders(
    PDO $pdo,
    string $from,
    string $to
): array {
    $stmt = $pdo->prepare(
        "SELECT
            COUNT(*) AS so_don,
            COALESCE(
                SUM(
                    CASE
                        WHEN TrangThai <> 'Đã hủy'
                        THEN TongTien
                        ELSE 0
                    END
                ),
                0
            ) AS doanh_thu
         FROM DonHang
         WHERE NgayDat >= ? AND NgayDat < ?"
    );

    $stmt->execute([$from, $to]);

    return $stmt->fetch();
}

$week = statOrders($pdo, $weekStart, $nextWeek);
$month = statOrders($pdo, $monthStart, $nextMonth);

$totalProducts = (int)$pdo->query(
    "SELECT COUNT(*) FROM SanPham WHERE TrangThai = 1"
)->fetchColumn();

$pending = (int)$pdo->query(
    "SELECT COUNT(*) FROM DonHang
     WHERE TrangThai = 'Chờ xác nhận'"
)->fetchColumn();

$orders = $pdo->query(
    "SELECT * FROM DonHang
     ORDER BY NgayDat DESC LIMIT 8"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị HaNa</title>
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

        <div>
            Xin chào, <?= e($_SESSION['admin_user'] ?? 'Admin') ?>
            · <a href="logout.php">Đăng xuất</a>
        </div>
    </div>
</div>

<main class="container admin-main">
    <h1 style="font-size:32px">Tổng quan cửa hàng</h1>

    <p class="muted">
        Số liệu được tổng hợp từ đơn hàng trong cơ sở dữ liệu.
    </p>

    <div class="stats">
        <div class="stat">
            <span>Món đang bán</span>
            <strong><?= $totalProducts ?></strong>
        </div>

        <div class="stat">
            <span>Đơn chờ xác nhận</span>
            <strong><?= $pending ?></strong>
        </div>

        <div class="stat">
            <span>Đơn tuần này</span>
            <strong><?= (int)$week['so_don'] ?></strong>
            <span>Doanh thu: <?= tien($week['doanh_thu']) ?></span>
        </div>

        <div class="stat">
            <span>Đơn tháng này</span>
            <strong><?= (int)$month['so_don'] ?></strong>
            <span>Doanh thu: <?= tien($month['doanh_thu']) ?></span>
        </div>
    </div>

    <div class="panel">
        <div class="section-head">
            <h2>Đơn hàng mới nhất</h2>
            <a class="btn btn-small" href="orders.php">Xem tất cả</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>SĐT</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?= $o['MaDH'] ?></td>
                        <td><?= e($o['TenKhach']) ?></td>
                        <td><?= e($o['SDT']) ?></td>
                        <td><?= e($o['NgayDat']) ?></td>
                        <td><?= tien($o['TongTien']) ?></td>
                        <td><?= e($o['TrangThai']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$orders): ?>
                    <tr>
                        <td colspan="6">Chưa có đơn hàng.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
