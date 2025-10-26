<?php
/**
 * Исправление 404 ошибок в sitemap
 * Удаление несуществующих страниц из sitemap
 */

require_once('wp-config.php');
require_once('wp-load.php');

echo "🔧 ИСПРАВЛЕНИЕ 404 ОШИБОК В SITEMAP\n";
echo "=====================================\n\n";

// Проблемные URL из sitemap
$problematic_urls = [
    'https://bizfin-pro.ru/kalkulyator-bankovskih-garantij/',
    'https://bizfin-pro.ru/kejsy/',
    'https://bizfin-pro.ru/kontakty/'
];

echo "📋 Проблемные URL для удаления:\n";
foreach ($problematic_urls as $url) {
    echo "   • $url\n";
}
echo "\n";

// Читаем текущий sitemap
$sitemap_content = file_get_contents('sitemap.xml');
if (!$sitemap_content) {
    echo "❌ Ошибка: не удалось прочитать sitemap.xml\n";
    exit(1);
}

echo "📄 Текущий размер sitemap: " . strlen($sitemap_content) . " байт\n";

// Создаем резервную копию
$backup_file = 'sitemap.xml.backup.' . date('Y-m-d_H-i-s');
file_put_contents($backup_file, $sitemap_content);
echo "💾 Создана резервная копия: $backup_file\n";

// Удаляем проблемные URL из sitemap
$lines = explode("\n", $sitemap_content);
$new_lines = [];
$removed_count = 0;

$skip_next = false;
foreach ($lines as $line) {
    if ($skip_next) {
        $skip_next = false;
        continue;
    }
    
    $should_remove = false;
    foreach ($problematic_urls as $url) {
        if (strpos($line, $url) !== false) {
            $should_remove = true;
            $removed_count++;
            echo "🗑️ Удаляем: $url\n";
            break;
        }
    }
    
    if ($should_remove) {
        // Пропускаем следующие 3 строки (lastmod, changefreq, priority)
        $skip_next = true;
        continue;
    }
    
    $new_lines[] = $line;
}

// Сохраняем исправленный sitemap
$new_content = implode("\n", $new_lines);
file_put_contents('sitemap.xml', $new_content);

echo "\n✅ Sitemap исправлен!\n";
echo "📊 Статистика:\n";
echo "   • Удалено URL: $removed_count\n";
echo "   • Новый размер: " . strlen($new_content) . " байт\n";
echo "   • Экономия: " . (strlen($sitemap_content) - strlen($new_content)) . " байт\n\n";

// Проверяем, что sitemap валидный XML
$xml = simplexml_load_string($new_content);
if ($xml === false) {
    echo "❌ Ошибка: исправленный sitemap не является валидным XML!\n";
    echo "🔄 Восстанавливаем из резервной копии...\n";
    file_put_contents('sitemap.xml', $sitemap_content);
    exit(1);
}

echo "✅ Sitemap валидный XML\n";

// Обновляем lastmod для sitemap
$xml = simplexml_load_string($new_content);
$xml->url[0]->lastmod = date('Y-m-d');
$new_content = $xml->asXML();
file_put_contents('sitemap.xml', $new_content);

echo "📅 Обновлена дата модификации sitemap\n\n";

echo "🎯 СЛЕДУЮЩИЕ ШАГИ:\n";
echo "1. Проверить sitemap через Google Search Console\n";
echo "2. Отправить обновленный sitemap в поисковые системы\n";
echo "3. Мониторить 404 ошибки\n\n";

echo "✅ Исправление 404 ошибок в sitemap завершено!\n";
?>

