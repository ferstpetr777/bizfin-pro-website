<?php
/**
 * Класс для шорткода
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCC_Shortcode {
    
    private $logger;
    
    public function __construct() {
        // Логгер будет инициализирован в init()
    }
    
    /**
     * Инициализация
     */
    public function init() {
        $this->logger = bizfin_chatgpt_consultant()->get_logger();
        
        add_shortcode('bizfin_chatgpt', [$this, 'render_chat_shortcode']);
        add_shortcode('bizfin_chatgpt_consultant', [$this, 'render_chat_shortcode']); // Альтернативное название
        
        // Регистрируем скрипты и стили
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        $this->logger->info('BCC_Shortcode initialized');
    }
    
    /**
     * Подключение скриптов и стилей
     */
    public function enqueue_scripts() {
        // Загружаем скрипты на всех страницах для совместимости с Elementor
        $this->load_scripts_and_styles();
    }
    
    /**
     * Загрузка скриптов и стилей
     */
    public function load_scripts_and_styles() {
        // Подключаем стили
        wp_enqueue_style(
            'bcc-frontend-css',
            BCC_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            BCC_VERSION
        );
        
        // Подключаем скрипты
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'bcc-frontend-js',
            BCC_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            BCC_VERSION,
            true
        );
        
        // Локализация для AJAX
        wp_localize_script('bcc-frontend-js', 'bcc_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bcc_nonce'),
            'agent_name' => get_option('bcc_agent_name', BCC_DEFAULT_AGENT_NAME),
        ]);
    }
    
    /**
     * Рендер шорткода чата
     */
    public function render_chat_shortcode($atts) {
        // Загружаем скрипты и стили
        $this->load_scripts_and_styles();
        
        $atts = shortcode_atts([
            'title' => 'Консультант по банковским гарантиям',
            'subtitle' => 'Задайте вопрос нашему AI-консультанту',
            'placeholder' => 'Введите ваш вопрос о банковских гарантиях...',
            'button_text' => 'Отправить',
            'theme' => 'light', // light, dark
            'height' => '600px',
            'width' => '100%',
            'show_files' => 'true',
            'show_history' => 'true',
            'max_files' => '5',
        ], $atts);
        
        // Проверяем, включена ли обработка файлов
        $show_files = get_option('bcc_enable_file_processing', true) && $atts['show_files'] === 'true';
        
        // Генерируем уникальный ID для чата
        $chat_id = 'bcc-chat-' . uniqid();
        
        ob_start();
        ?>
        <div id="<?php echo esc_attr($chat_id); ?>" class="bcc-chat-container bcc-collapsed" data-theme="<?php echo esc_attr($atts['theme']); ?>" style="height: <?php echo esc_attr($atts['height']); ?>; width: <?php echo esc_attr($atts['width']); ?>;">
            
            <!-- Заголовок чата -->
            <div class="bcc-chat-header" onclick="expandChat('<?php echo esc_attr($chat_id); ?>')">
                <div class="bcc-chat-title">
                    <h3><?php echo esc_html($atts['title']); ?></h3>
                    <p><?php echo esc_html($atts['subtitle']); ?></p>
                </div>
                <div class="bcc-chat-status">
                    <span class="bcc-status-indicator" id="bcc-status-<?php echo esc_attr($chat_id); ?>">●</span>
                    <span class="bcc-status-text">Готов к работе</span>
                </div>
            </div>
            
            <!-- Область сообщений -->
            <div class="bcc-chat-messages" id="bcc-messages-<?php echo esc_attr($chat_id); ?>">
                <div class="bcc-welcome-message">
                    <div class="bcc-message bcc-message-assistant">
                        <div class="bcc-message-avatar">
                            <span class="bcc-avatar"><?php echo esc_html(substr(get_option('bcc_agent_name', BCC_DEFAULT_AGENT_NAME), 0, 1)); ?></span>
                        </div>
                        <div class="bcc-message-content">
                            <div class="bcc-message-text">
                                Привет! Я <?php echo esc_html(get_option('bcc_agent_name', BCC_DEFAULT_AGENT_NAME)); ?>, ваш консультант по банковским гарантиям. 
                                Чем могу помочь? Задавайте любые вопросы о банковских гарантиях, их видах, условиях получения и использования.
                            </div>
                            <div class="bcc-message-time"><?php echo current_time('H:i'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Область ввода -->
            <div class="bcc-chat-input-area">
                
                <!-- Загрузка файлов -->
                <?php if ($show_files): ?>
                <div class="bcc-file-upload-area" id="bcc-files-<?php echo esc_attr($chat_id); ?>" style="display: none;">
                    <div class="bcc-file-list" id="bcc-file-list-<?php echo esc_attr($chat_id); ?>"></div>
                    <div class="bcc-file-upload-controls">
                        <input type="file" id="bcc-file-input-<?php echo esc_attr($chat_id); ?>" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx" style="display: none;">
                        <button type="button" class="bcc-file-upload-btn" onclick="document.getElementById('bcc-file-input-<?php echo esc_attr($chat_id); ?>').click();">
                            📎 Выбрать файлы
                        </button>
                        <span class="bcc-file-info">Максимум <?php echo esc_html($atts['max_files']); ?> файлов</span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Поле ввода -->
                <div class="bcc-input-container">
                    <div class="bcc-input-wrapper">
                        <textarea 
                            id="bcc-input-<?php echo esc_attr($chat_id); ?>" 
                            class="bcc-message-input" 
                            placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                            rows="1"
                        ></textarea>
                        
                        <div class="bcc-input-actions">
                            <?php if ($show_files): ?>
                            <button type="button" class="bcc-attach-btn" onclick="toggleFileUpload('<?php echo esc_attr($chat_id); ?>')" title="Прикрепить файл">
                                📎
                            </button>
                            <?php endif; ?>
                            
                            <button type="button" class="bcc-send-btn" id="bcc-send-<?php echo esc_attr($chat_id); ?>" onclick="sendMessage('<?php echo esc_attr($chat_id); ?>')">
                                <span class="bcc-send-text"><?php echo esc_html($atts['button_text']); ?></span>
                                <span class="bcc-send-icon">➤</span>
                            </button>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Индикатор загрузки -->
            <div class="bcc-loading-indicator" id="bcc-loading-<?php echo esc_attr($chat_id); ?>" style="display: none;">
                <div class="bcc-typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="bcc-loading-text"><?php echo esc_html(get_option('bcc_agent_name', BCC_DEFAULT_AGENT_NAME)); ?> печатает...</span>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            initializeChat('<?php echo esc_attr($chat_id); ?>');
        });
        
        function initializeChat(chatId) {
            // Если контейнер свернут — разворачиваем при фокусе в поле или первой отправке
            const container = document.getElementById(chatId);
            // Автоматическое изменение высоты textarea
            const textarea = document.getElementById('bcc-input-' + chatId);
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });
            textarea.addEventListener('focus', function(){
                if (container.classList.contains('bcc-collapsed')) {
                    expandChat(chatId);
                }
            });
            
            // Отправка сообщения по Enter (Shift+Enter для новой строки)
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (container.classList.contains('bcc-collapsed')) {
                        expandChat(chatId);
                    }
                    sendMessage(chatId);
                }
            });
            
            // Обработка загрузки файлов
            const fileInput = document.getElementById('bcc-file-input-' + chatId);
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    handleFileUpload(chatId, e.target.files);
                });
            }
            
            // Загружаем историю чата если есть
            loadChatHistory(chatId);
        }
        
        function sendMessage(chatId) {
            const input = document.getElementById('bcc-input-' + chatId);
            const message = input.value.trim();
            
            if (!message) return;
            const container = document.getElementById(chatId);
            if (container.classList.contains('bcc-collapsed')) {
                expandChat(chatId);
            }
            
            // Добавляем сообщение пользователя в чат
            addMessageToChat(chatId, message, 'user');
            
            // Очищаем поле ввода
            input.value = '';
            input.style.height = 'auto';
            
            // Показываем индикатор загрузки
            showLoadingIndicator(chatId);
            
            // Отправляем AJAX запрос
            jQuery.post(bcc_ajax.ajax_url, {
                action: 'bcc_send_message',
                nonce: bcc_ajax.nonce,
                message: message,
                session_id: getSessionId(chatId)
            }, function(response) {
                hideLoadingIndicator(chatId);
                
                if (response.success) {
                    addMessageToChat(chatId, response.data.response, 'assistant');
                    updateSessionId(chatId, response.data.session_id);
                } else {
                    addMessageToChat(chatId, 'Извините, произошла ошибка. Попробуйте еще раз.', 'assistant', true);
                }
            }).fail(function() {
                hideLoadingIndicator(chatId);
                addMessageToChat(chatId, 'Ошибка соединения. Проверьте интернет-подключение.', 'assistant', true);
            });
        }

        function expandChat(chatId){
            const el = document.getElementById(chatId);
            if (!el) return;
            el.classList.remove('bcc-collapsed');
        }
        
        function addMessageToChat(chatId, message, type, isError = false) {
            const messagesContainer = document.getElementById('bcc-messages-' + chatId);
            const messageDiv = document.createElement('div');
            messageDiv.className = 'bcc-message bcc-message-' + type + (isError ? ' bcc-message-error' : '');
            
            const avatar = type === 'user' ? '👤' : '<?php echo esc_html(substr(get_option('bcc_agent_name', BCC_DEFAULT_AGENT_NAME), 0, 1)); ?>';
            const time = new Date().toLocaleTimeString('ru-RU', {hour: '2-digit', minute: '2-digit'});
            
            const formatted = formatMessageHtml(message);
            messageDiv.innerHTML = `
                <div class="bcc-message-avatar">
                    <span class="bcc-avatar">${avatar}</span>
                </div>
                <div class="bcc-message-content">
                    <div class="bcc-message-text">${formatted}</div>
                    <div class="bcc-message-time">${time}</div>
                </div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        function showLoadingIndicator(chatId) {
            document.getElementById('bcc-loading-' + chatId).style.display = 'block';
            document.getElementById('bcc-send-' + chatId).disabled = true;
        }
        
        function hideLoadingIndicator(chatId) {
            document.getElementById('bcc-loading-' + chatId).style.display = 'none';
            document.getElementById('bcc-send-' + chatId).disabled = false;
        }
        
        function toggleFileUpload(chatId) {
            const fileArea = document.getElementById('bcc-files-' + chatId);
            if (fileArea.style.display === 'none') {
                fileArea.style.display = 'block';
            } else {
                fileArea.style.display = 'none';
            }
        }
        
        function handleFileUpload(chatId, files) {
            const maxFiles = <?php echo intval($atts['max_files']); ?>;
            const fileList = document.getElementById('bcc-file-list-' + chatId);
            
            if (files.length > maxFiles) {
                alert('Максимум ' + maxFiles + ' файлов');
                return;
            }
            
            // Здесь можно добавить логику обработки файлов
            // Пока просто показываем список файлов
            fileList.innerHTML = '';
            for (let i = 0; i < files.length; i++) {
                const fileItem = document.createElement('div');
                fileItem.className = 'bcc-file-item';
                fileItem.innerHTML = `
                    <span class="bcc-file-name">${files[i].name}</span>
                    <span class="bcc-file-size">${formatFileSize(files[i].size)}</span>
                    <button type="button" class="bcc-file-remove" onclick="removeFile(this)">×</button>
                `;
                fileList.appendChild(fileItem);
            }
        }
        
        function removeFile(button) {
            button.parentElement.remove();
        }
        
        function getSessionId(chatId) {
            return localStorage.getItem('bcc_session_' + chatId) || '';
        }
        
        function updateSessionId(chatId, sessionId) {
            localStorage.setItem('bcc_session_' + chatId, sessionId);
        }
        
        function loadChatHistory(chatId) {
            const sessionId = getSessionId(chatId);
            if (!sessionId) return;
            
            jQuery.post(bcc_ajax.ajax_url, {
                action: 'bcc_get_chat_history',
                nonce: bcc_ajax.nonce,
                session_id: sessionId
            }, function(response) {
                if (response.success && response.data.length > 0) {
                    const messagesContainer = document.getElementById('bcc-messages-' + chatId);
                    const welcomeMessage = messagesContainer.querySelector('.bcc-welcome-message');
                    if (welcomeMessage) {
                        welcomeMessage.remove();
                    }
                    
                    response.data.forEach(function(message) {
                        addMessageToChat(chatId, message.content, message.type);
                    });
                }
            });
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Безопасное форматирование: абзацы, списки, переносы строк, базовый markdown
        function formatMessageHtml(text) {
            const escaped = escapeHtml(String(text || ''));
            const lines = escaped.split(/\r?\n/);
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
                if (trimmed === '') { flushList(); htmlParts.push('<p></p>'); return; }
                if (/^(?:[-•*])\s+/.test(trimmed)) { listBuffer.push(applyBasicMarkdown(trimmed.replace(/^(?:[-•*])\s+/, ''))); listOrdered = false; return; }
                const orderedMatch = trimmed.match(/^(\d+)\.\s+(.*)$/);
                if (orderedMatch) { listBuffer.push(applyBasicMarkdown(orderedMatch[2])); listOrdered = true; return; }
                flushList();
                htmlParts.push(`<p>${applyBasicMarkdown(trimmed)}</p>`);
            });
            flushList();
            if (!htmlParts.length) return escaped.replace(/\n/g, '<br>');
            return htmlParts.join('').replace(/(?:<p><\/p>\s*){2,}/g, '<p></p>');
        }
        
        function applyBasicMarkdown(safeHtml) {
            let html = safeHtml;
            html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1<\/strong>');
            html = html.replace(/(^|\s)\*(?!\s)([^*]+?)\*(?=\s|$)/g, '$1<em>$2<\/em>');
            html = html.replace(/`([^`]+)`/g, '<code>$1<\/code>');
            return html;
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Форматирование размера файла
     */
    private function format_file_size($bytes) {
        if ($bytes === 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}
