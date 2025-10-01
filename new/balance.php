<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$success = false;
$error = '';
$currency = 'RUB';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = clean($_POST['amount']);
    
    if (!is_numeric($amount) || $amount <= 0) {
        $error = 'Введите корректную сумму';
    } else {
        // Формируем ссылку на оплату
        $signature = md5(MERCHANT_ID.':'.$amount.':'.SECRET_KEY2.':RUB:'.$_SESSION['user_id']);
        
        header("Location: https://pay.fk.money/?" .
               http_build_query([
                   'm' => MERCHANT_ID,
                   'oa' => $amount,
                   'currency' => $currency,
                   'o' => $_SESSION['user_id'], // Пользователь ID
                   's' => $signature
               ]));
        exit;
    }
}

// Получаем текущий баланс
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$balance = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include 'includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пополнение баланса - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="icon" href="../favicon.pngg" type="image/png">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Пополнение баланса</h4>
                    </div>
                    <div class="card-body">
                        <p>Текущий баланс: <strong><?php echo number_format($balance, 2); ?> руб.</strong></p>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Баланс успешно пополнен!
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Сумма пополнения (руб.)</label>
                                <input type="number" class="form-control" id="amount" name="amount" min="1" step="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Пополнить</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>