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
 * @package    core_menus
 */

/**
 * Module page class.
 */
class Module_admin_menus
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = true;
        return $info;
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions.
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user).
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        return array(
            'browse' => array('MENU_MANAGEMENT', 'menu/adminzone/structure/menus'),
        );
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        require_code('input_filter_2');
        modsecurity_workaround_enable();

        $type = get_param_string('type', 'browse');

        require_lang('menus');

        if ($type == 'browse') {
            set_helper_panel_tutorial('tut_menus');

            $this->title = get_screen_title('MENU_MANAGEMENT');
        }

        if ($type == 'edit') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:browse', do_lang_tempcode('MENU_MANAGEMENT'))));

            $id = get_param_string('id', get_param_string('id_new', ''));
            if ($id == '') {
                $this->title = get_screen_title('EDIT_MENU');
            } else {
                $this->title = get_screen_title('_EDIT_MENU', true, array(escape_html($id)));
            }
        }

        if ($type == '_edit') {
            $this->title = get_screen_title('_EDIT_MENU', true, array(escape_html(get_param_string('id'))));
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution.
     */
    public function run()
    {
        require_code('input_filter_2');
        rescue_shortened_post_request();

        require_javascript('menu_editor');
        require_javascript('ajax');

        require_code('menus');
        require_code('menus2');

        require_css('menu_editor');

        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->choose_menu_name();
        }
        if ($type == 'edit') {
            return $this->edit_menu();
        }
        if ($type == '_edit') {
            return $this->_edit_menu();
        }

        return new Tempcode();
    }

    /**
     * The UI to choose a menu to edit / create a new menu.
     *
     * @return Tempcode The UI
     */
    public function choose_menu_name()
    {
        require_code('form_templates');

        $rows = $GLOBALS['SITE_DB']->query_select('menu_items', array('DISTINCT i_menu'), null, 'ORDER BY i_menu');
        $rows = list_to_map('i_menu', $rows);
        $list = new Tempcode();
        foreach ($rows as $row) {
            $item_count = $GLOBALS['SITE_DB']->query_select_value('menu_items', 'COUNT(*)', array('i_menu' => $row['i_menu']));
            $label = do_lang_tempcode('MENU_ITEM_COUNT', escape_html($row['i_menu']), escape_html(integer_format($item_count)));
            $list->attach(form_input_list_entry($row['i_menu'], false, $label));
        }
        if (!isset($rows['main_menu'])) {
            $list->attach(form_input_list_entry('', false, do_lang_tempcode('DEFAULT')));
        }

        $fields = new Tempcode();

        $set_name = 'menu';
        $required = true;
        $set_title = do_lang_tempcode('MENU');
        $field_set = alternate_fields_set__start($set_name);

        if (!$list->is_empty()) {
            $field_set->attach(form_input_list(do_lang_tempcode('EXISTING'), do_lang_tempcode('EXISTING_MENU'), 'id', $list, null, true, false));
        }

        $field_set->attach(form_input_codename(do_lang_tempcode('NEW'), do_lang_tempcode('NEW_MENU'), 'id_new', '', false));

        $fields->attach(alternate_fields_set__end($set_name, $set_title, '', $field_set, $required));

        // Actualiser URL
        $map = array('page' => '_SELF', 'type' => 'edit');
        if (get_param_string('redirect', '!') != '!') {
            $map['redirect'] = get_param_string('redirect');
        }
        $post_url = build_url($map, '_SELF', null, false, true);

        $submit_name = do_lang_tempcode('EDIT');

        return do_template('FORM_SCREEN', array(
            '_GUID' => 'f3c04ea3fb5e429210c5e33e5a2f2092',
            'GET' => true,
            'SKIP_WEBSTANDARDS' => true,
            'TITLE' => $this->title,
            'HIDDEN' => '',
            'TEXT' => do_lang_tempcode('CHOOSE_EDIT_LIST'),
            'FIELDS' => $fields,
            'URL' => $post_url,
            'SUBMIT_ICON' => 'buttons__proceed',
            'SUBMIT_NAME' => $submit_name,
        ));
    }

    /**
     * The UI to edit a menu.
     *
     * @return Tempcode The UI
     */
    public function edit_menu()
    {
        if (!has_js()) {
            warn_exit(do_lang_tempcode('MSG_JS_NEEDED'));
        }

        $id = get_param_string('id', '');
        if ($id == '') {
            $id = get_param_string('id_new', '');
        }
        if (substr($id, 0, 1) == '_') {
            warn_exit(do_lang_tempcode('MENU_UNDERSCORE_RESERVED'));
        }

        // Option to copy to an editable menu
        if ($id == '') {
            $preview = do_lang_tempcode('COPY_TO_EDITABLE_MENU');
            $confirm_url = build_url(array('page' => '_SELF', 'type' => 'edit', 'id' => 'main_menu', 'redirect' => get_param_string('redirect', null)), '_SELF');
            require_code('templates_confirm_screen');
            return confirm_screen($this->title, $preview, $confirm_url, null, array('copy_from' => get_option('header_menu_call_string'), 'switch_over' => 1));
        }

        require_code('type_sanitisation');
        if (!is_alphanumeric($id)) {
            warn_exit(do_lang_tempcode('BAD_CODENAME'));
        }

        $copy_from = post_param_string('copy_from', null);
        if ($copy_from !== null) {
            require_code('menus2');
            delete_menu($id);
            copy_from_sitemap_to_new_menu($id, $copy_from);

            if (post_param_integer('switch_over', 0) == 1) {
                require_code('config2');
                set_option('header_menu_call_string', $id);

                // Config option saves into templates
                require_code('caches3');
                erase_cached_templates(false, array('GLOBAL_HTML_WRAP'));
            }
        }

        $clickable_sections = (get_param_integer('clickable_sections', 0) == 1); // This is set to '1 if we have a menu type where pop out sections may be clicked on to be loaded. If we do then we make no UI distinction between page nodes and contracted/expanded, so people don't get compelled to choose a URL for everything, it simply becomes an option for them.

        // This will be a templates for branches created dynamically
        $t_id = 'replace_me_with_random';
        $branch = do_template('MENU_EDITOR_BRANCH', array(
            '_GUID' => '59d5c9bebecdac1440112ef8301d7c67',
            'CLICKABLE_SECTIONS' => $clickable_sections ? 'true' : 'false',
            'I' => $t_id,
            'CHILD_BRANCH_TEMPLATE' => '',
            'CHILD_BRANCHES' => '',
        ));
        $child_branch_template = do_template('MENU_EDITOR_BRANCH_WRAP', array(
            '_GUID' => 'fb16265f553127b47dfdaf33a420136b',
            'DISPLAY' => $clickable_sections ? 'display: block' : 'display: none',
            'CLICKABLE_SECTIONS' => $clickable_sections,
            'ORDER' => 'replace_me_with_order',
            'PARENT' => 'replace_me_with_parent',
            'BRANCH_TYPE' => '0',
            'NEW_WINDOW' => '0',
            'CHECK_PERMS' => '0',
            'INCLUDE_SITEMAP' => '0',
            'CAPTION_LONG' => '',
            'CAPTION' => '',
            'URL' => '',
            'PAGE_ONLY' => '',
            'THEME_IMG_CODE' => '',
            'I' => $t_id,
            'BRANCH' => $branch,
        ));

        $order = 0;
        $menu_items = $GLOBALS['SITE_DB']->query_select('menu_items', array('*'), array('i_menu' => $id), 'ORDER BY i_parent,i_order');
        $child_branches = $this->menu_branch($id, null, $order, $clickable_sections, $menu_items);

        $root_branch = do_template('MENU_EDITOR_BRANCH', array(
            '_GUID' => '28009b66089c05744d2727ff4689e43e',
            'CLICKABLE_SECTIONS' => $clickable_sections ? 'true' : 'false',
            'CHILD_BRANCH_TEMPLATE' => $child_branch_template,
            'CHILD_BRANCHES' => $child_branches,
            'I' => '',
        ));

        $map = array('page' => '_SELF', 'type' => '_edit', 'id' => $id);
        if (get_param_string('redirect', '!') != '!') {
            $map['redirect'] = get_param_string('redirect');
        }
        $post_url = build_url($map, '_SELF');

        $map = array('page' => '_SELF', 'type' => '_edit', 'id' => $id); // Actually same as edit URL, just we put this into an empty post form
        if (get_param_string('redirect', '!') != '!') {
            $map['redirect'] = get_param_string('redirect');
        }
        $delete_url = build_url($map, '_SELF');

        require_code('form_templates');
        $fields_template = new Tempcode();
        $fields_template->attach(form_input_line(do_lang_tempcode('LINK'), do_lang_tempcode('MENU_ENTRY_URL'), 'url', '', false));
        $options = array(
            array(do_lang_tempcode('MENU_ENTRY_NEW_WINDOW'), 'new_window', false, new Tempcode()),
            array(do_lang_tempcode('MENU_ENTRY_CHECK_PERMS'), 'check_perms', true, do_lang_tempcode('DESCRIPTION_MENU_ENTRY_CHECK_PERMS')),
        );
        $fields_template->attach(form_input_various_ticks($options, '', null, do_lang_tempcode('OPTIONS'), false));
        $list = new Tempcode();
        if (!$clickable_sections) {
            $list->attach(form_input_list_entry('page', false, do_lang_tempcode('PAGE')));
        }
        $list->attach(form_input_list_entry('branch_minus', false, do_lang_tempcode('CONTRACTED_BRANCH')));
        $list->attach(form_input_list_entry('branch_plus', false, do_lang_tempcode('EXPANDED_BRANCH')));
        $fields_template->attach(form_input_list(do_lang_tempcode('BRANCH_TYPE'), do_lang_tempcode('MENU_ENTRY_BRANCH'), 'branch_type', $list));

        $fields_template->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '9d8636a88bfca7069d1fc0ff5a30c237', 'SECTION_HIDDEN' => true, 'TITLE' => do_lang_tempcode('ADVANCED'))));
        $fields_template->attach(form_input_line(do_lang_tempcode('CAPTION_LONG'), do_lang_tempcode('MENU_ENTRY_CAPTION_LONG'), 'caption_long', '', false));
        $list = new Tempcode();
        $list->attach(form_input_list_entry('', false, do_lang_tempcode('NONE_EM')));
        require_code('themes2');
        $list->attach(create_selection_list_theme_images(null, null, false, true));
        $fields_template->attach(form_input_list(do_lang_tempcode('THEME_IMAGE'), do_lang_tempcode('DESCRIPTION_THEME_IMAGE_FOR_MENU_ITEM'), 'theme_img_code', $list, null, false, false, get_all_image_ids_type('icons', true)));
        $fields_template->attach(form_input_line(do_lang_tempcode('RESTRICT_PAGE_VISIBILITY'), do_lang_tempcode('MENU_ENTRY_MATCH_KEYS'), 'page_only', '', false));
        $list = new Tempcode();
        $list->attach(form_input_list_entry('0', false, do_lang_tempcode('INCLUDE_SITEMAP_NO')));
        $list->attach(form_input_list_entry('1', false, do_lang_tempcode('INCLUDE_SITEMAP_OVER')));
        $list->attach(form_input_list_entry('2', false, do_lang_tempcode('INCLUDE_SITEMAP_UNDER')));
        $fields_template->attach(form_input_list(do_lang_tempcode('INCLUDE_SITEMAP'), new Tempcode(), 'include_sitemap', $list, null, false, false));

        require_javascript('ajax');
        require_javascript('tree_list');

        list($warning_details, $ping_url) = handle_conflict_resolution();

        $all_menus = array();
        $menu_rows = $GLOBALS['SITE_DB']->query_select('menu_items', array('DISTINCT i_menu'), null, 'ORDER BY i_menu');
        foreach ($menu_rows as $menu_row) {
            if ($menu_row['i_menu'] != $id) {
                $all_menus[] = $menu_row['i_menu'];
            }
        }

        return do_template('MENU_EDITOR_SCREEN', array(
            '_GUID' => 'd2bc26eaea38f3d5b3221be903ff541e',
            'ALL_MENUS' => $all_menus,
            'MENU_NAME' => $id,
            'DELETE_URL' => $delete_url,
            'PING_URL' => $ping_url,
            'WARNING_DETAILS' => $warning_details,
            'FIELDS_TEMPLATE' => $fields_template,
            'HIGHEST_ORDER' => strval($order),
            'URL' => $post_url,
            'CHILD_BRANCH_TEMPLATE' => $child_branch_template,
            'ROOT_BRANCH' => $root_branch,
            'TITLE' => $this->title,
        ));
    }

    /**
     * Show a branch-editor of the menu editor.
     *
     * @param  AUTO_LINK $id The ID of the branch we are displaying items for
     * @param  integer $branch The parent branch holding the branch
     * @param  integer $order The order this branch has in the editor (and due to linearly moving through, the number of branches shown assembled ready)
     * @param  boolean $clickable_sections Whether childed branches themselves can have URLs (etc)
     * @param  array $menu_items All rows on the menu
     * @return Tempcode The part of the UI
     */
    public function menu_branch($id, $branch, &$order, $clickable_sections, $menu_items)
    {
        $child_branches = new Tempcode();
        foreach ($menu_items as $menu_item) {
            if ($menu_item['i_parent'] == $branch) {
                $caption = get_translated_text($menu_item['i_caption']);
                $url = $menu_item['i_url'];
                $page_only = $menu_item['i_page_only'];
                $theme_img_code = $menu_item['i_theme_img_code'];
                $new_window = $menu_item['i_new_window'];
                $check_perms = $menu_item['i_check_permissions'];
                $include_sitemap = $menu_item['i_include_sitemap'];
                $caption_long = get_translated_text($menu_item['i_caption_long']);
                $branch_type = 0;
                foreach ($menu_items as $_menu_item) {
                    if ($_menu_item['i_parent'] == $menu_item['id']) {
                        $branch_type = ($menu_item['i_expanded'] == 1) ? 2 : 1;
                        break;
                    }
                }
                if (($url == '') && ($branch_type == 0)) {
                    $branch_type = 1;
                }

                $display = (($branch_type == 0) && (!$clickable_sections)) ? 'display: none' : '';
                $_child_branches = $this->menu_branch($id, $menu_item['id'], $order, $clickable_sections, $menu_items);
                $_branch = do_template('MENU_EDITOR_BRANCH', array('_GUID' => '381f5600da214b84e300bcf668f66570', 'CLICKABLE_SECTIONS' => $clickable_sections ? 'true' : 'false', 'I' => strval($menu_item['id']), 'CHILD_BRANCHES' => $_child_branches->evaluate()));
                $_wrap = do_template('MENU_EDITOR_BRANCH_WRAP', array(
                    '_GUID' => '1ace7da7a1d8a18f13305eec5069e4c5',
                    'DISPLAY' => $display,
                    'CLICKABLE_SECTIONS' => $clickable_sections,
                    'ORDER' => strval($order),
                    'PARENT' => is_null($branch) ? '' : strval($branch),
                    'BRANCH_TYPE' => strval($branch_type),
                    'NEW_WINDOW' => strval($new_window),
                    'CHECK_PERMS' => strval($check_perms),
                    'INCLUDE_SITEMAP' => strval($include_sitemap),
                    'CAPTION' => $caption,
                    'CAPTION_LONG' => $caption_long,
                    'URL' => $url,
                    'PAGE_ONLY' => $page_only,
                    'THEME_IMG_CODE' => $theme_img_code,
                    'I' => strval($menu_item['id']),
                    'BRANCH' => $_branch,
                ));
                $child_branches->attach($_wrap);
                $order++;
            }
        }

        return $child_branches;
    }

    /**
     * The actualiser to edit a menu.
     *
     * @return Tempcode The UI
     */
    public function _edit_menu()
    {
        post_param_integer('confirm'); // Just to make sure hackers don't try and get people to erase this form via a URL

        $menu_id = get_param_string('id');

        if (post_param_integer('delete_confirm', 0) == 1) {
            delete_menu($menu_id);

            log_it('DELETE_MENU', $menu_id);

            // Go back to menu editor screen
            $url = get_param_string('redirect', null);
            if ($url === null) {
                $_url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');
                $url = $_url->evaluate();
            }
        } else {
            // Find what we have on the menu first
            $ids = array();
            foreach ($_POST as $key => $val) {
                if (is_string($val)) {
                    if (substr($key, 0, 7) == 'parent_') {
                        $ids[intval(substr($key, 7))] = $val;
                    }
                }
            }

            $orderings = array_keys($ids);

            // Get language strings currently used
            $old_menu_bits = list_to_map('id', $GLOBALS['SITE_DB']->query_select('menu_items', array('id', 'i_caption', 'i_caption_long'), array('i_menu' => $menu_id)));

            // Now, process everything on the root
            $order = 0;
            foreach ($orderings as $id) {
                $parent = $ids[$id];

                if ($parent == '') {
                    $this->add_menu_item($menu_id, $id, $ids, null, $old_menu_bits, $order);
                    $order++;
                }
            }

            // Erase old stuff
            foreach ($old_menu_bits as $menu_item_id => $lang_code) {
                $GLOBALS['SITE_DB']->query_delete('menu_items', array('id' => $menu_item_id));
                delete_lang($lang_code['i_caption']);
                delete_lang($lang_code['i_caption_long']);
            }

            log_it('EDIT_MENU', $menu_id);

            // Go back to editing the menu
            $url = get_param_string('redirect', null);
            if ($url === null) {
                $_url = build_url(array('page' => '_SELF', 'type' => 'edit', 'id' => $menu_id), '_SELF');
                $url = $_url->evaluate();
            }
        }

        decache('menu');
        persistent_cache_delete(array('MENU', $menu_id));

        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }

    /**
     * Add a menu item from details in POST.
     *
     * @param  ID_TEXT $menu The name of the menu the item is on
     * @param  integer $id The ID of the menu item (i.e. what it is referenced as in POST)
     * @param  array $ids The map of IDs on the menu (ID=>parent)
     * @param  ?integer $parent The ID of the parent branch (null: no parent)
     * @param  array $old_menu_bits The map of menu id=>string language string IDs employed by items before the edit
     * @param  integer $order The order this branch has in the editor (and due to linearly moving through, the number of branches shown assembled ready)
     */
    public function add_menu_item($menu, $id, &$ids, $parent, &$old_menu_bits, &$order)
    {
        // Load in details of menu item
        $caption = post_param_string('caption_' . strval($id), ''); // Default needed to workaround Opera problem
        $caption_long = post_param_string('caption_long_' . strval($id), ''); // Default needed to workaround Opera problem
        $page_only = post_param_string('page_only_' . strval($id), ''); // Default needed to workaround Opera problem
        $theme_img_code = post_param_string('theme_img_code_' . strval($id), ''); // Default needed to workaround Opera problem
        $check_permissions = post_param_integer('check_perms_' . strval($id), 0);
        $branch_type = post_param_string('branch_type_' . strval($id), 'branch_plus'); // Default needed to workaround Opera problem
        if ($branch_type == 'branch_plus') {
            $expanded = 1;
        } else {
            $expanded = 0;
        }
        $new_window = post_param_integer('new_window_' . strval($id), 0);
        $include_sitemap = post_param_integer('include_sitemap_' . strval($id), 0);

        $url = post_param_string('url_' . strval($id), '');

        // See if we can tidy it back to a page-link
        if (preg_match('#^[' . URL_CONTENT_REGEXP . ']+$#', $url) != 0) {
            $url = ':' . $url; // So users do not have to think about zones
        }
        $page_link = url_to_page_link($url, true);
        if ($page_link != '') {
            $url = $page_link;
        } elseif (strpos($url, ':') === false) {
            $url = fixup_protocolless_urls($url);
        }

        $menu_save_map = array(
            'i_menu' => $menu,
            'i_order' => $order,
            'i_parent' => $parent,
            'i_url' => $url,
            'i_check_permissions' => $check_permissions,
            'i_expanded' => $expanded,
            'i_new_window' => $new_window,
            'i_include_sitemap' => $include_sitemap,
            'i_page_only' => $page_only,
            'i_theme_img_code' => $theme_img_code,
        );

        // Save
        if (array_key_exists($id, $old_menu_bits)) {
            $menu_save_map += lang_remap_comcode('i_caption', $old_menu_bits[$id]['i_caption'], $caption);
            $menu_save_map += lang_remap_comcode('i_caption_long', $old_menu_bits[$id]['i_caption_long'], $caption_long);
            $GLOBALS['SITE_DB']->query_update('menu_items', $menu_save_map, array('id' => $id));

            unset($old_menu_bits[$id]);
            $insert_id = $id;
        } else {
            $menu_save_map += insert_lang_comcode('i_caption', $caption, 1);
            $menu_save_map += insert_lang_comcode('i_caption_long', $caption_long, 1);
            $insert_id = $GLOBALS['SITE_DB']->query_insert('menu_items', $menu_save_map, true);
        }

        // Menu item children
        $my_kids = array();
        foreach ($ids as $new_id => $child_parent) {
            if (strval($id) == $child_parent) {
                $my_kids[] = $new_id;
            }
        }

        foreach ($my_kids as $new_id) {
            $this->add_menu_item($menu, $new_id, $ids, $insert_id, $old_menu_bits, $order);
            $order++;
        }
    }
}