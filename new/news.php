<?php
// Подключение к базе данных
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

try {
    global $pdo;
    
 // Запрашиваем последние 5 новостей
    $stmt = $pdo->prepare("SELECT * FROM news ORDER BY date DESC LIMIT 4");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        foreach($result as $row) { ?>
            <!-- HTML-шаблон каждой новости -->
            <div class="news-item">
                <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                <p><small>Опубликована <?php echo date('d.m.Y H:i', strtotime($row['date'])); ?></small></p>
                <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
            </div>
        <?php }
    } else {
        echo "<p>Новостей пока нет.</p>";
    }

} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
</body>
</html>