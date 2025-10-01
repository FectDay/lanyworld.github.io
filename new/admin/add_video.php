<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    die(json_encode(['success' => false, 'error' => 'Доступ запрещен']));
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? null;
$url = $data['url'] ?? '';

// Извлекаем ID видео из URL YouTube
preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
if (!isset($matches[1])) {
    die(json_encode(['success' => false, 'error' => 'Неверный формат URL YouTube']));
}

$video_id = $matches[1];
$embed_url = "https://www.youtube.com/embed/" . $video_id;

$stmt = $pdo->prepare("INSERT INTO product_media (product_id, type, url) VALUES (?, 'video', ?)");
if ($stmt->execute([$product_id, $embed_url])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка при добавлении видео']);
}