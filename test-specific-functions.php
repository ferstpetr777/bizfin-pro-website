<?php
/**
 * Тест конкретных функций плагинов
 */

require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin-pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

echo "=== ТЕСТ КОНКРЕТНЫХ ФУНКЦИЙ ПЛАГИНОВ ===\n\n";

// 1. Тест ChatGPT Consultant - отправка сообщения
echo "1. Тест ChatGPT Consultant - отправка сообщения...\n";
if (class_exists('BizFin_ChatGPT_Consultant')) {
    $consultant = bizfin_chatgpt_consultant();
    $chat_handler = $consultant->get_chat_handler();
    
    if ($chat_handler) {
        $test_message = "Расскажи кратко о банковских гарантиях";
        $session_id = 'test-session-' . time();
        
        try {
            $response = $chat_handler->process_message($test_message, $session_id);
            if ($response && isset($response['success']) && $response['success']) {
                echo "✅ ChatGPT Consultant работает корректно\n";
                echo "   Ответ: " . substr($response['content'], 0, 100) . "...\n";
                echo "   Модель: " . ($response['model'] ?? 'неизвестно') . "\n";
                echo "   Токены: " . ($response['tokens_used'] ?? 0) . "\n";
            } else {
                echo "❌ Ошибка ChatGPT Consultant\n";
                if (isset($response['error'])) {
                    echo "   Ошибка: " . $response['error'] . "\n";
                }
            }
        } catch (Exception $e) {
            echo "❌ Исключение: " . $e->getMessage() . "\n";
        }
    }
}

// 2. Тест ABP Image Generator - генерация изображения
echo "\n2. Тест ABP Image Generator - генерация изображения...\n";
if (class_exists('ABP_Image_Generator')) {
    // Создаем тестовый пост
    $test_post = wp_insert_post([
        'post_title' => 'Тест генерации изображения',
        'post_content' => 'Тестовый контент для генерации изображения',
        'post_status' => 'publish',
        'post_type' => 'post'
    ]);
    
    if ($test_post && !is_wp_error($test_post)) {
        echo "✅ Тестовый пост создан (ID: $test_post)\n";
        
        // Симулируем генерацию изображения
        $image_generator = new ABP_Image_Generator();
        
        // Проверяем метод генерации
        if (method_exists($image_generator, 'generate_image_with_openai')) {
            echo "✅ Метод генерации изображения найден\n";
            
            // Тестируем с простым промптом
            $test_prompt = "Simple business illustration for banking guarantee article";
            
            try {
                // Используем рефлексию для доступа к приватному методу
                $reflection = new ReflectionClass($image_generator);
                $method = $reflection->getMethod('generate_image_with_openai');
                $method->setAccessible(true);
                
                $image_url = $method->invoke($image_generator, $test_prompt);
                
                if ($image_url && filter_var($image_url, FILTER_VALIDATE_URL)) {
                    echo "✅ Изображение сгенерировано успешно\n";
                    echo "   URL: " . substr($image_url, 0, 80) . "...\n";
                } else {
                    echo "❌ Ошибка генерации изображения\n";
                }
            } catch (Exception $e) {
                echo "❌ Исключение при генерации: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ Метод генерации изображения не найден\n";
        }
        
        // Удаляем тестовый пост
        wp_delete_post($test_post, true);
        echo "✅ Тестовый пост удален\n";
    } else {
        echo "❌ Не удалось создать тестовый пост\n";
    }
}

// 3. Тест ABP AI Categorization
echo "\n3. Тест ABP AI Categorization...\n";
if (class_exists('ABP_AI_Categorization')) {
    $categorizer = new ABP_AI_Categorization();
    
    // Проверяем метод категоризации
    if (method_exists($categorizer, 'call_openai_api')) {
        echo "✅ Метод API найден\n";
        
        $test_content = "Статья о банковских гарантиях для тендеров";
        
        try {
            $reflection = new ReflectionClass($categorizer);
            $method = $reflection->getMethod('call_openai_api');
            $method->setAccessible(true);
            
            $result = $method->invoke($categorizer, "Категоризируй: $test_content");
            
            if ($result) {
                echo "✅ AI категоризация работает\n";
                echo "   Результат: " . substr($result, 0, 100) . "...\n";
            } else {
                echo "❌ Ошибка AI категоризации\n";
            }
        } catch (Exception $e) {
            echo "❌ Исключение: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Метод API не найден\n";
    }
}

// 4. Тест Yoast Alphabet Integration
echo "\n4. Тест Yoast Alphabet Integration...\n";
if (class_exists('YoastAlphabetIntegration')) {
    $yoast_integration = new YoastAlphabetIntegration();
    
    if (method_exists($yoast_integration, 'call_openai_api')) {
        echo "✅ Метод API найден\n";
        
        try {
            $reflection = new ReflectionClass($yoast_integration);
            $method = $reflection->getMethod('call_openai_api');
            $method->setAccessible(true);
            
            $result = $method->invoke($yoast_integration, "Создай SEO заголовок для статьи о банковских гарантиях");
            
            if ($result) {
                echo "✅ Yoast интеграция работает\n";
                echo "   Результат: " . substr($result, 0, 100) . "...\n";
            } else {
                echo "❌ Ошибка Yoast интеграции\n";
            }
        } catch (Exception $e) {
            echo "❌ Исключение: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Метод API не найден\n";
    }
}

// 5. Тест BizFin SEO Article Generator
echo "\n5. Тест BizFin SEO Article Generator...\n";
if (class_exists('BizFin_SEO_Article_Generator')) {
    $generator = BizFin_SEO_Article_Generator::get_instance();
    
    // Проверяем матрицу ключевых слов
    $seo_matrix = $generator->get_seo_matrix();
    $keywords = $seo_matrix['keywords'] ?? [];
    
    if (!empty($keywords)) {
        echo "✅ SEO матрица загружена\n";
        echo "   Ключевых слов: " . count($keywords) . "\n";
        
        // Тестируем с первым ключевым словом
        $first_keyword = array_key_first($keywords);
        echo "   Тестовое ключевое слово: $first_keyword\n";
        
        // Проверяем структуру статьи
        $keyword_data = $keywords[$first_keyword];
        $structure = $seo_matrix['article_structures'][$keyword_data['structure']] ?? null;
        
        if ($structure) {
            echo "✅ Структура статьи найдена\n";
            echo "   Тип: " . $keyword_data['structure'] . "\n";
            echo "   H2 секций: " . count($structure['h2_sections']) . "\n";
        }
    }
}

// 6. Проверка производительности
echo "\n6. Проверка производительности...\n";
$start_time = microtime(true);

// Тест скорости ответа API
$test_messages = [['role' => 'user', 'content' => 'Быстрый тест']];
$consultant = bizfin_chatgpt_consultant();
$openai_client = $consultant->get_openai_client();

$api_start = microtime(true);
$result = $openai_client->send_message($test_messages, 'gpt-4o', ['max_tokens' => 5]);
$api_time = microtime(true) - $api_start;

if ($result && $result['success']) {
    echo "✅ API ответ получен за " . round($api_time, 2) . " секунд\n";
    echo "   Время обработки в API: " . ($result['processing_time'] ?? 'неизвестно') . " сек\n";
} else {
    echo "❌ Ошибка теста производительности\n";
}

$total_time = microtime(true) - $start_time;
echo "   Общее время теста: " . round($total_time, 2) . " секунд\n";

echo "\n=== ТЕСТ ФУНКЦИЙ ЗАВЕРШЕН ===\n";
?>
