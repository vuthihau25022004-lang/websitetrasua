<?php
require 'config.php';

$count = (int)$pdo->query(
    "SELECT COUNT(*) FROM Admin"
)->fetchColumn();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $count === 0) {
    $user = trim($_POST['TenDangNhap'] ?? '');
    $pass = $_POST['MatKhau'] ?? '';

    if (strlen($user) < 4 || strlen($pass) < 8) {
        $message = 'Tên đăng nhập tối thiểu 4 ký tự, mật khẩu tối thiểu 8 ký tự.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO Admin (TenDangNhap, MatKhau)
             VALUES (?, ?)"
        );

        $stmt->execute([
            $user,
            password_hash($pass, PASSWORD_DEFAULT)
        ]);

        $message = 'Đã tạo tài khoản quản trị. Hãy xóa file setup_admin.php khỏi thư mục website và đăng nhập.';
        $count = 1;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo quản trị HaNa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
<section>
    <div class="form-card" style="max-width:500px;margin:auto">
        <h2>Khởi tạo tài khoản quản trị</h2>

        <?php if ($message): ?>
            <div class="notice"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($count === 0): ?>
            <form method="post">
                <label>Tên đăng nhập</label>
                <input name="TenDangNhap" required minlength="4">

                <label>Mật khẩu (ít nhất 8 ký tự)</label>
                <input type="password" name="MatKhau" required minlength="8">

                <button class="btn" style="margin-top:15px">
                    Tạo tài khoản
                </button>
            </form>
        <?php else: ?>
            <p>Tài khoản quản trị đã tồn tại hoặc vừa được tạo.</p>
            <a class="btn" href="login.php">Đến trang đăng nhập</a>
        <?php endif; ?>
    </div>
</section>
</main>
</body>
</html>
