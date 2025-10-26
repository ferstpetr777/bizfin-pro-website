<?php
/*
Plugin Name: HTTP Connection Fix
Description: Исправляет проблемы с HTTP соединениями для AI плагинов
Version: 1.0
Author: System Admin
*/

// Добавляем фильтры для улучшения HTTP соединений
add_filter('http_request_args', 'fix_http_request_args', 10, 2);
add_filter('http_request_timeout', 'increase_http_timeout');

// КРИТИЧЕСКОЕ ИСПРАВЛЕНИЕ: Заменяем wp_remote_post на прямой cURL для API запросов
add_filter('pre_http_request', 'bypass_wp_remote_for_openai', 10, 3);

function fix_http_request_args($args, $url) {
    // Увеличиваем таймаут для API запросов
    if (strpos($url, 'api.openai.com') !== false || 
        strpos($url, 'api.anthropic.com') !== false) {
        $args['timeout'] = 120;
        $args['redirection'] = 5;
        $args['httpversion'] = '1.1';
        $args['user-agent'] = 'WordPress/' . get_bloginfo('version') . '; ' . home_url();
        $args['reject_unsafe_urls'] = false;
        $args['sslverify'] = true;
        
        // Добавляем дополнительные заголовки для стабильности
        if (!isset($args['headers'])) {
            $args['headers'] = array();
        }
        $args['headers']['Connection'] = 'keep-alive';
        $args['headers']['Keep-Alive'] = 'timeout=120, max=1000';
    }
    
    return $args;
}

function increase_http_timeout($timeout) {
    return 120; // Увеличиваем таймаут до 120 секунд
}

// Добавляем обработку ошибок cURL
add_filter('http_response', 'handle_http_response_errors', 10, 3);

function handle_http_response_errors($response, $args, $url) {
    if (is_wp_error($response)) {
        $error_code = $response->get_error_code();
        $error_message = $response->get_error_message();
        
        // Логируем ошибки для отладки
        error_log("HTTP Error for $url: $error_code - $error_message");
        
        // Если это ошибка прокси или соединения, пробуем альтернативные методы
        if (strpos($error_message, '503') !== false || 
            strpos($error_message, 'proxy') !== false ||
            strpos($error_message, 'CONNECT') !== false) {
            
            // Пробуем повторить запрос с другими настройками
            $args['timeout'] = 60;
            $args['sslverify'] = false;
            $args['headers']['User-Agent'] = 'Mozilla/5.0 (compatible; WordPress; +https://wordpress.org/)';
            
            // Повторяем запрос
            $retry_response = wp_remote_request($url, $args);
            
            if (!is_wp_error($retry_response)) {
                return $retry_response;
            }
        }
    }
    
    return $response;
}

// Добавляем поддержку альтернативных DNS серверов
add_action('init', 'setup_dns_fallback');

function setup_dns_fallback() {
    // Устанавливаем альтернативные DNS серверы для улучшения резолвинга
    if (function_exists('dns_get_record')) {
        ini_set('default_socket_timeout', 120);
    }
}

// КРИТИЧЕСКАЯ ФУНКЦИЯ: Обход WordPress HTTP API для OpenAI запросов
function bypass_wp_remote_for_openai($response, $parsed_args, $url) {
    // Проверяем, является ли это запросом к OpenAI или Anthropic API
    if (strpos($url, 'api.openai.com') !== false || 
        strpos($url, 'api.anthropic.com') !== false) {
        
        // Логируем попытку обхода
        error_log("HTTP Fix: Обход WordPress HTTP API для $url");
        
        // Используем прямой cURL вместо wp_remote_post
        $curl_response = make_direct_curl_request($url, $parsed_args);
        
        if ($curl_response !== false) {
            return $curl_response;
        }
    }
    
    return $response; // Возвращаем оригинальный ответ, если обход не сработал
}

function make_direct_curl_request($url, $args) {
    // Инициализируем cURL
    $ch = curl_init();
    
    // Базовые настройки
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, isset($args['timeout']) ? $args['timeout'] : 120);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, isset($args['redirection']) ? $args['redirection'] : 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_CAINFO, '/etc/ssl/certs/ca-certificates.crt');
    
    // Настройки HTTP версии
    if (isset($args['httpversion']) && $args['httpversion'] === '1.1') {
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    } else {
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
    }
    
    // Заголовки
    if (isset($args['headers']) && is_array($args['headers'])) {
        $curl_headers = array();
        foreach ($args['headers'] as $key => $value) {
            $curl_headers[] = "$key: $value";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
    }
    
    // Тело запроса (для POST)
    if (isset($args['body']) && !empty($args['body'])) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args['body']);
    }
    
    // User-Agent
    if (!isset($args['headers']['User-Agent'])) {
        curl_setopt($ch, CURLOPT_USERAGENT, 'WordPress/' . get_bloginfo('version') . '; ' . home_url());
    }
    
    // Дополнительные настройки для стабильности
    curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
    curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 30);
    curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 10);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    
    // Выполняем запрос
    $response_body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_errno = curl_errno($ch);
    
    // Получаем заголовки ответа
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $response_headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    
    curl_close($ch);
    
    // Обрабатываем результат
    if ($curl_error) {
        error_log("HTTP Fix: cURL Error - $curl_error (Code: $curl_errno)");
        return new WP_Error('http_request_failed', "cURL Error: $curl_error", array('curl_errno' => $curl_errno));
    }
    
    // Создаем ответ в формате WordPress
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
    
    // Парсим заголовки ответа
    if ($response_headers) {
        $header_lines = explode("\r\n", $response_headers);
        foreach ($header_lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $response['headers'][trim($key)] = trim($value);
            }
        }
    }
    
    error_log("HTTP Fix: Успешный обход для $url - HTTP $http_code");
    return $response;
}
?>
