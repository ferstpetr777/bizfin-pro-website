<?php
// Простой тест OpenAI API
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

$ai_settings = get_option('ab_gpt_ai_engine_settings');
$api_key = $ai_settings['api_key'] ?? '';

echo "API Key: " . substr($api_key, 0, 10) . "...\n";
echo "Length: " . strlen($api_key) . "\n";

// Простой тест
$response = wp_remote_get('https://api.openai.com/v1/models', [
    'headers' => ['Authorization' => 'Bearer ' . $api_key]
]);

if (is_wp_error($response)) {
    echo "Error: " . $response->get_error_message() . "\n";
} else {
    $code = wp_remote_retrieve_response_code($response);
    echo "HTTP Code: $code\n";
    
    if ($code === 200) {
        echo "SUCCESS: API key is valid\n";
    } else {
        $body = wp_remote_retrieve_body($response);
        echo "FAILED: " . substr($body, 0, 200) . "\n";
    }
}
?>




