<?php
// Получаем текущую тему из настроек
$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'site_theme'");
$stmt->execute();
$theme = $stmt->fetchColumn() ?? 'default';
?>


<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-sm">
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>/admin/">Админ-панель</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/">Главная</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/products.php">Товары</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/add_news.php">Новости</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/users.php">Пользователи</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/orders.php">Заказы</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/categories.php">Категории</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/promo_codes.php">Промокоды</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/settings.php">Настройки</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>">На сайт</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/logout.php">Выйти</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Подключение стилей темы -->
<link href="<?php echo SITE_URL; ?>/assets/css/themes/<?php echo $theme; ?>.css" rel="stylesheet">
<link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">