<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include 'includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Официальная страница</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <link rel="icon" href="../favicon.png" type="image/png">
    <style>
        /* Основные стили для вертикальной карусели */
        .vertical-carousel {
            height: 100vh; /* Устанавливаем высоту страницы на полный экран */
            overflow-y: scroll; /* Прокручиваем по вертикали */
            scroll-behavior: smooth; /* Плавная прокрутка */
        }
        .vertical-carousel > div {
            height: 100vh; /* Высота каждого слайда - полный экран */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .vertical-carousel::-webkit-scrollbar {
            display: none; /* Скрываем полосу прокрутки */
        }
        .vertical-carousel {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        /* Стили для навигации */
        .thumbnail-navigation {
            position: fixed;
            top: 50%;
            left: 20px;
            transform: translateY(-50%); /* Центрирование по вертикали */
            display: flex;
            flex-direction: column; /* Расположим элементы вертикально */
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .thumbnail-navigation li {
            margin-bottom: 10px;
        }
        .thumbnail-navigation img {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .thumbnail-navigation img.active {
            filter: brightness(1.2);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

        <div class="wrapper">
        <!-- Основной контент -->
         <div class="container mt-4">
                <?php include 'scripts/carousel.php'; ?>
        </div>
    </div>

    <div class="vertical-carousel">
        <!-- Секция приветствия -->
        <div class="wrapper" id="slide1">
            <div class="container text-center">
                <p>"Виртуальность становится реальностью, будущее уже наступило."</p>
                <div class="carousel monitoringindex"><?php include 'monitoring/status.html'; ?></div>
            </div>
        </div>


        <!-- Секция новостей -->
        <div class="wrapper" id="slide2">
            <div class="container">
                <?php include 'news.php'; ?>
            </div>
        </div>

        <!-- Секция преимуществ -->
        <div class="wrapper" id="slide3">
            <div class="container">
                <div class="advantages">
                    <?php include 'scripts/advantages.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Навигация точками -->
    <ul class="thumbnail-navigation">
        <li><img src="images/preview1.png" onclick="goToSlide('slide1')" class="active"/></li>
        <li><img src="images/preview2.png" onclick="goToSlide('slide2')"/></li>
        <li><img src="images/preview3.png" onclick="goToSlide('slide3')"/></li>
    </ul>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function goToSlide(id) {
            document.getElementById(id).scrollIntoView({behavior: 'smooth'});
            updateNavigationThumbnails(id);
        }

        function updateNavigationThumbnails(activeId) {
            const thumbnails = document.querySelectorAll('.thumbnail-navigation img');
            thumbnails.forEach(thumbnail => {
                thumbnail.classList.remove('active');
            });
            document.querySelector(`.thumbnail-navigation img[onclick^="goToSlide('${activeId}')"]`).classList.add('active');
        }

        // Установка активной миниатюры при первом запуске
        updateNavigationThumbnails('slide1');
    </script>
</body>
</html>