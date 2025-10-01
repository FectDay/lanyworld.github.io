<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('/');
}

$success = false;
$error = '';

// Изменение статуса заказа
if (isset($_POST['update_status']) && isset($_POST['id'])) {
    $status = clean($_POST['status']);
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $_POST['id']])) {
        $success = "Статус заказа обновлен";
    } else {
        $error = "Ошибка при обновлении статуса";
    }
}

// Удаление заказа
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    if ($stmt->execute([$_POST['id']])) {
        $success = "Заказ успешно удален";
    } else {
        $error = "Ошибка при удалении заказа";
    }
}

// Получение списка заказов с информацией о пользователе и товаре
$orders = $pdo->query("
    SELECT 
        orders.*,
        users.username,
        users.email,
        products.name as product_name,
        products.category
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN products ON orders.product_id = products.id
    ORDER BY orders.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include '../includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заказами - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Управление заказами</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Пользователь</th>
                                <th>Товар</th>
                                <th>Категория</th>
                                <th>Цена</th>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['username']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['category']); ?></td>
                                    <td><?php echo number_format($order['final_price'], 2); ?> ₽</td>
                                    <td>
                                        <form method="POST" class="d-flex align-items-center">
                                            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="form-select form-select-sm" 
                                                    onchange="this.form.submit()" style="width: 130px;">
                                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>
                                                    Ожидает
                                                </option>
                                                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>
                                                    Выполнен
                                                </option>
                                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>
                                                    Отменён
                                                </option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Удалить заказ?');">
                                            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                                Удалить
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>