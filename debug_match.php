<?php
require_once('wp-config.php');

// Проверим несколько конкретных случаев
$test_posts = array(2895, 2889, 2887, 2884, 2880);
$test_images = array(3212, 3211, 3210, 3209, 3208);

echo "=== ОТЛАДКА СОВПАДЕНИЙ ===\n\n";

foreach ($test_posts as $post_id) {
    $post_title = get_the_title($post_id);
    echo "Статья ID $post_id: '$post_title'\n";
    echo "Длина: " . strlen($post_title) . " символов\n";
    echo "Байты: " . bin2hex($post_title) . "\n\n";
}

echo "=== ИЗОБРАЖЕНИЯ ===\n\n";

foreach ($test_images as $image_id) {
    $image_title = get_the_title($image_id);
    echo "Изображение ID $image_id: '$image_title'\n";
    echo "Длина: " . strlen($image_title) . " символов\n";
    echo "Байты: " . bin2hex($image_title) . "\n\n";
}

// Проверим точное совпадение
$post_title = get_the_title(2889);
$image_title = get_the_title(3212);

echo "=== ПРОВЕРКА СОВПАДЕНИЯ ===\n";
echo "Статья: '$post_title'\n";
echo "Изображение: '$image_title'\n";
echo "Совпадают: " . ($post_title === $image_title ? 'ДА' : 'НЕТ') . "\n";
echo "Совпадают (trim): " . (trim($post_title) === trim($image_title) ? 'ДА' : 'НЕТ') . "\n";
