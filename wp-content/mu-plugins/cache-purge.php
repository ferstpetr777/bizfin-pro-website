<?php
/*
Plugin Name: Bizfin Cache Purge (MU)
Description: REST эндпоинт для очистки кэша и dev-заголовки no-cache. Автоподгружается как MU-плагин.
Author: Ops
Version: 1.0.0
*/

// Добавляем no-cache заголовки на уровне WP, чтобы исключить промежуточное кэширование
add_action('send_headers', function () {
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
});

// REST: /wp-json/bizfin/v1/purge
add_action('rest_api_init', function () {
    register_rest_route('bizfin/v1', '/purge', [
        'methods'  => 'GET',
        'callback' => function () {
            // Разрешаем только локальные запросы с сервера
            $allowed = [
                '127.0.0.1',
                '::1',
                '46.149.67.20', // сервер из nginx-конфига
            ];
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            if (!in_array($ip, $allowed, true)) {
                return new WP_REST_Response(['status' => 'forbidden', 'ip' => $ip], 403);
            }

            // Сброс внутреннего кэша/объектного кэша, если включен
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
            }

            // Перестроить правила
            flush_rewrite_rules(false);

            // Плагины кэширования (если активны)
            if (function_exists('w3tc_flush_all')) {
                @w3tc_flush_all();
            }
            if (class_exists('WPO_Cache_Config')) { // WP-Optimize
                try { do_action('wpo_cache_flush'); } catch (Throwable $e) {}
            }
            if (function_exists('rocket_clean_domain')) { // WP Rocket
                @rocket_clean_domain();
            }

            // OPcache
            if (function_exists('opcache_reset')) { @opcache_reset(); }

            return new WP_REST_Response(['status' => 'ok', 'ts' => time()], 200);
        },
        'permission_callback' => '__return_true',
    ]);
});



