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
    <title>Условия использования - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4 mb-4">
        <h1 class="mb-4">Условия использования</h1>
        
        <div class="card">
            <div class="card-body">
                <h2>1. Общие положения</h2>
                <p>Добро пожаловать на <?php echo SITE_NAME; ?>. Используя наш сайт, вы принимаете настоящие условия использования в полном объеме. Если вы не согласны с каким-либо пунктом условий, вы не можете использовать наш сайт.</p>

                <h2>2. Регистрация и аккаунт</h2>
                <ul>
                    <li>Мы оставляем за собой право заблокировать или удалить ваш аккаунт при нарушении правил</li>
                </ul>

        

                <h2>Изменения условий</h2>
                <p>Мы оставляем за собой право изменять эти условия в любое время. Продолжая использовать сайт после внесения изменений, вы принимаете новые условия.</p>

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