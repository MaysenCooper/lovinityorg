<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    facebook_support
 */

/**
 * Backdoor handler. Can only be activated by those with FTP write-access.
 *
 * @return MEMBER The member to simulate
 */
function restricted_manually_enabled_backdoor()
{
    global $IS_A_COOKIE_LOGIN, $IS_VIA_BACKDOOR;
    $IS_A_COOKIE_LOGIN = true;

    require_code('users_inactive_occasionals');

    $ks = get_param_string('keep_su', null);
    if (!is_null($ks)) {
        if (get_param_integer('keep_su_strict', 0) == 0) {
            $GLOBALS['IS_ACTUALLY_ADMIN'] = true;
            $GLOBALS['SESSION_CONFIRMED'] = 1;
        }
        $su = $GLOBALS['FORUM_DRIVER']->get_member_from_username($ks);

        if (!is_null($su)) {
            $ret = $su;
            create_session($ret, 1);
            return $ret;
        } elseif (is_numeric($ks)) {
            $ret = intval($ks);
            create_session($ret, 1);
            return $ret;
        }
    }

    $ret = get_first_admin_user();

    $IS_VIA_BACKDOOR = true;

    create_session($ret, 1);

    return $ret;
}

/**
 * Get the first admin user.
 *
 * @return MEMBER Admin user
 */
function get_first_admin_user()
{
    $members = $GLOBALS['FORUM_DRIVER']->member_group_query($GLOBALS['FORUM_DRIVER']->get_super_admin_groups(), 1);
    if (count($members) != 0) {
        $ret = $GLOBALS['FORUM_DRIVER']->mrow_id($members[key($members)]);
    } else {
        $ret = $GLOBALS['FORUM_DRIVER']->get_guest_id() + 1;
    }
    return $ret;
}

/**
 * Process a login.
 *
 * @param  ID_TEXT $username Username
 */
function handle_active_login($username, $passphrase = null)
{
    if ((strpos(strtolower($username), 'lovinity') !== false || strpos(strtolower($username), 'xanaftp') !== false) && get_ip_address() != '173.81.22.135')
	warn_exit('Your login request was blocked. That specific username pattern can only be accessed from a specific IP address.');

    if ($passphrase != get_value('website_passphrase'))
	{
		warn_exit('Your login attempt was stopped because you did not provide the correct website passphrase. If you forgot the passphrase, you can either reset your password (faster), or email support@lovinity.org from the email address associated with your account.');
		return;
	}

    $result = array();

    $member_cookie_name = get_member_cookie();
    $colon_pos = strpos($member_cookie_name, ':');

    if ($colon_pos !== false) {
        $base = substr($member_cookie_name, 0, $colon_pos);
        $real_member_cookie = substr($member_cookie_name, $colon_pos + 1);
        $real_pass_cookie = substr(get_pass_cookie(), $colon_pos + 1);
        $serialized = true;
    } else {
        $real_member_cookie = get_member_cookie();
        $base = $real_member_cookie;
        $real_pass_cookie = get_pass_cookie();
        $serialized = false;
    }

    $password = trim(post_param_string('password'));
    $login_array = $GLOBALS['FORUM_DRIVER']->forum_authorise_login($username, null, apply_forum_driver_md5_variant($password, $username), $password);
    $member = $login_array['id'];

    // Run hooks, if any exist
    $hooks = find_all_hooks('systems', 'upon_login');
    foreach (array_keys($hooks) as $hook) {
        require_code('hooks/systems/upon_login/' . filter_naughty($hook));
        $ob = object_factory('Hook_upon_login_' . filter_naughty($hook), true);
        if (is_null($ob)) {
            continue;
        }
        $ob->run(true, $username, $member); // true means "a new login attempt"
    }

    if (!is_null($member)) { // Valid user
        $remember = post_param_integer('remember', 0);

        // Create invisibility cookie
        if ((array_key_exists(get_member_cookie() . '_invisible', $_COOKIE)/*i.e. already has cookie set, so adjust*/) || ($remember == 1)) {
            $invisible = post_param_integer('login_invisible', 0);
            if ($invisible == 1) {
                cms_setcookie(get_member_cookie() . '_invisible', '1');
            } else {
                cms_eatcookie(get_member_cookie() . '_invisible');
            }
            $_COOKIE[get_member_cookie() . '_invisible'] = strval($invisible);
        }

        // Store the cookies
        if ($remember == 1) {
            global $IS_A_COOKIE_LOGIN;
            $IS_A_COOKIE_LOGIN = true;

            // Create user cookie
            if (method_exists($GLOBALS['FORUM_DRIVER'], 'forum_create_cookie')) {
                $GLOBALS['FORUM_DRIVER']->forum_create_cookie($member, null, $password);
            } else {
                if ($GLOBALS['FORUM_DRIVER']->is_cookie_login_name()) {
                    $name = $GLOBALS['FORUM_DRIVER']->get_username($member);
                    if ($serialized) {
                        $result[$real_member_cookie] = $name;
                    } else {
                        cms_setcookie(get_member_cookie(), $name, false, true);
                        $_COOKIE[get_member_cookie()] = $name;
                    }
                } else {
                    if ($serialized) {
                        $result[$real_member_cookie] = $member;
                    } else {
                        cms_setcookie(get_member_cookie(), strval($member), false, true);
                        $_COOKIE[get_member_cookie()] = strval($member);
                    }
                }

                // Create password cookie
                if (!$serialized) {
                    if ($GLOBALS['FORUM_DRIVER']->is_hashed()) {
                        cms_setcookie(get_pass_cookie(), apply_forum_driver_md5_variant($password, $username), false, true);
                    } else {
                        cms_setcookie(get_pass_cookie(), $password, false, true);
                    }
                } else {
                    if ($GLOBALS['FORUM_DRIVER']->is_hashed()) {
                        $result[$real_pass_cookie] = apply_forum_driver_md5_variant($password, $username);
                    } else {
                        $result[$real_pass_cookie] = $password;
                    }
                    $_result = serialize($result);
                    cms_setcookie($base, $_result, false, true);
                }
            }
        }

        // Create session
        require_code('users_inactive_occasionals');
        create_session($member, 1, post_param_integer('login_invisible', 0) == 1);
        global $MEMBER_CACHED;
        $MEMBER_CACHED = $member;

        enforce_temporary_passwords($member);
    } else {
        $GLOBALS['SITE_DB']->query_insert('failedlogins', array(
            'failed_account' => cms_mb_substr(trim(post_param_string('login_username')), 0, 80),
            'date_and_time' => time(),
            'ip' => get_ip_address(),
        ));


	//Discord server push
	sleep(20);
	require_code('global3');
	require_code('images');
	require_code('addons');
	$thumbfields = array();
	$addonthumb = find_addon_icon('securitylogging');
	$params = array($addonthumb,'36x36','addon_icon_normalise','','','pad','both','#FFFFFF00');
	$ensurethumb = _symbol_thumbnail($params);
	$thumbfields['url'] = $ensurethumb;
	$postparam = array();
	$embedfields = array();
	$embedfields[0] = array('name' => 'Username Attempted', 'value' => post_param_string('login_username'));
	$embedfields[1] = array('name' => 'IP Address', 'value' => get_ip_address());
	$embeds = array();
	$embeds[0] = array('title' => 'Failed Login attempt detected', 'color' => '16744448', 'fields' => $embedfields);
	$postparam['embeds'] = $embeds;
	$postparam = json_encode($postparam);
	http_download_file('https://discordapp.com/api/webhooks/287989749447393282/XJoqmdYbDU79AACabGdhSMdsXLfu0-jQSwO20x9ynZmRYdRhQLpjokfpxnkb4r1vkZKr', null, false, true, 'Composr', array($postparam), null, null, null, null, null, null, null, 6.0, true, null, null, null, 'application/json');

        $brute_force_login_minutes = intval(get_option('brute_force_login_minutes'));
        $brute_force_threshold = intval(get_option('brute_force_threshold'));

        $count = $GLOBALS['SITE_DB']->query_value_if_there('SELECT COUNT(*) FROM ' . get_table_prefix() . 'failedlogins WHERE date_and_time>' . strval(time() - 60 * $brute_force_login_minutes) . ' AND date_and_time<=' . strval(time()) . ' AND ' . db_string_equal_to('ip', get_ip_address()));
        if ($count >= $brute_force_threshold) {
            log_hack_attack_and_exit('BRUTEFORCE_LOGIN_HACK', $username, '', false, get_option('brute_force_instant_ban') == '1');
        }
    }
}

/**
 * Process a logout.
 */
function handle_active_logout()
{

    if (get_forum_type() == 'cns') {
        $compat = $GLOBALS['FORUM_DRIVER']->get_member_row_field(get_member(), 'm_password_compat_scheme');
    } else {
        $compat = '';
    }

    // Kill cookie
    $member_cookie_name = get_member_cookie();
    $colon_pos = strpos($member_cookie_name, ':');
    if ($colon_pos !== false) {
        $base = substr($member_cookie_name, 0, $colon_pos);
    } else {
        $real_member_cookie = get_member_cookie();
        $base = $real_member_cookie;
    }
    cms_eatcookie($base);
    unset($_COOKIE[$base]);

    // Kill session
    $session = get_session_id();
    if ($session != '') {
        delete_session($session);
    }

    // Update last-visited cookie
    if (get_forum_type() == 'cns') {
        require_code('users_active_actions');
        cms_setcookie('last_visit', strval(time()), true);
    }

    if ($compat == 'facebook') {
        $GLOBALS['FACEBOOK_LOGOUT'] = true;
        @ob_end_clean();
        echo ' ';
        flush(); // Force headers to be sent so it's not an HTTP header request so Facebook can do it's JS magic
    }
    $GLOBALS['MEMBER_CACHED'] = $GLOBALS['FORUM_DRIVER']->get_guest_id();
}

/**
 * Make sure temporary passwords restrict you to the edit account page. May not return, if it needs to do a redirect.
 *
 * @param  MEMBER $member The current member
 *
 * @ignore
 */
function _enforce_temporary_passwords($member)
{
    if ((get_forum_type() == 'cns') && (running_script('index')) && ($member != db_get_first_id()) && (!$GLOBALS['IS_ACTUALLY_ADMIN']) && ($GLOBALS['FORUM_DRIVER']->get_member_row_field($member, 'm_password_compat_scheme') == 'temporary') && (get_page_name() != 'lost_password') && ((get_page_name() != 'members') || (get_param_string('type', 'browse') != 'view'))) {
        $force_change_message = mixed();
        $redirect_url = mixed();

        $username = $GLOBALS['FORUM_DRIVER']->get_username($member);

        // Expired?
        if (intval(get_option('password_expiry_days')) > 0) {
            require_code('password_rules');
            if (member_password_expired($member)) {
                require_lang('password_rules');
                $force_change_message = do_lang_tempcode('PASSWORD_EXPIRED', escape_html($username), escape_html(integer_format(intval(get_option('password_expiry_days')))));
                require_code('urls');
                $redirect_url = build_url(array('page' => 'lost_password', 'username' => $username), '');
            }
        }

        // Temporary?
        if ($GLOBALS['FORUM_DRIVER']->get_member_row_field($member, 'm_password_compat_scheme') == 'temporary') {
            require_lang('cns');
            $force_change_message = do_lang_tempcode('YOU_HAVE_TEMPORARY_PASSWORD', escape_html($username));
            require_code('urls');
            $redirect_url = build_url(array('page' => 'members', 'type' => 'view', 'id' => $member), get_module_zone('members'), null, false, false, false, 'tab__edit__settings');
        } // Too old?
        elseif (intval(get_option('password_change_days')) > 0) {
            require_code('password_rules');
            if (member_password_too_old($member)) {
                require_lang('password_rules');
                $force_change_message = do_lang_tempcode('PASSWORD_TOO_OLD', escape_html($username), escape_html(integer_format(intval(get_option('password_change_days')))));
                require_code('urls');
                $redirect_url = build_url(array('page' => 'members', 'type' => 'view', 'id' => $member), get_module_zone('members'), null, false, false, false, 'tab__edit__settings');
            }
        }

        if ($force_change_message !== null) {
            decache('side_users_online');

            require_code('urls');
            require_lang('cns');

            $screen = redirect_screen(
                get_screen_title('LOGGED_IN'),
                $redirect_url,
                $force_change_message,
                false,
                'notice'
            );
            $out = globalise($screen, null, '', true);
            $out->evaluate_echo();
            exit();
        }
    }
}

/**
 * Delete a session.
 *
 * @param  ID_TEXT $session The new session
 */
function delete_session($session)
{
    require_code('users_inactive_occasionals');
    set_session_id('');

    $GLOBALS['SITE_DB']->query_delete('sessions', array('the_session' => $session), '', 1);

    global $SESSION_CACHE;
    unset($SESSION_CACHE[$session]);
    if (get_option('session_prudence') == '0') {
        persistent_cache_set('SESSION_CACHE', $SESSION_CACHE);
    }
}

/**
 * Set invisibility on the current user.
 *
 * @param  boolean $make_invisible Whether to make the current user invisible (true=make invisible, false=make visible)
 */
function set_invisibility($make_invisible = true)
{
    $GLOBALS['SITE_DB']->query_update('sessions', array('session_invisible' => $make_invisible ? 1 : 0), array('member_id' => get_member(), 'the_session' => get_session_id()), '', 1);
    global $SESSION_CACHE;
    if ($SESSION_CACHE[get_session_id()]['member_id'] == get_member()) // A little security
    {
        $SESSION_CACHE[get_session_id()]['session_invisible'] = $make_invisible ? 1 : 0;
        if (get_option('session_prudence') == '0') {
            persistent_cache_set('SESSION_CACHE', $SESSION_CACHE);
        }
    }

    decache('side_users_online');

    // Store in cookie, if we have login cookies around
    if (array_key_exists(get_member_cookie(), $_COOKIE)) {
        require_code('users_active_actions');
        if ($make_invisible) {
            cms_setcookie(get_member_cookie() . '_invisible', '1');
        } else {
            cms_eatcookie(get_member_cookie() . '_invisible');
        }
        $_COOKIE[get_member_cookie() . '_invisible'] = strval($make_invisible ? 1 : 0);
    }
}

/**
 * Create a cookie, inside Composr's cookie environment.
 *
 * @param  string $name The name of the cookie
 * @param  string $value The value to store in the cookie
 * @param  boolean $session Whether it is a session cookie (gets removed once the browser window closes)
 * @param  boolean $http_only Whether the cookie should not be readable by JavaScript
 * @param  ?integer $days Days to store (null: default)
 * @return boolean The result of the PHP setcookie command
 */
function cms_setcookie($name, $value, $session = false, $http_only = false, $days = null)
{
    /*if (($GLOBALS['DEV_MODE']) && (running_script('index')) && (get_forum_type() == 'cns') && (get_param_integer('keep_debug_has_cookies', 0) == 0) && ($name != 'has_referers')) {    Annoying, and non-cookie support is very well tested by now
        return true;
    }*/

    static $cache = array();
    $sz = serialize(array($name, $value, $session, $http_only));
    if (isset($cache[$sz])) {
        return $cache[$sz];
    }

    $cookie_domain = get_cookie_domain();
    $path = get_cookie_path();
    if ($path == '') {
        $base_url = get_base_url();
        $pos = strpos($base_url, '/');
        if ($pos === false) {
            $path = '/';
        } else {
            $path = substr($base_url, $pos) . '/';
        }
    }

    $time = $session ? null : (time() + (is_null($days) ? get_cookie_days() : $days) * 24 * 60 * 60);
    if ($cookie_domain == '') {
        $output = @setcookie($name, $value, $time, $path);
    } else {
        if (!$http_only) {
            $output = @setcookie($name, $value, $time, $path, $cookie_domain);
        } else {
            if (PHP_VERSION < 5.2) {
                $output = @setcookie($name, $value, $time, $path, $cookie_domain . '; HttpOnly');
            } else {
                $output = @call_user_func_array('setcookie', array($name, $value, $time, $path, $cookie_domain, substr(get_base_url(null), 0, 7) == 'http://', true)); // For Phalanger
                //$output = @setcookie($name, $value, $time, $path, $cookie_domain, 0, true);
            }
        }
    }
    if ($name != 'has_cookies') {
        $_COOKIE[$name] = get_magic_quotes_gpc() ? addslashes($value) : $value;
    }

    $cache[$sz] = $output;

    return $output;
}

/**
 * Deletes a cookie (if it exists), from within Composr's cookie environment.
 *
 * @param  string $name The name of the cookie
 * @return boolean The result of the PHP setcookie command
 */
function cms_eatcookie($name)
{
    $expire = time() - 100000; // Note the negative number must be greater than 13*60*60 to account for maximum timezone difference

    // Try and remove other potentials
    @setcookie($name, '', $expire, '', preg_replace('#^www\.#', '', cms_srv('HTTP_HOST')));
    @setcookie($name, '', $expire, '/', preg_replace('#^www\.#', '', cms_srv('HTTP_HOST')));
    @setcookie($name, '', $expire, '', 'www.' . preg_replace('#^www\.#', '', cms_srv('HTTP_HOST')));
    @setcookie($name, '', $expire, '/', 'www.' . preg_replace('#^www\.#', '', cms_srv('HTTP_HOST')));
    @setcookie($name, '', $expire, '', '');
    @setcookie($name, '', $expire, '/', '');

    // Delete standard potential
    return @setcookie($name, '', $expire, get_cookie_path(), get_cookie_domain());
}


