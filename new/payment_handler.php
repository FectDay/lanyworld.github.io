<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

// Параметры пришедшие от FreeKassa
$mrh_orderid = $_GET['MERCHANT_ORDER_ID']; // Идентификатор заказа
$amt = $_GET['AMOUNT'];                 // Сумма платежа
$inv_id = $_GET['MERCHANT_ORDER_ID'];           // Номер счета

// Проверяем подпись платежа
$my_signature = md5(MERCHANT_ID.":".$amt.":".SECRET_KEY2.":".$inv_id);

if ($my_signature != $_REQUEST['SIGN']) {
    die("Подпись неверна!");
}

// Запись в базу данных (пример)
$stmt = $pdo->prepare("UPDATE users SET balance = balance + :amount WHERE id = :user_id");
$stmt->execute(['amount' => $amt, 'user_id' => $mrh_orderid]);

echo "YES"; // Подтверждаем успешную обработку платежа
exit;