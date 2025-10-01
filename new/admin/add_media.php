<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    die(json_encode(['success' => false, 'error' => 'Доступ запрещен']));
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die(json_encode(['success' => false, 'error' => 'Ошибка при загрузке файла']));
}

$product_id = $_POST['product_id'] ?? null;
if (!$product_id) {
    die(json_encode(['success' => false, 'error' => 'ID товара не указан']));
}


$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $_FILES['image']['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    die(json_encode(['success' => false, 'error' => 'Недопустимый тип файла']));
}

$upload_dir = '../assets/images/products/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '.' . $extension;
$filepath = $upload_dir . $filename;
$url = SITE_URL . '/assets/images/products/' . $filename;

// Перемещаем загруженный файл
if (!move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
    die(json_encode(['success' => false, 'error' => 'Ошибка при сохранении файла']));
}

// Добавляем запись в базу данных
try {
    $stmt = $pdo->prepare("
        INSERT INTO product_media (product_id, type, url) 
        VALUES (?, 'image', ?)
    ");
    
    if ($stmt->execute([$product_id, $url])) {
        echo json_encode(['success' => true]);
    } else {
        unlink($filepath);
        echo json_encode(['success' => false, 'error' => 'Ошибка при сохранении в базу данных']);
    }
} catch (Exception $e) {
    // В случае ошибки удаляем загруженный файл
    unlink($filepath);
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}