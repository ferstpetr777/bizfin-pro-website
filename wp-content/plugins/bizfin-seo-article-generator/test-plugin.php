<?php
/**
 * Простой тест плагина
 */

echo "=== Тест плагина BizFin SEO Article Generator ===\n\n";

// Проверяем, что файлы существуют
$files_to_check = [
    'bizfin-seo-article-generator.php',
    'includes/tone-style-generator.php',
    'includes/quality-system.php',
    'includes/prompt-chaining-system.php'
];

echo "Проверка файлов:\n";
foreach ($files_to_check as $file) {
    $file_path = __DIR__ . '/' . $file;
    if (file_exists($file_path)) {
        echo "✅ {$file} - существует\n";
    } else {
        echo "❌ {$file} - не найден\n";
    }
}

echo "\nПроверка синтаксиса PHP:\n";

// Проверяем синтаксис основного файла
$main_file = __DIR__ . '/bizfin-seo-article-generator.php';
$output = [];
$return_code = 0;
exec("php -l {$main_file} 2>&1", $output, $return_code);

if ($return_code === 0) {
    echo "✅ Основной файл плагина - синтаксис корректен\n";
} else {
    echo "❌ Основной файл плагина - ошибки синтаксиса:\n";
    foreach ($output as $line) {
        echo "   {$line}\n";
    }
}

// Проверяем синтаксис генератора тона
$tone_file = __DIR__ . '/includes/tone-style-generator.php';
$output = [];
$return_code = 0;
exec("php -l {$tone_file} 2>&1", $output, $return_code);

if ($return_code === 0) {
    echo "✅ Генератор тона и стиля - синтаксис корректен\n";
} else {
    echo "❌ Генератор тона и стиля - ошибки синтаксиса:\n";
    foreach ($output as $line) {
        echo "   {$line}\n";
    }
}

echo "\nПроверка структуры матрицы:\n";

// Читаем основной файл и ищем матрицу
$content = file_get_contents($main_file);
if (strpos($content, 'global_tone_style') !== false) {
    echo "✅ Критерии глобального тона найдены в матрице\n";
} else {
    echo "❌ Критерии глобального тона не найдены в матрице\n";
}

if (strpos($content, 'introduction_template') !== false) {
    echo "✅ Шаблон введения найден в матрице\n";
} else {
    echo "❌ Шаблон введения не найден в матрице\n";
}

if (strpos($content, 'BizFin_Tone_Style_Generator') !== false) {
    echo "✅ Генератор тона интегрирован в основной файл\n";
} else {
    echo "❌ Генератор тона не интегрирован в основной файл\n";
}

echo "\n=== Тест завершён ===\n";

