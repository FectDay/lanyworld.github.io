<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('/');
}

$success = false;
$error = '';

if (!isset($_GET['id'])) {
    redirect('/admin/products.php');
}

// Получаем данные товара
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$_GET['id']]);
$product = $stmt->fetch();

if (!$product) {
    redirect('/admin/products.php');
}

// Получаем текущую активную скидку
$stmt = $pdo->prepare("
    SELECT * FROM time_discounts 
    WHERE product_id = ? AND is_active = 1 
    ORDER BY created_at DESC LIMIT 1
");
$stmt->execute([$_GET['id']]);
$current_discount = $stmt->fetch();

// Обновление скидки
if (isset($_POST['update_discount'])) {
    $discount_percent = clean($_POST['discount_percent']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Проверяем корректность введенных данных
    if ($discount_percent < 0 || $discount_percent > 99) {
        $error = "Процент скидки должен быть от 0 до 99";
    } elseif ($start_date >= $end_date) {
        $error = "Дата окончания должна быть позже даты начала";
    } else {
        // Деактивируем старые скидки
        $stmt = $pdo->prepare("
            UPDATE time_discounts 
            SET is_active = 0 
            WHERE product_id = ?
        ");
        $stmt->execute([$_GET['id']]);
        
        // Добавляем новую скидку если процент больше 0
        if ($discount_percent > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO time_discounts 
                (product_id, discount_percent, start_date, end_date) 
                VALUES (?, ?, ?, ?)
            ");
            if ($stmt->execute([$_GET['id'], $discount_percent, $start_date, $end_date])) {
                $success = "Скидка успешно обновлена";
                // Обновляем информацию о текущей скидке
                $stmt = $pdo->prepare("
                    SELECT * FROM time_discounts 
                    WHERE product_id = ? AND is_active = 1 
                    ORDER BY created_at DESC LIMIT 1
                ");
                $stmt->execute([$_GET['id']]);
                $current_discount = $stmt->fetch();
            } else {
                $error = "Ошибка при обновлении скидки";
            }
        } else {
            $success = "Скидка успешно удалена";
            $current_discount = null;
        }
    }
}

// Обновление товара
if (isset($_POST['update'])) {
    $name = clean($_POST['name']);
    $description = $_POST['description'];
    
    // Обновляем товар в базе данных
    $stmt = $pdo->prepare("UPDATE products SET 
        name = ?,
        description = ?, 
        price = ?,
        category = ?
        WHERE id = ?");
    
    $stmt->execute([
        clean($_POST['name']),
        $description, // Не применяем clean() к описанию
        floatval($_POST['price']),
        intval($_POST['category']),
        $product['id']
    ]);

    $price = clean($_POST['price']);
    $category = clean($_POST['category']);

    if (empty($name) || empty($price)) {
        $error = "Заполните обязательные поля";
    } else {
        $file_path = $product['file_path'];
        $file_name = $product['file_name'];
        $image = $product['image'];

        // Обработка нового файла товара
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $allowed = ['zip', 'rar', 'jar', 'txt'];
            $filename = $_FILES['file']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $error = "Недопустимый тип файла";
            } else if (!validateFileType($_FILES['file']['tmp_name'], $allowed)) {
                $error = "Недопустимый тип файла";
            } else {
                // Удаляем старый файл
                if ($product['file_path'] && file_exists(UPLOADS_PATH . $product['file_path'])) {
                    unlink(UPLOADS_PATH . $product['file_path']);
                }

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

        // Обработка нового изображения
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_images = ['jpg', 'jpeg', 'png', 'gif'];
            $image_filename = $_FILES['image']['name'];
            $image_ext = strtolower(pathinfo($image_filename, PATHINFO_EXTENSION));

            if (!in_array($image_ext, $allowed_images)) {
                $error = "Недопустимый тип изображения";
            } else {
                if ($product['image'] && file_exists('../' . $product['image'])) {
                    unlink('../' . $product['image']);
                }

                $image_upload_name = uniqid() . '.' . $image_ext;
                $image = 'assets/images/products/' . $image_upload_name;

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
                UPDATE products 
                SET name = ?, description = ?, price = ?, category = ?, 
                    file_path = ?, file_name = ?, image = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([
                $name, $description, $price, $category, 
                $file_path ?? $product['file_path'], 
                $file_name ?? $product['file_name'], 
                $image ?? $product['image'], 
                $product['id']
            ])) {
                $success = "Товар успешно обновлен";
                // Обновляем данные товара
                $product = $pdo->query("SELECT * FROM products WHERE id = {$product['id']}")->fetch();
            } else {
                $error = "Ошибка при обновлении товара";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include '../includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование товара - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<style>
    .card-header {
        background-color: var(--card);
    }

    .card-header h5 {
        color: var(--text-color);
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    #video-container:empty::after,
    #image-container:empty::after {
        content: 'Нет добавленных файлов';
        display: block;
        width: 100%;
        text-align: center;
        color: #6c757d;
        padding: 20px;
        font-style: italic;
    }

    .media-item {
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .media-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 0 15px #444;
    }

    .media-item .card-img-top {
        height: 200px;
        object-fit: cover;
    }

    .video-preview {
        position: relative;
    }

    .video-play-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 3rem;
        color: white;
        text-shadow: 0 0 10px rgba(0,0,0,0.5);
    }

    .video-play-icon i {
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }

    .video-preview:hover .video-play-icon i {
        opacity: 1;
    }

    .cke_chrome {
        border: 1px solid #ddd !important;
        border-radius: 4px;
    }

    .cke_top {
        border-bottom: 1px solid #ddd !important;
        background: #f8f9fa !important;
    }

    .cke_bottom {
        border-top: 1px solid #ddd !important;
        background: #f8f9fa !important;
    }

</style>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container mt-4">
        <h1>Редактирование товара</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Название</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="editor" class="form-label">Описание товара</label>
                        <textarea id="editor" name="description"><?php echo $product['description'] ?? ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                    <label class="form-label">Медиа файлы</label>
                    
                    <!-- Секция видео -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Видео</h5>
                            <button type="button" class="btn btn-primary btn-sm" onclick="addVideo()">
                                <i class="bi bi-camera-video"></i> Добавить видео
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row g-4" id="video-container">
                                <?php
                                $stmt = $pdo->prepare("SELECT * FROM product_media WHERE product_id = ? AND type = 'video' ORDER BY sort_order");
                                $stmt->execute([$product['id']]);
                                while ($media = $stmt->fetch()): ?>
                                    <div class="col-md-4">
                                        <div class="media-item card h-100">
                                            <?php
                                            preg_match('/embed\/([\w-]+)/', $media['url'], $matches);
                                            $videoId = $matches[1] ?? '';
                                            $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg";
                                            ?>
                                            <div class="video-preview">
                                                <img src="<?php echo $thumbnailUrl; ?>" 
                                                    class="card-img-top"
                                                    alt="Превью видео">
                                                <div class="video-play-icon">
                                                    <i class="bi bi-play-circle"></i>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <button type="button" class="btn btn-danger btn-sm w-100" 
                                                        onclick="deleteMedia(<?php echo $media['id']; ?>)">
                                                    <i class="bi bi-trash"></i> Удалить
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Секция изображений -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Изображения</h5>
                            <button type="button" class="btn btn-success btn-sm" onclick="uploadImage()">
                                <i class="bi bi-image"></i> Добавить изображение
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row g-4" id="image-container">
                                <?php
                                $stmt = $pdo->prepare("SELECT * FROM product_media WHERE product_id = ? AND type = 'image' ORDER BY sort_order");
                                $stmt->execute([$product['id']]);
                                while ($media = $stmt->fetch()): ?>
                                    <div class="col-md-4">
                                        <div class="media-item card h-100">
                                            <img src="<?php echo htmlspecialchars($media['url']); ?>" 
                                                class="card-img-top"
                                                alt="Изображение">
                                            <div class="card-body">
                                                <button type="button" class="btn btn-danger btn-sm w-100" 
                                                        onclick="deleteMedia(<?php echo $media['id']; ?>)">
                                                    <i class="bi bi-trash"></i> Удалить
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Цена</label>
                        <input type="number" class="form-control" id="price" name="price" 
                               value="<?php echo $product['price']; ?>" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Категория</label>
                        <select class="form-control" id="category" name="category">
                            <?php
                            $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
                            foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $cat['id'] == $product['category'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label">Файл товара</label>
                        <?php if ($product['file_name']): ?>
                            <p class="text-muted">Текущий файл: <?php echo htmlspecialchars($product['file_name']); ?></p>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="file" name="file">
                        <small class="text-muted">Поддерживаемые форматы: zip, rar, jar, txt</small>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Изображение товара</label>
                        <?php if ($product['image']): ?>
                            <div class="mb-2">
                                <img src="<?php echo '../' . $product['image']; ?>" 
                                     alt="Текущее изображение" 
                                     style="max-width: 200px; height: auto;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image">
                        <small class="text-muted">Поддерживаемые форматы: jpg, jpeg, png, gif</small>
                    </div>

                    <button type="submit" name="update" class="btn btn-primary">Сохранить изменения</button>
                    <a href="products.php" class="btn btn-secondary">Назад</a>
                </form>
            </div>
        </div>

        <!-- Форма управления скидкой -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Временная скидка</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="discount_percent" class="form-label">Процент скидки</label>
                                <input type="number" class="form-control" id="discount_percent" 
                                       name="discount_percent" min="0" max="99" step="0.1"
                                       value="<?php echo $current_discount ? $current_discount['discount_percent'] : ''; ?>">
                                <div class="form-text">0 = удалить скидку</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Дата начала</label>
                                <input type="datetime-local" class="form-control" id="start_date" 
                                       name="start_date"
                                       value="<?php echo $current_discount ? date('Y-m-d\TH:i', strtotime($current_discount['start_date'])) : date('Y-m-d\TH:i'); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">Дата окончания</label>
                                <input type="datetime-local" class="form-control" id="end_date" 
                                       name="end_date"
                                       value="<?php echo $current_discount ? date('Y-m-d\TH:i', strtotime($current_discount['end_date'])) : date('Y-m-d\TH:i', strtotime('+7 days')); ?>">
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="update_discount" class="btn btn-primary">
                        <?php echo $current_discount ? 'Обновить скидку' : 'Добавить скидку'; ?>
                    </button>
                    <?php if ($current_discount): ?>
                        <div class="mt-3">
                            <p class="text-muted">
                                Текущая скидка: <?php echo $current_discount['discount_percent']; ?>%
                                <br>
                                Период: <?php echo date('d.m.Y H:i', strtotime($current_discount['start_date'])); ?> - 
                                       <?php echo date('d.m.Y H:i', strtotime($current_discount['end_date'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function uploadImage() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.onchange = async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('image', file);
                formData.append('product_id', <?php echo $product['id']; ?>);
                
                try {
                    const response = await fetch('add_media.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        location.reload();
                    } else {
                        alert(result.error || 'Ошибка при загрузке изображения');
                    }
                } catch (error) {
                    console.error(error);
                    alert('Ошибка при загрузке изображения');
                }
            };
            input.click();
        }

        function addVideo() {
            const url = prompt('Введите URL видео YouTube:');
            if (!url) return;
            
            fetch('add_video.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: <?php echo $product['id']; ?>,
                    url: url
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.error);
                }
            })
            .catch(() => alert('Ошибка при добавлении видео'));
        }

        function deleteMedia(mediaId) {
            if (!confirm('Удалить этот медиа-файл?')) return;
            
            fetch('delete_media.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: mediaId })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.error);
                }
            })
            .catch(() => alert('Ошибка при удалении'));
        }

        document.addEventListener('DOMContentLoaded', function() {
        CKEDITOR.replace('editor', {
            language: 'ru',
            height: 400,
            versionCheck: false,
            toolbar: [
                { name: 'document', items: ['Source', '-', 'NewPage', 'Preview', '-', 'Templates'] },
                { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'] },
                { name: 'forms', items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'] },
                '/',
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language'] },
                { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
                { name: 'insert', items: ['Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe'] },
                '/',
                { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] },
                { name: 'tools', items: ['Maximize', 'ShowBlocks'] },
                { name: 'about', items: ['About'] }
            ],
            font_names: 'Arial/Arial, Helvetica, sans-serif;' +
                'Comic Sans MS/Comic Sans MS, cursive;' +
                'Courier New/Courier New, Courier, monospace;' +
                'Georgia/Georgia, serif;' +
                'Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;' +
                'Tahoma/Tahoma, Geneva, sans-serif;' +
                'Times New Roman/Times New Roman, Times, serif;' +
                'Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;' +
                'Verdana/Verdana, Geneva, sans-serif;' +
                'Minecraft/Minecraft, sans-serif',
            removeButtons: '',
            format_tags: 'p;h1;h2;h3;pre',
            removeDialogTabs: 'image:advanced;link:advanced',
            allowedContent: true,
            extraPlugins: 'colorbutton,font,justify',
            removePlugins: 'elementspath,resize'
        });
    });
    </script>
</body>
</html>