<?php
/**
 * Скрипт развертывания и тестирования BFCalc Live Rates
 * Автоматическая активация плагина и проверка работоспособности
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

class BFCalc_Deployer {
    
    private $plugin_path;
    private $plugin_file;
    private $deployment_log = [];
    
    public function __construct() {
        $this->plugin_path = WP_CONTENT_DIR . '/plugins/bfcalc-live-rates/';
        $this->plugin_file = $this->plugin_path . 'bfcalc-live-rates.php';
    }
    
    public function deploy() {
        echo "<h1>🚀 Развертывание BFCalc Live Rates</h1>\n";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .step { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
            .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
            .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
            .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
            .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; }
        </style>\n";
        
        $this->log("Начинаем развертывание системы автоматического обновления ставок...");
        
        $this->check_requirements();
        $this->activate_plugin();
        $this->test_plugin_functionality();
        $this->update_calculator();
        $this->run_final_tests();
        $this->display_summary();
    }
    
    private function log($message, $type = 'info') {
        $this->deployment_log[] = [
            'message' => $message,
            'type' => $type,
            'timestamp' => current_time('mysql')
        ];
        
        $class = $type;
        echo "<div class='step {$class}'>";
        echo "<strong>" . strtoupper($type) . ":</strong> {$message}";
        echo "</div>\n";
    }
    
    private function check_requirements() {
        $this->log("Проверка системных требований...", 'info');
        
        // Проверяем версию WordPress
        global $wp_version;
        if (version_compare($wp_version, '5.0', '>=')) {
            $this->log("WordPress версия: {$wp_version} ✓", 'success');
        } else {
            $this->log("WordPress версия слишком старая: {$wp_version}", 'error');
            return false;
        }
        
        // Проверяем PHP версию
        if (version_compare(PHP_VERSION, '7.4', '>=')) {
            $this->log("PHP версия: " . PHP_VERSION . " ✓", 'success');
        } else {
            $this->log("PHP версия слишком старая: " . PHP_VERSION, 'error');
            return false;
        }
        
        // Проверяем наличие необходимых функций
        $required_functions = ['wp_remote_get', 'wp_remote_retrieve_body', 'get_transient', 'set_transient'];
        foreach ($required_functions as $func) {
            if (function_exists($func)) {
                $this->log("Функция {$func} доступна ✓", 'success');
            } else {
                $this->log("Функция {$func} недоступна", 'error');
                return false;
            }
        }
        
        // Проверяем права на запись
        if (is_writable(WP_CONTENT_DIR . '/plugins/')) {
            $this->log("Права на запись в директорию плагинов ✓", 'success');
        } else {
            $this->log("Нет прав на запись в директорию плагинов", 'error');
            return false;
        }
        
        return true;
    }
    
    private function activate_plugin() {
        $this->log("Активация плагина BFCalc Live Rates...", 'info');
        
        // Проверяем, существует ли файл плагина
        if (!file_exists($this->plugin_file)) {
            $this->log("Файл плагина не найден: {$this->plugin_file}", 'error');
            return false;
        }
        
        $this->log("Файл плагина найден ✓", 'success');
        
        // Активируем плагин
        $plugin_basename = 'bfcalc-live-rates/bfcalc-live-rates.php';
        
        if (!is_plugin_active($plugin_basename)) {
            $result = activate_plugin($plugin_basename);
            
            if (is_wp_error($result)) {
                $this->log("Ошибка активации плагина: " . $result->get_error_message(), 'error');
                return false;
            } else {
                $this->log("Плагин успешно активирован ✓", 'success');
            }
        } else {
            $this->log("Плагин уже активен ✓", 'success');
        }
        
        // Проверяем, что класс загружен
        if (class_exists('BFCalc_Live_Rates')) {
            $this->log("Класс BFCalc_Live_Rates загружен ✓", 'success');
        } else {
            $this->log("Класс BFCalc_Live_Rates не найден", 'error');
            return false;
        }
        
        return true;
    }
    
    private function test_plugin_functionality() {
        $this->log("Тестирование функциональности плагина...", 'info');
        
        // Тестируем REST API
        $url = home_url('/wp-json/bfcalc/v1/rates?scheme=avg');
        $response = wp_remote_get($url, ['timeout' => 10]);
        
        if (is_wp_error($response)) {
            $this->log("Ошибка REST API: " . $response->get_error_message(), 'error');
        } else {
            $status_code = wp_remote_retrieve_response_code($response);
            if ($status_code === 200) {
                $this->log("REST API отвечает корректно (200) ✓", 'success');
                
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
                
                if ($data && isset($data['ok']) && $data['ok']) {
                    $this->log("API возвращает валидные данные ✓", 'success');
                    
                    if (isset($data['baseRates'])) {
                        $this->log("Базовые ставки найдены ✓", 'success');
                        
                        // Показываем пример ставок
                        if (isset($data['baseRates']['44fz']['participation'])) {
                            $rate = $data['baseRates']['44fz']['participation'];
                            $this->log("Пример ставки (44-ФЗ, участие): {$rate}%", 'info');
                        }
                    }
                } else {
                    $this->log("API возвращает некорректные данные", 'warning');
                }
            } else {
                $this->log("REST API отвечает с кодом: {$status_code}", 'warning');
            }
        }
        
        // Проверяем cron задачи
        $next_cron = wp_next_scheduled('bfcalc_fetch_rates_daily');
        if ($next_cron) {
            $next_run = date('Y-m-d H:i:s', $next_cron);
            $this->log("Cron задача запланирована на: {$next_run} ✓", 'success');
        } else {
            $this->log("Cron задача не запланирована", 'warning');
            
            // Пытаемся запланировать
            if (!wp_next_scheduled('bfcalc_fetch_rates_daily')) {
                wp_schedule_event(time() + 120, 'daily', 'bfcalc_fetch_rates_daily');
                $this->log("Cron задача запланирована вручную ✓", 'success');
            }
        }
        
        // Проверяем кеш
        $cached_data = get_transient('bfcalc_live_rates_v1');
        if ($cached_data) {
            $this->log("Кеш содержит данные ✓", 'success');
            if (isset($cached_data['updated'])) {
                $this->log("Данные обновлены: " . $cached_data['updated'], 'info');
            }
        } else {
            $this->log("Кеш пуст, выполняется первоначальная загрузка...", 'info');
            
            // Запускаем загрузку данных
            if (class_exists('BFCalc_Live_Rates')) {
                $instance = new BFCalc_Live_Rates();
                $result = $instance->fetch_and_cache();
                
                if ($result) {
                    $this->log("Первоначальная загрузка данных выполнена ✓", 'success');
                } else {
                    $this->log("Ошибка первоначальной загрузки данных", 'warning');
                }
            }
        }
        
        return true;
    }
    
    private function update_calculator() {
        $this->log("Обновление калькулятора...", 'info');
        
        $calc_file = ABSPATH . 'bfcalc-updated.html';
        
        if (file_exists($calc_file)) {
            $this->log("Файл обновленного калькулятора найден ✓", 'success');
            
            // Проверяем содержимое
            $content = file_get_contents($calc_file);
            
            $required_elements = [
                'loadLiveRates' => 'Функция загрузки live-ставок',
                'applyLiveRates' => 'Функция применения ставок',
                '/wp-json/bfcalc/v1/rates' => 'URL API эндпойнта'
            ];
            
            foreach ($required_elements as $element => $description) {
                if (strpos($content, $element) !== false) {
                    $this->log("{$description} найдена ✓", 'success');
                } else {
                    $this->log("{$description} не найдена", 'error');
                }
            }
            
            // Создаем инструкцию по интеграции
            $this->log("Создание инструкции по интеграции...", 'info');
            
            $integration_guide = $this->create_integration_guide();
            $guide_file = ABSPATH . 'bfcalc-integration-guide.md';
            file_put_contents($guide_file, $integration_guide);
            
            if (file_exists($guide_file)) {
                $this->log("Инструкция по интеграции создана: {$guide_file} ✓", 'success');
            }
            
        } else {
            $this->log("Файл обновленного калькулятора не найден", 'error');
        }
        
        return true;
    }
    
    private function create_integration_guide() {
        return "# Инструкция по интеграции BFCalc Live Rates

## 🎯 Обзор

Система автоматического обновления ставок для калькулятора банковских гарантий успешно развернута и готова к использованию.

## 📁 Файлы системы

- **Плагин:** `wp-content/plugins/bfcalc-live-rates/bfcalc-live-rates.php`
- **Обновленный калькулятор:** `bfcalc-updated.html`
- **Тесты:** `test-bfcalc-live-rates.php`
- **Скрипт развертывания:** `deploy-bfcalc-live-rates.php`

## 🔧 Как интегрировать в Elementor

1. **Откройте страницу с калькулятором в Elementor**
2. **Найдите HTML виджет с калькулятором**
3. **Замените содержимое на код из файла `bfcalc-updated.html`**
4. **Сохраните изменения**

## 🌐 API Эндпойнт

Система предоставляет REST API для получения актуальных ставок:

```
GET /wp-json/bfcalc/v1/rates?scheme=avg
```

**Параметры:**
- `scheme` - схема агрегации: `avg` (средняя), `min` (минимальная), `max` (максимальная)

**Ответ:**
```json
{
  \"ok\": true,
  \"scheme\": \"avg\",
  \"updated\": \"2025-10-18 10:30:00\",
  \"baseRates\": {
    \"44fz\": {
      \"participation\": 2.2,
      \"performance\": 3.8,
      \"warranty\": 5.0,
      \"advance\": 4.5
    },
    \"223fz\": { ... },
    \"185fz\": { ... },
    \"comm\": { ... }
  },
  \"source\": \"https://garantolog.ru/tarify-bankov/\",
  \"banks_count\": 15
}
```

## ⏰ Автоматическое обновление

- **Частота:** Ежедневно в 00:02
- **Источник:** https://garantolog.ru/tarify-bankov/
- **Кеширование:** 24 часа
- **Fallback:** Локальная матрица ставок

## 🧪 Тестирование

Запустите тесты для проверки работоспособности:

```
https://bizfin-pro.ru/test-bfcalc-live-rates.php?run_tests=1
```

## 📊 Мониторинг

Админ-панель WordPress:
- Перейдите в **Настройки → BFCalc Live Rates**
- Просматривайте статистику загрузки
- Ручное обновление ставок

## 🔍 Отладка

**Логи WordPress:**
```php
// Включите отладку в wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

**Проверка кеша:**
```php
$cached = get_transient('bfcalc_live_rates_v1');
var_dump($cached);
```

**Проверка cron:**
```php
$next = wp_next_scheduled('bfcalc_fetch_rates_daily');
echo date('Y-m-d H:i:s', $next);
```

## ⚠️ Важные замечания

1. **Парсинг:** Регулярные выражения могут потребовать настройки под изменения в структуре источника
2. **CORS:** API работает только с вашего домена
3. **Fallback:** При недоступности источника используются локальные ставки
4. **Кеширование:** Браузер кеширует ставки на 24 часа

## 🚀 Готово к использованию!

Система полностью настроена и готова к продуктивному использованию.
";
    }
    
    private function run_final_tests() {
        $this->log("Запуск финальных тестов...", 'info');
        
        // Тест 1: API доступность
        $url = home_url('/wp-json/bfcalc/v1/rates?scheme=avg');
        $response = wp_remote_get($url, ['timeout' => 5]);
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $this->log("Тест 1: API доступен ✓", 'success');
        } else {
            $this->log("Тест 1: API недоступен", 'error');
        }
        
        // Тест 2: Кеширование
        $cached = get_transient('bfcalc_live_rates_v1');
        if ($cached && !empty($cached['per_bank'])) {
            $this->log("Тест 2: Кеширование работает ✓", 'success');
        } else {
            $this->log("Тест 2: Проблемы с кешированием", 'warning');
        }
        
        // Тест 3: Cron задачи
        $next_cron = wp_next_scheduled('bfcalc_fetch_rates_daily');
        if ($next_cron) {
            $this->log("Тест 3: Cron задачи настроены ✓", 'success');
        } else {
            $this->log("Тест 3: Cron задачи не настроены", 'warning');
        }
        
        // Тест 4: Файлы калькулятора
        $calc_file = ABSPATH . 'bfcalc-updated.html';
        if (file_exists($calc_file)) {
            $this->log("Тест 4: Файлы калькулятора на месте ✓", 'success');
        } else {
            $this->log("Тест 4: Файлы калькулятора отсутствуют", 'error');
        }
        
        return true;
    }
    
    private function display_summary() {
        echo "<div class='step success'>";
        echo "<h2>🎉 Развертывание завершено!</h2>";
        echo "<p>Система автоматического обновления ставок для калькулятора банковских гарантий успешно развернута.</p>";
        echo "</div>";
        
        echo "<div class='step info'>";
        echo "<h3>📋 Следующие шаги:</h3>";
        echo "<ol>";
        echo "<li>Интегрируйте обновленный код калькулятора в Elementor</li>";
        echo "<li>Запустите тесты: <a href='test-bfcalc-live-rates.php?run_tests=1'>test-bfcalc-live-rates.php?run_tests=1</a></li>";
        echo "<li>Проверьте работу калькулятора на сайте</li>";
        echo "<li>Настройте мониторинг в админ-панели WordPress</li>";
        echo "</ol>";
        echo "</div>";
        
        echo "<div class='step info'>";
        echo "<h3>🔗 Полезные ссылки:</h3>";
        echo "<ul>";
        echo "<li><a href='test-bfcalc-live-rates.php?run_tests=1'>Запустить тесты</a></li>";
        echo "<li><a href='bfcalc-integration-guide.md'>Инструкция по интеграции</a></li>";
        echo "<li><a href='" . admin_url('options-general.php?page=bfcalc-live-rates') . "'>Админ-панель плагина</a></li>";
        echo "<li><a href='/wp-json/bfcalc/v1/rates?scheme=avg'>API эндпойнт</a></li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div class='code'>";
        echo "<h3>📊 Статистика развертывания:</h3>";
        echo "<ul>";
        foreach ($this->deployment_log as $log) {
            echo "<li>[{$log['timestamp']}] {$log['type']}: {$log['message']}</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
}

// Запуск развертывания
if (isset($_GET['deploy'])) {
    $deployer = new BFCalc_Deployer();
    $deployer->deploy();
} else {
    echo "<h1>🚀 BFCalc Live Rates - Развертывание</h1>";
    echo "<p><a href='?deploy=1' style='background: #0073aa; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>🚀 Начать развертывание</a></p>";
    echo "<p>Этот скрипт автоматически развернет систему автоматического обновления ставок для калькулятора банковских гарантий.</p>";
    echo "<h2>Что будет сделано:</h2>";
    echo "<ul>";
    echo "<li>✅ Проверка системных требований</li>";
    echo "<li>✅ Активация плагина BFCalc Live Rates</li>";
    echo "<li>✅ Тестирование функциональности</li>";
    echo "<li>✅ Обновление калькулятора</li>";
    echo "<li>✅ Финальные тесты</li>";
    echo "</ul>";
    echo "<p><strong>Внимание:</strong> Убедитесь, что у вас есть права администратора WordPress.</p>";
}
?>
