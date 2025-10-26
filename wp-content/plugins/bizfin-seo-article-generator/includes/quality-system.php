<?php
/**
 * BizFin SEO Article Generator - Quality System
 * Система качества на основе архитектуры ALwrity
 */

if (!defined('ABSPATH')) exit;

class BizFin_Quality_System {
    
    private $quality_thresholds = [
        'factual_accuracy' => 0.85,
        'professional_tone' => 0.80,
        'industry_relevance' => 0.90,
        'seo_optimization' => 0.85,
        'content_uniqueness' => 0.95,
        'global_tone_style' => 0.90, // НОВЫЙ критерий
        // Живой тон/сторителлинг: примеры, человеческий язык, нарратив
        'human_storytelling' => 0.85,
        // БЕЗУСЛОВНОЕ ПРАВИЛО: Длина статьи
        'content_length' => 1.0, // 1.0 = 100% при достижении минимума 2500 слов
        'overall_score' => 0.85
    ];
    
    private $professional_indicators = [
        'гарантия', 'банк', 'тендер', 'закупка', 'контракт', 'документы',
        'процесс', 'требования', 'сроки', 'стоимость', 'риски', 'анализ',
        'рекомендации', 'опыт', 'практика', 'кейс', 'решение'
    ];
    
    private $unprofessional_indicators = [
        'круто', 'прикольно', 'вау', 'супер', 'классно', 'отлично',
        'здорово', 'прекрасно', 'замечательно', 'чудесно', 'фантастически'
    ];
    
    private $banking_terminology = [
        'банковская гарантия', 'тендерная гарантия', 'гарантия исполнения',
        'гарантия возврата аванса', 'гарантия предложения', 'покрытие',
        'обеспечение', 'гарантийное обязательство', 'принципал', 'бенефициар',
        'гарант', 'безотзывная гарантия', 'условная гарантия', 'безусловная гарантия'
    ];
    
    // НОВОЕ: Индикаторы глобального тона и стиля
    private $global_tone_indicators = [
        'friendly_professional' => [
            'вам', 'ваш', 'ваше', 'для вас', 'вашему бизнесу', 'вашему проекту',
            'поможет', 'упростит', 'пригодится', 'важно', 'нужно', 'стоит'
        ],
        'active_voice' => [
            'сделали', 'получили', 'оформили', 'выдали', 'зарегистрировали',
            'анализируем', 'рекомендуем', 'предлагаем', 'помогаем'
        ],
        'concrete_examples' => [
            'например', 'к примеру', 'представьте', 'допустим', 'скажем',
            'в случае', 'при условии', 'если', 'когда'
        ],
        'reader_focus' => [
            'вам необходимо', 'вы можете', 'вам нужно', 'вам стоит', 'вам важно',
            'для вашего', 'в вашем случае', 'в вашей ситуации'
        ],
        // Маркеры сторителлинга/человеческого языка
        'storytelling_markers' => [
            'история', 'кейс', 'ситуация', 'реальный пример', 'на практике',
            'мы', 'наш', 'я', 'когда-то', 'вчера', 'сегодня', 'в прошлом году',
            '«', '»', '—'
        ]
    ];
    
    private $bureaucratic_language = [
        'осуществляется', 'предусматривается', 'в соответствии с', 'в целях',
        'является', 'представляет собой', 'имеет место', 'находится в состоянии',
        'направлено на', 'способствует', 'обеспечивает', 'содействует'
    ];
    
    private $target_audience_keywords = [
        'руководитель', 'менеджер', 'директор', 'бизнес', 'проект', 'компания',
        'организация', 'предприятие', 'фирма', 'закупки', 'тендеры', 'контракты'
    ];
    
    public function __construct() {
        // Инициализация системы качества
        add_action('bsag_article_generated', [$this, 'run_quality_analysis'], 10, 2);
        add_action('bsag_content_optimization', [$this, 'optimize_content_quality'], 10, 2);
    }
    
    /**
     * Запуск полного анализа качества статьи
     */
    public function run_quality_analysis($post_id, $article_data) {
        $content = $article_data['content'];
        $keyword = $article_data['keyword'];
        
        // Параллельный анализ качества (как в ALwrity)
        $analysis_results = [
            'factual_accuracy' => $this->assess_factual_accuracy($content, $keyword),
            'professional_tone' => $this->assess_professional_tone($content),
            'industry_relevance' => $this->assess_industry_relevance($content, 'banking'),
            'seo_optimization' => $this->assess_seo_optimization($content, $keyword),
            'content_uniqueness' => $this->assess_content_uniqueness($content, $keyword),
            'global_tone_style' => $this->assess_global_tone_style($content, $article_data), // НОВЫЙ критерий
            'content_length' => $this->assess_content_length($content), // БЕЗУСЛОВНОЕ ПРАВИЛО
            'readability_score' => $this->assess_readability($content),
            'keyword_density' => $this->calculate_keyword_density($content, $keyword),
            // Новый показатель живого тона/сторителлинга
            'human_storytelling' => $this->assess_human_storytelling($content)
        ];
        
        // Расчет общего балла качества
        $overall_score = $this->calculate_overall_quality_score($analysis_results);
        
        // Проверка качества через Quality Gates
        $quality_gates_result = $this->validate_quality_gates($analysis_results, $overall_score);
        
        // Генерация рекомендаций
        $recommendations = $this->generate_quality_recommendations($analysis_results, $quality_gates_result);
        
        // Сохранение результатов анализа
        $this->save_quality_analysis($post_id, [
            'analysis_results' => $analysis_results,
            'overall_score' => $overall_score,
            'quality_gates_result' => $quality_gates_result,
            'recommendations' => $recommendations,
            'analysis_timestamp' => current_time('mysql')
        ]);
        
        return [
            'overall_score' => $overall_score,
            'analysis_results' => $analysis_results,
            'quality_gates_passed' => $quality_gates_result['passed'],
            'recommendations' => $recommendations
        ];
    }
    
    /**
     * Оценка фактической точности (на основе ALwrity)
     */
    private function assess_factual_accuracy($content, $keyword) {
        $score = 0.0;
        $max_score = 1.0;
        
        // Проверка наличия ключевых терминов банковских гарантий
        $terminology_score = 0.0;
        foreach ($this->banking_terminology as $term) {
            if (stripos($content, $term) !== false) {
                $terminology_score += 0.1;
            }
        }
        $terminology_score = min($terminology_score, 0.4);
        
        // Проверка структуры статьи
        $structure_score = 0.0;
        if (stripos($content, '<h1>') !== false) $structure_score += 0.1;
        if (stripos($content, '<h2>') !== false) $structure_score += 0.1;
        if (stripos($content, '<p>') !== false) $structure_score += 0.1;
        if (stripos($content, '<ul>') !== false || stripos($content, '<ol>') !== false) $structure_score += 0.1;
        
        // Проверка длины контента
        $length_score = 0.0;
        $word_count = str_word_count(strip_tags($content));
        if ($word_count >= 1500 && $word_count <= 3000) {
            $length_score = 0.2;
        } elseif ($word_count >= 1000) {
            $length_score = 0.1;
        }
        
        $score = $terminology_score + $structure_score + $length_score;
        
        return min($score, $max_score);
    }
    
    /**
     * Оценка профессионального тона
     */
    private function assess_professional_tone($content) {
        $score = 0.0;
        $max_score = 1.0;
        
        $content_lower = mb_strtolower($content, 'UTF-8');
        
        // Подсчет профессиональных индикаторов
        $professional_count = 0;
        foreach ($this->professional_indicators as $indicator) {
            $professional_count += substr_count($content_lower, $indicator);
        }
        
        // Подсчет непрофессиональных индикаторов
        $unprofessional_count = 0;
        foreach ($this->unprofessional_indicators as $indicator) {
            $unprofessional_count += substr_count($content_lower, $indicator);
        }
        
        // Расчет балла
        $total_words = str_word_count($content);
        $professional_ratio = $professional_count / max($total_words, 1);
        $unprofessional_ratio = $unprofessional_count / max($total_words, 1);
        
        $score = min($professional_ratio * 10, 0.7) - min($unprofessional_ratio * 20, 0.3);
        
        return max(0, min($score, $max_score));
    }
    
    /**
     * Оценка релевантности отрасли
     */
    private function assess_industry_relevance($content, $industry) {
        $score = 0.0;
        $max_score = 1.0;
        
        $content_lower = mb_strtolower($content, 'UTF-8');
        
        // Проверка релевантных терминов для банковских гарантий
        $relevant_terms = [
            'банковская гарантия', 'тендер', 'закупка', 'контракт', 'государственные закупки',
            '44-фз', '223-фз', '275-фз', 'еис', 'заказчик', 'поставщик', 'подрядчик'
        ];
        
        $relevance_count = 0;
        foreach ($relevant_terms as $term) {
            if (stripos($content_lower, $term) !== false) {
                $relevance_count++;
            }
        }
        
        $score = min($relevance_count / count($relevant_terms), $max_score);
        
        return $score;
    }
    
    /**
     * Оценка SEO оптимизации
     */
    private function assess_seo_optimization($content, $keyword) {
        $score = 0.0;
        $max_score = 1.0;
        
        // Проверка наличия ключевого слова в заголовках
        $keyword_in_headings = 0.0;
        if (stripos($content, '<h1>' . $keyword) !== false || stripos($content, $keyword . '</h1>') !== false) {
            $keyword_in_headings += 0.3;
        }
        if (stripos($content, '<h2>') !== false) {
            $keyword_in_headings += 0.2;
        }
        
        // Проверка плотности ключевых слов
        $keyword_density = $this->calculate_keyword_density($content, $keyword);
        $density_score = 0.0;
        // Оптимальный коридор для RU: ~0.8–1.8%
        if ($keyword_density >= 0.8 && $keyword_density <= 1.8) {
            $density_score = 0.3;
        } elseif ($keyword_density > 0 && $keyword_density < 2.5) {
            $density_score = 0.2;
        }
        
        // Проверка внутренних ссылок
        $internal_links_score = 0.0;
        $link_count = substr_count($content, '<a href=');
        if ($link_count >= 3 && $link_count <= 7) {
            $internal_links_score = 0.2;
        }
        
        $score = $keyword_in_headings + $density_score + $internal_links_score;
        
        return min($score, $max_score);
    }
    
    /**
     * НОВОЕ: Оценка глобального тона и стиля
     */
    public function assess_global_tone_style($content, $keyword_data = []) {
        $tone_score = 0;
        $total_checks = 0;
        
        // 1. Проверка дружелюбного профессионализма
        $friendly_professional_score = $this->check_friendly_professional_tone($content);
        $tone_score += $friendly_professional_score * 25;
        $total_checks += 25;
        
        // 2. Проверка активного залога
        $active_voice_score = $this->check_active_voice($content);
        $tone_score += $active_voice_score * 20;
        $total_checks += 20;
        
        // 3. Проверка обращений к читателю
        $reader_focus_score = $this->check_reader_focus($content);
        $tone_score += $reader_focus_score * 20;
        $total_checks += 20;
        
        // 4. Проверка конкретных примеров
        $concrete_examples_score = $this->check_concrete_examples($content);
        $tone_score += $concrete_examples_score * 15;
        $total_checks += 15;
        
        // 5. Проверка отсутствия канцелярита
        $no_bureaucratic_score = $this->check_no_bureaucratic_language($content);
        $tone_score += $no_bureaucratic_score * 20;
        $total_checks += 20;
        
        return $total_checks > 0 ? $tone_score / $total_checks : 0;
    }

    /**
     * Оценка «живого тона» и сторителлинга
     * Учитывает: наличие примеров/историй, нарративные местоимения,
     * временные маркеры, диалоговые символы, микро‑пример в секциях.
     */
    private function assess_human_storytelling($content) {
        $score = 0.0;
        $max_score = 1.0;

        $lower = mb_strtolower($content, 'UTF-8');

        // 1) Наличие явных примеров/историй
        $example_markers = ['пример:', 'история', 'кейс', 'ситуация', 'реальный пример'];
        $examples_count = 0;
        foreach ($example_markers as $m) {
            $examples_count += substr_count($lower, $m);
        }
        $examples_score = min($examples_count / 3, 1); // ожидаем >=3 примеров

        // 2) Нарративные местоимения (человеческий голос)
        $narrative_words = ['мы', 'наш', 'я'];
        $narrative_count = 0;
        foreach ($narrative_words as $w) {
            $narrative_count += substr_count($lower, $w);
        }
        $narrative_score = min($narrative_count / 5, 1); // ожидаем хотя бы 5 в длинной статье

        // 3) Временные маркеры/контекст
        $time_markers = ['вчера', 'сегодня', 'когда-то', 'в прошлом году', 'в 202'];
        $time_count = 0;
        foreach ($time_markers as $t) {
            $time_count += substr_count($lower, $t);
        }
        $time_score = min($time_count / 2, 1); // >=2 упоминаний

        // 4) Диалоговые/разговорные символы
        $dialogue_count = substr_count($content, '«') + substr_count($content, '»') + substr_count($content, '—');
        $dialogue_score = min($dialogue_count / 6, 1); // несколько реплик/цитат

        // 5) Микро‑пример на секцию (эвристика: соотношение "Пример:" к числу H2)
        $h2_count = substr_count(mb_strtolower($content, 'UTF-8'), '<h2');
        $micro_examples_ratio = $h2_count > 0 ? min(($examples_count / max($h2_count, 1)), 1) : ($examples_count > 0 ? 1 : 0);

        // Агрегация с весами
        $score = (
            $examples_score * 0.35 +
            $narrative_score * 0.20 +
            $time_score * 0.10 +
            $dialogue_score * 0.10 +
            $micro_examples_ratio * 0.25
        );

        return max(0, min($score, $max_score));
    }
    
    /**
     * Проверка дружелюбного профессионализма
     */
    public function check_friendly_professional_tone($content) {
        $score = 0;
        $total_indicators = 0;
        
        foreach ($this->global_tone_indicators['friendly_professional'] as $indicator) {
            $count = substr_count(strtolower($content), $indicator);
            if ($count > 0) {
                $score += min($count, 3); // Максимум 3 балла за индикатор
            }
            $total_indicators++;
        }
        
        // Нормализуем к 0-1
        $max_possible_score = $total_indicators * 3;
        return $max_possible_score > 0 ? min($score / $max_possible_score, 1) : 0;
    }
    
    /**
     * Проверка активного залога
     */
    private function check_active_voice($content) {
        $active_count = 0;
        $total_verbs = 0;
        
        foreach ($this->global_tone_indicators['active_voice'] as $active_verb) {
            $count = substr_count(strtolower($content), $active_verb);
            $active_count += $count;
        }
        
        // Подсчитываем общее количество глаголов (приблизительно)
        $total_verbs = preg_match_all('/\b(дела|получ|оформ|выда|регистр|анализ|рекоменд|предлаг|помог)\w*\b/u', $content);
        
        return $total_verbs > 0 ? min($active_count / $total_verbs, 1) : 0;
    }
    
    /**
     * Проверка фокуса на читателе
     */
    private function check_reader_focus($content) {
        $reader_focus_count = 0;
        
        foreach ($this->global_tone_indicators['reader_focus'] as $focus_phrase) {
            $count = substr_count(strtolower($content), $focus_phrase);
            $reader_focus_count += $count;
        }
        
        // Нормализуем к 0-1 (ожидаем минимум 5 обращений к читателю)
        return min($reader_focus_count / 5, 1);
    }
    
    /**
     * Проверка конкретных примеров
     */
    private function check_concrete_examples($content) {
        $examples_count = 0;
        
        foreach ($this->global_tone_indicators['concrete_examples'] as $example_word) {
            $count = substr_count(strtolower($content), $example_word);
            $examples_count += $count;
        }
        
        // Нормализуем к 0-1 (ожидаем минимум 3 примера)
        return min($examples_count / 3, 1);
    }
    
    /**
     * Проверка отсутствия канцелярского языка
     */
    private function check_no_bureaucratic_language($content) {
        $bureaucratic_count = 0;
        
        foreach ($this->bureaucratic_language as $bureaucratic_word) {
            $count = substr_count(strtolower($content), $bureaucratic_word);
            $bureaucratic_count += $count;
        }
        
        // Нормализуем к 0-1 (чем меньше канцелярита, тем лучше)
        // Если канцелярита нет (0), возвращаем 1
        // Если канцелярита много (>=5), возвращаем 0
        if ($bureaucratic_count == 0) {
            return 1;
        }
        
        return max(0, 1 - ($bureaucratic_count / 5));
    }
    
    /**
     * Оценка уникальности контента
     */
    private function assess_content_uniqueness($content, $keyword) {
        // Простая проверка уникальности на основе длины и разнообразия
        $word_count = str_word_count($content);
        $unique_words = count(array_unique(str_word_count($content, 1)));
        $uniqueness_ratio = $unique_words / max($word_count, 1);
        
        // Базовый балл за уникальность
        $base_score = min($uniqueness_ratio * 2, 0.8);
        
        // Дополнительные баллы за структуру
        $structure_bonus = 0.0;
        if (stripos($content, '<h2>') !== false) $structure_bonus += 0.1;
        if (stripos($content, '<ul>') !== false) $structure_bonus += 0.05;
        if (stripos($content, '<ol>') !== false) $structure_bonus += 0.05;
        
        return min($base_score + $structure_bonus, 1.0);
    }
    
    /**
     * Оценка читаемости (упрощенная версия)
     */
    private function assess_readability($content) {
        $sentences = preg_split('/[.!?]+/', strip_tags($content));
        $words = str_word_count(strip_tags($content));
        $syllables = $this->estimate_syllables($content);
        
        $avg_sentence_length = $words / max(count($sentences), 1);
        $avg_syllables_per_word = $syllables / max($words, 1);
        
        // Упрощенная формула читаемости
        $readability_score = 206.835 - (1.015 * $avg_sentence_length) - (84.6 * $avg_syllables_per_word);
        
        // Нормализация к шкале 0-1
        return max(0, min(($readability_score - 0) / 100, 1));
    }
    
    /**
     * Расчет плотности ключевых слов
     */
    private function calculate_keyword_density($content, $keyword) {
        $content_lower = mb_strtolower(strip_tags($content), 'UTF-8');
        $keyword_lower = mb_strtolower($keyword, 'UTF-8');
        
        $total_words = str_word_count($content_lower);
        $keyword_count = substr_count($content_lower, $keyword_lower);
        
        return ($keyword_count / max($total_words, 1)) * 100;
    }
    
    /**
     * Оценка количества слогов (упрощенная)
     */
    private function estimate_syllables($content) {
        $words = str_word_count(strip_tags($content), 1);
        $total_syllables = 0;
        
        foreach ($words as $word) {
            // Простая оценка: количество гласных + 1
            $vowels = preg_match_all('/[аеёиоуыэюя]/iu', $word);
            $total_syllables += max($vowels, 1);
        }
        
        return $total_syllables;
    }
    
    /**
     * Расчет общего балла качества
     */
    private function calculate_overall_quality_score($analysis_results) {
        $weights = [
            'factual_accuracy' => 0.15,
            'professional_tone' => 0.10,
            'industry_relevance' => 0.10,
            'seo_optimization' => 0.15,
            'content_uniqueness' => 0.08,
            'global_tone_style' => 0.15,
            'human_storytelling' => 0.12,
            'content_length' => 0.15 // БЕЗУСЛОВНОЕ ПРАВИЛО: 15% веса для длины статьи
        ];
        
        $weighted_score = 0.0;
        foreach ($weights as $metric => $weight) {
            if (isset($analysis_results[$metric])) {
                $weighted_score += $analysis_results[$metric] * $weight;
            }
        }
        
        return round($weighted_score, 3);
    }
    
    /**
     * Валидация через Quality Gates
     */
    private function validate_quality_gates($analysis_results, $overall_score) {
        $gates_passed = 0;
        $total_gates = count($this->quality_thresholds);
        
        foreach ($this->quality_thresholds as $gate => $threshold) {
            if ($gate === 'overall_score') {
                if ($overall_score >= $threshold) {
                    $gates_passed++;
                }
            } else {
                if (isset($analysis_results[$gate]) && $analysis_results[$gate] >= $threshold) {
                    $gates_passed++;
                }
            }
        }
        
        return [
            'passed' => $gates_passed,
            'total' => $total_gates,
            'percentage' => ($gates_passed / $total_gates) * 100,
            'all_passed' => $gates_passed === $total_gates
        ];
    }
    
    /**
     * Генерация рекомендаций по улучшению качества
     */
    private function generate_quality_recommendations($analysis_results, $quality_gates_result) {
        $recommendations = [];
        
        // Рекомендации по фактической точности
        if ($analysis_results['factual_accuracy'] < $this->quality_thresholds['factual_accuracy']) {
            $recommendations[] = [
                'category' => 'factual_accuracy',
                'priority' => 'high',
                'message' => 'Увеличьте количество профессиональных терминов и улучшите структуру статьи',
                'action' => 'Добавьте больше банковской терминологии и четкую структуру H1-H2-H3'
            ];
        }
        
        // Рекомендации по профессиональному тону
        if ($analysis_results['professional_tone'] < $this->quality_thresholds['professional_tone']) {
            $recommendations[] = [
                'category' => 'professional_tone',
                'priority' => 'high',
                'message' => 'Улучшите профессиональный тон статьи',
                'action' => 'Используйте больше профессиональной терминологии, избегайте разговорных выражений'
            ];
        }
        
        // Рекомендации по SEO
        if ($analysis_results['seo_optimization'] < $this->quality_thresholds['seo_optimization']) {
            $recommendations[] = [
                'category' => 'seo_optimization',
                'priority' => 'medium',
                'message' => 'Оптимизируйте SEO параметры',
                'action' => 'Проверьте плотность ключевых слов, добавьте внутренние ссылки'
            ];
        }
        
        // Рекомендации по уникальности
        if ($analysis_results['content_uniqueness'] < $this->quality_thresholds['content_uniqueness']) {
            $recommendations[] = [
                'category' => 'content_uniqueness',
                'priority' => 'high',
                'message' => 'Улучшите уникальность контента',
                'action' => 'Добавьте уникальные примеры, кейсы, личный опыт'
            ];
        }
        
        // НОВОЕ: Рекомендации по глобальному тону и стилю
        if ($analysis_results['global_tone_style'] < $this->quality_thresholds['global_tone_style']) {
            $recommendations[] = [
                'category' => 'global_tone_style',
                'priority' => 'high',
                'message' => 'Улучшите глобальный тон и стиль статьи',
                'action' => 'Добавьте больше обращений к читателю (вам, ваш), используйте активный залог, избегайте канцелярского языка, добавьте конкретные примеры'
            ];
        }

        // НОВОЕ: Рекомендации по сторителлингу/живому тону
        if ($analysis_results['human_storytelling'] < $this->quality_thresholds['human_storytelling']) {
            $recommendations[] = [
                'category' => 'human_storytelling',
                'priority' => 'high',
                'message' => 'Добавьте живой тон общения и реальные ситуации',
                'action' => 'Включите мини‑истории и кейсы (не менее 3), используйте "мы/я" там, где уместно, добавьте временные маркеры (сроки, годы), оформляйте микро‑пример в каждой H2‑секции'
            ];
        }
        
        // БЕЗУСЛОВНОЕ ПРАВИЛО: Рекомендации по длине статьи
        if ($analysis_results['content_length'] < $this->quality_thresholds['content_length']) {
            $word_count = $this->count_words_in_content($content ?? '');
            $deficit = 2500 - $word_count;
            
            $recommendations[] = [
                'category' => 'content_length',
                'priority' => 'critical',
                'message' => "КРИТИЧЕСКИЙ НЕДОБОР: Статья содержит {$word_count} слов, требуется минимум 2500 слов",
                'action' => "Добавьте {$deficit} слов: расширьте разделы примерами, добавьте пошаговые инструкции, создайте чек-листы, включите дополнительные кейсы и детализацию процессов"
            ];
        }
        
        // Общие рекомендации
        if ($quality_gates_result['percentage'] < 80) {
            $recommendations[] = [
                'category' => 'overall',
                'priority' => 'critical',
                'message' => 'Общее качество статьи требует значительного улучшения',
                'action' => 'Переработайте статью с учетом всех рекомендаций'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Сохранение результатов анализа качества
     */
    private function save_quality_analysis($post_id, $analysis_data) {
        update_post_meta($post_id, '_bsag_quality_analysis', $analysis_data);
        update_post_meta($post_id, '_bsag_quality_score', $analysis_data['overall_score']);
        update_post_meta($post_id, '_bsag_quality_timestamp', current_time('mysql'));
    }
    
    /**
     * Получение результатов анализа качества
     */
    public function get_quality_analysis($post_id) {
        return get_post_meta($post_id, '_bsag_quality_analysis', true);
    }
    
    /**
     * Оптимизация качества контента
     */
    public function optimize_content_quality($post_id, $content) {
        $analysis = $this->get_quality_analysis($post_id);
        
        if (!$analysis) {
            return $content;
        }
        
        $recommendations = $analysis['recommendations'];
        $optimized_content = $content;
        
        foreach ($recommendations as $recommendation) {
            $optimized_content = $this->apply_recommendation($optimized_content, $recommendation);
        }
        
        return $optimized_content;
    }
    
    /**
     * Применение рекомендации к контенту
     */
    private function apply_recommendation($content, $recommendation) {
        switch ($recommendation['category']) {
            case 'factual_accuracy':
                // Добавление профессиональных терминов
                if (strpos($content, 'банковская гарантия') === false) {
                    $content = str_replace('гарантия', 'банковская гарантия', $content);
                }
                break;
                
            case 'professional_tone':
                // Замена непрофессиональных слов
                $replacements = [
                    'круто' => 'эффективно',
                    'прикольно' => 'интересно',
                    'вау' => 'впечатляет'
                ];
                foreach ($replacements as $from => $to) {
                    $content = str_ireplace($from, $to, $content);
                }
                break;
                
            case 'seo_optimization':
                // Добавление внутренних ссылок если их мало
                $link_count = substr_count($content, '<a href=');
                if ($link_count < 3) {
                    $content .= '<p><a href="/bank-guarantee-calculator/">Рассчитать стоимость банковской гарантии</a></p>';
                }
                break;
        }
        
        return $content;
    }
    
    /**
     * Получение статистики качества
     */
    public function get_quality_stats() {
        global $wpdb;
        
        $posts = $wpdb->get_results("
            SELECT p.ID, p.post_title, pm.meta_value as quality_score
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'post' 
            AND pm.meta_key = '_bsag_quality_score'
            ORDER BY pm.meta_value DESC
        ");
        
        $stats = [
            'total_analyzed' => count($posts),
            'avg_quality_score' => 0,
            'high_quality_posts' => 0,
            'medium_quality_posts' => 0,
            'low_quality_posts' => 0
        ];
        
        if (!empty($posts)) {
            $total_score = 0;
            foreach ($posts as $post) {
                $score = floatval($post->quality_score);
                $total_score += $score;
                
                if ($score >= 0.85) $stats['high_quality_posts']++;
                elseif ($score >= 0.70) $stats['medium_quality_posts']++;
                else $stats['low_quality_posts']++;
            }
            
            $stats['avg_quality_score'] = round($total_score / count($posts), 3);
        }
        
        return $stats;
    }
    
    /**
     * БЕЗУСЛОВНОЕ ПРАВИЛО: Оценка длины статьи
     */
    private function assess_content_length($content) {
        $word_count = $this->count_words_in_content($content);
        $min_words = 2500; // БЕЗУСЛОВНЫЙ МИНИМУМ
        
        // Если достигнут минимум - 100% (1.0)
        if ($word_count >= $min_words) {
            return 1.0;
        }
        
        // Линейная шкала от 0 до 1 для недобора
        $percentage = $word_count / $min_words;
        
        // Если меньше 50% от минимума - критический недобор
        if ($percentage < 0.5) {
            return 0.0;
        }
        
        return round($percentage, 3);
    }
    
    /**
     * Подсчет слов в контенте (аналогично Prompt Chaining System)
     */
    private function count_words_in_content($content) {
        // Удаляем HTML теги
        $clean_content = strip_tags($content);
        
        // Удаляем служебные элементы
        $clean_content = preg_replace('/<!--.*?-->/s', '', $clean_content);
        $clean_content = preg_replace('/<script.*?<\/script>/s', '', $clean_content);
        $clean_content = preg_replace('/<style.*?<\/style>/s', '', $clean_content);
        
        // Декодируем HTML entities
        $clean_content = html_entity_decode($clean_content, ENT_QUOTES, 'UTF-8');
        
        // Нормализуем пробелы
        $clean_content = preg_replace('/\s+/', ' ', $clean_content);
        $clean_content = trim($clean_content);
        
        // Подсчитываем слова с учетом кириллицы
        $words = preg_split('/\p{L}+/u', $clean_content, -1, PREG_SPLIT_NO_EMPTY);
        
        return count($words);
    }
}

// Инициализация системы качества
new BizFin_Quality_System();
