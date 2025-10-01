<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('/');
}

$success = false;
$error = '';

// Добавление новости
if (isset($_POST['add'])) {
    $title = clean($_POST['title']);
    $content = clean($_POST['content']);

    if (empty($title) || empty($content)) {
        $error = "Заполните необходимые поля";
    } else {
        $stmt = $pdo->prepare("INSERT INTO news (title, content) VALUES (?, ?)");
        if ($stmt->execute([$title, $content])) {
            $success = "Новость успешно добавлена!";
        } else {
            $error = "Ошибка при добавлении новости";
        }
    }
}

// Редактирование новости
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $title = clean($_POST['title']);
    $content = clean($_POST['content']);

    if (empty($title) || empty($content)) {
        $error = "Заполните необходимые поля";
    } else {
        $stmt = $pdo->prepare("UPDATE news SET title=?, content=? WHERE id=?");
        if ($stmt->execute([$title, $content, $id])) {
            $success = "Новость успешно обновлена!";
        } else {
            $error = "Ошибка при обновлении новости";
        }
    }
}

// Удаление новости
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    if ($stmt->execute([$_POST['id']])) {
        $success = "Новость успешно удалена!";
    } else {
        $error = "Ошибка при удалении новости";
    }
}

// Выборка новостей
$news = $pdo->query("SELECT * FROM news ORDER BY date DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include '../includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление новостями - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Управление новостями</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                Добавить новость
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
                                <th>Заголовок</th>
                                <th>Дата публикации</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($news as $item): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($item['date'])); ?></td>
                                    <td>
                                        <a href="#" data-id="<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-info edit-news-btn">Редактировать</a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Вы действительно хотите удалить новость?');">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
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

    <!-- Модальное окно добавления новости -->
    <div class="modal fade" id="addNewsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добавить новость</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">Заголовок</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Содержание</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                        </div>
                        <button type="submit" name="add" class="btn btn-primary">Добавить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно редактирования новости -->
    <div class="modal fade" id="editNewsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактировать новость</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Заголовок</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editContent" class="form-label">Содержание</label>
                            <textarea class="form-control" id="editContent" name="content" rows="5" required></textarea>
                        </div>
                        <button type="submit" name="update" class="btn btn-primary">Обновить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editBtns = document.querySelectorAll('.edit-news-btn');
            editBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const modal = new bootstrap.Modal(document.getElementById('editNewsModal'));
                    const id = this.dataset.id;

                    fetch(`get_news_data.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('editId').value = data.id;
                            document.getElementById('editTitle').value = data.title;
                            document.getElementById('editContent').value = data.content;
                            modal.show();
                        });
                });
            });
        });
    </script>
</body>
</html>