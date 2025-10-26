<?php
/**
 * Тест работы плагинов через прокси
 * Проверяет доступность OpenAI API через голландский прокси
 */

// Подключаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

echo "=== ТЕСТ РАБОТЫ ПЛАГИНОВ ЧЕРЕЗ ПРОКСИ ===\n\n";

// 1. Тест голландского прокси
echo "1. Тестирование голландского прокси...\n";
$proxy_url = 'http://89.110.80.198:8889';
$test_url = 'https://api.openai.com/v1/models';

$response = wp_remote_get($test_url, [
    'proxy' => $proxy_url,
    'timeout' => 30,
    'headers' => [
        'Authorization' => 'Bearer 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'
    ]
]);

if (is_wp_error($response)) {
    echo "❌ Ошибка прокси: " . $response->get_error_message() . "\n";
} else {
    $code = wp_remote_retrieve_response_code($response);
    echo "✅ Прокси работает! HTTP код: $code\n";
}

// 2. Тест ChatGPT Consultant
echo "\n2. Тестирование BizFin ChatGPT Consultant...\n";
if (class_exists('BizFin_ChatGPT_Consultant')) {
    $consultant = bizfin_chatgpt_consultant();
    $openai_client = $consultant->get_openai_client();
    
    if ($openai_client) {
        echo "✅ ChatGPT Consultant инициализирован\n";
        
        // Тестовое сообщение
        $test_messages = [
            ['role' => 'user', 'content' => 'Тест подключения через прокси. Ответь одним словом: "OK"']
        ];
        
        try {
            $result = $openai_client->send_message($test_messages, 'gpt-4o', ['max_tokens' => 10]);
            if ($result && $result['success']) {
                echo "✅ ChatGPT API работает через прокси: " . $result['content'] . "\n";
                echo "   Модель: " . $result['model'] . "\n";
                echo "   Токены: " . $result['tokens_used'] . "\n";
            } else {
                echo "❌ Ошибка ChatGPT API\n";
            }
        } catch (Exception $e) {
            echo "❌ Исключение ChatGPT: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ OpenAI Client не инициализирован\n";
    }
} else {
    echo "❌ BizFin ChatGPT Consultant не найден\n";
}

// 3. Тест ABP Image Generator
echo "\n3. Тестирование ABP Image Generator...\n";
if (class_exists('ABP_Image_Generator')) {
    echo "✅ ABP Image Generator найден\n";
    
    // Проверяем настройки
    $settings = get_option('abp_image_generator_settings', []);
    echo "   Модель: " . ($settings['model'] ?? 'dall-e-2') . "\n";
    echo "   Автогенерация: " . ($settings['auto_generate'] ? 'Включена' : 'Отключена') . "\n";
    
    // Тестируем API
    $test_prompt = "Simple test image for proxy testing";
    $response = wp_remote_post('https://api.openai.com/v1/images/generations', [
        'proxy' => $proxy_url,
        'timeout' => 60,
        'headers' => [
            'Authorization' => 'Bearer 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',
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode([
            'model' => 'dall-e-2',
            'prompt' => $test_prompt,
            'size' => '256x256',
            'n' => 1
        ])
    ]);
    
    if (is_wp_error($response)) {
        echo "❌ Ошибка DALL-E API: " . $response->get_error_message() . "\n";
    } else {
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($code === 200 && isset($data['data'][0]['url'])) {
            echo "✅ DALL-E API работает через прокси\n";
            echo "   Изображение: " . substr($data['data'][0]['url'], 0, 50) . "...\n";
        } else {
            echo "❌ Ошибка DALL-E API. Код: $code\n";
        }
    }
} else {
    echo "❌ ABP Image Generator не найден\n";
}

// 4. Тест ABP AI Categorization
echo "\n4. Тестирование ABP AI Categorization...\n";
if (class_exists('ABP_AI_Categorization')) {
    echo "✅ ABP AI Categorization найден\n";
} else {
    echo "❌ ABP AI Categorization не найден\n";
}

// 5. Тест Yoast Alphabet Integration
echo "\n5. Тестирование Yoast Alphabet Integration...\n";
if (class_exists('YoastAlphabetIntegration')) {
    echo "✅ Yoast Alphabet Integration найден\n";
} else {
    echo "❌ Yoast Alphabet Integration не найден\n";
}

// 6. Тест BizFin SEO Article Generator
echo "\n6. Тестирование BizFin SEO Article Generator...\n";
if (class_exists('BizFin_SEO_Article_Generator')) {
    echo "✅ BizFin SEO Article Generator найден\n";
    
    $generator = BizFin_SEO_Article_Generator::get_instance();
    $seo_matrix = $generator->get_seo_matrix();
    echo "   Ключевых слов в матрице: " . count($seo_matrix['keywords']) . "\n";
} else {
    echo "❌ BizFin SEO Article Generator не найден\n";
}

// 7. Проверка логов
echo "\n7. Проверка логов прокси...\n";
$debug_log = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/debug.log';
if (file_exists($debug_log)) {
    $log_size = filesize($debug_log);
    echo "   Размер debug.log: " . round($log_size / 1024 / 1024, 2) . " MB\n";
    
    // Ищем последние записи о прокси
    $log_content = file_get_contents($debug_log);
    $proxy_entries = substr_count($log_content, 'Dutch Proxy');
    echo "   Записей о голландском прокси: $proxy_entries\n";
    
    // Последние 5 записей о прокси
    $lines = explode("\n", $log_content);
    $proxy_lines = array_filter($lines, function($line) {
        return strpos($line, 'Dutch Proxy') !== false;
    });
    $recent_proxy_lines = array_slice($proxy_lines, -5);
    
    if (!empty($recent_proxy_lines)) {
        echo "   Последние записи о прокси:\n";
        foreach ($recent_proxy_lines as $line) {
            echo "     " . trim($line) . "\n";
        }
    }
} else {
    echo "❌ debug.log не найден\n";
}

// 8. Проверка настроек прокси
echo "\n8. Проверка настроек прокси...\n";
$mu_plugins = [
    'dutch-proxy-integration.php',
    'force-proxy.php', 
    'http-internal-bypass.php'
];

foreach ($mu_plugins as $plugin) {
    $path = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/mu-plugins/' . $plugin;
    if (file_exists($path)) {
        echo "✅ $plugin - активен\n";
    } else {
        echo "❌ $plugin - не найден\n";
    }
}

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
?>
