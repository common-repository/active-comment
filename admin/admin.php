<?php
if (!defined('ABSPATH')) {
    exit();
}

add_action('admin_menu', 'activeCommentRemoveMenus');
add_action('admin_enqueue_scripts', 'activeCommentAdminAssets');
add_action('admin_init', 'activeCommentSaveOption');

function activeCommentSaveOption() {
    register_setting('Active_API_settings', 'Active_API_settings', 'activeCommentValidation');
}

function activeCommentValidation($settings) {
    return $settings;
}

function activeCommentRemoveMenus() {
    remove_menu_page('edit-comments.php');
}
/* Load Admin Css */
function activeCommentAdminAssets() {
    wp_register_style('active-comment-admin-style', ACTIVE_COMMENT_URL . 'assets/css/admin-style.css', array(), ACTIVE_COMMENTING_PLUGIN_VERSION);
    wp_enqueue_style('active-comment-admin-style');
    wp_register_script('active-comment-admin-script', ACTIVE_COMMENT_URL . 'assets/js/active-activation.js', array(), ACTIVE_COMMENTING_PLUGIN_VERSION);
    wp_enqueue_script('active-comment-admin-script');
}