<?php
/*
Plugin Name: X-Frame-Options SAMEORIGIN Fix
Description: Устанавливает X-Frame-Options: SAMEORIGIN, чтобы Elementor мог загружать превью во фрейме.
Version: 1.0
*/

add_action('send_headers', function () {
    // Снимаем возможный заголовок от сервера и ставим SAMEORIGIN
    if (function_exists('header_remove')) {
        @header_remove('X-Frame-Options');
    }
    header('X-Frame-Options: SAMEORIGIN');
    // Современный способ контроля фреймов (перекрывает X-Frame-Options в большинстве браузеров)
    header("Content-Security-Policy: frame-ancestors 'self'");
}, 0);

?>


