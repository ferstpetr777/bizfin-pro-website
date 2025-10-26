/**
 * BizFin SEO Article Generator - Modules JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Инициализация интерфейса модулей
    initializeModulesInterface();
    
    function initializeModulesInterface() {
        // Обработчики событий
        $('#add-toc-item').on('click', addTocItem);
        $(document).on('click', '.remove-toc-item', removeTocItem);
        $('#bsag-modules-form').on('submit', handleFormSubmit);
        $('#test-with-modules').on('click', handleTestWithModules);
        
        // Загрузка статистики модулей
        loadModulesStats();
    }
    
    /**
     * Добавление нового элемента оглавления
     */
    function addTocItem() {
        const tocContainer = $('#table-of-contents');
        const newItem = $(`
            <div class="toc-item">
                <input type="text" name="toc_heading[]" placeholder="Заголовок раздела" class="form-control">
                <textarea name="toc_subheadings[]" placeholder="Подзаголовки (по одному на строку)" class="form-control" rows="2"></textarea>
                <textarea name="toc_key_points[]" placeholder="Ключевые моменты (по одному на строку)" class="form-control" rows="2"></textarea>
                <input type="number" name="toc_target_words[]" placeholder="Целевое количество слов" class="form-control" value="300" min="100" max="1000">
                <button type="button" class="button remove-toc-item">Удалить</button>
            </div>
        `);
        
        tocContainer.append(newItem);
    }
    
    /**
     * Удаление элемента оглавления
     */
    function removeTocItem() {
        $(this).closest('.toc-item').remove();
    }
    
    /**
     * Обработка отправки формы
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        
        const formData = collectFormData();
        
        if (!validateFormData(formData)) {
            return;
        }
        
        generateArticleWithModules(formData);
    }
    
    /**
     * Сбор данных формы
     */
    function collectFormData() {
        const keyword = $('#keyword').val().trim();
        const userInstruction = $('#user_instruction').val().trim();
        const modules = $('input[name="modules[]"]:checked').map(function() {
            return $(this).val();
        }).get();
        
        const tableOfContents = [];
        $('.toc-item').each(function() {
            const heading = $(this).find('input[name="toc_heading[]"]').val().trim();
            const subheadings = $(this).find('textarea[name="toc_subheadings[]"]').val().split('\n').map(s => s.trim()).filter(s => s);
            const keyPoints = $(this).find('textarea[name="toc_key_points[]"]').val().split('\n').map(s => s.trim()).filter(s => s);
            const targetWords = parseInt($(this).find('input[name="toc_target_words[]"]').val()) || 300;
            
            if (heading) {
                tableOfContents.push({
                    heading: heading,
                    subheadings: subheadings,
                    key_points: keyPoints,
                    target_words: targetWords
                });
            }
        });
        
        return {
            keyword: keyword,
            user_instruction: userInstruction,
            table_of_contents: tableOfContents,
            modules: modules
        };
    }
    
    /**
     * Валидация данных формы
     */
    function validateFormData(data) {
        if (!data.keyword) {
            showNotification('Введите ключевое слово', 'error');
            return false;
        }
        
        if (!data.user_instruction) {
            showNotification('Введите инструкцию для статьи', 'error');
            return false;
        }
        
        if (data.table_of_contents.length === 0) {
            showNotification('Добавьте хотя бы один раздел в оглавление', 'error');
            return false;
        }
        
        if (data.modules.length === 0) {
            showNotification('Выберите хотя бы один модуль', 'error');
            return false;
        }
        
        return true;
    }
    
    /**
     * Генерация статьи с модулями
     */
    function generateArticleWithModules(data) {
        showNotification('Генерация статьи с модулями...', 'info');
        
        $.ajax({
            url: bsag_modules_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bsag_generate_with_modules',
                nonce: bsag_modules_ajax.nonce,
                keyword: data.keyword,
                user_instruction: data.user_instruction,
                table_of_contents: data.table_of_contents,
                modules: data.modules
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Статья успешно сгенерирована с модулями!', 'success');
                    displayGenerationResult(response.data);
                } else {
                    showNotification('Ошибка генерации: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('AJAX ошибка: ' + error, 'error');
            }
        });
    }
    
    /**
     * Тестирование с модулями
     */
    function handleTestWithModules() {
        const testData = {
            keyword: 'Что такое банковская гарантия',
            user_instruction: 'Информационный; владельцы ИП/ООО впервые сталкиваются с требованиями. Отстройка: простая визуальная модель «кто кому что должен».',
            table_of_contents: [
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
            ],
            modules: ['calculator', 'schema_diagram', 'comparison_table']
        };
        
        // Заполняем форму тестовыми данными
        $('#keyword').val(testData.keyword);
        $('#user_instruction').val(testData.user_instruction);
        
        // Очищаем существующие элементы оглавления
        $('#table-of-contents').empty();
        
        // Добавляем тестовые элементы оглавления
        testData.table_of_contents.forEach(function(item) {
            const tocItem = $(`
                <div class="toc-item">
                    <input type="text" name="toc_heading[]" placeholder="Заголовок раздела" class="form-control" value="${item.heading}">
                    <textarea name="toc_subheadings[]" placeholder="Подзаголовки (по одному на строку)" class="form-control" rows="2">${item.subheadings.join('\n')}</textarea>
                    <textarea name="toc_key_points[]" placeholder="Ключевые моменты (по одному на строку)" class="form-control" rows="2">${item.key_points.join('\n')}</textarea>
                    <input type="number" name="toc_target_words[]" placeholder="Целевое количество слов" class="form-control" value="${item.target_words}" min="100" max="1000">
                    <button type="button" class="button remove-toc-item">Удалить</button>
                </div>
            `);
            $('#table-of-contents').append(tocItem);
        });
        
        // Выбираем тестовые модули
        $('input[name="modules[]"]').prop('checked', false);
        testData.modules.forEach(function(module) {
            $(`input[name="modules[]"][value="${module}"]`).prop('checked', true);
        });
        
        showNotification('Форма заполнена тестовыми данными. Нажмите "Сгенерировать статью" для тестирования.', 'info');
    }
    
    /**
     * Отображение результата генерации
     */
    function displayGenerationResult(data) {
        const resultHtml = `
            <div class="generation-result">
                <h4>Статья успешно сгенерирована!</h4>
                <div class="result-meta">
                    <p><strong>Ключевое слово:</strong> ${data.keyword}</p>
                    <p><strong>Количество слов:</strong> ${data.word_count}</p>
                    <p><strong>Модули:</strong> ${data.modules_used.join(', ')}</p>
                    <p><strong>Статус интеграции:</strong> ${data.integration_status}</p>
                </div>
                <div class="result-actions">
                    <button type="button" class="button button-primary" onclick="publishGeneratedArticle(${data.post_id})">Опубликовать</button>
                    <button type="button" class="button" onclick="editGeneratedArticle(${data.post_id})">Редактировать</button>
                    <a href="${data.post_url}" class="button" target="_blank">Посмотреть</a>
                </div>
            </div>
        `;
        
        $('#result-content').html(resultHtml);
        $('#generation-result').show();
    }
    
    /**
     * Загрузка статистики модулей
     */
    function loadModulesStats() {
        // Здесь будет загрузка статистики использования модулей
        // Пока что показываем заглушку
        $('#modules-stats-content').html(`
            <div class="stats-placeholder">
                <p>Статистика использования модулей будет доступна после создания первых статей.</p>
                <div class="stats-grid">
                    <div class="stat-item">
                        <strong>Всего статей:</strong> 0
                    </div>
                    <div class="stat-item">
                        <strong>Использовано модулей:</strong> 0
                    </div>
                    <div class="stat-item">
                        <strong>Средний объем статьи:</strong> 0 слов
                    </div>
                </div>
            </div>
        `);
    }
    
    /**
     * Показ уведомлений
     */
    function showNotification(message, type) {
        const notification = $(`
            <div class="notification ${type}">
                ${message}
            </div>
        `);
        
        // Удаляем предыдущие уведомления
        $('.notification').remove();
        
        // Добавляем новое уведомление
        $('.bsag-modules-container').prepend(notification);
        
        // Автоматически скрываем через 5 секунд
        setTimeout(function() {
            notification.fadeOut();
        }, 5000);
    }
    
    /**
     * Публикация сгенерированной статьи
     */
    window.publishGeneratedArticle = function(postId) {
        showNotification('Публикация статьи...', 'info');
        
        $.ajax({
            url: bsag_modules_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bsag_publish_article',
                nonce: bsag_modules_ajax.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Статья успешно опубликована!', 'success');
                } else {
                    showNotification('Ошибка публикации: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('AJAX ошибка: ' + error, 'error');
            }
        });
    };
    
    /**
     * Редактирование сгенерированной статьи
     */
    window.editGeneratedArticle = function(postId) {
        const editUrl = bsag_modules_ajax.ajax_url.replace('admin-ajax.php', 'post.php?post=' + postId + '&action=edit');
        window.open(editUrl, '_blank');
    };
    
    // Добавляем стили для статистики
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }
            
            .stat-item {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 4px;
                border-left: 4px solid #0073aa;
            }
            
            .result-meta p {
                margin: 5px 0;
            }
            
            .result-actions {
                margin-top: 15px;
                display: flex;
                gap: 10px;
            }
        `)
        .appendTo('head');
});

