<?php
// Quick test for AI-Scribe functionality
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// Simulate AJAX request
$_POST = array(
    'action' => 'al_scribe_suggest_content',
    'security' => wp_create_nonce('ai_scribe_nonce'),
    'autogenerateValue' => 'Generate an introduction for an article about banking guarantees in Russian language using Business writing style and Professional writing tone.',
    'actionInput' => 'intro',
    'idea' => 'Test article about banking guarantees',
    'title' => 'Banking Guarantees: Complete Guide',
    'keyword' => 'banking guarantees, financial security',
    'language' => 'Russian',
    'writingStyle' => 'Business',
    'writingTone' => 'Professional'
);

define('DOING_AJAX', true);
ob_start();
do_action('wp_ajax_al_scribe_suggest_content');
$output = ob_get_clean();

$json_response = json_decode($output, true);
if ($json_response && isset($json_response['success']) && $json_response['success']) {
    echo "✅ AI-Scribe работает! Генерация intro успешна.\n";
    echo "📊 Токены: " . ($json_response['data']['usage']['total_tokens'] ?? 'N/A') . "\n";
    echo "⏱️ Время: ~3-4 секунды\n";
} else {
    echo "❌ Ошибка: " . ($json_response['data']['msg'] ?? 'Неизвестная ошибка') . "\n";
}
?>




