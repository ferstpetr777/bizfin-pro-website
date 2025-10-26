<?php
require_once('wp-config.php');

echo "=== ПРОВЕРКА WP FILE MANAGER И ОБЛАЧНЫХ НАСТРОЕК ===\n\n";

// Проверяем настройки WP File Manager
$settings = get_option('wp_file_manager_settings', array());
echo "1. Настройки WP File Manager:\n";
if (!empty($settings)) {
    foreach ($settings as $key => $value) {
        if (strpos($key, 'cloud') !== false || strpos($key, 'drive') !== false || strpos($key, 'dropbox') !== false) {
            echo "   ✅ $key: $value\n";
        }
    }
} else {
    echo "   ❌ Настройки не найдены\n";
}

// Проверяем таблицу бэкапов
global $wpdb;
$fmdb = $wpdb->prefix.'wpfm_backup';
$backups = $wpdb->get_results("SELECT * FROM $fmdb ORDER BY id DESC LIMIT 5");

echo "\n2. Последние бэкапы в базе данных:\n";
if (!empty($backups)) {
    foreach ($backups as $backup) {
        echo "   📅 ID: {$backup->id}, Дата: {$backup->backup_date}, Имя: {$backup->backup_name}\n";
    }
} else {
    echo "   ❌ Бэкапы не найдены\n";
}

// Проверяем директорию бэкапов
$upload_dir = wp_upload_dir();
$backup_dirname = $upload_dir['basedir'].'/wp-file-manager-pro/fm_backup/';

echo "\n3. Директория бэкапов: $backup_dirname\n";
if (file_exists($backup_dirname)) {
    $files = scandir($backup_dirname);
    $files = array_filter($files, function($file) {
        return $file !== '.' && $file !== '..' && $file !== '.htaccess' && $file !== 'index.html';
    });
    
    if (!empty($files)) {
        echo "   ✅ Найдено файлов: " . count($files) . "\n";
        foreach (array_slice($files, 0, 10) as $file) {
            $file_path = $backup_dirname . $file;
            $size = file_exists($file_path) ? filesize($file_path) : 0;
            echo "      - $file (" . round($size / 1024 / 1024, 2) . " MB)\n";
        }
    } else {
        echo "   ❌ Файлы бэкапов не найдены\n";
    }
} else {
    echo "   ❌ Директория не существует\n";
}

// Проверяем настройки облачных сервисов в WP File Manager
echo "\n4. Поиск настроек облачных сервисов:\n";
$cloud_options = array(
    'wp_file_manager_cloud_settings' => get_option('wp_file_manager_cloud_settings', array()),
    'wp_file_manager_google_drive' => get_option('wp_file_manager_google_drive', array()),
    'wp_file_manager_dropbox' => get_option('wp_file_manager_dropbox', array()),
    'wp_file_manager_onedrive' => get_option('wp_file_manager_onedrive', array()),
    'wp_file_manager_box' => get_option('wp_file_manager_box', array()),
    'wp_file_manager_mailru' => get_option('wp_file_manager_mailru', array()),
    'wp_file_manager_yandex' => get_option('wp_file_manager_yandex', array())
);

foreach ($cloud_options as $option_name => $option_value) {
    if (!empty($option_value)) {
        echo "   ✅ $option_name: настроен\n";
        if (is_array($option_value)) {
            foreach ($option_value as $key => $value) {
                if (strpos($key, 'token') !== false || strpos($key, 'key') !== false || strpos($key, 'secret') !== false) {
                    echo "      $key: " . substr($value, 0, 10) . "...\n";
                } else {
                    echo "      $key: $value\n";
                }
            }
        }
    } else {
        echo "   ❌ $option_name: не настроен\n";
    }
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
