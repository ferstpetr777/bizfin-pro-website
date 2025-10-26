<?php
/**
 * BizFin SEO Article Generator - Tone and Style Generator
 * Генератор тона и стиля статей на основе матрицы плагина
 */

if (!defined('ABSPATH')) exit;

class BizFin_Tone_Style_Generator {
    
    private $main_plugin;
    
    public function __construct($main_plugin) {
        $this->main_plugin = $main_plugin;
    }
    
    /**
     * Генерирует введение статьи на основе матрицы критериев
     */
    public function generate_introduction($keyword, $keyword_data) {
        $tone_style = $keyword_data['global_tone_style'] ?? [];
        $intro_template = $keyword_data['introduction_template'] ?? [];
        
        $introduction = $this->build_introduction_structure($keyword, $tone_style, $intro_template);
        
        return $introduction;
    }
    
    /**
     * Строит структуру введения согласно шаблону
     */
    private function build_introduction_structure($keyword, $tone_style, $intro_template) {
        $structure = $intro_template['structure'] ?? [];
        $rules = $intro_template['editorial_rules'] ?? [];
        
        $html = '<section class="intro">' . "\n";
        
        // SEO заголовок
        if (!empty($structure['seo_title'])) {
            $title = $this->apply_tone_formula($structure['seo_title'], $tone_style);
            $html .= "  <h1>{$title}</h1>\n";
        }
        
        // БЕЗУСЛОВНОЕ ПРАВИЛО: Простое определение
        $html .= $this->generate_mandatory_simple_definition($keyword, $tone_style);
        
        // БЕЗУСЛОВНОЕ ПРАВИЛО: Симпатичный пример
        $html .= $this->generate_mandatory_sympathetic_example($keyword, $tone_style);
        
        // Краткое описание
        if (!empty($structure['brief_description'])) {
            $description = $this->apply_tone_formula($structure['brief_description'], $tone_style);
            $html .= "  <p>{$description}</p>\n";
        }
        
        // Анонс содержания
        if (!empty($structure['content_announcement'])) {
            $announcement = $this->apply_tone_formula($structure['content_announcement'], $tone_style);
            $html .= "  <p>{$announcement}</p>\n";
        }
        
        // БЕЗУСЛОВНОЕ ПРАВИЛО: Кликабельное оглавление
        if (!empty($structure['table_of_contents'])) {
            $html .= $this->build_clickable_table_of_contents($structure['table_of_contents']);
        }
        
        // Основное определение
        if (!empty($structure['main_definition'])) {
            $definition = $this->apply_tone_formula($structure['main_definition'], $tone_style);
            $html .= "  <h2>Что такое " . $keyword . "</h2>\n";
            $html .= "  <p>{$definition}</p>\n";
        }
        
        // Пример из жизни (добавим короткий живой кейс, если отсутствует)
        if (!empty($structure['life_example'])) {
            $example = $this->apply_tone_formula($structure['life_example'], $tone_style);
            $html .= '  <div class="example">' . "\n";
            $html .= "    <p><strong>Пример:</strong> {$example}</p>\n";
            $html .= "  </div>\n";
        } else {
            $fallback = $this->generate_micro_story_example($keyword, $tone_style);
            if (!empty($fallback)) {
                $html .= '  <div class="example">' . "\n";
                $html .= "    <p><strong>Пример:</strong> {$fallback}</p>\n";
                $html .= "  </div>\n";
            }
        }
        
        // Правовой контекст
        if (!empty($structure['legal_context'])) {
            $legal = $this->apply_tone_formula($structure['legal_context'], $tone_style);
            $html .= "  <p>{$legal}</p>\n";
        }
        
        // Логический переход
        if (!empty($structure['logical_transition'])) {
            $transition = $this->apply_tone_formula($structure['logical_transition'], $tone_style);
            $html .= "  <p>{$transition}</p>\n";
        }
        
        $html .= '</section>' . "\n";
        
        return $html;
    }
    
    /**
     * Строит оглавление статьи
     */
    private function build_table_of_contents($contents_text) {
        $html = '  <nav class="toc">' . "\n";
        $html .= "    <strong>Содержание:</strong>\n";
        $html .= "    <ul>\n";
        
        // Разбиваем текст на пункты
        $items = explode(',', $contents_text);
        foreach ($items as $item) {
            $item = trim($item);
            if (!empty($item)) {
                $html .= "      <li>{$item}</li>\n";
            }
        }
        
        $html .= "    </ul>\n";
        $html .= "  </nav>\n";
        
        return $html;
    }
    
    /**
     * БЕЗУСЛОВНОЕ ПРАВИЛО: Строит кликабельное оглавление с якорными ссылками
     */
    private function build_clickable_table_of_contents($contents_text) {
        $html = '  <nav class="toc">' . "\n";
        $html .= "    <strong>Содержание:</strong>\n";
        $html .= "    <ul>\n";
        
        // Разбиваем текст на пункты и создаём якорные ссылки
        $items = explode(',', $contents_text);
        foreach ($items as $item) {
            $item = trim($item);
            if (!empty($item)) {
                $anchor_id = $this->generate_anchor_id($item);
                $html .= "      <li><a href=\"#{$anchor_id}\">{$item}</a></li>\n";
            }
        }
        
        $html .= "    </ul>\n";
        $html .= "  </nav>\n";
        
        return $html;
    }
    
    /**
     * БЕЗУСЛОВНОЕ ПРАВИЛО: Генерирует простое определение
     */
    private function generate_mandatory_simple_definition($keyword, $tone_style) {
        $seo_matrix = $this->main_plugin->get_seo_matrix();
        $mandatory_rules = $seo_matrix['mandatory_intro_blocks'] ?? [];
        
        if (!empty($mandatory_rules['simple_definition']['required'])) {
            $template = $mandatory_rules['simple_definition']['template'] ?? '';
            $example = $mandatory_rules['simple_definition']['example'] ?? '';
            
            // Адаптируем под ключевое слово
            $definition = str_replace('[Термин]', $keyword, $template);
            $definition = $this->apply_tone_formula($definition, $tone_style);
            
            return "  <p>{$definition}</p>\n";
        }
        
        return '';
    }
    
    /**
     * БЕЗУСЛОВНОЕ ПРАВИЛО: Генерирует симпатичный пример
     */
    private function generate_mandatory_sympathetic_example($keyword, $tone_style) {
        $seo_matrix = $this->main_plugin->get_seo_matrix();
        $mandatory_rules = $seo_matrix['mandatory_intro_blocks'] ?? [];
        
        if (!empty($mandatory_rules['sympathetic_example']['required'])) {
            $template = $mandatory_rules['sympathetic_example']['template'] ?? '';
            $example = $mandatory_rules['sympathetic_example']['example'] ?? '';
            
            // Адаптируем под ключевое слово
            $sympathetic_example = str_replace('[Термин]', $keyword, $template);
            $sympathetic_example = $this->apply_tone_formula($sympathetic_example, $tone_style);
            
            return "  <p>{$sympathetic_example}</p>\n";
        }
        
        return '';
    }
    
    /**
     * Генерирует якорный ID из текста (kebab-case)
     */
    private function generate_anchor_id($text) {
        // Убираем лишние символы и приводим к нижнему регистру
        $anchor = mb_strtolower($text, 'UTF-8');
        $anchor = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $anchor);
        $anchor = preg_replace('/\s+/', '-', trim($anchor));
        $anchor = preg_replace('/-+/', '-', $anchor);
        $anchor = trim($anchor, '-');
        
        return $anchor;
    }
    
    /**
     * Применяет формулу тона к тексту
     */
    private function apply_tone_formula($text, $tone_style) {
        $formula = $tone_style['tone_formula'] ?? [];
        $guidelines = $tone_style['language_guidelines'] ?? [];
        
        // Применяем правила языка
        $text = $this->apply_language_guidelines($text, $guidelines);
        
        return $text;
    }
    
    /**
     * Применяет языковые правила
     */
    private function apply_language_guidelines($text, $guidelines) {
        // Убираем канцелярит
        if (!empty($guidelines['avoid_clichés'])) {
            $text = $this->remove_bureaucratic_language($text);
        }
        
        // Добавляем обращения к читателю
        if (!empty($guidelines['focus_on_reader'])) {
            $text = $this->add_reader_references($text);
        }
        
        // Делаем активный залог
        if (!empty($guidelines['active_voice'])) {
            $text = $this->make_active_voice($text);
        }
        
        // Добавляем мягкие нарративные маркеры (человеческий голос)
        if (!empty($guidelines['human_storytelling'])) {
            $text = $this->enrich_with_narrative_markers($text);
        }
        
        return $text;
    }
    
    /**
     * Убирает канцелярский язык
     */
    private function remove_bureaucratic_language($text) {
        $replacements = [
            'осуществляется' => 'происходит',
            'предусматривается' => 'предполагается',
            'в соответствии с' => 'согласно',
            'в целях' => 'для',
            'в случае' => 'если',
            'в рамках' => 'в процессе',
            'является' => 'это',
            'представляет собой' => 'это'
        ];
        
        foreach ($replacements as $bureaucratic => $simple) {
            $text = str_ireplace($bureaucratic, $simple, $text);
        }
        
        return $text;
    }
    
    /**
     * Добавляет обращения к читателю
     */
    private function add_reader_references($text) {
        // Заменяем безличные конструкции на обращения
        $replacements = [
            'необходимо' => 'вам необходимо',
            'можно' => 'вы можете',
            'нужно' => 'вам нужно',
            'стоит' => 'вам стоит',
            'важно' => 'вам важно'
        ];
        
        foreach ($replacements as $impersonal => $personal) {
            $text = preg_replace('/\b' . $impersonal . '\b/u', $personal, $text);
        }
        
        return $text;
    }
    
    /**
     * Делает активный залог
     */
    private function make_active_voice($text) {
        $replacements = [
            'было сделано' => 'сделали',
            'было получено' => 'получили',
            'было оформлено' => 'оформили',
            'было выдано' => 'выдали',
            'было зарегистрировано' => 'зарегистрировали'
        ];
        
        foreach ($replacements as $passive => $active) {
            $text = str_ireplace($passive, $active, $text);
        }
        
        return $text;
    }

    /**
     * Генерирует короткий микро‑пример (1–2 предложения) в человеческом тоне
     */
    private function generate_micro_story_example($keyword, $tone_style) {
        $templates = [
            'Вчера к нам обратился предприниматель с типичной ситуацией: требовалась гарантия, чтобы подтвердить надёжность на торгах — оформили за два дня и избежали заморозки оборотных средств.',
            'Недавно мы помогли клиенту: выиграли тендер, но нужно было быстро подтвердить обязательства — гарантия закрыла вопрос, контракт подписали в срок.',
            'На практике часто бывает так: поставщик просит подтверждение сделки — банковская гарантия снимает риски и ускоряет согласование.'
        ];
        return $templates[array_rand($templates)];
    }

    /**
     * Мягко обогащает текст нарративными маркерами, не ломая смысл
     */
    private function enrich_with_narrative_markers($text) {
        // Лёгкие вставки: "на практике", "часто сталкиваемся", "мы рекомендуем"
        $replacements = [
            '/\bна практике\b/ui' => 'на практике',
            '/\bрекомендуется\b/ui' => 'мы рекомендуем',
            '/\bследует\b/ui' => 'вам стоит',
            '/\bможно\b/ui' => 'вы можете'
        ];
        foreach ($replacements as $pattern => $rep) {
            $text = preg_replace($pattern, $rep, $text);
        }
        return $text;
    }
    
    /**
     * Генерирует основной контент статьи с применением тона
     */
    public function generate_content_with_tone($keyword, $keyword_data, $content_sections) {
        $tone_style = $keyword_data['global_tone_style'] ?? [];
        
        $html = '';
        
        foreach ($content_sections as $section) {
            $html .= $this->format_content_section($section, $tone_style);
        }
        
        // БЕЗУСЛОВНОЕ ПРАВИЛО: Добавляем внутренние ссылки
        if (isset($this->main_plugin->internal_linking_manager)) {
            $html = $this->main_plugin->internal_linking_manager->generate_internal_links(
                $html, 
                $keyword, 
                5
            );
        }
        
        return $html;
    }
    
    /**
     * Форматирует секцию контента с применением тона
     */
    private function format_content_section($section, $tone_style) {
        $html = '';
        
        // Заголовок секции
        if (!empty($section['heading'])) {
            $heading = $this->apply_tone_formula($section['heading'], $tone_style);
            $html .= "<h2>{$heading}</h2>\n";
        }
        
        // Контент секции
        if (!empty($section['content'])) {
            $content = $this->apply_tone_formula($section['content'], $tone_style);
            $html .= "<p>{$content}</p>\n";
        }
        
        // Примеры (гарантируем микро‑пример в каждой H2‑секции)
        if (!empty($section['examples'])) {
            $html .= '<div class="examples">' . "\n";
            foreach ($section['examples'] as $example) {
                $example_text = $this->apply_tone_formula($example, $tone_style);
                $html .= "  <div class=\"example\">\n";
                $html .= "    <p><strong>Пример:</strong> {$example_text}</p>\n";
                $html .= "  </div>\n";
            }
            $html .= "</div>\n";
        } else {
            $auto_example = $this->generate_micro_story_example('', $tone_style);
            if (!empty($auto_example)) {
                $html .= '<div class="examples">' . "\n";
                $html .= "  <div class=\"example\">\n";
                $html .= "    <p><strong>Пример:</strong> {$auto_example}</p>\n";
                $html .= "  </div>\n";
                $html .= "</div>\n";
            }
        }
        
        // Списки
        if (!empty($section['lists'])) {
            foreach ($section['lists'] as $list) {
                $html .= $this->format_list($list, $tone_style);
            }
        }
        
        return $html;
    }
    
    /**
     * Форматирует список с применением тона
     */
    private function format_list($list, $tone_style) {
        $html = "<ul>\n";
        
        foreach ($list['items'] as $item) {
            $item_text = $this->apply_tone_formula($item, $tone_style);
            $html .= "  <li>{$item_text}</li>\n";
        }
        
        $html .= "</ul>\n";
        
        return $html;
    }
    
    /**
     * Получает критерии тона для ключевого слова
     */
    public function get_tone_criteria($keyword) {
        $seo_matrix = $this->main_plugin->get_seo_matrix();
        $keyword_data = $seo_matrix['keywords'][$keyword] ?? [];
        
        return [
            'global_tone_style' => $keyword_data['global_tone_style'] ?? [],
            'introduction_template' => $keyword_data['introduction_template'] ?? []
        ];
    }
    
    /**
     * Валидирует соответствие текста критериям тона
     */
    public function validate_tone_compliance($text, $tone_criteria) {
        $compliance_score = 0;
        $total_criteria = 0;
        
        $guidelines = $tone_criteria['global_tone_style']['language_guidelines'] ?? [];
        
        // Проверяем читаемость
        if (!empty($guidelines['readability'])) {
            $total_criteria++;
            if ($this->check_readability($text)) {
                $compliance_score++;
            }
        }
        
        // Проверяем активный залог
        if (!empty($guidelines['active_voice'])) {
            $total_criteria++;
            if ($this->check_active_voice($text)) {
                $compliance_score++;
            }
        }
        
        // Проверяем обращения к читателю
        if (!empty($guidelines['focus_on_reader'])) {
            $total_criteria++;
            if ($this->check_reader_references($text)) {
                $compliance_score++;
            }
        }
        
        // Проверяем отсутствие канцелярита
        if (!empty($guidelines['avoid_clichés'])) {
            $total_criteria++;
            if ($this->check_no_bureaucratic_language($text)) {
                $compliance_score++;
            }
        }
        
        return $total_criteria > 0 ? ($compliance_score / $total_criteria) : 0;
    }
    
    /**
     * Проверяет читаемость текста
     */
    private function check_readability($text) {
        // Проверяем длину предложений (не более 20 слов)
        $sentences = preg_split('/[.!?]+/', $text);
        $long_sentences = 0;
        
        foreach ($sentences as $sentence) {
            $words = str_word_count($sentence);
            if ($words > 20) {
                $long_sentences++;
            }
        }
        
        return ($long_sentences / count($sentences)) < 0.3; // Менее 30% длинных предложений
    }
    
    /**
     * Проверяет использование активного залога
     */
    private function check_active_voice($text) {
        $passive_patterns = [
            'было сделано', 'было получено', 'было оформлено', 
            'было выдано', 'было зарегистрировано'
        ];
        
        $passive_count = 0;
        foreach ($passive_patterns as $pattern) {
            $passive_count += substr_count(strtolower($text), $pattern);
        }
        
        return $passive_count < 3; // Менее 3 пассивных конструкций
    }
    
    /**
     * Проверяет обращения к читателю
     */
    private function check_reader_references($text) {
        $reader_words = ['вам', 'ваш', 'вы', 'ваше', 'для вас'];
        $reader_count = 0;
        
        foreach ($reader_words as $word) {
            $reader_count += substr_count(strtolower($text), $word);
        }
        
        return $reader_count >= 3; // Минимум 3 обращения к читателю
    }
    
    /**
     * Проверяет отсутствие канцелярского языка
     */
    private function check_no_bureaucratic_language($text) {
        $bureaucratic_words = [
            'осуществляется', 'предусматривается', 'в соответствии с',
            'в целях', 'является', 'представляет собой'
        ];
        
        $bureaucratic_count = 0;
        foreach ($bureaucratic_words as $word) {
            $bureaucratic_count += substr_count(strtolower($text), $word);
        }
        
        return $bureaucratic_count < 2; // Менее 2 канцелярских слов
    }
    
    /**
     * БЕЗУСЛОВНОЕ ПРАВИЛО: Авто-доращивание статьи до минимума 2500 слов
     */
    public function auto_expand_content($content, $keyword, $keyword_data) {
        $word_count = $this->count_words_in_content($content);
        $min_words = 2500;
        
        if ($word_count >= $min_words) {
            return $content; // Уже достаточно слов
        }
        
        $deficit = $min_words - $word_count;
        $expansion_attempts = 0;
        $max_attempts = 3;
        
        while ($word_count < $min_words && $expansion_attempts < $max_attempts) {
            $content = $this->expand_content_sections($content, $keyword, $keyword_data, $deficit);
            $new_word_count = $this->count_words_in_content($content);
            
            if ($new_word_count > $word_count) {
                $word_count = $new_word_count;
                $deficit = $min_words - $word_count;
            }
            
            $expansion_attempts++;
        }
        
        return $content;
    }
    
    /**
     * Расширение контента по секциям
     */
    private function expand_content_sections($content, $keyword, $keyword_data, $deficit) {
        // Находим H2 секции для расширения
        $h2_sections = $this->extract_h2_sections($content);
        
        if (empty($h2_sections)) {
            return $content;
        }
        
        // Расширяем самые короткие секции
        $target_sections = $this->identify_shortest_sections($h2_sections, $deficit);
        
        foreach ($target_sections as $section) {
            $expanded_section = $this->expand_single_section($section, $keyword, $keyword_data);
            $content = str_replace($section['full_content'], $expanded_section, $content);
        }
        
        return $content;
    }
    
    /**
     * Извлечение H2 секций из контента
     */
    private function extract_h2_sections($content) {
        $sections = [];
        $pattern = '/<h2[^>]*>(.*?)<\/h2>(.*?)(?=<h2|$)/s';
        
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $sections[] = [
                'title' => strip_tags($match[1]),
                'content' => $match[2],
                'full_content' => $match[0],
                'word_count' => $this->count_words_in_content($match[2])
            ];
        }
        
        return $sections;
    }
    
    /**
     * Определение самых коротких секций для расширения
     */
    private function identify_shortest_sections($sections, $deficit) {
        // Сортируем по количеству слов (от меньшего к большему)
        usort($sections, function($a, $b) {
            return $a['word_count'] - $b['word_count'];
        });
        
        $target_sections = [];
        $words_to_add = 0;
        
        foreach ($sections as $section) {
            if ($words_to_add >= $deficit) {
                break;
            }
            
            // Расширяем секции с менее чем 300 словами
            if ($section['word_count'] < 300) {
                $target_sections[] = $section;
                $words_to_add += (300 - $section['word_count']);
            }
        }
        
        return $target_sections;
    }
    
    /**
     * Расширение одной секции
     */
    private function expand_single_section($section, $keyword, $keyword_data) {
        $current_content = $section['content'];
        $current_words = $section['word_count'];
        $target_words = 350; // Целевое количество слов для секции
        
        if ($current_words >= $target_words) {
            return $section['full_content'];
        }
        
        $words_to_add = $target_words - $current_words;
        
        // Генерируем дополнительный контент
        $additional_content = $this->generate_section_expansion($section['title'], $keyword, $words_to_add);
        
        // Вставляем дополнительный контент в секцию
        $expanded_content = $current_content . "\n\n" . $additional_content;
        
        return '<h2>' . $section['title'] . '</h2>' . $expanded_content;
    }
    
    /**
     * Генерация расширения для секции
     */
    private function generate_section_expansion($section_title, $keyword, $words_to_add) {
        $expansion_strategies = [
            'add_examples' => 'Добавление практических примеров и кейсов',
            'add_details' => 'Расширение объяснений и детализация процессов',
            'add_subheadings' => 'Добавление подзаголовков с дополнительным контентом',
            'add_lists' => 'Создание списков и чек-листов',
            'add_step_by_step' => 'Пошаговые инструкции и алгоритмы'
        ];
        
        $strategy = array_rand($expansion_strategies);
        $content = '';
        
        switch ($strategy) {
            case 'add_examples':
                $content = $this->generate_practical_examples($section_title, $keyword);
                break;
            case 'add_details':
                $content = $this->generate_detailed_explanations($section_title, $keyword);
                break;
            case 'add_subheadings':
                $content = $this->generate_subheadings_content($section_title, $keyword);
                break;
            case 'add_lists':
                $content = $this->generate_lists_and_checklists($section_title, $keyword);
                break;
            case 'add_step_by_step':
                $content = $this->generate_step_by_step_guide($section_title, $keyword);
                break;
        }
        
        return $content;
    }
    
    /**
     * Генерация практических примеров
     */
    private function generate_practical_examples($section_title, $keyword) {
        return '<div class="practical-example">
            <h3>Практический пример</h3>
            <p>Рассмотрим реальную ситуацию: компания "СтройИнвест" участвует в тендере на строительство школы. Для подачи заявки требуется банковская гарантия на сумму 2,5 млн рублей. Руководитель компании обращается в банк, предоставляет необходимые документы, и через 5 рабочих дней получает гарантию. Это позволило компании участвовать в тендере и в итоге выиграть контракт.</p>
            <p>Ключевые моменты этого примера:</p>
            <ul>
                <li>Срок получения гарантии составил 5 дней</li>
                <li>Сумма гарантии соответствовала требованиям тендера</li>
                <li>Компания смогла участвовать в конкурентной борьбе</li>
                <li>Гарантия помогла выиграть контракт</li>
            </ul>
        </div>';
    }
    
    /**
     * Генерация детальных объяснений
     */
    private function generate_detailed_explanations($section_title, $keyword) {
        return '<div class="detailed-explanation">
            <h3>Детальный разбор процесса</h3>
            <p>Для полного понимания механизма работы банковской гарантии важно разобрать каждый этап подробно. Процесс начинается с анализа потребностей компании и заканчивается исполнением обязательств по гарантии.</p>
            <p>На каждом этапе есть свои особенности и нюансы, которые могут повлиять на успешность получения гарантии. Важно учитывать требования банка, сроки рассмотрения заявки, необходимые документы и условия предоставления гарантии.</p>
        </div>';
    }
    
    /**
     * Генерация подзаголовков с контентом
     */
    private function generate_subheadings_content($section_title, $keyword) {
        return '<h3>Важные аспекты</h3>
        <p>При работе с банковскими гарантиями необходимо учитывать несколько важных аспектов, которые могут повлиять на успешность получения гарантии и её эффективное использование.</p>
        
        <h3>Типичные ошибки</h3>
        <p>Многие компании допускают типичные ошибки при получении банковских гарантий. К ним относятся неправильное оформление документов, несоблюдение сроков подачи заявки, неверный расчет суммы гарантии.</p>
        
        <h3>Рекомендации экспертов</h3>
        <p>Эксперты рекомендуют тщательно подходить к выбору банка-гаранта, внимательно изучать условия предоставления гарантии, заранее готовить все необходимые документы и учитывать сроки рассмотрения заявки.</p>';
    }
    
    /**
     * Генерация списков и чек-листов
     */
    private function generate_lists_and_checklists($section_title, $keyword) {
        return '<div class="checklist">
            <h3>Чек-лист для получения банковской гарантии</h3>
            <ul>
                <li>✓ Определить сумму и срок гарантии</li>
                <li>✓ Выбрать подходящий банк-гарант</li>
                <li>✓ Подготовить пакет документов</li>
                <li>✓ Подать заявку в банк</li>
                <li>✓ Дождаться рассмотрения заявки</li>
                <li>✓ Подписать договор с банком</li>
                <li>✓ Получить гарантию</li>
                <li>✓ Зарегистрировать в реестре (при необходимости)</li>
            </ul>
        </div>';
    }
    
    /**
     * Генерация пошагового руководства
     */
    private function generate_step_by_step_guide($section_title, $keyword) {
        return '<div class="step-by-step">
            <h3>Пошаговое руководство</h3>
            <ol>
                <li><strong>Шаг 1:</strong> Анализ требований тендера или контракта</li>
                <li><strong>Шаг 2:</strong> Расчет необходимой суммы гарантии</li>
                <li><strong>Шаг 3:</strong> Выбор банка-гаранта</li>
                <li><strong>Шаг 4:</strong> Подготовка документов</li>
                <li><strong>Шаг 5:</strong> Подача заявки</li>
                <li><strong>Шаг 6:</strong> Ожидание решения банка</li>
                <li><strong>Шаг 7:</strong> Подписание договора</li>
                <li><strong>Шаг 8:</strong> Получение гарантии</li>
            </ol>
        </div>';
    }
    
    /**
     * Подсчет слов в контенте
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
