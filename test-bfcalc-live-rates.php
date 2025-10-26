<?php
/**
 * E2E тесты для BFCalc Live Rates
 * Проверка работы системы автоматического обновления ставок
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

class BFCalc_Live_Rates_Tests {
    
    private $test_results = [];
    private $plugin_instance;
    
    public function __construct() {
        // Проверяем, что плагин активен
        if (!class_exists('BFCalc_Live_Rates')) {
            $this->add_result('ERROR', 'Плагин BFCalc_Live_Rates не найден');
            return;
        }
        
        $this->plugin_instance = new BFCalc_Live_Rates();
    }
    
    public function run_all_tests() {
        echo "<h1>🧪 BFCalc Live Rates - E2E Тесты</h1>\n";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .test-pass { color: green; }
            .test-fail { color: red; }
            .test-warn { color: orange; }
            .test-info { color: blue; }
            .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .test-result { margin: 10px 0; padding: 10px; background: #f9f9f9; border-radius: 3px; }
        </style>\n";
        
        $this->test_plugin_activation();
        $this->test_rest_api_endpoint();
        $this->test_rate_parsing();
        $this->test_caching_mechanism();
        $this->test_calculator_integration();
        $this->test_error_handling();
        
        $this->display_summary();
    }
    
    private function add_result($type, $message, $details = '') {
        $this->test_results[] = [
            'type' => $type,
            'message' => $message,
            'details' => $details,
            'timestamp' => current_time('mysql')
        ];
    }
    
    private function test_plugin_activation() {
        echo "<div class='test-section'>";
        echo "<h2>🔧 Тест активации плагина</h2>";
        
        // Проверяем, что плагин загружен
        if (class_exists('BFCalc_Live_Rates')) {
            $this->add_result('PASS', 'Плагин BFCalc_Live_Rates успешно загружен');
            echo "<div class='test-result test-pass'>✅ Плагин загружен</div>";
        } else {
            $this->add_result('FAIL', 'Плагин BFCalc_Live_Rates не найден');
            echo "<div class='test-result test-fail'>❌ Плагин не найден</div>";
            return;
        }
        
        // Проверяем REST API маршруты
        $routes = rest_get_server()->get_routes();
        if (isset($routes['/bfcalc/v1/rates'])) {
            $this->add_result('PASS', 'REST API маршрут /bfcalc/v1/rates зарегистрирован');
            echo "<div class='test-result test-pass'>✅ REST API маршрут зарегистрирован</div>";
        } else {
            $this->add_result('FAIL', 'REST API маршрут /bfcalc/v1/rates не найден');
            echo "<div class='test-result test-fail'>❌ REST API маршрут не найден</div>";
        }
        
        // Проверяем cron задачи
        $next_cron = wp_next_scheduled('bfcalc_fetch_rates_daily');
        if ($next_cron) {
            $this->add_result('PASS', 'Cron задача запланирована на ' . date('Y-m-d H:i:s', $next_cron));
            echo "<div class='test-result test-pass'>✅ Cron задача запланирована</div>";
        } else {
            $this->add_result('WARN', 'Cron задача не запланирована');
            echo "<div class='test-result test-warn'>⚠️ Cron задача не запланирована</div>";
        }
        
        echo "</div>";
    }
    
    private function test_rest_api_endpoint() {
        echo "<div class='test-section'>";
        echo "<h2>🌐 Тест REST API эндпойнта</h2>";
        
        // Тестируем эндпойнт
        $url = home_url('/wp-json/bfcalc/v1/rates?scheme=avg');
        $response = wp_remote_get($url, ['timeout' => 10]);
        
        if (is_wp_error($response)) {
            $this->add_result('FAIL', 'Ошибка запроса к REST API: ' . $response->get_error_message());
            echo "<div class='test-result test-fail'>❌ Ошибка запроса: " . $response->get_error_message() . "</div>";
        } else {
            $status_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if ($status_code === 200) {
                $this->add_result('PASS', 'REST API отвечает с кодом 200');
                echo "<div class='test-result test-pass'>✅ REST API отвечает (200)</div>";
                
                if ($data && isset($data['ok'])) {
                    if ($data['ok']) {
                        $this->add_result('PASS', 'API возвращает валидные данные');
                        echo "<div class='test-result test-pass'>✅ API возвращает валидные данные</div>";
                        
                        if (isset($data['baseRates'])) {
                            $this->add_result('PASS', 'Найдены базовые ставки');
                            echo "<div class='test-result test-pass'>✅ Базовые ставки найдены</div>";
                            
                            // Проверяем структуру ставок
                            $required_modes = ['44fz', '223fz', '185fz', 'comm'];
                            $required_types = ['participation', 'performance', 'warranty', 'advance'];
                            
                            foreach ($required_modes as $mode) {
                                if (isset($data['baseRates'][$mode])) {
                                    foreach ($required_types as $type) {
                                        if (isset($data['baseRates'][$mode][$type])) {
                                            $rate = $data['baseRates'][$mode][$type];
                                            if (is_numeric($rate) && $rate > 0 && $rate < 20) {
                                                $this->add_result('PASS', "Ставка {$mode}.{$type}: {$rate}%");
                                            } else {
                                                $this->add_result('WARN', "Подозрительная ставка {$mode}.{$type}: {$rate}%");
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $this->add_result('WARN', 'Базовые ставки не найдены в ответе');
                        }
                    } else {
                        $this->add_result('WARN', 'API возвращает ok=false: ' . ($data['error'] ?? 'неизвестная ошибка'));
                        echo "<div class='test-result test-warn'>⚠️ API возвращает ошибку: " . ($data['error'] ?? 'неизвестная ошибка') . "</div>";
                    }
                } else {
                    $this->add_result('FAIL', 'API возвращает невалидный JSON');
                    echo "<div class='test-result test-fail'>❌ Невалидный JSON ответ</div>";
                }
            } else {
                $this->add_result('FAIL', "REST API отвечает с кодом {$status_code}");
                echo "<div class='test-result test-fail'>❌ HTTP {$status_code}</div>";
            }
        }
        
        echo "</div>";
    }
    
    private function test_rate_parsing() {
        echo "<div class='test-section'>";
        echo "<h2>📊 Тест парсинга ставок</h2>";
        
        // Тестируем парсинг напрямую
        $instance = new BFCalc_Live_Rates();
        $result = $instance->fetch_and_cache();
        
        if ($result && !empty($result['per_bank'])) {
            $this->add_result('PASS', 'Парсинг успешен, найдено банков: ' . count($result['per_bank']));
            echo "<div class='test-result test-pass'>✅ Парсинг успешен</div>";
            
            // Показываем примеры данных
            echo "<h3>Примеры найденных ставок:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Банк</th><th>44-ФЗ (участие)</th><th>44-ФЗ (исполнение)</th><th>223-ФЗ (участие)</th><th>Коммерческий (участие)</th></tr>";
            
            foreach (array_slice($result['per_bank'], 0, 5) as $bank) {
                echo "<tr>";
                echo "<td>" . esc_html($bank['name']) . "</td>";
                echo "<td>" . ($bank['44fz']['participation'] ?? 'N/A') . "%</td>";
                echo "<td>" . ($bank['44fz']['performance'] ?? 'N/A') . "%</td>";
                echo "<td>" . ($bank['223fz']['participation'] ?? 'N/A') . "%</td>";
                echo "<td>" . ($bank['comm']['participation'] ?? 'N/A') . "%</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            $this->add_result('INFO', 'Данные обновлены: ' . $result['updated']);
        } else {
            $this->add_result('WARN', 'Парсинг не дал результатов, используются fallback данные');
            echo "<div class='test-result test-warn'>⚠️ Парсинг не дал результатов</div>";
        }
        
        echo "</div>";
    }
    
    private function test_caching_mechanism() {
        echo "<div class='test-section'>";
        echo "<h2>💾 Тест кеширования</h2>";
        
        // Проверяем transient
        $cached_data = get_transient('bfcalc_live_rates_v1');
        
        if ($cached_data) {
            $this->add_result('PASS', 'Данные найдены в кеше');
            echo "<div class='test-result test-pass'>✅ Кеш содержит данные</div>";
            
            if (isset($cached_data['updated'])) {
                $this->add_result('INFO', 'Кеш обновлен: ' . $cached_data['updated']);
                echo "<div class='test-result test-info'>ℹ️ Обновлено: " . $cached_data['updated'] . "</div>";
            }
            
            if (isset($cached_data['per_bank']) && is_array($cached_data['per_bank'])) {
                $this->add_result('PASS', 'Кеш содержит ' . count($cached_data['per_bank']) . ' банков');
                echo "<div class='test-result test-pass'>✅ Кеш содержит " . count($cached_data['per_bank']) . " банков</div>";
            }
        } else {
            $this->add_result('WARN', 'Кеш пуст');
            echo "<div class='test-result test-warn'>⚠️ Кеш пуст</div>";
        }
        
        // Тестируем TTL
        $transient_timeout = get_option('_transient_timeout_bfcalc_live_rates_v1');
        if ($transient_timeout) {
            $time_left = $transient_timeout - time();
            $hours_left = round($time_left / 3600, 1);
            $this->add_result('INFO', "Кеш истекает через {$hours_left} часов");
            echo "<div class='test-result test-info'>ℹ️ Кеш истекает через {$hours_left} часов</div>";
        }
        
        echo "</div>";
    }
    
    private function test_calculator_integration() {
        echo "<div class='test-section'>";
        echo "<h2>🧮 Тест интеграции с калькулятором</h2>";
        
        // Проверяем, что файл калькулятора существует
        $calc_file = ABSPATH . 'bfcalc-updated.html';
        if (file_exists($calc_file)) {
            $this->add_result('PASS', 'Файл калькулятора найден');
            echo "<div class='test-result test-pass'>✅ Файл калькулятора найден</div>";
            
            $content = file_get_contents($calc_file);
            
            // Проверяем наличие ключевых элементов
            $checks = [
                'loadLiveRates' => 'Функция загрузки live-ставок',
                'applyLiveRates' => 'Функция применения ставок',
                'paintLiveStatus' => 'Функция отображения статуса',
                '/wp-json/bfcalc/v1/rates' => 'URL API эндпойнта',
                'res__live' => 'Элемент статуса ставок'
            ];
            
            foreach ($checks as $check => $description) {
                if (strpos($content, $check) !== false) {
                    $this->add_result('PASS', $description . ' найдена');
                    echo "<div class='test-result test-pass'>✅ {$description}</div>";
                } else {
                    $this->add_result('FAIL', $description . ' не найдена');
                    echo "<div class='test-result test-fail'>❌ {$description}</div>";
                }
            }
        } else {
            $this->add_result('FAIL', 'Файл калькулятора не найден');
            echo "<div class='test-result test-fail'>❌ Файл калькулятора не найден</div>";
        }
        
        echo "</div>";
    }
    
    private function test_error_handling() {
        echo "<div class='test-section'>";
        echo "<h2>⚠️ Тест обработки ошибок</h2>";
        
        // Тестируем обработку неверных параметров
        $url = home_url('/wp-json/bfcalc/v1/rates?scheme=invalid');
        $response = wp_remote_get($url, ['timeout' => 5]);
        
        if (!is_wp_error($response)) {
            $status_code = wp_remote_retrieve_response_code($response);
            if ($status_code === 200) {
                $this->add_result('PASS', 'API корректно обрабатывает неверные параметры');
                echo "<div class='test-result test-pass'>✅ Обработка неверных параметров работает</div>";
            } else {
                $this->add_result('WARN', "API возвращает код {$status_code} для неверных параметров");
                echo "<div class='test-result test-warn'>⚠️ HTTP {$status_code} для неверных параметров</div>";
            }
        }
        
        // Тестируем недоступность источника
        $this->add_result('INFO', 'Тест недоступности источника требует ручной проверки');
        echo "<div class='test-result test-info'>ℹ️ Тест недоступности источника требует ручной проверки</div>";
        
        echo "</div>";
    }
    
    private function display_summary() {
        echo "<div class='test-section'>";
        echo "<h2>📋 Сводка результатов</h2>";
        
        $pass_count = 0;
        $fail_count = 0;
        $warn_count = 0;
        $info_count = 0;
        
        foreach ($this->test_results as $result) {
            switch ($result['type']) {
                case 'PASS': $pass_count++; break;
                case 'FAIL': $fail_count++; break;
                case 'WARN': $warn_count++; break;
                case 'INFO': $info_count++; break;
            }
        }
        
        echo "<div style='font-size: 18px; margin: 20px 0;'>";
        echo "<span class='test-pass'>✅ Пройдено: {$pass_count}</span> | ";
        echo "<span class='test-fail'>❌ Провалено: {$fail_count}</span> | ";
        echo "<span class='test-warn'>⚠️ Предупреждений: {$warn_count}</span> | ";
        echo "<span class='test-info'>ℹ️ Информации: {$info_count}</span>";
        echo "</div>";
        
        if ($fail_count === 0) {
            echo "<div class='test-result test-pass' style='font-size: 16px; font-weight: bold;'>";
            echo "🎉 Все критические тесты пройдены! Система готова к работе.";
            echo "</div>";
        } else {
            echo "<div class='test-result test-fail' style='font-size: 16px; font-weight: bold;'>";
            echo "⚠️ Обнаружены проблемы, требующие внимания.";
            echo "</div>";
        }
        
        echo "<h3>Детальные результаты:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Тип</th><th>Сообщение</th><th>Время</th></tr>";
        
        foreach ($this->test_results as $result) {
            $class = 'test-' . strtolower($result['type']);
            echo "<tr class='{$class}'>";
            echo "<td>{$result['type']}</td>";
            echo "<td>{$result['message']}</td>";
            echo "<td>{$result['timestamp']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
    }
}

// Запуск тестов
if (isset($_GET['run_tests'])) {
    $tests = new BFCalc_Live_Rates_Tests();
    $tests->run_all_tests();
} else {
    echo "<h1>BFCalc Live Rates - E2E Тесты</h1>";
    echo "<p><a href='?run_tests=1' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>🚀 Запустить тесты</a></p>";
    echo "<p>Этот файл содержит комплексные E2E тесты для проверки работы системы автоматического обновления ставок.</p>";
    echo "<h2>Что тестируется:</h2>";
    echo "<ul>";
    echo "<li>✅ Активация и регистрация плагина</li>";
    echo "<li>✅ Работа REST API эндпойнта</li>";
    echo "<li>✅ Парсинг ставок с внешнего источника</li>";
    echo "<li>✅ Механизм кеширования</li>";
    echo "<li>✅ Интеграция с калькулятором</li>";
    echo "<li>✅ Обработка ошибок</li>";
    echo "</ul>";
}
?>
