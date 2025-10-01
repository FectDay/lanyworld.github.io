<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    die(json_encode(['success' => false, 'error' => 'Доступ запрещен']));
}

$data = json_decode(file_get_contents('php://input'), true);
$media_id = $data['id'] ?? null;

// Получаем информацию о медиа
$stmt = $pdo->prepare("SELECT * FROM product_media WHERE id = ?");
$stmt->execute([$media_id]);
$media = $stmt->fetch();

if (!$media) {
    die(json_encode(['success' => false, 'error' => 'Медиа не найдено']));
}

// Если это изображение, удаляем файл
if ($media['type'] == 'image' && file_exists('../' . $media['url'])) {
    unlink('../' . $media['url']);
}

// Удаляем запись из БД
$stmt = $pdo->prepare("DELETE FROM product_media WHERE id = ?");
if ($stmt->execute([$media_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка при удалении']);
}