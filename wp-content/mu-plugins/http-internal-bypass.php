<?php
/*
Plugin Name: HTTP Internal Bypass
Description: Отключает любые прокси для внутренних URL (собственный домен, админка, ajax, REST, Elementor).
Version: 1.0
Author: Ops
*/

add_filter('http_request_args', function ($args, $url) {
    $site_host = parse_url(home_url(), PHP_URL_HOST);
    $req_host  = parse_url($url, PHP_URL_HOST);
    $req_path  = (string) parse_url($url, PHP_URL_PATH);

    $is_same_host = ($site_host && $req_host && strtolower($site_host) === strtolower($req_host));
    $is_admin_or_internal = (
        strpos($req_path, '/wp-admin/') !== false ||
        strpos($req_path, 'admin-ajax.php') !== false ||
        strpos($req_path, '/wp-json/') !== false ||
        strpos($req_path, '/elementor/') !== false ||
        strpos($req_path, '/wp-cron.php') !== false
    );

    if ($is_same_host || $is_admin_or_internal) {
        unset($args['proxy']);
        $args['sslverify'] = true; // по умолчанию
    }
    return $args;
}, 999, 2);

add_filter('pre_http_request', function ($response, $parsed_args, $url) {
    $site_host = parse_url(home_url(), PHP_URL_HOST);
    $req_host  = parse_url($url, PHP_URL_HOST);
    $req_path  = (string) parse_url($url, PHP_URL_PATH);

    $is_same_host = ($site_host && $req_host && strtolower($site_host) === strtolower($req_host));
    $is_admin_or_internal = (
        strpos($req_path, '/wp-admin/') !== false ||
        strpos($req_path, 'admin-ajax.php') !== false ||
        strpos($req_path, '/wp-json/') !== false ||
        strpos($req_path, '/elementor/') !== false ||
        strpos($req_path, '/wp-cron.php') !== false
    );

    if ($is_same_host || $is_admin_or_internal) {
        // Ничего не перехватываем для внутренних запросов
        return null;
    }
    return $response;
}, 999, 3);

?>


