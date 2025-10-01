<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Получаем выбранную категорию из GET-параметра
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : '';

// Формируем базовый SQL-запрос
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category = c.id
        WHERE p.is_hidden = 0";

// Условие фильтрации по категории, если она выбрана
if (!empty($selected_category)) {
    $sql .= " AND p.category = :category";
}

$sql .= " ORDER BY p.created_at DESC";


// Подготавливаем и выполняем запрос
$stmt = $pdo->prepare($sql);
if (!empty($selected_category)) {
    $stmt->bindParam(':category', $selected_category, PDO::PARAM_INT);
}
$stmt->execute();
$products = $stmt->fetchAll();

// Получаем все категории для фильтра
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include 'includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Магазин - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="icon" href="../favicon.png" type="image/png">
    <style>
        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
        }
        .price-block {
            min-height: 60px;
        }
        .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 0.9em;
        }
        .discounted-price {
            color: #dc3545;
            font-weight: bold;
        }
        .product-card {
            position: relative;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h1>Магазин</h1>
            </div>
            <div class="col-auto">
                <select class="form-select" id="category-filter">
                    <option value="">Все категории</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo $selected_category == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (empty($products)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Товары не найдены
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): 
                    // Получаем информацию о скидке для товара
                    $time_discount = getActiveTimeDiscount($product['id']);
                    $original_price = $product['price'];
                    $final_price = $original_price;
                    
                    if ($time_discount) {
                        $discount_amount = $original_price * ($time_discount['discount_percent'] / 100);
                        $final_price = $original_price - $discount_amount;
                    }

                    // Проверяем, куплен ли товар текущим пользователем
                    $isPurchased = false;
                    if (isLoggedIn()) {
                        $stmt = $pdo->prepare("
                            SELECT COUNT(*) FROM orders 
                            WHERE user_id = ? AND product_id = ? AND status = 'completed'
                        ");
                        $stmt->execute([$_SESSION['user_id'], $product['id']]);
                        $isPurchased = (bool)$stmt->fetchColumn();
                    }
                ?>
                    <div class="col">
                        <div class="card h-100 product-card">
                            <?php if ($time_discount): ?>
                                <div class="discount-badge">
                                    <span class="badge bg-danger">
                                        -<?php echo $time_discount['discount_percent']; ?>%
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if ($product['image']): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                     class="product-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="height: 400px; object-fit: cover;">
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h5>
                                
                                <p class="card-text">
                                    <?php echo mb_substr(($product['description']), 0, 3); ?>...
                                </p>

                                <div class="price-block mb-3">
                                    <?php if ($time_discount): ?>
                                        <div class="original-price">
                                            <?php echo number_format($original_price, 2); ?> ₽
                                        </div>
                                        <div class="discounted-price">
                                            <?php echo number_format($final_price, 2); ?> ₽
                                        </div>
                                        <small class="text-muted">
                                            Скидка до <?php echo date('d.m.Y', strtotime($time_discount['end_date'])); ?>
                                        </small>
                                    <?php else: ?>
                                        <div class="regular-price">
                                            <?php echo number_format($product['price'], 2); ?> ₽
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-grid gap-2">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        Подробнее
                                    </a>
                                    <?php if (isLoggedIn()): ?>
                                        <?php if ($isPurchased): ?>
                                            <button class="btn btn-success btn-sm" disabled>
                                                Куплено
                                            </button>
                                        <?php else: ?>
                                            <a href="buy.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                Купить
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-secondary btn-sm">
                                            Войдите для покупки
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('category-filter').addEventListener('change', function() {
            const category = this.value;
            window.location.href = 'shop.php' + (category ? '?category=' + category : '');
        });
    </script>
</body>
</html>