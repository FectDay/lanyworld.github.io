<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

if (!isset($_GET['id'])) {
    redirect('/shop.php');
}

// Проверяем, купил ли пользователь этот товар
$stmt = $pdo->prepare("
    SELECT products.* FROM products 
    JOIN orders ON products.id = orders.product_id 
    WHERE orders.user_id = ? AND products.id = ? AND orders.status = 'completed'
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id'], $_GET['id']]);
$product = $stmt->fetch();

if (!$product || !$product['file_path']) {
    die('Файл не найден');
}

$file = UPLOADS_PATH . basename($product['file_path']);

if (!file_exists($file)) {
    error_log("File not found: " . $file);
    die('Файл не найден');
}

// Защита от path traversal
if (strpos(realpath($file), realpath(UPLOADS_PATH)) !== 0) {
    error_log("Attempted path traversal: " . $file);
    die('Доступ запрещен');
}

// Получаем MIME-тип файла
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file);
finfo_close($finfo);

// Отправляем файл
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . basename($product['file_name']) . '"');
header('Content-Length: ' . filesize($file));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private');

// Отправляем файл частями для экономии памяти
$handle = fopen($file, 'rb');
while (!feof($handle)) {
    echo fread($handle, 8192);
    flush();
}
fclose($handle);
exit;