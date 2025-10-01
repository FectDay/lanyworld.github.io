<?php
session_start();

// Функция для очистки входных данных
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Функция для проверки авторизации пользователя
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT is_blocked FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $is_blocked = $stmt->fetchColumn();
    
    if ($is_blocked) {
        session_destroy();
        return false;
    }
    
    return true;
}

// Функция для перенаправления
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

// Функция для проверки прав администратора
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    global $pdo;
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return (bool)$stmt->fetchColumn();
}

// Функция для создания слага
function createSlug($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace('/[^a-zA-Zа-яА-Я0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    $str = trim($str, '-');
    return $str;
}

// Функция для проверки типа файла
function validateFileType($file_path, $allowed_types) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    
    $allowed_mime_types = [
        'application/zip' => ['zip'],
        'application/x-rar' => ['rar'],
        'application/java-archive' => ['jar'],
        'text/plain' => ['txt']
    ];
    
    foreach ($allowed_mime_types as $mime => $extensions) {
        if ($mime_type === $mime && array_intersect($extensions, $allowed_types)) {
            return true;
        }
    }
    
    return false;
}

// Функция для получения активной временной скидки
function getActiveTimeDiscount($product_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM time_discounts 
        WHERE product_id = ? 
        AND is_active = 1 
        AND start_date <= NOW() 
        AND end_date >= NOW()
        ORDER BY discount_percent DESC 
        LIMIT 1
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}

// Функция для проверки и расчета промокода
function validatePromoCode($code, $price_after_time_discount, $original_price, $has_time_discount = false) {
    global $pdo;
    
    // Проверяем, разрешены ли промокоды на товары со скидкой
    if ($has_time_discount) {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'allow_promo_with_discount'");
        $stmt->execute();
        $allow_promo_with_discount = $stmt->fetchColumn() ?? '0';
        
        if ($allow_promo_with_discount != '1') {
            return [
                'valid' => false,
                'message' => 'Промокоды нельзя использовать на товары со скидкой'
            ];
        }
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM promo_codes 
        WHERE code = ? 
        AND is_active = 1
        AND (uses_left IS NULL OR uses_left > 0)
        AND (start_date IS NULL OR start_date <= NOW())
        AND (end_date IS NULL OR end_date >= NOW())
    ");
    $stmt->execute([strtoupper($code)]);
    $promo = $stmt->fetch();
    
    if (!$promo) {
        $stmt = $pdo->prepare("
            SELECT 
                CASE 
                    WHEN NOT EXISTS (SELECT 1 FROM promo_codes WHERE code = ?) 
                        THEN 'Промокод не существует'
                    WHEN (SELECT is_active FROM promo_codes WHERE code = ?) = 0 
                        THEN 'Промокод деактивирован'
                    WHEN (SELECT uses_left FROM promo_codes WHERE code = ?) = 0 
                        THEN 'Промокод больше не действителен (закончились использования)'
                    WHEN NOW() < (SELECT start_date FROM promo_codes WHERE code = ?) 
                        THEN 'Промокод еще не активен'
                    WHEN NOW() > (SELECT end_date FROM promo_codes WHERE code = ?) 
                        THEN 'Срок действия промокода истек'
                    ELSE 'Промокод недействителен'
                END as reason
        ");
        $stmt->execute([strtoupper($code), strtoupper($code), strtoupper($code), strtoupper($code), strtoupper($code)]);
        return ['valid' => false, 'message' => $stmt->fetchColumn()];
    }
    
    if ($original_price < $promo['min_order_amount']) {
        return [
            'valid' => false, 
            'message' => 'Минимальная сумма заказа: ' . number_format($promo['min_order_amount'], 2) . ' ₽'
        ];
    }

    // Применяем промокод к цене после временной скидки
    $promo_discount_amount = $price_after_time_discount * ($promo['discount'] / 100);
    $final_price = $price_after_time_discount - $promo_discount_amount;
    
    // Защита от отрицательной цены
    $final_price = max(0.01, $final_price);
    
    return [
        'valid' => true,
        'discount_percent' => $promo['discount'],
        'discount_amount' => $promo_discount_amount,
        'final_price' => $final_price,
        'promo_id' => $promo['id']
    ];
}
