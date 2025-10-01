<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('/');
}

$success = false;
$error = '';

// Добавление категории
if (isset($_POST['add'])) {
    $name = clean($_POST['name']);
    $slug = createSlug($name);

    if (empty($name)) {
        $error = "Введите название категории";
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        if ($stmt->execute([$name, $slug])) {
            $success = "Категория успешно добавлена";
        } else {
            $error = "Ошибка при добавлении категории";
        }
    }
}

// Удаление категории
if (isset($_POST['delete']) && isset($_POST['id'])) {
    // Проверяем, есть ли товары в этой категории
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category = ?");
    $stmt->execute([$_POST['id']]);
    if ($stmt->fetchColumn() > 0) {
        $error = "Нельзя удалить категорию, содержащую товары";
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$_POST['id']])) {
            $success = "Категория успешно удалена";
        } else {
            $error = "Ошибка при удалении категории";
        }
    }
}

// Получение списка категорий
$categories = $pdo->query("
    SELECT 
        categories.*,
        COUNT(products.id) as products_count
    FROM categories
    LEFT JOIN products ON categories.id = products.category
    GROUP BY categories.id
    ORDER BY categories.name
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include '../includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление категориями - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Управление категориями</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                Добавить категорию
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
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Slug</th>
                                <th>Кол-во товаров</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                    <td><?php echo $category['products_count']; ?></td>
                                    <td>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Удалить категорию?');">
                                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" name="delete" 
                                                    class="btn btn-sm btn-danger"
                                                    <?php echo $category['products_count'] > 0 ? 'disabled' : ''; ?>>
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

    <!-- Модальное окно добавления категории -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добавить категорию</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Название</label>
                            <input type="text" class="form-control" id="name" name="name" required>
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