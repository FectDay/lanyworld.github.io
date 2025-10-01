<?php
// Настройки базы данных
define('DB_HOST', 'localhost'); //Хост базы
define('DB_USER', 'user'); //Пользователь
define('DB_PASS', 'pass'); //Пароль от базы
define('DB_NAME', 'dbname'); //Имя базы

// Настройки сайта
define('SITE_NAME', 'ExampleCOM'); //Название сайта
define('SITE_URL', 'https://example.com'); //Адрес сайта

// Настройка мерчанта freekassa
define('MERCHANT_ID', 'ID кассы'); // Ваш merchant ID
define('SECRET_KEY', 'd019******************');   // Ваш секретный ключ
define('SECRET_KEY1', '***********'); //Секретное слово 1
define('SECRET_KEY2', '***********'); //Секретное слово 2

// Включаем отображение ошибок (потом отключим на продакшене)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Добавить константу для пути к загрузкам
define('UPLOADS_PATH', dirname(__DIR__) . '/../minecraft_shop_uploads/');


//Ссылки в будущем добавлю в футер сайта или в другое место =3
define('SOCIAL_TELEGRAM', 'https://t.me/example');
define('SOCIAL_TELEGRAM_NAME', '@example');

define('SOCIAL_DISCORD', 'https://discord.gg/example');
define('SOCIAL_YOUTUBE', 'https://www.youtube.com/example');

define('SOCIAL_EMAIL', 'example@example.com');
