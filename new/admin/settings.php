<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('/');
}

$success = false;
$error = '';

// Обработка формы
if (isset($_POST['update_settings'])) {
    $auto_complete = isset($_POST['auto_complete_orders']) ? '1' : '0';
    $allow_promo_with_discount = isset($_POST['allow_promo_with_discount']) ? '1' : '0';
    $show_hidden_to_buyers = isset($_POST['show_hidden_to_buyers']) ? '1' : '0';

    try {
        $pdo->beginTransaction();

        // Обновляем настройку автозавершения заказов
        $stmt = $pdo->prepare("
            INSERT INTO settings (name, value) 
            VALUES ('auto_complete_orders', ?) 
            ON DUPLICATE KEY UPDATE value = ?
        ");
        $stmt->execute([$auto_complete, $auto_complete]);

        // Обновляем настройку промокодов на товары со скидкой
        $stmt = $pdo->prepare("
            INSERT INTO settings (name, value) 
            VALUES ('allow_promo_with_discount', ?) 
            ON DUPLICATE KEY UPDATE value = ?
        ");
        $stmt->execute([$allow_promo_with_discount, $allow_promo_with_discount]);


        // Отображение скрытых товаров
        $stmt = $pdo->prepare("
            INSERT INTO settings (name, value) 
            VALUES ('show_hidden_to_buyers', ?) 
            ON DUPLICATE KEY UPDATE value = ?
        ");
        $stmt->execute([$show_hidden_to_buyers, $show_hidden_to_buyers]);

        // Обновляем тему, если она была изменена
        if (isset($_POST['theme'])) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (name, value) 
                VALUES ('site_theme', ?) 
                ON DUPLICATE KEY UPDATE value = ?
            ");
            $stmt->execute([$_POST['theme'], $_POST['theme']]);
        }

        $pdo->commit();
        $success = "Настройки успешно обновлены";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Ошибка при обновлении настроек";
    }
}

// Получаем текущие настройки
$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'auto_complete_orders'");
$stmt->execute();
$auto_complete_orders = $stmt->fetchColumn() ?? '0';

$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'allow_promo_with_discount'");
$stmt->execute();
$allow_promo_with_discount = $stmt->fetchColumn() ?? '0';

$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'show_hidden_to_buyers'");
$stmt->execute();
$show_hidden_to_buyers = $stmt->fetchColumn() ?? '0';

$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'site_theme'");
$stmt->execute();
$current_theme = $stmt->fetchColumn() ?? 'dark';

// Массив доступных тем
$available_themes = [
    'default' => 'Стандартная',
    'dark' => 'Темная',
    'minecraft' => 'Minecraft'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include '../includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки - Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container mt-4">
        <h1>Настройки сайта</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <!-- Автоматическое выполнение заказов -->
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="auto_complete_orders" 
                            name="auto_complete_orders" <?php echo $auto_complete_orders == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="auto_complete_orders">
                            Автоматическое выполнение заказов
                        </label>
                        <div class="form-text">
                            Если включено, заказы будут автоматически помечаться как выполненные после оплаты
                        </div>
                    </div>

                    <!-- Новая настройка для промокодов -->
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="allow_promo_with_discount" 
                            name="allow_promo_with_discount" <?php echo $allow_promo_with_discount == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="allow_promo_with_discount">
                            Разрешить промокоды на товары со скидкой
                        </label>
                        <div class="form-text">
                            Если включено, промокоды можно будет использовать на товары с временной скидкой
                        </div>
                    </div>

                    <!-- Отображение скрытых товаров -->
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="show_hidden_to_buyers" 
                            name="show_hidden_to_buyers" <?php echo $show_hidden_to_buyers == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="show_hidden_to_buyers">
                            Показывать скрытые товары покупателям
                        </label>
                        <div class="form-text">
                            Если включено, пользователи смогут видеть купленные ими скрытые товары в своем профиле
                        </div>
                    </div>

                    <!-- Выбор темы -->
                    <div class="mb-3">
                        <label for="theme" class="form-label">Тема оформления</label>
                        <select class="form-control" id="theme" name="theme">
                            <?php foreach ($available_themes as $value => $label): ?>
                                <option value="<?php echo $value; ?>" 
                                        <?php echo $current_theme == $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" name="update_settings" class="btn btn-primary">
                        Сохранить настройки
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>