<?php
/**
 * Класс для работы с API арбитражных дел
 * Company Rating Checker - Arbitration API
 */

class ArbitrationAPI {
    
    private $base_url = 'https://kad.arbitr.ru/';
    private $timeout = 30;
    private $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    
    /**
     * Получение данных об арбитражных делах по ИНН
     */
    public function get_cases_by_inn($inn, $limit = 10) {
        $results = array(
            'total_cases' => 0,
            'active_cases' => 0,
            'completed_cases' => 0,
            'total_amount' => 0,
            'cases' => array(),
            'summary' => array()
        );
        
        try {
            // Поиск дел через POST запрос
            $search_data = $this->search_cases($inn, $limit);
            
            if ($search_data && isset($search_data['data'])) {
                $results = $this->parse_cases_data($search_data['data'], $results);
            }
            
            // Дополнительный поиск через альтернативные источники
            $alternative_data = $this->search_alternative_sources($inn);
            if ($alternative_data) {
                $results = $this->merge_alternative_data($results, $alternative_data);
            }
            
        } catch (Exception $e) {
            error_log('ArbitrationAPI Error: ' . $e->getMessage());
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Поиск дел через основной API
     */
    private function search_cases($inn, $limit) {
        $url = $this->base_url . 'Search';
        
        $post_data = array(
            'Page' => 1,
            'Count' => $limit,
            'Courts' => '',
            'DateFrom' => '',
            'DateTo' => '',
            'Sides' => $inn,
            'Judges' => '',
            'CaseNumbers' => '',
            'WithVKSInstances' => 'false'
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json, text/html, */*',
            'X-Requested-With: XMLHttpRequest'
        ));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        if ($http_code !== 200) {
            throw new Exception('HTTP Error: ' . $http_code);
        }
        
        // Попытка декодировать JSON
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
        
        // Если не JSON, попробуем парсить HTML
        return $this->parse_html_response($response);
    }
    
    /**
     * Парсинг HTML ответа
     */
    private function parse_html_response($html) {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        $cases = array();
        
        // Поиск таблицы с делами
        $rows = $xpath->query('//table[@class="table"]//tr');
        
        foreach ($rows as $row) {
            $cells = $xpath->query('.//td', $row);
            if ($cells->length >= 4) {
                $case = array(
                    'number' => trim($cells->item(0)->textContent),
                    'date' => trim($cells->item(1)->textContent),
                    'court' => trim($cells->item(2)->textContent),
                    'participants' => trim($cells->item(3)->textContent)
                );
                $cases[] = $case;
            }
        }
        
        return array('data' => $cases);
    }
    
    /**
     * Поиск через альтернативные источники
     */
    private function search_alternative_sources($inn) {
        $sources = array(
            'rospravosudie' => 'https://rospravosudie.com/search?q=' . urlencode($inn),
            'sudrf' => 'https://sudrf.ru/index.php?id=300&act=go_ms_search&searchtype=ms&var=true&ms_type=ms&court_subj=1&court_dst=&ms_category=1&ms_number=&ms_date=&ms_date_to=&ms_participant=' . urlencode($inn)
        );
        
        $results = array();
        
        foreach ($sources as $name => $url) {
            try {
                $data = $this->fetch_url($url);
                if ($data) {
                    $results[$name] = $this->parse_alternative_source($data, $name);
                }
            } catch (Exception $e) {
                error_log("Error fetching {$name}: " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Получение URL
     */
    private function fetch_url($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            return $response;
        }
        
        return false;
    }
    
    /**
     * Парсинг альтернативных источников
     */
    private function parse_alternative_source($html, $source_name) {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        $cases = array();
        
        // Различные селекторы для разных источников
        $selectors = array(
            'rospravosudie' => '//div[@class="search-result"]//div[@class="case-item"]',
            'sudrf' => '//table//tr[position()>1]'
        );
        
        $selector = $selectors[$source_name] ?? '//div[contains(@class, "case")]';
        $elements = $xpath->query($selector);
        
        foreach ($elements as $element) {
            $case_data = $this->extract_case_data($element, $xpath, $source_name);
            if ($case_data) {
                $cases[] = $case_data;
            }
        }
        
        return $cases;
    }
    
    /**
     * Извлечение данных о деле
     */
    private function extract_case_data($element, $xpath, $source_name) {
        $case = array(
            'source' => $source_name,
            'number' => '',
            'date' => '',
            'court' => '',
            'participants' => '',
            'status' => 'unknown'
        );
        
        // Извлечение данных в зависимости от источника
        switch ($source_name) {
            case 'rospravosudie':
                $number = $xpath->query('.//span[@class="case-number"]', $element);
                if ($number->length > 0) {
                    $case['number'] = trim($number->item(0)->textContent);
                }
                break;
                
            case 'sudrf':
                $cells = $xpath->query('.//td', $element);
                if ($cells->length >= 3) {
                    $case['number'] = trim($cells->item(0)->textContent);
                    $case['date'] = trim($cells->item(1)->textContent);
                    $case['court'] = trim($cells->item(2)->textContent);
                }
                break;
        }
        
        return $case;
    }
    
    /**
     * Парсинг и анализ данных дел
     */
    private function parse_cases_data($data, $results) {
        if (is_array($data)) {
            foreach ($data as $case) {
                $results['total_cases']++;
                
                // Анализ статуса дела
                $status = $this->determine_case_status($case);
                if ($status === 'active') {
                    $results['active_cases']++;
                } else {
                    $results['completed_cases']++;
                }
                
                // Извлечение суммы иска
                $amount = $this->extract_case_amount($case);
                $results['total_amount'] += $amount;
                
                $results['cases'][] = array(
                    'number' => $case['number'] ?? '',
                    'date' => $case['date'] ?? '',
                    'court' => $case['court'] ?? '',
                    'participants' => $case['participants'] ?? '',
                    'status' => $status,
                    'amount' => $amount
                );
            }
        }
        
        // Создание сводки
        $results['summary'] = $this->create_summary($results);
        
        return $results;
    }
    
    /**
     * Определение статуса дела
     */
    private function determine_case_status($case) {
        $participants = strtolower($case['participants'] ?? '');
        $number = strtolower($case['number'] ?? '');
        
        // Ключевые слова для определения статуса
        $active_keywords = array('рассматривается', 'назначено', 'отложено', 'приостановлено');
        $completed_keywords = array('завершено', 'прекращено', 'решение', 'определение');
        
        foreach ($active_keywords as $keyword) {
            if (strpos($participants, $keyword) !== false || strpos($number, $keyword) !== false) {
                return 'active';
            }
        }
        
        foreach ($completed_keywords as $keyword) {
            if (strpos($participants, $keyword) !== false || strpos($number, $keyword) !== false) {
                return 'completed';
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Извлечение суммы иска
     */
    private function extract_case_amount($case) {
        $text = $case['participants'] ?? '';
        
        // Поиск сумм в тексте
        preg_match_all('/(\d{1,3}(?:\s\d{3})*(?:,\d{2})?)\s*руб/i', $text, $matches);
        
        if (!empty($matches[1])) {
            $amount = str_replace(array(' ', ','), array('', '.'), $matches[1][0]);
            return floatval($amount);
        }
        
        return 0;
    }
    
    /**
     * Создание сводки
     */
    private function create_summary($results) {
        $summary = array();
        
        if ($results['total_cases'] > 0) {
            $summary['risk_level'] = $this->calculate_risk_level($results);
            $summary['recommendation'] = $this->get_recommendation($results);
        } else {
            $summary['risk_level'] = 'low';
            $summary['recommendation'] = 'Судебных дел не найдено';
        }
        
        return $summary;
    }
    
    /**
     * Расчет уровня риска
     */
    private function calculate_risk_level($results) {
        $total = $results['total_cases'];
        $active = $results['active_cases'];
        $amount = $results['total_amount'];
        
        if ($total === 0) return 'low';
        if ($active > 5 || $amount > 1000000) return 'high';
        if ($active > 2 || $amount > 100000) return 'medium';
        return 'low';
    }
    
    /**
     * Получение рекомендации
     */
    private function get_recommendation($results) {
        $risk = $results['summary']['risk_level'];
        
        switch ($risk) {
            case 'high':
                return 'Высокий риск - много активных дел или крупные суммы';
            case 'medium':
                return 'Средний риск - есть судебные дела';
            case 'low':
                return 'Низкий риск - мало дел или они завершены';
            default:
                return 'Риск не определен';
        }
    }
    
    /**
     * Объединение данных из альтернативных источников
     */
    private function merge_alternative_data($results, $alternative_data) {
        foreach ($alternative_data as $source => $cases) {
            if (is_array($cases)) {
                $results['total_cases'] += count($cases);
                $results['cases'] = array_merge($results['cases'], $cases);
            }
        }
        
        // Пересчет сводки
        $results['summary'] = $this->create_summary($results);
        
        return $results;
    }
}

// Тестирование класса
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    echo "<h2>Тестирование ArbitrationAPI</h2>\n";
    
    $api = new ArbitrationAPI();
    $test_inn = '5260482041';
    
    echo "<p>Тестовый ИНН: {$test_inn}</p>\n";
    echo "<hr>\n";
    
    $results = $api->get_cases_by_inn($test_inn, 5);
    
    echo "<h3>Результаты поиска:</h3>\n";
    echo "<pre>" . json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>\n";
}
?>
