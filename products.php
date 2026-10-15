<?php
require 'config.php';
yeuCauDangNhap();

$cats = $pdo->query(
    "SELECT * FROM DanhMuc ORDER BY MaDM"
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = trim($_POST['MaSP'] ?? '');
        $name = trim($_POST['TenSP'] ?? '');
        $price = (float)($_POST['Gia'] ?? 0);
        $qty = (int)($_POST['SoLuong'] ?? 0);
        $desc = trim($_POST['MoTa'] ?? '');
        $cat = (int)($_POST['MaDM'] ?? 0);
        $status = isset($_POST['TrangThai']) ? 1 : 0;

        if ($name !== '' && $price >= 0 && $qty >= 0 && $cat > 0) {
            if ($id === '') {
                $id = 'SP' . strtoupper(bin2hex(random_bytes(3)));

                $stmt = $pdo->prepare(
                    "INSERT INTO SanPham
                    (MaSP, TenSP, Gia, SoLuong, MoTa, MaDM, TrangThai)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    $id, $name, $price, $qty,
                    $desc, $cat, $status
                ]);
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE SanPham
                     SET TenSP = ?, Gia = ?, SoLuong = ?,
                         MoTa = ?, MaDM = ?, TrangThai = ?
                     WHERE MaSP = ?"
                );

                $stmt->execute([
                    $name, $price, $qty,
                    $desc, $cat, $status, $id
                ]);
            }
        }
    } elseif ($action === 'toggle') {
        $stmt = $pdo->prepare(
            "UPDATE SanPham
             SET TrangThai = 1 - TrangThai
             WHERE MaSP = ?"
        );

        $stmt->execute([$_POST['MaSP'] ?? '']);
    }

    header('Location: products.php');
    exit;
}

$edit = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare(
        "SELECT * FROM SanPham WHERE MaSP = ?"
    );

    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}

$products = $pdo->query(
    "SELECT sp.*, dm.TenDM
     FROM SanPham sp
     JOIN DanhMuc dm ON sp.MaDM = dm.MaDM
     ORDER BY sp.NgayTao DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý món | HaNa</title>
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
    <h1 style="font-size:32px">Quản lý đồ uống</h1>

    <div class="form-card">
        <h2>
            <?= $edit ? 'Sửa món: ' . e($edit['TenSP']) : 'Thêm món mới' ?>
        </h2>

        <form method="post">
            <input type="hidden" name="action" value="save">

            <input
                type="hidden"
                name="MaSP"
                value="<?= e($edit['MaSP'] ?? '') ?>"
            >

            <div class="two-col">
                <div>
                    <label>Tên món *</label>
                    <input
                        name="TenSP"
                        required
                        maxlength="100"
                        value="<?= e($edit['TenSP'] ?? '') ?>"
                    >
                </div>

                <div>
                    <label>Danh mục *</label>
                    <select name="MaDM" required>
                        <?php foreach ($cats as $c): ?>
                            <option
                                value="<?= $c['MaDM'] ?>"
                                <?= (int)($edit['MaDM'] ?? 0) === (int)$c['MaDM'] ? 'selected' : '' ?>
                            >
                                <?= e($c['TenDM']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Giá (VNĐ) *</label>
                    <input
                        type="number"
                        min="0"
                        step="1000"
                        name="Gia"
                        required
                        value="<?= e($edit['Gia'] ?? '') ?>"
                    >
                </div>

                <div>
                    <label>Số lượng</label>
                    <input
                        type="number"
                        min="0"
                        name="SoLuong"
                        value="<?= e($edit['SoLuong'] ?? 100) ?>"
                    >
                </div>
            </div>

            <label>Mô tả</label>
            <input
                name="MoTa"
                maxlength="200"
                value="<?= e($edit['MoTa'] ?? '') ?>"
            >

            <label>
                <input
                    style="width:auto"
                    type="checkbox"
                    name="TrangThai"
                    <?= !isset($edit['TrangThai']) || (int)$edit['TrangThai'] === 1 ? 'checked' : '' ?>
                >
                Đang hiển thị trên menu
            </label>

            <button class="btn" style="margin-top:12px">
                <?= $edit ? 'Lưu thay đổi' : 'Thêm món' ?>
            </button>

            <?php if ($edit): ?>
                <a class="btn btn-light" href="products.php">
                    Hủy sửa
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="panel" style="margin-top:24px">
        <h2>Danh sách món</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mã món</th>
                        <th>Tên món</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Hiển thị</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= e($p['MaSP']) ?></td>
                        <td><?= e($p['TenSP']) ?></td>
                        <td><?= e($p['TenDM']) ?></td>
                        <td><?= tien($p['Gia']) ?></td>
                        <td><?= $p['SoLuong'] ?></td>
                        <td>
                            <?= $p['TrangThai'] ? 'Đang bán' : 'Đã ẩn' ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a
                                    class="btn btn-small btn-light"
                                    href="products.php?edit=<?= urlencode($p['MaSP']) ?>"
                                >
                                    Sửa
                                </a>

                                <form
                                    method="post"
                                    onsubmit="return confirm('Bạn có chắc muốn đổi trạng thái hiển thị món này?')"
                                >
                                    <input
                                        type="hidden"
                                        name="action"
                                        value="toggle"
                                    >

                                    <input
                                        type="hidden"
                                        name="MaSP"
                                        value="<?= e($p['MaSP']) ?>"
                                    >

                                    <button
                                        class="btn btn-small <?= $p['TrangThai'] ? 'btn-danger' : '' ?>"
                                    >
                                        <?= $p['TrangThai'] ? 'Ẩn món' : 'Hiện món' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="muted">
            Để giữ lịch sử đơn hàng, món đã có giao dịch
            được ẩn thay vì xóa vĩnh viễn.
        </p>
    </div>
</main>

</body>
</html>
