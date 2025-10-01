<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

if (!isset($_GET['id'])) {
    redirect('/shop.php');
}

// Получаем информацию о товаре
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$_GET['id']]);
$product = $stmt->fetch();

if (!$product) {
    redirect('/shop.php');
}

// Проверяем, не куплен ли уже товар
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM orders 
    WHERE user_id = ? AND product_id = ? AND status = 'completed'
");
$stmt->execute([$_SESSION['user_id'], $product['id']]);
if ($stmt->fetchColumn() > 0) {
    redirect('/shop.php?error=already_purchased');
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Инициализация переменных для расчета цен
$original_price = $product['price'];
$final_price = $original_price;
$time_discount = getActiveTimeDiscount($product['id']);
$time_discount_amount = 0;
$promo_result = null;
$error = '';

// Применяем временную скидку если есть
if ($time_discount) {
    $time_discount_amount = $original_price * ($time_discount['discount_percent'] / 100);
    $final_price = $original_price - $time_discount_amount;
}

// Получаем настройку автовыполнения заказов
$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'auto_complete_orders'");
$stmt->execute();
$auto_complete_orders = $stmt->fetchColumn() ?? '0';


// Проверка промокода
if (isset($_POST['check_promo']) || (isset($_POST['buy']) && !empty($_POST['promo_code']))) {
    $promo_code = clean($_POST['promo_code']);
    if (!empty($promo_code)) {
        $promo_result = validatePromoCode(
            $promo_code, 
            $final_price, 
            $original_price, 
            $time_discount ? true : false
        );
        if ($promo_result['valid']) {
            $final_price = $promo_result['final_price'];
        } else {
            $error = $promo_result['message'];
        }
    }
}

// Обработка покупки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buy'])) {
    if ($user['balance'] < $final_price) {
        $error = "Недостаточно средств на балансе";
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$final_price, $user['id']]);
            
            $order_status = $auto_complete_orders == '1' ? 'completed' : 'pending';
            
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, product_id, price, promo_code_id,
                    discount_amount, final_price, status,
                    time_discount_percent, time_discount_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user['id'],
                $product['id'],
                $original_price,
                $promo_result ? $promo_result['promo_id'] : null,
                ($time_discount_amount + ($promo_result ? $promo_result['discount_amount'] : 0)),
                $final_price,
                $order_status,
                $time_discount ? $time_discount['discount_percent'] : 0,
                $time_discount_amount
            ]);

            if ($promo_result && $promo_result['promo_id']) {
                $stmt = $pdo->prepare("
                    UPDATE promo_codes 
                    SET uses_left = uses_left - 1 
                    WHERE id = ? AND uses_left IS NOT NULL
                ");
                $stmt->execute([$promo_result['promo_id']]);
            }

            $pdo->commit();
            
            $success_message = $auto_complete_orders == '1' ? 'purchase' : 'purchase_pending';
            redirect('/profile.php?success=' . $success_message);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Ошибка при оформлении заказа";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head> 
    <?php include 'includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Покупка товара - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Покупка товара</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h5><?php echo htmlspecialchars($product['name']); ?></h5>

                            
                            <p><strong>Обычная цена:</strong> <?php echo number_format($original_price, 2); ?> ₽</p>
                            
                            <?php if ($time_discount): ?>
                                <p>
                                    <strong>Скидка:</strong> 
                                    -<?php echo $time_discount['discount_percent']; ?>% 
                                    (-<?php echo number_format($time_discount_amount, 2); ?> ₽)
                                </p>
                            <?php endif; ?>

                            <?php if ($promo_result && $promo_result['valid']): ?>
                                <p>
                                    <strong>Промокод:</strong> 
                                    -<?php echo $promo_result['discount_percent']; ?>% 
                                    (-<?php echo number_format($promo_result['discount_amount'], 2); ?> ₽)
                                </p>
                            <?php endif; ?>

                            <p>
                                <strong>Итоговая цена:</strong> 
                                <span class="h4"><?php echo number_format($final_price, 2); ?> ₽</span>
                            </p>

                            <p>Ваш баланс: <strong><?php echo number_format($user['balance'], 2); ?> ₽</strong></p>
                        </div>

                        <form method="POST" class="mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" id="promo_code" name="promo_code" 
                                       placeholder="Введите промокод" 
                                       value="<?php echo isset($_POST['promo_code']) ? htmlspecialchars($_POST['promo_code']) : ''; ?>">
                                <button type="submit" name="check_promo" class="btn btn-outline-secondary">
                                    Применить
                                </button>
                            </div>
                        </form>

                        <?php if ($user['balance'] >= $final_price): ?>
                            <form method="POST">
                                <input type="hidden" name="promo_code" 
                                       value="<?php echo isset($_POST['promo_code']) ? htmlspecialchars($_POST['promo_code']) : ''; ?>">
                                <button type="submit" name="buy" class="btn btn-primary">Подтвердить покупку</button>
                                <a href="shop.php" class="btn btn-secondary">Отмена</a>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                Недостаточно средств. <a href="balance.php">Пополнить баланс</a>
                            </div>
                            <a href="shop.php" class="btn btn-secondary">Вернуться в магазин</a>
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