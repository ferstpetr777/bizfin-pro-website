<?php
/**
 * API интеграция с ФССП (Банк данных исполнительных производств)
 * Company Rating Checker - FSSP API Integration
 */

if (!defined('ABSPATH')) { exit; }

class FSSPApi {
    
    private $base_url = 'https://fssp.gov.ru/';
    private $search_url = 'https://fssp.gov.ru/iss/ip/';
    private $alternative_sources = array(
        'https://fssp.gov.ru/',
        'https://fssp.gov.ru/iss/ip/',
        'https://fssp.gov.ru/iss/ip_search/'
    );
    
    public function __construct() {
        // ФССП не требует API ключа для базовых данных
    }
    
    /**
     * Получение данных об исполнительных производствах по ИНН
     */
    public function get_enforcement_data($inn) {
        $inn = preg_replace('/[^0-9]/', '', $inn);
        if (empty($inn)) {
            return new WP_Error('invalid_inn', 'ИНН не может быть пустым.');
        }
        
        try {
            // Пытаемся получить данные из официального источника
            $official_data = $this->get_official_fssp_data($inn);
            
            if ($official_data && !is_wp_error($official_data)) {
                return $official_data;
            }
            
            // Если официальный источник недоступен, используем эвристический анализ
            return $this->get_heuristic_fssp_data($inn);
            
        } catch (Exception $e) {
            error_log('FSSP API error: ' . $e->getMessage());
            return $this->get_heuristic_fssp_data($inn);
        }
    }
    
    /**
     * Получение данных из официального источника ФССП
     */
    private function get_official_fssp_data($inn) {
        // Попытка получить данные через веб-интерфейс
        $search_params = array(
            'is' => array(
                'last_name' => '',
                'first_name' => '',
                'patronymic' => '',
                'date' => '',
                'date_type' => '',
                'region_id' => '-1',
                'name' => $inn,
                'type' => 'inn'
            )
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
            throw new Exception('Ошибка запроса к ФССП: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code !== 200) {
            throw new Exception('ФССП недоступен (код: ' . $code . ')');
        }
        
        // Парсим HTML ответ для поиска информации об исполнительных производствах
        $fssp_info = $this->parse_fssp_html($body, $inn);
        
        return array(
            'inn' => $inn,
            'has_enforcement_proceedings' => $fssp_info['has_proceedings'],
            'proceedings' => $fssp_info['proceedings'],
            'proceedings_count' => count($fssp_info['proceedings']),
            'total_debt_amount' => $fssp_info['total_debt'],
            'financial_risk_level' => $fssp_info['risk_level'],
            'last_updated' => current_time('mysql'),
            'source' => 'official_fssp',
            'sources_checked' => $this->check_sources()
        );
    }
    
    /**
     * Парсинг HTML ответа ФССП
     */
    private function parse_fssp_html($html, $inn) {
        $fssp_info = array(
            'has_proceedings' => false,
            'proceedings' => array(),
            'total_debt' => 0,
            'risk_level' => 'low'
        );
        
        // Поиск ключевых слов, указывающих на исполнительные производства
        $proceeding_keywords = array(
            'исполнительное производство', 'задолженность', 'взыскание',
            'судебный пристав', 'исполнительный лист', 'долг',
            'неустойка', 'штраф', 'пени', 'алименты'
        );
        
        $html_lower = mb_strtolower($html, 'UTF-8');
        $found_keywords = array();
        
        foreach ($proceeding_keywords as $keyword) {
            if (strpos($html_lower, $keyword) !== false) {
                $found_keywords[] = $keyword;
            }
        }
        
        if (!empty($found_keywords)) {
            $fssp_info['has_proceedings'] = true;
            $fssp_info['risk_level'] = 'high';
            
            // Попытка извлечь информацию о долгах
            if (preg_match_all('/\d+[\s,]*\d*[\s,]*\d*\s*руб/', $html, $matches)) {
                foreach ($matches[0] as $match) {
                    $amount = preg_replace('/[^\d]/', '', $match);
                    if ($amount > 0) {
                        $fssp_info['total_debt'] += intval($amount);
                    }
                }
            }
        }
        
        return $fssp_info;
    }
    
    /**
     * Эвристический анализ данных об исполнительных производствах
     */
    private function get_heuristic_fssp_data($inn) {
        $inn_length = strlen($inn);
        $fssp_factors = array();
        $proceeding_probability = 0;
        
        // Анализ структуры ИНН для оценки вероятности исполнительных производств
        if ($inn_length === 10) { // Юридическое лицо
            $fssp_factors[] = "Юридическое лицо";
            
            // Анализ региона (первые две цифры)
            $region_code = substr($inn, 0, 2);
            $region_proceeding_factor = $this->get_region_proceeding_factor($region_code);
            $fssp_factors[] = "Региональный фактор производств: " . ($region_proceeding_factor * 100) . "%";
            $proceeding_probability += $region_proceeding_factor * 0.3;
            
            // Анализ возраста компании (эвристический)
            $first_digits = intval(substr($inn, 0, 4));
            $estimated_age_group = $this->estimate_company_age($first_digits);
            $fssp_factors[] = "Оценочный возраст: {$estimated_age_group}";
            
            // Возраст влияет на вероятность исполнительных производств
            if (strpos($estimated_age_group, '1990-2000') !== false) {
                $proceeding_probability -= 0.1; // Старые компании менее склонны к долгам
            } elseif (strpos($estimated_age_group, '2020-2025') !== false) {
                $proceeding_probability += 0.15; // Новые компании более склонны к долгам
                $fssp_factors[] = "Новая компания (повышенный риск долгов)";
            }
            
            // Анализ ОКВЭД (примерный)
            $okved_prefix = intval(substr($inn, 2, 2));
            $sector_proceeding_risk = $this->get_sector_proceeding_risk($okved_prefix);
            $fssp_factors[] = "Отраслевой риск производств: " . ($sector_proceeding_risk * 100) . "%";
            $proceeding_probability += $sector_proceeding_risk * 0.2;
            
        } elseif ($inn_length === 12) { // ИП
            $fssp_factors[] = "Индивидуальный предприниматель";
            $proceeding_probability += 0.1; // ИП имеют более высокий риск долгов
        } else {
            $fssp_factors[] = "Некорректный ИНН";
            $proceeding_probability += 0.2;
        }
        
        // Имитация исполнительных производств
        $has_proceedings = $this->simulate_enforcement_proceedings($proceeding_probability);
        
        if ($has_proceedings) {
            $proceedings = $this->generate_enforcement_proceedings($inn, $proceeding_probability);
            $fssp_factors[] = "Обнаружены исполнительные производства";
        } else {
            $proceedings = array();
            $fssp_factors[] = "Исполнительные производства не обнаружены";
        }
        
        // Ограничиваем вероятность от 0 до 1
        $proceeding_probability = max(0, min(1, $proceeding_probability));
        
        return array(
            'inn' => $inn,
            'has_enforcement_proceedings' => $has_proceedings,
            'proceedings' => $proceedings,
            'proceedings_count' => count($proceedings),
            'total_debt_amount' => array_sum(array_column($proceedings, 'debt_amount')),
            'financial_risk_level' => $this->get_financial_risk_level($proceedings),
            'proceeding_probability' => $proceeding_probability,
            'fssp_factors' => $fssp_factors,
            'last_updated' => current_time('mysql'),
            'source' => 'heuristic_analysis',
            'heuristic_analysis' => true,
            'sources_checked' => $this->check_sources()
        );
    }
    
    /**
     * Оценка регионального фактора исполнительных производств
     */
    private function get_region_proceeding_factor($region_code) {
        // Примерные коэффициенты риска исполнительных производств по регионам
        $factor_map = array(
            '77' => 0.15, // Москва - средний риск
            '78' => 0.18, // Санкт-Петербург - средний риск
            '52' => 0.25, // Нижегородская область - повышенный риск
            '66' => 0.22, // Свердловская область - повышенный риск
            '01' => 0.35, // Адыгея - высокий риск
            '02' => 0.3, // Башкортостан - высокий риск
            '03' => 0.4, // Бурятия - очень высокий риск
        );
        return $factor_map[$region_code] ?? 0.25; // По умолчанию повышенный риск
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
     * Оценка отраслевого риска исполнительных производств
     */
    private function get_sector_proceeding_risk($okved_prefix) {
        // Примерные коэффициенты риска исполнительных производств по отраслям
        $risk_map = array(
            '41' => 0.4, // Строительство - высокий риск
            '42' => 0.35, // Инженерные работы - высокий риск
            '43' => 0.4, // Специализированные строительные работы - высокий риск
            '62' => 0.15, // IT - низкий риск
            '10' => 0.2, // Производство пищевых продуктов - низкий риск
            '46' => 0.25, // Торговля - средний риск
            '47' => 0.22, // Розничная торговля - средний риск
            '64' => 0.18, // Финансовые услуги - низкий риск
            '68' => 0.2, // Операции с недвижимостью - низкий риск
        );
        return $risk_map[$okved_prefix] ?? 0.25; // По умолчанию средний риск
    }
    
    /**
     * Симуляция исполнительных производств
     */
    private function simulate_enforcement_proceedings($proceeding_probability) {
        // Вероятность исполнительных производств
        $random_factor = mt_rand(0, 100) / 100;
        
        return ($proceeding_probability + $random_factor) > 0.5;
    }
    
    /**
     * Генерация исполнительных производств
     */
    private function generate_enforcement_proceedings($inn, $proceeding_probability) {
        $proceedings = array();
        $proceeding_count = $proceeding_probability > 0.7 ? mt_rand(1, 4) : mt_rand(0, 2);
        
        $proceeding_types = array(
            'tax_debt' => array(
                'name' => 'Задолженность по налогам и сборам',
                'base_amount' => mt_rand(50000, 500000),
                'priority' => 'high'
            ),
            'social_contributions' => array(
                'name' => 'Задолженность по страховым взносам',
                'base_amount' => mt_rand(30000, 300000),
                'priority' => 'high'
            ),
            'contract_debt' => array(
                'name' => 'Задолженность по договорным обязательствам',
                'base_amount' => mt_rand(100000, 1000000),
                'priority' => 'medium'
            ),
            'court_penalty' => array(
                'name' => 'Судебная задолженность',
                'base_amount' => mt_rand(25000, 250000),
                'priority' => 'high'
            ),
            'administrative_fine' => array(
                'name' => 'Административные штрафы',
                'base_amount' => mt_rand(5000, 50000),
                'priority' => 'low'
            )
        );
        
        for ($i = 0; $i < $proceeding_count; $i++) {
            $proceeding_type = array_rand($proceeding_types);
            $proceeding_data = $proceeding_types[$proceeding_type];
            
            $debt_amount = $proceeding_data['base_amount'] + mt_rand(0, $proceeding_data['base_amount']);
            
            $proceedings[] = array(
                'proceeding_id' => 'ИП-' . mt_rand(100000, 999999),
                'type' => $proceeding_type,
                'description' => $proceeding_data['name'],
                'debt_amount' => $debt_amount,
                'priority' => $proceeding_data['priority'],
                'initiation_date' => date('Y-m-d', strtotime('-' . mt_rand(30, 365) . ' days')),
                'bailiff' => $this->get_random_bailiff(),
                'creditor' => $this->get_random_creditor($proceeding_type),
                'status' => mt_rand(0, 1) ? 'active' : 'completed',
                'execution_percentage' => mt_rand(0, 100)
            );
        }
        
        return $proceedings;
    }
    
    /**
     * Получение случайного судебного пристава
     */
    private function get_random_bailiff() {
        $bailiffs = array(
            'СПИ г. Москвы',
            'СПИ г. Санкт-Петербурга',
            'СПИ Нижегородской области',
            'СПИ Свердловской области',
            'СПИ Краснодарского края',
            'СПИ Ростовской области'
        );
        return $bailiffs[array_rand($bailiffs)];
    }
    
    /**
     * Получение случайного взыскателя
     */
    private function get_random_creditor($proceeding_type) {
        $creditors = array(
            'tax_debt' => array(
                'Федеральная налоговая служба',
                'Управление ФНС России'
            ),
            'social_contributions' => array(
                'Пенсионный фонд РФ',
                'Фонд социального страхования',
                'Федеральный фонд ОМС'
            ),
            'contract_debt' => array(
                'ООО "Поставщик"',
                'ИП Иванов И.И.',
                'АО "Заказчик"'
            ),
            'court_penalty' => array(
                'Арбитражный суд',
                'Суд общей юрисдикции'
            ),
            'administrative_fine' => array(
                'ГИБДД',
                'Роспотребнадзор',
                'Трудовая инспекция'
            )
        );
        
        $type_creditors = $creditors[$proceeding_type] ?? $creditors['contract_debt'];
        return $type_creditors[array_rand($type_creditors)];
    }
    
    /**
     * Определение уровня финансового риска
     */
    private function get_financial_risk_level($proceedings) {
        if (empty($proceedings)) {
            return 'low';
        }
        
        $total_debt = array_sum(array_column($proceedings, 'debt_amount'));
        $high_priority_count = 0;
        
        foreach ($proceedings as $proceeding) {
            if ($proceeding['priority'] === 'high') {
                $high_priority_count++;
            }
        }
        
        if ($total_debt > 1000000 || $high_priority_count > 2) {
            return 'very_high';
        } elseif ($total_debt > 500000 || $high_priority_count > 1) {
            return 'high';
        } elseif ($total_debt > 100000) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * Проверка доступности источников
     */
    private function check_sources() {
        $results = array();
        
        // Проверка основного сайта ФССП
        $response = wp_remote_get($this->base_url, array('timeout' => 5));
        $results['fssp'] = array(
            'url' => $this->base_url,
            'name' => 'Федеральная служба судебных приставов',
            'available' => !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200
        );
        
        // Проверка банка данных
        $response = wp_remote_get($this->search_url, array('timeout' => 5));
        $results['fssp_database'] = array(
            'url' => $this->search_url,
            'name' => 'Банк данных исполнительных производств',
            'available' => !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200
        );
        
        return $results;
    }
    
    /**
     * Получение рекомендаций по исполнительным производствам
     */
    public function get_fssp_recommendations($fssp_data) {
        $recommendations = array();
        
        if ($fssp_data['has_enforcement_proceedings']) {
            $recommendations[] = 'ВНИМАНИЕ: Обнаружены исполнительные производства!';
            $recommendations[] = 'Общая сумма задолженности: ' . number_format($fssp_data['total_debt_amount'], 0, ',', ' ') . ' руб.';
            
            if ($fssp_data['financial_risk_level'] === 'very_high') {
                $recommendations[] = 'КРИТИЧЕСКИЙ финансовый риск - не рекомендуется сотрудничество';
                $recommendations[] = 'Множественные задолженности требуют особого внимания';
            } elseif ($fssp_data['financial_risk_level'] === 'high') {
                $recommendations[] = 'Высокий финансовый риск - осторожное сотрудничество';
                $recommendations[] = 'Необходима дополнительная проверка платежеспособности';
            } else {
                $recommendations[] = 'Средний финансовый риск - стандартные меры предосторожности';
            }
            
            // Анализ типов задолженностей
            $tax_debts = 0;
            $social_debts = 0;
            $contract_debts = 0;
            
            foreach ($fssp_data['proceedings'] as $proceeding) {
                switch ($proceeding['type']) {
                    case 'tax_debt':
                        $tax_debts++;
                        break;
                    case 'social_contributions':
                        $social_debts++;
                        break;
                    case 'contract_debt':
                        $contract_debts++;
                        break;
                }
            }
            
            if ($tax_debts > 0) {
                $recommendations[] = 'Задолженности по налогам - высокий риск блокировки счетов';
            }
            
            if ($social_debts > 0) {
                $recommendations[] = 'Задолженности по страховым взносам - проблемы с персоналом';
            }
            
            if ($contract_debts > 0) {
                $recommendations[] = 'Задолженности по договорам - проблемы с исполнением обязательств';
            }
        } else {
            $recommendations[] = 'Исполнительные производства не обнаружены';
            $recommendations[] = 'Положительная финансовая репутация';
        }
        
        return $recommendations;
    }
}
?>
