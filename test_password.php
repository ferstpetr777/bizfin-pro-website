<?php
/**
 * Тестовый скрипт для проверки пароля пользователя
 */

require_once('wp-load.php');

$username = 'bizfin_pro_r';
$password = 'Y9KpDrd?-bUG:@:9';

echo "Тестирование входа в WordPress...\n\n";

// Получаем пользователя
$user = get_user_by('login', $username);

if (!$user) {
    echo "❌ Пользователь '$username' не найден!\n";
    exit;
}

echo "✅ Пользователь '$username' найден (ID: {$user->ID})\n";
echo "Email: {$user->user_email}\n";
echo "Статус: {$user->user_status}\n";
echo "Хеш пароля в БД: {$user->user_pass}\n\n";

// Проверяем пароль
$check_password = wp_check_password($password, $user->user_pass, $user->ID);

if ($check_password) {
    echo "✅ Пароль корректный!\n";
} else {
    echo "❌ Пароль неверный!\n";
    
    // Попробуем создать новый хеш
    echo "\nПопытка создать новый хеш...\n";
    $new_hash = wp_hash_password($password);
    echo "Новый хеш: $new_hash\n";
    
    // Проверим новый хеш
    $check_new = wp_check_password($password, $new_hash, $user->ID);
    if ($check_new) {
        echo "✅ Новый хеш работает!\n";
    } else {
        echo "❌ Новый хеш тоже не работает!\n";
    }
}

echo "\n--- Дополнительная информация ---\n";
echo "WordPress версия: " . get_bloginfo('version') . "\n";
echo "URL сайта: " . get_site_url() . "\n";
echo "URL админки: " . admin_url() . "\n";
echo "HTTPS включен: " . (is_ssl() ? 'Да' : 'Нет') . "\n";
echo "FORCE_SSL_ADMIN: " . (defined('FORCE_SSL_ADMIN') ? (FORCE_SSL_ADMIN ? 'Да' : 'Нет') : 'Не определен') . "\n";
?>
