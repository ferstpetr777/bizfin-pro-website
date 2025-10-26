<?php
/*
Plugin Name: Dutch Proxy Integration for AI-Scribe
Description: Принудительная интеграция голландского прокси для AI-Scribe.
Version: 1.0
Author: System Admin
*/

// Голландский прокси сервер (прямое подключение)
if (!defined('DUTCH_PROXY_HOST')) {
    define('DUTCH_PROXY_HOST', '89.110.80.198');
}
if (!defined('DUTCH_PROXY_PORT')) {
    define('DUTCH_PROXY_PORT', '8889');
}
if (!defined('DUTCH_PROXY_URL')) {
    define('DUTCH_PROXY_URL', 'http://' . DUTCH_PROXY_HOST . ':' . DUTCH_PROXY_PORT);
}

// Принудительно устанавливаем прокси для всех HTTP запросов
add_filter('http_request_args', 'force_dutch_proxy_for_all_requests', 10, 2);

function force_dutch_proxy_for_all_requests($args, $url) {
    // Никогда не проксируем запросы к собственному домену (любой протокол)
    $site_host = parse_url(home_url(), PHP_URL_HOST);
    $req_host  = parse_url($url, PHP_URL_HOST);

    // Путь запроса (для исключения админки/Elementor/REST)
    $req_path = (string) parse_url($url, PHP_URL_PATH);

    $is_same_host = ($site_host && $req_host && strtolower($site_host) === strtolower($req_host));
    $is_admin_or_internal = (
        strpos($req_path, '/wp-admin/') !== false ||
        strpos($req_path, 'admin-ajax.php') !== false ||
        strpos($req_path, '/wp-json/') !== false ||
        strpos($req_path, '/elementor/') !== false
    );

    // Проксируем только внешние запросы и не админку/элементы редактора
    if (!$is_same_host && !$is_admin_or_internal) {
        $args['proxy'] = DUTCH_PROXY_URL;
        $args['timeout'] = 60;
        $args['redirection'] = 5;
        $args['sslverify'] = false; // Для прокси может потребоваться
        error_log("Dutch Proxy: Принудительно используем голландский прокси " . DUTCH_PROXY_URL . " для URL: " . $url);
    }

    return $args;
}

// Перехватываем все HTTP запросы для AI API
add_filter('pre_http_request', 'bypass_wp_remote_with_dutch_proxy', 10, 3);

function bypass_wp_remote_with_dutch_proxy($response, $parsed_args, $url) {
    // Не перехватываем собственный домен никогда
    $site_host = parse_url(home_url(), PHP_URL_HOST);
    $req_host  = parse_url($url, PHP_URL_HOST);
    if ($site_host && $req_host && strtolower($site_host) === strtolower($req_host)) {
        return $response;
    }

    // Перехватываем запросы к OpenAI и Anthropic (оставляем как есть)
    if (strpos($url, 'api.openai.com') !== false ||
        strpos($url, 'api.anthropic.com') !== false) {

        error_log("Dutch Proxy: Перехватываем запрос к $url для использования голландского прокси");

        $curl_response = make_dutch_proxy_curl_request($url, $parsed_args);

        if ($curl_response !== false) {
            return $curl_response;
        }
    }
    
    return $response;
}

function make_dutch_proxy_curl_request($url, $args) {
    $ch = curl_init();
    
    // Базовые настройки cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, isset($args['timeout']) ? $args['timeout'] : 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, isset($args['redirection']) ? $args['redirection'] : 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // ОБЯЗАТЕЛЬНЫЙ голландский прокси
    curl_setopt($ch, CURLOPT_PROXY, DUTCH_PROXY_URL);
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
    
    // Заголовки
    if (isset($args['headers']) && is_array($args['headers'])) {
        $curl_headers = array();
        foreach ($args['headers'] as $key => $value) {
            $curl_headers[] = "$key: $value";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
    }
    
    // POST данные
    if (isset($args['body']) && !empty($args['body'])) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args['body']);
    }
    
    // User-Agent
    if (!isset($args['headers']['User-Agent'])) {
        curl_setopt($ch, CURLOPT_USERAGENT, 'WordPress/' . get_bloginfo('version') . '; ' . home_url());
    }
    
    // Настройки соединения
    curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
    curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 30);
    curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 10);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    
    error_log("Dutch Proxy: Выполняем cURL запрос через голландский прокси " . DUTCH_PROXY_URL);
    
    $response_body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_errno = curl_errno($ch);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    
    curl_close($ch);
    
    if ($curl_error) {
        error_log("Dutch Proxy: cURL Error через голландский прокси: $curl_error (Code: $curl_errno)");
        return false;
    }
    
    error_log("Dutch Proxy: Успешный запрос через голландский прокси: HTTP $http_code, время: " . round($total_time, 2) . "с");
    
    $response = array(
        'headers' => new Requests_Utility_CaseInsensitiveDictionary(array()),
        'body' => $response_body,
        'response' => array(
            'code' => $http_code,
            'message' => get_status_header_desc($http_code)
        ),
        'cookies' => array(),
        'filename' => null
    );
    
    return $response;
}

// Увеличиваем таймауты для работы с прокси
add_filter('http_request_timeout', function($timeout) {
    return 120; // 2 минуты для работы через прокси
});

add_filter('http_connect_timeout', function($timeout) {
    return 60; // 1 минута для подключения через прокси
});

// Логируем все HTTP запросы для отладки
add_action('http_api_debug', 'log_dutch_proxy_requests', 10, 5);

function log_dutch_proxy_requests($response, $context, $transport, $args, $url) {
    if (strpos($url, 'api.openai.com') !== false || strpos($url, 'api.anthropic.com') !== false) {
        error_log("Dutch Proxy Debug: URL=$url, Args=" . print_r($args, true));
        if (is_wp_error($response)) {
            error_log("Dutch Proxy Debug: WP_Error=" . $response->get_error_message());
        } else {
            error_log("Dutch Proxy Debug: Response Code=" . wp_remote_retrieve_response_code($response));
        }
    }
}
?>
