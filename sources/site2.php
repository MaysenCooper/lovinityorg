<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/**
 * Get list of staff contextual actions.
 *
 * @return string The list
 */
function get_staff_actions_list()
{
    require_lang('lang');
    $list = array(
        'view' => do_lang_tempcode('SCREEN_DEV_TOOLS'),
    );
    $list += array(
        'spacer_1' => do_lang_tempcode('THEME'),
        'show_edit_links' => do_lang_tempcode('TEMPLATES_WITH_EDIT_LINKS'),
        'show_markers' => do_lang_tempcode('TEMPLATES_WITH_HTML_COMMENT_MARKERS'),
        'tree' => do_lang_tempcode('TEMPLATE_TREE'),
        'templates' => do_lang_tempcode('TEMPLATES'),
        'theme_images' => do_lang_tempcode('THEME_IMAGE_EDITING'),
        'code' => do_lang_tempcode('WEBSTANDARDS'),
    );
    if (get_param_integer('keep_no_minify', 0) == 0) { // When minification on we need to hard-code CSS list as cannot be auto-detected
        $is_admin = $GLOBALS['FORUM_DRIVER']->is_super_admin(get_member());
        $zone_name = get_zone_name();
        $grouping_codename = 'merged__';
        $grouping_codename .= $zone_name;
        if ($is_admin) {
            $grouping_codename .= '__admin';
        }
        $value = get_value_newer_than($grouping_codename . '.css', time() - 60 * 60 * 24);
        if ($value !== null) {
            $_value = explode('::', $value);
            $resources = explode(',', $_value[0]);
            foreach ($resources as $resource) {
                $list[$resource . '.css'] = ($resource == 'global') ? do_lang_tempcode('CONTEXTUAL_CSS_EDITING_GLOBAL', 'global.css') : do_lang_tempcode('CONTEXTUAL_CSS_EDITING', escape_html($resource . '.css'));
            }
        }
    }
    require_code('lang2');
    $list += array(
        'spacer_2' => do_lang_tempcode('PAGE'),
        'sitemap' => do_lang_tempcode('FIND_IN_SITEMAP'),
        'spacer_3' => do_lang_tempcode('LANGUAGE'),
    );
    $all_langs = multi_lang() ? find_all_langs() : array(user_lang() => 'lang_custom');
    $tcode = do_lang('lang:TRANSLATE_CODE');
    foreach (array_keys($all_langs) as $lang) {
        $list += array(
            'lang_' . $lang => $tcode . ((count($all_langs) == 1) ? '' : (': ' . lookup_language_full_name($lang))),
        );
    }
    if ((multi_lang()) && (multi_lang_content())) {
        $tcontent = do_lang('TRANSLATE_CONTENT');
        foreach (array_keys($all_langs) as $lang) {
            $list['lang_content_' . $lang] = $tcontent . ': ' . lookup_language_full_name($lang);
        }
    }
    $list += array(
        'spacer_4' => do_lang_tempcode('DEVELOPMENT_VIEWS'),
        'query' => do_lang_tempcode('VIEW_PAGE_QUERIES'),
        'ide_linkage' => do_lang_tempcode('IDE_LINKAGE'),
        'memory' => do_lang_tempcode('_MEMORY_USAGE'),
    );
    $special_page_type = get_param_string('special_page_type', 'view');
    $staff_actions = '';
    $started_opt_group = false;
    foreach ($list as $name => $text) {
        $is_group = (($name[0] == 's') && (substr($name, 0, 7) == 'spacer_'));
        if ($is_group) {
            if ($started_opt_group) {
                $staff_actions .= '</optgroup>';
            }
            $staff_actions .= '<optgroup id="' . escape_html($name) . '" label="' . (isset($text->codename)/*faster than is_object*/ ? $text->evaluate() : escape_html($text)) . '">';
            $started_opt_group = true;
            continue;
        }
        $staff_actions .= '<option' . (($staff_actions == '') ? ' disabled="disabled" class="label"' : '') . ' ' . (($name == $special_page_type) ? 'selected="selected" ' : '') . 'value="' . escape_html($name) . '">' . (isset($text->codename/*faster than is_object*/) ? $text->evaluate() : escape_html($text)) . '</option>'; // XHTMLXHTML
        //$staff_actions .= static_evaluate_tempcode(form_input_list_entry($name, ($name == $special_page_type), $text, false, $disabled)); Disabled 'proper' way for performance reasons
    }
    if ($started_opt_group) {
        $staff_actions .= '</optgroup>';
    }
    return $staff_actions;
}

/**
 * A page is not validated, so show a warning.
 *
 * @param  ID_TEXT $zone The zone the page is being loaded from
 * @param  ID_TEXT $codename The codename of the page
 * @param  Tempcode $edit_url The edit URL (blank if no edit access)
 * @return Tempcode The warning
 */
function get_page_warning_details($zone, $codename, $edit_url)
{
    $warning_details = new Tempcode();
    if ((!has_privilege(get_member(), 'jump_to_unvalidated')) && (addon_installed('unvalidated'))) {
        access_denied('PRIVILEGE', 'jump_to_unvalidated');
    }
    $uv_warning = do_lang_tempcode((get_param_integer('redirected', 0) == 1) ? 'UNVALIDATED_TEXT_NON_DIRECT' : 'UNVALIDATED_TEXT', 'comcode_page'); // Wear sun cream
    if (!$edit_url->is_empty()) {
        $menu_links = $GLOBALS['SITE_DB']->query('SELECT DISTINCT i_menu FROM ' . get_table_prefix() . 'menu_items WHERE ' . db_string_equal_to('i_url', $zone . ':' . $codename) . ' OR ' . db_string_equal_to('i_url', '_SEARCH:' . $codename));
        if (count($menu_links) != 0) {
            $menu_items_linking = new Tempcode();
            foreach ($menu_links as $menu_link) {
                if (!$menu_items_linking->is_empty()) {
                    $menu_items_linking->attach(do_lang_tempcode('LIST_SEP'));
                }
                $menu_edit_url = build_url(array('page' => 'admin_menus', 'type' => 'edit', 'id' => $menu_link['i_menu']), get_module_zone('admin_menus'));
                $menu_items_linking->attach(hyperlink($menu_edit_url, $menu_link['i_menu'], false, true));
            }
            $uv_warning = do_lang_tempcode('UNVALIDATED_TEXT_STAFF', $menu_items_linking, 'comcode_page');
        }
    }
    $warning_details->attach(do_template('WARNING_BOX', array('_GUID' => 'ee79289f87986bcb916a5f1810a25330', 'WARNING' => $uv_warning)));
    return $warning_details;
}

/**
 * Assign a page refresh to the specified URL.
 * This is almost always used before calling the redirect_screen function. It assumes Composr will output a full screen.
 *
 * @sets_output_state
 *
 * @param  mixed $url Refresh to this URL (URLPATH or Tempcode URL)
 * @param  float $multiplier Take this many times longer than a 'standard Composr refresh'
 */
function assign_refresh($url, $multiplier = 0.0)
{
    if ($url == '') {
        fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    // URL clean up
    if (is_object($url)) {
        $url = $url->evaluate();
    }
    if (strpos($url, 'keep_session') !== false) {
        $url = enforce_sessioned_url($url); // In case the session changed in transit (this refresh URL may well have been relayed from a much earlier point)
    }
    if ((strpos($url, "\n") !== false) || (strpos($url, "\r") !== false)) {
        log_hack_attack_and_exit('HEADER_SPLIT_HACK');
    }

    $must_show_message = ($multiplier != 0.0);

    // Fudge so that redirects can't count as flooding
    if (get_forum_type() == 'cns') {
        require_code('cns_groups');
        $restrict_answer = cns_get_best_group_property($GLOBALS['FORUM_DRIVER']->get_members_groups(get_member()), 'flood_control_access_secs');
        if ($restrict_answer != 0) {
            $restrict_setting = 'm_last_visit_time';
            $GLOBALS['FORUM_DB']->query_update('f_members', array('m_last_visit_time' => time() - $restrict_answer - 1), array('id' => get_member()), '', 1);
        }
    }

    // Emergency meta tag
    if (headers_sent()) {
        if ($GLOBALS['RELATIVE_PATH'] != '_tests') {
            safe_ini_set('ocproducts.xss_detect', '0');
            echo '<meta http-equiv="Refresh" content="0; URL=' . escape_html($url) . '" />'; // XHTMLXHTML
        }
        return;
    }

    global $FORCE_META_REFRESH;

    // Redirect via meta tag in standard Composr output
    if ($must_show_message || $FORCE_META_REFRESH) {
        global $REFRESH_URL;
        $REFRESH_URL[0] = $url;
        $REFRESH_URL[1] = 2.5 * $multiplier;
        return;
    }

    // HTTP redirect
    if ((running_script('index')) && (!$FORCE_META_REFRESH)) {
        header('Location: ' . escape_header($url));
        if (strpos($url, '#') === false) {
            $GLOBALS['QUICK_REDIRECT'] = true;
        }
    }
    return;
}

/**
 * Assign a redirect to the specified URL, with no visual component.
 * If possible, use an HTTP header; but if output has already started, use a meta tag.
 *
 * @sets_output_state
 *
 * @param  mixed $url Refresh to this URL (URLPATH or Tempcode URL)
 */
function smart_redirect($url)
{
    assign_refresh($url, 0.0);

    $middle = redirect_screen(get_screen_title('REDIRECTING'), $url);
    $echo = globalise($middle, null, '', true);
    $echo->evaluate_echo();
    exit();
}

/**
 * Render the site as closed.
 */
function closed_site()
{
    if ((get_page_name() != 'login') && (get_page_name() != 'join') && (get_page_name() != 'lost_password')) {
        $closed_message = comcode_to_tempcode(get_option('closed'), null, true);

        require_code('failure');
        if (throwing_errors()) {
            throw new CMSException($closed_message);
        }

        if (!headers_sent()) {
            if ((!browser_matches('ie')) && (strpos(cms_srv('SERVER_SOFTWARE'), 'IIS') === false)) {
                header('HTTP/1.0 503 Service Temporarily Unavailable');
            }
        }

        log_stats('/closed', 0);

        $GLOBALS['SCREEN_TEMPLATE_CALLED'] = '';

        if (has_interesting_post_fields()) {
            $_redirect = build_url(array('page' => ''), '', array('keep_session' => 1));
        } else {
            $_redirect = build_url(array('page' => '_SELF'), '_SELF', array('keep_session' => 1), true);
        }
        $redirect = $_redirect->evaluate();
        $login_url = build_url(array('page' => 'login', 'type' => 'browse', 'redirect' => $redirect), get_module_zone('login'));
        $join_url = (get_forum_type() == 'none') ? '' : $GLOBALS['FORUM_DRIVER']->join_url();
        $middle = do_template('CLOSED_SITE', array('_GUID' => '4e753c50eca7c98344d2107fc18c4554', 'CLOSED' => comcode_to_tempcode(get_option('closed'), null, true), 'LOGIN_URL' => $login_url, 'JOIN_URL' => $join_url));
        $echo = globalise($middle, null, '', true);
        $echo->evaluate_echo();
        exit();
    }
}

/**
 * Render that the page wasn't found. Show alternate likely candidates based on misspellings.
 *
 * @param  ID_TEXT $codename The codename of the page to load
 * @param  ID_TEXT $zone The zone the page is being loaded in
 * @return Tempcode Message
 */
function page_not_found($codename, $zone)
{
    global $PAGE_NAME_CACHE;
    $PAGE_NAME_CACHE = '404';

    set_http_status_code('404');

    // Maybe problem with URL Schemes
    $url_scheme = get_option('url_scheme');
    if ((get_zone_name() == '') && ((($url_scheme == 'HTM') || ($url_scheme == 'SIMPLE'))) && (has_zone_access(get_member(), 'adminzone'))) {
        $self_url = get_self_url_easy();
        $zones = find_all_zones();
        foreach ($zones as $_zone) {
            if (($_zone != '') && ($_zone != 'site') && (strpos($self_url, '/' . $_zone . '/') !== false)) {
                attach_message(do_lang_tempcode('HTACCESS_SEO_PROBLEM'), 'warn');
            }
        }
    }

    // "Did you mean?" support
    $all_pages_in_zone = array_keys(find_all_pages_wrap($zone));
    $did_mean = array();
    foreach ($all_pages_in_zone as $possibility) {
        if (is_integer($possibility)) {
            $possibility = strval($possibility); // e.g. '404' page has been converted to integer by PHP, grr
        }

        $from = str_replace(array('-', 'cms_', 'admin_'), array('_', '', ''), $possibility);
        $to = str_replace(array('-', 'cms_', 'admin_'), array('_', '', ''), $codename);
        //$dist = levenshtein($from, $to);  If we use this, change > to < also
        //$threshold = 4;
        $dist = 0.0;
        similar_text($from, $to, $dist);
        $threshold = 75.0;
        if (($dist > $threshold) && (has_page_access(get_member(), $codename, $zone))) {
            $did_mean[$dist] = $possibility;
        }
    }
    ksort($did_mean);
    $_did_mean = array_pop($did_mean);
    if ($_did_mean == '') {
        $_did_mean = null;
    }

    require_code('global4');
    if ((cms_srv('HTTP_REFERER') != '') && (!handle_has_checked_recently('request-' . $zone . ':' . $codename))) {
        require_code('failure');
        relay_error_notification(do_lang('_MISSING_RESOURCE', $zone . ':' . $codename, do_lang('PAGE')) . ' ' . do_lang('REFERRER', cms_srv('HTTP_REFERER'), substr(get_browser_string(), 0, 255)), false, 'error_occurred_missing_page');
    }

    $title = get_screen_title('ERROR_OCCURRED');
    $add_access = has_add_comcode_page_permission($zone);
    $redirect_access = addon_installed('redirects_editor') && has_actual_page_access(get_member(), 'admin_redirects');
    require_lang('zones');
    $add_url = $add_access ? build_url(array('page' => 'cms_comcode_pages', 'type' => '_edit', 'page_link' => $zone . ':' . $codename), get_module_zone('cms_comcode_pages')) : new Tempcode();
    $add_redirect_url = $redirect_access ? build_url(array('page' => 'admin_redirects', 'type' => 'browse', 'page_link' => $zone . ':' . $codename), get_module_zone('admin_redirects')) : new Tempcode();
    return do_template('MISSING_SCREEN', array('_GUID' => '22f371577cd2ba437e7b0cb241931575', 'TITLE' => $title, 'DID_MEAN' => $_did_mean, 'ADD_URL' => $add_url, 'ADD_REDIRECT_URL' => $add_redirect_url, 'PAGE' => $codename));
}

/**
 * Load Comcode page from disk, then cache it.
 *
 * @param  PATH $string The relative (to Composr's base directory) path to the page (e.g. pages/comcode/EN/start.txt)
 * @param  ID_TEXT $zone The zone the page is being loaded from
 * @param  ID_TEXT $codename The codename of the page
 * @param  PATH $file_base The file base to load from
 * @param  ?array $comcode_page_row Row from database (holds submitter etc) (null: no row, originated first from disk)
 * @param  array $new_comcode_page_row New row for database, used if necessary (holds submitter etc)
 * @param  boolean $being_included Whether the page is being included from another
 * @return array A tuple: The page HTML (as Tempcode), New Comcode page row, Title, Raw Comcode
 *
 * @ignore
 */
function _load_comcode_page_not_cached($string, $zone, $codename, $file_base, $comcode_page_row, $new_comcode_page_row, $being_included = false)
{
    global $COMCODE_PARSE_TITLE;

    $nql_backup = $GLOBALS['NO_QUERY_LIMIT'];
    $GLOBALS['NO_QUERY_LIMIT'] = true;

    // Not cached :(
    $comcode = cms_file_get_contents_safe($file_base . '/' . $string);
    if (strpos($string, '_custom/') === false) {
        global $LANG_FILTER_OB;
        $comcode = $LANG_FILTER_OB->compile_time(null, $comcode);
    }
    apply_comcode_page_substitutions($comcode);
    $comcode = fix_bad_unicode($comcode);

    if (is_null($new_comcode_page_row['p_submitter'])) {
        $as_admin = true;
        require_code('users_active_actions');
        $new_comcode_page_row['p_submitter'] = get_first_admin_user();
    }

    if (is_null($comcode_page_row)) { // Default page. We need to find an admin to assign it to.
        $page_submitter = $new_comcode_page_row['p_submitter'];
    } else {
        $as_admin = false; // Will only have admin privileges if $page_submitter has them
        $page_submitter = $comcode_page_row['p_submitter'];
    }
    if (is_null($page_submitter)) {
        $page_submitter = get_member();
    }

    // Parse and work out how to add
    $lang = user_lang();
    global $LAX_COMCODE;
    $temp = $LAX_COMCODE;
    $LAX_COMCODE = true;
    require_code('attachments2');
    $_new = do_comcode_attachments($comcode, 'comcode_page', $zone . ':' . $codename, false, null, $as_admin/*Ideally we assign $page_submitter based on this as well so it is safe if the Comcode cache is emptied*/, $page_submitter);
    $_text_parsed = $_new['tempcode'];
    $LAX_COMCODE = $temp;

    // Flatten for performance reasons?
    if (strpos($comcode, '{$,Quick Cache}') !== false) {
        $_text_parsed = apply_quick_caching($_text_parsed);
    }

    $text_parsed = $_text_parsed->to_assembly();

    // Check it still needs inserting (it might actually be there, but not translated)
    $trans_key = $GLOBALS['SITE_DB']->query_select_value_if_there('cached_comcode_pages', 'string_index', array('the_page' => $codename, 'the_zone' => $zone, 'the_theme' => $GLOBALS['FORUM_DRIVER']->get_theme()));
    if (is_null($COMCODE_PARSE_TITLE)) {
        $COMCODE_PARSE_TITLE = '';
    }
    $title_to_use = clean_html_title($COMCODE_PARSE_TITLE);
    if (is_null($trans_key)) {
        $map = array(
            'the_zone' => $zone,
            'the_page' => $codename,
            'the_theme' => $GLOBALS['FORUM_DRIVER']->get_theme(),
        );
        $map += insert_lang('cc_page_title', clean_html_title($COMCODE_PARSE_TITLE), 1, null, false, null, null, false, null, null, null, true, true);
        if (multi_lang_content()) {
            $map['string_index'] = $GLOBALS['SITE_DB']->query_insert('translate', array('source_user' => $page_submitter, 'broken' => 0, 'importance_level' => 1, 'text_original' => $comcode, 'text_parsed' => $text_parsed, 'language' => $lang), true, false, true);
        } else {
            $map['string_index'] = $comcode;
            $map['string_index__source_user'] = $page_submitter;
            $map['string_index__text_parsed'] = $text_parsed;
        }
        $GLOBALS['SITE_DB']->query_insert('cached_comcode_pages', $map, false, true); // Race conditions

        decache('main_comcode_page_children');

        // Try and insert corresponding page; will silently fail if already exists. This is only going to add a row for a page that was not created in-system
        if (is_null($comcode_page_row)) {
            $comcode_page_row = $new_comcode_page_row;
            $GLOBALS['SITE_DB']->query_insert('comcode_pages', $comcode_page_row, false, true);

            if (addon_installed('content_reviews')) {
                require_code('content_reviews2');
                schedule_content_review('comcode_page', $zone . ':' . $codename, intval(get_option('comcode_page_default_review_freq')));
            }
        }
    } else {
        $_comcode_page_row = $GLOBALS['SITE_DB']->query_select('comcode_pages', array('*'), array('the_zone' => $zone, 'the_page' => $codename), '', 1);
        if (array_key_exists(0, $_comcode_page_row)) {
            $comcode_page_row = $_comcode_page_row[0];
        } else {
            $comcode_page_row = $new_comcode_page_row;
            $GLOBALS['SITE_DB']->query_insert('comcode_pages', $comcode_page_row, false, true);

            if (addon_installed('content_reviews')) {
                require_code('content_reviews2');
                schedule_content_review('comcode_page', $zone . ':' . $codename, intval(get_option('comcode_page_default_review_freq')));
            }
        }

        // Check to see if it needs translating
        if (multi_lang_content()) {
            $test = $GLOBALS['SITE_DB']->query_select_value_if_there('translate', 'id', array('id' => $trans_key, 'language' => $lang));
            if (is_null($test)) {
                $GLOBALS['SITE_DB']->query_insert('translate', array('id' => $trans_key, 'source_user' => $page_submitter, 'broken' => 0, 'importance_level' => 1, 'text_original' => $comcode, 'text_parsed' => $text_parsed, 'language' => $lang), false, true);
                $index = $trans_key;

                $trans_cc_page_title_key = $GLOBALS['SITE_DB']->query_select_value_if_there('cached_comcode_pages', 'cc_page_title', array('the_page' => $codename, 'the_zone' => $zone, 'the_theme' => $GLOBALS['FORUM_DRIVER']->get_theme()));
                if (!is_null($trans_cc_page_title_key)) {
                    $test = $GLOBALS['SITE_DB']->query_select_value_if_there('translate', 'id', array('id' => $trans_cc_page_title_key, 'language' => $lang));
                    if (is_null($test)) {
                        $GLOBALS['SITE_DB']->query_insert('translate', array('id' => $trans_cc_page_title_key, 'source_user' => $page_submitter, 'broken' => 0, 'importance_level' => 1, 'text_original' => $title_to_use, 'text_parsed' => '', 'language' => $lang), true);
                    }
                } // else race condition, decached while being recached
            }
        } else {
            $map = array();
            $map += insert_lang('cc_page_title', clean_html_title($COMCODE_PARSE_TITLE), 1, null, false, null, null, false, null, null, null, true, true);
            $map['string_index'] = $comcode;
            $map['string_index__source_user'] = $page_submitter;
            $map['string_index__text_parsed'] = $text_parsed;
            $GLOBALS['SITE_DB']->query_update('cached_comcode_pages', $map, array('the_page' => $codename, 'the_zone' => $zone, 'the_theme' => $GLOBALS['FORUM_DRIVER']->get_theme()), '', 1);
        }
    }

    $GLOBALS['NO_QUERY_LIMIT'] = $nql_backup;

    return array($_text_parsed, $title_to_use, $comcode_page_row, $comcode);
}

/**
 * If any Comcode substitutions are configured, apply them.
 *
 * @param  string $comcode The Comcode page contents
 */
function apply_comcode_page_substitutions(&$comcode)
{
    global $SITE_INFO;
    if (isset($SITE_INFO['reps'])) {
        foreach ($SITE_INFO['reps'] as $search => $replace) {
            $comcode = str_replace($search, $replace, $comcode);
        }
    }
}

/**
 * Load Comcode page from disk.
 *
 * @param  PATH $string The relative (to Composr's base directory) path to the page (e.g. pages/comcode/EN/start.txt)
 * @param  ID_TEXT $zone The zone the page is being loaded from
 * @param  ID_TEXT $codename The codename of the page
 * @param  PATH $file_base The file base to load from
 * @param  array $new_comcode_page_row New row for database, used if nesessary (holds submitter etc)
 * @param  boolean $being_included Whether the page is being included from another
 * @return array A tuple: The page HTML (as Tempcode), New Comcode page row, Title, Raw Comcode
 *
 * @ignore
 */
function _load_comcode_page_cache_off($string, $zone, $codename, $file_base, $new_comcode_page_row, $being_included = false)
{
    global $COMCODE_PARSE_TITLE;

    if (is_null($new_comcode_page_row['p_submitter'])) {
        $as_admin = true;
        $members = $GLOBALS['FORUM_DRIVER']->member_group_query($GLOBALS['FORUM_DRIVER']->get_super_admin_groups(), 1);
        if (count($members) != 0) {
            $new_comcode_page_row['p_submitter'] = $GLOBALS['FORUM_DRIVER']->mrow_id($members[key($members)]);
        } else {
            $new_comcode_page_row['p_submitter'] = db_get_first_id() + 1; // On Conversr and most forums, this is the first admin member
        }
    }

    $_comcode_page_row = $GLOBALS['SITE_DB']->query_select('comcode_pages', array('*'), array('the_zone' => $zone, 'the_page' => $codename), '', 1);

    $comcode = cms_file_get_contents_safe($file_base . '/' . $string);
    if (strpos($string, '_custom/') === false) {
        global $LANG_FILTER_OB;
        $comcode = $LANG_FILTER_OB->compile_time(null, $comcode);
    }
    apply_comcode_page_substitutions($comcode);

    global $LAX_COMCODE;
    $temp = $LAX_COMCODE;
    $LAX_COMCODE = true;
    require_code('attachments2');
    $_new = do_comcode_attachments($comcode, 'comcode_page', $zone . ':' . $codename, false, null, (!array_key_exists(0, $_comcode_page_row)) || (is_guest($_comcode_page_row[0]['p_submitter'])), array_key_exists(0, $_comcode_page_row) ? $_comcode_page_row[0]['p_submitter'] : get_member());
    $html = $_new['tempcode'];
    $LAX_COMCODE = $temp;
    $title_to_use = is_null($COMCODE_PARSE_TITLE) ? null : clean_html_title($COMCODE_PARSE_TITLE);

    // Try and insert corresponding page; will silently fail if already exists. This is only going to add a row for a page that was not created in-system
    if (array_key_exists(0, $_comcode_page_row)) {
        $comcode_page_row = $_comcode_page_row[0];
    } else {
        $comcode_page_row = $new_comcode_page_row;
        $GLOBALS['SITE_DB']->query_insert('comcode_pages', $comcode_page_row, false, true);

        if (addon_installed('content_reviews')) {
            require_code('content_reviews2');
            schedule_content_review('comcode_page', $zone . ':' . $codename, intval(get_option('comcode_page_default_review_freq')));
        }
    }

    return array($html, $comcode_page_row, $title_to_use, $comcode);
}

/**
 * Turn an HTML title, which could be complex with images, into a nice simple string we can use in <title> and ;.
 *
 * @param  string $title The relative (to Composr's base directory) path to the page (e.g. pages/comcode/EN/start.txt)
 * @return string Fixed
 */
function clean_html_title($title)
{
    $_title = trim(strip_html($title));
    if ($_title == '') { // Complex case
        $matches = array();
        if (preg_match('#<img[^>]*alt="([^"]+)"#', $title, $matches) != 0) {
            return $matches[1];
        }
        return $title;
    }
    return $_title;
}
