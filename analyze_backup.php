<?php
// Анализируем архив с оригинальным контентом
$backup_file = 'original_content_backup.json';

if (!file_exists($backup_file)) {
    echo "❌ Файл архива не найден: $backup_file\n";
    exit;
}

$backup_data = json_decode(file_get_contents($backup_file), true);

if (!$backup_data) {
    echo "❌ Ошибка при чтении архива\n";
    exit;
}

echo "=== АНАЛИЗ АРХИВА ОРИГИНАЛЬНОГО КОНТЕНТА ===\n";
echo "Всего статей в архиве: " . count($backup_data) . "\n\n";

$total_size = 0;
$template_count = 0;
$unique_count = 0;

foreach ($backup_data as $post_id => $article) {
    $title = $article['title'];
    $content = $article['content'];
    $content_size = strlen($content);
    $total_size += $content_size;
    
    // Проверяем, является ли контент шаблонным
    $is_template = (
        strpos($content, 'Подробное описание темы статьи') !== false ||
        strpos($content, 'Полное руководство по теме статьи') !== false ||
        strpos($content, 'Что это такое?') !== false ||
        strpos($content, 'Как это работает?') !== false
    );
    
    if ($is_template) {
        $template_count++;
    } else {
        $unique_count++;
    }
    
    echo "ID: $post_id\n";
    echo "Заголовок: " . substr($title, 0, 80) . "...\n";
    echo "Размер контента: $content_size символов\n";
    echo "Тип: " . ($is_template ? "❌ Шаблонный" : "✅ Уникальный") . "\n";
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

echo "=== ИТОГИ АНАЛИЗА ===\n";
echo "Всего статей: " . count($backup_data) . "\n";
echo "Шаблонных статей: $template_count\n";
echo "Уникальных статей: $unique_count\n";
echo "Общий размер контента: $total_size символов\n";
echo "Средний размер статьи: " . round($total_size / count($backup_data)) . " символов\n";

if ($unique_count == 0) {
    echo "\n❌ ВНИМАНИЕ: Все статьи в архиве содержат только шаблонный контент!\n";
    echo "Оригинального уникального контента для этих статей не существует.\n";
    echo "Необходимо создать уникальный контент для каждой статьи.\n";
}
