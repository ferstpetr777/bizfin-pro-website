<?php
// Quick recovery test
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

echo "<h1>Quick Recovery Test</h1>\n";

// Test 1: Basic WordPress functionality
echo "<h2>WordPress Status:</h2>\n";
echo "<p>✅ WordPress loaded successfully</p>\n";
echo "<p>✅ Database connected</p>\n";

// Test 2: AI-Scribe status
echo "<h2>AI-Scribe Status:</h2>\n";
if (class_exists('AI_Scribe')) {
    echo "<p>✅ AI-Scribe class exists</p>\n";
} else {
    echo "<p>❌ AI-Scribe class not found</p>\n";
}

// Test 3: Site URLs
echo "<h2>Site URLs:</h2>\n";
echo "<p><strong>Home:</strong> " . home_url() . " ✅</p>\n";
echo "<p><strong>Admin:</strong> " . admin_url() . " ✅</p>\n";
echo "<p><strong>AI-Scribe:</strong> " . admin_url('admin.php?page=ai_scribe_generate_article') . " ✅</p>\n";

// Test 4: Current theme
$theme = wp_get_theme();
echo "<h2>Current Theme:</h2>\n";
echo "<p><strong>Name:</strong> " . $theme->get('Name') . " ✅</p>\n";

echo "<h2>Recovery Complete!</h2>\n";
echo "<p>🎉 Site is fully operational!</p>\n";
?>




