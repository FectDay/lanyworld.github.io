<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include 'includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Политика конфиденциальности - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4 mb-4">
        <h1 class="mb-4">Политика конфиденциальности</h1>
        
        <div class="card">
            <div class="card-body">
                <h2>Введение</h2>
                <p>Мы - <?php echo SITE_NAME; ?>. Мы стремимся защищать Вашу конфиденциальность и уважать её. Ваша личная информация является для нас важной и ценной, и мы делаем все возможное, чтобы обеспечить её безопасное хранение и использование. Если у Вас есть вопросы о Вашей личной информации или нашей Политике конфиденциальности, пожалуйста, свяжитесь с нами.</p>

                <h2>Какую информацию о Вас мы собираем</h2>
                <p>Мы собираем различные типы данных для обеспечения работы Сайта и предоставления Вам услуг. Среди этих данных:</p>
                <ul>
                    <li>Информация о балансе вашего аккаунта</li>
                </ul>

                

                <h2>Изменения в политике конфиденциальности</h2>
                <p>Мы оставляем за собой право вносить изменения в данную политику конфиденциальности. Все изменения будут опубликованы на этой странице с указанием даты последнего обновления.</p>

                <div class="mt-4">
                    <p class="text-muted">Последнее обновление: <?php echo date('d.m.Y'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>