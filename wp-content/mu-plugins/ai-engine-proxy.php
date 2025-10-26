<?php
/*
Plugin Name: AI Engine Proxy Bridge
Description: Настраивает прокси для запросов AI Engine/любых плагинов к OpenAI/Anthropic через голландский прокси, не затрагивая остальное.
Version: 1.0
Author: System Admin
*/

if (!defined('DUTCH_PROXY_HOST')) define('DUTCH_PROXY_HOST', '89.110.80.198');
if (!defined('DUTCH_PROXY_PORT')) define('DUTCH_PROXY_PORT', '8889');
if (!defined('DUTCH_PROXY_URL'))  define('DUTCH_PROXY_URL', 'http://' . DUTCH_PROXY_HOST . ':' . DUTCH_PROXY_PORT);

function ai_engine_targeted_proxy_url($url) {
    return (strpos($url, 'api.openai.com') !== false) || (strpos($url, 'api.anthropic.com') !== false);
}

// 1) Для тех, кто использует WP HTTP API
add_filter('http_request_args', function ($args, $url) {
    if (ai_engine_targeted_proxy_url($url)) {
        $args['proxy']       = DUTCH_PROXY_URL; // http://host:port
        $args['timeout']     = isset($args['timeout']) ? max(60, (int)$args['timeout']) : 60;
        $args['redirection'] = isset($args['redirection']) ? max(5, (int)$args['redirection']) : 5;
        $args['sslverify']   = false; // через прокси может требоваться
    }
    return $args;
}, 20, 2);

// 1b) Установим переменные окружения прокси для любых прямых cURL, когда целевой URL — OpenAI/Anthropic
add_filter('pre_http_request', function($response, $parsed_args, $url){
    if (ai_engine_targeted_proxy_url($url)) {
        @putenv('https_proxy=' . DUTCH_PROXY_URL);
        @putenv('http_proxy='  . DUTCH_PROXY_URL);
        @putenv('HTTPS_PROXY=' . DUTCH_PROXY_URL);
        @putenv('HTTP_PROXY='  . DUTCH_PROXY_URL);
        // исключения: локальные обращения
        $noProxy = parse_url(home_url(), PHP_URL_HOST);
        if ($noProxy) {
            @putenv('no_proxy=' . $noProxy . ',localhost,127.0.0.1');
            @putenv('NO_PROXY=' . $noProxy . ',localhost,127.0.0.1');
        }
    }
    return $response;
}, 1, 3);

// 2) Для тех, кто использует curl напрямую через WP transport
add_action('http_api_curl', function ($handle, $r, $url) {
    if (ai_engine_targeted_proxy_url($url)) {
        curl_setopt($handle, CURLOPT_PROXY, DUTCH_PROXY_URL);
        curl_setopt($handle, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($handle, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($handle, CURLOPT_TIMEOUT, isset($r['timeout']) ? max(60, (int)$r['timeout']) : 60);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
    }
}, 10, 3);

// 3) Увеличим таймауты только для целевых запросов (через фильтры глобально, но логика в http_request_args выше)
add_filter('http_request_timeout', function ($timeout) { return max(60, (int)$timeout); });
add_filter('http_connect_timeout', function ($timeout) { return max(30, (int)$timeout); });

// 4) Логи для отладки
add_action('http_api_debug', function ($response, $context, $transport, $args, $url) {
    if (ai_engine_targeted_proxy_url($url)) {
        error_log('[AI Engine Proxy] ' . $context . ' via ' . $transport . ' URL=' . $url);
    }
}, 10, 5);
