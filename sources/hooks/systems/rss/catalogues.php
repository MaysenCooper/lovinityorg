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
 * @package    catalogues
 */

/**
 * Hook class.
 */
class Hook_rss_catalogues
{
    /**
     * Run function for RSS hooks.
     *
     * @param  string $_filters A list of categories we accept from
     * @param  TIME $cutoff Cutoff time, before which we do not show results from
     * @param  string $prefix Prefix that represents the template set we use
     * @set    RSS_ ATOM_
     * @param  string $date_string The standard format of date to use for the syndication type represented in the prefix
     * @param  integer $max The maximum number of entries to return, ordering by date
     * @return ?array A pair: The main syndication section, and a title (null: error)
     */
    public function run($_filters, $cutoff, $prefix, $date_string, $max)
    {
        if (!addon_installed('catalogues')) {
            return null;
        }

        if (!has_actual_page_access(get_member(), 'catalogues')) {
            return null;
        }

        $filters_1 = selectcode_to_sqlfragment($_filters, 'id', 'catalogue_categories', 'cc_parent_id', 'id', 'id'); // Note that the parameters are fiddled here so that category-set and record-set are the same, yet SQL is returned to deal in an entirely different record-set (entries' record-set)
        $filters = selectcode_to_sqlfragment($_filters, 'cc_id', 'catalogue_categories', 'cc_parent_id', 'cc_id', 'id'); // Note that the parameters are fiddled here so that category-set and record-set are the same, yet SQL is returned to deal in an entirely different record-set (entries' record-set)

        require_code('catalogues');

        $_categories = $GLOBALS['SITE_DB']->query('SELECT id,c_name,cc_title FROM ' . get_table_prefix() . 'catalogue_categories WHERE ' . $filters_1, 300, null, false, true);
        foreach ($_categories as $i => $_category) {
            $_categories[$i]['_title'] = get_translated_text($_category['cc_title']);
        }
        $rows = $GLOBALS['SITE_DB']->query('SELECT * FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'catalogue_entries WHERE ce_add_date>' . strval(time() - $cutoff) . (((!has_privilege(get_member(), 'see_unvalidated')) && (addon_installed('unvalidated'))) ? ' AND ce_validated=1 ' : '') . ' AND ' . $filters . ' ORDER BY ce_add_date DESC', $max, null, false, true);
        $categories = array();
        foreach ($_categories as $category) {
            $categories[$category['id']] = $category;
        }

        $privacy_join = '';
        $privacy_where = '';
        if (addon_installed('content_privacy')) {
            require_code('content_privacy');
            list($privacy_join, $privacy_where) = get_privacy_where_clause('catalogue_entry', 'e');
        }

        $query = 'SELECT c.* FROM ' . get_table_prefix() . 'catalogues c';
        if (can_arbitrary_groupby()) {
            $query .= ' JOIN ' . get_table_prefix() . 'catalogue_entries e ON e.c_name=c.c_name' . $privacy_join . ' WHERE 1=1' . $privacy_where . ' GROUP BY c.c_name';
        }
        $_catalogues = $GLOBALS['SITE_DB']->query($query);

        $catalogues = array();
        foreach ($_catalogues as $catalogue) {
            $catalogues[$catalogue['c_name']] = $catalogue;
        }

        $content = new Tempcode();
        foreach ($rows as $row) {
            if ((count($_categories) == 300) && (!array_key_exists($row['cc_id'], $categories))) {
                $val = $GLOBALS['SITE_DB']->query('SELECT id,c_name,cc_title FROM ' . get_table_prefix() . 'catalogue_categories WHERE id=' . strval($row['cc_id']) . ' AND (' . $filters_1 . ')');
                if (array_key_exists(0, $val)) {
                    $categories[$row['cc_id']] = $val[0];
                    $categories[$row['cc_id']]['_title'] = get_translated_text($val[0]['cc_title']);
                }
            }
            if (!array_key_exists($row['cc_id'], $categories)) {
                continue; // The catalogue was filtered out, thus category not known
            }
            $_category = $categories[$row['cc_id']];
            if ((has_category_access(get_member(), 'catalogues_catalogue', $_category['c_name'])) && ((get_value('disable_cat_cat_perms') === '1') || (has_category_access(get_member(), 'catalogues_category', strval($row['cc_id']))))) {
                if (!isset($catalogues[$_category['c_name']])) {
                    continue;
                }

                $id = strval($row['id']);
                $author = $GLOBALS['FORUM_DRIVER']->get_username($row['ce_submitter']);
                if (is_null($author)) {
                    $author = '';
                }

                $news_date = date($date_string, $row['ce_add_date']);
                $edit_date = is_null($row['ce_edit_date']) ? '' : date($date_string, $row['ce_edit_date']);

                $tpl_set = $_category['c_name'];
                $map = get_catalogue_entry_map($row, $catalogues[$_category['c_name']], 'PAGE', $tpl_set, db_get_first_id());
                $_title = $map['FIELD_0'];
                $news_title = xmlentities(is_object($_title) ? $_title->evaluate() : escape_html($_title));
                if (array_key_exists('FIELD_1', $map)) {
                    $summary = xmlentities(is_object($map['FIELD_1']) ? $map['FIELD_1']->evaluate() : $map['FIELD_1']);
                } else {
                    $summary = '';
                }
                $news = '';

                $category = $_category['_title'];
                $category_raw = strval($row['cc_id']);

                $view_url = build_url(array('page' => 'catalogues', 'type' => 'entry', 'id' => $row['id']), get_module_zone('catalogues'), null, false, false, true);

                if (($prefix == 'RSS_') && (get_option('is_on_comments') == '1') && ($row['allow_comments'] >= 1)) {
                    $if_comments = do_template('RSS_ENTRY_COMMENTS', array('_GUID' => 'ee850d0e7f50b21f2dbe17cc49494baa', 'COMMENT_URL' => $view_url, 'ID' => strval($row['id'])), null, false, null, '.xml', 'xml');
                } else {
                    $if_comments = new Tempcode();
                }

                $content->attach(do_template($prefix . 'ENTRY', array('VIEW_URL' => $view_url, 'SUMMARY' => $summary, 'EDIT_DATE' => $edit_date, 'IF_COMMENTS' => $if_comments, 'TITLE' => $news_title, 'CATEGORY_RAW' => $category_raw, 'CATEGORY' => $category, 'AUTHOR' => $author, 'ID' => $id, 'NEWS' => $news, 'DATE' => $news_date), null, false, null, '.xml', 'xml'));
            }
        }

        require_lang('catalogues');
        return array($content, do_lang('CATALOGUE_ENTRIES'));
    }
}