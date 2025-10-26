<?php
/*
Plugin Name: HTTP API Force Proxy
Description: Enforces HTTP proxy for outbound requests to OpenAI/Anthropic so plugins (incl. AI‑Scribe) always use proxy.
*/

add_filter('http_request_args', function ($args, $url) {
    $targets = [
        'api.openai.com',
        'openai.com',
        'api.anthropic.com',
        'anthropic.com'
    ];
    $host = parse_url($url, PHP_URL_HOST);
    if ($host && in_array($host, $targets, true)) {
        $args['proxy'] = 'http://127.0.0.1:8118';
        // Increase timeout for long generations
        if (empty($args['timeout']) || $args['timeout'] < 110) {
            $args['timeout'] = 110;
        }
    }
    return $args;
}, 10, 2);

add_action('http_api_curl', function ($handle, $r, $url) {
    $targets = [
        'api.openai.com',
        'openai.com',
        'api.anthropic.com',
        'anthropic.com'
    ];
    $host = parse_url($url, PHP_URL_HOST);
    if ($host && in_array($host, $targets, true)) {
        curl_setopt($handle, CURLOPT_PROXY, '127.0.0.1');
        curl_setopt($handle, CURLOPT_PROXYPORT, 8118);
        curl_setopt($handle, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        // DNS через прокси не критично для HTTP-прокси
    }
}, 10, 3);


