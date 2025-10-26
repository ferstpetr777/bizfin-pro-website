<?php
// Check user permissions for AI-Scribe
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

echo "<h1>AI-Scribe User Permissions Check</h1>\n";

// Check if user is logged in
if (is_user_logged_in()) {
    $user = wp_get_current_user();
    echo "<h2>Current User:</h2>\n";
    echo "<p><strong>ID:</strong> " . $user->ID . "</p>\n";
    echo "<p><strong>Login:</strong> " . $user->user_login . "</p>\n";
    echo "<p><strong>Email:</strong> " . $user->user_email . "</p>\n";
    echo "<p><strong>Roles:</strong> " . implode(', ', $user->roles) . "</p>\n";
    
    echo "<h2>Capabilities Check:</h2>\n";
    echo "<p><strong>manage_options:</strong> " . (current_user_can('manage_options') ? '✅ YES' : '❌ NO') . "</p>\n";
    echo "<p><strong>edit_posts:</strong> " . (current_user_can('edit_posts') ? '✅ YES' : '❌ NO') . "</p>\n";
    echo "<p><strong>edit_pages:</strong> " . (current_user_can('edit_pages') ? '✅ YES' : '❌ NO') . "</p>\n";
    echo "<p><strong>administrator:</strong> " . (current_user_can('administrator') ? '✅ YES' : '❌ NO') . "</p>\n";
    
    echo "<h2>All Capabilities:</h2>\n";
    $caps = $user->allcaps;
    echo "<pre>" . print_r($caps, true) . "</pre>\n";
    
} else {
    echo "<p>❌ User is not logged in</p>\n";
}

echo "<h2>AI-Scribe Page Access Test:</h2>\n";
echo "<p><a href='/wp-admin/admin.php?page=ai_scribe_saved_shortcodes'>Test Saved Shortcodes Page</a></p>\n";
echo "<p><a href='/wp-admin/admin.php?page=ai_scribe_generate_article'>Test Generate Article Page</a></p>\n";
?>




