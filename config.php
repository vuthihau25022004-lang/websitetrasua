<?php
session_start();

date_default_timezone_set('Asia/Ho_Chi_Minh');

$host = 'localhost';
$dbname = 'hana_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    exit(
        'Không thể kết nối database. ' .
        'Hãy kiểm tra config.php và database.sql.'
    );
}

const TEN_QUAN = 'HaNa';
const SDT_QUAN = '0846919512';
const DIA_CHI_QUAN = '617 Đường Liên Thôn';

function e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function tien($value): string
{
    return number_format(
        (float)$value,
        0,
        ',',
        '.'
    ) . 'đ';
}

function yeuCauDangNhap(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
?>
