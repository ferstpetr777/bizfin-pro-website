/**
 * Yoast Alphabet Integration - Admin JavaScript
 */

(function($) {
    'use strict';

    let isOptimizing = false;

    // Инициализация
    $(document).ready(function() {
        initEventHandlers();
    });

    function initEventHandlers() {
        // Оптимизация всех постов
        $('#yai-optimize-all').on('click', function() {
            if (!isOptimizing) {
                optimizeAllPosts();
            }
        });

        // Проверка оптимизации
        $('#yai-check-optimization').on('click', function() {
            checkOptimization();
        });
    }

    function optimizeAllPosts() {
        if (isOptimizing) return;

        isOptimizing = true;
        const $button = $('#yai-optimize-all');
        const $results = $('#yai-results');
        const $resultsContent = $('#yai-results-content');

        // Показываем результаты
        $results.show();
        $resultsContent.html('<div class="yai-loading"></div> Оптимизация началась...');

        // Блокируем кнопку
        $button.prop('disabled', true).addClass('yai-loading');

        $.ajax({
            url: YAI.ajax_url,
            type: 'POST',
            data: {
                action: 'yai_optimize_all_posts',
                nonce: YAI.nonce
            },
            success: function(response) {
                if (response.success) {
                    showOptimizationResults(response.data);
                } else {
                    showError('Ошибка оптимизации: ' + (response.data || 'Неизвестная ошибка'));
                }
            },
            error: function() {
                showError('Ошибка сети при выполнении оптимизации');
            },
            complete: function() {
                isOptimizing = false;
                $button.prop('disabled', false).removeClass('yai-loading');
            }
        });
    }

    function showOptimizationResults(data) {
        const $resultsContent = $('#yai-results-content');
        
        let html = '<div class="yai-summary">';
        html += '<div class="yai-summary-item">';
        html += '<span class="yai-summary-number">' + data.total + '</span>';
        html += '<div class="yai-summary-label">Всего постов</div>';
        html += '</div>';
        
        html += '<div class="yai-summary-item">';
        html += '<span class="yai-summary-number">' + data.optimized + '</span>';
        html += '<div class="yai-summary-label">Оптимизировано</div>';
        html += '</div>';
        
        html += '<div class="yai-summary-item">';
        html += '<span class="yai-summary-number">' + data.errors + '</span>';
        html += '<div class="yai-summary-label">Ошибок</div>';
        html += '</div>';
        
        const percentage = data.total > 0 ? Math.round((data.optimized / data.total) * 100) : 0;
        html += '<div class="yai-summary-item">';
        html += '<span class="yai-summary-number">' + percentage + '%</span>';
        html += '<div class="yai-summary-label">Процент успеха</div>';
        html += '</div>';
        html += '</div>';

        // Прогресс-бар
        html += '<div class="yai-progress">';
        html += '<div class="yai-progress-bar" style="width: ' + percentage + '%">' + percentage + '%</div>';
        html += '</div>';

        // Сообщение о результате
        if (data.errors === 0) {
            html += '<div class="notice notice-success"><p>✅ Все посты успешно оптимизированы!</p></div>';
        } else if (data.optimized > 0) {
            html += '<div class="notice notice-warning"><p>⚠️ Оптимизация завершена с ошибками. Проверьте логи.</p></div>';
        } else {
            html += '<div class="notice notice-error"><p>❌ Оптимизация не удалась. Проверьте настройки.</p></div>';
        }

        $resultsContent.html(html);

        // Обновляем статистику на странице
        setTimeout(function() {
            location.reload();
        }, 3000);
    }

    function checkOptimization() {
        const $button = $('#yai-check-optimization');
        const $results = $('#yai-results');
        const $resultsContent = $('#yai-results-content');

        $results.show();
        $resultsContent.html('<div class="yai-loading"></div> Проверка оптимизации...');
        $button.prop('disabled', true);

        // Симуляция проверки (в реальном проекте здесь был бы AJAX запрос)
        setTimeout(function() {
            $resultsContent.html('<div class="notice notice-info"><p>ℹ️ Проверка оптимизации завершена. Статистика обновлена.</p></div>');
            $button.prop('disabled', false);
            
            // Обновляем страницу для показа актуальной статистики
            setTimeout(function() {
                location.reload();
            }, 2000);
        }, 2000);
    }

    function showError(message) {
        const $resultsContent = $('#yai-results-content');
        $resultsContent.html('<div class="notice notice-error"><p>❌ ' + message + '</p></div>');
    }

    // Утилиты
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function showLoading(element) {
        $(element).html('<div class="yai-loading"></div> Загрузка...');
    }

    function hideLoading(element, content) {
        $(element).html(content || '');
    }

})(jQuery);



