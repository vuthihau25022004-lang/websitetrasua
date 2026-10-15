<?php
require 'config.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['TenDangNhap'] ?? '');
    $pass = $_POST['MatKhau'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT * FROM Admin WHERE TenDangNhap = ?"
    );

    $stmt->execute([$user]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($pass, $admin['MatKhau'])) {
        session_regenerate_id(true);

        $_SESSION['admin_id'] = $admin['MaAdmin'];
        $_SESSION['admin_user'] = $admin['TenDangNhap'];

        header('Location: admin.php');
        exit;
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập quản trị | HaNa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
<section>
    <div class="form-card" style="max-width:440px;margin:35px auto">
        <a class="logo" href="index.php">HaNa</a>
        <h2 style="margin-top:18px">Đăng nhập quản trị</h2>

        <?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Tên đăng nhập</label>
            <input name="TenDangNhap" required>

            <label>Mật khẩu</label>
            <input type="password" name="MatKhau" required>

            <button class="btn" style="width:100%;margin-top:18px">
                Đăng nhập
            </button>
        </form>

        <p class="muted">
            <a href="index.php">← Về website khách hàng</a>
        </p>
    </div>
</section>
</main>
</body>
</html>
