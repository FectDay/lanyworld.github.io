<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('/shop.php');
}

// Получаем данные товара
$stmt = $pdo->prepare("
    SELECT products.*, categories.name as category_name 
    FROM products 
    LEFT JOIN categories ON products.category = categories.id 
    WHERE products.id = ?
");
$stmt->execute([$_GET['id']]);
$product = $stmt->fetch();

if (!$product) {
    redirect('/shop.php');
}

// Получаем активную скидку для товара
$time_discount = getActiveTimeDiscount($product['id']);
$original_price = $product['price'];
$final_price = $original_price;
$discount_info = null;

if ($time_discount) {
    $discount_percent = $time_discount['discount_percent'];
    $final_price = $original_price * (1 - $discount_percent / 100);
    $discount_info = [
        'percent' => $discount_percent,
        'amount' => $original_price - $final_price,
        'end_date' => $time_discount['end_date']
    ];
}

// Проверяем, куплен ли товар текущим пользователем
$is_purchased = false;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM orders 
        WHERE user_id = ? AND product_id = ? AND status = 'completed'
    ");
    $stmt->execute([$_SESSION['user_id'], $product['id']]);
    $is_purchased = (bool)$stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include 'includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" as="style">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<style>
    .swiper {
        width: 100%;
        height: 500px;
        margin-bottom: 20px;
    }

    .swiper-slide {
        text-align: center;
        background: var(--navbar);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .swiper-slide img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .video-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .video-wrapper iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .swiper-button-next,
    .swiper-button-prev {
        color: #0d6efd !important;
    }

    .nav-tabs {
        border-radius: 10px;
        width: fit-content;
        background: var(--card);
        box-shadow: 0px -3px 10px #444;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd;
    }

    .tab-content {
        padding: 20px;
        background: var(--card);
        border: 1px solid var(--card-bg);
        border-top: none;
        border-radius: 0px 15px 15px 15px;
        box-shadow: 0 0 10px #444;
    }


    .video-section {
        background: var(--card);
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 0 10px #444;
    }

    .video-container {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%;
        border-radius: 10px;
        height: 0;
        overflow: hidden;
    }

    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }

</style>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="shop.php">Магазин</a></li>
                <li class="breadcrumb-item">
                    <a href="shop.php?category=<?php echo $product['category']; ?>">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($product['name']); ?>
                </li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <?php if ($product['image']): ?>
                            <div class="product-image mb-4">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                    class="img-fluid rounded" 
                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                        <?php endif; ?>

                        <div class="product-description mb-4">
                            <?php echo $product['description']; ?>
                        </div>

                    <!-- Отображение медиа -->
                    <div class="product-media mb-4">
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM product_media WHERE product_id = ? ORDER BY sort_order");
                        $stmt->execute([$product['id']]);
                        $media_files = $stmt->fetchAll();
                        
                        $images = array_filter($media_files, fn($m) => $m['type'] == 'image');
                        $videos = array_filter($media_files, fn($m) => $m['type'] == 'video');
                        
                        if (!empty($videos) || !empty($images)): 
                        ?>
                        <ul class="nav nav-tabs" id="mediaTab" role="tablist">
                            <?php if (!empty($videos)): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="videos-tab" data-bs-toggle="tab" 
                                            data-bs-target="#videos" type="button" role="tab">
                                        <i class="bi bi-camera-video"></i> 
                                        Видео (<?php echo count($videos); ?> шт.)
                                    </button>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (!empty($images)): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?php echo empty($videos) ? 'active' : ''; ?>" 
                                            id="images-tab" data-bs-toggle="tab" 
                                            data-bs-target="#images" type="button" role="tab">
                                        <i class="bi bi-images"></i> 
                                        Фото (<?php echo count($images); ?> шт.)
                                    </button>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <div class="tab-content" id="mediaTabContent">
                            <!-- Видео таб -->
                            <?php if (!empty($videos)): ?>
                                <div class="tab-pane fade show active" id="videos" role="tabpanel">
                                    <div class="swiper videoSwiper">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($videos as $video): ?>
                                                <div class="swiper-slide">
                                                    <div class="video-container">
                                                        <div class="video-wrapper" data-video-url="<?php echo htmlspecialchars($video['url']); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="swiper-pagination"></div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Фото таб -->
                            <?php if (!empty($images)): ?>
                                <div class="tab-pane fade <?php echo empty($videos) ? 'show active' : ''; ?>" 
                                    id="images" role="tabpanel">
                                    <div class="swiper imageSwiper">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($images as $image): ?>
                                                <div class="swiper-slide">
                                                    <img src="<?php echo htmlspecialchars($image['url']); ?>" 
                                                        class="img-fluid rounded" 
                                                        alt="Изображение товара">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="swiper-pagination"></div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($is_purchased): ?>
                            <div class="alert alert-success">
                                <h5>Вы уже приобрели этот товар</h5>
                                <?php if ($product['file_path']): ?>
                                    <a href="download.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-success">
                                        Скачать файл
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Информация о товаре</h5>
                        <p class="card-text">
                            <strong>Категория:</strong> 
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </p>
                        
                        <!-- Отображение цены с учетом скидки -->
                        <div class="price-block mb-3">
                            <?php if ($discount_info): ?>
                                <p class="card-text mb-1">
                                    <strong>Обычная цена:</strong> 
                                    <span class="text-decoration-line-through text-muted">
                                        <?php echo number_format($original_price, 2); ?> ₽
                                    </span>
                                </p>
                                <p class="card-text mb-1">
                                    <strong>Цена со скидкой:</strong> 
                                    <span class="text-danger h4">
                                        <?php echo number_format($final_price, 2); ?> ₽
                                    </span>
                                    <span class="badge bg-danger">
                                        -<?php echo $discount_info['percent']; ?>%
                                    </span>
                                </p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Скидка действует до <?php echo date('d.m.Y H:i', strtotime($discount_info['end_date'])); ?>
                                    </small>
                                </p>
                            <?php else: ?>
                                <p class="card-text">
                                    <strong>Цена:</strong> 
                                    <span class="h4"><?php echo number_format($original_price, 2); ?> ₽</span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <?php if ($product['file_path']): ?>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="bi bi-file-earmark"></i> 
                                    Товар содержит файл для скачивания
                                </small>
                            </p>
                        <?php endif; ?>

                        <?php if (isLoggedIn()): ?>
                            <?php if ($is_purchased): ?>
                                <button class="btn btn-success w-100" disabled>Товар куплен</button>
                            <?php else: ?>
                                <a href="buy.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-primary w-100">Купить</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-secondary w-100">Войдите для покупки</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageSwiper = new Swiper(".imageSwiper", {
                slidesPerView: 1,
                spaceBetween: 30,
                loop: true,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            });
            const videoSwiper = new Swiper(".videoSwiper", {
                slidesPerView: 1,
                spaceBetween: 30,
                loop: true,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                on: {
                    slideChange: function () {
                        const activeSlide = this.slides[this.activeIndex];
                        const videoWrapper = activeSlide.querySelector('.video-wrapper');
                        if (videoWrapper && !videoWrapper.querySelector('iframe')) {
                            loadVideo(videoWrapper);
                        }
                    },
                    init: function() {
                        const activeSlide = this.slides[this.activeIndex];
                        const videoWrapper = activeSlide.querySelector('.video-wrapper');
                        if (videoWrapper) {
                            loadVideo(videoWrapper);
                        }
                    }
                }
            });
        });

        function loadVideo(wrapper) {
            const videoUrl = wrapper.dataset.videoUrl;
            if (!videoUrl) return;

            if (!wrapper.querySelector('iframe')) {
                const iframe = document.createElement('iframe');
                iframe.src = videoUrl;
                iframe.title = "YouTube video";
                iframe.allowFullscreen = true;
                iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
                iframe.loading = "lazy";
                wrapper.appendChild(iframe);
            }
        }
</script>
</body>
</html>