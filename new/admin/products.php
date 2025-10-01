<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('/');
}

$success = false;
$error = '';

// Удаление товара
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$_POST['id']])) {
        $success = "Товар успешно удален";
    } else {
        $error = "Ошибка при удалении товара";
    }
}

// Добавление товара
if (isset($_POST['add'])) {
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    $price = clean($_POST['price']);
    $category = clean($_POST['category']);
    $is_hidden = isset($_POST['is_hidden']) ? 1 : 0;

    if (empty($name) || empty($price)) {
        $error = "Заполните обязательные поля";
    } else {
        $file_path = null;
        $file_name = null;
        $image = null;

        // Обработка загруженного файла
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $allowed = ['zip', 'rar', 'jar', 'txt'];
            $filename = $_FILES['file']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $error = "Недопустимый тип файла";
            } else if (!validateFileType($_FILES['file']['tmp_name'], $allowed)) {
                $error = "Недопустимый тип файла";
            } else {
                $file_name = $filename;
                $upload_name = uniqid() . '.' . $ext;
                $file_path = basename($upload_name); 
                $upload_path = UPLOADS_PATH . $upload_name;

                if (!file_exists(UPLOADS_PATH)) {
                    mkdir(UPLOADS_PATH, 0750, true);
                }

                if (!is_writable(UPLOADS_PATH)) {
                    $error = "Ошибка прав доступа к директории загрузок";
                } else if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                    $error = "Ошибка при загрузке файла";
                }
            }
        }

        // Обработка загруженного изображения
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_images = ['jpg', 'jpeg', 'png', 'gif'];
            $image_filename = $_FILES['image']['name'];
            $image_ext = strtolower(pathinfo($image_filename, PATHINFO_EXTENSION));

            if (!in_array($image_ext, $allowed_images)) {
                $error = "Недопустимый тип изображения";
            } else {
                $image_upload_name = uniqid() . '.' . $image_ext;
                $image = 'assets/images/products/' . $image_upload_name;

                // Создаем директорию для изображений, если её нет
                if (!file_exists('../assets/images/products/')) {
                    mkdir('../assets/images/products/', 0777, true);
                }

                if (!move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image)) {
                    $error = "Ошибка при загрузке изображения";
                }
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare("
                INSERT INTO products (name, description, price, category, file_path, file_name, image, is_hidden) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$name, $description, $price, $category, $file_path, $file_name, $image, $is_hidden])) {
                $success = "Товар успешно добавлен";
            } else {
                $error = "Ошибка при добавлении товара";
            }
        }
    }
}

// Переключение видимости товара
if (isset($_POST['toggle_visibility']) && isset($_POST['product_id'])) {
    $stmt = $pdo->prepare("
        UPDATE products 
        SET is_hidden = NOT is_hidden 
        WHERE id = ?
    ");
    if ($stmt->execute([$_POST['product_id']])) {
        $success = "Статус товара успешно обновлен";
    } else {
        $error = "Ошибка при обновлении статуса товара";
    }
}

// Получение списка товаров
$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include '../includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление товарами - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Управление товарами</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                Добавить товар
            </button>
        </div>

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
                                <th>Название</th>
                                <th>Категория</th>
                                <th>Цена</th>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                                    <td><?php echo number_format($product['price'], 2); ?> ₽</td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="toggle_visibility" 
                                                    class="btn btn-sm <?php echo $product['is_hidden'] ? 'btn-warning' : 'btn-success'; ?>">
                                                <?php echo $product['is_hidden'] ? 'Скрыт' : 'Отображается'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td><?php echo date('d.m.Y', strtotime($product['created_at'])); ?></td>
                                    <td>
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-sm btn-primary">Редактировать</a>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Вы уверены?');">
                                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
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

    <!-- Модальное окно добавления товара -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добавить товар</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Название</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Цена</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Категория</label>
                            <select class="form-control" id="category" name="category" required>
                                <?php
                                $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
                                foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label">Файл товара</label>
                            <input type="file" class="form-control" id="file" name="file">
                            <small class="text-muted">Поддерживаемые форматы: zip, rar, jar, txt</small>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Изображение товара</label>
                            <input type="file" class="form-control" id="image" name="image">
                            <small class="text-muted">Поддерживаемые форматы: jpg, jpeg, png, gif</small>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_hidden" name="is_hidden">
                            <label class="form-check-label" for="is_hidden">
                                Скрыть товар
                            </label>
                            <div class="form-text">
                                Если включено, товар будет скрыт от пользователей в магазине
                            </div>
                        </div>
                        <button type="submit" name="add" class="btn btn-primary">Добавить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>