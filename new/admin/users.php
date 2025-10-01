<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('/');
}

$success = false;
$error = '';

// Блокировка/разблокировка пользователя
if (isset($_POST['toggle_block']) && isset($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE users SET is_blocked = NOT is_blocked WHERE id = ?");
    if ($stmt->execute([$_POST['id']])) {
        $success = "Статус пользователя изменен";
    } else {
        $error = "Ошибка при изменении статуса пользователя";
    }
}

// Изменение баланса
if (isset($_POST['update_balance']) && isset($_POST['id'])) {
    $balance = clean($_POST['balance']);
    if (!is_numeric($balance)) {
        $error = "Некорректная сумма";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
        if ($stmt->execute([$balance, $_POST['id']])) {
            $success = "Баланс пользователя обновлен";
        } else {
            $error = "Ошибка при обновлении баланса";
        }
    }
}

// Получение списка пользователей
$users = $pdo->query("
    SELECT 
        users.*, 
        COUNT(orders.id) as orders_count,
        COALESCE(SUM(orders.final_price), 0) as total_spent
    FROM users 
    LEFT JOIN orders ON users.id = orders.user_id
    GROUP BY users.id 
    ORDER BY users.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include '../includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Управление пользователями</h1>

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
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Баланс</th>
                                <th>Заказов</th>
                                <th>Потрачено</th>
                                <th>Статус</th>
                                <th>Дата рег.</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <form method="POST" class="d-flex align-items-center">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <input type="number" name="balance" value="<?php echo $user['balance']; ?>" 
                                                   class="form-control form-control-sm" style="width: 100px">
                                            <button type="submit" name="update_balance" 
                                                    class="btn btn-sm btn-outline-primary ms-2">✓</button>
                                        </form>
                                    </td>
                                    <td><?php echo $user['orders_count']; ?></td>
                                    <td><?php echo number_format($user['total_spent'], 2); ?> ₽</td>
                                    <td>
                                        <?php if ($user['is_blocked']): ?>
                                            <span class="badge bg-danger">Заблокирован</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Активен</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="toggle_block" 
                                                    class="btn btn-sm <?php echo $user['is_blocked'] ? 'btn-success' : 'btn-danger'; ?>">
                                                <?php echo $user['is_blocked'] ? 'Разблокировать' : 'Заблокировать'; ?>
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