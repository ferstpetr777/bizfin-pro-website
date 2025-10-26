<?php
// Layout and CSS loading test
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

echo "<h1>Layout and CSS Test</h1>\n";

// Test 1: Check main CSS file
$css_url = 'https://bizfin-pro.ru/wp-content/themes/astra/assets/css/minified/main.min.css?ver=4.11.10';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $css_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_NOBODY, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo "<p>✅ Main CSS file loads correctly: $http_code</p>\n";
} else {
    echo "<p>❌ Main CSS file failed: $http_code</p>\n";
}

// Test 2: Check Google Fonts
$fonts_url = 'https://fonts.googleapis.com/css2?family=Tenor+Sans&display=swap';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fonts_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_NOBODY, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo "<p>✅ Google Fonts load correctly: $http_code</p>\n";
} else {
    echo "<p>❌ Google Fonts failed: $http_code</p>\n";
}

// Test 3: Check if AI-Scribe is interfering
echo "<h2>AI-Scribe Plugin Status:</h2>\n";
if (class_exists('AI_Scribe')) {
    echo "<p>✅ AI-Scribe class exists</p>\n";
} else {
    echo "<p>❌ AI-Scribe class not found</p>\n";
}

// Test 4: Check current theme
$current_theme = wp_get_theme();
echo "<h2>Current Theme:</h2>\n";
echo "<p><strong>Name:</strong> " . $current_theme->get('Name') . "</p>\n";
echo "<p><strong>Version:</strong> " . $current_theme->get('Version') . "</p>\n";

// Test 5: Check active plugins
echo "<h2>Active Plugins:</h2>\n";
$active_plugins = get_option('active_plugins');
foreach ($active_plugins as $plugin) {
    if (strpos($plugin, 'ai-scribe') !== false) {
        echo "<p>🔧 AI-Scribe: $plugin</p>\n";
    } else {
        echo "<p>📦 $plugin</p>\n";
    }
}

echo "<h2>Layout Test Complete!</h2>\n";
echo "<p>🎯 If all tests pass, the layout should work correctly.</p>\n";
?>




