<?php

/*
 * Plugin Name: Active Comment
 * Version: 1.0.0
 * Description: Active Comment is a commenting plugin which allows user to comment on post.
 * Author: Active Comment
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 */
if (!defined('ABSPATH')) {
    exit();
}
define('ACTIVE_COMMENT_DIR', plugin_dir_path(__FILE__));
define('ACTIVE_COMMENT_URL', plugin_dir_url(__FILE__));
define('ACTIVE_COMMENTING_PLUGIN_VERSION', '1.0.0');
$activeComment_settings = get_option('Active_API_settings');
add_action('admin_menu', 'activeCommentAdminMenu');
/* Creating Plugin Admin Menu */
function activeCommentAdminMenu() {
    add_menu_page('Active Comment', 'Active Comment', 'manage_options', 'active-comment', 'activeCommentAdminSettings', ACTIVE_COMMENT_URL . 'assets/images/favicon.ico');
}
require_once(ACTIVE_COMMENT_DIR . 'admin/admin.php');
require_once(ACTIVE_COMMENT_DIR . 'front/front.php');

function activeCommentAdminSettings() {
    global $activeComment_settings;
    require_once(ACTIVE_COMMENT_DIR . 'admin/views/admin-view.php');
}

function activeCommentPluginActivation() {
    add_option('Active_API_settings', array(
        "ActiveComment_apikey"=>"",
        "ActiveComment_secret"=>"",
        "sso_enable"=>"1"
    ));    
}
register_activation_hook( __FILE__, 'activeCommentPluginActivation' );