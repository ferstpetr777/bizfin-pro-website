<?php
/**
 * Тест улучшенной версии ЕГРЮЛ API
 * Company Rating Checker - Test Improved EGRUL
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем улучшенную версию
require_once('egrul-api-improved.php');

echo "🔍 ТЕСТ УЛУЧШЕННОЙ ВЕРСИИ ЕГРЮЛ API\n";
echo "====================================\n\n";

// Тестовый ИНН
$test_inn = '5260482041';

echo "📋 ИНН для анализа: {$test_inn}\n";
echo "⏰ Время анализа: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр улучшенного ЕГРЮЛ API
$egrul_api = new EGRULApiImproved();

echo "🚀 ТЕСТИРОВАНИЕ УЛУЧШЕННОЙ ВЕРСИИ...\n";
echo "=====================================\n\n";

// Проверяем доступность источников
echo "🌐 ПРОВЕРКА ДОСТУПНОСТИ ИСТОЧНИКОВ:\n";
echo "===================================\n";
$sources = $egrul_api->check_sources();
foreach ($sources as $source_key => $source_info) {
    $status = $source_info['available'] ? '✅ Доступен' : '❌ Недоступен';
    echo "   {$status} {$source_info['name']}\n";
    echo "      🔗 URL: {$source_info['url']}\n";
}
echo "\n";

// Тестируем получение данных
echo "📊 ТЕСТИРОВАНИЕ ПОЛУЧЕНИЯ ДАННЫХ ЕГРЮЛ:\n";
echo "=======================================\n";

try {
    $egrul_data = $egrul_api->get_egrul_data($test_inn);
    
    if ($egrul_data && !is_wp_error($egrul_data)) {
        echo "✅ ДАННЫЕ ЕГРЮЛ ПОЛУЧЕНЫ УСПЕШНО!\n\n";
        
        // Основная информация
        echo "📋 ОСНОВНАЯ ИНФОРМАЦИЯ:\n";
        echo "=======================\n";
        echo "   🏢 ИНН: " . ($egrul_data['inn'] ?? 'Не указан') . "\n";
        echo "   📝 Название: " . ($egrul_data['name'] ?? 'Не указано') . "\n";
        echo "   🆔 ОГРН: " . ($egrul_data['ogrn'] ?? 'Не указан') . "\n";
        echo "   🏛️ КПП: " . ($egrul_data['kpp'] ?? 'Не указан') . "\n";
        echo "   📍 Адрес: " . ($egrul_data['address'] ?? 'Не указан') . "\n";
        echo "   📊 Статус: " . ($egrul_data['status'] ?? 'Не указан') . "\n";
        echo "   📅 Дата регистрации: " . ($egrul_data['registration_date'] ?? 'Не указана') . "\n";
        echo "   👤 Руководитель: " . ($egrul_data['manager'] ?? 'Не указан') . "\n";
        echo "   🏭 ОКВЭД: " . ($egrul_data['okved'] ?? 'Не указан') . "\n";
        echo "   💰 Уставный капитал: " . number_format($egrul_data['authorized_capital'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   📅 Последнее обновление: " . ($egrul_data['last_updated'] ?? 'Не указано') . "\n";
        echo "   🔍 Источник данных: " . ($egrul_data['source'] ?? 'Не указан') . "\n";
        echo "   🧠 Эвристический анализ: " . (isset($egrul_data['heuristic_analysis']) && $egrul_data['heuristic_analysis'] ? 'ДА' : 'НЕТ') . "\n\n";
        
        // Факторы анализа (если есть)
        if (isset($egrul_data['egrul_factors']) && !empty($egrul_data['egrul_factors'])) {
            echo "🔍 ФАКТОРЫ АНАЛИЗА ЕГРЮЛ:\n";
            echo "=========================\n";
            foreach ($egrul_data['egrul_factors'] as $factor) {
                echo "   📊 {$factor}\n";
            }
            echo "\n";
        }
        
        // Проверяем качество данных
        echo "📈 АНАЛИЗ КАЧЕСТВА ДАННЫХ:\n";
        echo "==========================\n";
        $data_quality = $this->analyze_data_quality($egrul_data);
        echo "   📊 Качество данных: {$data_quality['score']}/100\n";
        echo "   📝 Заполненность: {$data_quality['completeness']}%\n";
        echo "   ✅ Полных полей: {$data_quality['complete_fields']}\n";
        echo "   ❌ Пустых полей: {$data_quality['empty_fields']}\n\n";
        
    } else {
        echo "❌ ОШИБКА ПОЛУЧЕНИЯ ДАННЫХ ЕГРЮЛ\n";
        if (is_wp_error($egrul_data)) {
            echo "   Код ошибки: " . $egrul_data->get_error_code() . "\n";
            echo "   Сообщение: " . $egrul_data->get_error_message() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . "\n";
    echo "   Строка: " . $e->getLine() . "\n";
}

// Тестируем несколько запусков для проверки стабильности
echo "🔄 ТЕСТ НА СТАБИЛЬНОСТЬ (5 запусков):\n";
echo "=====================================\n";
$consistent_results = true;
$first_result = null;

for ($i = 1; $i <= 5; $i++) {
    echo "   Запуск {$i}: ";
    
    try {
        $test_data = $egrul_api->get_egrul_data($test_inn);
        
        if ($test_data && !is_wp_error($test_data)) {
            $name = $test_data['name'] ?? 'Не указано';
            $status = $test_data['status'] ?? 'Не указан';
            $source = $test_data['source'] ?? 'Не указан';
            
            echo "Название: {$name}, Статус: {$status}, Источник: {$source}\n";
            
            // Проверяем консистентность
            if ($i === 1) {
                $first_result = $test_data;
            } else {
                if ($first_result['name'] !== $test_data['name'] ||
                    $first_result['status'] !== $test_data['status']) {
                    $consistent_results = false;
                }
            }
        } else {
            echo "Ошибка получения данных\n";
            $consistent_results = false;
        }
    } catch (Exception $e) {
        echo "Исключение: " . $e->getMessage() . "\n";
        $consistent_results = false;
    }
}

echo "\n📈 РЕЗУЛЬТАТ ТЕСТА НА СТАБИЛЬНОСТЬ:\n";
echo "====================================\n";
if ($consistent_results) {
    echo "   ✅ РЕЗУЛЬТАТЫ СТАБИЛЬНЫ - API работает корректно!\n";
} else {
    echo "   ⚠️ РЕЗУЛЬТАТЫ НЕСТАБИЛЬНЫ - требуется дополнительная работа\n";
}

echo "\n⏰ Время завершения теста: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ТЕСТ УЛУЧШЕННОЙ ВЕРСИИ ЗАВЕРШЕН!\n";
echo "====================================\n";

// Функция анализа качества данных
function analyze_data_quality($data) {
    $required_fields = array('inn', 'name', 'ogrn', 'kpp', 'address', 'status', 'registration_date', 'manager', 'okved', 'authorized_capital');
    $complete_fields = 0;
    $empty_fields = 0;
    
    foreach ($required_fields as $field) {
        if (isset($data[$field]) && !empty($data[$field]) && $data[$field] !== 'Не указано' && $data[$field] !== 'Не указан' && $data[$field] !== 'Не указана') {
            $complete_fields++;
        } else {
            $empty_fields++;
        }
    }
    
    $completeness = round(($complete_fields / count($required_fields)) * 100, 1);
    $score = $completeness;
    
    return array(
        'score' => $score,
        'completeness' => $completeness,
        'complete_fields' => $complete_fields,
        'empty_fields' => $empty_fields
    );
}
?>
