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
 * @package    wiki
 */

/**
 * Hook class.
 */
class Hook_addon_registry_wiki
{
    /**
     * Get a list of file permissions to set
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array($runtime = false)
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the description of the addon
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'Collaborative/encyclopaedic database interface. A wiki-like community database with rich media capabilities.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_wiki',
            'tut_information',
        );
    }

    /**
     * Get a mapping of dependency types
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(),
            'recommends' => array(),
            'conflicts_with' => array(),
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/rich_content/wiki.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/rich_content/wiki.png',
            'themes/default/images/icons/24x24/menu/rich_content/wiki/random_page.png',
            'themes/default/images/icons/48x48/menu/rich_content/wiki.png',
            'themes/default/images/icons/48x48/menu/rich_content/wiki/random_page.png',
            'themes/default/images/icons/24x24/buttons/edit_tree.png',
            'themes/default/images/icons/48x48/buttons/edit_tree.png',
            'themes/default/images/icons/24x24/menu/rich_content/wiki/index.html',
            'themes/default/images/icons/48x48/menu/rich_content/wiki/index.html',
            'sources/hooks/systems/sitemap/wiki_page.php',
            'sources/hooks/systems/content_meta_aware/wiki_page.php',
            'sources/hooks/systems/content_meta_aware/wiki_post.php',
            'sources/hooks/systems/commandr_fs/wiki.php',
            'sources/hooks/systems/config/wiki_show_stats_count_pages.php',
            'sources/hooks/systems/config/wiki_show_stats_count_posts.php',
            'sources/hooks/systems/config/points_wiki.php',
            'sources/hooks/systems/config/wiki_enable_children.php',
            'sources/hooks/systems/config/wiki_enable_content_posts.php',
            'sources/hooks/systems/meta/wiki_page.php',
            'sources/hooks/systems/disposable_values/num_wiki_files.php',
            'sources/hooks/systems/disposable_values/num_wiki_pages.php',
            'sources/hooks/systems/disposable_values/num_wiki_posts.php',
            'sources/hooks/systems/cns_cpf_filter/points_wiki.php',
            'sources/hooks/systems/addon_registry/wiki.php',
            'sources/hooks/modules/admin_themewizard/wiki.php',
            'sources/hooks/modules/admin_import_types/wiki.php',
            'themes/default/templates/WIKI_LIST_TREE_LINE.tpl',
            'themes/default/templates/WIKI_MANAGE_TREE_SCREEN.tpl',
            'themes/default/templates/WIKI_PAGE_SCREEN.tpl',
            'themes/default/templates/WIKI_POST.tpl',
            'themes/default/templates/WIKI_POSTING_SCREEN.tpl',
            'themes/default/templates/WIKI_RATING.tpl',
            'themes/default/templates/WIKI_RATING_FORM.tpl',
            'themes/default/css/wiki.css',
            'sources/hooks/systems/ajax_tree/choose_wiki_page.php',
            'cms/pages/modules/cms_wiki.php',
            'lang/EN/wiki.ini',
            'site/pages/modules/wiki.php',
            'sources/wiki.php',
            'sources/wiki_stats.php',
            'sources/hooks/blocks/main_staff_checklist/wiki.php',
            'sources/hooks/blocks/side_stats/stats_wiki.php',
            'sources/hooks/modules/admin_newsletter/wiki.php',
            'sources/hooks/modules/admin_unvalidated/wiki.php',
            'sources/hooks/modules/search/wiki_pages.php',
            'sources/hooks/modules/search/wiki_posts.php',
            'sources/hooks/systems/attachments/wiki_page.php',
            'sources/hooks/systems/attachments/wiki_post.php',
            'sources/hooks/systems/page_groupings/wiki.php',
            'sources/hooks/systems/preview/wiki_page.php',
            'sources/hooks/systems/preview/wiki_post.php',
            'sources/hooks/systems/rss/wiki.php',
            'sources/hooks/systems/module_permissions/wiki_page.php',
            'sources/hooks/systems/notifications/wiki.php',
        );
    }

    /**
     * Get mapping between template names and the method of this class that can render a preview of them
     *
     * @return array The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/WIKI_MANAGE_TREE_SCREEN.tpl' => 'administrative__wiki_manage_tree_screen',
            'templates/WIKI_LIST_TREE_LINE.tpl' => 'wiki_list_tree',
            'templates/WIKI_POST.tpl' => 'wiki_page_screen',
            'templates/WIKI_PAGE_SCREEN.tpl' => 'wiki_page_screen',
            'templates/WIKI_RATING.tpl' => 'wiki_page_screen',
            'templates/WIKI_POSTING_SCREEN.tpl' => 'wiki_posting_screen',
            'templates/WIKI_RATING_FORM.tpl' => 'wiki_page_screen'
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__wiki_manage_tree_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('WIKI_MANAGE_TREE_SCREEN', array(
                'PAGE_TITLE' => lorem_phrase(),
                'PING_URL' => placeholder_url(),
                'WARNING_DETAILS' => '',
                'TITLE' => lorem_title(),
                'FORM' => placeholder_form(),
                'WIKI_TREE' => placeholder_options(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__wiki_list_tree()
    {
        return array(
            lorem_globalise(do_lorem_template('WIKI_LIST_TREE_LINE', array(
                'BREADCRUMBS' => lorem_phrase(),
                'TITLE' => lorem_word(),
                'ID' => placeholder_id(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__wiki_page_screen()
    {
        require_lang('cns');

        $extra = new Tempcode();
        $extra = do_lorem_template('BUTTON_SCREEN_ITEM', array(
            'REL' => 'edit',
            'IMMEDIATE' => false,
            'URL' => placeholder_url(),
            'TITLE' => do_lang_tempcode('EDIT'),
            'FULL_TITLE' => do_lang_tempcode('EDIT'),
            'IMG' => 'buttons__edit',
        ));
        $extra->attach(do_lorem_template('BUTTON_SCREEN_ITEM', array(
            'REL' => 'move',
            'IMMEDIATE' => false,
            'URL' => placeholder_url(),
            'TITLE' => do_lang_tempcode('MOVE'),
            'FULL_TITLE' => do_lang_tempcode('MOVE'),
            'IMG' => 'buttons__move',
        )));

        $all_rating_criteria = array();
        $all_rating_criteria[] = array(
            'TITLE' => lorem_word(),
            'RATING' => make_string_tempcode('6'),
            'NUM_RATINGS' => placeholder_number(),
            'TYPE' => lorem_word(),
        );
        $rating_inside = do_lorem_template('WIKI_RATING_FORM', array(
            'LIKES' => true,
            'CONTENT_TYPE' => 'wiki',
            'ID' => placeholder_id(),
            'URL' => placeholder_url(),
            'ALL_RATING_CRITERIA' => $all_rating_criteria,
            'OVERALL_NUM_RATINGS' => placeholder_number(),
            'HAS_RATINGS' => true,
            'SIMPLISTIC' => true,
            'ERROR' => '',
        ));

        $rating_details = do_lorem_template('WIKI_RATING', array(
            'OVERALL_NUM_RATINGS' => placeholder_number(),
            'RATING_FORM' => $rating_inside,
            'ALL_RATING_CRITERIA' => $all_rating_criteria,
            'HAS_RATINGS' => true,
        ));

        $posts = do_lorem_template('WIKI_POST', array(
            'INCLUDE_EXPANSION' => lorem_phrase(),
            'UNVALIDATED' => do_lang('UNVALIDATED'),
            'STAFF_ACCESS' => lorem_phrase(),
            'EXP_IMG' => placeholder_img_code(),
            'RATE_URL' => placeholder_url(),
            'RATING' => $rating_details,
            'ID' => placeholder_id(),
            'POSTER_URL' => placeholder_url(),
            'POSTER' => lorem_phrase(),
            'POST_DATE_RAW' => placeholder_date(),
            'POST_DATE' => placeholder_date(),
            'POST' => lorem_phrase(),
            'BUTTONS' => $extra,
        ));

        $children = array();
        $children[] = array(
            'URL' => placeholder_url(),
            'MY_CHILD_POSTS' => placeholder_number(),
            'MY_CHILD_CHILDREN' => placeholder_number(),
            'CHILD' => lorem_phrase(),
            'BODY_CONTENT' => placeholder_number(),
        );

        return array(
            lorem_globalise(do_lorem_template('WIKI_PAGE_SCREEN', array(
                'TAGS' => lorem_word_html(),
                'HIDE_POSTS' => placeholder_id(),
                'ID' => placeholder_id(),
                'CHAIN' => placeholder_id(),
                'VIEWS' => placeholder_number(),
                'STAFF_ACCESS' => '1',
                'DESCRIPTION' => lorem_paragraph_html(),
                'TITLE' => lorem_title(),
                'CHILDREN' => $children,
                'POSTS' => $posts,
                'NUM_POSTS' => placeholder_number(),
                'BUTTONS' => placeholder_button(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__wiki_posting_screen()
    {
        require_javascript('checking');
        require_lang('comcode');

        require_css('forms');

        $posting_form = do_lorem_template('POSTING_FORM', array(
            'TABINDEX_PF' => placeholder_number(),
            'JAVASCRIPT' => '',
            'PREVIEW' => lorem_phrase(),
            'COMCODE_EDITOR' => lorem_phrase(),
            'COMCODE_EDITOR_SMALL' => lorem_phrase(),
            'CLASS' => lorem_phrase(),
            'COMCODE_URL' => placeholder_url(),
            'EXTRA' => '',
            'POST_COMMENT' => lorem_phrase(),
            'EMOTICON_CHOOSER' => lorem_phrase(),
            'SUBMIT_ICON' => 'menu___generic_admin__add_one',
            'SUBMIT_NAME' => lorem_word(),
            'HIDDEN_FIELDS' => '',
            'URL' => placeholder_url(),
            'POST' => lorem_phrase(),
            'DEFAULT_PARSED' => lorem_phrase(),
            'CONTINUE_URL' => placeholder_url(),
            'ATTACHMENTS' => lorem_phrase(),
            'SPECIALISATION' => placeholder_fields(),
            'SPECIALISATION2' => '',
            'REQUIRED' => true,
            'SUPPORT_AUTOSAVE' => true,
        ));

        return array(
            lorem_globalise(do_lorem_template('WIKI_POSTING_SCREEN', array(
                'PING_URL' => '',
                'WARNING_DETAILS' => '',
                'TEXT' => lorem_phrase(),
                'TITLE' => lorem_title(),
                'POSTING_FORM' => $posting_form,
            )), null, '', true)
        );
    }
}