<?php
require_once '../includes/config.php';
require_once '../includes/database.php';

$id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);
exit;