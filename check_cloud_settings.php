<?php
require_once('wp-config.php');

echo "=== ПРОВЕРКА НАСТРОЕК ОБЛАЧНЫХ СЕРВИСОВ ===\n\n";

// Проверяем настройки WP File Manager
$file_manager_options = get_option('wp_file_manager_options', array());
echo "1. Настройки WP File Manager:\n";
if (!empty($file_manager_options)) {
    print_r($file_manager_options);
} else {
    echo "   ❌ Настройки не найдены\n";
}

// Проверяем настройки облачных сервисов
$cloud_options = array(
    'googledrive' => get_option('googledrive', array()),
    'dropbox2' => get_option('dropbox2', array()),
    'onedrive' => get_option('onedrive', array()),
    'box' => get_option('box', array()),
    'mailru' => get_option('mailru', array()),
    'yandex' => get_option('yandex', array())
);

echo "\n2. Настройки облачных сервисов:\n";
foreach ($cloud_options as $service => $options) {
    if (!empty($options)) {
        echo "   ✅ $service: настроен\n";
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                if (strpos($key, 'token') !== false || strpos($key, 'key') !== false || strpos($key, 'secret') !== false) {
                    echo "      $key: " . substr($value, 0, 10) . "...\n";
                } else {
                    echo "      $key: $value\n";
                }
            }
        }
    } else {
        echo "   ❌ $service: не настроен\n";
    }
}

// Проверяем все опции, связанные с облаком
echo "\n3. Поиск всех опций, связанных с облаком:\n";
global $wpdb;
$cloud_options = $wpdb->get_results("
    SELECT option_name, option_value 
    FROM {$wpdb->options} 
    WHERE option_name LIKE '%cloud%' 
    OR option_name LIKE '%drive%' 
    OR option_name LIKE '%dropbox%' 
    OR option_name LIKE '%onedrive%' 
    OR option_name LIKE '%mailru%' 
    OR option_name LIKE '%yandex%'
    OR option_name LIKE '%file_manager%'
    ORDER BY option_name
");

if (!empty($cloud_options)) {
    foreach ($cloud_options as $option) {
        echo "   📋 {$option->option_name}: ";
        if (strlen($option->option_value) > 100) {
            echo substr($option->option_value, 0, 100) . "...\n";
        } else {
            echo $option->option_value . "\n";
        }
    }
} else {
    echo "   ❌ Опции облачных сервисов не найдены\n";
}

// Проверяем директории для бэкапов
echo "\n4. Проверка директорий для бэкапов:\n";
$backup_dirs = array(
    '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/Backup/',
    '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/uploads/wp-file-manager-pro/fm_backup/',
    '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/uploads/backup/',
    '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/backup/',
    '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/backup/'
);

foreach ($backup_dirs as $dir) {
    if (file_exists($dir)) {
        $files = scandir($dir);
        $files = array_filter($files, function($file) {
            return $file !== '.' && $file !== '..';
        });
        echo "   ✅ $dir: " . count($files) . " файлов\n";
        if (count($files) > 0) {
            foreach (array_slice($files, 0, 5) as $file) {
                echo "      - $file\n";
            }
            if (count($files) > 5) {
                echo "      ... и еще " . (count($files) - 5) . " файлов\n";
            }
        }
    } else {
        echo "   ❌ $dir: не существует\n";
    }
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
