<?php
/**
 * Расширенная аналитика и улучшенные алгоритмы оценки
 * Company Rating Checker - Advanced Analytics
 */

if (!defined('ABSPATH')) { exit; }

class AdvancedAnalytics {
    
    private $risk_weights = array(
        'financial' => 0.3,
        'operational' => 0.25,
        'market' => 0.2,
        'regulatory' => 0.15,
        'reputation' => 0.1
    );
    
    private $sector_benchmarks = array();
    private $regional_benchmarks = array();
    
    public function __construct() {
        $this->initialize_benchmarks();
    }
    
    /**
     * Инициализация эталонных показателей
     */
    private function initialize_benchmarks() {
        // Эталонные показатели по отраслям
        $this->sector_benchmarks = array(
            '62' => array( // IT
                'avg_revenue' => 50000000,
                'avg_profitability' => 15,
                'avg_employee_count' => 25,
                'growth_rate' => 0.2,
                'risk_level' => 0.3
            ),
            '41' => array( // Строительство
                'avg_revenue' => 100000000,
                'avg_profitability' => 8,
                'avg_employee_count' => 50,
                'growth_rate' => 0.05,
                'risk_level' => 0.6
            ),
            '42' => array( // Инженерные работы
                'avg_revenue' => 150000000,
                'avg_profitability' => 12,
                'avg_employee_count' => 75,
                'growth_rate' => 0.08,
                'risk_level' => 0.5
            ),
            '10' => array( // Пищевая промышленность
                'avg_revenue' => 80000000,
                'avg_profitability' => 6,
                'avg_employee_count' => 100,
                'growth_rate' => 0.03,
                'risk_level' => 0.4
            ),
            '46' => array( // Оптовая торговля
                'avg_revenue' => 200000000,
                'avg_profitability' => 4,
                'avg_employee_count' => 30,
                'growth_rate' => 0.02,
                'risk_level' => 0.7
            )
        );
        
        // Эталонные показатели по регионам
        $this->regional_benchmarks = array(
            '77' => array( // Москва
                'economic_stability' => 0.9,
                'business_environment' => 0.85,
                'infrastructure' => 0.95,
                'market_size' => 0.9,
                'competition_level' => 0.8
            ),
            '78' => array( // Санкт-Петербург
                'economic_stability' => 0.85,
                'business_environment' => 0.8,
                'infrastructure' => 0.9,
                'market_size' => 0.7,
                'competition_level' => 0.7
            ),
            '52' => array( // Нижегородская область
                'economic_stability' => 0.7,
                'business_environment' => 0.65,
                'infrastructure' => 0.75,
                'market_size' => 0.6,
                'competition_level' => 0.5
            ),
            '66' => array( // Свердловская область
                'economic_stability' => 0.75,
                'business_environment' => 0.7,
                'infrastructure' => 0.8,
                'market_size' => 0.65,
                'competition_level' => 0.6
            )
        );
    }
    
    /**
     * Комплексный анализ компании
     */
    public function comprehensive_analysis($company_data) {
        $analysis = array(
            'financial_health' => $this->analyze_financial_health($company_data),
            'operational_efficiency' => $this->analyze_operational_efficiency($company_data),
            'market_position' => $this->analyze_market_position($company_data),
            'risk_assessment' => $this->comprehensive_risk_assessment($company_data),
            'growth_potential' => $this->analyze_growth_potential($company_data),
            'sustainability' => $this->analyze_sustainability($company_data),
            'overall_score' => 0,
            'recommendations' => array()
        );
        
        // Расчет общего балла
        $analysis['overall_score'] = $this->calculate_overall_score($analysis);
        
        // Генерация рекомендаций
        $analysis['recommendations'] = $this->generate_recommendations($analysis, $company_data);
        
        return $analysis;
    }
    
    /**
     * Анализ финансового здоровья
     */
    private function analyze_financial_health($data) {
        $score = 0;
        $factors = array();
        
        // Анализ выручки
        if (isset($data['fns']['revenue']) && $data['fns']['revenue'] > 0) {
            $revenue = $data['fns']['revenue'];
            $revenue_score = $this->evaluate_revenue($revenue, $data);
            $score += $revenue_score;
            $factors[] = "Выручка: " . number_format($revenue, 0, ',', ' ') . " руб. (балл: {$revenue_score})";
        }
        
        // Анализ прибыльности
        if (isset($data['fns']['profitability']) && $data['fns']['profitability'] > 0) {
            $profitability = $data['fns']['profitability'];
            $profitability_score = $this->evaluate_profitability($profitability, $data);
            $score += $profitability_score;
            $factors[] = "Рентабельность: " . round($profitability, 2) . "% (балл: {$profitability_score})";
        }
        
        // Анализ задолженности
        if (isset($data['fns']['debt_ratio'])) {
            $debt_ratio = $data['fns']['debt_ratio'];
            $debt_score = $this->evaluate_debt_ratio($debt_ratio);
            $score += $debt_score;
            $factors[] = "Доля задолженности: " . round($debt_ratio, 2) . "% (балл: {$debt_score})";
        }
        
        // Анализ ликвидности (эвристический)
        $liquidity_score = $this->estimate_liquidity($data);
        $score += $liquidity_score;
        $factors[] = "Оценочная ликвидность (балл: {$liquidity_score})";
        
        return array(
            'score' => min(25, $score),
            'max_score' => 25,
            'factors' => $factors,
            'level' => $this->get_score_level($score, 25)
        );
    }
    
    /**
     * Анализ операционной эффективности
     */
    private function analyze_operational_efficiency($data) {
        $score = 0;
        $factors = array();
        
        // Анализ размера и структуры
        if (isset($data['rosstat']['enterprise_size'])) {
            $size_data = $data['rosstat']['enterprise_size'];
            $size_score = $this->evaluate_enterprise_size($size_data);
            $score += $size_score;
            $factors[] = "Размер предприятия: " . $size_data['size_category'] . " (балл: {$size_score})";
        }
        
        // Анализ отраслевой эффективности
        if (isset($data['rosstat']['sector'])) {
            $sector_data = $data['rosstat']['sector'];
            $sector_score = $this->evaluate_sector_efficiency($sector_data);
            $score += $sector_score;
            $factors[] = "Отраслевая эффективность: " . $sector_data['sector_name'] . " (балл: {$sector_score})";
        }
        
        // Анализ региональной эффективности
        if (isset($data['rosstat']['region'])) {
            $region_data = $data['rosstat']['region'];
            $region_score = $this->evaluate_regional_efficiency($region_data);
            $score += $region_score;
            $factors[] = "Региональная эффективность: " . $region_data['region_name'] . " (балл: {$region_score})";
        }
        
        // Анализ стабильности занятости
        if (isset($data['rosstat']['employment']['employment_stability'])) {
            $stability = $data['rosstat']['employment']['employment_stability'];
            $stability_score = $stability * 5; // До 5 баллов
            $score += $stability_score;
            $factors[] = "Стабильность занятости: " . round($stability * 100, 1) . "% (балл: " . round($stability_score, 1) . ")";
        }
        
        return array(
            'score' => min(20, $score),
            'max_score' => 20,
            'factors' => $factors,
            'level' => $this->get_score_level($score, 20)
        );
    }
    
    /**
     * Анализ рыночной позиции
     */
    private function analyze_market_position($data) {
        $score = 0;
        $factors = array();
        
        // Анализ конкурентной позиции
        $competitive_score = $this->evaluate_competitive_position($data);
        $score += $competitive_score;
        $factors[] = "Конкурентная позиция (балл: {$competitive_score})";
        
        // Анализ рыночной доли (эвристический)
        $market_share_score = $this->estimate_market_share($data);
        $score += $market_share_score;
        $factors[] = "Оценочная рыночная доля (балл: {$market_share_score})";
        
        // Анализ барьеров входа
        $barriers_score = $this->evaluate_entry_barriers($data);
        $score += $barriers_score;
        $factors[] = "Барьеры входа в отрасль (балл: {$barriers_score})";
        
        return array(
            'score' => min(15, $score),
            'max_score' => 15,
            'factors' => $factors,
            'level' => $this->get_score_level($score, 15)
        );
    }
    
    /**
     * Комплексная оценка рисков
     */
    private function comprehensive_risk_assessment($data) {
        $risks = array(
            'financial_risk' => $this->assess_financial_risk($data),
            'operational_risk' => $this->assess_operational_risk($data),
            'market_risk' => $this->assess_market_risk($data),
            'regulatory_risk' => $this->assess_regulatory_risk($data),
            'reputation_risk' => $this->assess_reputation_risk($data)
        );
        
        // Взвешенная оценка общего риска
        $total_risk = 0;
        foreach ($risks as $risk_type => $risk_score) {
            $weight = $this->risk_weights[$risk_type] ?? 0.2;
            $total_risk += $risk_score * $weight;
        }
        
        return array(
            'total_risk' => $total_risk,
            'risk_level' => $this->get_risk_level($total_risk),
            'individual_risks' => $risks,
            'recommendations' => $this->get_risk_recommendations($risks)
        );
    }
    
    /**
     * Анализ потенциала роста
     */
    private function analyze_growth_potential($data) {
        $score = 0;
        $factors = array();
        
        // Анализ отраслевого роста
        if (isset($data['rosstat']['sector']['growth']['annual_growth'])) {
            $growth_rate = $data['rosstat']['sector']['growth']['annual_growth'];
            $growth_score = $this->evaluate_growth_rate($growth_rate);
            $score += $growth_score;
            $factors[] = "Отраслевой рост: " . round($growth_rate * 100, 1) . "% (балл: {$growth_score})";
        }
        
        // Анализ регионального потенциала
        if (isset($data['rosstat']['region']['economic']['gdp_growth'])) {
            $gdp_growth = $data['rosstat']['region']['economic']['gdp_growth'];
            $gdp_score = $this->evaluate_gdp_growth($gdp_growth);
            $score += $gdp_score;
            $factors[] = "Региональный рост ВВП: " . round($gdp_growth, 1) . "% (балл: {$gdp_score})";
        }
        
        // Анализ размера компании для роста
        $size_growth_score = $this->evaluate_size_growth_potential($data);
        $score += $size_growth_score;
        $factors[] = "Потенциал роста по размеру (балл: {$size_growth_score})";
        
        return array(
            'score' => min(10, $score),
            'max_score' => 10,
            'factors' => $factors,
            'level' => $this->get_score_level($score, 10)
        );
    }
    
    /**
     * Анализ устойчивости
     */
    private function analyze_sustainability($data) {
        $score = 0;
        $factors = array();
        
        // Анализ возраста компании
        $age_score = $this->evaluate_company_age($data);
        $score += $age_score;
        $factors[] = "Возраст компании (балл: {$age_score})";
        
        // Анализ стабильности руководства
        $management_score = $this->evaluate_management_stability($data);
        $score += $management_score;
        $factors[] = "Стабильность руководства (балл: {$management_score})";
        
        // Анализ диверсификации
        $diversification_score = $this->evaluate_diversification($data);
        $score += $diversification_score;
        $factors[] = "Диверсификация деятельности (балл: {$diversification_score})";
        
        return array(
            'score' => min(10, $score),
            'max_score' => 10,
            'factors' => $factors,
            'level' => $this->get_score_level($score, 10)
        );
    }
    
    /**
     * Вспомогательные методы оценки
     */
    private function evaluate_revenue($revenue, $data) {
        $okved = $this->extract_okved($data);
        $benchmark = $this->sector_benchmarks[$okved] ?? $this->sector_benchmarks['46'];
        
        $ratio = $revenue / $benchmark['avg_revenue'];
        
        if ($ratio >= 2) return 8; // Выше среднего в 2+ раза
        if ($ratio >= 1.5) return 6; // Выше среднего в 1.5+ раза
        if ($ratio >= 1) return 4; // На уровне среднего
        if ($ratio >= 0.5) return 2; // Ниже среднего
        return 1; // Значительно ниже среднего
    }
    
    private function evaluate_profitability($profitability, $data) {
        $okved = $this->extract_okved($data);
        $benchmark = $this->sector_benchmarks[$okved] ?? $this->sector_benchmarks['46'];
        
        $ratio = $profitability / $benchmark['avg_profitability'];
        
        if ($ratio >= 2) return 7; // Выше среднего в 2+ раза
        if ($ratio >= 1.5) return 5; // Выше среднего в 1.5+ раза
        if ($ratio >= 1) return 3; // На уровне среднего
        if ($ratio >= 0.5) return 1; // Ниже среднего
        return 0; // Значительно ниже среднего
    }
    
    private function evaluate_debt_ratio($debt_ratio) {
        if ($debt_ratio < 10) return 5; // Очень низкая задолженность
        if ($debt_ratio < 30) return 4; // Низкая задолженность
        if ($debt_ratio < 50) return 2; // Средняя задолженность
        if ($debt_ratio < 70) return 1; // Высокая задолженность
        return 0; // Очень высокая задолженность
    }
    
    private function estimate_liquidity($data) {
        // Эвристическая оценка ликвидности
        $score = 3; // Базовый балл
        
        if (isset($data['fns']['profitability']) && $data['fns']['profitability'] > 10) {
            $score += 2; // Высокая прибыльность улучшает ликвидность
        }
        
        if (isset($data['fns']['debt_ratio']) && $data['fns']['debt_ratio'] < 30) {
            $score += 2; // Низкая задолженность улучшает ликвидность
        }
        
        if (isset($data['rosstat']['enterprise_size']['size_category'])) {
            $size = $data['rosstat']['enterprise_size']['size_category'];
            if (in_array($size, array('large', 'medium'))) {
                $score += 1; // Крупные компании обычно более ликвидны
            }
        }
        
        return min(5, $score);
    }
    
    private function evaluate_enterprise_size($size_data) {
        $size = $size_data['size_category'];
        $employees = $size_data['estimated_employees'] ?? 0;
        
        switch ($size) {
            case 'large':
                return 8; // Крупные компании более эффективны
            case 'medium':
                return 6; // Средние компании
            case 'small':
                return 4; // Малые компании
            case 'micro':
                return 2; // Микропредприятия
            default:
                return 3;
        }
    }
    
    private function evaluate_sector_efficiency($sector_data) {
        $rating = $sector_data['sector_rating'] ?? 0.5;
        return $rating * 5; // До 5 баллов
    }
    
    private function evaluate_regional_efficiency($region_data) {
        $rating = $region_data['statistical_rating'] ?? 0.5;
        return $rating * 3; // До 3 баллов
    }
    
    private function evaluate_competitive_position($data) {
        $score = 5; // Базовый балл
        
        // Анализ размера относительно конкурентов
        if (isset($data['rosstat']['enterprise_size']['size_category'])) {
            $size = $data['rosstat']['enterprise_size']['size_category'];
            if ($size === 'large') $score += 3;
            elseif ($size === 'medium') $score += 2;
            elseif ($size === 'small') $score += 1;
        }
        
        // Анализ отраслевой позиции
        if (isset($data['rosstat']['sector']['sector_rating'])) {
            $sector_rating = $data['rosstat']['sector']['sector_rating'];
            $score += $sector_rating * 2;
        }
        
        return min(10, $score);
    }
    
    private function estimate_market_share($data) {
        // Эвристическая оценка рыночной доли
        $score = 2; // Базовый балл
        
        if (isset($data['rosstat']['enterprise_size']['size_category'])) {
            $size = $data['rosstat']['enterprise_size']['size_category'];
            if ($size === 'large') $score += 3;
            elseif ($size === 'medium') $score += 2;
            elseif ($size === 'small') $score += 1;
        }
        
        return min(5, $score);
    }
    
    private function evaluate_entry_barriers($data) {
        // Оценка барьеров входа в отрасль
        if (isset($data['rosstat']['sector']['market']['barriers_to_entry'])) {
            $barriers = $data['rosstat']['sector']['market']['barriers_to_entry'];
            return $barriers * 5; // До 5 баллов
        }
        
        return 2.5; // Средний уровень барьеров
    }
    
    private function assess_financial_risk($data) {
        $risk = 0.5; // Базовый риск
        
        if (isset($data['fns']['debt_ratio'])) {
            $debt_ratio = $data['fns']['debt_ratio'];
            if ($debt_ratio > 70) $risk += 0.3;
            elseif ($debt_ratio > 50) $risk += 0.2;
            elseif ($debt_ratio < 20) $risk -= 0.2;
        }
        
        if (isset($data['fns']['profitability'])) {
            $profitability = $data['fns']['profitability'];
            if ($profitability < 0) $risk += 0.4;
            elseif ($profitability < 5) $risk += 0.2;
            elseif ($profitability > 15) $risk -= 0.2;
        }
        
        return max(0, min(1, $risk));
    }
    
    private function assess_operational_risk($data) {
        $risk = 0.5; // Базовый риск
        
        if (isset($data['rosstat']['employment']['employment_stability'])) {
            $stability = $data['rosstat']['employment']['employment_stability'];
            $risk += (1 - $stability) * 0.3;
        }
        
        if (isset($data['rosstat']['enterprise_size']['size_category'])) {
            $size = $data['rosstat']['enterprise_size']['size_category'];
            if ($size === 'micro') $risk += 0.2;
            elseif ($size === 'small') $risk += 0.1;
        }
        
        return max(0, min(1, $risk));
    }
    
    private function assess_market_risk($data) {
        $risk = 0.5; // Базовый риск
        
        if (isset($data['rosstat']['sector']['market']['competition_level'])) {
            $competition = $data['rosstat']['sector']['market']['competition_level'];
            $risk += $competition * 0.3;
        }
        
        if (isset($data['rosstat']['sector']['growth']['annual_growth'])) {
            $growth = $data['rosstat']['sector']['growth']['annual_growth'];
            if ($growth < 0.05) $risk += 0.2; // Низкий рост увеличивает риск
        }
        
        return max(0, min(1, $risk));
    }
    
    private function assess_regulatory_risk($data) {
        $risk = 0.3; // Базовый риск
        
        // Анализ отраслевого регулирования
        if (isset($data['rosstat']['sector']['sector_name'])) {
            $sector = $data['rosstat']['sector']['sector_name'];
            if (strpos($sector, 'финансов') !== false || strpos($sector, 'банк') !== false) {
                $risk += 0.3; // Финансовый сектор более регулируемый
            }
        }
        
        return max(0, min(1, $risk));
    }
    
    private function assess_reputation_risk($data) {
        $risk = 0.3; // Базовый риск
        
        // Анализ арбитражных рисков
        if (isset($data['arbitration']['risk_level'])) {
            $arbitration_risk = $data['arbitration']['risk_level'];
            if ($arbitration_risk === 'high') $risk += 0.4;
            elseif ($arbitration_risk === 'medium') $risk += 0.2;
        }
        
        // Анализ репутации в закупках
        if (isset($data['zakupki']['summary']['reputation_level'])) {
            $zakupki_reputation = $data['zakupki']['summary']['reputation_level'];
            if (in_array($zakupki_reputation, array('poor', 'very_poor'))) {
                $risk += 0.3;
            }
        }
        
        return max(0, min(1, $risk));
    }
    
    private function get_risk_level($total_risk) {
        if ($total_risk <= 0.3) return 'low';
        if ($total_risk <= 0.6) return 'medium';
        if ($total_risk <= 0.8) return 'high';
        return 'very_high';
    }
    
    private function get_risk_recommendations($risks) {
        $recommendations = array();
        
        foreach ($risks as $risk_type => $risk_score) {
            if ($risk_score > 0.7) {
                switch ($risk_type) {
                    case 'financial_risk':
                        $recommendations[] = 'Рекомендуется улучшить финансовые показатели и снизить задолженность';
                        break;
                    case 'operational_risk':
                        $recommendations[] = 'Рекомендуется повысить операционную эффективность';
                        break;
                    case 'market_risk':
                        $recommendations[] = 'Рекомендуется диверсифицировать деятельность';
                        break;
                    case 'regulatory_risk':
                        $recommendations[] = 'Рекомендуется усилить соответствие регулятивным требованиям';
                        break;
                    case 'reputation_risk':
                        $recommendations[] = 'Рекомендуется улучшить репутацию и снизить судебные риски';
                        break;
                }
            }
        }
        
        return $recommendations;
    }
    
    private function evaluate_growth_rate($growth_rate) {
        if ($growth_rate > 0.15) return 4; // Высокий рост
        if ($growth_rate > 0.08) return 3; // Средний рост
        if ($growth_rate > 0.03) return 2; // Низкий рост
        return 1; // Отрицательный или нулевой рост
    }
    
    private function evaluate_gdp_growth($gdp_growth) {
        if ($gdp_growth > 3) return 3; // Высокий рост ВВП
        if ($gdp_growth > 2) return 2; // Средний рост ВВП
        if ($gdp_growth > 1) return 1; // Низкий рост ВВП
        return 0; // Отрицательный рост ВВП
    }
    
    private function evaluate_size_growth_potential($data) {
        if (isset($data['rosstat']['enterprise_size']['size_category'])) {
            $size = $data['rosstat']['enterprise_size']['size_category'];
            switch ($size) {
                case 'micro':
                    return 3; // Высокий потенциал роста
                case 'small':
                    return 2; // Средний потенциал роста
                case 'medium':
                    return 1; // Низкий потенциал роста
                case 'large':
                    return 0; // Минимальный потенциал роста
            }
        }
        return 1; // Базовый потенциал
    }
    
    private function evaluate_company_age($data) {
        // Анализ возраста компании
        if (isset($data['state']['registration_date'])) {
            $reg_date = $data['state']['registration_date'];
            $age = (time() - $reg_date / 1000) / (365 * 24 * 3600); // Возраст в годах
            
            if ($age > 10) return 4; // Старые компании более устойчивы
            if ($age > 5) return 3; // Зрелые компании
            if ($age > 2) return 2; // Молодые компании
            return 1; // Новые компании
        }
        
        return 2; // Базовый балл
    }
    
    private function evaluate_management_stability($data) {
        // Анализ стабильности руководства
        if (isset($data['management']['start_date'])) {
            $start_date = $data['management']['start_date'];
            $tenure = (time() - $start_date / 1000) / (365 * 24 * 3600); // Стаж в годах
            
            if ($tenure > 5) return 3; // Долгий стаж
            if ($tenure > 2) return 2; // Средний стаж
            return 1; // Короткий стаж
        }
        
        return 2; // Базовый балл
    }
    
    private function evaluate_diversification($data) {
        // Анализ диверсификации (эвристический)
        $score = 2; // Базовый балл
        
        // Анализ по отраслям
        if (isset($data['rosstat']['sector']['sector_name'])) {
            $sector = $data['rosstat']['sector']['sector_name'];
            if (strpos($sector, 'IT') !== false || strpos($sector, 'технологи') !== false) {
                $score += 1; // IT-сектор более диверсифицирован
            }
        }
        
        return min(5, $score);
    }
    
    private function calculate_overall_score($analysis) {
        $weights = array(
            'financial_health' => 0.3,
            'operational_efficiency' => 0.25,
            'market_position' => 0.2,
            'growth_potential' => 0.15,
            'sustainability' => 0.1
        );
        
        $total_score = 0;
        $total_max = 0;
        
        foreach ($weights as $category => $weight) {
            if (isset($analysis[$category])) {
                $category_data = $analysis[$category];
                $total_score += $category_data['score'] * $weight;
                $total_max += $category_data['max_score'] * $weight;
            }
        }
        
        return $total_max > 0 ? ($total_score / $total_max) * 100 : 0;
    }
    
    private function generate_recommendations($analysis, $company_data) {
        $recommendations = array();
        
        // Рекомендации по финансовому здоровью
        if ($analysis['financial_health']['level'] === 'low') {
            $recommendations[] = 'Улучшить финансовые показатели: увеличить прибыльность и снизить задолженность';
        }
        
        // Рекомендации по операционной эффективности
        if ($analysis['operational_efficiency']['level'] === 'low') {
            $recommendations[] = 'Повысить операционную эффективность: оптимизировать процессы и структуру';
        }
        
        // Рекомендации по рыночной позиции
        if ($analysis['market_position']['level'] === 'low') {
            $recommendations[] = 'Укрепить рыночную позицию: развивать конкурентные преимущества';
        }
        
        // Рекомендации по росту
        if ($analysis['growth_potential']['level'] === 'low') {
            $recommendations[] = 'Развивать потенциал роста: искать новые рынки и возможности';
        }
        
        // Рекомендации по устойчивости
        if ($analysis['sustainability']['level'] === 'low') {
            $recommendations[] = 'Повысить устойчивость: диверсифицировать деятельность и укрепить команду';
        }
        
        // Рекомендации по рискам
        if (isset($analysis['risk_assessment']['recommendations'])) {
            $recommendations = array_merge($recommendations, $analysis['risk_assessment']['recommendations']);
        }
        
        return array_unique($recommendations);
    }
    
    private function get_score_level($score, $max_score) {
        $percentage = ($score / $max_score) * 100;
        
        if ($percentage >= 80) return 'excellent';
        if ($percentage >= 60) return 'good';
        if ($percentage >= 40) return 'average';
        if ($percentage >= 20) return 'poor';
        return 'very_poor';
    }
    
    private function extract_okved($data) {
        // Извлечение ОКВЭД из данных
        if (isset($data['okved'])) {
            return substr($data['okved'], 0, 2);
        }
        
        if (isset($data['rosstat']['sector']['okved_prefix'])) {
            return str_pad($data['rosstat']['sector']['okved_prefix'], 2, '0', STR_PAD_LEFT);
        }
        
        return '46'; // По умолчанию - торговля
    }
}
?>
