jQuery(document).ready(function($) {
    let articlesData = [];
    let fullAnalysisData = {};
    let currentSort = { column: null, direction: 'asc' };
    let selectedPosts = [];
    
    // Загрузка статей
    function loadArticles(search = '') {
        $.ajax({
            url: aarData.ajax_url,
            method: 'POST',
            data: {
                action: 'aar_get_articles',
                nonce: aarData.nonce,
                search: search
            },
            success: function(response) {
                if (response.success) {
                    articlesData = response.data;
                    renderTable();
                } else {
                    alert('Ошибка загрузки статей');
                }
            }
        });
    }
    
    // Поиск
    $('#aar-search-btn').on('click', function() {
        const search = $('#aar-search').val();
        loadArticles(search);
    });
    
    // Enter в поиске
    $('#aar-search').on('keypress', function(e) {
        if (e.which === 13) {
            $('#aar-search-btn').click();
        }
    });
    
    // Выбрать все / Снять все
    $('#aar-select-all').on('click', function() {
        $('.aar-post-checkbox').prop('checked', true);
        updateSelectedPosts();
    });
    
    $('#aar-deselect-all').on('click', function() {
        $('.aar-post-checkbox').prop('checked', false);
        selectedPosts = [];
        updateSelectedPosts();
    });
    
    // Чекбокс статьи
    $(document).on('change', '.aar-post-checkbox', function() {
        updateSelectedPosts();
    });
    
    function updateSelectedPosts() {
        selectedPosts = [];
        $('.aar-post-checkbox:checked').each(function() {
            selectedPosts.push($(this).data('post-id'));
        });
        updateBulkButtons();
    }
    
    function updateBulkButtons() {
        $('.aar-bulk-criteria-btn .count').text(selectedPosts.length);
    }
    
    // Регенерация статьи
    $(document).on('click', '.aar-btn-regenerate', function() {
        const postId = $(this).data('post-id');
        
        if (!confirm('Вы уверены, что хотите регенерировать эту статью?')) {
            return;
        }
        
        $(this).prop('disabled', true).text('Регенерация...');
        
        $.ajax({
            url: aarData.ajax_url,
            method: 'POST',
            data: {
                action: 'aar_regenerate_article',
                nonce: aarData.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    alert('Статья успешно регенерирована!');
                    loadArticles($('#aar-search').val());
                } else {
                    alert('Ошибка регенерации: ' + (response.data || 'Неизвестная ошибка'));
                }
            },
            error: function() {
                alert('Ошибка при регенерации статьи');
            },
            complete: function() {
                $('.aar-btn-regenerate[data-post-id="' + postId + '"]')
                    .prop('disabled', false)
                    .text('Регенерировать');
            }
        });
    });
    
    // Исправление битых ссылок
    $(document).on('click', '.aar-btn-fix-links', function() {
        const postId = $(this).data('post-id');
        
        if (!confirm('Исправить битые ссылки в этой статье?')) {
            return;
        }
        
        $(this).prop('disabled', true).text('Исправление...');
        
        $.ajax({
            url: aarData.ajax_url,
            method: 'POST',
            data: {
                action: 'aar_fix_broken_links',
                nonce: aarData.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    loadArticles($('#aar-search').val());
                } else {
                    alert('Ошибка: ' + (response.data || 'Неизвестная ошибка'));
                }
            },
            complete: function() {
                $('.aar-btn-fix-links[data-post-id="' + postId + '"]')
                    .prop('disabled', false)
                    .text('Исправить ссылки');
            }
        });
    });
    
    // Массовая обработка по критерию
    $(document).on('click', '.aar-bulk-criteria-btn', function() {
        if (selectedPosts.length === 0) {
            alert('Выберите статьи для обработки');
            return;
        }
        
        const action = $(this).data('action');
        const actionText = getActionName(action);
        
        if (!confirm(`Вы уверены, что хотите исправить критерий "${actionText}" для ${selectedPosts.length} статей?`)) {
            return;
        }
        
        $.ajax({
            url: aarData.ajax_url,
            method: 'POST',
            data: {
                action: 'aar_bulk_process',
                nonce: aarData.nonce,
                post_ids: selectedPosts,
                bulk_action: action
            },
            success: function(response) {
                if (response.success) {
                    alert('Обработка завершена!');
                    loadArticles($('#aar-search').val());
                } else {
                    alert('Ошибка: ' + (response.data || 'Неизвестная ошибка'));
                }
            }
        });
    });
    
    // Анализ статьи
    function analyzeArticle(postId, callback) {
        $.ajax({
            url: aarData.ajax_url,
            method: 'POST',
            data: {
                action: 'aar_analyze_article',
                nonce: aarData.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success && callback) {
                    callback(response.data);
                }
            }
        });
    }
    
    function getActionName(action) {
        const names = {
            'intro_definition': 'Определение',
            'example': 'Пример',
            'toc': 'Оглавление',
            'links': 'Ссылки',
            'faq': 'FAQ',
            'cta': 'CTA',
            'duplicate_h1': 'Дубли H1',
            'visible_html': 'Видимый HTML',
            'fix_links': 'Битые ссылки'
        };
        return names[action] || action;
    }
    
    // Сортировка таблицы
    function sortTable(column) {
        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.direction = 'asc';
        }
        
        renderTable();
    }
    
    // Клик по заголовку для сортировки
    $(document).on('click', '.aar-sortable-header', function() {
        const column = $(this).data('column');
        sortTable(column);
    });
    
    // Функция сортировки данных
    function sortArticles() {
        if (!currentSort.column) {
            return articlesData;
        }
        
        const sorted = [...articlesData].sort((a, b) => {
            let aVal, bVal;
            
            switch(currentSort.column) {
                case 'id':
                    aVal = a.id;
                    bVal = b.id;
                    break;
                case 'title':
                    aVal = a.title.toLowerCase();
                    bVal = b.title.toLowerCase();
                    break;
                case 'word_count':
                    aVal = a.word_count;
                    bVal = b.word_count;
                    break;
                case 'intro_definition':
                case 'example':
                case 'toc':
                case 'links':
                case 'faq':
                case 'cta':
                case 'duplicate_h1':
                case 'visible_html':
                    const aAnalysis = fullAnalysisData[a.id];
                    const bAnalysis = fullAnalysisData[b.id];
                    if (!aAnalysis || !bAnalysis) return 0;
                    
                    const criteriaMap = {
                        'intro_definition': 'has_intro_definition',
                        'example': 'has_example',
                        'toc': 'has_toc',
                        'links': 'has_internal_links',
                        'faq': 'has_faq',
                        'cta': 'has_cta',
                        'duplicate_h1': 'has_no_duplicate_h1',
                        'visible_html': 'has_no_visible_html'
                    };
                    
                    aVal = aAnalysis.criteria[criteriaMap[currentSort.column]] ? 1 : 0;
                    bVal = bAnalysis.criteria[criteriaMap[currentSort.column]] ? 1 : 0;
                    break;
                case 'compliance_score':
                    aVal = fullAnalysisData[a.id] ? fullAnalysisData[a.id].compliance_score : 0;
                    bVal = fullAnalysisData[b.id] ? fullAnalysisData[b.id].compliance_score : 0;
                    break;
                default:
                    return 0;
            }
            
            if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
            if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
            return 0;
        });
        
        return sorted;
    }
    
    // Отображение таблицы
    function renderTable() {
        const sortedArticles = sortArticles();
        
        let html = '';
        
        // Кнопки массовой обработки по критериям
        html += '<div class="aar-criteria-buttons">';
        html += '<div class="aar-criteria-row">';
        html += '<button class="aar-bulk-criteria-btn" data-action="intro_definition">Определение (<span class="count">0</span>)</button>';
        html += '<button class="aar-bulk-criteria-btn" data-action="example">Пример (<span class="count">0</span>)</button>';
        html += '<button class="aar-bulk-criteria-btn" data-action="toc">Оглавление (<span class="count">0</span>)</button>';
        html += '<button class="aar-bulk-criteria-btn" data-action="links">Ссылки (<span class="count">0</span>)</button>';
        html += '</div>';
        html += '<div class="aar-criteria-row">';
        html += '<button class="aar-bulk-criteria-btn" data-action="faq">FAQ (<span class="count">0</span>)</button>';
        html += '<button class="aar-bulk-criteria-btn" data-action="cta">CTA (<span class="count">0</span>)</button>';
        html += '<button class="aar-bulk-criteria-btn" data-action="duplicate_h1">Дубли H1 (<span class="count">0</span>)</button>';
        html += '<button class="aar-bulk-criteria-btn" data-action="visible_html">Видимый HTML (<span class="count">0</span>)</button>';
        html += '<button class="aar-bulk-criteria-btn" data-action="fix_links">Битые ссылки (<span class="count">0</span>)</button>';
        html += '</div>';
        html += '</div>';
        
        html += '<table id="aar-articles-table">';
        html += '<thead><tr>';
        html += '<th class="aar-checkbox-col"><input type="checkbox" id="aar-select-all-checkbox"></th>';
        html += '<th class="aar-sortable-header" data-column="id">ID <span class="aar-sort-indicator"></span></th>';
        html += '<th class="aar-sortable-header" data-column="title">Название <span class="aar-sort-indicator"></span></th>';
        html += '<th class="aar-sortable-header" data-column="word_count">Слов <span class="aar-sort-indicator"></span></th>';
        html += '<th class="aar-sortable-header" data-column="intro_definition">Определение <span class="aar-sort-indicator"></span></th>';
        html += '<th class="aar-sortable-header" data-column="example">Пример <span class="aar-sort-indicator"></span></th>';
        html += '<th class="aar-sortable-header" data-column="toc">Оглавление <span class="aar-sort-indicator"></span></th>';
        html += '<th class="aar-sortable-header" data-column="links">Ссылки <span class="aar-sort-indicator"></span></th>';
        html += '<th class="aar-sortable-header" data-column="faq">FAQ <span class="aar-sort-indicator"></span></th>';
        html += '<th class="aar-sortable-header" data-column="cta">CTA <span class="aar-sort-indicator"></span></th>';
        html += '<th class="aar-sortable-header" data-column="duplicate_h1">Нет H1 <span class="aar-sort-indicator"></span></th>';
        html += '<th class="aar-sortable-header" data-column="visible_html">Нет HTML <span class="aar-sort-indicator"></span></th>';
        html += '<th class="aar-sortable-header" data-column="compliance_score">Соответствие <span class="aar-sort-indicator"></span></th>';
        html += '<th>Действия</th>';
        html += '</tr></thead><tbody>';
        
        sortedArticles.forEach(function(article) {
            const articleUrl = aarData.site_url + '/?p=' + article.id;
            html += '<tr>';
            html += '<td><input type="checkbox" class="aar-post-checkbox" data-post-id="' + article.id + '"></td>';
            html += '<td><a href="' + articleUrl + '" target="_blank">' + article.id + '</a></td>';
            html += '<td><a href="/wp-admin/post.php?post=' + article.id + '&action=edit">' + (article.title.length > 40 ? article.title.substring(0, 40) + '...' : article.title) + '</a></td>';
            html += '<td>' + article.word_count + '</td>';
            html += '<td data-post-id="' + article.id + '" class="aar-analyze-cell">Анализ...</td>';
            html += '<td data-post-id="' + article.id + '" class="aar-analyze-cell">Анализ...</td>';
            html += '<td data-post-id="' + article.id + '" class="aar-analyze-cell">Анализ...</td>';
            html += '<td data-post-id="' + article.id + '" class="aar-analyze-cell">Анализ...</td>';
            html += '<td data-post-id="' + article.id + '" class="aar-analyze-cell">Анализ...</td>';
            html += '<td data-post-id="' + article.id + '" class="aar-analyze-cell">Анализ...</td>';
            html += '<td data-post-id="' + article.id + '" class="aar-analyze-cell">Анализ...</td>';
            html += '<td data-post-id="' + article.id + '" class="aar-analyze-cell">Анализ...</td>';
            html += '<td data-post-id="' + article.id + '" class="aar-analyze-cell">Анализ...</td>';
            html += '<td><button class="aar-btn-regenerate" data-post-id="' + article.id + '">Регенерировать</button> <button class="aar-btn-fix-links" data-post-id="' + article.id + '">Исправить ссылки</button></td>';
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        
        $('#aar-table-container').html(html);
        
        // Обновление индикаторов сортировки
        updateSortIndicators();
        updateBulkButtons();
        
        // Запуск анализа для каждой статьи
        sortedArticles.forEach(function(article) {
            analyzeArticle(article.id, function(analysis) {
                fullAnalysisData[article.id] = analysis;
                const criteria = analysis.criteria;
                const row = $('tr:has(td[data-post-id="' + analysis.id + '"]:first)');
                
                row.find('td:nth-child(5)').html(getIcon(criteria.has_intro_definition));
                row.find('td:nth-child(6)').html(getIcon(criteria.has_example));
                row.find('td:nth-child(7)').html(getIcon(criteria.has_toc));
                row.find('td:nth-child(8)').html(getIcon(criteria.has_internal_links));
                row.find('td:nth-child(9)').html(getIcon(criteria.has_faq));
                row.find('td:nth-child(10)').html(getIcon(criteria.has_cta));
                row.find('td:nth-child(11)').html(getIcon(criteria.has_no_duplicate_h1));
                row.find('td:nth-child(12)').html(getIcon(criteria.has_no_visible_html));
                
                const badgeClass = analysis.compliance_score >= 80 ? 'high' : 
                                  analysis.compliance_score >= 50 ? 'medium' : 'low';
                row.find('td:nth-child(13)').html(
                    '<span class="aar-compliance-badge aar-compliance-' + badgeClass + '">' +
                    analysis.compliance_score + '%</span>'
                );
            });
        });
    }
    
    // Обновление индикаторов сортировки
    function updateSortIndicators() {
        $('.aar-sort-indicator').html('');
        
        if (currentSort.column) {
            const header = $('.aar-sortable-header[data-column="' + currentSort.column + '"] .aar-sort-indicator');
            const icon = currentSort.direction === 'asc' ? '↑' : '↓';
            header.html(' ' + icon);
        }
    }
    
    function getIcon(value) {
        const icon = value ? 'yes' : 'no';
        return '<span class="aar-criteria-icon ' + icon + '" title="' + (value ? 'Да' : 'Нет') + '"></span>';
    }
    
    // Загрузка при инициализации
    loadArticles();
});
