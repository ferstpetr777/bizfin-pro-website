<?php
// Test AI-Scribe menu registration
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

echo "<h1>AI-Scribe Menu Test</h1>\n";

// Test 1: Check if AI-Scribe class exists
if (class_exists('AI_Scribe')) {
    echo "<p>✅ AI-Scribe class exists</p>\n";
} else {
    echo "<p>❌ AI-Scribe class not found</p>\n";
}

// Test 2: Check if plugin is active
if (is_plugin_active('ai-scribe-the-chatgpt-powered-seo-content-creation-wizard/article_builder.php')) {
    echo "<p>✅ AI-Scribe plugin is active</p>\n";
} else {
    echo "<p>❌ AI-Scribe plugin is not active</p>\n";
}

// Test 3: Check admin menu items
global $menu, $submenu;
echo "<h2>Admin Menu Items:</h2>\n";
if (isset($menu)) {
    foreach ($menu as $item) {
        if (isset($item[0]) && (strpos($item[0], 'AI-Scribe') !== false || strpos($item[0], 'AI Writer') !== false)) {
            echo "<p>✅ Found AI-Scribe menu: " . strip_tags($item[0]) . "</p>\n";
        }
    }
} else {
    echo "<p>❌ Menu not loaded (not in admin context)</p>\n";
}

// Test 4: Check AI-Scribe pages
echo "<h2>AI-Scribe Pages:</h2>\n";
$ai_scribe_pages = array(
    'ai_scribe_help' => 'AI-Scribe Help',
    'ai_scribe_generate_article' => 'Generate Article',
    'ai_scribe_saved_shortcodes' => 'Saved Shortcodes',
    'ai_scribe_settings' => 'Settings'
);

foreach ($ai_scribe_pages as $page => $title) {
    $url = admin_url("admin.php?page=$page");
    echo "<p><strong>$title:</strong> <a href='$url' target='_blank'>$url</a></p>\n";
}

// Test 5: Check Elementor conflict
echo "<h2>Elementor Status:</h2>\n";
if (is_plugin_active('elementor/elementor.php')) {
    echo "<p>✅ Elementor is active</p>\n";
} else {
    echo "<p>❌ Elementor is not active</p>\n";
}

if (is_plugin_active('elementor-pro/elementor-pro.php')) {
    echo "<p>✅ Elementor Pro is active</p>\n";
} else {
    echo "<p>❌ Elementor Pro is not active</p>\n";
}

echo "<h2>Test Complete!</h2>\n";
echo "<p>🎯 Check the AI-Scribe menu links above to verify functionality.</p>\n";
?>




