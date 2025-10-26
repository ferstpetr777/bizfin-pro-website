<?php
/**
 * Исправление ошибки генерации изображения для статьи 2895
 */

require_once('wp-config.php');
require_once('wp-load.php');

echo "=== ИСПРАВЛЕНИЕ СТАТЬИ 2895 ===\n";
echo "Начало: " . date('Y-m-d H:i:s') . "\n\n";

$post_id = 2895;

try {
    // 1. Проверяем пост
    $post = get_post($post_id);
    if (!$post) {
        echo "❌ Ошибка: пост не найден\n";
        exit;
    }
    
    echo "✅ Пост найден: {$post->post_title}\n";
    
    // 2. Проверяем наличие изображения
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if ($thumbnail_id) {
        echo "⚠️ У поста уже есть изображение (ID: $thumbnail_id)\n";
        exit;
    }
    
    echo "✅ Изображение отсутствует, можно генерировать\n";
    
    // 3. Проверяем мета-описание
    $meta_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
    if (empty($meta_desc)) {
        echo "❌ Ошибка: отсутствует мета-описание\n";
        exit;
    }
    
    echo "✅ Мета-описание найдено: " . wp_trim_words($meta_desc, 8) . "\n";
    
    // 4. Создаем упрощенный промпт
    $prompt = "Professional abstract artwork in Kandinsky style. Modern geometric composition with blue and gold colors. Banking and finance concept. High quality artistic illustration.";
    
    echo "✅ Упрощенный промпт создан (длина: " . strlen($prompt) . " символов)\n";
    
    // 5. Вызываем API OpenAI с cURL напрямую
    echo "🔄 Отправка запроса к OpenAI API через cURL...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/images/generations');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'prompt' => $prompt,
        'n' => 1,
        'size' => '1024x1024',
        'model' => 'dall-e-2'
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer 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',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "📊 HTTP код: $http_code\n";
    echo "📊 Длина ответа: " . strlen($response) . " байт\n";
    
    if ($curl_error) {
        echo "❌ Ошибка cURL: $curl_error\n";
        exit;
    }
    
    if ($http_code !== 200) {
        echo "❌ API вернул ошибку: $http_code\n";
        echo "Ответ: " . substr($response, 0, 300) . "...\n";
        exit;
    }
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['data'][0]['url'])) {
        echo "❌ Неверный формат ответа API\n";
        echo "Ответ: " . substr($response, 0, 300) . "...\n";
        exit;
    }
    
    $image_url = $data['data'][0]['url'];
    echo "✅ Изображение сгенерировано: $image_url\n";
    
    // 6. Загружаем изображение в медиатеку
    echo "🔄 Загрузка изображения в медиатеку...\n";
    
    $upload_dir = wp_upload_dir();
    $image_data = wp_remote_get($image_url);
    
    if (is_wp_error($image_data)) {
        echo "❌ Ошибка загрузки изображения: " . $image_data->get_error_message() . "\n";
        exit;
    }
    
    $image_content = wp_remote_retrieve_body($image_data);
    $filename = 'generated-image-' . $post_id . '-' . time() . '.png';
    $file_path = $upload_dir['path'] . '/' . $filename;
    
    if (file_put_contents($file_path, $image_content) === false) {
        echo "❌ Ошибка сохранения файла\n";
        exit;
    }
    
    // 7. Создаем attachment
    $attachment = [
        'post_mime_type' => 'image/png',
        'post_title' => $post->post_title,
        'post_content' => '',
        'post_status' => 'inherit'
    ];
    
    $attachment_id = wp_insert_attachment($attachment, $file_path, $post_id);
    
    if (is_wp_error($attachment_id)) {
        echo "❌ Ошибка создания attachment: " . $attachment_id->get_error_message() . "\n";
        exit;
    }
    
    // 8. Генерируем метаданные
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata($attachment_id, $attachment_data);
    
    // 9. Устанавливаем как featured image
    set_post_thumbnail($post_id, $attachment_id);
    
    // 10. SEO оптимизация
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $post->post_title);
    
    echo "✅ Изображение успешно прикреплено (ID: $attachment_id)\n";
    echo "✅ SEO оптимизация выполнена\n";
    echo "🎉 СТАТЬЯ ID $post_id УСПЕШНО ИСПРАВЛЕНА!\n";
    
} catch (Exception $e) {
    echo "❌ Критическая ошибка: " . $e->getMessage() . "\n";
}

echo "\nЗавершено: " . date('Y-m-d H:i:s') . "\n";
?>

