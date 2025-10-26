<?php
/**
 * Проверка настроенных API ключей
 * Company Rating Checker - Check API Keys
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

echo "🔑 ПРОВЕРКА НАСТРОЕННЫХ API КЛЮЧЕЙ\n";
echo "===================================\n\n";

echo "⏰ Время проверки: " . date('Y-m-d H:i:s') . "\n\n";

// Проверяем все API ключи
$api_keys = array(
    'crc_dadata_token' => 'DaData API токен',
    'crc_fns_api_key' => 'ФНС API ключ',
    'crc_fssp_api_key' => 'ФССП API ключ',
    'crc_zakupki_api_key' => 'API ключ госзакупок',
    'crc_rosstat_api_key' => 'Росстат API ключ',
    'crc_efrsb_api_key' => 'ЕФРСБ API ключ',
    'crc_rnp_api_key' => 'РНП API ключ'
);

echo "📋 СТАТУС API КЛЮЧЕЙ:\n";
echo "=====================\n";

foreach ($api_keys as $option_name => $description) {
    $key = get_option($option_name, '');
    $status = !empty($key) ? '✅ УСТАНОВЛЕН' : '❌ НЕ УСТАНОВЛЕН';
    $key_preview = !empty($key) ? substr($key, 0, 8) . '...' : 'Нет';
    
    echo "   {$description}:\n";
    echo "   - Статус: {$status}\n";
    echo "   - Ключ: {$key_preview}\n";
    echo "   - Опция: {$option_name}\n\n";
}

// Проверяем настройки включения/отключения источников
echo "🔧 НАСТРОЙКИ ИСТОЧНИКОВ ДАННЫХ:\n";
echo "===============================\n";

$sources = array(
    'crc_fns_enabled' => 'ФНС данные',
    'crc_fssp_enabled' => 'ФССП данные',
    'crc_zakupki_enabled' => 'Госзакупки',
    'crc_rosstat_enabled' => 'Росстат данные',
    'crc_efrsb_enabled' => 'ЕФРСБ данные',
    'crc_rnp_enabled' => 'РНП данные'
);

foreach ($sources as $option_name => $description) {
    $enabled = get_option($option_name, 1);
    $status = $enabled ? '✅ ВКЛЮЧЕН' : '❌ ОТКЛЮЧЕН';
    
    echo "   {$description}: {$status}\n";
}

echo "\n";

// Проверяем доступность реальных API
echo "🌐 ПРОВЕРКА ДОСТУПНОСТИ API:\n";
echo "============================\n";

$apis_to_check = array(
    'https://api-fns.ru/api/' => 'ФНС API',
    'https://fssp.gov.ru/iss/ip/' => 'ФССП API',
    'https://zakupki.gov.ru/' => 'Госзакупки API',
    'https://rosstat.gov.ru/' => 'Росстат API',
    'https://bankrot.fedresurs.ru/' => 'ЕФРСБ API',
    'https://zakupki.gov.ru/epz/dishonestsupplier' => 'РНП API'
);

foreach ($apis_to_check as $url => $name) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'HEAD'
        ]
    ]);
    
    $headers = @get_headers($url, 1, $context);
    $status = $headers && strpos($headers[0], '200') !== false ? '✅ ДОСТУПЕН' : '❌ НЕДОСТУПЕН';
    
    echo "   {$name}: {$status}\n";
    echo "   URL: {$url}\n\n";
}

echo "⏰ Время завершения проверки: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ПРОВЕРКА API КЛЮЧЕЙ ЗАВЕРШЕНА!\n";
echo "==================================\n";
?>
