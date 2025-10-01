<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Получаем настройку отображения скрытых товаров
$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'show_hidden_to_buyers'");
$stmt->execute();
$show_hidden_to_buyers = $stmt->fetchColumn() ?? '0';

// Получаем историю заказов с логикой скрытых товаров
$stmt = $pdo->prepare("
    SELECT orders.*, 
           products.name as product_name, 
           products.id as product_id, 
           products.file_path,
           products.is_hidden
    FROM orders 
    JOIN products ON orders.product_id = products.id
    WHERE orders.user_id = ? 
    " . ($show_hidden_to_buyers == '0' ? 
        "AND products.is_hidden = 0" : 
        "") . "
    ORDER BY orders.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include 'includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="icon" href="../favicon.png" type="image/png">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Профиль</h5>
                        <p class="card-text">Имя: <?php echo htmlspecialchars($user['username']); ?></p>
                        <p class="card-text">Email: <?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="card-text">Баланс: <?php echo number_format($user['balance'], 2); ?> руб.</p>
                        <a href="balance.php" class="btn btn-primary">Пополнить баланс</a>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">История покупок</h5>
                        <?php if (!empty($orders)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Товар</th>
                                            <th>Цена</th>
                                            <th>Статус</th>
                                            <th>Дата</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($order['product_name']); ?>
                                                    <?php if ($order['status'] == 'completed' && $order['file_path']): ?>
                                                        <br>
                                                        <a href="download.php?id=<?php echo $order['product_id']; ?>" 
                                                           class="btn btn-sm btn-success mt-1">
                                                            Скачать файл
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo number_format($order['price'], 2); ?> ₽</td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'pending' => 'warning',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $status_text = [
                                                        'pending' => 'Ожидает',
                                                        'completed' => 'Выполнен',
                                                        'cancelled' => 'Отменён'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class[$order['status']]; ?>">
                                                        <?php echo $status_text[$order['status']]; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>История покупок пока пуста.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>