/**
 * ABP Image Generator Admin JavaScript
 */

(function($) {
    'use strict';

    // Глобальные переменные
    let bulkOperationInProgress = false;
    let currentBulkProgress = 0;
    let totalBulkItems = 0;

    // Инициализация
    $(document).ready(function() {
        initImageGenerator();
        bindEvents();
        loadStats();
    });

    /**
     * Инициализация плагина
     */
    function initImageGenerator() {
        console.log('ABP Image Generator Admin initialized');
        
        // Проверяем интеграцию с системой блога
        checkBlogIntegration();
        
        // Загружаем статистику
        loadStats();
        
        // Инициализируем табы
        initTabs();
    }

    /**
     * Привязка событий
     */
    function bindEvents() {
        // Генерация изображения для отдельного поста
        $(document).on('click', '.abp-generate-image', function(e) {
            e.preventDefault();
            const postId = $(this).data('post-id');
            generateImageForPost(postId, $(this));
        });

        // Массовая генерация
        $(document).on('click', '.abp-bulk-generate', function(e) {
            e.preventDefault();
            bulkGenerateImages();
        });

        // Генерация по букве
        $(document).on('click', '.abp-generate-by-letter', function(e){
            e.preventDefault();
            const letter = ($(this).data('letter') || $('#abp-letter-input').val() || '').trim();
            if (!letter) { showNotice('warning', 'Укажите букву'); return; }
            generateByLetter(letter);
        });

        // Выбор всех постов
        $(document).on('change', '.abp-select-all', function() {
            const isChecked = $(this).is(':checked');
            $('.abp-post-checkbox').prop('checked', isChecked);
            updateBulkActions();
        });

        // Выбор отдельных постов
        $(document).on('change', '.abp-post-checkbox', function() {
            updateBulkActions();
        });

        // Сохранение настроек
        $(document).on('submit', '.abp-settings-form', function(e) {
            e.preventDefault();
            saveSettings();
        });

        // Проверка SEO
        $(document).on('click', '.abp-check-seo', function(e) {
            e.preventDefault();
            const postId = $(this).data('post-id');
            checkPostSEO(postId);
        });

        // Обновление статистики
        $(document).on('click', '.abp-refresh-stats', function(e) {
            e.preventDefault();
            loadStats();
        });

        // Просмотр логов
        $(document).on('click', '.abp-view-logs', function(e) {
            e.preventDefault();
            showLogsModal();
        });

        // Закрытие модальных окон
        $(document).on('click', '.abp-modal-close, .abp-modal', function(e) {
            if (e.target === this) {
                $(this).closest('.abp-modal').hide();
            }
        });

        // Тестирование API
        $(document).on('click', '.abp-test-api', function(e) {
            e.preventDefault();
            testOpenAIAPI();
        });
    }

    /**
     * Генерация изображения для поста
     */
    function generateImageForPost(postId, button) {
        if (!postId) {
            showNotice('error', 'Неверный ID поста');
            return;
        }

        const originalText = button.text();
        button.prop('disabled', true).html('<span class="abp-loader"></span>Генерируем...');

        $.ajax({
            url: ABPImageGenerator.ajax_url,
            type: 'POST',
            data: {
                action: 'abp_generate_image',
                post_id: postId,
                nonce: ABPImageGenerator.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'Изображение успешно создано для поста #' + postId);
                    loadStats(); // Обновляем статистику
                    loadPostsWithoutImages(); // Обновляем список постов
                } else {
                    showNotice('error', response.data?.message || 'Ошибка при создании изображения');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showNotice('error', 'Ошибка AJAX запроса');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    }

    /**
     * Массовая генерация изображений
     */
    function bulkGenerateImages() {
        const selectedPosts = $('.abp-post-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedPosts.length === 0) {
            showNotice('warning', 'Выберите посты для генерации изображений');
            return;
        }

        if (!confirm(`Вы уверены, что хотите сгенерировать изображения для ${selectedPosts.length} постов? Это может занять несколько минут.`)) {
            return;
        }

        bulkOperationInProgress = true;
        totalBulkItems = selectedPosts.length;
        currentBulkProgress = 0;

        showBulkProgress();

        $.ajax({
            url: ABPImageGenerator.ajax_url,
            type: 'POST',
            data: {
                action: 'abp_bulk_generate_images',
                post_ids: selectedPosts,
                nonce: ABPImageGenerator.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', `Успешно сгенерировано ${response.data.message}`);
                    loadStats();
                    loadPostsWithoutImages();
                } else {
                    showNotice('error', response.data?.message || 'Ошибка при массовой генерации');
                }
            },
            error: function(xhr, status, error) {
                console.error('Bulk AJAX Error:', error);
                showNotice('error', 'Ошибка AJAX запроса при массовой генерации');
            },
            complete: function() {
                bulkOperationInProgress = false;
                hideBulkProgress();
            }
        });
    }

    function generateByLetter(letter){
        const btns = $('.abp-generate-by-letter');
        const original = btns.first().text();
        btns.prop('disabled', true).text('Генерация...');
        $.ajax({
            url: ABPImageGenerator.ajax_url,
            type: 'POST',
            data: { action: 'abp_generate_images_by_letter', letter: letter, nonce: ABPImageGenerator.nonce },
            success: function(response){
                if (response.success){
                    showNotice('success', response.data.message);
                    loadStats();
                } else {
                    showNotice('error', response.data?.message || 'Ошибка генерации по букве');
                }
            },
            error: function(){ showNotice('error', 'Ошибка AJAX'); },
            complete: function(){ btns.prop('disabled', false).text(original || 'Сгенерировать по букве'); }
        });
    }

    /**
     * Проверка SEO поста
     */
    function checkPostSEO(postId) {
        if (!postId) return;

        $.ajax({
            url: ABPImageGenerator.ajax_url,
            type: 'POST',
            data: {
                action: 'abp_check_post_seo',
                post_id: postId,
                nonce: ABPImageGenerator.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSEOResults(response.data, postId);
                } else {
                    showNotice('error', 'Ошибка при проверке SEO');
                }
            },
            error: function(xhr, status, error) {
                console.error('SEO Check Error:', error);
                showNotice('error', 'Ошибка AJAX запроса при проверке SEO');
            }
        });
    }

    /**
     * Загрузка статистики
     */
    function loadStats() {
        $.ajax({
            url: ABPImageGenerator.ajax_url,
            type: 'POST',
            data: {
                action: 'abp_get_generation_stats',
                nonce: ABPImageGenerator.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStatsDisplay(response.data);
                } else {
                    console.error('Failed to load stats');
                }
            },
            error: function(xhr, status, error) {
                console.error('Stats AJAX Error:', error);
            }
        });
    }

    /**
     * Обновление отображения статистики
     */
    function updateStatsDisplay(stats) {
        $('.abp-stat-total-posts .stat-number').text(stats.total_posts);
        $('.abp-stat-posts-with-images .stat-number').text(stats.posts_with_images);
        $('.abp-stat-posts-without-images .stat-number').text(stats.posts_without_images);

        // Обновляем прогресс бар
        const percentage = stats.total_posts > 0 ? (stats.posts_with_images / stats.total_posts) * 100 : 0;
        $('.abp-progress-fill').css('width', percentage + '%');

        // Обновляем статистику генераций
        if (stats.generation_stats) {
            updateGenerationStats(stats.generation_stats);
        }
    }

    /**
     * Обновление статистики генераций
     */
    function updateGenerationStats(generationStats) {
        let html = '<table class="abp-table">';
        html += '<thead><tr><th>Статус</th><th>Количество</th></tr></thead>';
        html += '<tbody>';

        generationStats.forEach(function(stat) {
            html += `<tr>
                <td><span class="abp-status abp-status-${stat.status}">${stat.status}</span></td>
                <td>${stat.count}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        $('.abp-generation-stats').html(html);
    }

    /**
     * Загрузка постов без изображений
     */
    function loadPostsWithoutImages() {
        $.ajax({
            url: ABPImageGenerator.ajax_url,
            type: 'POST',
            data: {
                action: 'abp_get_posts_without_images',
                nonce: ABPImageGenerator.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayPostsWithoutImages(response.data.posts);
                } else {
                    console.error('Failed to load posts without images');
                }
            },
            error: function(xhr, status, error) {
                console.error('Posts AJAX Error:', error);
            }
        });
    }

    /**
     * Отображение постов без изображений
     */
    function displayPostsWithoutImages(posts) {
        let html = '';

        if (posts.length === 0) {
            html = '<div class="abp-notice abp-notice-success">Все посты имеют изображения!</div>';
        } else {
            html = '<div class="abp-posts-grid">';
            
            posts.forEach(function(post) {
                html += `
                    <div class="abp-post-card">
                        <h4>${post.title}</h4>
                        <div class="post-meta">
                            ID: ${post.id} | Дата: ${post.date} | Автор: ${post.author}
                        </div>
                        <div class="post-excerpt">${post.excerpt}</div>
                        <div class="post-actions">
                            <input type="checkbox" class="abp-post-checkbox" value="${post.id}">
                            <button class="abp-btn abp-btn-small abp-generate-image" data-post-id="${post.id}">
                                Сгенерировать изображение
                            </button>
                            <button class="abp-btn abp-btn-small abp-btn-secondary abp-check-seo" data-post-id="${post.id}">
                                Проверить SEO
                            </button>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
        }

        $('.abp-posts-without-images').html(html);
    }

    /**
     * Обновление массовых действий
     */
    function updateBulkActions() {
        const selectedCount = $('.abp-post-checkbox:checked').length;
        const totalCount = $('.abp-post-checkbox').length;

        $('.abp-selected-count').text(selectedCount);
        
        if (selectedCount > 0) {
            $('.abp-bulk-actions').show();
        } else {
            $('.abp-bulk-actions').hide();
        }

        $('.abp-select-all').prop('checked', selectedCount === totalCount && totalCount > 0);
    }

    /**
     * Показ прогресса массовых операций
     */
    function showBulkProgress() {
        $('.abp-bulk-progress').addClass('active');
        updateBulkProgress(0);
    }

    /**
     * Скрытие прогресса массовых операций
     */
    function hideBulkProgress() {
        $('.abp-bulk-progress').removeClass('active');
    }

    /**
     * Обновление прогресса массовых операций
     */
    function updateBulkProgress(progress) {
        const percentage = Math.round((progress / totalBulkItems) * 100);
        $('.abp-progress-fill').css('width', percentage + '%');
        $('.abp-progress-text').text(`Обработано: ${progress} из ${totalBulkItems} (${percentage}%)`);
    }

    /**
     * Показ результатов SEO проверки
     */
    function showSEOResults(data, postId) {
        let html = `
            <div class="abp-modal" id="seo-results-modal">
                <div class="abp-modal-content">
                    <div class="abp-modal-header">
                        <h3>SEO Результаты для поста #${postId}</h3>
                        <button class="abp-modal-close">&times;</button>
                    </div>
                    <div class="abp-seo-results">
        `;

        if (data.has_image) {
            html += `<div class="abp-notice abp-notice-success">
                <strong>Изображение найдено!</strong>
            </div>`;
        } else {
            html += `<div class="abp-notice abp-notice-error">
                <strong>Изображение отсутствует!</strong>
            </div>`;
        }

        html += `<div class="abp-seo-score abp-seo-score-${data.seo_score >= 80 ? 'excellent' : data.seo_score >= 60 ? 'good' : 'poor'}">
            SEO Score: ${data.seo_score}%
        </div>`;

        if (data.issues && data.issues.length > 0) {
            html += '<h4>Проблемы:</h4><ul>';
            data.issues.forEach(function(issue) {
                html += `<li>${issue}</li>`;
            });
            html += '</ul>';
        }

        html += `
                    </div>
                </div>
            </div>
        `;

        $('body').append(html);
        $('#seo-results-modal').show();
    }

    /**
     * Сохранение настроек
     */
    function saveSettings() {
        const formData = $('.abp-settings-form').serialize();
        
        $.ajax({
            url: ABPImageGenerator.ajax_url,
            type: 'POST',
            data: formData + '&action=abp_save_settings&nonce=' + ABPImageGenerator.nonce,
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'Настройки сохранены успешно');
                } else {
                    showNotice('error', 'Ошибка при сохранении настроек');
                }
            },
            error: function(xhr, status, error) {
                console.error('Settings Save Error:', error);
                showNotice('error', 'Ошибка AJAX запроса при сохранении настроек');
            }
        });
    }

    /**
     * Показ модального окна с логами
     */
    function showLogsModal() {
        $.ajax({
            url: ABPImageGenerator.ajax_url,
            type: 'POST',
            data: {
                action: 'abp_get_logs',
                nonce: ABPImageGenerator.nonce
            },
            success: function(response) {
                if (response.success) {
                    showLogsModalContent(response.data.logs);
                } else {
                    showNotice('error', 'Ошибка при загрузке логов');
                }
            },
            error: function(xhr, status, error) {
                console.error('Logs AJAX Error:', error);
                showNotice('error', 'Ошибка AJAX запроса при загрузке логов');
            }
        });
    }

    /**
     * Показ содержимого модального окна логов
     */
    function showLogsModalContent(logs) {
        let html = `
            <div class="abp-modal" id="logs-modal">
                <div class="abp-modal-content">
                    <div class="abp-modal-header">
                        <h3>Логи ABP Image Generator</h3>
                        <button class="abp-modal-close">&times;</button>
                    </div>
                    <div class="abp-logs">
        `;

        if (logs && logs.length > 0) {
            logs.forEach(function(log) {
                html += `<div class="abp-log-entry ${log.level}">${log.message}</div>`;
            });
        } else {
            html += '<div class="abp-log-entry info">Логи отсутствуют</div>';
        }

        html += `
                    </div>
                </div>
            </div>
        `;

        $('body').append(html);
        $('#logs-modal').show();
    }

    /**
     * Тестирование OpenAI API
     */
    function testOpenAIAPI() {
        const button = $('.abp-test-api');
        const originalText = button.text();
        
        button.prop('disabled', true).html('<span class="abp-loader"></span>Тестируем...');

        $.ajax({
            url: ABPImageGenerator.ajax_url,
            type: 'POST',
            data: {
                action: 'abp_test_openai_api',
                nonce: ABPImageGenerator.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'OpenAI API работает корректно');
                } else {
                    showNotice('error', response.data?.message || 'Ошибка при тестировании API');
                }
            },
            error: function(xhr, status, error) {
                console.error('API Test Error:', error);
                showNotice('error', 'Ошибка AJAX запроса при тестировании API');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    }

    /**
     * Проверка интеграции с системой блога
     */
    function checkBlogIntegration() {
        $.ajax({
            url: ABPImageGenerator.ajax_url,
            type: 'POST',
            data: {
                action: 'abp_check_blog_integration',
                nonce: ABPImageGenerator.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateBlogIntegrationStatus(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Blog Integration Check Error:', error);
            }
        });
    }

    /**
     * Обновление статуса интеграции с блогом
     */
    function updateBlogIntegrationStatus(data) {
        $('.abp-blog-integration .abp-integration-status').removeClass('active inactive');
        
        if (data.alphabet_blog_panel) {
            $('.abp-alphabet-blog-status').addClass('active');
        } else {
            $('.abp-alphabet-blog-status').addClass('inactive');
        }

        if (data.yoast_integration) {
            $('.abp-yoast-status').addClass('active');
        } else {
            $('.abp-yoast-status').addClass('inactive');
        }
    }

    /**
     * Инициализация табов
     */
    function initTabs() {
        $('.abp-nav-tab').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this).attr('href');
            
            // Обновляем активный таб
            $('.abp-nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Показываем соответствующий контент
            $('.abp-tab-content').hide();
            $(target).show();
        });
    }

    /**
     * Показ уведомлений
     */
    function showNotice(type, message) {
        const noticeClass = `abp-notice-${type}`;
        const notice = $(`<div class="abp-notice ${noticeClass}">${message}</div>`);
        
        $('.abp-image-generator').prepend(notice);
        
        // Автоматически скрываем уведомление через 5 секунд
        setTimeout(function() {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Экспорт функций для глобального использования
    window.ABPImageGeneratorAdmin = {
        generateImageForPost: generateImageForPost,
        bulkGenerateImages: bulkGenerateImages,
        loadStats: loadStats,
        showNotice: showNotice
    };

})(jQuery);



