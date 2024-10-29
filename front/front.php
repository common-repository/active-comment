<?php
if (!defined('ABSPATH')) {
    exit();
}
add_action('wp_footer', 'activeCommentFrontScript', 100);
add_action('wp_head', 'activeCommentCoreScript');
add_action('the_content', 'activeCommentCommentingInterface');
add_action('wp_ajax_nopriv_activeCommentDecryptedUserProfile', 'activeCommentDecryptedUserProfile');
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

/* Calling Of Commenting Div */

function activeCommentCommentingInterface($content) {
    if (is_single()) {
        return $content . '<div id="commenting_container"></div>';
    }
    return $content;
}

function activeCommentCoreScript() {
    if (is_single()) {
        wp_enqueue_script('activeComment', '//api.activecomment.com/assets/js/comment.js', array(), ACTIVE_COMMENTING_PLUGIN_VERSION);
    }
}

/* User Profile Encryption Algo */

function activeCommentProfileDataEncrypt($secret, $user_profile) {
    $initvector = "tu89geji340t89u2";
    $keysize = 256;
    $plain_text = mb_convert_encoding($user_profile, 'UTF-8');
    $pass_phrase = mb_convert_encoding($secret, 'UTF-8');
    $salt = str_pad("", 8, "\0");
    $key = hash_pbkdf2('sha1', $pass_phrase, $salt, 10000, $keysize / 8, true);
    $init_vector = mb_convert_encoding($initvector, 'UTF-8');
    $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $padding = $block - (strlen($plain_text) % $block);
    $plain_text .= str_repeat(chr($padding), $padding);
    $temp_cipher = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plain_text, MCRYPT_MODE_CBC, $init_vector);
    $token = base64_encode($temp_cipher);
    $ctx = hash_init('md5');
    hash_update($ctx, $token);
    return $token . '*' . hash_final($ctx);
}

/* User Profile Encryption Method */

function activeCommentEncryptedUserProfile() {
    global $activeComment_settings;
    $current_user = wp_get_current_user();
    $profile = array(
        "name" => $current_user->user_login,
        "email" => $current_user->user_email,
        "avatar" => get_avatar_url($current_user->ID)
    );
    return activeCommentProfileDataEncrypt($activeComment_settings['ActiveComment_secret'], json_encode($profile));
}

/* Commenting Script */

function activeCommentFrontScript() {
    if (is_single()) {
        global $activeComment_settings;
        ?>
        <script>
            var count = 0;
            jQuery(document).ready(function () {
                jQuery('#wp-admin-bar-logout').find('a').removeAttr("href");
                jQuery('#wp-admin-bar-logout').find('a').attr("onclick", "active_comment_logout()");
                active_comment('commenting_container',
                {  apikey: "<?php echo $activeComment_settings['ActiveComment_apikey'] ?>",
        <?php if (isset($activeComment_settings['sso_enable']) && $activeComment_settings['sso_enable'] == '1') { ?>
                    sso: {
                    loginCallback: function (response) {
                    if (response) {
                    count++;
            <?php if (!is_user_logged_in()) { ?>
                        setTimeout(function () {
                        jQuery.ajax({
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                                data: {
                                'action': 'activeCommentDecryptedUserProfile',
                                        'data': response
                                },
                                type: 'POST',
                                success: function (data) {
                                // This outputs the result of the ajax request
                                window.location.href = window.location.href;
                                }
                        });
                        }, (count * 1000));
            <?php } ?>
                    }
                    },
                            logoutCallback: function (response) {
                            if (response) {
                            window.location.href = "<?php echo html_entity_decode(wp_logout_url(get_permalink())) ?>";
                            }
                            },
                    },<?php if (is_user_logged_in()) { ?>
                        profile: "<?php echo activeCommentEncryptedUserProfile() ?>"
                <?php
            }
        }
        ?>
                }
                );
            });
        </script>
        <?php
    }
}

function activeCommentSetCookies($userId = 0, $remember = true) {
    wp_clear_auth_cookie();
    wp_set_auth_cookie($userId, $remember);
    wp_set_current_user($userId);
    return true;
}

function activeCommentDecryptedUserProfile() {
    global $activeComment_settings;
    $data = isset($_POST['data']) ? trim($_POST['data']) : '';
    if (!empty($data)) {
        $userProfile = activeCommentDecryptProfile($activeComment_settings['ActiveComment_secret'], $data);
        $profileDecode = json_decode(preg_replace('/[\x00-\x1F\x7F]/', '', $userProfile));
        if (isset($profileDecode->email) && !empty($profileDecode->email)) {
            $userRole = get_option('default_role');
            $exists = email_exists($profileDecode->email);
            if ($exists) {
                activeCommentSetCookies($exists);
                $user = get_user_by('id', $exists);
                do_action('wp_login', $user->user_login, $user);
            } else {
                /* Register New User */
                if(!isset($profileDecode->name) || empty($profileDecode->name)){
                    $profileDecode->name = preg_replace('/([^@]*).*/', '$1', $profileDecode->email);
                }
                $password = wp_generate_password(12, true);
                $user_id = wp_create_user($profileDecode->name, $password, $profileDecode->email);
                // Set the role
                $user = new WP_User($user_id);
                $user->set_role($userRole);
                $creds = array();
                $creds['user_login'] = $profileDecode->name;
                $creds['user_password'] = $password;
                $user = wp_signon($creds, false);
                $user = get_user_by('email', $profileDecode->email);
                do_action('wp_login', $user->user_login, $user);
            }
        }
    }
    wp_die();
}

function activeCommentDecryptProfile($secret, $profile) {
    $initvector = "tu89geji340t89u2";
    $keysize = 256;
    $tempProfile = explode('*', $profile);
    $ciphered_token = isset($tempProfile[0]) ? $tempProfile[0] : '';
    $cipher_hash = isset($tempProfile[1]) ? $tempProfile[1] : '';
    if (!empty($ciphered_token) && !empty($cipher_hash)) {
        $ctx = hash_init('md5');
        hash_update($ctx, $ciphered_token);
        $created_hash = hash_final($ctx);
        if ($cipher_hash == $created_hash) {
            $ciphered_token = base64_decode(str_replace("%2B", "+", $ciphered_token));
            $pass_phrase = mb_convert_encoding($secret, 'UTF-8');
            $salt = str_pad("", 8, "\0");
            $key = hash_pbkdf2('sha1', $pass_phrase, $salt, 10000, $keysize / 8, true);
            $init_vector = mb_convert_encoding($initvector, 'UTF-8');
            return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphered_token, MCRYPT_MODE_CBC, $init_vector);
        }
    }
    return false;
}
