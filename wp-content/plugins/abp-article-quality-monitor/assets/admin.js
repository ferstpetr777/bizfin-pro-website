/**
 * ABP Article Quality Monitor - Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    // Переключение вкладок
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Убираем активный класс со всех вкладок
        $('.nav-tab').removeClass('nav-tab-active');
        
        // Добавляем активный класс к текущей вкладке
        $(this).addClass('nav-tab-active');
        
        // Скрываем все содержимое вкладок
        $('.abp-tab-content').hide();
        
        // Показываем содержимое выбранной вкладки
        var target = $(this).attr('href');
        $(target).show();
    });
    
    // Функциональность "Выбрать все" для проблемных статей
    $('#select-all-problem-posts').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('#problem-posts .post-checkbox').prop('checked', isChecked);
    });
    
    // Функциональность "Выбрать все" для всех статей
    $('#select-all-posts').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('#all-posts .post-checkbox').prop('checked', isChecked);
    });
    
    // Комплексная оптимизация всех проблемных статей
    $('#abp-bulk-optimize').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $progress = $('#abp-bulk-progress');
        
        $button.prop('disabled', true).text('Оптимизация...');
        $progress.show();
        
        // Запускаем оптимизацию
        $.ajax({
            url: abpQuality.ajaxUrl,
            type: 'POST',
            data: {
                action: 'abp_bulk_optimize',
                nonce: abpQuality.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    const message = `Оптимизация завершена! Обработано: ${data.total_processed}, Успешно: ${data.success_count}, Ошибок: ${data.error_count}`;
                    showNotice('success', message);
                    updateProgress(100);
                    
                    // Обновляем страницу через 3 секунды
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    showNotice('error', 'Ошибка при оптимизации: ' + (response.data || 'Неизвестная ошибка'));
                }
            },
            error: function(xhr, status, error) {
                showNotice('error', 'Ошибка при выполнении запроса: ' + error);
            },
            complete: function() {
                $button.prop('disabled', false).text('🔧 Комплексная оптимизация всех проблемных статей');
                $progress.hide();
            }
        });
    });
    
    // Оптимизация выбранных статей из вкладки "Все статьи"
    $('#abp-bulk-optimize-all').on('click', function(e) {
        e.preventDefault();
        
        var selectedPosts = [];
        $('#all-posts .post-checkbox:checked').each(function() {
            selectedPosts.push($(this).val());
        });
        
        if (selectedPosts.length === 0) {
            showNotice('warning', 'Выберите статьи для оптимизации');
            return;
        }
        
        var $button = $(this);
        var $progress = $('#abp-bulk-progress-all');
        
        $button.prop('disabled', true).text('Оптимизация...');
        $progress.show();
        
        // Запускаем оптимизацию для каждой выбранной статьи
        var completed = 0;
        var total = selectedPosts.length;
        
        selectedPosts.forEach(function(postId) {
            $.ajax({
                url: abpQuality.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'abp_optimize_single',
                    post_id: postId,
                    nonce: abpQuality.nonce
                },
                success: function(response) {
                    completed++;
                    updateProgress((completed / total) * 100);
                    
                    if (completed === total) {
                        showNotice('success', `Оптимизация завершена! Обработано статей: ${total}`);
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                },
                error: function() {
                    completed++;
                    if (completed === total) {
                        showNotice('error', 'Произошли ошибки при оптимизации');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                }
            });
        });
    });
    
    // Оптимизация отдельной статьи (делегированная обработка событий)
        $(document).on('click', '.abp-optimize-single', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var postId = $button.data('post-id');
            var $row = $button.closest('tr');
        
        $button.prop('disabled', true).text('Оптимизация...');
        $row.addClass('abp-loading');
        
        // Запускаем отслеживание прогресса
        startProgressTracking(postId);
        
        console.log('ABP Quality Monitor: Starting AJAX request for post', postId);
        console.log('ABP Quality Monitor: AJAX URL:', abpQuality.ajaxUrl);
        console.log('ABP Quality Monitor: Nonce:', abpQuality.nonce);
        
        $.ajax({
            url: abpQuality.ajaxUrl,
            type: 'POST',
            data: {
                action: 'abp_optimize_single',
                post_id: postId,
                nonce: abpQuality.nonce
            },
            success: function(response) {
                console.log('ABP Quality Monitor: AJAX success response:', response);
                stopProgressTracking(postId);
                
                if (response.success) {
                    console.log('ABP Quality Monitor: Optimization successful for post', postId);
                    showNotice('success', 'Статья ID ' + postId + ' успешно оптимизирована!');
                    
                    // Обновляем строку таблицы с новыми данными
                    updateTableRow(postId, response.data.quality_data);
                    
                } else {
                    console.log('ABP Quality Monitor: Optimization failed for post', postId, response.data);
                    showNotice('error', 'Ошибка при оптимизации статьи ID ' + postId + ': ' + (response.data || 'Неизвестная ошибка'));
                    setProgressError(postId);
                }
            },
            error: function(xhr, status, error) {
                console.log('ABP Quality Monitor: AJAX error:', xhr, status, error);
                console.log('ABP Quality Monitor: Response text:', xhr.responseText);
                stopProgressTracking(postId);
                setProgressError(postId);
                showNotice('error', 'Ошибка при выполнении запроса для статьи ID ' + postId + ': ' + error);
            },
            complete: function() {
                $button.prop('disabled', false).text('Оптимизировать');
                $row.removeClass('abp-loading');
            }
        });
    });
    
    // Автообновление статистики каждые 30 секунд
    setInterval(function() {
        updateStats();
    }, 30000);
    
    // Функция обновления статистики
    function updateStats() {
        $.ajax({
            url: abpQuality.ajaxUrl,
            type: 'POST',
            data: {
                action: 'abp_get_quality_stats',
                nonce: abpQuality.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStatsDisplay(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log('Ошибка обновления статистики:', error);
            }
        });
    }
    
    // Функция обновления отображения статистики
    function updateStatsDisplay(stats) {
        // Обновляем числа в карточках
        $('.abp-stat-number').each(function() {
            var $this = $(this);
            var cardType = $this.closest('.abp-stat-card').find('h3').text().toLowerCase();
            
            switch(cardType) {
                case 'всего статей':
                    $this.text(stats.total_posts);
                    break;
                case 'качественные':
                    $this.text(stats.quality_posts);
                    $this.next('.abp-stat-percent').text(stats.quality_percent + '%');
                    break;
                case 'требуют доработки':
                    $this.text(stats.problem_posts);
                    $this.next('.abp-stat-percent').text(stats.problem_percent + '%');
                    break;
                case 'ai-категоризированы':
                    $this.text(stats.ai_categorized);
                    $this.next('.abp-stat-percent').text(stats.ai_percent + '%');
                    break;
            }
        });
        
        // Обновляем таблицу детальной статистики
        $('.abp-stats-table tbody tr').each(function() {
            var $row = $(this);
            var criterion = $row.find('td:first').text();
            
            switch(criterion) {
                case 'AI-категоризация':
                    $row.find('td:eq(2)').text(stats.ai_categorized);
                    $row.find('td:eq(3)').text(stats.ai_missing);
                    $row.find('td:eq(4)').text(stats.ai_percent + '%');
                    break;
                case 'SEO-оптимизация':
                    $row.find('td:eq(2)').text(stats.seo_optimized);
                    $row.find('td:eq(3)').text(stats.seo_missing);
                    $row.find('td:eq(4)').text(stats.seo_percent + '%');
                    break;
                case 'Алфавитная система':
                    $row.find('td:eq(2)').text(stats.alphabet_correct);
                    $row.find('td:eq(3)').text(stats.alphabet_missing);
                    $row.find('td:eq(4)').text(stats.alphabet_percent + '%');
                    break;
            }
        });
    }
    
    // Функция обновления прогресс-бара
    function updateProgress(percent) {
        $('.abp-progress-fill').css('width', percent + '%');
        $('.abp-progress-text').text('Обработано: ' + percent + '%');
    }
    
    // Функция показа уведомлений
    function showNotice(type, message) {
        console.log('ABP Quality Monitor: Showing notice:', type, message);
        
        var $notice = $('<div class="abp-notice abp-notice-' + type + '">' + message + '</div>');
        
        // Удаляем старые уведомления
        $('.abp-notice').remove();
        
        // Добавляем новое уведомление
        var $dashboard = $('.abp-quality-dashboard');
        if ($dashboard.length === 0) {
            $dashboard = $('.wrap');
        }
        $dashboard.prepend($notice);
        
        console.log('ABP Quality Monitor: Notice added to page');
        
        // Автоматически скрываем через 5 секунд
        setTimeout(function() {
            $notice.fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Функция для обновления детальных SEO колонок
    function updateSeoColumns(row, qualityData) {
        console.log('ABP Quality Monitor: Updating SEO columns with data:', qualityData);
        
        // SEO Title (4-я колонка)
        const seoTitleCell = row.find('td:nth-child(4)');
        if (qualityData.seo_details && qualityData.seo_details.seo_title) {
            if (qualityData.seo_details.seo_title.status === 'ok') {
                seoTitleCell.html(`<span class="abp-status-ok" title="${qualityData.seo_details.seo_title.value}">✅</span>`);
            } else {
                seoTitleCell.html('<span class="abp-status-missing" title="Отсутствует SEO title">❌</span>');
            }
        }
        
        // Meta Description (5-я колонка)
        const metaDescCell = row.find('td:nth-child(5)');
        if (qualityData.seo_details && qualityData.seo_details.meta_desc) {
            if (qualityData.seo_details.meta_desc.status === 'ok') {
                const shortDesc = qualityData.seo_details.meta_desc.value.substring(0, 50) + '...';
                metaDescCell.html(`<span class="abp-status-ok" title="${shortDesc}">✅</span>`);
            } else {
                metaDescCell.html('<span class="abp-status-missing" title="Отсутствует meta description">❌</span>');
            }
        }
        
        // Focus Keyword (6-я колонка)
        const focusKwCell = row.find('td:nth-child(6)');
        if (qualityData.seo_details && qualityData.seo_details.focus_kw) {
            if (qualityData.seo_details.focus_kw.status === 'ok') {
                focusKwCell.html(`<span class="abp-status-ok" title="${qualityData.seo_details.focus_kw.value}">✅</span>`);
            } else {
                focusKwCell.html('<span class="abp-status-missing" title="Отсутствует focus keyword">❌</span>');
            }
        }
        
        // Canonical URL (7-я колонка)
        const canonicalCell = row.find('td:nth-child(7)');
        if (qualityData.seo_details && qualityData.seo_details.canonical) {
            if (qualityData.seo_details.canonical.status === 'ok') {
                canonicalCell.html(`<span class="abp-status-ok" title="${qualityData.seo_details.canonical.value}">✅</span>`);
            } else {
                canonicalCell.html('<span class="abp-status-missing" title="Отсутствует canonical URL">❌</span>');
            }
        }
        
        // Meta Description Keyword Match (8-я колонка)
        const metaDescKwCell = row.find('td:nth-child(8)');
        if (qualityData.meta_desc_keyword_status) {
            if (qualityData.meta_desc_keyword_status === 'ok') {
                metaDescKwCell.html('<span class="abp-status-ok" title="Meta Description начинается с ключевого слова">✅</span>');
            } else {
                metaDescKwCell.html('<span class="abp-status-missing" title="Meta Description не начинается с ключевого слова">❌</span>');
            }
        }
        
        // Title Keyword Match (9-я колонка)
        const titleKwCell = row.find('td:nth-child(9)');
        if (qualityData.title_keyword_match_status) {
            if (qualityData.title_keyword_match_status === 'ok') {
                titleKwCell.html('<span class="abp-status-ok" title="Заголовок соответствует ключевому слову">✅</span>');
            } else {
                titleKwCell.html('<span class="abp-status-missing" title="Заголовок не соответствует ключевому слову">❌</span>');
            }
        }
        
        console.log('ABP Quality Monitor: SEO columns updated');
    }
    
    // Функция для создания графика истории проверок
    function createHistoryChart() {
        // Здесь можно добавить код для создания графика с помощью Chart.js или другой библиотеки
        var $chartContainer = $('#abp-history-chart');
        
        // Пока что показываем заглушку
        $chartContainer.html('<p>📊 График истории проверок будет здесь</p><p><em>Функция в разработке</em></p>');
    }
    
    // Инициализация графика при загрузке страницы
    createHistoryChart();
    
    // Обработка ошибок AJAX
    $(document).ajaxError(function(event, xhr, settings, thrownError) {
        if (settings.url.indexOf('abp_') !== -1) {
            showNotice('error', 'Ошибка соединения с сервером. Проверьте подключение к интернету.');
        }
    });
    
    // Подтверждение действий (удалено - теперь обрабатывается в основном обработчике)
    
    // Добавляем индикатор загрузки для всех AJAX запросов
    $(document).ajaxStart(function() {
        $('body').addClass('abp-loading');
    }).ajaxStop(function() {
        $('body').removeClass('abp-loading');
    });
    
    // Функция для экспорта статистики
    function exportStats() {
        var stats = {
            timestamp: new Date().toISOString(),
            total_posts: $('.abp-stat-card:first .abp-stat-number').text(),
            quality_posts: $('.abp-stat-card:nth-child(2) .abp-stat-number').text(),
            problem_posts: $('.abp-stat-card:nth-child(3) .abp-stat-number').text(),
            ai_categorized: $('.abp-stat-card:nth-child(4) .abp-stat-number').text()
        };
        
        var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(stats, null, 2));
        var downloadAnchorNode = document.createElement('a');
        downloadAnchorNode.setAttribute("href", dataStr);
        downloadAnchorNode.setAttribute("download", "abp-quality-stats-" + new Date().toISOString().split('T')[0] + ".json");
        document.body.appendChild(downloadAnchorNode);
        downloadAnchorNode.click();
        downloadAnchorNode.remove();
        
        showNotice('success', 'Статистика экспортирована!');
    }
    
    // Добавляем кнопку экспорта (если нужно)
    if ($('#abp-export-stats').length === 0) {
        $('.abp-detailed-stats h2').after('<button id="abp-export-stats" class="button button-secondary">📊 Экспорт статистики</button>');
        $('#abp-export-stats').on('click', exportStats);
    }
    
    // Функция для фильтрации таблицы проблемных статей
    function filterProblemPosts(filter) {
        var $rows = $('.abp-problem-posts tbody tr');
        
        $rows.each(function() {
            var $row = $(this);
            var issues = $row.find('td:nth-child(6)').text().toLowerCase();
            
            if (filter === 'all' || issues.indexOf(filter) !== -1) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    }
    
    // Добавляем фильтры (если нужно)
    if ($('#abp-filter-controls').length === 0 && $('.abp-problem-posts tbody tr').length > 0) {
        var filterHtml = '<div id="abp-filter-controls" style="margin: 10px 0;">' +
            '<label for="abp-issue-filter">Фильтр по проблемам: </label>' +
            '<select id="abp-issue-filter">' +
            '<option value="all">Все проблемы</option>' +
            '<option value="ai">AI-категория</option>' +
            '<option value="seo">SEO</option>' +
            '<option value="алфавит">Алфавит</option>' +
            '</select>' +
            '</div>';
        
        $('.abp-problem-posts h2').after(filterHtml);
        
        $('#abp-issue-filter').on('change', function() {
            filterProblemPosts($(this).val());
        });
    }
    
    // Функция для массового выбора статей
    function toggleSelectAll() {
        var $checkboxes = $('.abp-post-checkbox');
        var $selectAllCheckbox = $('#abp-select-all');
        
        if ($selectAllCheckbox.prop('checked')) {
            $checkboxes.prop('checked', true);
        } else {
            $checkboxes.prop('checked', false);
        }
    }
    
    // Добавляем чекбоксы для массового выбора (если нужно)
    if ($('.abp-problem-posts tbody tr').length > 0 && $('.abp-post-checkbox').length === 0) {
        // Добавляем заголовок с чекбоксом
        $('.abp-problem-posts thead th:first').html('<input type="checkbox" id="abp-select-all">');
        
        // Добавляем чекбоксы к строкам
        $('.abp-problem-posts tbody tr').each(function() {
            $(this).find('td:first').html('<input type="checkbox" class="abp-post-checkbox" value="' + $(this).find('td:first').text() + '"> ' + $(this).find('td:first').text());
        });
        
        // Обработчик для выбора всех
        $('#abp-select-all').on('change', toggleSelectAll);
    }
    
    // Функция для массовой оптимизации выбранных статей
    function optimizeSelected() {
        var selectedIds = [];
        $('.abp-post-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if (selectedIds.length === 0) {
            showNotice('error', 'Выберите статьи для оптимизации');
            return;
        }
        
        if (!confirm('Оптимизировать выбранные статьи (' + selectedIds.length + ' шт.)?')) {
            return;
        }
        
        // Здесь можно добавить AJAX запрос для массовой оптимизации
        showNotice('info', 'Массовая оптимизация выбранных статей будет реализована в следующей версии');
    }
    
    // Добавляем кнопку массовой оптимизации (если есть чекбоксы)
    if ($('.abp-post-checkbox').length > 0 && $('#abp-optimize-selected').length === 0) {
        $('.abp-bulk-actions').append('<button id="abp-optimize-selected" class="button button-secondary">Оптимизировать выбранные</button>');
        $('#abp-optimize-selected').on('click', optimizeSelected);
    }
    
    // Функции для отслеживания прогресса
    let progressTimers = {};
    
    function startProgressTracking(postId) {
        console.log('ABP Quality Monitor: Starting progress tracking for post', postId);
        
        const progressIndicator = $(`.abp-progress-indicator[data-post-id="${postId}"]`);
        const progressText = progressIndicator.find('.abp-progress-text');
        
        console.log('ABP Quality Monitor: Progress indicator found:', progressIndicator.length);
        console.log('ABP Quality Monitor: Progress text found:', progressText.length);
        
        if (progressIndicator.length === 0) {
            console.log('ABP Quality Monitor: Progress indicator not found for post', postId);
            return;
        }
        
        progressText.removeClass('abp-progress-success abp-progress-error').addClass('abp-progress-active');
        progressText.text('0с');
        
        let seconds = 0;
        progressTimers[postId] = setInterval(function() {
            seconds++;
            progressText.text(seconds + 'с');
        }, 1000);
        
        console.log('ABP Quality Monitor: Progress tracking started for post', postId);
    }
    
    function stopProgressTracking(postId) {
        console.log('ABP Quality Monitor: Stopping progress tracking for post', postId);
        
        if (progressTimers[postId]) {
            clearInterval(progressTimers[postId]);
            delete progressTimers[postId];
        }
        
        const progressIndicator = $(`.abp-progress-indicator[data-post-id="${postId}"]`);
        const progressText = progressIndicator.find('.abp-progress-text');
        
        console.log('ABP Quality Monitor: Progress indicator found for stop:', progressIndicator.length);
        
        if (progressIndicator.length > 0) {
            progressText.removeClass('abp-progress-active').addClass('abp-progress-success');
            progressText.text('✓');
            
            // Через 3 секунды возвращаем к исходному состоянию
            setTimeout(function() {
                progressText.removeClass('abp-progress-success').text('—');
            }, 3000);
        }
    }
    
    function setProgressError(postId) {
        if (progressTimers[postId]) {
            clearInterval(progressTimers[postId]);
            delete progressTimers[postId];
        }
        
        const progressIndicator = $(`.abp-progress-indicator[data-post-id="${postId}"]`);
        const progressText = progressIndicator.find('.abp-progress-text');
        
        progressText.removeClass('abp-progress-active').addClass('abp-progress-error');
        progressText.text('✗');
        
        // Через 5 секунд возвращаем к исходному состоянию
        setTimeout(function() {
            progressText.removeClass('abp-progress-error').text('—');
        }, 5000);
    }
    
    // Функция обновления строки таблицы
    function updateTableRow(postId, qualityData) {
        console.log('ABP Quality Monitor: Updating table row for post', postId, qualityData);
        
        const row = $(`tr:has(.abp-optimize-single[data-post-id="${postId}"])`);
        console.log('ABP Quality Monitor: Found row:', row.length);
        
        if (row.length === 0) {
            console.log('ABP Quality Monitor: Row not found for post', postId);
            return;
        }
        
        // Обновляем статусы AI-категории
        const aiCell = row.find('td:nth-child(3)');
        console.log('ABP Quality Monitor: AI status:', qualityData.ai_category_status);
        console.log('ABP Quality Monitor: AI cell found:', aiCell.length);
        if (qualityData.ai_category_status === 'ok') {
            aiCell.html('<span class="abp-status-ok">✅</span>');
            console.log('ABP Quality Monitor: AI status updated to OK');
        } else {
            aiCell.html('<span class="abp-status-missing">❌</span>');
            console.log('ABP Quality Monitor: AI status updated to MISSING');
        }
        
        // Обновляем детальные SEO статусы
        updateSeoColumns(row, qualityData);
        
        // Обновляем статусы алфавита (теперь 10-я колонка)
        const alphabetCell = row.find('td:nth-child(10)');
        console.log('ABP Quality Monitor: Alphabet status:', qualityData.alphabet_system_status);
        console.log('ABP Quality Monitor: Alphabet cell found:', alphabetCell.length);
        if (qualityData.alphabet_system_status === 'ok') {
            alphabetCell.html('<span class="abp-status-ok">✅</span>');
            console.log('ABP Quality Monitor: Alphabet status updated to OK');
        } else {
            alphabetCell.html('<span class="abp-status-missing">❌</span>');
            console.log('ABP Quality Monitor: Alphabet status updated to MISSING');
        }
        
        // Обновляем проблемы (теперь 11-я колонка)
        const issuesCell = row.find('td:nth-child(11)');
        console.log('ABP Quality Monitor: Issues cell found:', issuesCell.length);
        console.log('ABP Quality Monitor: Issues text:', qualityData.issues);
        issuesCell.text(qualityData.issues || 'Нет проблем');
        console.log('ABP Quality Monitor: Issues cell updated');
        
        // Если все проблемы решены, скрываем строку
        if (qualityData.overall_status === 'ok') {
            row.addClass('abp-optimized');
            setTimeout(function() {
                row.fadeOut(500, function() {
                    $(this).remove();
                    // Обновляем счетчик проблемных статей
                    updateProblemCount();
                });
            }, 2000);
        }
    }
    
    // Функция обновления счетчика проблемных статей
    function updateProblemCount() {
        const remainingRows = $('.abp-problem-posts tbody tr').length;
        const headerText = $('.abp-problem-posts h2').text();
        const newText = headerText.replace(/\(\d+\)/, `(${remainingRows})`);
        $('.abp-problem-posts h2').text(newText);
        
        // Если проблемных статей не осталось, показываем сообщение
        if (remainingRows === 0) {
            $('.abp-problem-posts').html(`
                <div class="abp-no-problems">
                    <h2>🎉 Отлично! Все статьи оптимизированы</h2>
                    <p>У вас нет статей, требующих доработки.</p>
                </div>
            `);
        }
    }
    
    console.log('ABP Article Quality Monitor admin script loaded successfully');
});



