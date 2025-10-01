<head>
    <?php include 'includes/meta.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<?php
// Список изображений
$images = [
    'img1.jpg'
];

// Генерируем HTML-код карусели
echo '<div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">';
echo '<div class="carousel-inner">';

// Первая картинка активна по умолчанию
for ($i = 0; $i < count($images); $i++) {
    $activeClass = ($i === 0) ? ' active' : '';
    echo '<div class="carousel-item'.$activeClass.'">';
    echo '<img src="../images/'.$images[$i].'" class="d-block w-100" alt="'.$images[$i].'">';
    echo '</div>';
}

echo '</div>';

// Управляющие стрелки
echo '<button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">';
echo '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
echo '<span class="visually-hidden">Previous</span>';
echo '</button>';

echo '<button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">';
echo '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
echo '<span class="visually-hidden">Next</span>';
echo '</button>';

echo '</div>';
?>