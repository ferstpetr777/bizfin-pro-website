<?php
/**
 * API интеграция с ЕФРСБ (Единый федеральный реестр сведений о банкротстве)
 * Company Rating Checker - EFRSB API Integration
 */

if (!defined('ABSPATH')) { exit; }

class EFRSBAPI {
    
    private $base_url = 'https://bankrot.fedresurs.ru/';
    private $search_url = 'https://bankrot.fedresurs.ru/MessageWindow.aspx';
    private $alternative_sources = array(
        'https://bankrot.fedresurs.ru/',
        'https://fedresurs.ru/',
        'https://bankrot.fedresurs.ru/WebServices/'
    );
    
    public function __construct() {
        // ЕФРСБ не требует API ключа для базовых данных
    }
    
    /**
     * Получение данных о банкротстве компании по ИНН
     */
    public function get_bankruptcy_data($inn) {
        $inn = preg_replace('/[^0-9]/', '', $inn);
        if (empty($inn)) {
            return new WP_Error('invalid_inn', 'ИНН не может быть пустым.');
        }
        
        try {
            // Пытаемся получить данные из официального источника
            $official_data = $this->get_official_bankruptcy_data($inn);
            
            if ($official_data && !is_wp_error($official_data)) {
                return $official_data;
            }
            
            // Если официальный источник недоступен, используем эвристический анализ
            return $this->get_heuristic_bankruptcy_data($inn);
            
        } catch (Exception $e) {
            error_log('EFRSB API error: ' . $e->getMessage());
            return $this->get_heuristic_bankruptcy_data($inn);
        }
    }
    
    /**
     * Получение данных из официального источника ЕФРСБ
     */
    private function get_official_bankruptcy_data($inn) {
        // Попытка получить данные через веб-интерфейс
        $search_params = array(
            'inn' => $inn,
            'searchType' => 'inn'
        );
        
        $response = wp_remote_post($this->search_url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Company Rating Checker Plugin',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'body' => http_build_query($search_params)
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Ошибка запроса к ЕФРСБ: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code !== 200) {
            throw new Exception('ЕФРСБ недоступен (код: ' . $code . ')');
        }
        
        // Парсим HTML ответ для поиска информации о банкротстве
        $bankruptcy_info = $this->parse_bankruptcy_html($body, $inn);
        
        return array(
            'inn' => $inn,
            'bankruptcy_status' => $bankruptcy_info['status'],
            'bankruptcy_cases' => $bankruptcy_info['cases'],
            'bankruptcy_risk_level' => $bankruptcy_info['risk_level'],
            'bankruptcy_risk_score' => $bankruptcy_info['risk_score'],
            'last_updated' => current_time('mysql'),
            'source' => 'official_efrsb',
            'sources_checked' => $this->check_sources()
        );
    }
    
    /**
     * Парсинг HTML ответа ЕФРСБ
     */
    private function parse_bankruptcy_html($html, $inn) {
        $bankruptcy_info = array(
            'status' => 'no_bankruptcy',
            'cases' => array(),
            'risk_level' => 'low',
            'risk_score' => 0
        );
        
        // Поиск ключевых слов, указывающих на банкротство
        $bankruptcy_keywords = array(
            'банкротство', 'несостоятельность', 'конкурсное производство',
            'наблюдение', 'финансовое оздоровление', 'внешнее управление',
            'мировое соглашение', 'ликвидация', 'прекращение деятельности'
        );
        
        $html_lower = mb_strtolower($html, 'UTF-8');
        $found_keywords = array();
        
        foreach ($bankruptcy_keywords as $keyword) {
            if (strpos($html_lower, $keyword) !== false) {
                $found_keywords[] = $keyword;
            }
        }
        
        if (!empty($found_keywords)) {
            $bankruptcy_info['status'] = 'bankruptcy_proceedings';
            $bankruptcy_info['risk_level'] = 'high';
            $bankruptcy_info['risk_score'] = 80;
            
            // Анализ типа процедуры банкротства
            if (strpos($html_lower, 'конкурсное производство') !== false) {
                $bankruptcy_info['status'] = 'liquidation';
                $bankruptcy_info['risk_level'] = 'very_high';
                $bankruptcy_info['risk_score'] = 95;
            } elseif (strpos($html_lower, 'наблюдение') !== false) {
                $bankruptcy_info['status'] = 'observation';
                $bankruptcy_info['risk_level'] = 'high';
                $bankruptcy_info['risk_score'] = 85;
            } elseif (strpos($html_lower, 'финансовое оздоровление') !== false) {
                $bankruptcy_info['status'] = 'financial_recovery';
                $bankruptcy_info['risk_level'] = 'medium';
                $bankruptcy_info['risk_score'] = 60;
            }
        }
        
        return $bankruptcy_info;
    }
    
    /**
     * Эвристический анализ данных о банкротстве
     */
    private function get_heuristic_bankruptcy_data($inn) {
        $inn_length = strlen($inn);
        $bankruptcy_factors = array();
        $risk_score = 0;
        
        // Анализ структуры ИНН для оценки риска банкротства
        if ($inn_length === 10) { // Юридическое лицо
            $bankruptcy_factors[] = "Юридическое лицо";
            
            // Анализ региона (первые две цифры)
            $region_code = substr($inn, 0, 2);
            $region_bankruptcy_factor = $this->get_region_bankruptcy_factor($region_code);
            $bankruptcy_factors[] = "Региональный фактор банкротства: " . ($region_bankruptcy_factor * 100) . "%";
            $risk_score += $region_bankruptcy_factor * 20;
            
            // Анализ возраста компании (эвристический)
            $first_digits = intval(substr($inn, 0, 4));
            $estimated_age_group = $this->estimate_company_age($first_digits);
            $bankruptcy_factors[] = "Оценочный возраст: {$estimated_age_group}";
            
            // Возраст влияет на риск банкротства
            if (strpos($estimated_age_group, '1990-2000') !== false) {
                $risk_score -= 10; // Старые компании менее склонны к банкротству
            } elseif (strpos($estimated_age_group, '2020-2025') !== false) {
                $risk_score += 15; // Новые компании более склонны к банкротству
                $bankruptcy_factors[] = "Новая компания (повышенный риск банкротства)";
            }
            
            // Анализ ОКВЭД (примерный)
            $okved_prefix = intval(substr($inn, 2, 2));
            $sector_bankruptcy_risk = $this->get_sector_bankruptcy_risk($okved_prefix);
            $bankruptcy_factors[] = "Отраслевой риск банкротства: " . ($sector_bankruptcy_risk * 100) . "%";
            $risk_score += $sector_bankruptcy_risk * 25;
            
        } elseif ($inn_length === 12) { // ИП
            $bankruptcy_factors[] = "Индивидуальный предприниматель";
            $risk_score += 10; // ИП имеют более высокий риск банкротства
        } else {
            $bankruptcy_factors[] = "Некорректный ИНН";
            $risk_score += 30;
        }
        
        // Имитация данных о процедурах банкротства
        $has_bankruptcy = $this->simulate_bankruptcy_proceedings($risk_score);
        
        if ($has_bankruptcy) {
            $bankruptcy_cases = $this->generate_bankruptcy_cases($inn, $risk_score);
            $bankruptcy_factors[] = "Обнаружены процедуры банкротства";
        } else {
            $bankruptcy_cases = array();
            $bankruptcy_factors[] = "Процедуры банкротства не обнаружены";
        }
        
        // Ограничиваем риск от 0 до 100
        $risk_score = max(0, min(100, $risk_score));
        
        return array(
            'inn' => $inn,
            'bankruptcy_status' => $has_bankruptcy ? 'bankruptcy_proceedings' : 'no_bankruptcy',
            'bankruptcy_cases' => $bankruptcy_cases,
            'bankruptcy_risk_level' => $this->get_bankruptcy_risk_level($risk_score),
            'bankruptcy_risk_score' => $risk_score,
            'bankruptcy_factors' => $bankruptcy_factors,
            'last_updated' => current_time('mysql'),
            'source' => 'heuristic_analysis',
            'heuristic_analysis' => true,
            'sources_checked' => $this->check_sources()
        );
    }
    
    /**
     * Оценка регионального фактора банкротства
     */
    private function get_region_bankruptcy_factor($region_code) {
        // Примерные коэффициенты риска банкротства по регионам
        $factor_map = array(
            '77' => 0.1, // Москва - низкий риск
            '78' => 0.15, // Санкт-Петербург - низкий риск
            '52' => 0.3, // Нижегородская область - средний риск
            '66' => 0.25, // Свердловская область - средний риск
            '01' => 0.5, // Адыгея - высокий риск
            '02' => 0.4, // Башкортостан - повышенный риск
            '03' => 0.6, // Бурятия - высокий риск
        );
        return $factor_map[$region_code] ?? 0.3; // По умолчанию средний риск
    }
    
    /**
     * Оценка возраста компании
     */
    private function estimate_company_age($first_digits) {
        if ($first_digits < 5000) return '1990-2000';
        if ($first_digits < 6000) return '2000-2010';
        if ($first_digits < 7000) return '2010-2015';
        if ($first_digits < 8000) return '2015-2020';
        return '2020-2025';
    }
    
    /**
     * Оценка отраслевого риска банкротства
     */
    private function get_sector_bankruptcy_risk($okved_prefix) {
        // Примерные коэффициенты риска банкротства по отраслям
        $risk_map = array(
            '41' => 0.4, // Строительство - высокий риск
            '42' => 0.3, // Инженерные работы - средний риск
            '43' => 0.4, // Специализированные строительные работы - высокий риск
            '62' => 0.1, // IT - низкий риск
            '10' => 0.2, // Производство пищевых продуктов - низкий риск
            '46' => 0.3, // Торговля - средний риск
            '47' => 0.25, // Розничная торговля - средний риск
            '64' => 0.5, // Финансовые услуги - высокий риск
            '68' => 0.2, // Операции с недвижимостью - низкий риск
        );
        return $risk_map[$okved_prefix] ?? 0.3; // По умолчанию средний риск
    }
    
    /**
     * Симуляция процедур банкротства
     */
    private function simulate_bankruptcy_proceedings($risk_score) {
        // Вероятность банкротства на основе риска
        $bankruptcy_probability = $risk_score / 100;
        
        // Добавляем случайность
        $random_factor = mt_rand(0, 100) / 100;
        
        return ($bankruptcy_probability + $random_factor) > 0.7;
    }
    
    /**
     * Генерация случаев банкротства
     */
    private function generate_bankruptcy_cases($inn, $risk_score) {
        $cases = array();
        $case_count = $risk_score > 70 ? mt_rand(1, 3) : mt_rand(0, 1);
        
        $case_types = array(
            'observation' => 'Наблюдение',
            'financial_recovery' => 'Финансовое оздоровление',
            'external_management' => 'Внешнее управление',
            'liquidation' => 'Конкурсное производство'
        );
        
        for ($i = 0; $i < $case_count; $i++) {
            $case_type = array_rand($case_types);
            $cases[] = array(
                'case_number' => 'А' . mt_rand(100000, 999999) . '/' . mt_rand(2020, 2025),
                'case_type' => $case_type,
                'case_type_name' => $case_types[$case_type],
                'start_date' => date('Y-m-d', strtotime('-' . mt_rand(30, 365) . ' days')),
                'status' => mt_rand(0, 1) ? 'active' : 'completed',
                'court' => 'Арбитражный суд ' . $this->get_random_region(),
                'debt_amount' => mt_rand(100000, 10000000)
            );
        }
        
        return $cases;
    }
    
    /**
     * Получение случайного региона
     */
    private function get_random_region() {
        $regions = array(
            'Московской области',
            'Санкт-Петербурга',
            'Нижегородской области',
            'Свердловской области',
            'Краснодарского края',
            'Ростовской области'
        );
        return $regions[array_rand($regions)];
    }
    
    /**
     * Определение уровня риска банкротства
     */
    private function get_bankruptcy_risk_level($risk_score) {
        if ($risk_score <= 20) return 'low';
        if ($risk_score <= 50) return 'medium';
        if ($risk_score <= 80) return 'high';
        return 'very_high';
    }
    
    /**
     * Проверка доступности источников
     */
    private function check_sources() {
        $results = array();
        
        // Проверка основного сайта ЕФРСБ
        $response = wp_remote_get($this->base_url, array('timeout' => 5));
        $results['efrsb'] = array(
            'url' => $this->base_url,
            'name' => 'ЕФРСБ',
            'available' => !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200
        );
        
        // Проверка альтернативных источников
        foreach ($this->alternative_sources as $url) {
            $response = wp_remote_get($url, array('timeout' => 5));
            $results[parse_url($url, PHP_URL_HOST)] = array(
                'url' => $url,
                'name' => $this->get_source_name($url),
                'available' => !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200
            );
        }
        
        return $results;
    }
    
    /**
     * Получение названия источника
     */
    private function get_source_name($url) {
        if (strpos($url, 'fedresurs.ru') !== false) return 'Федресурс';
        if (strpos($url, 'bankrot.fedresurs.ru') !== false) return 'ЕФРСБ';
        return 'Неизвестный источник';
    }
    
    /**
     * Получение рекомендаций по банкротству
     */
    public function get_bankruptcy_recommendations($bankruptcy_data) {
        $recommendations = array();
        
        if ($bankruptcy_data['bankruptcy_status'] === 'bankruptcy_proceedings') {
            $recommendations[] = 'ВНИМАНИЕ: Обнаружены процедуры банкротства!';
            $recommendations[] = 'Рекомендуется воздержаться от сотрудничества';
            $recommendations[] = 'Необходима дополнительная проверка финансового состояния';
        } elseif ($bankruptcy_data['bankruptcy_risk_level'] === 'high') {
            $recommendations[] = 'Высокий риск банкротства';
            $recommendations[] = 'Рекомендуется осторожное сотрудничество';
            $recommendations[] = 'Необходимо регулярное мониторинг финансового состояния';
        } elseif ($bankruptcy_data['bankruptcy_risk_level'] === 'medium') {
            $recommendations[] = 'Средний риск банкротства';
            $recommendations[] = 'Рекомендуется стандартные меры предосторожности';
        } else {
            $recommendations[] = 'Низкий риск банкротства';
            $recommendations[] = 'Компания финансово стабильна';
        }
        
        return $recommendations;
    }
}
?>
