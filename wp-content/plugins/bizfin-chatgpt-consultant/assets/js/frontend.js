/**
 * BizFin ChatGPT Consultant - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    // Глобальные переменные
    let currentSessionId = '';
    let isTyping = false;
    let typingTimer = null;
    
    // Инициализация при загрузке страницы
    $(document).ready(function() {
        initializeChatInterface();
        setupEventListeners();
    });
    
    /**
     * Инициализация интерфейса чата
     */
    function initializeChatInterface() {
        // Инициализируем все чаты на странице
        $('.bcc-chat-container').each(function() {
            const chatId = $(this).attr('id');
            if (chatId) {
                initializeSingleChat(chatId);
            }
        });
    }
    
    /**
     * Инициализация одного чата
     */
    function initializeSingleChat(chatId) {
        const $chat = $('#' + chatId);
        const $input = $chat.find('.bcc-message-input');
        const $sendBtn = $chat.find('.bcc-send-btn');
        const $fileInput = $chat.find('.bcc-file-input');
        
        // Получаем или создаем session ID
        currentSessionId = getSessionId(chatId);
        
        // Стартуем в свернутом состоянии (принудительно для всех браузеров)
        $chat.addClass('bcc-collapsed');
        
        // Автоматическое изменение высоты textarea
        $input.on('input', function() {
            autoResizeTextarea($(this));
        });
        
        // Первое взаимодействие пользователя разворачивает чат
        $input.on('focus', function() {
            $chat.removeClass('bcc-collapsed');
        });
        
        // Отправка сообщения по Enter (Shift+Enter для новой строки)
        $input.on('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(chatId);
            }
        });
        
        // Обработка загрузки файлов
        if ($fileInput.length) {
            $fileInput.on('change', function(e) {
                handleFileUpload(chatId, e.target.files);
            });
        }
        
        // Загружаем историю чата
        loadChatHistory(chatId);
        
        // Фокус на поле ввода
        $input.focus();
    }
    
    /**
     * Настройка обработчиков событий
     */
    function setupEventListeners() {
        // Обработка клика по кнопке отправки
        $(document).on('click', '.bcc-send-btn', function() {
            const chatId = $(this).closest('.bcc-chat-container').attr('id');
            if (chatId) {
                sendMessage(chatId);
            }
        });
        
        // Клик по шапке чата также разворачивает его
        $(document).on('click', '.bcc-chat-header', function() {
            const $c = $(this).closest('.bcc-chat-container');
            $c.removeClass('bcc-collapsed');
        });
        
        // Обработка клика по кнопке прикрепления файлов
        $(document).on('click', '.bcc-attach-btn', function() {
            const chatId = $(this).closest('.bcc-chat-container').attr('id');
            if (chatId) {
                toggleFileUpload(chatId);
            }
        });
        
        // Обработка удаления файлов
        $(document).on('click', '.bcc-file-remove', function() {
            $(this).closest('.bcc-file-item').remove();
        });
        
        // Обработка drag & drop для файлов
        $(document).on('dragover', '.bcc-chat-container', function(e) {
            e.preventDefault();
            $(this).addClass('bcc-drag-over');
        });
        
        $(document).on('dragleave', '.bcc-chat-container', function(e) {
            e.preventDefault();
            $(this).removeClass('bcc-drag-over');
        });
        
        $(document).on('drop', '.bcc-chat-container', function(e) {
            e.preventDefault();
            $(this).removeClass('bcc-drag-over');
            
            const chatId = $(this).attr('id');
            const files = e.originalEvent.dataTransfer.files;
            
            if (chatId && files.length > 0) {
                handleFileUpload(chatId, files);
            }
        });
    }
    
    /**
     * Автоматическое изменение высоты textarea
     */
    function autoResizeTextarea($textarea) {
        $textarea.css('height', 'auto');
        const newHeight = Math.min($textarea[0].scrollHeight, 120);
        $textarea.css('height', newHeight + 'px');
    }
    
    /**
     * Отправка сообщения
     */
    function sendMessage(chatId) {
        const $chat = $('#' + chatId);
        const $input = $chat.find('.bcc-message-input');
        const $sendBtn = $chat.find('.bcc-send-btn');
        const message = $input.val().trim();
        
        if (!message) {
            return;
        }
        
        // Разворачиваем чат при первой отправке
        $chat.removeClass('bcc-collapsed');
        
        // Добавляем сообщение пользователя в чат
        addMessageToChat(chatId, message, 'user');
        
        // Очищаем поле ввода
        $input.val('');
        $input.css('height', 'auto');
        
        // Показываем индикатор загрузки
        showLoadingIndicator(chatId);
        
        // Отправляем AJAX запрос
        $.ajax({
            url: bcc_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bcc_send_message',
                nonce: bcc_ajax.nonce,
                message: message,
                session_id: currentSessionId
            },
            timeout: 60000, // 60 секунд
            success: function(response) {
                hideLoadingIndicator(chatId);
                
                if (response.success) {
                    addMessageToChat(chatId, response.data.response, 'assistant');
                    updateSessionId(chatId, response.data.session_id);
                    
                    // Обновляем статистику если доступна
                    if (response.data.tokens_used) {
                        updateTokenUsage(response.data.tokens_used);
                    }
                } else {
                    addMessageToChat(chatId, response.data.message || 'Произошла ошибка. Попробуйте еще раз.', 'assistant', true);
                }
            },
            error: function(xhr, status, error) {
                hideLoadingIndicator(chatId);
                
                let errorMessage = 'Ошибка соединения. Проверьте интернет-подключение.';
                
                if (status === 'timeout') {
                    errorMessage = 'Превышено время ожидания ответа. Попробуйте еще раз.';
                } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMessage = xhr.responseJSON.data.message;
                }
                
                addMessageToChat(chatId, errorMessage, 'assistant', true);
            }
        });
    }
    
    /**
     * Добавление сообщения в чат
     */
    function addMessageToChat(chatId, message, type, isError = false) {
        const $chat = $('#' + chatId);
        const $messagesContainer = $chat.find('.bcc-chat-messages');
        
        // Удаляем приветственное сообщение если есть
        $messagesContainer.find('.bcc-welcome-message').remove();
        
        const messageHtml = createMessageHtml(message, type, isError);
        $messagesContainer.append(messageHtml);
        
        // Прокручиваем к последнему сообщению
        scrollToBottom($messagesContainer);
        
        // Анимация появления
        $messagesContainer.find('.bcc-message').last().hide().fadeIn(300);
    }
    
    /**
     * Создание HTML для сообщения
     */
    function createMessageHtml(message, type, isError = false) {
        const time = new Date().toLocaleTimeString('ru-RU', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const avatar = type === 'user' ? '👤' : getAgentInitial();
        const messageClass = `bcc-message bcc-message-${type}`;
        const errorClass = isError ? ' bcc-message-error' : '';
        
        // Готовим HTML безопасно: преобразуем переносы строк в абзацы/брейки и поддерживаем списки/markdown
        const formattedHtml = formatMessageHtml(message);

        return `
            <div class="${messageClass}${errorClass}">
                <div class="bcc-message-avatar">
                    <span class="bcc-avatar">${avatar}</span>
                </div>
                <div class="bcc-message-content">
                    <div class="bcc-message-text">${formattedHtml}</div>
                    <div class="bcc-message-time">${time}</div>
                </div>
            </div>
        `;
    }

    /**
     * Форматирование текста сообщения в безопасный HTML
     * - Экранирует HTML
     * - Преобразует двойные переводы строк в абзацы
     * - Преобразует одинарные переводы строк в <br>
     * - Конвертирует маркеры списка ("- ", "• ", "* ") и "1. " в списки
     * - Применяет базовый Markdown (**жирный**, *курсив*, `code`)
     */
    function formatMessageHtml(text) {
        const escaped = escapeHtml(String(text || ''));

        // Разбиваем на строки
        const lines = escaped.split(/\r?\n/);

        // Группируем в списки и параграфы
        let htmlParts = [];
        let listBuffer = [];
        let listOrdered = false;

        const flushList = () => {
            if (listBuffer.length) {
                const tag = listOrdered ? 'ol' : 'ul';
                htmlParts.push('<' + tag + ' class="bcc-message-list">' + listBuffer.map(li => `<li>${li}</li>`).join('') + '</' + tag + '>');
                listBuffer = [];
                listOrdered = false;
            }
        };

        lines.forEach(line => {
            const trimmed = line.trim();

            // Пустая строка — разделитель параграфов/конец списка
            if (trimmed === '') {
                flushList();
                htmlParts.push('<p></p>');
                return;
            }

            // Элементы списка
            if (/^(?:[-•*])\s+/.test(trimmed)) {
                const item = applyBasicMarkdown(trimmed.replace(/^(?:[-•*])\s+/, ''));
                listBuffer.push(item);
                listOrdered = false;
                return;
            }

            // Нумерованный список: 1. пункт
            const orderedMatch = trimmed.match(/^(\d+)\.\s+(.*)$/);
            if (orderedMatch) {
                const item = applyBasicMarkdown(orderedMatch[2]);
                listBuffer.push(item);
                listOrdered = true;
                return;
            }

            // Обычная строка текста
            flushList();
            htmlParts.push(`<p>${applyBasicMarkdown(trimmed)}</p>`);
        });

        // Закрываем возможный хвост списка
        flushList();

        // Если ничего не собрали (маловероятно) — вернем безопасный текст с <br>
        if (!htmlParts.length) {
            return escaped.replace(/\n/g, '<br>');
        }

        // Удаляем дублирующиеся пустые <p></p>
        const compact = htmlParts
            .join('')
            .replace(/(?:<p><\/p>\s*){2,}/g, '<p></p>');

        return compact;
    }

    // Базовая поддержка markdown для жирного/курсива и инлайн-кода
    function applyBasicMarkdown(safeHtml) {
        let html = safeHtml;
        // **bold**
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1<\/strong>');
        // *italic*
        html = html.replace(/(^|\s)\*(?!\s)([^*]+?)\*(?=\s|$)/g, '$1<em>$2<\/em>');
        // `code`
        html = html.replace(/`([^`]+)`/g, '<code>$1<\/code>');
        return html;
    }
    
    /**
     * Показать индикатор загрузки
     */
    function showLoadingIndicator(chatId) {
        const $chat = $('#' + chatId);
        const $loadingIndicator = $chat.find('.bcc-loading-indicator');
        const $sendBtn = $chat.find('.bcc-send-btn');
        
        $loadingIndicator.show();
        $sendBtn.prop('disabled', true);
        
        // Обновляем статус
        updateChatStatus(chatId, 'typing');
    }
    
    /**
     * Скрыть индикатор загрузки
     */
    function hideLoadingIndicator(chatId) {
        const $chat = $('#' + chatId);
        const $loadingIndicator = $chat.find('.bcc-loading-indicator');
        const $sendBtn = $chat.find('.bcc-send-btn');
        
        $loadingIndicator.hide();
        $sendBtn.prop('disabled', false);
        
        // Обновляем статус
        updateChatStatus(chatId, 'online');
    }
    
    /**
     * Обновление статуса чата
     */
    function updateChatStatus(chatId, status) {
        const $chat = $('#' + chatId);
        const $statusIndicator = $chat.find('.bcc-status-indicator');
        const $statusText = $chat.find('.bcc-status-text');
        
        const statusMap = {
            'online': { color: '#4ade80', text: 'Готов к работе' },
            'typing': { color: '#f59e0b', text: 'Печатает...' },
            'offline': { color: '#ef4444', text: 'Недоступен' }
        };
        
        const statusInfo = statusMap[status] || statusMap['offline'];
        
        $statusIndicator.css('color', statusInfo.color);
        $statusText.text(statusInfo.text);
    }
    
    /**
     * Переключение области загрузки файлов
     */
    function toggleFileUpload(chatId) {
        const $chat = $('#' + chatId);
        const $fileArea = $chat.find('.bcc-file-upload-area');
        
        $fileArea.slideToggle(200);
    }
    
    /**
     * Обработка загрузки файлов
     */
    function handleFileUpload(chatId, files) {
        const $chat = $('#' + chatId);
        const $fileList = $chat.find('.bcc-file-list');
        const maxFiles = parseInt($chat.data('max-files') || 5);
        
        if (files.length > maxFiles) {
            showNotification(`Максимум ${maxFiles} файлов`, 'error');
            return;
        }
        
        // Очищаем предыдущий список
        $fileList.empty();
        
        // Добавляем файлы в список
        Array.from(files).forEach(file => {
            if (validateFile(file)) {
                addFileToList($fileList, file);
            }
        });
        
        // Показываем область файлов
        $chat.find('.bcc-file-upload-area').show();
    }
    
    /**
     * Валидация файла
     */
    function validateFile(file) {
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 
                             'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                             'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (!allowedTypes.includes(file.type)) {
            showNotification(`Неподдерживаемый тип файла: ${file.name}`, 'error');
            return false;
        }
        
        if (file.size > maxSize) {
            showNotification(`Файл слишком большой: ${file.name}`, 'error');
            return false;
        }
        
        return true;
    }
    
    /**
     * Добавление файла в список
     */
    function addFileToList($fileList, file) {
        const fileItem = $(`
            <div class="bcc-file-item">
                <span class="bcc-file-name">${escapeHtml(file.name)}</span>
                <span class="bcc-file-size">${formatFileSize(file.size)}</span>
                <button type="button" class="bcc-file-remove" title="Удалить файл">×</button>
            </div>
        `);
        
        $fileList.append(fileItem);
    }
    
    /**
     * Загрузка истории чата
     */
    function loadChatHistory(chatId) {
        if (!currentSessionId) {
            return;
        }
        
        $.ajax({
            url: bcc_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bcc_get_chat_history',
                nonce: bcc_ajax.nonce,
                session_id: currentSessionId
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    const $chat = $('#' + chatId);
                    const $messagesContainer = $chat.find('.bcc-chat-messages');
                    
                    // Удаляем приветственное сообщение
                    $messagesContainer.find('.bcc-welcome-message').remove();
                    
                    // Добавляем историю
                    response.data.forEach(message => {
                        const messageHtml = createMessageHtml(message.content, message.type);
                        $messagesContainer.append(messageHtml);
                    });
                    
                    // Прокручиваем к последнему сообщению
                    scrollToBottom($messagesContainer);
                }
            },
            error: function() {
                // Игнорируем ошибки загрузки истории
            }
        });
    }
    
    /**
     * Прокрутка к низу контейнера
     */
    function scrollToBottom($container) {
        $container.animate({
            scrollTop: $container[0].scrollHeight
        }, 300);
    }
    
    /**
     * Получение session ID
     */
    function getSessionId(chatId) {
        return localStorage.getItem('bcc_session_' + chatId) || '';
    }
    
    /**
     * Обновление session ID
     */
    function updateSessionId(chatId, sessionId) {
        currentSessionId = sessionId;
        localStorage.setItem('bcc_session_' + chatId, sessionId);
    }
    
    /**
     * Получение инициала агента
     */
    function getAgentInitial() {
        // Можно получить из настроек или использовать по умолчанию
        return 'А';
    }
    
    /**
     * Экранирование HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Форматирование размера файла
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    /**
     * Показать уведомление
     */
    function showNotification(message, type = 'info') {
        // Создаем уведомление
        const notification = $(`
            <div class="bcc-notification bcc-notification-${type}">
                <span class="bcc-notification-message">${escapeHtml(message)}</span>
                <button type="button" class="bcc-notification-close">×</button>
            </div>
        `);
        
        // Добавляем стили если их нет
        if (!$('#bcc-notification-styles').length) {
            $('head').append(`
                <style id="bcc-notification-styles">
                    .bcc-notification {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: white;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        padding: 16px 20px;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                        z-index: 10000;
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        max-width: 400px;
                        animation: slideInRight 0.3s ease-out;
                    }
                    .bcc-notification-success {
                        border-left: 4px solid #10b981;
                    }
                    .bcc-notification-error {
                        border-left: 4px solid #ef4444;
                    }
                    .bcc-notification-warning {
                        border-left: 4px solid #f59e0b;
                    }
                    .bcc-notification-info {
                        border-left: 4px solid #3b82f6;
                    }
                    .bcc-notification-close {
                        background: none;
                        border: none;
                        font-size: 18px;
                        cursor: pointer;
                        color: #6b7280;
                        padding: 0;
                        width: 20px;
                        height: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .bcc-notification-close:hover {
                        color: #374151;
                    }
                    @keyframes slideInRight {
                        from {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                </style>
            `);
        }
        
        // Добавляем уведомление на страницу
        $('body').append(notification);
        
        // Автоматически удаляем через 5 секунд
        setTimeout(() => {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
        
        // Обработка закрытия
        notification.find('.bcc-notification-close').on('click', function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        });
    }
    
    /**
     * Обновление статистики использования токенов
     */
    function updateTokenUsage(tokens) {
        // Можно добавить отображение статистики токенов
        console.log('Tokens used:', tokens);
    }
    
    // Экспорт функций для глобального использования
    window.BizFinChatGPT = {
        sendMessage: sendMessage,
        addMessageToChat: addMessageToChat,
        showNotification: showNotification,
        formatFileSize: formatFileSize
    };
    
})(jQuery);
