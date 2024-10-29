<?php
/**
 * The activation settings class.
 */
if (!defined('ABSPATH')) {
    exit();
}
?>
<div class="wrap active-wrap cf">
    <header>
        <h2><?php _e('Active Comment', 'active-plugin-slug') ?></h2>
    </header>
    <?php
    settings_errors();
    ?>
    <div class="activeCommentContent">
        <form action="options.php" method="post">
            <?php
            settings_fields('Active_API_settings');
            ?>
            <ul class="active-options-tab-btns">
                <li class="nav-tab active-active" data-tab="active_options_tab-1"><?php _e('Activation', 'active-plugin-slug') ?></li>
                <li class="nav-tab" data-tab="active_options_tab-2"><?php _e('SSO Settings', 'active-plugin-slug') ?></li>
            </ul>
            <div style="clear: both;"></div>        
            <div id="active_options_tab-1" class="active-tab-frame active-active">
                <div class="active_options_container">
                    <div class="active-row">
                        <label>
                            <span class="active_property_title"><?php _e('API Key', 'Active Comment'); ?></span>
                            <input type="text" class="active-row-field" name="Active_API_settings[ActiveComment_apikey]" value="<?php echo ( isset($activeComment_settings['ActiveComment_apikey']) && !empty($activeComment_settings['ActiveComment_apikey']) ) ? $activeComment_settings['ActiveComment_apikey'] : ''; ?>" autofill='off' autocomplete='off' />
                        </label>
                        <label>
                            <span class="active_property_title"><?php _e('API Secret', 'Active Comment'); ?></span>
                            <input type="text" class="active-row-field" name="Active_API_settings[ActiveComment_secret]" value="<?php echo $activeComment_settings['ActiveComment_secret']; ?>" autofill='off' autocomplete='off' />
                        </label>
                    </div>
                </div>
            </div>
            <div id="active_options_tab-2" class="active-tab-frame">
                <div class="active_options_container">
                    <div class="active-row" >
                        <h3><?php _e('Enable SSO', 'Active Comment'); ?></h3>
                        <label class="active-toggle">
                            <input type="checkbox" class="active-toggle" name="Active_API_settings[sso_enable]" value="1" <?php echo ( isset($activeComment_settings['sso_enable']) && $activeComment_settings['sso_enable'] == '1' ) ? 'checked' : ''; ?> />
                            <span class="active-toggle-name">
                                <?php _e('Do you want to enable sso?', 'Active Comment'); ?>
                            </span>
                        </label>
                    </div><!-- active-row -->
                </div>
            </div>
            <p class="submit">
                <?php submit_button('Save Settings', 'primary', 'submit', false); ?>
            </p>
        </form>
    </div>
    
</div>