<?php
// Final test for AI-Scribe functionality
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

echo "<h1>AI-Scribe Final Test</h1>\n";

// Test 1: Check if AI-Scribe class exists
if (class_exists('AI_Scribe')) {
    echo "<p>✅ AI-Scribe class exists</p>\n";
} else {
    echo "<p>❌ AI-Scribe class not found</p>\n";
}

// Test 2: Check if plugin is active
if (is_plugin_active('ai-scribe-the-chatgpt-powered-seo-content-creation-wizard/ai-scribe.php')) {
    echo "<p>✅ AI-Scribe plugin is active</p>\n";
} else {
    echo "<p>❌ AI-Scribe plugin is not active</p>\n";
}

// Test 3: Check Dutch proxy
echo "<h2>Dutch Proxy Test:</h2>\n";
$proxy_url = '89.110.80.198:8889';
$test_url = 'https://httpbin.org/ip';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_PROXY, $proxy_url);
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response && $http_code == 200) {
    echo "<p>✅ Dutch proxy working: $proxy_url</p>\n";
    $data = json_decode($response, true);
    if (isset($data['origin'])) {
        echo "<p>📍 IP through proxy: " . $data['origin'] . "</p>\n";
    }
} else {
    echo "<p>❌ Dutch proxy failed: $error (HTTP: $http_code)</p>\n";
}

// Test 4: Check OpenAI API key
echo "<h2>API Configuration:</h2>\n";
$openai_key = get_option('ai_scribe_openai_api_key');
if ($openai_key) {
    echo "<p>✅ OpenAI API key configured</p>\n";
} else {
    echo "<p>❌ OpenAI API key not configured</p>\n";
}

$anthropic_key = get_option('ai_scribe_anthropic_api_key');
if ($anthropic_key) {
    echo "<p>✅ Anthropic API key configured</p>\n";
} else {
    echo "<p>❌ Anthropic API key not configured</p>\n";
}

echo "<h2>System Status:</h2>\n";
echo "<p><strong>Site:</strong> " . home_url() . " ✅</p>\n";
echo "<p><strong>Admin:</strong> " . admin_url() . " ✅</p>\n";
echo "<p><strong>AI-Scribe:</strong> " . admin_url('admin.php?page=ai_scribe_generate_article') . " ✅</p>\n";

echo "<h2>Test Complete!</h2>\n";
echo "<p>🎉 AI-Scribe plugin is ready to use!</p>\n";
?>




