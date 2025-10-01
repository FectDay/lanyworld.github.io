<?php
// Получаем текущую тему из настроек
$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'site_theme'");
$stmt->execute();
$theme = $stmt->fetchColumn() ?? 'dark';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-sm">
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>">Главная</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/shop.php"><i class="fa-solid fa-coins"></i> Магазин</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/wiki.php"><i class="fa-etch fa-solid fa-wifi"></i> Wiki</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="<?php echo SITE_URL; ?>/admin/">
                                <i class="bi bi-gear-fill"></i> Админ-панель
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/profile.php"><i class="fa-notdog fa-solid fa-address-card"></i></i> Профиль</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/balance.php"><i class="fa-solid fa-money-bill-1"></i> Баланс</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/logout.php"><i class="fa-regular fa-door-open"></i> Выйти</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item login-btn">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/login.php"><i class="fa-solid fa-right-from-bracket"></i> Войти</a>
                    </li>
                    <li class="nav-item register-btn">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/register.php"><i class="fa-regular fa-registered"></i> Регистрация</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Подключение стилей темы -->
<link rel="stylesheet" type="text/css" href="/assets/css/style.css">
<!-- Подключаем иконки Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
<link rel="icon" href="../favicon.png" type="image/png">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.6.0/css/all.css">