<?php
/**
 * Проверка настроек плагина
 * Company Rating Checker - Check Plugin Settings
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

echo "🔍 ПРОВЕРКА НАСТРОЕК ПЛАГИНА\n";
echo "============================\n\n";

echo "📊 СТАТУС ВСЕХ ИСТОЧНИКОВ ДАННЫХ:\n";
echo "==================================\n";

// Проверяем настройки всех источников
$sources = array(
    'crc_arbitration_enabled' => 'Арбитражные суды',
    'crc_zakupki_enabled' => 'Государственные закупки',
    'crc_fns_enabled' => 'ФНС данные',
    'crc_rosstat_enabled' => 'Росстат данные',
    'crc_efrsb_enabled' => 'ЕФРСБ данные',
    'crc_rnp_enabled' => 'РНП данные',
    'crc_fssp_enabled' => 'ФССП данные',
    'crc_advanced_analytics_enabled' => 'Расширенная аналитика'
);

foreach ($sources as $option => $name) {
    $enabled = get_option($option, 1);
    $status = $enabled ? '✅ Включен' : '❌ Отключен';
    echo "   {$status} {$name} ({$option})\n";
}

echo "\n📈 РАСЧЕТ МАКСИМАЛЬНОГО БАЛЛА:\n";
echo "===============================\n";

$max_score = 100; // Базовый балл
$factors_count = 8; // Базовые факторы

echo "   Базовый балл: {$max_score}\n";
echo "   Базовые факторы: {$factors_count}\n\n";

foreach ($sources as $option => $name) {
    $enabled = get_option($option, 1);
    if ($enabled) {
        switch ($option) {
            case 'crc_arbitration_enabled':
                $max_score += 10;
                $factors_count++;
                echo "   +10 баллов - Арбитражные суды\n";
                break;
            case 'crc_zakupki_enabled':
                $max_score += 10;
                $factors_count++;
                echo "   +10 баллов - Государственные закупки\n";
                break;
            case 'crc_fns_enabled':
                $max_score += 15;
                $factors_count++;
                echo "   +15 баллов - ФНС данные\n";
                break;
            case 'crc_rosstat_enabled':
                $max_score += 10;
                $factors_count++;
                echo "   +10 баллов - Росстат данные\n";
                break;
            case 'crc_efrsb_enabled':
                $max_score += 20;
                $factors_count++;
                echo "   +20 баллов - ЕФРСБ данные\n";
                break;
            case 'crc_rnp_enabled':
                $max_score += 15;
                $factors_count++;
                echo "   +15 баллов - РНП данные\n";
                break;
            case 'crc_fssp_enabled':
                $max_score += 15;
                $factors_count++;
                echo "   +15 баллов - ФССП данные\n";
                break;
        }
    }
}

echo "\n   📊 ИТОГОВЫЙ МАКСИМАЛЬНЫЙ БАЛЛ: {$max_score}\n";
echo "   📈 ИТОГОВОЕ КОЛИЧЕСТВО ФАКТОРОВ: {$factors_count}\n\n";

// Проверяем другие настройки
echo "🔧 ДРУГИЕ НАСТРОЙКИ:\n";
echo "===================\n";

$other_settings = array(
    'crc_dadata_api_key' => 'DaData API ключ',
    'crc_dadata_secret' => 'DaData Secret',
    'crc_fns_api_key' => 'ФНС API ключ',
    'crc_cache_duration' => 'Время кэширования (часы)',
    'crc_debug_mode' => 'Режим отладки'
);

foreach ($other_settings as $option => $name) {
    $value = get_option($option, '');
    if ($option === 'crc_debug_mode') {
        $value = $value ? 'Включен' : 'Отключен';
    } elseif ($option === 'crc_cache_duration') {
        $value = $value ?: '12';
    }
    echo "   {$name}: {$value}\n";
}

echo "\n⏰ Время проверки: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ПРОВЕРКА НАСТРОЕК ЗАВЕРШЕНА!\n";
echo "===============================\n";
?>
