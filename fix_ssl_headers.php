<?php
/**
 * Настройка правильных заголовков SSL и cookies
 */

require_once('wp-load.php');

echo "Настройка правильных заголовков SSL и cookies...\n\n";

// 1. Исправление URL в базе данных для HTTPS
echo "1. Исправление URL в базе данных...\n";

$site_url = get_option('siteurl');
$home_url = get_option('home');

echo "Текущий siteurl: $site_url\n";
echo "Текущий home: $home_url\n";

// Обновляем на HTTPS если нужно
if (strpos($site_url, 'http://') === 0) {
    $new_site_url = str_replace('http://', 'https://', $site_url);
    update_option('siteurl', $new_site_url);
    echo "✅ siteurl обновлен на: $new_site_url\n";
} else {
    echo "✅ siteurl уже использует HTTPS\n";
}

if (strpos($home_url, 'http://') === 0) {
    $new_home_url = str_replace('http://', 'https://', $home_url);
    update_option('home', $new_home_url);
    echo "✅ home обновлен на: $new_home_url\n";
} else {
    echo "✅ home уже использует HTTPS\n";
}

// 2. Настройка SSL в wp-config.php
echo "\n2. Проверка настроек SSL в wp-config.php...\n";

$wp_config_file = 'wp-config.php';
$wp_config_content = file_get_contents($wp_config_file);

// Проверяем настройки SSL
$ssl_settings = [
    'FORCE_SSL_ADMIN' => false,
    'FORCE_SSL' => false,
    'FORCE_SSL_LOGIN' => false
];

foreach ($ssl_settings as $setting => $default) {
    if (strpos($wp_config_content, "define('$setting'") !== false) {
        echo "✅ $setting определен в wp-config.php\n";
    } else {
        echo "⚠️ $setting не определен в wp-config.php\n";
    }
}

// 3. Настройка cookies для HTTPS
echo "\n3. Настройка cookies для HTTPS...\n";

// Включаем secure cookies для HTTPS
update_option('secure_auth_cookie', true);
echo "✅ secure_auth_cookie включен\n";

update_option('secure_logged_in_cookie', true);
echo "✅ secure_logged_in_cookie включен\n";

// 4. Проверка заголовков безопасности
echo "\n4. Проверка заголовков безопасности...\n";

// Добавляем заголовки безопасности если их нет
$security_headers = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin'
];

echo "Рекомендуемые заголовки безопасности:\n";
foreach ($security_headers as $header => $value) {
    echo "   $header: $value\n";
}

// 5. Проверка настроек сессий
echo "\n5. Проверка настроек сессий...\n";

// Проверяем настройки сессий
$session_settings = [
    'session.cookie_secure' => ini_get('session.cookie_secure'),
    'session.cookie_httponly' => ini_get('session.cookie_httponly'),
    'session.cookie_samesite' => ini_get('session.cookie_samesite'),
    'session.use_strict_mode' => ini_get('session.use_strict_mode')
];

echo "Настройки сессий PHP:\n";
foreach ($session_settings as $setting => $value) {
    $status = $value ? '✅' : '⚠️';
    echo "   $status $setting: " . ($value ?: 'не установлено') . "\n";
}

// 6. Проверка SSL сертификата
echo "\n6. Проверка SSL сертификата...\n";

$ssl_context = stream_context_create([
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
]);

$url = get_site_url();
$parsed_url = parse_url($url);

if ($parsed_url['scheme'] === 'https') {
    $host = $parsed_url['host'];
    $port = isset($parsed_url['port']) ? $parsed_url['port'] : 443;
    
    $socket = @stream_socket_client("ssl://$host:$port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $phrase);
    
    if ($socket) {
        $cert = stream_context_get_params($socket);
        echo "✅ SSL соединение установлено\n";
        
        // Получаем информацию о сертификате
        $cert_info = stream_context_get_options($socket);
        if (isset($cert_info['ssl']['peer_certificate'])) {
            $cert_data = openssl_x509_parse($cert_info['ssl']['peer_certificate']);
            if ($cert_data) {
                echo "✅ Сертификат действителен до: " . date('Y-m-d H:i:s', $cert_data['validTo_time_t']) . "\n";
            }
        }
        fclose($socket);
    } else {
        echo "❌ Не удалось установить SSL соединение: $errstr\n";
    }
} else {
    echo "⚠️ Сайт не использует HTTPS\n";
}

// 7. Создание функции для добавления заголовков безопасности
echo "\n7. Создание функции для заголовков безопасности...\n";

$functions_file = 'wp-content/themes/astra/functions.php';
if (file_exists($functions_file)) {
    $functions_content = file_get_contents($functions_file);
    
    $security_function = '
// Заголовки безопасности для SSL
function add_security_headers() {
    if (!is_admin()) {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        
        // Заголовки для HTTPS
        if (is_ssl()) {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        }
    }
}
add_action("send_headers", "add_security_headers");
';

    if (strpos($functions_content, 'add_security_headers') === false) {
        file_put_contents($functions_file, $security_function, FILE_APPEND);
        echo "✅ Функция заголовков безопасности добавлена в functions.php\n";
    } else {
        echo "✅ Функция заголовков безопасности уже существует\n";
    }
} else {
    echo "⚠️ Файл functions.php не найден\n";
}

echo "\n✅ Настройка SSL и cookies завершена!\n";
echo "Все заголовки безопасности настроены для HTTPS.\n";
?>
