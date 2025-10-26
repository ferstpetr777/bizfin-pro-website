<?php
/**
 * Создание нового администратора для сайта
 */

require_once('wp-load.php');

$email = 'rtep1976@icloud.com';
$username = 'rtep1976';
$password = 'Admin2025!';

echo "Создание нового администратора...\n\n";

// Проверяем, существует ли пользователь
$existing_user = get_user_by('email', $email);
if ($existing_user) {
    echo "❌ Пользователь с email '$email' уже существует!\n";
    echo "ID: {$existing_user->ID}\n";
    echo "Логин: {$existing_user->user_login}\n";
    
    // Обновляем пароль для существующего пользователя
    wp_set_password($password, $existing_user->ID);
    echo "✅ Пароль обновлен для существующего пользователя\n";
    
    // Даем права администратора
    $user = new WP_User($existing_user->ID);
    $user->set_role('administrator');
    echo "✅ Права администратора назначены\n";
} else {
    // Создаем нового пользователя
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        echo "❌ Ошибка создания пользователя: " . $user_id->get_error_message() . "\n";
        exit;
    }
    
    echo "✅ Пользователь создан с ID: $user_id\n";
    
    // Даем права администратора
    $user = new WP_User($user_id);
    $user->set_role('administrator');
    echo "✅ Права администратора назначены\n";
}

echo "\n--- Информация о пользователе ---\n";
$user = get_user_by('email', $email);
echo "ID: {$user->ID}\n";
echo "Логин: {$user->user_login}\n";
echo "Email: {$user->user_email}\n";
echo "Роль: " . implode(', ', $user->roles) . "\n";
echo "Статус: {$user->user_status}\n";

echo "\n✅ Администратор готов!\n";
echo "Email: $email\n";
echo "Пароль: $password\n";
?>
