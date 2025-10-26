/**
 * BizFin ChatGPT Consultant - Admin JavaScript
 */

(function($) {
    'use strict';
    
    // Глобальные переменные
    let refreshInterval = null;
    let isRefreshing = false;
    
    // Инициализация при загрузке страницы
    $(document).ready(function() {
        initializeAdminInterface();
        setupEventListeners();
        startAutoRefresh();
    });
    
    /**
     * Инициализация админ-интерфейса
     */
    function initializeAdminInterface() {
        // Инициализируем компоненты в зависимости от текущей страницы
        const currentPage = getCurrentPage();
        
        switch (currentPage) {
            case 'main':
                initializeDashboard();
                break;
            case 'settings':
                initializeSettings();
                break;
            case 'statistics':
                initializeStatistics();
                break;
            case 'logs':
                initializeLogs();
                break;
        }
    }
    
    /**
     * Получение текущей страницы
     */
    function getCurrentPage() {
        const url = window.location.href;
        
        if (url.includes('bizfin-chatgpt-consultant-stats')) {
            return 'statistics';
        } else if (url.includes('bizfin-chatgpt-consultant-logs')) {
            return 'logs';
        } else if (url.includes('bizfin-chatgpt-consultant-settings')) {
            return 'settings';
        } else {
            return 'main';
        }
    }
    
    /**
     * Инициализация дашборда
     */
    function initializeDashboard() {
        loadSystemStatus();
        loadQuickStats();
        loadRecentLogs();
        
        // Обновляем каждые 30 секунд
        refreshInterval = setInterval(function() {
            if (!isRefreshing) {
                loadSystemStatus();
                loadQuickStats();
                loadRecentLogs();
            }
        }, 30000);
    }
    
    /**
     * Инициализация настроек
     */
    function initializeSettings() {
        // Валидация полей
        setupFormValidation();
        
        // Предварительный просмотр настроек
        setupSettingsPreview();
        
        // Тестирование API
        setupApiTesting();
    }
    
    /**
     * Инициализация статистики
     */
    function initializeStatistics() {
        loadDetailedStatistics();
        
        // Обновляем каждые 60 секунд
        refreshInterval = setInterval(function() {
            if (!isRefreshing) {
                loadDetailedStatistics();
            }
        }, 60000);
    }
    
    /**
     * Инициализация логов
     */
    function initializeLogs() {
        loadLogs();
        
        // Автообновление логов каждые 10 секунд
        refreshInterval = setInterval(function() {
            if (!isRefreshing) {
                loadLogs();
            }
        }, 10000);
    }
    
    /**
     * Настройка обработчиков событий
     */
    function setupEventListeners() {
        // Кнопки действий
        $(document).on('click', '.bcc-test-api-btn', testOpenAIConnection);
        $(document).on('click', '.bcc-cleanup-btn', cleanupOldData);
        $(document).on('click', '.bcc-export-logs-btn', exportLogs);
        $(document).on('click', '.bcc-refresh-btn', refreshCurrentPage);
        
        // Формы
        $(document).on('submit', '.bcc-settings-form', handleSettingsSubmit);
        
        // Модальные окна
        $(document).on('click', '.bcc-modal-close', closeModal);
        $(document).on('click', '.bcc-modal-overlay', closeModal);
        
        // Клавиатурные сокращения
        $(document).on('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'r':
                        e.preventDefault();
                        refreshCurrentPage();
                        break;
                    case 't':
                        e.preventDefault();
                        testOpenAIConnection();
                        break;
                }
            }
        });
    }
    
    /**
     * Загрузка статуса системы
     */
    function loadSystemStatus() {
        if (isRefreshing) return;
        
        const $statusContainer = $('#bcc-system-status');
        if (!$statusContainer.length) return;
        
        $statusContainer.html('<div class="bcc-loading"></div>');
        
        $.ajax({
            url: bcc_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bcc_test_openai',
                nonce: bcc_admin_ajax.nonce
            },
            timeout: 10000,
            success: function(response) {
                if (response.success) {
                    $statusContainer.html(`
                        <div class="bcc-status-success">
                            <span class="bcc-indicator bcc-indicator-success"></span>
                            ${response.data.message}
                        </div>
                    `);
                } else {
                    $statusContainer.html(`
                        <div class="bcc-status-error">
                            <span class="bcc-indicator bcc-indicator-error"></span>
                            ${response.data.message}
                        </div>
                    `);
                }
            },
            error: function() {
                $statusContainer.html(`
                    <div class="bcc-status-error">
                        <span class="bcc-indicator bcc-indicator-error"></span>
                        Ошибка подключения к API
                    </div>
                `);
            }
        });
    }
    
    /**
     * Загрузка быстрой статистики
     */
    function loadQuickStats() {
        if (isRefreshing) return;
        
        const $statsContainer = $('#bcc-quick-stats');
        if (!$statsContainer.length) return;
        
        $statsContainer.html('<div class="bcc-loading"></div>');
        
        $.ajax({
            url: bcc_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bcc_get_statistics',
                nonce: bcc_admin_ajax.nonce
            },
            timeout: 10000,
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    const html = `
                        <div class="bcc-stats-grid">
                            <div class="bcc-metric-card">
                                <div class="bcc-metric-value">${stats.total_messages || 0}</div>
                                <div class="bcc-metric-label">Сообщений</div>
                            </div>
                            <div class="bcc-metric-card">
                                <div class="bcc-metric-value">${stats.total_sessions || 0}</div>
                                <div class="bcc-metric-label">Сессий</div>
                            </div>
                            <div class="bcc-metric-card">
                                <div class="bcc-metric-value">${formatNumber(stats.total_tokens || 0)}</div>
                                <div class="bcc-metric-label">Токенов</div>
                            </div>
                            <div class="bcc-metric-card">
                                <div class="bcc-metric-value">${stats.total_files || 0}</div>
                                <div class="bcc-metric-label">Файлов</div>
                            </div>
                        </div>
                    `;
                    $statsContainer.html(html);
                } else {
                    $statsContainer.html('<p class="bcc-error">Ошибка загрузки статистики</p>');
                }
            },
            error: function() {
                $statsContainer.html('<p class="bcc-error">Ошибка подключения</p>');
            }
        });
    }
    
    /**
     * Загрузка последних логов
     */
    function loadRecentLogs() {
        if (isRefreshing) return;
        
        const $logsContainer = $('#bcc-recent-logs');
        if (!$logsContainer.length) return;
        
        $.ajax({
            url: bcc_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bcc_export_logs',
                nonce: bcc_admin_ajax.nonce,
                lines: 20
            },
            timeout: 10000,
            success: function(response) {
                if (response.success) {
                    $logsContainer.html(`<pre>${escapeHtml(response.data)}</pre>`);
                } else {
                    $logsContainer.html('<p class="bcc-error">Ошибка загрузки логов</p>');
                }
            },
            error: function() {
                $logsContainer.html('<p class="bcc-error">Ошибка подключения</p>');
            }
        });
    }
    
    /**
     * Загрузка подробной статистики
     */
    function loadDetailedStatistics() {
        if (isRefreshing) return;
        
        const $container = $('#bcc-statistics-container');
        if (!$container.length) return;
        
        $container.html('<div class="bcc-loading"></div>');
        
        $.ajax({
            url: bcc_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bcc_get_statistics',
                nonce: bcc_admin_ajax.nonce
            },
            timeout: 15000,
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    const html = createDetailedStatsHtml(stats);
                    $container.html(html);
                } else {
                    $container.html('<p class="bcc-error">Ошибка загрузки статистики</p>');
                }
            },
            error: function() {
                $container.html('<p class="bcc-error">Ошибка подключения</p>');
            }
        });
    }
    
    /**
     * Создание HTML для подробной статистики
     */
    function createDetailedStatsHtml(stats) {
        let html = '<div class="bcc-stats-grid">';
        
        // Общая статистика
        html += `
            <div class="bcc-stats-card">
                <h3>📊 Общая статистика</h3>
                <ul>
                    <li><strong>Всего сообщений:</strong> ${stats.total_messages || 0}</li>
                    <li><strong>Всего сессий:</strong> ${stats.total_sessions || 0}</li>
                    <li><strong>Всего токенов:</strong> ${formatNumber(stats.total_tokens || 0)}</li>
                    <li><strong>Всего файлов:</strong> ${stats.total_files || 0}</li>
                    <li><strong>Всего векторов:</strong> ${stats.total_vectors || 0}</li>
                </ul>
            </div>
        `;
        
        // Статистика файлов
        if (stats.files_by_type && Object.keys(stats.files_by_type).length > 0) {
            html += `
                <div class="bcc-stats-card">
                    <h3>📁 Файлы по типам</h3>
                    <ul>
            `;
            for (const type in stats.files_by_type) {
                html += `<li><strong>${type}:</strong> ${stats.files_by_type[type]}</li>`;
            }
            html += `
                    </ul>
                </div>
            `;
        }
        
        // Статистика векторов
        if (stats.vectors_by_type && Object.keys(stats.vectors_by_type).length > 0) {
            html += `
                <div class="bcc-stats-card">
                    <h3>🔍 Векторы по типам</h3>
                    <ul>
            `;
            for (const type in stats.vectors_by_type) {
                html += `<li><strong>${type}:</strong> ${stats.vectors_by_type[type]}</li>`;
            }
            html += `
                    </ul>
                </div>
            `;
        }
        
        html += '</div>';
        
        return html;
    }
    
    /**
     * Загрузка логов
     */
    function loadLogs() {
        if (isRefreshing) return;
        
        const $container = $('#bcc-logs-container');
        if (!$container.length) return;
        
        $.ajax({
            url: bcc_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bcc_export_logs',
                nonce: bcc_admin_ajax.nonce,
                lines: 100
            },
            timeout: 10000,
            success: function(response) {
                if (response.success) {
                    $container.html(`<pre>${escapeHtml(response.data)}</pre>`);
                    // Автопрокрутка к концу
                    $container.scrollTop($container[0].scrollHeight);
                } else {
                    $container.html('<p class="bcc-error">Ошибка загрузки логов</p>');
                }
            },
            error: function() {
                $container.html('<p class="bcc-error">Ошибка подключения</p>');
            }
        });
    }
    
    /**
     * Тестирование подключения к OpenAI
     */
    function testOpenAIConnection() {
        if (isRefreshing) return;
        
        isRefreshing = true;
        showNotification('Тестирование подключения к OpenAI API...', 'info');
        
        $.ajax({
            url: bcc_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bcc_test_openai',
                nonce: bcc_admin_ajax.nonce
            },
            timeout: 30000,
            success: function(response) {
                if (response.success) {
                    showNotification('✅ ' + response.data.message, 'success');
                } else {
                    showNotification('❌ ' + response.data.message, 'error');
                }
            },
            error: function() {
                showNotification('❌ Ошибка подключения к API', 'error');
            },
            complete: function() {
                isRefreshing = false;
                loadSystemStatus();
            }
        });
    }
    
    /**
     * Очистка старых данных
     */
    function cleanupOldData() {
        if (!confirm('Вы уверены, что хотите очистить старые данные? Это действие нельзя отменить.')) {
            return;
        }
        
        if (isRefreshing) return;
        
        isRefreshing = true;
        showNotification('Очистка старых данных...', 'info');
        
        $.ajax({
            url: bcc_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bcc_cleanup_data',
                nonce: bcc_admin_ajax.nonce
            },
            timeout: 60000,
            success: function(response) {
                if (response.success) {
                    showNotification(`✅ Очистка завершена. Удалено: ${response.data} записей.`, 'success');
                    loadQuickStats();
                } else {
                    showNotification('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                showNotification('❌ Ошибка очистки данных', 'error');
            },
            complete: function() {
                isRefreshing = false;
            }
        });
    }
    
    /**
     * Экспорт логов
     */
    function exportLogs() {
        const url = bcc_admin_ajax.ajax_url + '?action=bcc_export_logs&nonce=' + bcc_admin_ajax.nonce + '&download=1';
        window.open(url, '_blank');
        showNotification('Экспорт логов начат', 'info');
    }
    
    /**
     * Обновление текущей страницы
     */
    function refreshCurrentPage() {
        if (isRefreshing) return;
        
        isRefreshing = true;
        showNotification('Обновление данных...', 'info');
        
        const currentPage = getCurrentPage();
        
        switch (currentPage) {
            case 'main':
                loadSystemStatus();
                loadQuickStats();
                loadRecentLogs();
                break;
            case 'statistics':
                loadDetailedStatistics();
                break;
            case 'logs':
                loadLogs();
                break;
        }
        
        setTimeout(() => {
            isRefreshing = false;
            showNotification('Данные обновлены', 'success');
        }, 1000);
    }
    
    /**
     * Настройка валидации форм
     */
    function setupFormValidation() {
        $('.bcc-settings-form').on('submit', function(e) {
            const $form = $(this);
            let isValid = true;
            
            // Валидация обязательных полей
            $form.find('[required]').each(function() {
                const $field = $(this);
                if (!$field.val().trim()) {
                    $field.addClass('error');
                    isValid = false;
                } else {
                    $field.removeClass('error');
                }
            });
            
            // Валидация числовых полей
            $form.find('input[type="number"]').each(function() {
                const $field = $(this);
                const value = parseFloat($field.val());
                const min = parseFloat($field.attr('min'));
                const max = parseFloat($field.attr('max'));
                
                if (isNaN(value) || (min !== undefined && value < min) || (max !== undefined && value > max)) {
                    $field.addClass('error');
                    isValid = false;
                } else {
                    $field.removeClass('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Пожалуйста, исправьте ошибки в форме', 'error');
            }
        });
    }
    
    /**
     * Настройка предварительного просмотра настроек
     */
    function setupSettingsPreview() {
        // Обновляем предварительный просмотр при изменении полей
        $('input[name="bcc_agent_name"]').on('input', function() {
            updatePreview('agent_name', $(this).val());
        });
        
        $('select[name="bcc_model"]').on('change', function() {
            updatePreview('model', $(this).val());
        });
    }
    
    /**
     * Обновление предварительного просмотра
     */
    function updatePreview(field, value) {
        // Можно добавить логику предварительного просмотра
        console.log('Preview update:', field, value);
    }
    
    /**
     * Настройка тестирования API
     */
    function setupApiTesting() {
        // Добавляем кнопку тестирования API рядом с полем модели
        const $modelField = $('select[name="bcc_model"]');
        if ($modelField.length) {
            const $testBtn = $('<button type="button" class="button bcc-test-api-btn">Тест API</button>');
            $modelField.after($testBtn);
        }
    }
    
    /**
     * Запуск автообновления
     */
    function startAutoRefresh() {
        // Автообновление только для дашборда и статистики
        const currentPage = getCurrentPage();
        if (currentPage === 'main' || currentPage === 'statistics') {
            // Уже настроено в initializeDashboard и initializeStatistics
        }
    }
    
    /**
     * Остановка автообновления
     */
    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }
    
    /**
     * Показать уведомление
     */
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="notice notice-${type} is-dismissible bcc-admin-notification">
                <p>${escapeHtml(message)}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Скрыть это уведомление.</span>
                </button>
            </div>
        `);
        
        $('.wrap h1').after(notification);
        
        // Автоматически удаляем через 5 секунд
        setTimeout(() => {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
        
        // Обработка закрытия
        notification.find('.notice-dismiss').on('click', function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        });
    }
    
    /**
     * Закрытие модального окна
     */
    function closeModal() {
        $('.bcc-modal').fadeOut(300);
    }
    
    /**
     * Обработка отправки формы настроек
     */
    function handleSettingsSubmit(e) {
        showNotification('Сохранение настроек...', 'info');
        
        // Форма отправится обычным способом
        // После перезагрузки покажем уведомление об успехе
        setTimeout(() => {
            showNotification('Настройки сохранены успешно', 'success');
        }, 1000);
    }
    
    /**
     * Форматирование чисел
     */
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }
    
    /**
     * Экранирование HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Очистка при уходе со страницы
    $(window).on('beforeunload', function() {
        stopAutoRefresh();
    });
    
    // Экспорт функций для глобального использования
    window.BizFinChatGPTAdmin = {
        testOpenAIConnection: testOpenAIConnection,
        cleanupOldData: cleanupOldData,
        exportLogs: exportLogs,
        refreshCurrentPage: refreshCurrentPage,
        showNotification: showNotification
    };
    
})(jQuery);
