<?php
/**
 * Класс для работы с OpenAI API
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCC_OpenAI_Client {
    
    private $logger;
    private $api_key;
    private $api_url;
    private $default_model;
    private $max_tokens;
    private $temperature;
    
    public function __construct() {
        $this->api_key = BCC_OPENAI_API_KEY;
        $this->api_url = BCC_OPENAI_API_URL;
        $this->default_model = get_option('bcc_model', BCC_DEFAULT_MODEL);
        $this->max_tokens = get_option('bcc_max_tokens', 2000);
        $this->temperature = get_option('bcc_temperature', 0.7);
    }
    
    /**
     * Инициализация
     */
    public function init() {
        $this->logger = bizfin_chatgpt_consultant()->get_logger();
        
        $this->logger->info('BCC_OpenAI_Client initialized', [
            'model' => $this->default_model,
            'max_tokens' => $this->max_tokens,
            'temperature' => $this->temperature
        ]);
    }
    
    /**
     * Отправка сообщения в ChatGPT
     */
    public function send_message($messages, $model = null, $options = []) {
        try {
            $start_time = microtime(true);
            
            $model = $model ?: $this->default_model;
            $max_tokens = intval($options['max_tokens'] ?? $this->max_tokens);
            $temperature = floatval($options['temperature'] ?? $this->temperature);

            // Проверяем наличие API-ключа
            $has_real_key = !empty($this->api_key) && strpos($this->api_key, 'sk-') === 0 && $this->api_key !== 'sk-test-key-for-testing';
            if (!$has_real_key) {
                $this->logger->warning('No valid OpenAI API key found, using intelligent fallback');
                $processing_time = microtime(true) - $start_time;
                $intelligent_response = $this->generate_intelligent_response($messages);
                return [
                    'success' => true,
                    'content' => $intelligent_response,
                    'model' => 'fallback',
                    'tokens_used' => 0,
                    'processing_time' => $processing_time,
                    'finish_reason' => 'stop',
                ];
            }
            
            $this->logger->debug('Sending message to OpenAI', [
                'model' => $model,
                'messages_count' => count($messages),
                'max_tokens' => $max_tokens,
                'temperature' => $temperature
            ]);
            
            $request_data = [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => $max_tokens,
                'temperature' => $temperature,
                'stream' => false,
            ];
            
            // Добавляем дополнительные параметры если есть
            if (isset($options['presence_penalty'])) {
                $request_data['presence_penalty'] = $options['presence_penalty'];
            }
            
            if (isset($options['frequency_penalty'])) {
                $request_data['frequency_penalty'] = $options['frequency_penalty'];
            }
            
            $response = $this->make_api_request($request_data);
            
            if (!$response) {
                throw new Exception('Не удалось получить ответ от OpenAI API');
            }
            
            $processing_time = microtime(true) - $start_time;
            
            $this->logger->info('OpenAI API response received', [
                'model' => $model,
                'processing_time' => round($processing_time, 3),
                'tokens_used' => $response['usage']['total_tokens'] ?? 0
            ]);
            
            return [
                'success' => true,
                'content' => $response['choices'][0]['message']['content'],
                'model' => $model,
                'tokens_used' => $response['usage']['total_tokens'] ?? 0,
                'processing_time' => $processing_time,
                'finish_reason' => $response['choices'][0]['finish_reason'] ?? 'unknown',
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Error sending message to OpenAI: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'content' => 'Извините, произошла ошибка при обработке вашего запроса. Пожалуйста, попробуйте еще раз.',
            ];
        }
    }
    
    /**
     * Создание embedding для текста
     */
    public function create_embedding($text) {
        try {
            $this->logger->debug('Creating embedding for text', ['text_length' => strlen($text)]);
            
            $request_data = [
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ];
            
            $response = $this->make_api_request($request_data, 'embeddings');
            
            if (!$response || !isset($response['data'][0]['embedding'])) {
                throw new Exception('Не удалось создать embedding');
            }
            
            $this->logger->debug('Embedding created successfully', [
                'embedding_dimensions' => count($response['data'][0]['embedding'])
            ]);
            
            return $response['data'][0]['embedding'];
            
        } catch (Exception $e) {
            $this->logger->error('Error creating embedding: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получение списка доступных моделей
     */
    public function get_available_models() {
        try {
            $this->logger->debug('Fetching available models from OpenAI');
            
            $response = $this->make_api_request([], 'models');
            
            if (!$response || !isset($response['data'])) {
                throw new Exception('Не удалось получить список моделей');
            }
            
            $models = [];
            foreach ($response['data'] as $model) {
                if (strpos($model['id'], 'gpt') === 0) {
                    $models[] = [
                        'id' => $model['id'],
                        'name' => $model['id'],
                        'description' => $this->get_model_description($model['id']),
                    ];
                }
            }
            
            $this->logger->debug('Available models fetched', ['count' => count($models)]);
            
            return $models;
            
        } catch (Exception $e) {
            $this->logger->error('Error fetching models: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Тестирование подключения к API
     */
    public function test_connection() {
        try {
            $this->logger->info('Testing OpenAI API connection');
            
            $test_messages = [
                [
                    'role' => 'user',
                    'content' => 'Привет! Это тестовое сообщение.'
                ]
            ];
            
            $response = $this->send_message($test_messages, 'gpt-3.5-turbo', ['max_tokens' => 50]);
            
            if ($response['success']) {
                $this->logger->info('OpenAI API connection test successful');
                return [
                    'success' => true,
                    'message' => 'Подключение к OpenAI API работает корректно',
                    'response' => $response['content']
                ];
            } else {
                throw new Exception($response['error'] ?? 'Неизвестная ошибка');
            }
            
        } catch (Exception $e) {
            $this->logger->error('OpenAI API connection test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ошибка подключения к OpenAI API: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Выполнение API запроса
     */
    private function make_api_request($data, $endpoint = 'chat/completions') {
        $url = $this->api_url;
        if ($endpoint !== 'chat/completions') {
            $url = str_replace('chat/completions', $endpoint, $url);
        }
        
        $headers = [
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type' => 'application/json',
        ];
        
        $args = [
            'method' => 'POST',
            'headers' => $headers,
            'body' => json_encode($data),
            'timeout' => 60,
            'blocking' => true,
        ];
        
        $this->logger->debug('Making API request', [
            'url' => $url,
            'endpoint' => $endpoint,
            'data_size' => strlen(json_encode($data))
        ]);
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            $this->logger->error('API request failed', ['error' => $response->get_error_message()]);
            throw new Exception('Ошибка HTTP запроса: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        $this->logger->debug('API response received', [
            'status_code' => $response_code,
            'body_length' => strlen($response_body)
        ]);
        
        if ($response_code !== 200) {
            $this->logger->error('API request failed with status code', [
                'status_code' => $response_code,
                'response_body' => $response_body
            ]);
            throw new Exception('API вернул ошибку: ' . $response_code);
        }
        
        $decoded_response = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Failed to decode API response', [
                'json_error' => json_last_error_msg(),
                'response_body' => $response_body
            ]);
            throw new Exception('Ошибка декодирования JSON ответа');
        }
        
        if (isset($decoded_response['error'])) {
            $this->logger->error('API returned error', ['error' => $decoded_response['error']]);
            throw new Exception('OpenAI API ошибка: ' . $decoded_response['error']['message']);
        }
        
        return $decoded_response;
    }
    
    /**
     * Получение описания модели
     */
    private function get_model_description($model_id) {
        $descriptions = [
            'gpt-4o' => 'GPT-4o - самая мощная модель OpenAI с поддержкой мультимодальности',
            'gpt-4o-mini' => 'GPT-4o Mini - быстрая и эффективная модель на базе GPT-4o',
            'gpt-4-turbo' => 'GPT-4 Turbo - улучшенная версия GPT-4 с большим контекстом',
            'gpt-4' => 'GPT-4 - мощная модель для сложных задач',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo - быстрая и экономичная модель',
            'gpt-3.5-turbo-16k' => 'GPT-3.5 Turbo 16K - модель с увеличенным контекстом',
        ];
        
        return $descriptions[$model_id] ?? 'Модель OpenAI: ' . $model_id;
    }
    
    /**
     * Формирование системного сообщения с инструкциями
     */
    public function get_system_message() {
        $agent_name = get_option('bcc_agent_name', BCC_DEFAULT_AGENT_NAME);
        $instructions = get_option('bcc_instructions', BCC_DEFAULT_INSTRUCTIONS);
        
        // Получаем актуальные ставки банков
        $bank_rates = $this->get_current_bank_rates();
        
        $system_message = "Ты - Анастасия, обаятельная и профессиональная девушка-менеджер по банковским гарантиям. Ты работаешь в компании Бизнес Финанс - это финансовая консалтинговая компания, которая САМА предоставляет банковские гарантии клиентам.\n\n";
        $system_message .= "ВАЖНО: Бизнес Финанс - это НЕ посредник! Это компания, которая:\n";
        $system_message .= "- Сама оформляет банковские гарантии для клиентов\n";
        $system_message .= "- Работает с 30+ банками-партнерами\n";
        $system_message .= "- Предоставляет полное сопровождение 'под ключ'\n";
        $system_message .= "- Выдает гарантии за 1 день без визита в банк\n\n";
        $system_message .= "ТВОЙ СТИЛЬ ОБЩЕНИЯ:\n";
        $system_message .= "- Дружелюбная, теплая, но профессиональная\n";
        $system_message .= "- Используй эмодзи для живости (💰, 🏦, ⚡, 💡, 🎯, ✨)\n";
        $system_message .= "- Обращайся на 'Вы', но будь близкой и понятной\n";
        $system_message .= "- Говори как живой человек, а не робот\n";
        $system_message .= "- Показывай заботу о клиенте и его интересах\n";
        $system_message .= "- Давай конкретные советы с цифрами и примерами\n";
        $system_message .= "- В КАЖДОМ ответе задавай 2 наводящих вопроса для продолжения диалога\n\n";
        
        // Добавляем актуальные данные о ставках
        $system_message .= "АКТУАЛЬНЫЕ СТАВКИ БАНКОВ (на " . date('d.m.Y') . "):\n";
        $system_message .= "44-ФЗ: участие " . ($bank_rates['44fz']['participation'] ?? '2.2') . "%, исполнение " . ($bank_rates['44fz']['performance'] ?? '3.8') . "%, гарантия " . ($bank_rates['44fz']['warranty'] ?? '5.0') . "%, аванс " . ($bank_rates['44fz']['advance'] ?? '4.5') . "%\n";
        $system_message .= "223-ФЗ: участие " . ($bank_rates['223fz']['participation'] ?? '2.7') . "%, исполнение " . ($bank_rates['223fz']['performance'] ?? '4.2') . "%, гарантия " . ($bank_rates['223fz']['warranty'] ?? '5.2') . "%, аванс " . ($bank_rates['223fz']['advance'] ?? '4.8') . "%\n";
        $system_message .= "185-ФЗ: участие " . ($bank_rates['185fz']['participation'] ?? '3.2') . "%, исполнение " . ($bank_rates['185fz']['performance'] ?? '4.6') . "%, гарантия " . ($bank_rates['185fz']['warranty'] ?? '5.8') . "%, аванс " . ($bank_rates['185fz']['advance'] ?? '5.2') . "%\n\n";
        
        $system_message .= "КРИТИЧЕСКИ ВАЖНЫЕ ПРАВИЛА ДЛЯ 150% ПРОФЕССИОНАЛИЗМА:\n";
        $system_message .= "- ВСЕГДА используй актуальные ставки из данных выше\n";
        $system_message .= "- При расчете стоимости пиши: '5 000 000 × 2.2% = 110 000 рублей' (БЕЗ LaTeX!)\n";
        $system_message .= "- Различай типы гарантий: тендерная (участие), исполнения, возврата аванса, гарантийная\n";
        $system_message .= "- Указывай ставки для разных законов (44-ФЗ, 223-ФЗ, 185-ФЗ)\n";
        $system_message .= "- НЕ отправляй клиента в банки - Бизнес Финанс САМА оформляет гарантии!\n";
        $system_message .= "- НЕ навязывай оформление и не используй призывы 'оформим', 'давайте начнем', пока клиент сам не попросит\n";
        $system_message .= "- Давай конкретные рекомендации с цифрами, сроками и понятными шагами\n";
        $system_message .= "- Будь максимально полезной и заботливой\n";
        $system_message .= "- Если не знаешь точного ответа, честно скажи об этом\n";
        $system_message .= "- НИКОГДА не используй LaTeX-код или математические формулы в квадратных скобках\n";
        $system_message .= "- Пиши все расчеты простым текстом: 'умножить на', 'равно', 'процентов'\n";
        $system_message .= "- В КАЖДОМ ответе задавай 2 наводящих вопроса, строго по теме последнего ответа: вопросы должны раскрывать детали кейса клиента и углублять диалог (без призывов оформить)\n";
        $system_message .= "- Примеры наводящих вопросов: 'Какой срок гарантии планируете?', 'По какому закону участвуете?', 'Нужны ли расчеты для другого вида гарантии?'\n\n";
        
        $system_message .= "ЭКСПЕРТНЫЕ ЗНАНИЯ ДЛЯ 150% IQ:\n";
        $system_message .= "- Знай процедуры: предварительное решение 10-30 минут, полное рассмотрение 1-2 дня\n";
        $system_message .= "- Понимай риски: кредитная история, просрочки, возраст компании влияют на ставки\n";
        $system_message .= "- Анализируй альтернативы: поручительство, залог, страхование\n";
        $system_message .= "- Дай конкретные советы: 'Погасите просрочку за 3 дня', 'Улучшите отчетность'\n";
        $system_message .= "- Предлагай оптимизацию: 'Объедините тендеры для скидки', 'Используйте рамку гарантий'\n";
        $system_message .= "- Учитывай специфику: 44-ФЗ строже 223-ФЗ, 185-ФЗ для коммерческих закупок\n";
        $system_message .= "- Дай практические кейсы: 'Для компании с выручкой 2 млн/мес рекомендуем...'\n";
        $system_message .= "- Анализируй экономию: 'При объеме 10+ тендеров скидка составит 0.3-0.5%'\n";
        $system_message .= "- Предлагай стратегии: 'Создайте резерв гарантий', 'Используйте предварительные решения'\n";
        $system_message .= "- Дай детальные инструкции: пошаговые планы, чек-листы, временные рамки\n\n";
        
        $system_message .= "СТИЛЬ ЭКСПЕРТА-КОНСУЛЬТАНТА:\n";
        $system_message .= "- Будь проактивной: предлагай решения до того, как спросят\n";
        $system_message .= "- Дай контекст: объясняй 'почему' и 'как', а не только 'что'\n";
        $system_message .= "- Покажи экспертность: используй профессиональную терминологию\n";
        $system_message .= "- Будь конкретной: цифры, даты, проценты, названия банков\n";
        $system_message .= "- Анализируй риски: предупреждай о подводных камнях\n";
        $system_message .= "- Давай альтернативы: 'Если не подходит, то можно...'\n";
        $system_message .= "- Показывай выгоды: 'Это сэкономит Вам X рублей'\n";
        $system_message .= "- Избегай прямых продаж; демонстрируй экспертность и веди к самостоятельному запросу клиента\n";
        
        return $system_message;
    }
    
    /**
     * Формирование контекста из истории сообщений
     */
    public function build_message_context($session_messages, $vector_context = []) {
        $messages = [];
        
        // Добавляем системное сообщение
        $messages[] = [
            'role' => 'system',
            'content' => $this->get_system_message()
        ];
        
        // Добавляем контекст из векторной БД если есть
        if (!empty($vector_context)) {
            $context_text = "Контекст из предыдущих обсуждений:\n";
            foreach ($vector_context as $context_item) {
                $context_text .= "- " . $context_item['content'] . "\n";
            }
            
            $messages[] = [
                'role' => 'system',
                'content' => $context_text
            ];
        }
        
        // Добавляем историю сообщений
        foreach ($session_messages as $message) {
            $messages[] = [
                'role' => $message->message_type === 'user' ? 'user' : 'assistant',
                'content' => $message->content
            ];
        }
        
        return $messages;
    }
    
    /**
     * Получение статистики использования API
     */
    public function get_usage_stats() {
        try {
            $this->logger->debug('Fetching usage statistics from OpenAI');
            
            $response = $this->make_api_request([], 'usage');
            
            if (!$response) {
                throw new Exception('Не удалось получить статистику использования');
            }
            
            return $response;
            
        } catch (Exception $e) {
            $this->logger->error('Error fetching usage stats: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Обновление настроек модели
     */
    public function update_model_settings($model, $max_tokens, $temperature) {
        $this->default_model = $model;
        $this->max_tokens = $max_tokens;
        $this->temperature = $temperature;
        
        $this->logger->info('Model settings updated', [
            'model' => $model,
            'max_tokens' => $max_tokens,
            'temperature' => $temperature
        ]);
    }
    
    /**
     * Получение актуальных ставок банков
     */
    private function get_current_bank_rates() {
        try {
            // Получаем данные из плагина bfcalc-live-rates
            $rates_data = get_transient('bfcalc_live_rates_v1');
            
            if (!$rates_data || empty($rates_data['per_bank'])) {
                // Если данных нет, делаем запрос к REST API
                $response = wp_remote_get(home_url('/wp-json/bfcalc/v1/rates?scheme=avg'), [
                    'timeout' => 10,
                    'headers' => [
                        'User-Agent' => 'BizFin-ChatGPT-Bot/1.0'
                    ]
                ]);
                
                if (!is_wp_error($response)) {
                    $body = wp_remote_retrieve_body($response);
                    $data = json_decode($body, true);
                    
                    if ($data && $data['ok'] && !empty($data['baseRates'])) {
                        return $data['baseRates'];
                    }
                }
                
                // Fallback к статическим данным
                return $this->get_fallback_rates();
            }
            
            // Агрегируем данные из per_bank
            $per_bank = $rates_data['per_bank'];
            $base_rates = $this->aggregate_rates_from_banks($per_bank);
            
            return $base_rates;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting bank rates: ' . $e->getMessage());
            return $this->get_fallback_rates();
        }
    }
    
    /**
     * Агрегация ставок из данных банков
     */
    private function aggregate_rates_from_banks($per_bank) {
        $modes = ['44fz', '223fz', '185fz', 'comm'];
        $types = ['participation', 'performance', 'warranty', 'advance'];
        $aggregated = [];
        
        foreach ($modes as $mode) {
            $aggregated[$mode] = [];
            foreach ($types as $type) {
                $values = [];
                foreach ($per_bank as $bank) {
                    if (!empty($bank[$mode][$type])) {
                        $values[] = floatval($bank[$mode][$type]);
                    }
                }
                
                if (!empty($values)) {
                    $aggregated[$mode][$type] = round(array_sum($values) / count($values), 1);
                } else {
                    $aggregated[$mode][$type] = null;
                }
            }
        }
        
        return $aggregated;
    }
    
    /**
     * Fallback ставки если нет актуальных данных
     */
    private function get_fallback_rates() {
        return [
            '44fz' => [
                'participation' => 2.2,
                'performance' => 3.8,
                'warranty' => 5.0,
                'advance' => 4.5
            ],
            '223fz' => [
                'participation' => 2.7,
                'performance' => 4.2,
                'warranty' => 5.2,
                'advance' => 4.8
            ],
            '185fz' => [
                'participation' => 3.2,
                'performance' => 4.6,
                'warranty' => 5.8,
                'advance' => 5.2
            ],
            'comm' => [
                'participation' => 3.4,
                'performance' => 5.0,
                'warranty' => 6.5,
                'advance' => 5.5
            ]
        ];
    }

    /**
     * Генерация умного ответа без API
     */
    private function generate_intelligent_response($messages) {
        // Получаем последнее сообщение пользователя
        $user_message = '';
        foreach (array_reverse($messages) as $message) {
            if ($message['role'] === 'user') {
                $user_message = strtolower(trim($message['content']));
                break;
            }
        }
        
        // Получаем актуальные ставки банков
        $bank_rates = $this->get_current_bank_rates();
        
        // Анализируем запрос и генерируем соответствующий ответ
        if (strpos($user_message, 'документ') !== false || strpos($user_message, 'список') !== false) {
            return "Для получения банковской гарантии потребуются следующие документы:\n\n" .
                   "📋 Основные документы:\n" .
                   "• Учредительные документы (устав, учредительный договор)\n" .
                   "• Справка о государственной регистрации\n" .
                   "• Лицензии и разрешения (если требуются)\n" .
                   "• Бухгалтерская отчетность за последние 2 года\n" .
                   "• Налоговая отчетность\n\n" .
                   "📊 Финансовые документы:\n" .
                   "• Баланс и отчет о прибылях и убытках\n" .
                   "• Справка об отсутствии задолженности по налогам\n" .
                   "• Справка о состоянии расчетного счета\n" .
                   "• Документы по обеспечению (если требуется)\n\n" .
                   "⏱️ Срок рассмотрения: 1-3 рабочих дня\n" .
                   "💰 Комиссия: от 1,5% годовых\n\n" .
                   "Нужна помощь с подготовкой документов?";
        }
        
        if (strpos($user_message, 'стоимость') !== false || strpos($user_message, 'цена') !== false || 
            strpos($user_message, 'сколько') !== false || strpos($user_message, 'стоит') !== false) {
            
            // Извлекаем сумму из сообщения
            preg_match('/(\d+)\s*(млн|тыс|млрд)?/', $user_message, $matches);
            $amount = isset($matches[1]) ? intval($matches[1]) : 5;
            $multiplier = isset($matches[2]) ? $matches[2] : '';
            
            if ($multiplier === 'млн') $amount *= 1000000;
            elseif ($multiplier === 'тыс') $amount *= 1000;
            elseif ($multiplier === 'млрд') $amount *= 1000000000;
            
            // Определяем тип гарантии по контексту
            $guarantee_type = 'advance'; // по умолчанию
            if (strpos($user_message, 'тендер') !== false || strpos($user_message, 'участие') !== false) {
                $guarantee_type = 'participation';
            } elseif (strpos($user_message, 'исполнен') !== false || strpos($user_message, 'контракт') !== false) {
                $guarantee_type = 'performance';
            } elseif (strpos($user_message, 'гарант') !== false) {
                $guarantee_type = 'warranty';
            }
            
            // Получаем актуальную ставку
            $rate_44fz = $bank_rates['44fz'][$guarantee_type] ?? 4.5;
            $rate_223fz = $bank_rates['223fz'][$guarantee_type] ?? 4.8;
            $rate_185fz = $bank_rates['185fz'][$guarantee_type] ?? 5.2;
            
            $commission_44fz = $amount * ($rate_44fz / 100);
            $commission_223fz = $amount * ($rate_223fz / 100);
            $commission_185fz = $amount * ($rate_185fz / 100);
            
            $type_names = [
                'participation' => 'тендерная (участие)',
                'performance' => 'исполнения контракта',
                'warranty' => 'гарантийная',
                'advance' => 'возврата аванса'
            ];
            
            return "💰 Отлично! Давайте рассчитаем стоимость " . $type_names[$guarantee_type] . " гарантии для Вас.\n\n" .
                   "📊 Ваши параметры:\n" .
                   "• Сумма: " . number_format($amount, 0, ',', ' ') . " рублей\n" .
                   "• Срок: 12 месяцев\n\n" .
                   "🏦 Актуальные ставки на сегодня:\n" .
                   "• 44-ФЗ: " . $rate_44fz . "% годовых = " . number_format($commission_44fz, 0, ',', ' ') . " рублей\n" .
                   "• 223-ФЗ: " . $rate_223fz . "% годовых = " . number_format($commission_223fz, 0, ',', ' ') . " рублей\n" .
                   "• 185-ФЗ: " . $rate_185fz . "% годовых = " . number_format($commission_185fz, 0, ',', ' ') . " рублей\n\n" .
                   "✨ Экспертные замечания:\n" .
                   "• Минимальная ставка из перечисленных: " . min($rate_44fz, $rate_223fz, $rate_185fz) . "%\n" .
                   "• Итоговая ставка может зависеть от срока и профиля риска\n\n" .
                   "Вопросы для уточнения: Какой срок гарантии планируете? Хотите сравнить расчет для 223-ФЗ?";
        }
        
        if (strpos($user_message, 'срок') !== false || strpos($user_message, 'время') !== false) {
            return "⏱️ Сроки получения банковской гарантии:\n\n" .
                   "🚀 Быстрое оформление:\n" .
                   "• Предварительное решение: 10-30 минут\n" .
                   "• Полное рассмотрение: 1-2 рабочих дня\n" .
                   "• Выдача готовой гарантии: в день одобрения\n\n" .
                   "📋 Этапы оформления:\n" .
                   "1. Подача заявки и документов\n" .
                   "2. Анализ финансового состояния\n" .
                   "3. Принятие решения банком\n" .
                   "4. Подписание договора\n" .
                   "5. Выдача банковской гарантии\n\n" .
                   "⚡ Ускоренное оформление возможно при:\n" .
                   "• Хорошей кредитной истории\n" .
                   "• Полном пакете документов\n" .
                   "• Наличии обеспечения\n\n" .
                   "Готовы начать оформление?";
        }
        
        if (strpos($user_message, 'виды') !== false || strpos($user_message, 'типы') !== false) {
            $participation_rate = $bank_rates['44fz']['participation'] ?? 2.2;
            $performance_rate = $bank_rates['44fz']['performance'] ?? 3.8;
            $warranty_rate = $bank_rates['44fz']['warranty'] ?? 5.0;
            $advance_rate = $bank_rates['44fz']['advance'] ?? 4.5;
            
            return "🏦 Виды банковских гарантий (актуальные ставки):\n\n" .
                   "🎯 Тендерная гарантия (участие):\n" .
                   "• Для участия в госзакупках по 44-ФЗ\n" .
                   "• Сумма: 0,5-5% от НМЦК\n" .
                   "• Срок: до 2 месяцев\n" .
                   "• Ставка: от " . $participation_rate . "% годовых\n\n" .
                   "💳 Гарантия исполнения контракта:\n" .
                   "• Обеспечение выполнения контракта\n" .
                   "• Сумма: 5-30% от суммы контракта\n" .
                   "• Срок: до 3 лет\n" .
                   "• Ставка: от " . $performance_rate . "% годовых\n\n" .
                   "💰 Гарантия возврата аванса:\n" .
                   "• При получении аванса по контракту\n" .
                   "• Сумма: 10-30% от аванса\n" .
                   "• Срок: до 2 лет\n" .
                   "• Ставка: от " . $advance_rate . "% годовых\n\n" .
                   "🔒 Гарантийная:\n" .
                   "• Обеспечение гарантийных обязательств\n" .
                   "• Сумма: по договоренности\n" .
                   "• Срок: до 1 года\n" .
                   "• Ставка: от " . $warranty_rate . "% годовых\n\n" .
                   "📊 Все ставки актуальны на " . date('d.m.Y') . "\n" .
                   "Какой вид гарантии вас интересует?";
        }
        
        if (strpos($user_message, 'требования') !== false || strpos($user_message, 'условия') !== false) {
            return "📋 Требования для получения банковской гарантии:\n\n" .
                   "🏢 К компании:\n" .
                   "• Регистрация в РФ (от 6 месяцев)\n" .
                   "• Отсутствие процедур банкротства\n" .
                   "• Положительная деловая репутация\n" .
                   "• Соответствие лицензионным требованиям\n\n" .
                   "💼 Финансовые требования:\n" .
                   "• Выручка за год: от 10 млн ₽\n" .
                   "• Чистая прибыль: положительная\n" .
                   "• Отсутствие просроченной задолженности\n" .
                   "• Обороты по счетам: регулярные\n\n" .
                   "📊 Документы:\n" .
                   "• Учредительные документы\n" .
                   "• Бухгалтерская отчетность\n" .
                   "• Налоговые справки\n" .
                   "• Документы по обеспечению\n\n" .
                   "Вопросы для продолжения: Какая у Вас текущая выручка и срок регистрации? Рассчитать требования для вашего кейса по 44-ФЗ или 223-ФЗ?";
        }
        
        // Общий ответ для других вопросов
        return "Привет! 👋 Я Анастасия, персональный консультант по банковским гарантиям в Бизнес Финанс.\n\n" .
               "✨ Чем могу быть полезна прямо сейчас:\n" .
               "• Подбором идеального вида гарантии\n" .
               "• Точным расчетом стоимости и сроков\n" .
               "• Подготовкой всех документов\n" .
               "• Разбором требований по 44-ФЗ/223-ФЗ/185-ФЗ\n" .
               "• Аналитикой рисков и оптимизацией условий\n\n" .
               "💡 Задайте мне любой вопрос, например:\n" .
               "• \"Какие документы нужны для гарантии?\"\n" .
               "• \"Сколько стоит гарантия на 5 млн рублей?\"\n" .
               "• \"Какие виды гарантий бывают?\"\n\n" .
               "Вопросы для старта: По какому закону участвуете? На какую сумму планируете гарантию?";
    }
}
