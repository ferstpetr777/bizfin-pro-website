/**
 * BizFin SEO Article Generator - Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    let selectedKeyword = null;
    let isGenerating = false;
    let generationProgress = 0;
    
    // Инициализация интерфейса
    initializeInterface();
    
    function initializeInterface() {
        // Обработчики событий для ключевых слов
        $('.bsag-keyword-item').on('click', function() {
            selectKeyword($(this));
        });
        
        // Обработчики кнопок
        $('#bsag-start-generation').on('click', startArticleGeneration);
        $('#bsag-stop-generation').on('click', stopArticleGeneration);
        $('#bsag-publish-article').on('click', publishArticle);
        $('#bsag-save-draft').on('click', saveDraft);
        $('#bsag-edit-article').on('click', editArticle);
        $('#bsag-generate-with-modules').on('click', generateWithModules);
        $('#bsag-test-with-modules').on('click', testWithModules);
        
        // Инициализация tooltips
        initializeTooltips();
    }
    
    function selectKeyword($item) {
        // Убираем выделение с других элементов
        $('.bsag-keyword-item').removeClass('selected');
        
        // Выделяем выбранный элемент
        $item.addClass('selected');
        
        // Получаем ключевое слово
        selectedKeyword = $item.data('keyword');
        
        // Обновляем интерфейс
        updateInterface();
        
        // Получаем данные ключевого слова
        getKeywordData(selectedKeyword);
    }
    
    function updateInterface() {
        if (selectedKeyword) {
            $('#bsag-selected-keyword').text(selectedKeyword);
            $('#bsag-start-generation').prop('disabled', false);
            addConversationMessage('system', `Выбрано ключевое слово: "${selectedKeyword}"`);
        } else {
            $('#bsag-selected-keyword').text('Не выбрано');
            $('#bsag-start-generation').prop('disabled', true);
        }
    }
    
    function getKeywordData(keyword) {
        $.ajax({
            url: bsagAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'bsag_get_keyword_data',
                keyword: keyword,
                nonce: bsagAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayKeywordData(response.data);
                } else {
                    addConversationMessage('error', 'Ошибка получения данных: ' + response.data);
                }
            },
            error: function() {
                addConversationMessage('error', 'Ошибка подключения к серверу');
            }
        });
    }
    
    function displayKeywordData(data) {
        const { keyword_data, article_structure, seo_requirements } = data;
        
        // Отображаем информацию о ключевом слове
        let message = `📊 Анализ ключевого слова:\n`;
        message += `• Интент: ${keyword_data.intent}\n`;
        message += `• Структура: ${keyword_data.structure}\n`;
        message += `• Целевая аудитория: ${keyword_data.target_audience}\n`;
        message += `• Объем статьи: ${keyword_data.word_count} слов\n`;
        message += `• Тип CTA: ${keyword_data.cta_type}`;
        
        addConversationMessage('ai', message);
        
        // Отображаем структуру статьи
        let structureMessage = `📝 Структура статьи:\n`;
        structureMessage += `• H1: ${article_structure.h1}\n`;
        structureMessage += `• Разделы (H2): ${article_structure.h2_sections.length}\n`;
        
        article_structure.h2_sections.forEach((section, index) => {
            structureMessage += `  ${index + 1}. ${section}\n`;
        });
        
        addConversationMessage('ai', structureMessage);
        
        // Отображаем SEO требования
        let seoMessage = `🎯 SEO требования:\n`;
        seoMessage += `• Длина Title: до ${seo_requirements.title_length} символов\n`;
        seoMessage += `• Мета-описание: до ${seo_requirements.meta_description_length} символов\n`;
        seoMessage += `• Минимум слов: ${seo_requirements.word_count_min}\n`;
        seoMessage += `• Максимум слов: ${seo_requirements.word_count_max}\n`;
        seoMessage += `• Плотность ключевых слов: ${seo_requirements.keyword_density[0]}-${seo_requirements.keyword_density[1]}%\n`;
        seoMessage += `• Внутренние ссылки: ${seo_requirements.internal_links[0]}-${seo_requirements.internal_links[1]}\n`;
        seoMessage += `• CTA блоки: ${seo_requirements.cta_blocks[0]}-${seo_requirements.cta_blocks[1]}`;
        
        addConversationMessage('ai', seoMessage);
    }
    
    function startArticleGeneration() {
        if (!selectedKeyword || isGenerating) {
            return;
        }
        
        isGenerating = true;
        generationProgress = 0;
        
        // Обновляем интерфейс
        $('#bsag-start-generation').hide();
        $('#bsag-stop-generation').show();
        $('.bsag-generation-progress').show();
        
        addConversationMessage('user', `Начинаю генерацию статьи по ключевому слову: "${selectedKeyword}"`);
        
        // Симулируем процесс генерации с ИИ-агентом
        simulateAIGeneration();
    }
    
    function simulateAIGeneration() {
        const steps = [
            {
                message: '🤖 ИИ-агент: Анализирую ключевое слово и определяю структуру статьи...',
                progress: 10
            },
            {
                message: '📊 ИИ-агент: Изучаю конкурентов и формирую уникальный контент...',
                progress: 25
            },
            {
                message: '✍️ ИИ-агент: Пишу введение с фокусным ключевым словом...',
                progress: 40
            },
            {
                message: '📝 ИИ-агент: Создаю основные разделы статьи...',
                progress: 60
            },
            {
                message: '🔍 ИИ-агент: Оптимизирую контент под SEO критерии...',
                progress: 80
            },
            {
                message: '✅ ИИ-агент: Добавляю мета-теги, CTA блоки и финализирую статью...',
                progress: 100
            }
        ];
        
        let currentStep = 0;
        
        function processNextStep() {
            if (currentStep >= steps.length) {
                completeGeneration();
                return;
            }
            
            const step = steps[currentStep];
            
            // Добавляем сообщение
            addConversationMessage('ai', step.message);
            
            // Обновляем прогресс
            updateProgress(step.progress);
            
            currentStep++;
            
            // Продолжаем через 2 секунды
            setTimeout(processNextStep, 2000);
        }
        
        processNextStep();
    }
    
    function updateProgress(percent) {
        generationProgress = percent;
        $('.bsag-progress-fill').css('width', percent + '%');
        $('.bsag-progress-text').text(`Генерация статьи... ${percent}%`);
    }
    
    function completeGeneration() {
        isGenerating = false;
        
        // Обновляем интерфейс
        $('#bsag-start-generation').show();
        $('#bsag-stop-generation').hide();
        
        addConversationMessage('ai', '🎉 Статья успешно сгенерирована! Проверьте предварительный просмотр ниже.');
        
        // Показываем предварительный просмотр
        showArticlePreview();
    }
    
    function stopArticleGeneration() {
        isGenerating = false;
        
        // Обновляем интерфейс
        $('#bsag-start-generation').show();
        $('#bsag-stop-generation').hide();
        $('.bsag-generation-progress').hide();
        
        addConversationMessage('system', '⏹️ Генерация статьи остановлена пользователем.');
    }
    
    function showArticlePreview() {
        // Генерируем примерный контент статьи
        const articleContent = generateSampleArticle(selectedKeyword);
        
        $('#bsag-article-content').html(articleContent);
        $('.bsag-article-preview').show();
        
        // Прокручиваем к предварительному просмотру
        $('html, body').animate({
            scrollTop: $('.bsag-article-preview').offset().top - 100
        }, 500);
    }
    
    function generateSampleArticle(keyword) {
        const keywordData = bsagAjax.seoMatrix.keywords[keyword];
        const structure = bsagAjax.seoMatrix.article_structures[keywordData.structure];
        
        let content = `<article class="bsag-generated-article">`;
        content += `<header><h1>${structure.h1}</h1></header>`;
        
        structure.h2_sections.forEach(section => {
            content += `<section>`;
            content += `<h2>${section}</h2>`;
            content += `<p>Это примерный контент для раздела "${section}". Здесь будет размещен уникальный, экспертный контент, оптимизированный под ключевое слово "${keyword}" и соответствующий всем SEO требованиям.</p>`;
            content += `<p>Контент будет содержать практические советы, примеры из практики, статистические данные и другую полезную информацию для целевой аудитории.</p>`;
            content += `</section>`;
        });
        
        content += `<footer class="bsag-article-meta">`;
        content += `<p><strong>Ключевое слово:</strong> ${keyword}</p>`;
        content += `<p><strong>Интент:</strong> ${keywordData.intent}</p>`;
        content += `<p><strong>Целевая аудитория:</strong> ${keywordData.target_audience}</p>`;
        content += `<p><strong>Объем:</strong> ${keywordData.word_count} слов</p>`;
        content += `</footer>`;
        content += `</article>`;
        
        return content;
    }
    
    function publishArticle() {
        if (!selectedKeyword) {
            return;
        }
        
        addConversationMessage('user', '📤 Публикую статью на сайте...');
        
        // Здесь будет AJAX запрос для публикации статьи
        setTimeout(() => {
            addConversationMessage('ai', '✅ Статья успешно опубликована! Проверьте её на сайте.');
        }, 1000);
    }
    
    function saveDraft() {
        if (!selectedKeyword) {
            return;
        }
        
        addConversationMessage('user', '💾 Сохраняю статью как черновик...');
        
        // Здесь будет AJAX запрос для сохранения черновика
        setTimeout(() => {
            addConversationMessage('ai', '✅ Статья сохранена как черновик.');
        }, 1000);
    }
    
    function editArticle() {
        if (!selectedKeyword) {
            return;
        }
        
        addConversationMessage('user', '✏️ Открываю редактор для редактирования статьи...');
        
        // Здесь будет переход в редактор WordPress
        setTimeout(() => {
            addConversationMessage('ai', '📝 Редактор открыт. Вы можете внести изменения в статью.');
        }, 1000);
    }
    
    function addConversationMessage(type, content) {
        const timestamp = new Date().toLocaleTimeString();
        const messageHtml = `
            <div class="bsag-conversation-message ${type}">
                <div class="bsag-message-time">${timestamp}</div>
                <div class="bsag-message-content">${content.replace(/\n/g, '<br>')}</div>
            </div>
        `;
        
        $('#bsag-conversation-log').append(messageHtml);
        
        // Прокручиваем к последнему сообщению
        const conversationLog = document.getElementById('bsag-conversation-log');
        conversationLog.scrollTop = conversationLog.scrollHeight;
    }
    
    function initializeTooltips() {
        // Инициализация tooltips для элементов интерфейса
        $('[data-tooltip]').each(function() {
            const $this = $(this);
            const tooltip = $this.data('tooltip');
            
            $this.on('mouseenter', function() {
                showTooltip($this, tooltip);
            });
            
            $this.on('mouseleave', function() {
                hideTooltip();
            });
        });
    }
    
    function showTooltip($element, text) {
        const tooltip = $('<div class="bsag-tooltip">' + text + '</div>');
        $('body').append(tooltip);
        
        const elementOffset = $element.offset();
        const elementWidth = $element.outerWidth();
        const elementHeight = $element.outerHeight();
        
        tooltip.css({
            position: 'absolute',
            top: elementOffset.top - tooltip.outerHeight() - 5,
            left: elementOffset.left + (elementWidth / 2) - (tooltip.outerWidth() / 2),
            zIndex: 9999
        });
    }
    
    function hideTooltip() {
        $('.bsag-tooltip').remove();
    }
    
    // Обработка ошибок AJAX
    $(document).ajaxError(function(event, xhr, settings, error) {
        addConversationMessage('error', 'Ошибка подключения: ' + error);
    });
    
    // Автосохранение настроек
    $('.bsag-settings input, .bsag-settings select').on('change', function() {
        const $this = $(this);
        const settingName = $this.attr('name');
        const settingValue = $this.val();
        
        // Здесь будет AJAX запрос для сохранения настроек
        console.log('Сохранение настройки:', settingName, settingValue);
    });
    
    // Экспорт/импорт конфигурации
    $('#bsag-export-config').on('click', function() {
        const config = {
            seo_matrix: bsagAjax.seoMatrix,
            settings: {
                yoast_integration: $('#bsag-yoast-integration').is(':checked'),
                auto_publish: $('#bsag-auto-publish').is(':checked'),
                default_category: $('#bsag-default-category').val()
            }
        };
        
        const dataStr = JSON.stringify(config, null, 2);
        const dataBlob = new Blob([dataStr], {type: 'application/json'});
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(dataBlob);
        link.download = 'bizfin-seo-generator-config.json';
        link.click();
    });
    
    $('#bsag-import-config').on('change', function() {
        const file = this.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const config = JSON.parse(e.target.result);
                // Здесь будет обработка импортированной конфигурации
                console.log('Импортированная конфигурация:', config);
                addConversationMessage('system', 'Конфигурация успешно импортирована.');
            } catch (error) {
                addConversationMessage('error', 'Ошибка при импорте конфигурации: ' + error.message);
            }
        };
        reader.readAsText(file);
    });
});

// Глобальные функции для интеграции с другими плагинами
window.BizFinSEOGenerator = {
    generateArticle: function(keyword) {
        // Функция для вызова генерации статьи из других плагинов
        console.log('Генерация статьи для ключевого слова:', keyword);
    },
    
    getKeywordData: function(keyword) {
        // Функция для получения данных ключевого слова
        return window.bsagAjax.seoMatrix.keywords[keyword] || null;
    },
    
    validateSEO: function(content, keyword) {
        // Функция для валидации SEO критериев
        const requirements = window.bsagAjax.seoMatrix.seo_requirements;
        const results = {
            title_length: content.title ? content.title.length <= requirements.title_length : false,
            meta_description_length: content.meta_description ? content.meta_description.length <= requirements.meta_description_length : false,
            word_count: content.body ? content.body.split(' ').length >= requirements.word_count_min : false,
            keyword_density: this.calculateKeywordDensity(content.body, keyword)
        };
        
        return results;
    },
    
    calculateKeywordDensity: function(content, keyword) {
        if (!content || !keyword) return 0;
        
        const words = content.toLowerCase().split(/\s+/);
        const keywordCount = words.filter(word => word.includes(keyword.toLowerCase())).length;
        
        return (keywordCount / words.length) * 100;
    },
    
    // Функции для работы с динамическими модулями
    generateWithModules: function(keyword, userInstruction, tableOfContents, modules) {
        return $.ajax({
            url: bsag_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'bsag_generate_with_modules',
                nonce: bsag_admin.nonce,
                keyword: keyword,
                user_instruction: userInstruction,
                table_of_contents: tableOfContents,
                modules: modules
            }
        });
    },
    
    publishArticle: function(postId) {
        return $.ajax({
            url: bsag_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'bsag_publish_article',
                nonce: bsag_admin.nonce,
                post_id: postId
            }
        });
    },
    
    getAvailableModules: function() {
        return [
            { key: 'calculator', name: 'Калькулятор банковских гарантий', description: 'Интерактивный калькулятор для расчета стоимости гарантии' },
            { key: 'schema_diagram', name: 'Схема-диаграмма процесса', description: 'Визуальная схема процесса получения банковской гарантии' },
            { key: 'comparison_table', name: 'Сравнительная таблица', description: 'Таблица сравнения банков и их условий' },
            { key: 'live_rates', name: 'Актуальные ставки', description: 'Блок с актуальными ставками банков' },
            { key: 'document_checklist', name: 'Чек-лист документов', description: 'Интерактивный список необходимых документов' },
            { key: 'timeline', name: 'Временная шкала', description: 'Timeline процесса получения гарантии' },
            { key: 'cost_breakdown', name: 'Разбор стоимости', description: 'Детальный разбор стоимости гарантии' },
            { key: 'bank_rating', name: 'Рейтинг банков', description: 'Рейтинг банков по надежности и условиям' }
        ];
    },
    
    testWithModules: function() {
        const keyword = 'Что такое банковская гарантия';
        const userInstruction = 'Информационный; владельцы ИП/ООО впервые сталкиваются с требованиями. Отстройка: простая визуальная модель «кто кому что должен».';
        const tableOfContents = [
            {
                heading: 'Определение и участники',
                subheadings: ['Банк', 'Принципал', 'Бенефициар'],
                key_points: ['Основные понятия', 'Роли участников', 'Взаимодействие'],
                target_words: 300
            },
            {
                heading: 'Как это работает',
                subheadings: ['Заявка', 'Исполнение', 'Гарантийные обязательства'],
                key_points: ['Процесс получения', 'Этапы оформления', 'Механизм действия'],
                target_words: 400
            },
            {
                heading: 'Когда без гарантии не обойтись',
                subheadings: ['Обязательные случаи', 'Добровольное использование'],
                key_points: ['Законодательные требования', 'Практические ситуации'],
                target_words: 350
            },
            {
                heading: 'Что проверяет банк перед выдачей',
                subheadings: ['Финансовая проверка', 'Документооборот', 'Оценка рисков'],
                key_points: ['Критерии оценки', 'Необходимые документы', 'Процедура проверки'],
                target_words: 300
            },
            {
                heading: 'Стоимость и факторы ценообразования',
                subheadings: ['Базовые ставки', 'Дополнительные факторы', 'Способы экономии'],
                key_points: ['Структура стоимости', 'Влияющие факторы', 'Оптимизация расходов'],
                target_words: 350
            },
            {
                heading: 'Ошибки новичков и как их избежать',
                subheadings: ['Типичные ошибки', 'Рекомендации', 'Лучшие практики'],
                key_points: ['Частые проблемы', 'Советы экспертов', 'Предотвращение ошибок'],
                target_words: 300
            },
            {
                heading: 'Мини‑кейс: простой контракт поставки',
                subheadings: ['Описание ситуации', 'Решение', 'Результат'],
                key_points: ['Практический пример', 'Пошаговое решение', 'Достигнутые результаты'],
                target_words: 400
            },
            {
                heading: 'Итоги и чек‑лист «нужно/не нужно»',
                subheadings: ['Ключевые выводы', 'Практические рекомендации'],
                key_points: ['Основные принципы', 'Контрольный список', 'Следующие шаги'],
                target_words: 250
            }
        ];
        const modules = ['calculator', 'schema_diagram', 'comparison_table'];
        
        return this.generateWithModules(keyword, userInstruction, tableOfContents, modules);
    }
};
