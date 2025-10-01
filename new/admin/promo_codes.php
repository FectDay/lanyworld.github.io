<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('/');
}

$success = '';
$error = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'status_changed':
            $success = 'Статус промокода успешно изменен';
            break;
        case 'added':
            $success = 'Промокод успешно добавлен';
            break;
        case 'deleted':
            $success = 'Промокод успешно удален';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'validation':
            $error = 'Проверьте правильность заполнения полей';
            break;
        case 'duplicate_code':
            $error = 'Промокод с таким кодом уже существует';
            break;
        case 'add_failed':
            $error = 'Ошибка при добавлении промокода';
            break;
        case 'status_change_failed':
            $error = 'Ошибка при изменении статуса';
            break;
        case 'delete_failed':
            $error = 'Ошибка при удалении промокода';
            break;
    }
}

// Добавление промокода
if (isset($_POST['add'])) {
    $code = strtoupper(clean($_POST['code']));
    $description = clean($_POST['description']);
    $discount = (int)$_POST['discount'];
    $min_order_amount = (float)$_POST['min_order_amount'];
    $uses_left = $_POST['uses_left'] !== '' ? (int)$_POST['uses_left'] : null;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if (empty($code) || $discount <= 0 || $discount > 100) {
        header('Location: ' . SITE_URL . '/admin/promo_codes.php?error=validation');
        exit;
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM promo_codes WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetchColumn() > 0) {
            header('Location: ' . SITE_URL . '/admin/promo_codes.php?error=duplicate_code');
            exit;
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO promo_codes (code, description, discount, min_order_amount, uses_left, start_date, end_date)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$code, $description, $discount, $min_order_amount, $uses_left, $start_date, $end_date])) {
                header('Location: ' . SITE_URL . '/admin/promo_codes.php?success=added');
                exit;
            } else {
                header('Location: ' . SITE_URL . '/admin/promo_codes.php?error=add_failed');
                exit;
            }
        }
    }
}

// Удаление промокода
if (isset($_POST['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM promo_codes WHERE id = ?");
    if ($stmt->execute([$_POST['id']])) {
        header('Location: ' . SITE_URL . '/admin/promo_codes.php?success=deleted');
        exit;
    } else {
        header('Location: ' . SITE_URL . '/admin/promo_codes.php?error=delete_failed');
        exit;
    }
}

// Изменение статуса промокода
if (isset($_POST['toggle_status'])) {
    $stmt = $pdo->prepare("UPDATE promo_codes SET is_active = NOT is_active WHERE id = ?");
    if ($stmt->execute([$_POST['id']])) {
        header('Location: ' . SITE_URL . '/admin/promo_codes.php?success=status_changed');
        exit;
    } else {
        header('Location: ' . SITE_URL . '/admin/promo_codes.php?error=status_change_failed');
        exit;
    }
}

// Получение списка промокодов
$promo_codes = $pdo->query("
    SELECT 
        promo_codes.*,
        COUNT(orders.id) as usage_count,
        CASE 
            WHEN is_active = 0 THEN 'Неактивен'
            WHEN NOW() < start_date THEN 'Ожидает'
            WHEN NOW() > end_date THEN 'Истёк'
            WHEN uses_left = 0 THEN 'Закончился'
            ELSE 'Активен'
        END as status_text,
        CASE 
            WHEN is_active = 0 THEN 'bg-danger'
            WHEN NOW() < start_date THEN 'bg-info'
            WHEN NOW() > end_date THEN 'bg-warning'
            WHEN uses_left = 0 THEN 'bg-secondary'
            ELSE 'bg-success'
        END as status_class
    FROM promo_codes
    LEFT JOIN orders ON orders.promo_code_id = promo_codes.id
    GROUP BY promo_codes.id
    ORDER BY created_at DESC
")->fetchAll();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include '../includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление промокодами - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container mt-4">
        <h1>Управление промокодами</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPromoModal">
                    Добавить промокод
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Код</th>
                                <th>Описание</th>
                                <th>Скидка</th>
                                <th>Мин. сумма</th>
                                <th>Использований</th>
                                <th>Период действия</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promo_codes as $promo): ?>
                                <tr>
                                    <td><?php echo $promo['id']; ?></td>
                                    <td><?php echo htmlspecialchars($promo['code']); ?></td>
                                    <td><?php echo htmlspecialchars($promo['description']); ?></td>
                                    <td><?php echo $promo['discount']; ?>%</td>
                                    <td><?php echo number_format($promo['min_order_amount'], 2); ?> ₽</td>
                                    <td>
                                        <?php 
                                        if ($promo['uses_left'] === null) {
                                            echo '∞';
                                        } else {
                                            echo $promo['uses_left'] . ' из ' . ($promo['uses_left'] + $promo['usage_count']);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($promo['start_date'] && $promo['end_date']) {
                                            echo date('d.m.Y', strtotime($promo['start_date'])) . ' - ' . 
                                                 date('d.m.Y', strtotime($promo['end_date']));
                                        } else {
                                            echo 'Без ограничений';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $promo['status_class']; ?>">
                                            <?php echo $promo['status_text']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?php echo $promo['id']; ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-sm btn-warning">
                                                <?php echo $promo['is_active'] ? 'Деактивировать' : 'Активировать'; ?>
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Удалить промокод?');">
                                            <input type="hidden" name="id" value="<?php echo $promo['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-danger">Удалить</button>
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

    <!-- Модальное окно добавления промокода -->
    <div class="modal fade" id="addPromoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добавить промокод</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="code" class="form-label">Код</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                            <div class="form-text text-muted">
                                Введите код промокода (например: IRUKA, IRUKAMINE2025). 
                                Используйте только буквы и цифры, код будет автоматически преобразован в верхний регистр.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="discount" class="form-label">Скидка (%)</label>
                            <input type="number" class="form-control" id="discount" name="discount" 
                                   min="1" max="100" required>
                                   <div class="form-text text-muted">
                                    Укажите процент скидки от 1 до 100. 
                                    Например: 10 = скидка 10%.
                                </div>
                        </div>
                        <div class="mb-3">
                            <label for="min_order_amount" class="form-label">Минимальная сумма заказа</label>
                            <input type="number" 
                                class="form-control" id="min_order_amount"  name="min_order_amount" min="0" 
                                step="0.01" value="0" placeholder="Например: 1000">
                            <div class="form-text text-muted">
                                Укажите минимальную сумму заказа, при которой можно использовать промокод. 
                                Установите 0 для применения без ограничений.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="uses_left" class="form-label">Количество использований</label>
                            <input type="number" class="form-control" id="uses_left" name="uses_left" 
                                   min="1" placeholder="Оставьте пустым для безлимитного">
                        </div>
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Дата начала</label>
                            <input type="datetime-local" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Дата окончания</label>
                            <input type="datetime-local" class="form-control" id="end_date" name="end_date">
                        </div>
                        <button type="submit" name="add" class="btn btn-primary">Добавить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Установка дефолтных дат при открытии модального окна -->
    <script>
        document.getElementById('addPromoModal').addEventListener('show.bs.modal', function (event) {
            const now = new Date();
            
            const startDate = now.toISOString().slice(0, 16);
            
            const weekLater = new Date(now);
            weekLater.setDate(weekLater.getDate() + 7);
            const endDate = weekLater.toISOString().slice(0, 16);
            
            document.getElementById('start_date').value = startDate;
            document.getElementById('end_date').value = endDate;
        });
    </script>
</body>
</html>